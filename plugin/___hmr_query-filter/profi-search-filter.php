<?php
	/*
	Plugin Name: ___MNV Filter
	*/
	
	define( 'HMR_CURRENT_VERSION', '1.0' );
	if( !session_id() )
		session_start();
		
	define( 'HMR_URL', plugins_url( '', __FILE__ ) . '/' );
	define( 'HMR_DIR', dirname( __FILE__ ) . '/' );
	define( 'HOME_URL', get_bloginfo( 'url' ) );
	define( 'HOME_NAME', get_bloginfo( 'name' ) );
	
	require_once( HMR_DIR . 'admin/admin.php' );
	require_once( HMR_DIR . 'ajax.php' );
	require_once( HMR_DIR . 'includes/wpml-functions.php' );
	require_once( HMR_DIR . 'includes/acf-checkboxes.php' );
	require_once( HMR_DIR . 'includes/types-checkboxes.php' );
	
	function hmr_textdomain() {
		$plugin_dir = basename( dirname( __FILE__ ) ) . '/res/lang/';
		load_plugin_textdomain( 'hmr', false, $plugin_dir );
	}
	add_action('plugins_loaded', 'hmr_textdomain');
	
	add_action( 'wp_head', 'hmr_head', 1 );
	function hmr_head(){
		$settings = get_option( 'search-filter-settings' );
		if( !isset( $settings['style'] ) || $settings['style'] == '' )
			wp_register_style( 'hmr-style', HMR_URL . 'res/style.css' );
		else
			wp_register_style( 'hmr-style', HMR_URL . 'res/' . $settings['style'] . '.css');
		wp_enqueue_style( 'hmr-style' );	
		
		
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-slider');
		wp_register_script( 'hmr-script', HMR_URL . 'res/hmr.js' );
		wp_enqueue_script( 'hmr-script' );
		
		?>
		<script>var hmr_ajax_root = '<?php echo admin_url('admin-ajax.php'); ?>'</script>
		<?php
	}
	
	
	function insert_searchform( $id ){
		$attr = array( 'id' => $id );
		
		ob_start();
		require( HMR_DIR . 'includes/shortcode.php' );
		$output_string=ob_get_contents();
		ob_end_clean();
		echo $output_string;
	}
	
	add_shortcode( 'search-form', 'hmr_init_searchform' );
	function hmr_init_searchform( $attr ){
		ob_start();
		require( HMR_DIR . 'includes/shortcode.php' );
		$output_string=ob_get_contents();
		ob_end_clean();
		return $output_string;
	}

	function get_all_postmetas_from_post_type( $post_type ){
		global $wpdb;
		if( !is_array( $post_type ) )
			$post_type = array( $post_type );
		
		
		$data   =   array();
		$sql = $wpdb->prepare( "
			SELECT $wpdb->postmeta.`meta_key`, $wpdb->postmeta.`meta_value`
			FROM $wpdb->postmeta, $wpdb->posts
			WHERE $wpdb->posts.`post_status` = 'publish' && $wpdb->posts.`post_type` IN (".implode(', ', array_fill(0, count($post_type), '%s')).") && $wpdb->postmeta.`post_id` = $wpdb->posts.`ID`
			group by $wpdb->postmeta.`meta_key`
		", $post_type );
		$wpdb->query( $sql );
		$no_key = array( '_edit_last', '_edit_lock', '_thumbnail_id' );		
		foreach($wpdb->last_result as $k => $v){
			if( !in_array( $v->meta_key , $no_key ) ):
				if( !is_array( maybe_unserialize( $v->meta_value ) ) ):
					$data[$v->meta_key] =   $v->meta_value;
				else:
					$add_this = apply_filters( 'hmr_postmeta_serialize', array( 'add_this' => false, 'meta_key' => $v->meta_key ) );
					if( $add_this['add_this'] )
						$data[ $v->meta_key ] = $v->meta_value;
				endif;
			endif;
		};
		return $data;
	}
	
	function get_all_post_taxonomies( $post_type ) {
		$taxonomies = array();
		if( !is_array( $post_type ) )
			$post_type = array( $post_type );
		foreach( $post_type as $p )
			$taxonomies = array_merge( $taxonomies, get_object_taxonomies($p, 'objects') ); 
		return (array) $taxonomies; 
	}
	
	function get_postmeta_values( $meta_key ){
		global $wpdb;
		$sql = $wpdb->prepare( "
			SELECT $wpdb->postmeta.`meta_key`, $wpdb->postmeta.`meta_value`
			FROM $wpdb->postmeta, $wpdb->posts
			WHERE 
				$wpdb->posts.`post_status` = 'publish' && 
				$wpdb->postmeta.`post_id` = $wpdb->posts.`ID` &&
				$wpdb->postmeta.`meta_key` = '%s' 
			group by 
				$wpdb->postmeta.`meta_value` order by $wpdb->postmeta.`meta_value` asc", 
			$meta_key );
		$res = $wpdb->get_results( $sql );
		$res = apply_filters( 'hmr_get_postmeta_values', $res );
		return $res;
	}
	
	function hmr_content_filter_join( $join_paged_statement, &$wp_query ) {
		global $wpdb;
		$metas = $wp_query->get( 'hmr-meta' );
		if( isset( $metas ) && is_array( $metas ) && count( $metas ) > 0 ){
			foreach( $wp_query->get( 'hmr-meta' ) as $meta => $val ){
				$join_paged_statement .= " LEFT JOIN " . $wpdb->prefix . "postmeta as HMR_" . md5( $meta ) . " ON ( HMR_" . md5( $meta ) . ".post_id = " . $wpdb->prefix . "posts.ID ) ";
			}
		}
		return $join_paged_statement;
	}
	
	function hmr_content_filter( $hmr_where, &$wp_query ){
        global $wpdb;
		
		if( $wp_query->get( 'hmr-title' ) || $wp_query->get( 'hmr-meta' ) || $wp_query->get( 'hmr-content' )  || $wp_query->get( 'hmr-excerpt' ) ):
			
			$concat_fields = array();
			
			$st = $wp_query->get( 'hmr-title' );
			if( isset( $st ) && !empty( $st ) ):
				$concat_fields[] = $wpdb->posts . '.post_title';
				$search_term = $st;
			endif;
			
			$st = $wp_query->get( 'hmr-content' );
			if( isset( $st ) && !empty( $st ) ):
				$concat_fields[] = $wpdb->posts . '.post_content';
				$search_term = $st;
			endif;
			
			$st = $wp_query->get( 'hmr-excerpt' );
			if( isset( $st ) && !empty( $st ) ):
				$concat_fields[] = $wpdb->posts . '.post_excerpt';
				$search_term = $st;
			endif;
			
			$metas = $wp_query->get( 'hmr-meta' );
			$post_meta_keys = array();
			if( isset( $metas ) && is_array( $metas ) && count( $metas ) > 0 ){
				foreach( $wp_query->get( 'hmr-meta' ) as $meta => $search_term ){
					preg_match( '^meta\[(.*)\]^', $meta, $match );
					$concat_fields[] = 'hmr_' . md5( $meta ) . '.meta_value';
					$post_meta_keys[ 'hmr_' . md5( $meta ) ] = $match[1];
				}
			}
			
			
			$concat_string = '';
			foreach( $concat_fields as $f ):
				$concat_string .= ',' . $f . ', " "';
			endforeach;
			$hmr_add_where = '';
			
			if( !empty( $concat_string ) ):
				$hmr_add_where = ' AND (';
				$concat_string = "CONCAT( \"\" " . $concat_string . ") LIKE '%" . esc_sql( like_escape( $search_term ) ) . "%' ";
				$hmr_add_where .= $concat_string;
				$hmr_add_where .= ' ) ';
				if( count( $post_meta_keys ) > 0 ):
					foreach( $post_meta_keys as $key => $val ):
						$hmr_add_where .= " AND " . $key . ".meta_key ='" . $val . "'";
					endforeach;
				endif;
			endif;
			$hmr_where .= $hmr_add_where;
		endif;
		
		return $hmr_where;
	}
	
	function order_terms_hierarchical_array( $terms, $parent = 0 ){
		$tmp_terms = array();
		foreach( $terms as $term ):
			if( $term->parent == $parent ):
				$term->children = order_terms_hierarchical_array( $terms, $term->term_id );
				$tmp_terms[] = $term;
			endif;
		endforeach;
		return $tmp_terms;
	}
	
	function flatten_terms_hierarchical_array( $terms, $symbol, $status = 0 ){
		$tmp_terms = array();
		foreach( $terms as $term ):
			if( $status == 1 ):
				$term->name = $symbol . ' ' . $term->name;
			endif;
			$tmp_terms[] = $term;
			
			if( count( $term->children ) > 0 ):
				if( $status == 1 )
					$s = $symbol . $symbol;
				else
					$s = $symbol;
				$tmp_terms = array_merge( $tmp_terms, flatten_terms_hierarchical_array( $term->children, $s, 1 ) ); 
			endif;
		endforeach;
		return $tmp_terms;
	}
	
	function order_terms_hierarchical( $terms, $symbol ){
		$terms = order_terms_hierarchical_array( $terms );
		$terms = flatten_terms_hierarchical_array( $terms, $symbol );		
		return $terms;
	}
?>