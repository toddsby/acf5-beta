<?php 

class acf_json_actions {
	
	function __construct() {
		
		
		// field group
		//add_action('acf/get_field_group', array($this, 'get_field_group'), 10, 1);
		add_action('acf/get_field_groups', array($this, 'get_field_groups'), 10, 1);
		add_action('acf/update_field_group', array($this, 'update_field_group'), 10, 1);
		add_action('acf/duplicate_field_group', array($this, 'update_field_group'), 10, 1);
		
	}
	
	
	function update_field_group( $field_group ) {
		
		// vars
		$path = acf_get_setting('save_json');
		$file = $field_group['key'] . '.json';
		$id_ref = array();
		
		
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
		
		
		// extract field group ID and add to ref
		$id = acf_extract_var( $field_group, 'ID' );
		$id_ref[ $id ] = $field_group['key'];
		
		
		// load fields
		$fields = acf_get_fields_by_id( $id );
		
		
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
					'field_group'
				));

				
				// extract field ID and add to ref
				$id_ref[ $extract['ID'] ] = $field['key'];
				
				
				// update parent ID to parent key
				$field['parent'] = $id_ref[ $field['parent'] ];
				
				
				// append field
				$field_group['fields'][] = $field;
			}
		}
		
		
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
		
		// vars
		$ignore = array();
		$json = acf_json()->groups;
		
		
		// overrride field groups and populate ignore list
		if( !empty($field_groups) )
		{
			foreach( $field_groups as $k => $group )
			{
				// override
				if( isset($json[ $group['key'] ]) )
				{
					$field_groups[ $k ] = $json[ $group['key'] ];
				}
				
				$ignore[] = $group['key'];
			}
		}
		
		
		// append field groups
		if( !empty($json) )
		{
			foreach( $json as $group )
			{
				if( !in_array($group['key'], $ignore) )
				{
					$field_groups[] = $group;
				}
			}
		}
		
		
		// return
		return $field_groups;
		
	}
	
}

new acf_json_actions();


class acf_json {
	
	var $groups 		= array(),
		//$groups_found 	= array(),
		$fields 		= array(),
		//$fields_found 	= array(),
		$parents 		= array();
	
	
	/*
	*  __construct
	*
	*  A dummy constructor to ensure ACF is only initialized once
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	5.0.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function __construct()
	{
		/* Do nothing here */
	}
	
	
	/*
	*  initialize
	*
	*  The real constructor to initialize ACF
	*
	*  @type	function
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
		
	function initialize()
	{
		// validate
		if( !acf_get_setting('json') )
		{
			return;
		}
		
		
		$this->load_json();
	}
	
	
	/*
	*  load_json
	*
	*  description
	*
	*  @type	function
	*  @date	6/12/2013
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function load_json() {

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
	
}


function acf_json()
{
	global $acf_json;
	
	if( !isset($acf_json) )
	{
		$acf_json = new acf_json();
		
		$acf_json->initialize();
	}
	
	return $acf_json;
}


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
	
	
	// vars
	$menu_order = array();
	
	
	// loop through and extract fields
	foreach( $fields as $field ) {
		
		// append $parents
		acf_json()->parents[ $field['parent'] ][] = $field['key'];
		
		
		// add in menu order
		if( !isset($menu_order[ $field['parent'] ]) )
		{
			$menu_order[ $field['parent'] ] = -1;
		}
		
		$menu_order[ $field['parent'] ]++;
		
		$field['menu_order'] = $menu_order[ $field['parent'] ];
		
		
		
		$field['ancestors'] = array();
		$parent = $field['parent'];
		
		while( acf_is_json_field($parent) )
		{
			$field['ancestors'][] = $parent;
			
			$parent = acf_get_json_field( $parent )['parent'];
		}
		
		$field['ancestors'][] = $field_group['key'];
		
		
		// add in field group
		$field['field_group'] = end($field['ancestors']);
		
		
		
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


function acf_has_json_fields( $parent ) {
	
	if( !empty(acf_json()->parents[ $parent ]) )
	{
		return true;
	}
	
	return false;
	
}


function acf_get_json_fields( $parent ) {

	$fields = array();
	
	foreach( acf_json()->parents[ $parent ] as $key )
	{
		$fields[] = acf_get_field( $key );
	}
	
	return $fields;
	
}



?>