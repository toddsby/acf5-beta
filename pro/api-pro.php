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
	$url = "http://connect/index.php?" . build_query($args);
	
	
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
	set_transient('acf_pro_get_info', $info, HOUR_IN_SECONDS );
	
	
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

?>