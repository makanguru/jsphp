<?php
	if( !function_exists( 'is_plugin_active' ) )
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	
	if( is_plugin_active( 'advanced-custom-fields/acf.php' ) || is_plugin_active( 'advanced-custom-fields-pro/acf.php' ) ):
		add_filter( 'hmr_postmeta_serialize', 'hmr_acf_is_checkbox' );
		add_filter( 'hmr_get_postmeta_values', 'hmr_acf_postmeta_values_of_checkboxes', 10, 2 );
		add_filter( 'hmr-filter-args', 'hmr_acf_check_args_for_checkboxes' );
	endif;
	
	function hmr_acf_is_checkbox( $return ){
		global $wpdb;
		if( $return['add_this'] )
			return $return;
		
		$meta_key = $return['meta_key'];
		$sql = "select meta_value, post_id from " . $wpdb->prefix . "postmeta where meta_key = %s group by meta_value order by post_id asc";
		$sql = $wpdb->prepare( $sql, '_' . $meta_key );
		$res = $wpdb->get_results( $sql );
		
		if( count( $res ) == 0 )
			return $return;
			
		$options = get_field_object( $meta_key, $res[0]->post_id );
		
		if( $options['type'] != 'checkbox' )
			return $return;
			
		return array( 'add_this' => true, 'meta_key' => $meta_key );
	}
	
	function hmr_acf_postmeta_values_of_checkboxes( $value  ){
		global $wpdb;
		
		$meta_key = $value[0]->meta_key;
		$sql = "select meta_value from " . $wpdb->prefix . "postmeta where meta_key = %s group by meta_value";
		$sql = $wpdb->prepare( $sql, '_' . $meta_key );
		$res = $wpdb->get_results( $sql );
		
		if( count( $res ) == 0 )
			return $value;
			
		$sql = "select meta_value from " . $wpdb->prefix . "postmeta where meta_key = %s group by meta_value";
		$sql = $wpdb->prepare( $sql, $res[0]->meta_value );
		$res = $wpdb->get_results( $sql );
		
		if( count( $res ) == 0 )
			return $value;
		
		$maybe_checkbox = maybe_unserialize( $res[0]->meta_value );		
		if( !isset( $maybe_checkbox['choices'] ) || !isset( $maybe_checkbox['type'] ) || $maybe_checkbox['type'] != 'checkbox' )
			return $value;
		
		$choices = array();
		foreach( $maybe_checkbox['choices'] as $key => $val ):
			$choice['meta_key'] = $key;
			$choice['meta_value'] = $key;
			$choices[] = $choice;
		endforeach;
		$choices = json_encode( $choices );
		$choices = json_decode( $choices );
		
		return $choices;
	}
	
	function hmr_acf_check_args_for_checkboxes( $args ){
		if( !isset( $args['meta_query'] ) )
			return $args;
		
		$acf_fields = array();
		foreach( $args['meta_query'] as $key => $val ):
			$is_checkbox = hmr_acf_is_checkbox( array( 'add_this' => false, 'meta_key' => $val['key'] ) );
			if( $is_checkbox['add_this'] ):
				$acf_fields[] = $val;
				unset( $args['meta_query'][ $key ] );
			endif;
		endforeach;
		
		$where_meta = array();
		foreach( $acf_fields as $field ):
			if( !is_array( $field['value'] ) ):
				$where_meta[ $field['key'] ][] = 's:' . strlen( $field['value'] ) . ':"' . $field['value'] . '";';
			else:
				foreach( $field['value'] as $fv ):
					$where_meta[ $field['key'] ][] = 's:' . strlen( $fv ) . ':"' . esc_sql( like_escape( $fv ) ) . '";';
				endforeach;
			endif;
		endforeach;
		if( count( $where_meta ) > 0 ):
			add_filter( 'posts_join_paged', 'hmr_acf_checkbox_filter_join', 10, 2 );
			add_filter( 'posts_where', 'hmr_acf_checkbox_filter_where', 10, 2 );
			add_filter( 'posts_groupby', 'hmr_groupby' );
			$args['hmr-acfcheckbox-meta'] = $where_meta;
		endif;
		
		return $args;
	}
	
	function hmr_groupby($groupby) {
		global $wpdb;
		$groupby = "{$wpdb->posts}.ID";
		return $groupby;
}	
	
	function hmr_acf_checkbox_filter_join( $join_paged_statement, &$wp_query ){
		global $wpdb;
		$acf = $wp_query->get( 'hmr-acfcheckbox-meta' );
		if( isset( $acf ) && is_array( $acf ) && count( $acf ) > 0 ):
			foreach( $wp_query->get( 'hmr-acfcheckbox-meta' ) as $meta => $val ):
				$join_paged_statement .= " LEFT JOIN " . $wpdb->prefix . "postmeta as " . md5( $meta ) . " ON ( " . md5( $meta ) . ".post_id = " . $wpdb->prefix . "posts.ID ) ";
			endforeach;
		endif;
		remove_filter( 'posts_join_paged', 'hmr_acf_checkbox_filter_join', 10 );
		return $join_paged_statement;
	}
	
	function hmr_acf_checkbox_filter_where( $hmr_where, &$wp_query ){
		global $wpdb;
		$acf = $wp_query->get( 'hmr-acfcheckbox-meta' );
		$hmr_add_where = '';
		if( isset( $acf ) && is_array( $acf ) && count( $acf ) > 0 ):
			$hmr_add_where = ' AND (';
			$hmr_add_meta_arr = array();
			foreach( $acf as $meta => $search_term_array ):
				foreach( $search_term_array as $search_term ):
					$hmr_add_meta_arr[ $meta ][] = ' (' .md5( $meta ) . '.meta_value LIKE \'%' . $search_term . '%\' ) ';
				endforeach;
			endforeach;
			
			foreach( $hmr_add_meta_arr as $meta => $val ):
				if( $hmr_add_where != ' AND (' )
					$hmr_add_where .= ' ) AND ( ';
				$hmr_add_meta_single = '';
				foreach( $val as $sql ):
					if( !empty( $hmr_add_meta_single ) )
						$hmr_add_meta_single .= ' OR ';
					$hmr_add_meta_single .= $sql;
				endforeach;
				
				$hmr_add_where .= $hmr_add_meta_single . ' && ' .md5( $meta ) . '.meta_key = \'' . $meta . '\'';
			endforeach;
			$hmr_add_where .= ' ) ';
		endif;
		$hmr_where .= $hmr_add_where;
		remove_filter( 'posts_where', 'hmr_acf_checkbox_filter_where', 10 );
		return $hmr_where;
	}
?>