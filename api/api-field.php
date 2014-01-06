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
	
	
	// defaults
	$field = acf_parse_args($field, array(
		'ID'				=> 0,
		'key'				=> '',
		'label'				=> '',
		'name'				=> '',
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
		'field_name'		=> '',
		'prefix'			=> '',
	));
	
	
	// id
	if( !$field['id'] )
	{
		$field['id'] = "acf-field-{$field['name']}";
	}
	
	
	// backup name to field name
	$field['field_name'] = $field['name'];
	
	
	// field specific defaults
	$field = apply_filters( "acf/get_valid_field", $field );
	$field = apply_filters( "acf/get_valid_field/type={$field['type']}", $field );
	
	
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
	
	
	// class
	//$field['class'] .= ' ' . $field['type'];
	
	
	// update the $field's name based on the key and prefix
	// $field['name'] has been used so far as a nice name for element attributes such as css.
	// now, however, the name is more useful as the $_POST name
	// this allows a plugin dev to use the field name for $_POST data, whilst ACF can use the field key
	/*
	if( $field['ID'] )
		{
			$field['name'] = $field['ID'];
		}
		else
	*/
	if( $field['key'] )
	{
		$field['name'] = $field['key'];
	}

	
	// prefix
	if( $field['prefix'] )
	{
		$field['name'] = "{$field['prefix']}[{$field['name']}]";
	}
	
	
	// create field specific html
	do_action( "acf/render_field", $field );
	do_action( "acf/render_field/type={$field['type']}", $field );
	
	
	// conditional logic (todo: move away from this function maybe)
	/*
if( is_array($field['conditional_logic']) && $field['conditional_logic']['status'] )
	{
		$field['conditional_logic']['field'] = $field['key'];
		
		?>
		<script type="text/javascript">
		(function($) {
			
			acf.conditional_logic.items.push(<?php echo json_encode($field['conditional_logic']); ?>);
			
		})(jQuery);	
		</script>
		<?php
	}
*/
	
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
		'post_parent'		=> $id
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
*  acf_get_fields
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
	else
	{
		$v = get_the_ID();
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
	if( empty($post[0]) )
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

function acf_update_field( $field = false ) {
	
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
	if( is_array($field['conditional_logic']) )
	{
		$field['conditional_logic'] = array_values( $field['conditional_logic'] );
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
		'value',
		'menu_order',
		'id',
		'class',
		'parent',
		'ancestors',
		'field_group',
		'field_name',
		'prefix',
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
    
    
    // Note: find out why by default, ACF is adding a -2
	add_filter( 'wp_unique_post_slug', 'acf_update_field_wp_unique_post_slug', 5, 6 ); 
		
		
    // update the field and update the ID
    $field['ID'] = wp_update_post( $save );
    
    
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

function acf_delete_field( $id ) {
	
	// vars
	$return = false;
	
	
	// action for 3rd party customisation
	do_action( 'acf/delete_field', $id );
	
	
	// delete field
	$return = wp_delete_post( $id, true );
	
	
	// return
	return $return;
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
	
	// load the origional field
	$field = acf_get_field( $selector );
	
	
	// bail early if field did not load correctly
	if( empty($field) )
	{
		return false;
	}
	
	
	// update ID
	$field['ID'] = false;
	
	
	// update field group
	if( $parent_id )
	{
		$field['parent'] = $parent_id;
	}
	
	
	// filter for 3rd party customization
	$field = apply_filters('acf/duplicate_field_group', $field);
	
	
	// save
	return acf_update_field( $field );
	
}

?>