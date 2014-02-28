<?php 

/*
*  get_field_reference()
*
*  This function will find the $field_key that is related to the $field_name.
*  This is know as the field value reference
*
*  @type	function
*  @since	3.6
*  @date	29/01/13
*
*  @param	$field_name (mixed) the name of the field. eg 'sub_heading'
*  @param	$post_id (int) the post_id of which the value is saved against
*  @return	$reference (string)	a string containing the field_key
*/

function get_field_reference( $field_name, $post_id ) {
	
	// vars
	$reference = false;
	
	
	// try cache
	$found = false;
	$cache = wp_cache_get( "field_reference/post_id={$post_id}/name={$field_name}", 'acf', false, $found );
	
	if( $found )
	{
		return $cache;
	}
			
	
	// load value depending on the $type
	if( is_numeric($post_id) )
	{
		$v = get_post_meta( $post_id, "_{$field_name}", false );
		
		// value is an array
		if( isset($v[0]) )
		{
		 	$reference = $v[0];
	 	}

	}
	elseif( strpos($post_id, 'user_') !== false )
	{
		$user_id = str_replace('user_', '', $post_id);
		$user_id = intval( $user_id );
		
		$v = get_user_meta( $user_id, "_{$field_name}", false );
		
		// value is an array
		if( isset($v[0]) )
		{
		 	$reference = $v[0];
	 	}
	 	
	}
	elseif( strpos($post_id, 'comment_') !== false )
	{
		$comment_id = str_replace('comment_', '', $post_id);
		$comment_id = intval( $comment_id );
		
		$v = get_comment_meta( $comment_id, "_{$field_name}", false );
		
		// value is an array
		if( isset($v[0]) )
		{
		 	$reference = $v[0];
	 	}
	 	
	}
	else
	{
		$v = get_option( "_{$post_id}_{$field_name}", false );
	
		if( ! is_null($v) )
		{
			$reference = $v;
	 	}
	}
	
	
	//update cache
	wp_cache_set( "field_reference/post_id={$post_id}/name={$field_name}", $reference, 'acf' );
	
	
	// return
	return $reference;
	
}


/*
*  acf_get_valid_post_id
*
*  This function will return a valid post_id based on the current screen / parameter
*
*  @type	function
*  @date	8/12/2013
*  @since	5.0.0
*
*  @param	$post_id (mixed) can be a post ID, comment / user / widget / options, or even an object 
*  @return	$post_id (int)
*/

function acf_get_valid_post_id( $post_id = 0 ) {
	
	// set post_id to global
	if( !$post_id )
	{
		$post_id = get_the_ID();
		$post_id = intval( $post_id );
	}
	
	
	// allow for option == options
	if( $post_id == "option" )
	{
		$post_id = "options";
	}
	
	
	// object
	if( is_object($post_id) )
	{
		if( isset($post_id->roles, $post_id->ID) )
		{
			$post_id = 'user_' . $post_id->ID;
		}
		elseif( isset($post_id->taxonomy, $post_id->term_id) )
		{
			$post_id = $post_id->taxonomy . '_' . $post_id->term_id;
		}
		elseif( isset($post_id->ID) )
		{
			$post_id = $post_id->ID;
		}
	}		
		
	/*
	*  Override for preview
	*  
	*  If the $_GET['preview_id'] is set, then the user wants to see the preview data.
	*  There is also the case of previewing a page with post_id = 1, but using get_field
	*  to load data from another post_id.
	*  In this case, we need to make sure that the autosave revision is actually related
	*  to the $post_id variable. If they match, then the autosave data will be used, otherwise, 
	*  the user wants to load data from a completely different post_id
	*/
	
	if( isset($_GET['preview_id']) )
	{
		$autosave = wp_get_post_autosave( $_GET['preview_id'] );
		if( $autosave->post_parent == $post_id )
		{
			$post_id = intval( $autosave->ID );
		}
	}
	
	
	// return
	return $post_id;
	
}


