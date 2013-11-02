<?php 

class acf_field_group_api {
	
	/*
	*  __construct
	*
	*  Initialize filters, action, variables and includes
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	5.0.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function __construct() {
	
		add_filter( 'acf/get_valid_field_group',	array( $this, 'get_valid_field_group'), 5, 1 );
		add_filter( 'acf/get_field_groups',			array( $this, 'get_field_groups'), 5, 2 );
		add_filter( 'acf/get_field_group',			array( $this, 'get_field_group'), 5, 3 );
		
		add_filter( 'acf/update_field_group',		array( $this, 'update_field_group'), 5, 1 );
	}
	
	
	/*
	*  get_valid_field_group
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
	
	function get_valid_field_group( $field_group = array() ) {
		
		
		// defaults
		$defaults = array(
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
		);
		$field_group = acf_parse_args( $field_group, $defaults );
		
		
		// validate types
		$field_group['menu_order'] = intval( $field_group['menu_order'] );
		
		
		// return
		return $field_group;
	}
	
	
	/*
	*  get_field_groups
	*
	*  This function will return an array of field groupss for the given args. Similar to the WP get_posts function
	*
	*  @type	action (acf/get_field_groups)
	*  @date	2/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/

	function get_field_groups( $field_groups = array(), $args = array() ) {
		
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
		
		if( $posts )
		{
			foreach( $posts as $post )
			{
				// add to return array
				$field_groups[] = acf_get_field_group( $post );
			}
		}
		
		
		// set cache
		wp_cache_set( 'field_groups', $field_groups, 'acf' );
				
		
		// return		
		return $field_groups;
		
	}
	
	
	/*
	*  get_field_group
	*
	*  This function will take either a post object, post ID or even null (for global $post), and
	*  will then return a valid field group array
	*
	*  @type	function
	*  @date	30/09/13
	*  @since	5.0.0
	*
	*  @param	$field_group (array)
	*  @param	$selector (mixed)
	*  @return	(array)
	*/
	
	function get_field_group( $field_group = array(), $selector = false ) {
		
		// vars
		$post = false;
		$cache_key = '';
		$found = false;
		
		
		// try cache
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
			$cache_key = "get_field_group/ID={$selector}";
		}
		
		$cache = wp_cache_get( $cache_key, 'acf', false, $found );
		
		
		if( $found )
		{
			return $cache;
		}
		
		
		// $post = 'field_123'
		if( is_numeric($selector) )
		{
			$post = get_post( $selector );
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
	*  update_field_group
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
	
	function update_field_group( $field_group ) {
		
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
		$return = $field_group;
		
		
		// extract some args
		$extract = acf_extract_vars($field_group, array(
			'ID',
			'title',
			'menu_order',
			'fields',
		));
		
		
		// serialize for DB
		$field_group = maybe_serialize( $field_group );
	        
	    
	    // save
	    $save = array(
	    	'ID'			=> $extract['ID'],
	    	'post_type'		=> 'acf-field-group',
	    	'post_title'	=> $extract['title'],
	    	'post_name'		=> "acf-field-group-{$extract['ID']}",
	    	'post_content'	=> $field_group,
	    	'menu_order'	=> $extract['menu_order'],
	    );
	    
	    
	    // update or insert the field group
	    if( $save['ID'] )
	    {
		    wp_update_post( $save );
	    }
	    else
	    {
		    $return['ID'] = wp_insert_post( $save );
	    }
	    
	    
	    
	    // return
	    return $return;
		
	}
	
	
}

new acf_field_group_api();



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
	
	return apply_filters('acf/get_valid_field_group', $field_group);
}


/*
*  acf_get_field_groups
*
*  
*
*  @type	function
*  @date	30/09/13
*  @since	5.0.0
*
*  @param	$args (array)
*  @return	(array)
*/

function acf_get_field_groups( $args = false ) {
	
	return apply_filters('acf/get_field_groups', array(), $args);
	
}


/*
*  acf_get_field_group
*
*  
*
*  @type	function
*  @date	30/09/13
*  @since	5.0.0
*
*  @param	$selector (mixed)
*  @return	(array)
*/

function acf_get_field_group( $selector = null ) {
	
	return apply_filters('acf/get_field_group', array(), $selector);
	
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
	
	return apply_filters('acf/update_field_group', $field_group);

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
	return count( $posts );
	
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
	
			
	return $e;
}

?>