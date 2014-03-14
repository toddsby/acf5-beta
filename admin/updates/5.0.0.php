<?php 

/*
*  Upgrade to version 5.0.0
*
*  @type	upgrade
*  @date	20/02/2014
*  @since	5.0.0
*
*  @param	n/a
*  @return	n/a
*/

// Exit if accessed directly
if( !defined('ABSPATH') ) exit;


// global
global $wpdb;


// migrate field groups
$ofgs = get_posts(array(
	'numberposts' 		=> -1,
	'post_type' 		=> 'acf',
	'orderby' 			=> 'menu_order title',
	'order' 			=> 'asc',
	'suppress_filters'	=> false,
));


// populate acfs
if( $ofgs ){ foreach( $ofgs as $ofg ){
	
	// migrate field group
	$nfg = _migrate_field_group_500( $ofg );
	
	
	// get field from postmeta
	$rows = $wpdb->get_results( $wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key LIKE %s", $ofg->ID, 'field_%'), ARRAY_A);
	
	
	if( $rows )
	{
		$nfg['fields'] = array();
		
		foreach( $rows as $row )
		{
			$field = $row['meta_value'];
			$field = maybe_unserialize( $field );
			$field = maybe_unserialize( $field ); // run again for WPML
			
			
			// add parent
			$field['field_group'] = $nfg['ID'];
			
			
			// migrate field
			$field = _migrate_field_500( $field );
		}
 	}
 		
}}


/*
*  _migrate_field_group_500
*
*  description
*
*  @type	function
*  @date	20/02/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function _migrate_field_group_500( $ofg ) {
	
	// get post status
	$post_status = $ofg->post_status;
	
	
	// create new field group
	$nfg = array(
		'ID'			=> 0,
		'title'			=> $ofg->post_title,
		'menu_order'	=> $ofg->menu_order,
	);
	
	
	// location rules
	$groups = array();
	
	
	// get all rules
 	$rules = get_post_meta($ofg->ID, 'rule', false);
 	
 	if( is_array($rules) )
 	{
 		$group_no = 0;
 		
	 	foreach( $rules as $rule )
	 	{
	 		// if field group was duplicated, it may now be a serialized string!
	 		$rule = maybe_unserialize($rule);
	 		
	 		
		 	// does this rule have a group?
		 	// + groups were added in 4.0.4
		 	if( !isset($rule['group_no']) )
		 	{
			 	$rule['group_no'] = $group_no;
			 	
			 	// sperate groups?
			 	if( get_post_meta($ofg->ID, 'allorany', true) == 'any' )
			 	{
				 	$group_no++;
			 	}
		 	}
		 	
		 	
		 	// extract vars
		 	$group = acf_extract_var( $rule, 'group_no' );
		 	$order = acf_extract_var( $rule, 'order_no' );
		 	
		 	
		 	// add to group
		 	$groups[ $group ][ $order ] = $rule;
		 	
		 	
		 	// sort rules
		 	ksort( $groups[ $group ] );
 	
	 	}
	 	
	 	// sort groups
		ksort( $groups );
 	}
 	
 	$nfg['location'] = $groups;
 	
 	
	// settings
 	$position = get_post_meta($ofg->ID, 'position', true);
 	if( $position )
	{
		$nfg['position'] = $position;
	}
	
	$layout = get_post_meta($ofg->ID, 'layout', true);
 	if( $layout )
	{
		// change no_box to seamless
		if( $layout == 'no_box' )
		{
			$layout = 'seamless';
		}
		
		// layout is now style
		$nfg['style'] = $layout;
	}
	
	$hide_on_screen = get_post_meta($ofg->ID, 'hide_on_screen', true);
 	if( $hide_on_screen )
	{
		$hide_on_screen = maybe_unserialize($hide_on_screen);
		$nfg['hide_on_screen'] = $hide_on_screen;
	}
	
	
	// save field group
	$nfg = acf_update_field_group($nfg);
	
	
	// return
	return $nfg;
}


/*
*  _migrate_field_500
*
*  description
*
*  @type	function
*  @date	20/02/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function _migrate_field_500( $field ) {
	
	// order_no is now menu_order
	$field['menu_order'] = acf_extract_var( $field, 'order_no' );
	
	
	// conditional logic has changed
	if( !empty($field['conditional_logic']['status']) )
	{
		if( is_array($field['conditional_logic']['rules']) )
	 	{
	 		$group = 0;
	 		$all_or_any = $field['conditional_logic']['all_or_any'];
	 		
		 	foreach( $field['conditional_logic']['rules'] as $rule )
		 	{
			 	// sperate groups?
			 	if( $all_or_any == 'any' )
			 	{
				 	$group++;
			 	}
			 	
			 	
			 	// add to group
			 	$groups[ $group ][] = $rule;
	 	
		 	}
		 	
		 	// sort groups
			ksort( $groups );
	 	}
	}
	else
	{
		$field['conditional_logic'] = 0;
	}
	
	
	// image / file settings
	if( $field['type'] == 'image' || $field['type'] == 'file' ) {
		
		// object is now array
		if( $field['return_format'] == 'object' ) {
			
			$field['return_format'] = 'array';
			
		}
		
	}
	
	
	// save field
	$field = acf_update_field( $field );
	
	
	// sub fields? They need formatted data
	$sub_field_groups = array();
	
	if( $field['type'] == 'repeater' )
	{
		$sub_fields = acf_extract_var($field, 'sub_fields');
		$keys = array_keys($sub_fields);
		
		foreach( $keys as $key )
		{
			$sub_field = acf_extract_var($sub_fields, $key);
			$sub_field['parent'] = $field['ID'];
			
			_migrate_field_500( $sub_field );
		}
		
		// save field again with less sub field data
		$field = acf_update_field( $field );
	}
	elseif( $field['type'] == 'flexible_content' )
	{
		$keys = array_keys($field['layouts']);
		
		foreach( $keys as $key )
		{
			$layout_key = uniqid();
			
			$field['layouts'][ $key ]['key'] = $layout_key;
			
			$sub_fields = acf_extract_var($field['layouts'][ $key ], 'sub_fields');
			$keys2 = array_keys($sub_fields);
			
			foreach( $keys2 as $key2 )
			{
				$sub_field = acf_extract_var($sub_fields, $key2);
				$sub_field['parent'] = $field['ID'];
				$sub_field['parent_layout'] = $layout_key;
				
				_migrate_field_500( $sub_field );
			}
		}
		
		// save field again with less sub field data
		$field = acf_update_field( $field );
		
	}
	
	
	// return
	return $field;
}

?>