/*
*  acf_is_field_key
*
*  This function will return true or false for the given $field_key parameter
*
*  @type	function
*  @date	6/12/2013
*  @since	5.0.0
*
*  @param	$field_key (string)
*  @return	(boolean)
*/

function acf_is_field_key( $field_key = '' ) {
		
	if( substr($field_key, 0, 6) === 'field_' )
	{
		return true;
	}
	
	return false;
	
}


/*
*  the_field()
*
*  This function is the same as echo get_field().
*
*  @type	function
*  @since	1.0.3
*  @date	29/01/13
*
*  @param	$selector (string) the field name or key
*  @param	$post_id (mixed) the post_id of which the value is saved against
*  @return	n/a
*/

function the_field( $selector, $post_id = false, $format_value = true ) {
	
	$value = get_field($selector, $post_id, $format_value);
	
	if( is_array($value) )
	{
		$value = @implode( ', ', $value );
	}
	
	echo $value;
}


/*
*  get_field()
*
*  This function will return a custom field value for a specific field name/key + post_id.
*  There is a 3rd parameter to turn on/off formating. This means that an image field will not use 
*  its 'return option' to format the value but return only what was saved in the database
*
*  @type	function
*  @since	3.6
*  @date	29/01/13
*
*  @param	$selector (string) the field name or key
*  @param	$post_id (mixed) the post_id of which the value is saved against
*  @param	$format_value (boolean) whether or not to format the value as described above
*  @return	(mixed)
*/
 
function get_field( $selector, $post_id = false, $format_value = true ) {
	
	// vars
	$load_value = true;
	
	
	// get field
	$field = get_field_object( $selector, $post_id, $format_value, $load_value);
	
	
	// return value
	if( is_array($field) )
	{
		return $field['value'];
	}
	
	
	// return
	return false;
	 
}


/*
*  get_field_object()
*
*  This function will return an array containing all the field data for a given field_name
*
*  @type	function
*  @since	3.6
*  @date	3/02/13
*
*  @param	$selector (string) the field name or key
*  @param	$post_id (mixed) the post_id of which the value is saved against
*  @param	$format_value (boolean) whether or not to format the field value
*  @param	$load_value (boolean) whether or not to load the field value
*  @return	$field (array)
*/

function get_field_object( $selector, $post_id = false, $format_value = true, $load_value = true ) {
	
	// compatibilty
	if( is_array($format_value) )
	{
		$format_value = acf_parse_args($format_value, array(
			'format_value'	=>	true,
			'load_value'	=>	true,
		));
		
		extract( $format_value );
	}
	
	
	// vars
	$override_name = false;
	
	
	// filter post_id
	$post_id = acf_get_valid_post_id( $post_id );
	
	
	// load field reference if not a field_key
	if( !acf_is_field_key($selector) )
	{
		$override_name = $selector;
		$selector = get_field_reference( $selector, $post_id );
	}
	
	
	// get field key
	$field = acf_get_field( $selector );
	
	
	// create blank field
	if( !$field )
	{
		$field = acf_get_valid_field(array(
			'name'	=> $selector,
			'key'	=> '',
			'type'	=> '',
		));
		
		$format_value = false;
	}
	
	
	// override name?
	// This allows the $selector to be a sub field (images_0_image)
	if( $override_name )
	{
		$field['name'] = $override_name;	
	}
	
	
	// load value
	if( $load_value )
	{
		$field['value'] = acf_get_value( $post_id, $field, $format_value, $format_value );
	
	}
	
	
	// return
	return $field;
	
}


/*
*  get_fields()
*
*  This function will return an array containing all the custom field values for a specific post_id.
*  The function is not very elegant and wastes a lot of PHP memory / SQL queries if you are not using all the values.
*
*  @type	function
*  @since	3.6
*  @date	29/01/13
*
*  @param	$post_id (mixed) the post_id of which the value is saved against
*  @param	$format_value (boolean) whether or not to format the field value
*  @return	(array)	associative array where field name => field value
*/

