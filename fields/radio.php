<?php

class acf_field_radio extends acf_field
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
		$this->name = 'radio';
		$this->label = __("Radio Button",'acf');
		$this->category = 'choice';
		$this->defaults = array(
			'layout'			=>	'vertical',
			'choices'			=>	array(),
			'default_value'		=>	'',
			'other_choice'		=>	0,
			'save_other_choice'	=>	0,
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
		$i = 0;
		
		// class
		$field['class'] .= ' acf-radio-list acf-cf';
		$field['class'] .= ($field['layout'] == 'horizontal') ? ' acf-hl' : ' acf-bl';

		
		// e
		$e = '<ul ' . acf_esc_attr(array( 'class' => $field['class'] )) . '>';

		
		// add choices
		if( is_array($field['choices']) )
		{
			foreach( $field['choices'] as $value => $label )
			{
				// vars
				$i++;
				$li_atts = array();
				$input_atts = array(
					'type'	=> 'radio',
					'name'	=> $field['name'],
					'value'	=> $value,
					'id'	=> "{$field['id']}-{$value}"
				);
				
				
				// if there is no value and this is the first of the choices, select this on by default
				if( $field['value'] === false )
				{
					if( $i === 1 )
					{
						$input_atts['checked'] = 'checked';
						$input_atts['data-checked'] = 'checked';
					}
				}
				else
				{
					if( strval($value) === strval($field['value']) )
					{
						$input_atts['checked'] = 'checked';
						$input_atts['data-checked'] = 'checked';
					}
				}
				
				
				if( array_key_exists('checked', $input_atts) )
				{
					$li_atts['class'] = 'active';
				}
				
				// HTML
				$e .= '<li ' . acf_esc_attr($li_atts) . '><label><input ' . acf_esc_attr( $input_atts ) . ' />' . $label . '</label></li>';
			}
		}
		
		
		// other choice
		if( $field['other_choice'] )
		{
			// vars
			$atts1 = array(
				'type'		=> 'radio',
				'name'		=> $field['name'],
				'value'		=> 'other',
				'id'		=> "{$field['id']}-other"
			);
			
			$atts2 = array(
				'type'		=> 'text',
				'name'		=> $field['name'],
				'value'		=> '',
				'disabled'	=> 'disabled'
			);
			
			
			if( $field['value'] !== false )
			{
				if( !isset($field['choices'][ $field['value'] ]) )
				{
					$atts1['checked'] = 'checked';
					$atts1['data-checked'] = 'checked';
					
					$atts2['value'] = $field['value'];
					unset( $atts2['disabled'] );
				}
			}
			
			
			$e .= '<li><label><input ' . acf_esc_attr( $atts1 ) . ' /></label> <input type="text" ' . acf_esc_attr( $atts2 ) . ' /></li>';
		}


		$e .= '</ul>';
		
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
		// implode checkboxes so they work in a textarea
		if( is_array($field['choices']) )
		{		
			foreach( $field['choices'] as $k => $v )
			{
				if( $k === $v )
				{
					$field['choices'][ $k ] = $v;
				}
				else
				{
					$field['choices'][ $k ] = $k . ' : ' . $v;
				}
				
			}
			$field['choices'] = implode("\n", $field['choices']);
		}
		
		
		// choices
		acf_render_field_option( $this->name, array(
			'label'			=> __('Choices','acf'),
			'instructions'	=> __('Enter each choice on a new line.','acf') . '<br /><br />' . __('For more control, you may specify both a value and label like this:','acf'). '<br /><br />' . __('red : Red','acf'),
			'type'			=> 'textarea',
			'name'			=> 'choices',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['choices'],
		));
		
		
		// other_choice
		acf_render_field_option( $this->name, array(
			'label'			=> __('Other','acf'),
			'instructions'	=> '',
			'type'			=> 'true_false',
			'name'			=> 'other_choice',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['other_choice'],
			'message'		=> __("Add 'other' choice to allow for custom values", 'acf')
		));
		
		
		// save_other_choice
		acf_render_field_option( $this->name, array(
			'label'			=> __('Save Other','acf'),
			'instructions'	=> '',
			'type'			=> 'true_false',
			'name'			=> 'save_other_choice',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['save_other_choice'],
			'message'		=> __("Save 'other' values to the field's choices", 'acf')
		));
		
		
		// default_value
		acf_render_field_option( $this->name, array(
			'label'			=> __('Default Value','acf'),
			'instructions'	=> __('Appears when creating a new post','acf'),
			'type'			=> 'text',
			'name'			=> 'default_value',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['default_value'],
		));
		
		
		// layout
		acf_render_field_option( $this->name, array(
			'label'			=> __('Layout','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'layout',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['layout'],
			'layout'		=> 'horizontal', 
			'choices'		=> array(
				'vertical'		=> __("Vertical",'acf'), 
				'horizontal'	=> __("Horizontal",'acf')
			)
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
	*  @param	$post_id - the $post_id of which the value will be saved
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$value - the modified value
	*/
	
	function update_value( $value, $post_id, $field )
	{
		// validate
		if( $field['save_other_choice'] )
		{
			// value isn't in choices yet
			if( !isset($field['choices'][ $value ]) )
			{
				// update $field
				$field['choices'][ $value ] = $value;
				
				
				// can save
				if( isset($field['field_group']) )
				{
					do_action('acf/update_field', $field, $field['field_group']);
				}
				
			}
		}		
		
		return $value;
	}
	
}

new acf_field_radio();

?>