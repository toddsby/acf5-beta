<?php

class acf_field_message extends acf_field
{
	
	/*
	*  __construct
	*
	*  Set name / label needed for actions / filters
	*
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function __construct()
	{
		// vars
		$this->name = 'message';
		$this->label = __("Message",'acf');
		$this->category = __("Layout",'acf');
		$this->defaults = array(
			'message'	=>	'',
		);
		
		
		// do not delete!
    	parent::__construct();
	}
	
	
	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field - an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function render_field( $field )
	{
		echo wpautop( $field['message'] );
	}
	
	
	/*
	*  render_field_options()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
	*
	*  @param	$field	- an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function render_field_options( $field ) {
		
		// default_value
		acf_render_field_option( $this->name, array(
			'label'			=> __('Message','acf'),
			'instructions'	=> __('Please note that all text will first be passed through the wp function ','acf') . 
							   '<a href="http://codex.wordpress.org/Function_Reference/wpautop" target="_blank">wpautop()</a>',
			'type'			=> 'textarea',
			'name'			=> 'message',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['message'],
		));
		
	}
	
}

new acf_field_message();

?>