function get_fields( $post_id = false, $format_value = true ) {
	
	// vars
	$fields = get_field_objects( $post_id, $format_value );
	$return = array();
	
	
	// populate
	if( is_array($fields) )
	{
		foreach( $fields as $k => $field )
		{
			$return[ $k ] = $field['value'];
		}
	}
	
	
	// return
	return $return;	
}


/*
*  get_field_objects()
*
*  This function will return an array containing all the custom field objects for a specific post_id.
*  The function is not very elegant and wastes a lot of PHP memory / SQL queries if you are not using all the fields / values.
*
*  @type	function
*  @since	3.6
*  @date	29/01/13
*
*  @param	$post_id (mixed) the post_id of which the value is saved against
*  @param	$format_value (boolean) whether or not to format the field value
*  @param	$load_value (boolean) whether or not to load the field value
*  @return	(array)	associative array where field name => field
*/

function get_field_objects( $post_id = false, $format_value = true, $load_value = true ) {
	
	// global
	global $wpdb;
	
	
	// filter post_id
	$post_id = acf_get_valid_post_id( $post_id );


	// vars
	$value = array();
	
	
	// get field_names
	if( is_numeric($post_id) )
	{
		$keys = $wpdb->get_col($wpdb->prepare(
			"SELECT meta_value FROM $wpdb->postmeta WHERE post_id = %d and meta_key LIKE %s AND meta_value LIKE %s",
			$post_id,
			'_%',
			'field_%'
		));
	}
	elseif( strpos($post_id, 'user_') !== false )
	{
		$user_id = str_replace('user_', '', $post_id);
		
		$keys = $wpdb->get_col($wpdb->prepare(
			"SELECT meta_value FROM $wpdb->usermeta WHERE user_id = %d and meta_key LIKE %s AND meta_value LIKE %s",
			$user_id,
			'_%',
			'field_%'
		));
	}
	elseif( strpos($post_id, 'comment_') !== false )
	{
		$comment_id = str_replace('comment_', '', $post_id);
		
		$keys = $wpdb->get_col($wpdb->prepare(
			"SELECT meta_value FROM $wpdb->commentmeta WHERE user_id = %d and meta_key LIKE %s AND meta_value LIKE %s",
			$comment_id,
			'_%',
			'field_%'
		));
	}
	else
	{
		$keys = $wpdb->get_col($wpdb->prepare(
			"SELECT option_value FROM $wpdb->options WHERE option_name LIKE %s",
			'_' . $post_id . '_%' 
		));
	}


	if( is_array($keys) )
	{
		foreach( $keys as $key )
		{
			$field = get_field_object( $key, $post_id, $format_value, $load_value );
			
			if( !is_array($field) )
			{
				continue;
			}
			
			$value[ $field['name'] ] = $field;
		}
 	}
 	
 	
	// no value
	if( empty($value) )
	{
		return false;
	}
	
	
	// return
	return $value;
}


/*
*  have_rows
*
*  This function will instantiate a global variable containing the rows of a repeater or flexible content field,
*  afterwhich, it will determin if another row exists to loop through
*
*  @type	function
*  @date	2/09/13
*  @since	4.3.0
*
*  @param	$field_name (string) the field name
*  @param	$post_id (mixed) the post_id of which the value is saved against
*  @return	(boolean)
*/

