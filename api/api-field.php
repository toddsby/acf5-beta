<?php

class acf_field_api
{
	
	/*
	*  __construct
	*
	*  Initialize filters, action, variables and includes
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
		// field
		add_filter( 'acf/load_field',			array( $this, 'load_field'), 5, 2 );
		add_filter( 'acf/update_field',			array( $this, 'update_field'), 5, 1 );
		add_action( 'acf/delete_field',			array( $this, 'delete_field'), 5, 1 );
		add_action( 'acf/render_field',			array( $this, 'render_field'), 5, 1 );
		add_action( 'acf/render_field_options',	array( $this, 'render_field_options'), 5, 1 );
		
		
		// helpers
		add_filter( 'acf/get_fields',			array( $this, 'get_fields'), 5, 2 );
		add_filter( 'acf/get_valid_field',		array( $this, 'get_valid_field'), 5, 1 );
		
		
		// WP filters
		// Note: find out why this is needed
		add_filter( 'wp_unique_post_slug',		array( $this, 'wp_unique_post_slug'), 5, 6 ); 
	}
	
	
	/*
	*  get_valid_field
	*
	*  This function will fill in any missing keys to the $field array making it valid
	*
	*  @type	action (acf/get_field_defaults)
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	$field (array)
	*  @return	$field (array)
	*/

	
	function get_valid_field( $field ) {
		
		// $field must be an array
		if( !is_array($field) )
		{
			$field = array();
		}
		
		
		// defaults
		$field = acf_parse_args($field, array(
			'ID'				=> 0,
			'key'				=> '',
			'label'				=> '',
			'name'				=> '',
			'type'				=> 'text',
			'value'				=> null,
			'menu_order'		=> 0,
			'instructions'		=> '',
			'required'			=> 0,
			'id'				=> '',
			'class'				=> '',
			'conditional_logic'	=> 0,
			'parent'			=> 0,
			'ancestors'			=> array(),
			'field_group'		=> 0,
			'field_name'		=> '',
			'prefix'			=> '',
		));
		
		
		
		// id
		if( !$field['id'] )
		{
			$field['id'] = "acf-field-{$field['name']}";
		}
		
		
		// backup name to field name
		$field['field_name'] = $field['name'];
		
		
		// Parse Values
		//$field = apply_filters( 'acf/parse_types', $field );
		
		
		// field specific defaults
		$field = apply_filters( "acf/get_valid_field/type={$field['type']}", $field );
		
		
		// return
		return $field;
	}
	
	
	
	/*
	*  render_field
	*
	*  This function will render a field input
	*
	*  @type	action (acf/render_field)
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	$field (array)
	*  @return	N/A
	*/
	
	function render_field( $field ) {
	
		// get valid field
		$field = acf_get_valid_field( $field );
		
		
		// class
		//$field['class'] .= ' ' . $field['type'];
		
		
		// update the $field's name based on the key and prefix
		// $field['name'] has been used so far as a nice name for element attributes such as css.
		// now, however, the name is more useful as the $_POST name
		// this allows a plugin dev to use the field name for $_POST data, whilst ACF can use the field key
		if( $field['ID'] )
		{
			$field['name'] = $field['ID'];
		}
		elseif( $field['key'] )
		{
			$field['name'] = "field_{$field['key']}";
		}

		
		// prefix
		if( $field['prefix'] )
		{
			$field['name'] = "{$field['prefix']}[{$field['name']}]";
		}
		
		
		// create field specific html
		do_action('acf/render_field/type=' . $field['type'], $field);
		
		
		// conditional logic (todo: move away from this function maybe)
		if( is_array($field['conditional_logic']) && $field['conditional_logic']['status'] )
		{
			$field['conditional_logic']['field'] = $field['key'];
			
			?>
			<script type="text/javascript">
			(function($) {
				
				acf.conditional_logic.items.push(<?php echo json_encode($field['conditional_logic']); ?>);
				
			})(jQuery);	
			</script>
			<?php
		}
		
	}
	
	
	/*
	*  render_field_options
	*
	*  This function will render the available field options using an action to trigger the field's render function
	*
	*  @type	action (acf/render_field_options)
	*  @date	23/01/13
	*  @since	3.6.0
	*
	*  @param	$field (array)
	*  @return	n/a
	*/
	
