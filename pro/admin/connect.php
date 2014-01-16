<?php 

class acf_pro_connect {
	
	var $info;
	
	
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
		
		// filters
		add_filter('pre_set_site_transient_update_plugins', array($this, 'update_plugins'));
	}
	
	
	/*
	*  update_plugins
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
	
	function update_plugins( $transient ) {
		
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
        $slug = explode('/', $basename);
        $slug = current($slug);
        
        
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
        $obj->package = acf_pro_get_remote_url( 'download', array( 'license' => acf_pro_get_license() ) );
        
        
        // add to transient
        $transient->response[ $basename ] = $obj;
        
		
		// return 
        return $transient;
	}
	
	
}


// initialize
new acf_pro_connect();

?>