function have_rows( $field_name, $post_id = false ) {
	
	// vars
	$depth = 0;
	$row = array();
	$new_parent_loop = false;
	$new_child_loop = false;
	
	
	// reference
	$_post_id = $post_id;
	
	
	// filter post_id
	$post_id = acf_get_valid_post_id( $post_id );
	
	
	// empty?
	if( empty($GLOBALS['acf_field']) )
	{
		// reset
		reset_rows( true );
		
		
		// create a new loop
		$new_parent_loop = true;
	}
	else
	{
		// vars
		$row = end( $GLOBALS['acf_field'] );
		$prev = prev( $GLOBALS['acf_field'] );
		
		
		// If post_id has changed, this is most likely an archive loop
		if( $post_id != $row['post_id'] )
		{
			if( $prev && $prev['post_id'] == $post_id )
			{
				// case: Change in $post_id was due to a nested loop ending
				// action: move up one level through the loops
				reset_rows();
			}
			elseif( empty($_post_id) && isset($row['value'][ $row['i'] ][ $field_name ]) )
			{
				// case: Change in $post_id was due to this being a nested loop and not specifying the $post_id
				// action: move down one level into a new loop
				$new_child_loop = true;
			}
			else
			{
				// case: Chang in $post_id is the most obvious, used in an WP_Query loop with multiple $post objects
				// action: leave this current loop alone and create a new parent loop
				$new_parent_loop = true;
			}
		}
		elseif( $field_name != $row['name'] )
		{
			if( $prev && $prev['name'] == $field_name && $prev['post_id'] == $post_id )
			{
				// case: Change in $field_name was due to a nested loop ending
				// action: move up one level through the loops
				reset_rows();
			}
			elseif( isset($row['value'][ $row['i'] ][ $field_name ]) )
			{
				// case: Change in $field_name was due to this being a nested loop
				// action: move down one level into a new loop
				$new_child_loop = true;
				
			}
			else
			{
				// case: Chang in $field_name is the most obvious, this is a new loop for a different field within the $post
				// action: leave this current loop alone and create a new parent loop
				$new_parent_loop = true;
			}
			
			
		}
	}
	
	
	if( $new_parent_loop )
	{
		// vars
		$f = get_field_object( $field_name, $post_id );
		$v = $f['value'];
		unset( $f['value'] );
		
		
		// add row
		$GLOBALS['acf_field'][] = array(
			'name'		=> $field_name,
			'value'		=> $v,
			'field'		=> $f,
			'i'			=> -1,
			'post_id'	=> $post_id,
		);
		
	}
	elseif( $new_child_loop )
	{
		// vars
		$f = get_field_object( $field_name, $post_id );
		$v = $row['value'][ $row['i'] ][ $field_name ];
		
		$GLOBALS['acf_field'][] = array(
			'name'		=> $field_name,
			'value'		=> $v,
			'field'		=> $f,
			'i'			=> -1,
			'post_id'	=> $post_id,
		);

	}	
	
	
	// update vars
	$row = end( $GLOBALS['acf_field'] );
	
	
	if( is_array($row['value']) && array_key_exists( $row['i']+1, $row['value'] ) )
	{
		// next row exists
		return true;
	}
	
	
	// no next row!
	reset_rows();
	
	
	// return
	return false;
  
}


/*
*  the_row
*
*  This function will progress the global repeater or flexible content value 1 row
*
*  @type	function
*  @date	2/09/13
*  @since	4.3.0
*
*  @param	N/A
*  @return	(array) the current row data
*/

function the_row() {
	
	// vars
	$depth = count( $GLOBALS['acf_field'] ) - 1;

	
	// increase row
	$GLOBALS['acf_field'][ $depth ]['i']++;
	
	
	// get row
	$value = $GLOBALS['acf_field'][ $depth ]['value'];
	$i = $GLOBALS['acf_field'][ $depth ]['i'];

	
	// return
	return $value[ $i ];
}


/*
*  reset_rows
*
*  This function will find the current loop and unset it from the global array.
*  To bo used when loop finishes or a break is used
*
*  @type	function
*  @date	26/10/13
*  @since	5.0.0
*
*  @param	$hard_reset (boolean) completely wipe the global variable, or just unset the active row
*  @return	(boolean)
*/

function reset_rows( $hard_reset = false ) {
	
	// completely destroy?
	if( $hard_reset )
	{
		$GLOBALS['acf_field'] = array();
	}
	else
	{
		// vars
		$depth = count( $GLOBALS['acf_field'] ) - 1;
		
		
		// remove
		unset( $GLOBALS['acf_field'][$depth] );
		
		
		// refresh index
		$GLOBALS['acf_field'] = array_values($GLOBALS['acf_field']);
	}
	
	
	// return
	return true;
	
	
}


