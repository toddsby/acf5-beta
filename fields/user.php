<?php

class acf_field_user extends acf_field
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
		$this->name = 'user';
		$this->label = __("User",'acf');
		$this->category = __("Relational",'acf');
		$this->defaults = array(
			'role' 			=> '',
			'multiple' 		=> 0,
			'allow_null' 	=> 0,
			'ui'			=> 0,
			'sortable'		=> 0,
		);
		
		
		// do not delete!
    	parent::__construct();
    	
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
	*  @param	$field	- the field array holding all the field options
	*
	*  @return	$value	- the modified value
	*/
	
	function format_value_for_api( $value, $post_id, $field )
	{

		// format value
		if( !$value || $value == 'null' )
		{
			return false;
		}
		
		
		// temp convert to array
		$is_array = true;
		
		if( !is_array($value) )
		{
			$is_array = false;
			$value = array( $value );
		}

		
		foreach( $value as $k => $v )
		{
			$user_data = get_userdata( $v );
			
			//cope with deleted users by @adampope
			if( !is_object($user_data) )
			{
				unset( $value[$k] );
				continue;
			}

			
			$value[ $k ] = array();
			$value[ $k ]['ID'] = $v;
			$value[ $k ]['user_firstname'] = $user_data->user_firstname;
			$value[ $k ]['user_lastname'] = $user_data->user_lastname;
			$value[ $k ]['nickname'] = $user_data->nickname;
			$value[ $k ]['user_nicename'] = $user_data->user_nicename;
			$value[ $k ]['display_name'] = $user_data->display_name;
			$value[ $k ]['user_email'] = $user_data->user_email;
			$value[ $k ]['user_url'] = $user_data->user_url;
			$value[ $k ]['user_registered'] = $user_data->user_registered;
			$value[ $k ]['user_description'] = $user_data->user_description;
			$value[ $k ]['user_avatar'] = get_avatar( $v );
			
		}
		
		
		// de-convert from array
		if( !$is_array && isset($value[0]) )
		{
			$value = $value[0];
		}
		

		// return value
		return $value;
		
	}
	
	
	/*
	*  input_admin_head()
	*
	*  This action is called in the admin_head action on the edit screen where your field is created.
	*  Use this action to add css and javascript to assist your render_field() action.
	*
	*  @info	http://codex.wordpress.org/Plugin_API/Action_Reference/admin_head
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/

	function input_admin_head()
	{
		if( ! function_exists( 'get_editable_roles' ) )
		{ 
			// if using front-end forms then we need to add this core file
			require_once( ABSPATH . '/wp-admin/includes/user.php' ); 
		}
	}
	
	
	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - an array holding all the field's data
	*/
	
	function render_field( $field )
	{
		// vars
		$field['choices'] = array();
		$args = array();
		$editable_roles = get_editable_roles();


		// roles
		if( empty($field['role']) )
		{
			$field['role'] = array();
			
			foreach( $editable_roles as $role => $details )
			{			
				// only translate the output not the value
				$field['role'][] = $role;
			}
		}
				
		
		// choices
		foreach( $field['role'] as $role )
		{
			$label = translate_user_role( $editable_roles[ $role ]['name'] );
			
			// get users			
			$users = get_users(array(
				'role' => $role	
			));
					
			
			if( $users )
			{
				$field['choices'][ $label ] = array();
				
				foreach( $users as $user )
				{
					$field['choices'][ $label ][ $user->ID ] = ucfirst( $user->display_name );
				}
			}
		}
		
		
		// modify field
		$field['type'] = 'select';
		
		acf_render_field( $field );		
		
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
		// role
		$choices = array();
		$editable_roles = get_editable_roles();

		foreach( $editable_roles as $role => $details )
		{			
			// only translate the output not the value
			$choices[ $role ] = translate_user_role( $details['name'] );
		}
		
		acf_render_field_option( $this->name, array(
			'label'			=> __('Filter by role','acf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'role',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['role'],
			'choices'		=> $choices,
			'multiple'		=> 1,
			'ui'			=> 1,
			'allow_null'	=> 1,
			'placeholder'	=> 'All user roles',
		));
		
		
		
		// allow_null
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
		
		
		// multiple
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
		// array?
		if( is_array($value) && isset($value['ID']) )
		{
			$value = $value['ID'];	
		}
		
		// object?
		if( is_object($value) && isset($value->ID) )
		{
			$value = $value->ID;
		}
		
		return $value;
	}
	
		
}

new acf_field_user();

?>