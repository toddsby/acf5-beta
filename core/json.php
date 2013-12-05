<?php 

class acf_json {
	
	var $groups = array(),
		$groups_found = array(),
		$fields = array();
	
	
	
	function __construct() {
		
		// bail early if json setting is disabled
		if( !acf_get_setting('json') )
		{
			return;
		}
		
		
		// wp (need to allow for this loading before init - user may use get_field in functions.php)
		add_action('init', array($this, 'wp_init'), 1);
		
		
		// field group
		//add_action('acf/get_field_group', array($this, 'get_field_group'), 10, 1);
		add_action('acf/get_field_groups', array($this, 'get_field_groups'), 10, 1);
		add_action('acf/update_field_group', array($this, 'update_field_group'), 10, 1);
		add_action('acf/duplicate_field_group', array($this, 'update_field_group'), 10, 1);
		
	}
	
	
	function wp_init() {
		
		// vars
		$paths = acf_get_setting('load_json');
		
		
		// add default
		$paths[] = get_template_directory() . '/acf-json';
		
		
		// loop through and add to cache
		foreach( $paths as $path )
		{
			$dir = opendir( $path );
	    
		    while(false !== ( $file = readdir($dir)) )
		    {
		    	// only json files
		    	if( strpos($file, '.json') === false )
		    	{
			    	continue;
		    	}
		    	
		    	
		    	// read json
		    	$json = file_get_contents("{$path}/{$file}");
		    	
		    	
		    	// validate json
		    	if( empty($json) )
		    	{
			    	continue;
		    	}
		    	
		    	
		    	$json = json_decode($json, true);
		    	
		    	
		    	acf_register_json_field_group( $json );
		        
		    }
		}	
	}
	
	
	function update_field_group( $field_group ) {
		
		// vars
		$path = acf_get_setting('save_json');
		$file = $field_group['key'] . '.json';
		
		
		// default
		if( !$path )
		{
			$path = get_template_directory() . '/acf-json';
		}
		
		
		// bail early if dir does not exist
		if( !is_dir( $path) )
		{
			error_log( 'ACF failed to save field group to .json file. Path does not exist: ' . $path );
			return $field_group;
		}
		
		
		// get fields
		$field_group['fields'] = acf_get_fields( array('field_group' => $field_group['ID']) );
		
		
		// write file
		$f = fopen("{$path}/{$file}", 'w');
		fwrite($f, json_encode($field_group, JSON_PRETTY_PRINT));
		fclose($f);
		
		
		// return
		return $field_group;
			
	}
	
	
	/*
	*  get_field_groups
	*
	*  description
	*
	*  @type	function
	*  @date	5/12/2013
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function get_field_groups( $field_groups ) {
		
		if( !empty($this->groups) )
		{
			foreach( $this->groups as $group )
			{
				if( !in_array($group['key'], $this->groups_found) )
				{
					$field_groups[] = $group;
				}
			}
		}
		
		
		// return
		return $field_groups;
		
	}

	
}


function acf_json()
{
	global $acf_json;
	
	if( !isset($acf_json) )
	{
		$acf_json = new acf_json();
	}
	
	return $acf_json;
}

acf_json();


/*
*  acf_register_field_group 
*
*  description
*
*  @type	function
*  @date	5/12/2013
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_register_json_field_group( $field_group ) {
	
	// remove fields
	$fields = acf_extract_var($field_group, 'fields');
	
	
	// add field group
	acf_json()->groups[ $field_group['key'] ] = $field_group;
	
	
	// loop through and extract fields
	foreach( $fields as $field ) {
		
		acf_json()->fields[ $field['key'] ] = $field;
		
	}
	
}


function acf_is_json_field_group( $key ) {
	
	if( isset(acf_json()->groups[ $key ]) )
	{
		return true;
	}
	
	return false;
	
}


function acf_get_json_field_group( $key ) {
	
	return acf_json()->groups[ $key ];
	
}


function acf_is_json_field( $key ) {
	
	if( isset(acf_json()->fields[ $key ]) )
	{
		return true;
	}
	
	return false;
	
}


function acf_get_json_field( $key ) {
	
	return acf_json()->fields[ $key ];
	
}



?>