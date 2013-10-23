<?php

class acf_field_select extends acf_field
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
		$this->name = 'select';
		$this->label = __("Select",'acf');
		$this->category = __("Choice",'acf');
		$this->defaults = array(
			'multiple' 		=>	0,
			'allow_null' 	=>	0,
			'choices'		=>	array(),
			'default_value'	=>	'',
			'ui'			=>	1,
			//'search'		=>	0,
			'ajax'			=>	0,
			'sortable'		=>	0
		);
		
		
		// do not delete!
    	parent::__construct();
    	
    	
    	// extra
		//add_filter('acf/update_field/type=select', array($this, 'update_field'), 5, 1);
		add_filter('acf/update_field/type=checkbox', array($this, 'update_field'), 5, 1);
		add_filter('acf/update_field/type=radio', array($this, 'update_field'), 5, 1);
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
		$optgroup = false;
		
		
		// determin if choices are grouped (2 levels of array)
		if( is_array($field['choices']) )
		{
			foreach( $field['choices'] as $k => $v )
			{
				if( is_array($v) )
				{
					$optgroup = true;
					break;
				}
			}
		}
		
		
		// value must be array
		if( !is_array($field['value']) )
		{
			// perhaps this is a default value with new lines in it?
			if( strpos($field['value'], "\n") !== false )
			{
				// found multiple lines, explode it
				$field['value'] = explode("\n", $field['value']);
			}
			else
			{
				$field['value'] = array( $field['value'] );
			}
		}
		
		
		// trim value
		$field['value'] = array_map('trim', $field['value']);
		
		
		// vars
		$atts = array(
			'id'			=> $field['id'],
			'class'			=> $field['class'],
			'name'			=> $field['name'],
			'data-ui'		=> $field['ui'],
			//'data-search'	=> $field['search'],
			'data-ajax'		=> $field['ajax'],
			'data-sortable'	=> $field['sortable'],
		);
		
		
		// multiple
		if( $field['multiple'] )
		{
			// create a hidden field to allow for no selections
			echo '<input type="hidden" name="' . acf_esc_attr($field['name']) . '" />';
			
			
			// update to atts
			$atts['multiple'] = 'multiple';
			$atts['size'] = 5;
			$atts['name'] .= '[]';
		} 
		
		
		// html
		echo '<select ' . acf_esc_attr( $atts ) . '>';	
		
		
		// null
		if( $field['allow_null'] )
		{
			echo '<option value="null">- ' . __("Select",'acf') . ' -</option>';
		}
		
		// loop through values and add them as options
		if( is_array($field['choices']) )
		{
			foreach( $field['choices'] as $key => $value )
			{
				if( $optgroup )
				{
					// this select is grouped with optgroup
					if($key != '') echo '<optgroup label="'.$key.'">';
					
					if( is_array($value) )
					{
						foreach($value as $id => $label)
						{
							$selected = in_array($id, $field['value']) ? 'selected="selected"' : '';
														
							echo '<option value="'.$id.'" '.$selected.'>'.$label.'</option>';
						}
					}
					
					if($key != '') echo '</optgroup>';
				}
				else
				{
					$selected = in_array($key, $field['value']) ? 'selected="selected"' : '';
					echo '<option value="'.$key.'" '.$selected.'>'.$value.'</option>';
				}
			}
		}

		echo '</select>';
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
		
		// implode choices so they work in a textarea
		if( is_array($field['choices']) )
		{		
			foreach( $field['choices'] as $k => $v )
			{
				$field['choices'][ $k ] = $k . ' : ' . $v;
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
		
		
		// default_value
		acf_render_field_option( $this->name, array(
			'label'			=> __('Default Value','acf'),
			'instructions'	=> __('Enter each default value on a new line','acf'),
			'type'			=> 'textarea',
			'name'			=> 'default_value',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['default_value'],
		));
		
		
		// formatting
		acf_render_field_option( $this->name, array(
			'label'			=> __('Allow Null?','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'allow_null',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['allow_null'],
			'choices'		=> array(
				1				=> __("Yes",'acf'),
				0				=> __("No",'acf'),
			),
			'layout'	=>	'horizontal',
		));
		
		
		// formatting
		acf_render_field_option( $this->name, array(
			'label'			=> __('Select multiple values?','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'multiple',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['multiple'],
			'choices'		=> array(
				1				=> __("Yes",'acf'),
				0				=> __("No",'acf'),
			),
			'layout'	=>	'horizontal',
		));
		
		
		// ui
		acf_render_field_option( $this->name, array(
			'label'			=> __('Stylised UI','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'ui',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['ui'],
			'choices'		=> array(
				1				=> __("Yes",'acf'),
				0				=> __("No",'acf'),
			),
			'layout'	=>	'horizontal',
		));
		
		
		// search
		/*
acf_render_field_option( $this->name, array(
			'label'			=> __('Allow Search?','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'search',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['search'],
			'choices'		=> array(
				1				=> __("Yes",'acf'),
				0				=> __("No",'acf'),
			),
			'layout'	=>	'horizontal',
		));
*/
		
		
		// ajax
		acf_render_field_option( $this->name, array(
			'label'			=> __('Use AJAX to lazy load choices?','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'ajax',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['ajax'],
			'choices'		=> array(
				1				=> __("Yes",'acf'),
				0				=> __("No",'acf'),
			),
			'layout'	=>	'horizontal',
		));
		
		
		// sortable
		acf_render_field_option( $this->name, array(
			'label'			=> __('Allow values to be sortable','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'sortable',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['sortable'],
			'choices'		=> array(
				1				=> __("Yes",'acf'),
				0				=> __("No",'acf'),
			),
			'layout'	=>	'horizontal',
		));
				
	}
	
	
	/*
	*  format_value_for_api()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is passed back to the api functions such as the_field
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value	- the value which was loaded from the database
	*  @param	$post_id - the $post_id from which the value was loaded
	*  @param	$field	- the field array holding all the field options
	*
	*  @return	$value	- the modified value
	*/
	
	function format_value_for_api( $value, $post_id, $field )
	{
		if( $value == 'null' )
		{
			$value = false;
		}
		
		
		return $value;
	}
	
	
	/*
	*  update_field()
	*
	*  This filter is appied to the $field before it is saved to the database
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - the field array holding all the field options
	*  @param	$post_id - the field group ID (post_type = acf)
	*
	*  @return	$field - the modified field
	*/

	function update_field( $field )
	{
		
		// check if is array. Normal back end edit posts a textarea, but a user might use update_field from the front end
		if( is_array( $field['choices'] ))
		{
		    return $field;
		}

		
		// vars
		$new_choices = array();
		
		
		// explode choices from each line
		if( $field['choices'] )
		{
			// stripslashes ("")
			$field['choices'] = stripslashes_deep($field['choices']);
		
			if(strpos($field['choices'], "\n") !== false)
			{
				// found multiple lines, explode it
				$field['choices'] = explode("\n", $field['choices']);
			}
			else
			{
				// no multiple lines! 
				$field['choices'] = array($field['choices']);
			}
			
			
			// key => value
			foreach($field['choices'] as $choice)
			{
				if(strpos($choice, ' : ') !== false)
				{
					$choice = explode(' : ', $choice);
					$new_choices[ trim($choice[0]) ] = trim($choice[1]);
				}
				else
				{
					$new_choices[ trim($choice) ] = trim($choice);
				}
			}
		}
		
		
		// update choices
		$field['choices'] = $new_choices;
		
		
		return $field;
	}
	
}

new acf_field_select();

?>
