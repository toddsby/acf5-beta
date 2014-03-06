<?php

class acf_field_tab extends acf_field
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
		$this->name = 'tab';
		$this->label = __("Tab",'acf');
		$this->category = 'layout';
		
		
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
		echo '<div class="acf-tab" data-id="' . $field['key'] . '">' . $field['label'] . '</div>';
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
			'label'			=> __('Instructions','acf'),
			'instructions'	=> '',
			'type'			=> 'message',
			'message'		=> __( 'Use "Tab Fields" to better organize your edit screen by grouping fields together.','acf') . 
							   '<br /><br />' .
							   __( 'All fields following this "tab field" (or until another "tab field" is defined) will be grouped together using this field\'s label as the tab heading.','acf')
							   
		));
		
				
	}
	
}

new acf_field_tab();

?>