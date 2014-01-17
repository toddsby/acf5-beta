<?php 

class acf_settings_addons {
	
	var $view;
	
	
	/*
	*  __construct
	*
	*  Initialize filters, action, variables and includes
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
	
		// actions
		add_action( 'admin_menu', 				array( $this, 'admin_menu' ) );
	}
	
	
	/*
	*  admin_menu
	*
	*  This function will add the ACF menu item to the WP admin
	*
	*  @type	action (admin_menu)
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function admin_menu() {
		
		// add page
		$page = add_submenu_page('edit.php?post_type=acf-field-group', __('Add-ons','acf'), __('Add-ons','acf'), 'manage_options','acf-settings-addons', array($this,'html') );
		
		
		// actions
		add_action('load-' . $page, array($this,'load'));
		
	}
	
	
	/*
	*  load
	*
	*  description
	*
	*  @type	function
	*  @date	7/01/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function load() {
		
		// vars
		$this->view = array(
			'json'		=> array(),
			'active'	=> acf_get_field_types()
		);
		
		
		// load json
        $request = wp_remote_post( 'http://assets.advancedcustomfields.com/add-ons/add-ons.json' );
        
        
        // validate
        if( is_wp_error($request) || wp_remote_retrieve_response_code($request) !== 200)
        {
        	acf_add_admin_notice('<b>Error</b>. Could not load add-ons list', 'error');
        }
        else
        {
	        $this->view['json'] = json_decode( $request['body'], true );
        }
		
	}
	
	
	/*
	*  html
	*
	*  description
	*
	*  @type	function
	*  @date	7/01/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function html() {
		
		// load view
		acf_get_view('settings-addons', $this->view);
		
	}
	
}


// initialize
new acf_settings_addons();

?>