	function render_field_options( $field ) {
		
		do_action("acf/render_field_options/type={$field['type']}", $field);
	}
	
	
	/*
	*  get_fields
	*
	*  This function will return an array of fields for the given args. Similar to the WP get_posts function
	*
	*  @type	action (acf/get_fields)
	*  @date	2/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/

	function get_fields( $fields = array(), $options = array() ) {
		
		// defaults
		$options = wp_parse_args($options, array(
			'field_group'	=> 0,
			'parent'		=> 0,
			'field_key'		=> '',
		));
		
		
		// vars
		$args = array(
			'posts_per_page'	=> -1,
			'post_type'			=> 'acf-field',
			'orderby'			=> 'menu_order',
			'order'				=> 'ASC',
			'suppress_filters'	=> false
		);
		
		
		// field_group
		if( $options['field_group'] )
		{
			$args['post_parent'] = $options['field_group'];
		}
		
		
		// sub_field
		if( $options['parent'] )
		{
			$args['post_parent'] = $options['parent'];
		}
		
		
		// field key lookup
		if( $options['field_key'] )
		{
			$args['acf_field_key'] = $options['field_key'];
			add_filter('posts_where', array( $this, 'posts_where'), 0, 2 );
		}
		
		
		// load fields
		$posts = get_posts( $args );
		
		if( $posts )
		{
			foreach( $posts as $post )
			{
				$fields[] = acf_get_field( $post );
			}	
		}
		
		
		// return
		return $fields;
		
	}
	
	
	function posts_where( $where, $wp_query )
	{
		// global
		global $wpdb;
		
		if( $field_key = $wp_query->get('acf_field_key') )
		{
			$where .= $wpdb->prepare(" AND {$wpdb->posts}.post_name = %s", $field_key );
        }
        elseif( $field_name = $wp_query->get('acf_field_name') )
		{
			$where .= $wpdb->prepare(" AND {$wpdb->posts}.post_excerpt = %s", $field_name );
        }
        
	    return $where;
	    
	}
	
	
	/*
	*  load_field
	*
	*  This function will return a of field for the given selector
	*
	*  @type	function
	*  @date	30/09/13
	*  @since	5.0.0
	*
	*  @param	$field (array)
	*  @param	$selector (mixed)
	*  @return	(array)
	*/
	
	function load_field( $field = array(), $selector = false ) {
		
		// vars
		$post = false;
		$cache_key = '';
		$found = false;
		
		
		// try cache
		if( !$selector )
		{
			global $post;
			$cache_key = "load_field/ID={$post->ID}";
		}
		elseif( is_object($selector) )
		{
			$post = $selector;
			$cache_key = "load_field/ID={$post->ID}";
		}
		elseif( is_numeric($selector) )
		{
			$cache_key = "load_field/ID={$selector}";
		}
		elseif( is_string($selector) )
		{
			$cache_key = "load_field/key={$selector}";
		}
		
		$cache = wp_cache_get( $cache_key, 'acf', false, $found );
		
		if( $found )
		{
			return $cache;
		}
		
		
		// field may already be loaded by earlier filter (exported JSON)
		if( empty($field) )
		{
		
			// $post = 'field_123'
			if( is_string($selector) )
			{
				$posts = acf_get_fields(array( 'field_key' => $selector ));
				if( !empty($posts) )
				{
					$field = array_pop($posts);
				}
			}
			else
			{
				// $post = $post_id
				if( is_numeric($selector) )
				{
					$post = get_post( $selector );
				}
				
				if( !is_object($post) )
				{
					echo '<pre>';
						print_r($selector);
					echo '</pre>';
					die;
				}
				
				// get data
				$data = maybe_unserialize( $post->post_content );
				
				if( is_array($data) )
				{
					$field = $data;
				}
				
				
				// update attributes
				$field['ID'] = $post->ID;
				$field['key'] = $post->post_name;
				$field['label'] = $post->post_title;
				$field['name'] = $post->post_excerpt;
				$field['menu_order'] = $post->menu_order;
				$field['ancestors'] = get_post_ancestors( $post );
				$field['parent'] = $post->post_parent;
				$field['field_group'] = end( $field['ancestors'] );
				
				
				// validate
				$field = acf_get_valid_field( $field );
				
			}
			
		}
		
		
		// If a field has been found, apply filters
		if( ! empty($field) )
		{
			// apply filters
			foreach( array('type', 'name', 'key') as $key )
			{
				// run filters
				$field = apply_filters('acf/load_field/' . $key . '=' . $field[ $key ], $field);
			}
		}
		
	
		// set cache
		wp_cache_set( $cache_key, $field, 'acf' );
		
		
		// return
		return $field;
	}
	

