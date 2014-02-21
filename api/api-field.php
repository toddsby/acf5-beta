<?php

/*
*  acf_get_valid_field
*
*  This function will fill in any missing keys to the $field array making it valid
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$field (array)
*  @return	$field (array)
*/

function acf_get_valid_field( $field = false ) {
	
	// $field must be an array
	if( !is_array($field) )
	{
		$field = array();
	}
	
	
	// bail ealry if field_name exists (only run this function once)
	if( !empty($field['_valid']) )
	{
		return $field;
	}
	
	
	// defaults
	$field = acf_parse_args($field, array(
		'ID'				=> 0,
		'key'				=> '',
		'label'				=> '',
		'name'				=> '',
		'prefix'			=> '',
		'type'				=> 'text',
		'value'				=> null,
		'menu_order'		=> 0,
		'instructions'		=> '',
		'required'			=> 0,
		'id'				=> '',
		'class'				=> '',
		'conditional_logic'	=> 0,
		'parent'			=> 0,
		'ancestors'			=> array(),
		'field_group'		=> 0,
		'_name'				=> '',
		'_input'			=> '',
		'_valid'			=> 0,
	));
	
	
	// _name
	$field['_name'] = $field['name'];
	
	
	// translate
	foreach( array('label', 'instructions') as $s )
	{
		$field[ $s ] = __($field[ $s ]);
	}
	
	
	// field specific defaults
	$field = apply_filters( "acf/get_valid_field", $field );
	$field = apply_filters( "acf/get_valid_field/type={$field['type']}", $field );
	
	
	// field is now valid
	$field['_valid'] = 1;
	
	
	// return
	return $field;
}


/*
*  acf_prepare_field
*
*  This function will prepare the field for input
*
*  @type	function
*  @date	12/02/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_prepare_field( $field ) {
	
	// _input
	if( !$field['_input'] )
	{
		$field['_input'] = $field['name'];
	
	
		// _input: key overrides name
		if( $field['key'] )
		{
			$field['_input'] = $field['key'];
		}
	
		
		// _input: prefix prepends name
		if( $field['prefix'] )
		{
			$field['_input'] = "{$field['prefix']}[{$field['_input']}]";
		}
	}
	
	
	// add id (may be custom set)
	if( !$field['id'] )
	{
		$field['id'] = str_replace(
			array('][', '[', ']'),
			array('-', '-', ''),
			$field['_input']
		);
	}
	
	
	// return
	return $field;
}


/*
*  acf_render_field
*
*  This function will render a field input
*
*  @type	action (acf/render_field)
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$field (array)
*  @return	N/A
*/

function acf_render_field( $field = false ) {
	
	// get valid field
	$field = acf_get_valid_field( $field );
	
	
	// prepare field for input
	$field = acf_prepare_field( $field );
	
	
	// update $field['name']
	$field['name'] = $field['_input'];
		
	
	// create field specific html
	do_action( "acf/render_field", $field );
	do_action( "acf/render_field/type={$field['type']}", $field );
	
}


/*
*  acf_render_field_options
*
*  This function will render the available field options using an action to trigger the field's render function
*
*  @type	function
*  @date	23/01/13
*  @since	3.6.0
*
*  @param	$field (array)
*  @return	n/a
*/

function acf_render_field_options( $field ) {
	
	// get valid field
	$field = acf_get_valid_field( $field );
	
	
	// create field specific html
	do_action( "acf/render_field_options", $field);
	do_action( "acf/render_field_options/type={$field['type']}", $field);
	
}


/*
*  acf_get_fields
*
*  This function will return an array of fields for the given $parent
*
*  @type	function
*  @date	30/09/13
*  @since	5.0.0
*
*  @param	$parent (array)
*  @return	(array)
*/

function acf_get_fields( $parent = false ) {

	
	// allow $parent to be a field group ID
	if( !is_array($parent) )
	{
		$parent = acf_get_field_group( $parent );
	}
	
	
	// validate
	if( !$parent )
	{
		return false;
	}
	
	
	// vars
	$fields = array();
	
	
	// try JSON before DB to save query time
	if( acf_has_json_fields( $parent['key'] ) )
	{
		$fields = acf_get_json_fields( $parent['key'] );
	}
	else
	{
		$fields = acf_get_fields_by_id( $parent['ID'] );
	}
	
	
	// return
	return apply_filters('acf/get_fields', $fields, $parent);
	
}

function acf_get_fields_by_id( $id ) {
	
	// vars
	$fields = array();
	
	
	// args
	$args = array(
		'posts_per_page'	=> -1,
		'post_type'			=> 'acf-field',
		'orderby'			=> 'menu_order',
		'order'				=> 'ASC',
		'suppress_filters'	=> false,
		'post_parent'		=> $id,
		'post_status'		=> 'publish, trash' // 'any' won't get trashed fields
	);
		
	
	// load fields
	//if( acf->get_setting('alow_db') )
	//{
	$posts = get_posts( $args );
	
	if( $posts )
	{
		foreach( $posts as $post )
		{
			$fields[] = acf_get_field( $post->ID );
		}	
	}
	//}
	
	
	// return
	return $fields;
	
}


