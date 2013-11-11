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
		if( class_exists('acf_field') )
		{
			include_once('repeater/repeater.php');
		}
		
		
		// actions
		add_action( 'init',										array($this, 'wp_init') );
		add_action( 'acf/input/admin_enqueue_scripts',			array($this, 'input_admin_enqueue_scripts') );
		add_action( 'acf/field_group/admin_enqueue_scripts',	array($this, 'field_group_admin_enqueue_scripts') );
		add_filter( 'acf/update_field',							array( $this, 'update_field'), 2, 2 );
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
				echo '<pre>';
					print_r($field['parent']);
				echo '</pre>';
				die;
				$parent = acf_get_field( $field['parent'] );
				
				echo '<pre>';
					print_r($parent);
				echo '</pre>';
				die;
				
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

endif;




?>