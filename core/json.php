<?php 

class acf_json {
	
	function __construct() {
		
		add_action('acf/update_field_group',		array($this, 'update_field_group'), 10, 1);
		add_action('acf/duplicate_field_group',		array($this, 'update_field_group'), 10, 1);
		add_action('acf/include_fields', 			array($this, 'include_fields'), 10, 1);
		
	}
	
	
	/*
	*  update_field_group
	*
	*  This function is hooked into the acf/update_field_group action and will save all field group data to a .json file 
	*
	*  @type	function
	*  @date	10/03/2014
	*  @since	5.0.0
	*
	*  @param	$field_group (array)
	*  @return	n/a
	*/
	
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
			//error_log( 'ACF failed to save field group to .json file. Path does not exist: ' . $path );
			return;
		}
		
		
		// load fields
		$id_ref = array();
		$fields = array();
		$this->populate_fields( $field_group, $fields );
		
		
		// extract field group ID and add to ref
		$id = acf_extract_var( $field_group, 'ID' );
		$id_ref[ $id ] = $field_group['key'];
		
		
		// load fields from DB
		if( !empty($fields) )
		{
			foreach( $fields as $field )
			{
				// extract some args
				$extract = acf_extract_vars($field, array(
					'ID',
					'value',
					'menu_order',
					'id',
					'class',
					'ancestors',
					'field_group',
					'_name',
					'_input',
					'_valid',
				));

				
				// extract field ID and add to ref
				$id_ref[ $extract['ID'] ] = $field['key'];
				
				
				// update parent ID to parent key
				$field['parent'] = $id_ref[ $field['parent'] ];
				
				
				// append to fields
				$field_group['fields'][] = $field;
			}
		}
		
				
		// write file
		$f = fopen("{$path}/{$file}", 'w');
		fwrite($f, acf_json_encode( $field_group ));
		fclose($f);
		
		
		// return
		return;
			
	}
	
	
	function populate_fields( $parent, &$return ) {
		
		// get fields
		$fields = acf_get_fields( $parent );
		
		
		// load fields from DB
		if( !empty($fields) )
		{
			foreach( $fields as $field )
			{
				// append to return
				$return[] = $field;
				
				
				// attempt next level
				$this->populate_fields( $field, $return );
			}
		}
		
	}
	
	
	/*
	*  include_fields
	*
	*  This function will include any JSON files found in the active theme
	*
	*  @type	function
	*  @date	10/03/2014
	*  @since	5.0.0
	*
	*  @param	$version (int)
	*  @return	n/a
	*/
	
	function include_fields() {
		
		// validate
		if( !acf_get_setting('json') )
		{
			return;
		}
		
		
		// vars
		$paths = acf_get_setting('load_json');
		
		
		// add default
		$paths[] = get_template_directory() . '/acf-json';
		
		
		// loop through and add to cache
		foreach( $paths as $path )
		{
			// check that path exists
			if( !file_exists( $path ) )
			{
				continue;
			}
			
			
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
		    	
		    	
		    	acf_add_local_field_group( $json );
		        
		    }
		}
		
	}
	
	
}

new acf_json();

?>