/*
*  acf_get_field
*
*  This function will return a field for the given selector. 
*
*  @type	function
*  @date	30/09/13
*  @since	5.0.0
*
*  @param	$selector (mixed)
*  @return	(array)
*/

function acf_get_field( $selector = null ) {
	
	// vars
	$field = false;
	$k = 'ID';
	$v = 0;
	
	
	// $post_id or $key
	if( is_numeric($selector) )
	{
		$v = $selector;
	}
	elseif( is_string($selector) )
	{
		$k = 'key';
		$v = $selector;
	}
	elseif( is_object($selector) )
	{
		$v = $selector->ID;
	}
	elseif( get_post() )
	{
		$v = get_the_ID();
	}
	else
	{
		return false;
	}
	
	
	// get cache key
	$cache_key = "load_field/{$k}={$v}";
	
	
	// get cache
	$found = false;
	$cache = wp_cache_get( $cache_key, 'acf', false, $found );
	
	if( $found )
	{
		return $cache;
	}
	
	
	// get field group from ID or key
	if( $k == 'ID' )
	{
		$field = _acf_get_field_by_id( $v );
	}
	else
	{
		$field = _acf_get_field_by_key( $v );
	}
	
		
	// filter for 3rd party customization
	$field = apply_filters('acf/load_field', $field);
	
	
	// If a field has been found, apply filters
	if( $field )
	{
		$field = apply_filters( "acf/load_field/type={$field['type']}", $field );
		$field = apply_filters( "acf/load_field/name={$field['name']}", $field );
		$field = apply_filters( "acf/load_field/key={$field['key']}", $field );
	}
	

	// set cache
	wp_cache_set( $cache_key, $field, 'acf' );
		
	
	// return
	return $field;
	
}


function _acf_get_field_by_id( $post_id = 0 ) {
	
	// vars
	$field = false;
	
	
	// get post
	$post = get_post( $post_id );
	
	
	// validate
	if( empty($post) )
	{
		return $field;	
	}
	
	
	// unserialize
	$data = maybe_unserialize( $post->post_content );
	
	
	// update $field
	if( is_array($data) )
	{
		$field = $data;
	}
	
	
	// update attributes
	$field['ID'] = $post->ID;
	$field['key'] = $post->post_name;
	$field['label'] = $post->post_title;
	$field['name'] = $post->post_excerpt;
	$field['menu_order'] = $post->menu_order;
	$field['parent'] = $post->post_parent;
	$field['ancestors'] = get_post_ancestors( $post );
	$field['field_group'] = end( $field['ancestors'] );


	// override with JSON
	if( acf_is_json_field( $field['key'] ) )
	{
		// extract some args
		$backup = acf_extract_vars($field, array(
			'ID',
			'parent',
			'ancestors',
			'field_group',
		));
		

		// load JSON field
		$field = acf_get_json_field( $field['key'] );
		
		
		// merge in backup
		$field = array_merge($field, $backup);
	}
	
	
	// validate
	$field = acf_get_valid_field( $field );
	
	
	// return
	return $field;
	
}


function _acf_get_field_by_key( $key = '' ) {
	
	// vars
	$field = false;	
	
	
	// try JSON before DB to save query time
	if( acf_is_json_field( $key ) )
	{
		$field = acf_get_json_field( $key );
		
		// validate
		$field = acf_get_valid_field( $field );
	
		// return
		return $field;
	}
	
	
	// vars
	$args = array(
		'posts_per_page'	=> 1,
		'post_type'			=> 'acf-field',
		'orderby' 			=> 'menu_order title',
		'order'				=> 'ASC',
		'suppress_filters'	=> false,
		'acf_field_key'		=> $key
	);
	
	
	// load posts
	$posts = get_posts( $args );
	
	
	// validate
	if( empty($posts) )
	{
		return $field;	
	}
	
	
	// load from ID
	$field = _acf_get_field_by_id( $posts[0]->ID );
	
		
	// return
	return $field;
	
}



/*
*  acf_update_field
*
*  This function will update a field into the DB
*
*  @type	function
*  @date	1/10/13
*  @since	5.0.0
*
*  @param	$field (array)
*  @return	(int)
*/

