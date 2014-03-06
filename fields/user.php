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
		$this->category = 'relational';
		$this->defaults = array(
			'role' 			=> '',
			'multiple' 		=> 0,
			'allow_null' 	=> 0,
		);
		
		
		// do not delete!
    	parent::__construct();
    	
    	
    	// extra
		add_action('wp_ajax_acf/fields/user/query',			array($this, 'ajax_query'));
		add_action('wp_ajax_nopriv_acf/fields/user/query',	array($this, 'ajax_query'));
	}

	
	/*
	*  query_posts
	*
	*  description
	*
	*  @type	function
	*  @date	24/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function ajax_query() {

   		// options
   		$options = acf_parse_args( $_GET, array(
			'post_id'					=>	0,
			's'							=>	'',
			'field_key'					=>	'',
			'nonce'						=>	'',
		));
		
		
   		// vars
   		$args = array();
   		$r = array();
   		
		
		// validate
		if( ! wp_verify_nonce($options['nonce'], 'acf_nonce') )
		{
			die();
		}
		
		
		// load field
		$field = acf_get_field( $options['field_key'] );
		
		if( !$field )
		{
			die();
		}
		
		
		// editable roles
		$editable_roles = get_editable_roles();
		
		if( !empty($field['role']) )
		{
			foreach( $editable_roles as $role => $role_info )
			{
				if( !in_array($role, $field['role']) )
				{
					unset( $editable_roles[ $role ] );
				}
			}
		}
		
				
		// search
		if( $options['s'] )
		{
			$args['search'] = $options['s'];
		}
		
		
		// filters
		$args = apply_filters('acf/fields/user/query', $args, $field, $options['post_id']);
		$args = apply_filters('acf/fields/user/query/name=' . $field['name'], $args, $field, $options['post_id'] );
		$args = apply_filters('acf/fields/user/query/key=' . $field['key'], $args, $field, $options['post_id'] );
			
		
		// get users
		$users = get_users( $args );
		
		
		if( !empty($users) && !empty($editable_roles) )
		{
			foreach( $editable_roles as $role => $role_info )
			{
				// vars
				$this_users = array();
				$this_json = array();
				
				
				// loop over users
				$keys = array_keys($users);
				foreach( $keys as $key )
				{
					if( in_array($role, $users[ $key ]->roles) )
					{
						$this_users[] = acf_extract_var( $users, $key );
					}
				}
				
				
				// bail early if no users for this role
				if( empty($this_users) )
				{
					continue;
				}
				
				
				// append to json
				foreach( $this_users as $user )
				{
					// add to json
					$this_json[] = array(
						'id'	=> $user->ID,
						'text'	=> ucfirst( $user->display_name )
					);
	
				}
				
				
				// add as optgroup or results
				if( count($editable_roles) == 1 )
				{
					$r = $this_json;
				}
				else
				{
					$r[] = array(
						'text'		=> translate_user_role( $role_info['name'] ),
						'children'	=> $this_json
					);
				}
				
			}
		}
		
		
		// return JSON
		echo json_encode( $r );
		die();
			
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
	
	function render_field( $field ) {
		
		// Change Field into a select
		$field['type'] = 'select';
		$field['ui'] = 1;
		$field['ajax'] = 1;
		$field['choices'] = array();
		
		
		// populate choices
		if( !empty($field['value']) )
		{
			$users = get_users(array(
				'include' => acf_force_type_array( $field['value'] )
			));
			
			if( !empty($users) )
			{
				foreach( $users as $user )
				{
					$field['choices'][ $user->ID ] = ucfirst( $user->display_name );
				}
			}
		}
		
		
		// render
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
		
		// bail early if no value
		if( empty($value) )
		{
			return $value;
		}
		
		
		// null
		if( $value == 'null' )
		{
			$value = null;
		}
		
		
		// bail early if not formatting for template use
		if( !$template )
		{
			return $value;
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
		
}

new acf_field_user();

?>