/*
*  has_sub_field()
*
*  This function is used inside a while loop to return either true or false (loop again or stop).
*  When using a repeater or flexible content field, it will loop through the rows until 
*  there are none left or a break is detected
*
*  @type	function
*  @since	1.0.3
*  @date	29/01/13
*
*  @param	$field_name (string) the field name
*  @param	$post_id (mixed) the post_id of which the value is saved against
*  @return	(boolean)
*/

function has_sub_field( $field_name, $post_id = false ) {
	
	// vars
	$r = have_rows( $field_name, $post_id );
	
	
	// if has rows, progress through 1 row for the while loop to work
	if( $r )
	{
		the_row();
	}
	
	
	// return
	return $r;
	
}

function has_sub_fields( $field_name, $post_id = false ) {
	
	return has_sub_field( $field_name, $post_id );
	
}


/*
*  get_sub_field()
*
*  This function is used inside a 'has_sub_field' while loop to return a sub field value
*
*  @type	function
*  @since	1.0.3
*  @date	29/01/13
*
*  @param	$field_name (string) the field name
*  @return	(mixed)
*/

function get_sub_field( $field_name ) {
	
	// no field?
	if( empty($GLOBALS['acf_field']) )
	{
		return false;
	}
	
	
	// vars
	$row = end( $GLOBALS['acf_field'] );
	
	
	// return value
	if( isset($row['value'][ $row['i'] ][ $field_name ]) )
	{
		return $row['value'][ $row['i'] ][ $field_name ];
	}
	
	
	// return false
	return false;
}


/*
*  the_sub_field()
*
*  This function is the same as echo get_sub_field
*
*  @type	function
*  @since	1.0.3
*  @date	29/01/13
*
*  @param	$field_name (string) the field name
*  @return	n/a
*/

function the_sub_field( $field_name ) {
	
	$value = get_sub_field( $field_name );
	
	if(is_array($value))
	{
		$value = implode(', ',$value);
	}
	
	echo $value;
}


/*
*  get_sub_field_object()
*
*  This function is used inside a 'has_sub_field' while loop to return a sub field object
*
*  @type	function
*  @since	3.5.8.1
*  @date	29/01/13
*
*  @param	$child_name (string) the field name
*  @return	(array)	
*/

function get_sub_field_object( $child_name ) {
	
	// no field?
	if( empty($GLOBALS['acf_field']) )
	{
		return false;
	}


	// vars
	$depth = count( $GLOBALS['acf_field'] ) - 1;
	$parent = $GLOBALS['acf_field'][ $depth ]['field'];


	// return
	return acf_get_child_field_from_parent_field( $child_name, $parent );
	
}


/*
*  acf_get_sub_field_from_parent_field()
*
*  This function is used by the get_sub_field_object to find a sub field within a parent field
*
*  @type	function
*  @since	3.5.8.1
*  @date	29/01/13
*
*  @param	$child_name (string) the field name
*  @param	$parent (array) the parent field
*  @return	(array)	
*/

function acf_get_child_field_from_parent_field( $child_name, $parent ) {
	
	// vars
	$return = false;
	
	
	// find child
	if( isset($parent['sub_fields']) && is_array($parent['sub_fields']) )
	{
		foreach( $parent['sub_fields'] as $child )
		{
			if( $child['name'] == $child_name || $child['key'] == $child_name )
			{
				$return = $child;
				break;
			}
			
			// perhaps child has grand children?
			$grand_child = acf_get_child_field_from_parent_field( $child_name, $child );
			if( $grand_child )
			{
				$return = $grand_child;
				break;
			}
		}
	}
	elseif( isset($parent['layouts']) && is_array($parent['layouts']) )
	{
		foreach( $parent['layouts'] as $layout )
		{
			$child = acf_get_child_field_from_parent_field( $child_name, $layout );
			if( $child )
			{
				$return = $child;
				break;
			}
		}
	}
	

	// return
	return $return;
	
}


