<?php

class acf_field_email extends acf_field
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
		$this->name = 'email';
		$this->label = __("Email",'acf');
		$this->defaults = array(
			'default_value'	=>	'',
			'placeholder'	=>	'',
			'prepend'		=>	'',
			'append'		=>	''
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
		// vars
		$o = array( 'type', 'id', 'class', 'name', 'value', 'placeholder' );
		$e = '';
		
		
		// prepend
		if( $field['prepend'] !== "" )
		{
			$field['class'] .= ' acf-is-prepended';
			$e .= '<div class="acf-input-prepend">' . $field['prepend'] . '</div>';
		}
		
		
		// append
		if( $field['append'] !== "" )
		{
			$field['class'] .= ' acf-is-appended';
			$e .= '<div class="acf-input-append">' . $field['append'] . '</div>';
		}
		
		
		// populate atts
		$atts = array();
		foreach( $o as $k )
		{
			$atts[ $k ] = $field[ $k ];	
		}
		
		
		// render
		$e .= '<div class="acf-input-wrap">';
		$e .= '<input ' . acf_esc_attr( $atts ) . ' />';
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
	
	function render_field_options( $field )
	{
		// default_value
		acf_render_field_option( $this->name, array(
			'label'			=> __('Default Value','acf'),
			'instructions'	=> __('Appears when creating a new post','acf'),
			'type'			=> 'text',
			'name'			=> 'default_value',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['default_value'],
		));
		
		
		// placeholder
		acf_render_field_option( $this->name, array(
			'label'			=> __('Placeholder Text','acf'),
			'instructions'	=> __('Appears within the input','acf'),
			'type'			=> 'text',
			'name'			=> 'placeholder',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['placeholder'],
		));
		
		
		// prepend
		acf_render_field_option( $this->name, array(
			'label'			=> __('Prepend','acf'),
			'instructions'	=> __('Appears before the input','acf'),
			'type'			=> 'text',
			'name'			=> 'prepend',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['prepend'],
		));
		
		
		// append
		acf_render_field_option( $this->name, array(
			'label'			=> __('Append','acf'),
			'instructions'	=> __('Appears after the input','acf'),
			'type'			=> 'text',
			'name'			=> 'append',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['append'],
		));

	}	
	
}

new acf_field_email();

?>