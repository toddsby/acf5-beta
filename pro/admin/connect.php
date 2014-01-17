<?php 

class acf_pro_connect {
	
	
	/*
	*  __construct
	*
	*  Initialize filters, action, variables and includes
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
		
		// override requests for plugin information
        add_filter('plugins_api', array($this, 'inject_info'), 20, 3);
        
        
		// insert our update info into the update array maintained by WP
		add_filter('site_transient_update_plugins', array($this, 'inject_update'));
	}
	
	
	/*
	*  inject_info
	*
	*  description
	*
	*  @type	function
	*  @date	17/01/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function inject_info( $res, $action = null, $args = null ) {
		
		// vars
		$slug = acf_get_setting('slug');
        
        
		// validate
    	if( isset($args->slug) && $args->slug == $slug )
    	{
	    	$info = acf_pro_get_remote_info();
	    	
	    	$obj = new stdClass();
		
		    foreach( $info as $k => $v )
		    {
		        $obj->$k = $v;
		    }
		    
		    return $obj;
		    
    	}
    	
    	        
        return $res;
        
	}
	
	
	/*
	*  inject_update
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
	
	function inject_update( $transient ) {
		
		// bail early if no plugins are being checked
	    if( empty($transient->checked) )
	    {
            return $transient;
        }
		
		
        // vars
        $info = acf_pro_get_remote_info();
        
        
        // bail early if no info
        if( !$info )
        {
	        return $transient;
        }
       
        
        // vars
        $version = acf_get_setting('version');
        $basename = acf_get_setting('basename');
        $slug = acf_get_setting('slug');
        
        
        // bail early if the external version is '<=' the current version
		if( version_compare($info['version'], $version, '<=') )
        {
        	return $transient;
        }
		
		
        // create new object for update
        $obj = new stdClass();
        $obj->slug = $slug;
        $obj->new_version = $info['version'];
        $obj->url = $info['homepage'];
        $obj->package = '';
        
        
        // license
		if( acf_pro_is_license_active() )
		{
			$obj->package = acf_pro_get_remote_url( 'download', array( 'k' => acf_pro_get_license() ) );
		}
		
        
        // add to transient
        $transient->response[ $basename ] = $obj;
        
		
		// return 
        return $transient;
	}
	
	
}


// initialize
new acf_pro_connect();

?>