<?php 

if( !class_exists('acf_pro') ):

class acf_pro {
	
	/*
	*  __construct
	*
	*  
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	5.0.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function __construct() {
		
		// includes
		$this->include_api();
		$this->include_fields();
		
		
		// includes (admin only)
		if( is_admin() && acf_get_setting('show_admin') )
		{
			$this->include_admin();
		}
		
		
		// actions
		add_action( 'init',										array( $this, 'wp_init') );
		add_action( 'acf/input/admin_enqueue_scripts',			array( $this, 'input_admin_enqueue_scripts') );
		add_action( 'acf/field_group/admin_enqueue_scripts',	array( $this, 'field_group_admin_enqueue_scripts') );
		add_filter( 'acf/update_field',							array( $this, 'update_field'), 1, 1 );
		
		
		// add-ons
		add_filter('acf/is_add_on_active/slug=acf-pro', '__return_true');
	}
	
	
	/*
	*  include_api
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
	
	function include_api() {
		
		include_once('api-pro.php');
		
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
	
	function include_fields() {
		
		include_once('fields/repeater.php');
		include_once('fields/flexible-content.php');
		include_once('fields/gallery.php');
		
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
	
	function include_admin() {
		
		// connect (update)
		include_once('admin/connect.php');
		
		// settings
		include_once('admin/settings-updates.php');
		
		// options page
		include_once('admin/options-page.php');
		
		
	}
	
	
	/*
	*  wp_init
	*
	*  description
	*
	*  @type	function
	*  @date	4/11/2013
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function wp_init() {
		
		// register acf scripts
		wp_register_script( 'acf-pro-input', acf_get_dir( 'pro/js/pro-input.js' ), false, acf_get_setting('version') );
		wp_register_style( 'acf-pro-input', acf_get_dir( 'pro/css/pro-input.css' ), false, acf_get_setting('version') ); 
		
		
		// register acf scripts
		wp_register_script( 'acf-pro-field-group', acf_get_dir( 'pro/js/pro-field-group.js' ), false, acf_get_setting('version') );
		wp_register_style( 'acf-pro-field-group', acf_get_dir( 'pro/css/pro-field-group.css' ), false, acf_get_setting('version') ); 
		
	}
	
	
	/*
	*  input_admin_enqueue_scripts
	*
	*  description
	*
	*  @type	function
	*  @date	4/11/2013
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function input_admin_enqueue_scripts() {
		
		// scripts
		wp_enqueue_script(array(
			'acf-pro-input',	
		));
	
	
		// styles
		wp_enqueue_style(array(
			'acf-pro-input',	
		));
		
	}
	
	
	/*
	*  field_group_admin_enqueue_scripts
	*
	*  description
	*
	*  @type	function
	*  @date	4/11/2013
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function field_group_admin_enqueue_scripts() {
		
		// scripts
		wp_enqueue_script(array(
			'acf-pro-field-group',	
		));
	
	
		// styles
		wp_enqueue_style(array(
			'acf-pro-field-group',	
		));
		
	}
	
	
	/*
	*  update_field
	*
	*  description
	*
	*  @type	function
	*  @date	4/11/2013
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function update_field( $field ) {
		
		if( $field['parent'] )
		{
			if( strpos($field['parent'], 'field_') === 0 )
			{
				$parent = acf_get_field( $field['parent'] );
				
				$field['parent'] = $parent['ID'];
			}
		}
		
		return $field;
	}
	
	
}


// instantiate
new acf_pro();


// update setting
acf_update_setting( 'pro', true );


// end class
endif;

?>