/*
*  get_row_layout()
*
*  This function will return a string representation of the current row layout within a 'have_rows' loop
*
*  @type	function
*  @since	3.0.6
*  @date	29/01/13
*
*  @param	n/a
*  @return	(string)
*/

function get_row_layout() {
	
	return get_sub_field('acf_fc_layout');
	
}


/*
*  acf_shortcode()
*
*  This function is used to add basic shortcode support for the ACF plugin
*  eg. [acf field="heading" post_id="123" format_value="1"]
*
*  @type	function
*  @since	1.1.1
*  @date	29/01/13
*
*  @param	$field (string) the field name or key
*  @param	$post_id (mixed) the post_id of which the value is saved against
*  @param	$format_value (boolean) whether or not to format the field value
*  @return	(string)
*/

function acf_shortcode( $atts )
{
	// extract attributs
	extract( shortcode_atts( array(
		'field'			=> '',
		'post_id'		=> false,
		'format_value'	=> true
	), $atts ) );
	
	
	// get value and return it
	$value = get_field( $field, $post_id, $format_value );
	
	
	if( is_array($value) )
	{
		$value = @implode( ', ', $value );
	}
	
	
	return $value;
}
add_shortcode( 'acf', 'acf_shortcode' );


/*
*  acf_form_head()
*
*  This function is placed at the very top of a template (before any HTML is rendered) and saves the $_POST data sent by acf_form.
*
*  @type	function
*  @since	1.1.4
*  @date	29/01/13
*
*  @param	n/a
*  @return	n/a
*/

function acf_form_head() {
	
	// verify nonce
	if( acf_verify_nonce('input') )
	{
		// validate data
	    if( acf_validate_save_post(true) )
		{
			// $post_id to save against
			$post_id = $_POST['post_id'];
			
			
			// allow for custom save
			$post_id = apply_filters('acf/pre_save_post', $post_id);
			
			
			// save
			acf_save_post( $post_id );
			
			
			// redirect
			if( !empty($_POST['return']) )
			{
				wp_redirect( $_POST['return'] );
				exit;
			}
		}
	}
	
	
	// load acf scripts
	acf_enqueue_scripts();
}


/*
*  acf_form()
*
*  This function is used to create an ACF form.
*
*  @type	function
*  @since	1.1.4
*  @date	29/01/13
*
*  @param	array		$options: an array containing many options to customize the form
*			string		+ post_id: post id to get field groups from and save data to. Default is false
*			array		+ field_groups: an array containing field group ID's. If this option is set, 
*						  the post_id will not be used to dynamically find the field groups
*			boolean		+ form: display the form tag or not. Defaults to true
*			array		+ form_attributes: an array containg attributes which will be added into the form tag
*			string		+ return: the return URL
*			string		+ html_before_fields: html inside form before fields
*			string		+ html_after_fields: html inside form after fields
*			string		+ submit_value: value of submit button
*			string		+ updated_message: default updated message. Can be false					 
*
*  @return	N/A
*/

