<?php 

/*
*  acf_get_valid_field_group
*
*  This function will fill in any missing keys to the $field_group array making it valid
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$field_group (array)
*  @return	$field_group (array)
*/

function acf_get_valid_field_group( $field_group = false ) {
	
	// parse in defaults
	$field_group = acf_parse_args( $field_group, array(
		'ID'					=> 0,
		'title'					=> '',
		'fields'				=> array(),
		'location'				=> array(),
		'menu_order'			=> 0,
		'position'				=> 'normal',
		'style'					=> 'seamless',
		'label_placement'		=> 'top',
		'instruction_placement'	=> 'label',
		'hide_on_screen'		=> array()
	));
	
	
	// filter
	$field_group = apply_filters('acf/get_valid_field_group', $field_group);
	
	
	// return
	return $field_group;
}


/*
*  acf_get_field_groups
*
*  This function will return an array of field groupss for the given args. Similar to the WP get_posts function
*
*  @type	function
*  @date	30/09/13
*  @since	5.0.0
*
*  @param	$args (array)
*  @return	$field_groups (array)
*/

function acf_get_field_groups( $args = false ) {
	
	// vars
	$field_groups = array();
	
	
	// EXPORT JSON hook needed
	
	
	// cache
	$found = false;
	$cache = wp_cache_get( 'field_groups', 'acf', false, $found );
	
	if( $found )
	{
		return $cache;
	}
	
	
	// load from DB
	$posts = get_posts(array(
		'post_type'			=> 'acf-field-group',
		'posts_per_page'	=> -1,
		'orderby' 			=> 'menu_order title',
		'order' 			=> 'asc',
		'suppress_filters'	=> false,
		'post_status'		=> 'publish',
	));
	
	
	// loop through and load field groups
	if( $posts )
	{
		foreach( $posts as $post )
		{
			// add to return array
			$field_groups[] = acf_get_field_group( $post );
		}
	}
	
	
	// filter
	$field_groups = apply_filters('acf/get_field_groups', $field_groups, $args);
	
	
	// set cache
	wp_cache_set( 'field_groups', $field_groups, 'acf' );
			
	
	// return		
	return $field_groups;
}


/*
*  acf_get_field_group
*
*  This function will take either a post object, post ID or even null (for global $post), and
*  will then return a valid field group array
*
*  @type	function
*  @date	30/09/13
*  @since	5.0.0
*
*  @param	$selector (mixed)
*  @return	$field_group (array)
*/

function acf_get_field_group( $selector = false ) {
	
	// vars
	$field_group = array();
	$post = false;
	$cache_key = '';
	$found = false;
	
	
	// EXPORT JSON hook needed
	
	
	// calculate $cache_key and $post
	if( !$selector )
	{
		global $post;
		$cache_key = "get_field_group/ID={$post->ID}";
	}
	elseif( is_object($selector) )
	{
		$post = $selector;
		$cache_key = "get_field_group/ID={$post->ID}";
	}
	elseif( is_numeric($selector) )
	{
		$post = get_post( $selector );
		$cache_key = "get_field_group/ID={$selector}";
	}
	
	
	// try cache
	$cache = wp_cache_get( $cache_key, 'acf', false, $found );
	
	if( $found )
	{
		return $cache;
	}

	
	// validate
	if( empty($post) || !isset($post->post_content) )
	{
		return false;
	}
	
		
	// get data
	$data = maybe_unserialize( $post->post_content );
	
	if( is_array($data) )
	{
		$field_group = $data;
	}
	
	
	// update attributes
	$field_group['ID'] = $post->ID;
	$field_group['title'] = $post->post_title;
	$field_group['menu_order'] = $post->menu_order;
	
	
	// validate
	$field_group = acf_get_valid_field_group( $field_group );
	
	
	// set cache
	wp_cache_set( $cache_key, $field_group, 'acf' );
	
	
	// return
	return $field_group;
}


/*
*  acf_update_field_group
*
*  This function will update a field group into the database
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$field_group (array)
*  @return	(int)
*/

function acf_update_field_group( $field_group = array() ) {
	
	// validate
	$field_group = acf_get_valid_field_group( $field_group );
	
	
	// may have been posted. Remove slashes
	$field_group = wp_unslash( $field_group );
	
	
	// locations may contain 'uniquid' array keys
	$field_group['location'] = array_values( $field_group['location'] );
	
	foreach( $field_group['location'] as $k => $v )
	{
		$field_group['location'][ $k ] = array_values( $v );
	}
	
	
	// store origional field group for return
	$data = $field_group;
	
	
	// extract some args
	$extract = acf_extract_vars($data, array(
		'ID',
		'title',
		'menu_order',
		'fields',
	));
	
	
	// serialize for DB
	$data = maybe_serialize( $data );
        
    
    // save
    $save = array(
    	'ID'			=> $extract['ID'],
    	'post_type'		=> 'acf-field-group',
    	'post_title'	=> $extract['title'],
    	'post_name'		=> "acf-field-group-{$extract['ID']}",
    	'post_content'	=> $data,
    	'menu_order'	=> $extract['menu_order'],
    );
    
    
    // update the field group and update the ID
	$field_group['ID'] = wp_update_post( $save );
	
	
	// action for 3rd party customization
	do_action('acf/update_field_group', $field_group);
	
	
    // return
    return $field_group;
	
}


