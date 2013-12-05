<?php 

function acf_register_field_group( $field_group ) {
	
	// remove fields
	$fields = acf_extract_var($field_group, 'fields');
	
	
	// add field group
	acf_json()->groups[] = $field_group;
	
	
	// loop through and extract fields
	foreach( $fields as $field ) {
		
		acf_json()->fields[] = $field;
		
	}
	
	
	// add reference
	acf_register_field_group
	
	
	echo '<pre>';
		print_r( acf_json() );
	echo '</pre>';
	die;
	
}


class acf_json {
	
	var $ref = array,
		$groups = array(),
		$fields = array();
	
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



add_action('acf/update_field_group', 'acf_json_update_field_group', 10, 1);

function acf_json_update_field_group( $field_group ) {
	
	// bail early if json setting is disabled
	if( !acf_get_setting('json') )
	{
		return $field_group;
	}
	
	
	// vars
	$path = acf_get_setting('save_json');
	$file = $field_group['ID'] . '-' . sanitize_title( $field_group['title'] ) . '.json';
	
	
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


add_action('init', 'acf_json_init', 10, 0);

function acf_json_init() {
	
	// bail early if json setting is disabled
	if( !acf_get_setting('json') )
	{
		return $field_group;
	}
	
	
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
	    	
	    	
	    	acf_register_field_group( $json );
	        
	    }
	}

	
}

?>