function acf_form( $args = array() ) {
	
	// defaults
	$args = acf_parse_args( $args, array(
		'id'					=> 'acf-form-1',
		'post_id'				=> false,
		'post_type'				=> false,
		'field_groups'			=> false,
		'fields'				=> false,
		'form'					=> true,
		'form_attributes'		=> array(
			'id'					=> 'post',
			'class'					=> '',
			'action'				=> '',
			'method'				=> 'post',
		),
		'return'				=> add_query_arg( 'updated', 'true', get_permalink() ),
		'html_before_fields'	=> '',
		'html_after_fields'		=> '',
		'submit_value'			=> __("Update", 'acf'),
		'updated_message'		=> __("Post updated", 'acf'),
		'label_placement'		=> 'top',
		'instruction_placement'	=> 'label' 
	));
	
	
	// filter post_id
	$args['post_id'] = acf_get_valid_post_id( $args['post_id'] );
	
	
	// attributes
	$args['form_attributes']['class'] .= " acf-form {$args['id']}";
	
	
	// vars
	$field_groups = array();
	$fields = array();
	
	
	// specific fields
	if( !empty($args['fields']) )
	{
		foreach( $args['fields'] as $selector )
		{
			$fields[] = acf_get_field( $selector );
		}
	}
	elseif( !empty($args['field_groups']) )
	{
		foreach( $args['field_groups'] as $selector )
		{
			$field_groups[] = acf_get_field_group( $selector );
		}
	}
	elseif( $args['post_id'] == 'new' )
	{
		$field_groups = acf_get_field_groups(array(
			'post_type' => $args['post_type']
		));
	}
	else
	{
		$field_groups = acf_get_field_groups(array(
			'post_id' => $args['post_id']
		));
	}
	
	
	//load fields based on field groups
	if( !empty($field_groups) )
	{
		foreach( $field_groups as $field_group )
		{
			$fields = array_merge($fields, acf_get_fields( $field_group ));
		}
	}
	
	
	// updated message
	if( !empty($_GET['updated']) && $args['updated_message'] )
	{
		echo '<div id="message" class="updated"><p>' . $args['updated_message'] . '</p></div>';
	}
	
	
	// display form
	if( $options['form'] ): ?>
	<form <?php acf_esc_attr_e( $args['form_attributes']); ?>>
	<?php endif; 
		
		
	// render post data
	acf_form_data(array( 
		'post_id'	=> $args['post_id'], 
		'nonce'		=> 'post' 
	));
	
	?>
	<div class="acf-hidden">
		<input type="hidden" name="return" value="<?php echo $args['return']; ?>" />
	</div>
	
	<div id="poststuff">
	<?php
	
	// html before fields
	echo $args['html_before_fields'];
	
	
	// render
	if( $args['label_placement'] == 'left' )
	{
		?>
		<table class="acf-table">
			<tbody>
				<?php acf_render_fields( $args['post_id'], $fields, 'tr', $args['instruction_placement'] ); ?>
			</tbody>
		</table>
		<?php
	}
	else
	{
		acf_render_fields( $args['post_id'], $fields, 'div', $args['instruction_placement'] );
	}
	
	
	// html after fields
	echo $args['html_after_fields'];
	
	?>
	
	<?php if( $args['form'] ): ?>
	<!-- Submit -->
	<div id="acf-form-submit">
		<input class="acf-button blue" type="submit" value="<?php echo $options['submit_value']; ?>" />
	</div>
	<!-- / Submit -->
	<?php endif; ?>
	
	</div><!-- <div id="poststuff"> -->
	
	<?php if( $options['form'] ): ?>
	</form>
	<?php endif;
}


/*
*  update_field()
*
*  This function will update a value in the database
*
*  @type	function
*  @since	3.1.9
*  @date	29/01/13
*
*  @param	$selector (string) the field name or key
*  @param	$value (mixed) the value to save in the database
*  @param	$post_id (mixed) the post_id of which the value is saved against
*  @return	(boolean)
*/

function update_field( $selector, $value, $post_id = false ) {
	
	// filter post_id
	$post_id = acf_get_valid_post_id( $post_id );
	
	
	// get field
	$field = get_field_object( $selector, $post_id, false, false);

	
	// sub fields? They need formatted data
	if( $field['type'] == 'repeater' )
	{
		$value = acf_convert_field_names_to_keys( $value, $field );
	}
	elseif( $field['type'] == 'flexible_content' )
	{
		if( $field['layouts'] )
		{
			foreach( $field['layouts'] as $layout )
			{
				$value = acf_convert_field_names_to_keys( $value, $layout );
			}
		}
	}
	
	
	// save
	return acf_update_value( $value, $post_id, $field );
	
}


/*
*  delete_field()
*
*  This function will remove a value from the database
*
*  @type	function
*  @since	3.1.9
*  @date	29/01/13
*
*  @param	$selector (string) the field name or key
*  @param	$post_id (mixed) the post_id of which the value is saved against
*  @return	(boolean)
*/

