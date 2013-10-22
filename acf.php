<?php
/*
Plugin Name: Advanced Custom Fields
Plugin URI: http://www.advancedcustomfields.com/
Description: Fully customise WordPress edit screens with powerful fields. Boasting a professional interface and a powerfull API, it’s a must have for any web developer working with WordPress. Field types include: Wysiwyg, text, textarea, image, file, select, checkbox, page link, post object, date picker, color picker, repeater, flexible content, gallery and more!
Version: 5.0.0
Author: Elliot Condon
Author URI: http://www.elliotcondon.com/
License: GPL
Copyright: Elliot Condon
*/

if( !class_exists('acf') ):

class acf
{
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
		
	function initialize()
	{
		// vars
		$this->settings = array(
			
			// versions
			'version'		=> '5.0.0',
			'upgrade'		=> '5.0.0',
			
			// urls
			'basename'		=> apply_filters( 'acf/settings/basename', plugin_basename( __FILE__ ) ),
			'path'			=> apply_filters( 'acf/settings/path', plugin_dir_path( __FILE__ ) ),
			'dir'			=> apply_filters( 'acf/settings/dir', plugin_dir_url( __FILE__ ) ),
			
			// options
			'show_admin'	=> apply_filters( 'acf/settings/hide_admin', true ),
			
		);
		
		
		// includes
		$this->include_api();
		$this->include_core();
		$this->include_fields();
		
		
		// actions (admin only)
		if( is_admin() && acf_get_setting('show_admin') )
		{
			$this->include_admin();
		}
		
		
		// set text domain
		load_textdomain( 'acf', acf_get_path( 'lang/acf-' . get_locale() . '.mo' ) );
		
		
		// actions
		add_action('init', array($this, 'wp_init'), 1);
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
	
	function include_api()
	{
		require('api/api-helpers.php');
		require('api/api-value.php');
		require('api/api-field.php');
		require('api/api-field-group.php');
		require('api/api-template.php');
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
	
	function include_core()
	{
		require('core/input.php');
		require('core/location.php');
		
		require('admin/comment.php');
	}
	
	
	/*
	*  include_fields
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
	
	function include_fields()
	{
		// register fields
		include_once('fields/_base.php');
		
		include_once('fields/text.php');
		include_once('fields/textarea.php');
		include_once('fields/number.php');
		include_once('fields/email.php');
		include_once('fields/password.php');
		
		include_once('fields/wysiwyg.php');
		include_once('fields/image.php');
		include_once('fields/file.php');
		
		include_once('fields/select.php');
		include_once('fields/checkbox.php');
		include_once('fields/radio.php');
		include_once('fields/true_false.php');
		
		include_once('fields/page_link.php');
		include_once('fields/post_object.php');
		include_once('fields/relationship.php');
		include_once('fields/taxonomy.php');
		include_once('fields/user.php');
		
		include_once('fields/google-map.php');
		include_once('fields/date_picker/date_picker.php');
		include_once('fields/color_picker.php');
		
		include_once('fields/message.php');
		include_once('fields/tab.php');
	}
	
	
	/*
	*  include_admin
	*
	*  This function will include all admin files
	*
	*  @type	function
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function include_admin()
	{
		require('admin/admin.php');
		require('admin/field-group.php');
		require('admin/field-groups.php');
		
		require('admin/post.php');
		require('admin/user.php');
		require('admin/taxonomy.php');
		
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
		
		
		// register scripts
		$scripts = array();
		$scripts[] = array(
			'handle'	=> 'acf-field-group',
			'src'		=> acf_get_dir( 'js/field-group.js'),
			'deps'		=> array('jquery'),
			'in_footer'	=> false
		);
		$scripts[] = array(
			'handle'	=> 'acf-input',
			'src'		=> acf_get_dir( 'js/input.js' ),
			'deps'		=> array('jquery'),
			'in_footer'	=> false
		);
		$scripts[] = array(
			'handle'	=> 'acf-datepicker',
			'src'		=> acf_get_dir( 'fields/date_picker/jquery.ui.datepicker.js' ),
			'deps'		=> array('jquery', 'acf-input'),
			'in_footer'	=> false
		);
		$scripts[] = array(
			'handle'	=> 'acf-googlemaps',
			'src'		=> 'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places',
			'deps'		=> array('jquery'),
			'in_footer'	=> true
		);
		
		foreach( $scripts as $script )
		{
			wp_register_script( $script['handle'], $script['src'], $script['deps'], acf_get_setting('version'), $script['in_footer'] );
		}
		
		
		// register styles
		$styles = array();
		$styles[] = array(
			'handle'	=> 'acf-global',
			'src'		=> acf_get_dir( 'css/global.css' ),
		);
		$styles[] = array(
			'handle'	=> 'acf-field-group',
			'src'		=> acf_get_dir( 'css/field-group.css' ),
		);
		$styles[] = array(
			'handle'	=> 'acf-input',
			'src'		=> acf_get_dir( 'css/input.css' ),
		);
		$styles[] = array(
			'handle'	=> 'acf-datepicker',
			'src'		=> acf_get_dir( 'fields/date_picker/style.date_picker.css' ),
		);
		
		foreach( $styles as $style )
		{
			wp_register_style( $style['handle'], $style['src'], false, acf_get_setting('version') ); 
		}
		
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
