<?php
/*
Plugin Name: Advanced Custom Fields Pro
Plugin URI: http://www.advancedcustomfields.com/
Description: Fully customise WordPress edit screens with powerful fields. Boasting a professional interface and a powerfull API, it’s a must have for any web developer working with WordPress. Field types include: Wysiwyg, text, textarea, image, file, select, checkbox, page link, post object, date picker, color picker, repeater, flexible content, gallery and more!
Version: 5.0.0 beta
Author: elliot condon
Author URI: http://www.elliotcondon.com/
License: GPL
Copyright: Elliot Condon
*/

// Current with acf v4 as of 27th Feb 1dde13a0bb8af763ef086f2ca0be4553ac955346

if( !class_exists('acf') ):

class acf {
	
	// vars
	var $settings;
		
	
	/*
	*  __construct
	*
	*  A dummy constructor to ensure ACF is only initialized once
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	5.0.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function __construct()
	{
		/* Do nothing here */
	}
	
	
	/*
	*  initialize
	*
	*  The real constructor to initialize ACF
	*
	*  @type	function
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
		
	function initialize() {
		
		// vars
		$this->settings = array(
			
			// basic
			'name'			=> __('Advanced Custom Fields Pro', 'acf'),
			'version'		=> '5.0.0',
						
			// urls
			'basename'		=> plugin_basename( __FILE__ ),
			'path'			=> plugin_dir_path( __FILE__ ),
			'dir'			=> plugin_dir_url( __FILE__ ),
			
			// options
			'show_admin'	=> true,
			'stripslashes'	=> true,
			'load_db'		=> true,
			'json'			=> true,
			'save_json'		=> '',
			'load_json'		=> array()
		);
		
		
		// includes
		$this->include_api();
		$this->include_core();
		$this->include_forms();
		$this->include_admin();
		$this->include_pro();
		
		
		// set text domain
		load_textdomain( 'acf', acf_get_path( 'lang/acf-' . get_locale() . '.mo' ) );
		
		
		// includes (after theme)
		add_action('plugins_loaded',	array($this, 'include_after_plugins'));
		add_action('after_setup_theme',	array($this, 'include_after_theme'));
		
		
		// actions
		add_action('init',				array($this, 'wp_init'), 1);
		add_filter('posts_where',		array($this, 'wp_posts_where'), 0, 2 );
		
		
		//add_filter('posts_join', array($this, 'wp_posts_join'), 0, 2 );
		//add_filter('posts_request', array($this, 'posts_request'), 0, 1 );
	}
	
	
	/*
	*  include_api
	*
	*  This function will include all API files
	*
	*  @type	function
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function include_api() {
		
		include_once('api/api-helpers.php');
		include_once('api/api-value.php');
		include_once('api/api-field.php');
		include_once('api/api-field-group.php');
		include_once('api/api-template.php');
	}
	
	
	/*
	*  include_core
	*
	*  This function will include all core files
	*
	*  @type	function
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function include_core() {
		
		include_once('core/field.php');
		include_once('core/input.php');
		include_once('core/json.php');
		include_once('core/location.php');
		include_once('core/revisions.php');
		
	}
	
	
	/*
	*  include_forms
	*
	*  This function will include all form files
	*
	*  @type	function
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function include_forms() {
		
		include_once('forms/comment.php');
		include_once('forms/post.php');
		include_once('forms/taxonomy.php');
		include_once('forms/user.php');
		include_once('forms/widget.php');
		
	}
	
	
	/*
	*  include_admin
	*
	*  This function will include all admin files
	*
	*  @type	function
	*  @date	4/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function include_admin() {
		
		// bail early if not admin
		if( !is_admin() )
		{
			return;
		}
		
		
		// admin
		include_once('admin/admin.php');
		include_once('admin/field-group.php');
		include_once('admin/field-groups.php');
		include_once('admin/update.php');
		
		
		// settings
		include_once('admin/settings-export.php');
		include_once('admin/settings-addons.php');
			
	}
	
	
	/*
	*  include_after_plugins
	*
	*  description
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function include_after_plugins() {
		
		// wpml
		if( defined('ICL_SITEPRESS_VERSION') )
		{
			include_once('core/wpml.php');
		}
		
	}
	
	
	/*
	*  include_after_theme
	*
	*  This function will include all files AFTER the theme has been setup.
	*  By this point, the user can modify the acf settings via filters
	*
	*  @type	function
	*  @date	26/02/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function include_after_theme() {
		
		// include fields types
		$this->include_field_types();
		
	}
	
	
	/*
	*  include_field_types
	*
	*  This function will include all field files
	*
	*  @type	function
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function include_field_types() {
		
		// validate
		if( acf_get_setting('include_field_types', false) )
		{
			return;
		}
		
		
		// update setting
		acf_update_setting('include_field_types', 1);
		
		
		// basic
		include_once('fields/text.php');
		include_once('fields/textarea.php');
		include_once('fields/number.php');
		include_once('fields/email.php');
		include_once('fields/password.php');
		
		
		// content
		include_once('fields/wysiwyg.php');
		include_once('fields/oembed.php');
		include_once('fields/image.php');
		include_once('fields/file.php');
		
		
		// choice
		include_once('fields/select.php');
		include_once('fields/checkbox.php');
		include_once('fields/radio.php');
		include_once('fields/true_false.php');
		
		
		// relational
		include_once('fields/post_object.php');
		include_once('fields/page_link.php');
		include_once('fields/relationship.php');
		include_once('fields/taxonomy.php');
		include_once('fields/user.php');
		
		
		// jQuery
		include_once('fields/google-map.php');
		include_once('fields/date_picker.php');
		include_once('fields/color_picker.php');
		
		
		// layout
		include_once('fields/message.php');
		include_once('fields/tab.php');
		
		
		// 3rd party
		do_action('acf/include_field_types', 5);
		
	}
	
	
	/*
	*  include_pro
	*
	*  This function will include all pro files
	*
	*  @type	function
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function include_pro() {
		
		if( file_exists( acf_get_path('pro/acf-pro.php') ) )
		{
			include_once( acf_get_path('pro/acf-pro.php') );
		}
	}
	
	
	/*
	*  wp_init
	*
	*  This function will run on the WP init action and setup many things
	*
	*  @type	action (init)
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function wp_init()
	{
		// Create post type 'acf-field-group'
		register_post_type( 'acf-field-group', array(
			'labels'			=> array(
			    'name'					=> __( 'Field&nbsp;Groups', 'acf' ),
				'singular_name'			=> __( 'Field Group', 'acf' ),
			    'add_new'				=> __( 'Add New' , 'acf' ),
			    'add_new_item'			=> __( 'Add New Field Group' , 'acf' ),
			    'edit_item'				=> __( 'Edit Field Group' , 'acf' ),
			    'new_item'				=> __( 'New Field Group' , 'acf' ),
			    'view_item'				=> __( 'View Field Group', 'acf' ),
			    'search_items'			=> __( 'Search Field Groups', 'acf' ),
			    'not_found'				=> __( 'No Field Groups found', 'acf' ),
			    'not_found_in_trash'	=> __( 'No Field Groups found in Trash', 'acf' ), 
			),
			'public'			=> false,
			'show_ui'			=> true,
			'_builtin'			=> false,
			'capability_type'	=> 'page',
			'hierarchical'		=> true,
			'rewrite'			=> false,
			'query_var'			=> 'acf-field-group',
			'supports' 			=> array( 'title' ),
			'show_in_menu'		=> false,
		));
		
		
		// Create post type 'acf-field'
		register_post_type( 'acf-field', array(
			'labels'			=> array(
			    'name'					=> __( 'Fields', 'acf' ),
				'singular_name'			=> __( 'Field', 'acf' ),
			    'add_new'				=> __( 'Add New' , 'acf' ),
			    'add_new_item'			=> __( 'Add New Field' , 'acf' ),
			    'edit_item'				=> __( 'Edit Field' , 'acf' ),
			    'new_item'				=> __( 'New Field' , 'acf' ),
			    'view_item'				=> __( 'View Field', 'acf' ),
			    'search_items'			=> __( 'Search Fields', 'acf' ),
			    'not_found'				=> __( 'No Fields found', 'acf' ),
			    'not_found_in_trash'	=> __( 'No Fields found in Trash', 'acf' ), 
			),
			'public'			=> false,
			'show_ui'			=> false,
			'_builtin'			=> false,
			'capability_type'	=> 'page',
			'hierarchical'		=> true,
			'rewrite'			=> false,
			'query_var'			=> 'acf-field',
			'supports' 			=> array( 'title' ),
			'show_in_menu'		=> false,
		));
		
		
		// min
		//$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$min = '';
		
		
		// register scripts
		$scripts = array();
		$scripts[] = array(
			'handle'	=> 'select2',
			'src'		=> acf_get_dir( "inc/select2/select2{$min}.js" ),
			'deps'		=> array('jquery'),
		);
		$scripts[] = array(
			'handle'	=> 'acf-input',
			'src'		=> acf_get_dir( "js/input{$min}.js" ),
			'deps'		=> array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'underscore', 'select2'),
		);
		$scripts[] = array(
			'handle'	=> 'acf-field-group',
			'src'		=> acf_get_dir( "js/field-group{$min}.js"),
			'deps'		=> array('acf-input'),
		);
		
		foreach( $scripts as $script )
		{
			wp_register_script( $script['handle'], $script['src'], $script['deps'], acf_get_setting('version') );
		}
		
		
		// register styles
		$styles = array();
		$styles[] = array(
			'handle'	=> 'select2',
			'src'		=> acf_get_dir( 'inc/select2/select2.css' ),
			'deps'		=> array(),
		);
		$styles[] = array(
			'handle'	=> 'acf-datepicker',
			'src'		=> acf_get_dir( 'inc/datepicker/jquery-ui-1.10.4.custom.min.css' ),
			'deps'		=> array(),
		);
		$styles[] = array(
			'handle'	=> 'acf-global',
			'src'		=> acf_get_dir( 'css/global.css' ),
			'deps'		=> array(),
		);
		$styles[] = array(
			'handle'	=> 'acf-field-group',
			'src'		=> acf_get_dir( 'css/field-group.css' ),
			'deps'		=> array(),
		);
		$styles[] = array(
			'handle'	=> 'acf-input',
			'src'		=> acf_get_dir( 'css/input.css' ),
			'deps'		=> array('acf-datepicker', 'select2'),
		);
		
		
		foreach( $styles as $style )
		{
			wp_register_style( $style['handle'], $style['src'], $style['deps'], acf_get_setting('version') ); 
		}
		
	}
	
	
	/*
	*  wp_posts_where
	*
	*  This function will add in some new parameters to the WP_Query args allowing fields to be found via key / name
	*
	*  @type	filter
	*  @date	5/12/2013
	*  @since	5.0.0
	*
	*  @param	$where (string)
	*  @param	$wp_query (object)
	*  @return	$where (string)
	*/
	
	function wp_posts_where( $where, $wp_query ) {
		
		// global
		global $wpdb;
		
		
		// acf_field_key
		if( $field_key = $wp_query->get('acf_field_key') )
		{
			$where .= $wpdb->prepare(" AND {$wpdb->posts}.post_name = %s", $field_key );
	    }
	    
	    
	    // acf_field_name
	    if( $field_name = $wp_query->get('acf_field_name') )
		{
			$where .= $wpdb->prepare(" AND {$wpdb->posts}.post_excerpt = %s", $field_name );
			
			// acf_post_id
		    if( $post_id = $wp_query->get('acf_post_id') )
			{
				$where .= $wpdb->prepare(" AND {$wpdb->postmeta}.post_id = %d", $post_id );
			}
	    }
	    
	    
	    // acf_group_key
		if( $group_key = $wp_query->get('acf_group_key') )
		{
			$where .= $wpdb->prepare(" AND {$wpdb->posts}.post_name = %s", $group_key );
	    }
	    
	    
	    return $where;
	    
	}
	
	
	/*
	*  debug SQL
	*
	*  description
	*
	*  @type	function
	*  @date	27/02/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function wp_posts_join( $join, $wp_query ) {
		
		/*
// acf_field_name
		if( $post_id = $wp_query->get('acf_post_id') )
		{
			$join = str_replace('.ID', '.post_name', $join);
			$join = str_replace('.post_id', '.meta_value', $join);
	   }
*/
	   
	   return $join;
	    
	    
	}
	
	
	function posts_request( $thing ) {
		/*

		echo '<pre>';
			print_r($thing );
		echo '</pre>';
		die;
*/
		
		return $thing;
	}
	
}


/*
*  acf
*
*  The main function responsible for returning the one true acf Instance to functions everywhere.
*  Use this function like you would a global variable, except without needing to declare the global.
*
*  Example: <?php $acf = acf(); ?>
*
*  @type	function
*  @date	4/09/13
*  @since	4.3.0
*
*  @param	N/A
*  @return	(object)
*/

function acf()
{
	global $acf;
	
	if( !isset($acf) )
	{
		$acf = new acf();
		
		$acf->initialize();
	}
	
	return $acf;
}


// initialize
acf();


endif; // class_exists check

?>