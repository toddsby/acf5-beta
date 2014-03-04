<?php

class acf_field_true_false extends acf_field
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
		$this->name = 'true_false';
		$this->label = __("True / False",'acf');
		$this->category = __("Choice",'acf');
		$this->defaults = array(
			'default_value'	=>	0,
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
		// vars
		$atts = array(
			'type'		=> 'checkbox',
			'id'		=> "{$field['id']}-1",
			'name'		=> $field['name'],
			'value'		=> '1',
		);
		
		
		// checked
		if( !empty($field['value']) )
		{
			$atts['checked'] = 'checked';
		}
		
		
		// html
		echo '<ul class="acf-checkbox-list acf-bl ' . acf_esc_attr($field['class']) . '">';
			echo '<input type="hidden" name="' . acf_esc_attr($field['name']) . '" value="0" />';
			echo '<li><label><input ' . acf_esc_attr($atts) . '/>' . $field['message'] . '</label></li>';
		echo '</ul>';
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
		// message
		acf_render_field_option( $this->name, array(
			'label'			=> __('Message','acf'),
			'instructions'	=> __('eg. Show extra content','acf'),
			'type'			=> 'text',
			'name'			=> 'message',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['message'],
		));
		
		
		// default_value
		acf_render_field_option( $this->name, array(
			'label'			=> __('Default Value','acf'),
			'instructions'	=> '',
			'type'			=> 'true_false',
			'name'			=> 'default_value',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['default_value'],
		));
		
	}
	
	
	/*
	*  format_value()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is passed to the render_field action
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value which was loaded from the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @param	$template (boolean) true if value requires formatting for front end template function
	*
	*  @return	$value (mixed) the modified value
	*/
	
	function format_value( $value, $post_id, $field, $template ) {
		
		return empty($value) ? false : true;
	}
	
}

new acf_field_true_false();

?>