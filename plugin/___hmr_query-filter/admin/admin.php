<?php
	add_action( 'admin_enqueue_scripts', 'hmr_adminscripts' );
	function hmr_adminscripts( $hook ){
		if( !in_array( $hook, array( 'toplevel_page_search-filter', 'admin_page_search-filter-edit','search-filter_page_search-filter-new' ) ) )
			return;
			
		wp_register_style('hmr-admin-css', HMR_URL . '/res/admin-style.css' ); 
        wp_enqueue_style('hmr-admin-css');
		wp_enqueue_style( 'font-awesome', '//netdna.bootstrapcdn.com/font-awesome/4.0.1/css/font-awesome.css', null, '4.0.1' );
		wp_enqueue_media();
		wp_enqueue_script('jquery-ui-tabs');
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('jquery-ui-draggable');
		wp_enqueue_script('jquery-ui-droppable');
		wp_enqueue_script('jquery-ui-accordion');
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker');
		wp_register_script('hmr-admin-js', HMR_URL . '/res/admin.js' ); 
        wp_enqueue_script('hmr-admin-js');
		wp_localize_script( 'hmr-admin-js', 'objectL10n', array(
			'datasource' => __( 'Data Source', 'hmr' ),
			'value' => __( 'Value', 'hmr' ),
			'fieldname' => __( 'Fieldname', 'hmr' ),
			'save' => __( 'Save', 'hmr' ),
			'cancel' => __( 'Cancel', 'hmr' ),
			'fulltext_search' => __( 'Fulltext Search', 'hmr' ),
			'delete_element' => __( 'Delete', 'hmr' ),
			'selectbox' => __( 'Selectbox', 'hmr' ),
			'radiobox' => __( 'Radiobox', 'hmr' ),
			'inputfield' => __( 'Inputfield', 'hmr' ),
			'checkbox' => __( 'Checkbox', 'hmr' ),
			'range' => __( 'Range', 'hmr' ),
			'start_range' => __( 'Start Range', 'hmr' ),
			'end_range' => __( 'End Range', 'hmr' ),
			'unit' => __( 'Unit', 'hmr' ),
			'unit_in_front' => __( 'Show unit in front', 'hmr' ),
			'operator' => __( 'Operator', 'hmr' ),
			'equal' => __( 'Equal', 'hmr' ),
			'like' => __( 'Like', 'hmr' ),
			'bigger_than' => __( 'Bigger Than', 'hmr' ),
			'smaller_than' => __( 'Smaller Than', 'hmr' ),
			'bigger_than_or_equal' => __( 'Equal or Bigger Than', 'hmr' ),
			'smaller_than_or_equal' => __( 'Equal or Smaller Than', 'hmr' ),
			'options' => __( 'Options', 'hmr' ),
			'automatic' => __( 'Automatic', 'hmr' ),
			'individual' => __( 'Individual', 'hmr' ),
			'enter_option' => __( 'Enter Option', 'hmr' ),
			'enter_option_value' => __( 'Enter Option Value', 'hmr' ),
			'enter_option_key' => __( 'Enter Option Key', 'hmr' ),
			'add_option' => __( 'Add Option', 'hmr' ),
			'please_enter_an_option_key' => __( 'Please enter an Option Key', 'hmr' ),
			'please_enter_an_option_value' => __( 'Please enter an Option Value', 'hmr' ),
			'delete_option' => __( 'Remove Option', 'hmr' ),
			'search_contents' => __( 'Search Content', 'hmr' ),
			'the_title' => __( 'The Title', 'hmr' ),
			'the_content' => __( 'The Content', 'hmr' ),
			'the_excerpt' => __( 'The Excerpt', 'hmr' ),
			'orderby' => __( 'Order By', 'hmr' ),
			'map' => __( 'Map', 'hmr' ),
			'latitude' => __( 'Latitude', 'hmr' ),
			'longitude' => __( 'Longitude', 'hmr' ),
			'apikey' => __( 'API Key', 'hmr' ),
			'map_admin_text' => __( 'Please select the Postmeta(s), in which the Latitude and Longitude are saved.', 'hmr' ),
			'map_center_lat' => __( 'Center at Latitude', 'hmr' ),
			'map_center_lon' => __( 'Center at Longitude', 'hmr' ),
			'map_zoom' => __( 'Zoom', 'hmr' ),
			'map_zoom_placeholder' => __( 'Choose between 1 and 18', 'hmr' ),
			'map_style' => __( 'Map style', 'hmr' ),
			'ROADMAP' => __( 'Roadmap', 'hmr' ),
			'SATELLITE' => __( 'Satellite', 'hmr' ),
			'HYBRID' => __( 'Hybrid', 'hmr' ),
			'TERRAIN' => __( 'Terrain', 'hmr' ),
			'map_postmeta_options' => __( 'Postmeta Options', 'hmr' ),
			'map_map_options' => __( 'Map Options', 'hmr' ),
			'show_field_when'	=>	__( 'Show field when', 'hmr' ),
			'show_always'	=>	__( 'Show always', 'hmr' ),
			'really_delete'	=>	__( 'Do you want to delete this form?', 'hmr' ),
			'item_delete'	=>	__( 'Yes, delete form', 'hmr' ),
			'cancel'	=>	__( 'Cancel', 'hmr' ),
			'update_option'	=>	__( 'Update Option', 'hmr' ),
			'hidden_field'	=>	__( 'Hidden Field', 'hmr' ),
			'hierarchical'	=>	__( 'Hierarchical order', 'hmr' ),
			'symbol_to_indent'	=>	__( 'Symbol to indent', 'hmr' ),
			'step'	=>	__( 'Step', 'hmr' ),
			'others'	=>	__( 'Others', 'hmr' ),
			'author'	=>	__( 'Author', 'hmr' ),
			'datebox'	=>	__( 'Date published', 'hmr' ),
			'type'	=>	__( 'Type', 'hmr' ),
			'from'	=>	__( 'From', 'hmr' ),
			'till'	=>	__( 'Till', 'hmr' ),
			'between'	=>	__( 'Between', 'hmr' ),
			'source'	=>	__( 'Source', 'hmr' ),
			'published'	=>	__( 'Date published', 'hmr' ),
			'modified'	=>	__( 'Date Modified', 'hmr' ),
			'style'	=>	__( 'Style', 'hmr' ),
			'dateformat'	=>	__( 'Dateformat', 'hmr' ),
			'include_children'	=>	__( 'Include Children', 'hmr' ),
			'operator'	=>	__('Operator', 'hmr' ),
			'yes'	=>	__('Yes', 'hmr' ),
			'no'	=>	__('No', 'hmr' ),
			'txt_in'	=>	__('IN', 'hmr' ),
			'not_in'	=>	__('NOT IN', 'hmr' ),
			'and'	=>	__('AND', 'hmr' ),
			'term_operations'	=>	__('Term operations', 'hmr' )
		) );
		
        wp_dequeue_script('hmr-script');
	}
	add_action( 'admin_menu', 'wp_hmr_adminpage' );
	function wp_hmr_adminpage() {
		add_menu_page( __( 'Search Filter', 'hmr' ), 'Search Filter', 'edit_posts', 'search-filter', 'hmr_admin_output_index', HMR_URL . 'res/admin/search-filter-icon.png' );		
		add_submenu_page( 'search-filter', __( 'New Filter', 'hmr' ), __( 'New Filter', 'hmr' ), 'edit_posts', 'search-filter-new', 'hmr_admin_output_new' );
		add_submenu_page( null, __( 'Edit Filter', 'hmr' ), __( 'Edit Filter', 'hmr' ), 'edit_posts', 'search-filter-edit', 'hmr_admin_output_edit' );
	}
	
	function hmr_admin_output_index(){
		require_once( dirname( __FILE__ )  . "/index.php");
	}
	
	function hmr_admin_output_new(){
		require_once( dirname( __FILE__ )  . "/new.php");	
	}
	
	function hmr_admin_output_edit(){
		require_once( dirname( __FILE__ )  . "/edit.php");	
	}
	
	
	
	

	
?>