/*
*  acf_duplicate_field_group
*
*  This function will duplicate a field group into the database
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$id (int)
*  @return	(int)
*/

function acf_duplicate_field_group( $selector = 0 ) {
	
	// load the origional field gorup
	$field_group = acf_get_field_group( $selector );
	
	
	// bail early if field group did not load correctly
	if( empty($field_group) )
	{
		return false;
	}
	
	
	// keep backup of field group
	$orig_field_group = $field_group;
	
	
	// update ID
	$field_group['ID'] = false;
	
	
	// save
	$field_group = acf_update_field_group( $field_group );
	
	
	// get fields
	$fields = acf_get_fields(array(
		'field_group' => $orig_field_group['ID']
	));
	
	
	if( !empty($fields) )
	{
		foreach( $fields as $field )
		{
			acf_duplicate_field( $field['ID'], $field_group['ID'] );
		}
	}
	
	
	// action for 3rd party customization
	do_action('acf/duplicate_field_group', $field_group);
	
	
	// return
	return $field_group;

}


/*
*  acf_get_field_count
*
*  This function will return the number of fields for the given field group
*
*  @type	function
*  @date	17/10/13
*  @since	5.0.0
*
*  @param	$field_group_id (int)
*  @return	(int)
*/

function acf_get_field_count( $field_group_id ) {
	
	// vars
	$args = array(
		'posts_per_page'	=> -1,
		'post_type'			=> 'acf-field',
		'orderby'			=> 'menu_order',
		'order'				=> 'ASC',
		'suppress_filters'	=> false,
		'post_parent'		=> $field_group_id,
		'fields'			=> 'ids'
	);
	
	
	// load fields
	$posts = get_posts( $args );
	
	
	// return
	return apply_filters('acf/get_field_count', count( $posts ), $field_group_id);
	
}


/*
*  acf_get_field_group_style
*
*  This function will render the CSS for a given field group
*
*  @type	function
*  @date	20/10/13
*  @since	5.0.0
*
*  @param	$field_group (array)
*  @return	n/a
*/

function acf_get_field_group_style( $field_group )
{
	// vars
	$e = '';
	
	
	// validate
	if( !is_array($field_group['hide_on_screen']) )
	{
		return $e;
	}
	
	
	// add style to html 
	if( in_array('the_content',$field_group['hide_on_screen']) )
	{
		$e .= '#postdivrich {display: none;} ';
	}
	
	if( in_array('excerpt',$field_group['hide_on_screen']) )
	{
		$e .= '#postexcerpt, #screen-meta label[for=postexcerpt-hide] {display: none;} ';
	}
	
	if( in_array('custom_fields',$field_group['hide_on_screen']) )
	{
		$e .= '#postcustom, #screen-meta label[for=postcustom-hide] { display: none; } ';
	}
	
	if( in_array('discussion',$field_group['hide_on_screen']) )
	{
		$e .= '#commentstatusdiv, #screen-meta label[for=commentstatusdiv-hide] {display: none;} ';
	}
	
	if( in_array('comments',$field_group['hide_on_screen']) )
	{
		$e .= '#commentsdiv, #screen-meta label[for=commentsdiv-hide] {display: none;} ';
	}
	
	if( in_array('slug',$field_group['hide_on_screen']) )
	{
		$e .= '#slugdiv, #screen-meta label[for=slugdiv-hide] {display: none;} ';
	}
	
	if( in_array('author',$field_group['hide_on_screen']) )
	{
		$e .= '#authordiv, #screen-meta label[for=authordiv-hide] {display: none;} ';
	}
	
	if( in_array('format',$field_group['hide_on_screen']) )
	{
		$e .= '#formatdiv, #screen-meta label[for=formatdiv-hide] {display: none;} ';
	}
	
	if( in_array('featured_image',$field_group['hide_on_screen']) )
	{
		$e .= '#postimagediv, #screen-meta label[for=postimagediv-hide] {display: none;} ';
	}
	
	if( in_array('revisions',$field_group['hide_on_screen']) )
	{
		$e .= '#revisionsdiv, #screen-meta label[for=revisionsdiv-hide] {display: none;} ';
	}
	
	if( in_array('categories',$field_group['hide_on_screen']) )
	{
		$e .= '#categorydiv, #screen-meta label[for=categorydiv-hide] {display: none;} ';
	}
	
	if( in_array('tags',$field_group['hide_on_screen']) )
	{
		$e .= '#tagsdiv-post_tag, #screen-meta label[for=tagsdiv-post_tag-hide] {display: none;} ';
	}
	
	if( in_array('send-trackbacks',$field_group['hide_on_screen']) )
	{
		$e .= '#trackbacksdiv, #screen-meta label[for=trackbacksdiv-hide] {display: none;} ';
	}
	
	
	// return	
	return apply_filters('acf/get_field_group_style', $e, $field_group);
}

?>