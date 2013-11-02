<?php

class acf_field_color_picker extends acf_field
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
		$this->name = 'color_picker';
		$this->label = __("Color Picker",'acf');
		$this->category = __("jQuery",'acf');
		$this->defaults = array(
			'default_value'	=>	'',
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
	
	function render_field( $field ) {
		
		// vars
		$atts = array();
		$e = '';
		
		
		// populate atts
		foreach( array( 'id', 'class', 'name', 'value' ) as $k )
		{
			$atts[ $k ] = $field[ $k ];
		}
		
		
		// render
		$e .= '<div class="acf-color_picker">';
		$e .= '<input type="text" ' . acf_esc_attr($atts) . ' />';
		$e .= '</div>';
		
		
		// return
		echo $e;
	}
	
	
	/*
	*  render_field_options()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field	- an array holding all the field's data
	*/
	
	function render_field_options( $field ) {
		
		// display_format
		acf_render_field_option( $this->name, array(
			'label'			=> __('Default Value','acf'),
			'instructions'	=> '',
			'type'			=> 'text',
			'name'			=> 'default_value',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['default_value'],
			'placeholder'	=> '#FFFFFF'
		));
		
	}
	
}

new acf_field_color_picker();

?>