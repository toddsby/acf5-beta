<?php 

/*
*  acf_pro_get_view
*
*  This function will load in a file from the 'admin/views' folder and allow variables to be passed through
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$view_name (string)
*  @param	$args (array)
*  @return	n/a
*/

function acf_pro_get_view( $view_name = '', $args = array() ) {
	
	// vars
	$path = acf_get_path("pro/admin/views/{$view_name}.php");
	
	
	if( file_exists($path) )
	{
		include( $path );
	}
}


/*
*  acf_pro_get_remote_url
*
*  description
*
*  @type	function
*  @date	16/01/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_pro_get_remote_url( $action = '', $args = array() ) {
	
	// defaults
	$args['a'] = $action;
	$args['p'] = 'pro';
	
	
	// vars
	$url = "http://connect.advancedcustomfields.com/index.php?" . build_query($args);
	
	
	// return
	return $url;
}


/*
*  acf_pro_get_remote_response
*
*  description
*
*  @type	function
*  @date	16/01/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_pro_get_remote_response( $action = '', $post = array() ) {
	
	// vars
	$url = acf_pro_get_remote_url( $action );
	
	
	// connect
	$request = wp_remote_post( $url, array(
		'body' => $post
	));
	

    if( !is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200)
    {
        return $request['body'];
    }
    
    
    // return
    return false;
}



/*
*  get_info
*
*  description
*
*  @type	function
*  @date	16/01/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_pro_get_remote_info() {
	
	// check for transient
	$transient = get_transient( 'acf_pro_get_remote_info' );
	
	if( !empty($transient) )
	{
		return $transient;
	}

	
	// vars
	$info = acf_pro_get_remote_response('get-info');
	
	
    // validate
    if( empty($info) )
    {
        return false;
    }
    
	
	// decode and return
	$info = json_decode($info, true);
	
	
	// update transient
	set_transient('acf_pro_get_remote_info', $info, 1 * HOUR_IN_SECONDS );
	
	
	return $info;
}

function acf_pro_is_license_active() {
	
	// vars
	$data = acf_pro_get_license( true );
	$url = get_bloginfo('url');
	
	
	if( isset($data['url'], $data['key']) && $data['url'] == $url )
	{
		return true;
	}
	
	
	return false;
	
}

function acf_pro_get_license( $all = false ) {
	
	// get option
	$data = get_option('acf_pro_license');
	
	
	// decode
	$data = base64_decode($data);
	
	
	// attempt deserialize
	if( is_serialized( $data ) )
	{
		$data = maybe_unserialize($data);
		
		// $all
		if( !$all )
		{
			$data = $data['key'];
		}
		
		return $data;
	}
	
	
	// return
	return false;
}



function acf_pro_update_license( $license ) {
	
	$save = array(
		'key'	=> $license,
		'url'	=> get_bloginfo('url')
	);
	
	
	$save = maybe_serialize($save);
	$save = base64_encode($save);
	
	
	return update_option('acf_pro_license', $save);
	
}


/*
*  acf_get_valid_options_page
*
*  description
*
*  @type	function
*  @date	24/02/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_get_valid_options_page( $page = '' ) {
	
	// allow for string
	if( is_string($page) )
	{
		$page_title = $page;
		
		$page = array(
			'page_title' => $page_title,
			'menu_title' => $page_title
		);
	}
	
	
	// defaults
	$page = acf_parse_args($page, array(
		'page_title' 	=> '',
		'menu_title'	=> '',
		'menu_slug' 	=> '',
		'capability'	=> 'edit_posts',
		'parent_slug'	=> false,
		'position'		=> false,
		'icon_url'		=> false,
	));
	
	
	// slug
	if( $page['menu_slug'] == '' )
	{
		$page['menu_slug'] = 'acf-options-' . sanitize_title( $page['menu_title'] );
	}
	
	
	// return
	return $page;
	
}

/*
*  acf_pro_get_option_pages
*
*  description
*
*  @type	function
*  @date	24/02/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_get_options_pages() {
	
	// vars
	$pages = array();
	
	
	// add parent page
	if( $default = acf_get_setting('options_page') )
	{
		$pages[ $default['menu_slug'] ] = $default;
	}
	
	
	// get pages
	if( !empty($GLOBALS['acf_options_pages']) )
	{
		$keys = array_keys($GLOBALS['acf_options_pages']);
		
		foreach( $keys as $key )
		{
			$pages[ $key ] = acf_extract_var($GLOBALS['acf_options_pages'], $key);
		}
	}
	
	
	// return
	return $pages;
	
}


/*
*  acf_pro_get_option_page
*
*  description
*
*  @type	function
*  @date	24/02/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_get_options_page( $slug ) {
	
	// vars
	$page = false;
	
	
	// get pages
	if( !empty($GLOBALS['acf_options_pages'][ $slug ]) )
	{
		$page = $GLOBALS['acf_options_pages'][ $slug ];
	}
	
	
	// return
	return $page;
	
}


/*
*  acf_add_options_page
*
*  description
*
*  @type	function
*  @date	24/02/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_add_options_page( $page = '' ) {
	
	// validate
	$page = acf_get_valid_options_page( $page );
	
	
	// instantiate globals
	if( empty($GLOBALS['acf_options_pages']) )
	{
		$GLOBALS['acf_options_pages'] = array();
	}
	
	
	// append
	$GLOBALS['acf_options_pages'][ $page['menu_slug'] ] = $page;
	
	
	// return
	return $page;
	
}


/*
*  acf_add_options_page
*
*  description
*
*  @type	function
*  @date	24/02/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_add_options_sub_page( $page = '' ) {
	
	// validate
	$page = acf_get_valid_options_page( $page );
	
	
	// parent
	if( empty($page['parent_slug']) )
	{
		$page['parent_slug'] = 'acf-options';
	}
	
	
	// return
	return acf_add_options_page( $page );
	
}



?>