function acf_update_field( $field = false, $specific = false ) {
	
	// $field must be an array
	if( !is_array($field) )
	{
		return false;
	}
	
	
	// validate
	$field = acf_get_valid_field( $field );
	
	
	// may have been posted. Remove slashes
	$field = wp_unslash( $field );
	
	
	// configure parent / field_group
	if( !$field['parent'] )
	{
		$field['parent'] = $field['field_group'];
	}
	
	
	// clean up conditional logic keys
	if( !empty($field['conditional_logic']) )
	{
		$field['conditional_logic'] = array_values( $field['conditional_logic'] );
		
		foreach( array_keys($field['conditional_logic']) as $key )
		{
			$field['conditional_logic'][ $key ] = array_values( $field['conditional_logic'][ $key ] );
		}
	}
	
	
	// filter for 3rd party customization
	$field = apply_filters( "acf/update_field", $field);
	$field = apply_filters( "acf/update_field/type={$field['type']}", $field );
	$field = apply_filters( "acf/update_field/name={$field['name']}", $field );
	$field = apply_filters( "acf/update_field/key={$field['key']}", $field );
	
	
	// store origional field for return
	$data = $field;
	
	
	// extract some args
	$extract = acf_extract_vars($data, array(
		'ID',
		'key',
		'label',
		'name',
		'prefix',
		'value',
		'menu_order',
		'id',
		'class',
		'parent',
		'ancestors',
		'field_group',
		'_name',
		'_input',
		'_valid',
	));
	
	
	// serialize for DB
	$data = maybe_serialize( $data );
    
    
    // save
    $save = array(
    	'ID'			=> $extract['ID'],
    	'post_status'	=> 'publish',
    	'post_type'		=> 'acf-field',
    	'post_title'	=> $extract['label'],
    	'post_name'		=> $extract['key'],
    	'post_excerpt'	=> $extract['name'],
    	'post_content'	=> $data,
    	'post_parent'	=> $extract['parent'],
    	'menu_order'	=> $extract['menu_order'],
    );
    
    
    // $specific
    if( !empty($specific) )
    {
    	// prepend ID
    	array_unshift( $specific, 'ID' );
    	
    	
    	// appen data
    	foreach( $specific as $key )
    	{
	    	$_save[ $key ] = $save[ $key ];
    	}
    	
    	
    	// override save
    	$save = $_save;
    	
    	
    	// clean up
    	unset($_save);
    	
    	
    }
    
    
    // Note: find out why by default, ACF is adding a -2
	add_filter( 'wp_unique_post_slug', 'acf_update_field_wp_unique_post_slug', 5, 6 ); 
	
	
    // update the field and update the ID
    if( $field['ID'] )
    {
	    wp_update_post( $save );
    }
    else
    {
	    $field['ID'] = wp_insert_post( $save );
    }
	
    
    // update cache
	wp_cache_set( "load_field/ID={$field['ID']}", $field, 'acf' );
	
	
    // return
    return $field;
	
}

function acf_update_field_wp_unique_post_slug( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug ) {
		
	if( $post_type == 'acf-field' )
	{
		$slug = $original_slug;
	}
	
	return $slug;
}


/*
*  acf_duplicate_field
*
*  This function will duplicate a field and attach it to the given field group ID
*
*  @type	function
*  @date	17/10/13
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_duplicate_field( $selector = 0, $parent_id = 0 ){
	
	// disable JSON to avoid conflicts between DB and JSON
	acf_update_setting('json', false);
	
	
	// load the origional field
	$field = acf_get_field( $selector );
	
	
	// bail early if field did not load correctly
	if( empty($field) )
	{
		return false;
	}
	
	
	// update ID
	$field['ID'] = false;
	$field['key'] = uniqid('field_');
	
	
	// update field group
	if( $parent_id )
	{
		$field['parent'] = $parent_id;
	}
	
	
	// filter for 3rd party customization
	$field = apply_filters('acf/duplicate_field', $field);
	
	
	// save
	return acf_update_field( $field );
	
}


/*
*  acf_delete_field
*
*  This function will delete a field from the databse
*
*  @type	function
*  @date	2/10/13
*  @since	5.0.0
*
*  @param	$id (int)
*  @return	(boolean)
*/

function acf_delete_field( $selector = 0 ) {
	
	// load the origional field gorup
	$field = acf_get_field( $selector );
	
	
	// bail early if field did not load correctly
	if( empty($field) )
	{
		return false;
	}
	
	
	// delete field
	wp_delete_post( $field['ID'], true );
	
	
	// action for 3rd party customisation
	do_action( 'acf/delete_field', $field );
	
	
	// return
	return true;
}


/*
*  acf_trash_field
*
*  This function will trash a field from the databse
*
*  @type	function
*  @date	2/10/13
*  @since	5.0.0
*
*  @param	$id (int)
*  @return	(boolean)
*/

function acf_trash_field( $selector = 0 ) {
	
	// load the origional field gorup
	$field = acf_get_field( $selector );
	
	
	// bail early if field did not load correctly
	if( empty($field) )
	{
		return false;
	}
	
	
	// delete field
	wp_trash_post( $field['ID'] );
	
	
	// action for 3rd party customisation
	do_action( 'acf/trash_field', $field );
	
	
	// return
	return true;
}


/*
*  acf_untrash_field
*
*  This function will restore a field from the trash
*
*  @type	function
*  @date	2/10/13
*  @since	5.0.0
*
*  @param	$id (int)
*  @return	(boolean)
*/

function acf_untrash_field( $selector = 0 ) {
	
	// load the origional field gorup
	$field = acf_get_field( $selector );
	
	
	// bail early if field did not load correctly
	if( empty($field) )
	{
		return false;
	}
	
	
	// delete field
	wp_untrash_post( $field['ID'] );
	
	
	// action for 3rd party customisation
	do_action( 'acf/untrash_field', $field );
	
	
	// return
	return true;
}




?>