	/*
	*  get_field
	*
	*  @description: loads a field from the database
	*  @since 3.5.1
	*  @created: 14/10/12
	*/
	/*

	function get_field( $field, $field_key, $post_id = false )
	{
		// load cache
		if( !$field )
		{
			$field = wp_cache_get( 'get_field/key=' . $field_key, 'acf' );
		}
		
		
		// load from DB
		if( !$field )
		{
			// vars
			global $wpdb;
			
			
			// get field from postmeta
			$sql = $wpdb->prepare("SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = %s", $field_key);
			
			if( $post_id )
			{
				$sql .= $wpdb->prepare("AND post_id = %d", $post_id);
			}
	
			$rows = $wpdb->get_results( $sql, ARRAY_A );
			
			
			
			// nothing found?
			if( !empty($rows) )
			{
				$row = $rows[0];
				
				
				
				*  WPML compatibility
				*
				*  If WPML is active, and the $post_id (Field group ID) was not defined,
				*  it is assumed that the get_field functio has been called from the API (front end).
				*  In this case, the field group ID is never known and we can check for the correct translated field group
				
				
				if( defined('ICL_LANGUAGE_CODE') && !$post_id )
				{
					$wpml_post_id = icl_object_id($row['post_id'], 'acf', true, ICL_LANGUAGE_CODE);
					
					foreach( $rows as $r )
					{
						if( $r['post_id'] == $wpml_post_id )
						{
							// this row is a field from the translated field group
							$row = $r;
						}
					}
				}
				
				
				// return field if it is not in a trashed field group
				if( get_post_status( $row['post_id'] ) != "trash" )
				{
					$field = $row['meta_value'];
					$field = maybe_unserialize( $field );
					$field = maybe_unserialize( $field ); // run again for WPML
					
					
					// add field_group ID
					$field['field_group'] = $row['post_id'];
				}
				
			}
		}
		
		
		// apply filters
		$field = apply_filters('acf/get_field_defaults', $field);
		
		
		// apply filters
		foreach( array('type', 'name', 'key') as $key )
		{
			// run filters
			$field = apply_filters('acf/get_field/' . $key . '=' . $field[ $key ], $field); // new filter
		}
		
	
		// set cache
		wp_cache_set( 'get_field/key=' . $field_key, $field, 'acf' );
		
		return $field;
	}
*/
	
	
	/*
	*  update_field
	*
	*  This function will update or insert a field into the DB
	*
	*  @type	function
	*  @date	24/01/13
	*  @since	3.6.0
	*
	*  @param	$field (array)
	*  @return	(mixed)
	*/
	
	function update_field( $field ) {
		
		// $field must be an array
		if( !is_array($field) )
		{
			return false;
		}
		
		
		// validate
		$field = acf_get_valid_field( $field );
		
		
		// may have been posted. Remove slashes
		$field = wp_unslash( $field );
		
		
		// apply filters
		foreach( array('type', 'name', 'key') as $key )
		{
			// run filters
			$field = apply_filters( 'acf/update_field/' . $key . '=' . $field[ $key ], $field );
		}
		
		
		// store origional field for return
		$return = $field;
		
		
		// extract some args
		$extract = acf_extract_vars($field, array(
			'ID',
			'key',
			'label',
			'name',
			'value',
			'menu_order',
			'id',
			'class',
			'parent',
			'ancestors',
			'field_group',
			'field_name',
			'prefix',
		));
		
		
		// configure parent / field_group
		if( !$extract['parent'] )
		{
			$extract['parent'] = $extract['field_group'];
		}
		
		
		// serialize for DB
		$field = maybe_serialize( $field );
	    
	    
	    // save
	    $save = array(
	    	'ID'			=> $extract['ID'],
	    	'post_status'	=> 'publish',
	    	'post_type'		=> 'acf-field',
	    	'post_title'	=> $extract['label'],
	    	'post_name'		=> $extract['key'],
	    	'post_excerpt'	=> $extract['name'],
	    	'post_content'	=> $field,
	    	'post_parent'	=> $extract['parent'],
	    	'menu_order'	=> $extract['menu_order'],
	    );
	    
	    
	    // update the field and update the ID
	    $return['ID'] = wp_update_post( $save );
	    
	    
	    // return
	    return $return;

	}
	
	
	/*
	*  delete_field
	*
	*  This function will delete a field from the DB
	*
	*  @type	function
	*  @date	24/01/13
	*  @since	3.6.0
	*
	*  @param	$id (int)
	*  @return	(boolean)
	*/
	
