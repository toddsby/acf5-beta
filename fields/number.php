<?php

class acf_field_number extends acf_field
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
		$this->name = 'number';
		$this->label = __("Number",'acf');
		$this->defaults = array(
			'default_value'	=>	'',
			'min'			=>	'',
			'max'			=>	'',
			'step'			=>	'',
			'placeholder'	=>	'',
			'prepend'		=>	'',
			'append'		=>	'',
			'readonly'		=>	0,
			'disabled'		=>	0,
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
		$o = array( 'type', 'id', 'class', 'min', 'max', 'step', 'name', 'value', 'placeholder' );
		$s = array( 'readonly', 'disabled' );
		$e = '';
		
		
		// step
		if( !$field['step'] )
		{
			$field['step'] = 'any';
		}
		
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
		
		
		// special atts
		foreach( $s as $k )
		{
			if( $field[ $k ] )
			{
				$atts[ $k ] = $k;
			}
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
		
		
		// min
		acf_render_field_option( $this->name, array(
			'label'			=> __('Minimum Value','acf'),
			'instructions'	=> '',
			'type'			=> 'number',
			'name'			=> 'min',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['min'],
		));
		
		
		// max
		acf_render_field_option( $this->name, array(
			'label'			=> __('Maximum Value','acf'),
			'instructions'	=> '',
			'type'			=> 'number',
			'name'			=> 'max',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['max'],
		));
		
		
		// max
		acf_render_field_option( $this->name, array(
			'label'			=> __('Step Size','acf'),
			'instructions'	=> '',
			'type'			=> 'number',
			'name'			=> 'step',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['step'],
		));
		
	}
	
	
	/*
	*  update_value()
	*
	*  This filter is appied to the $value before it is updated in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value - the value which will be saved in the database
	*  @param	$field - the field array holding all the field options
	*  @param	$post_id - the $post_id of which the value will be saved
	*
	*  @return	$value - the modified value
	*/
	
	function update_value( $value, $post_id, $field )
	{
		// remove ','
		$value = str_replace(',', '', $value);
		
		
		// convert to float. This removes any chars
		$value = floatval( $value );
		
		
		// convert back to string. This alows decimals to save
		$value = (string) $value;
		
		
		return $value;
	}
	
	
}

new acf_field_number();

?>