function delete_field( $selector, $post_id = false ) {
	
	// filter post_id
	$post_id = acf_get_valid_post_id( $post_id );
	
	
	// get field
	$field = get_field_object( $selector, $post_id, false, false);
	
	
	// delete
	return acf_delete_value( $post_id, $field['name'] );
	
}


/*
*  create_field()
*
*  This function will creat the HTML for a field
*
*  @type	function
*  @since	4.0.0
*  @date	17/03/13
*
*  @param	array	$field - an array containing all the field attributes
*
*  @return	N/A
*/

function create_field( $field ) {

	acf_render_field( $field );
}

function render_field( $field ) {

	acf_render_field( $field );
}


/*
*  acf_convert_field_names_to_keys()
*
*  Helper for the update_field function
*
*  @type	function
*  @since	4.0.0
*  @date	17/03/13
*
*  @param	array	$value: the value returned via get_field
*  @param	array	$field: the field or layout to find sub fields from
*
*  @return	N/A
*/

function acf_convert_field_names_to_keys( $value, $field )
{
	// only if $field has sub fields
	if( !isset($field['sub_fields']) )
	{
		return $value;
	}
	

	// define sub field keys
	$sub_fields = array();
	if( $field['sub_fields'] )
	{
		foreach( $field['sub_fields'] as $sub_field )
		{
			$sub_fields[ $sub_field['name'] ] = $sub_field;
		}
	}
	
	
	// loop through the values and format the array to use sub field keys
	if( is_array($value) )
	{
		foreach( $value as $row_i => $row)
		{
			if( $row )
			{
				foreach( $row as $sub_field_name => $sub_field_value )
				{
					// sub field must exist!
					if( !isset($sub_fields[ $sub_field_name ]) )
					{
						continue;
					}
					
					
					// vars
					$sub_field = $sub_fields[ $sub_field_name ];
					$sub_field_value = acf_convert_field_names_to_keys( $sub_field_value, $sub_field );
					
					
					// set new value
					$value[$row_i][ $sub_field['key'] ] = $sub_field_value;
					
					
					// unset old value
					unset( $value[$row_i][$sub_field_name] );
						
					
				}
				// foreach( $row as $sub_field_name => $sub_field_value )
			}
			// if( $row )
		}
		// foreach( $value as $row_i => $row)
	}
	// if( $value )
	
	
	return $value;

}



/*
*  Depreceated Functions
*
*  @description: 
*  @created: 23/07/12
*/


/*--------------------------------------------------------------------------------------
*
*	reset_the_repeater_field
*
*	@author Elliot Condon
*	@depreciated: 3.3.4 - now use has_sub_field
*	@since 1.0.3
* 
*-------------------------------------------------------------------------------------*/

function reset_the_repeater_field()
{
	// do nothing
}


/*--------------------------------------------------------------------------------------
*
*	the_repeater_field
*
*	@author Elliot Condon
*	@depreciated: 3.3.4 - now use has_sub_field
*	@since 1.0.3
* 
*-------------------------------------------------------------------------------------*/

function the_repeater_field($field_name, $post_id = false)
{
	return has_sub_field($field_name, $post_id);
}


/*--------------------------------------------------------------------------------------
*
*	the_flexible_field
*
*	@author Elliot Condon
*	@depreciated: 3.3.4 - now use has_sub_field
*	@since 3.?.?
* 
*-------------------------------------------------------------------------------------*/

function the_flexible_field($field_name, $post_id = false)
{
	return has_sub_field($field_name, $post_id);
}

/*
*  acf_filter_post_id()
*
*  This is a deprecated function which is now run through a filter
*
*  @type	function
*  @since	3.6
*  @date	29/01/13
*
*  @param	mixed	$post_id
*
*  @return	mixed	$post_id
*/

function acf_filter_post_id( $post_id )
{
	return apply_filters('acf/get_post_id', $post_id );
}





 ?>