	function delete_field( $id ) {
	
		return wp_delete_post( $id, true );
		
	}
	
	
	/*
	*  wp_unique_post_slug
	*
	*  This filter will allow ACF to save fields with the same post_name ( bypass wp_unique_post_slug() )
	*
	*  @type	function
	*  @date	4/11/2013
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function wp_unique_post_slug( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug ) {
		
		if( $post_type == 'acf-field' )
		{
			$slug = $original_slug;
		}
		
		return $slug;
	}
	
	
}

new acf_field_api();



/*
*  acf_get_valid_field
*
*  This function will fill in any missing keys to the $field array making it valid
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$field (array)
*  @return	$field (array)
*/

function acf_get_valid_field( $field = false ) {
	
	return apply_filters('acf/get_valid_field', $field);
}


/*
*  acf_render_field
*
*  This function will render a field input
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$field (array)
*  @return	N/A
*/

function acf_render_field( $field = false ) {
	
	do_action('acf/render_field', $field);
	
}


/*
*  acf_render_field_options
*
*  This function will render the available field options using an action to trigger the field's render function
*
*  @type	function
*  @date	23/01/13
*  @since	3.6.0
*
*  @param	$field (array)
*  @return	n/a
*/

function acf_render_field_options( $field = false ) {
	
	do_action('acf/render_field_options', $field);
	
}


/*
*  acf_get_fields
*
*  This function will return an array of fields for the given args. Similar to the WP get_posts function
*
*  @type	function
*  @date	30/09/13
*  @since	5.0.0
*
*  @param	$options (array)
*  @return	(array)
*/

function acf_get_fields( $options = false ) {

	return apply_filters('acf/get_fields', array(), $options);
	
}


/*
*  acf_get_fields
*
*  This function will return a field for the given selector. 
*
*  @type	function
*  @date	30/09/13
*  @since	5.0.0
*
*  @param	$selector (mixed)
*  @return	(array)
*/

function acf_get_field( $selector = null ) {
	
	return apply_filters('acf/load_field', array(), $selector);
	
}


/*
*  acf_update_field
*
*  This function will update a field into the DB
*
*  @type	function
*  @date	1/10/13
*  @since	5.0.0
*
*  @param	$field (array)
*  @return	(int)
*/

function acf_update_field( $field = false ) {
	
	return apply_filters( 'acf/update_field', $field );
	
}


/*
*  acf_delete_field
*
*  This function will delete a field from the databse
*
*  @type	function
*  @date	2/10/13
*  @since	5.0.0
*
*  @param	$id (int)
*  @return	(boolean)
*/

function acf_delete_field( $id )
{
	return do_action( 'acf/delete_field', $id );
}



/*
*  acf_duplicate_field
*
*  This function will duplicate a field and attach it to the given field group ID
*
*  @type	function
*  @date	17/10/13
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_duplicate_field( $selector = 0, $field_group_id = 0 ){
	
	
	// load the origional field
	$field = acf_get_field( $selector );
	
	
	// bail early if field did not load correctly
	if( empty($field) )
	{
		return false;
	}
	
	
	// update ID
	$field['ID'] = false;
	
	
	// update field group
	if( $field_group_id )
	{
		$field['parent'] = $field_group_id;
	}
	
	
	// save
	$field = acf_update_field( $field );
	
	
	// return
	return $field;
	
}

?>