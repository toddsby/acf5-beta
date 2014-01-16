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
	$url = "http://connect/index.php";
	
	
	// add in args
	$url = add_query_arg( $args, $url );
	
	
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
	$request = wp_remote_post( $url, $post );
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


function acf_pro_get_license() {
	
	return get_option('acf_pro_license');
	
}

function acf_pro_update_license( $license ) {
	
	return update_option('acf_pro_license', $license);
	
}

?>