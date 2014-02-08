<?php

class acf_field_post_object extends acf_field
{
	/*
	*  __construct
	*
	*  Set name / label needed for actions / filters
	*
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function __construct()
	{
		// vars
		$this->name = 'post_object';
		$this->label = __("Post Object",'acf');
		$this->category = __("Relational",'acf');
		$this->defaults = array(
			'post_type'		=> array(),
			'taxonomy'		=> array(),
			'allow_null' 	=> 0,
			'multiple'		=> 0,
			'return_format'	=> 'object',
			'ui'			=> 1,
			'sortable'		=> 0
		);
		
		
		// do not delete!
    	parent::__construct();
    	
    	
    	// extra
		add_action('wp_ajax_acf/fields/post_object/query',			array($this, 'ajax_query'));
		add_action('wp_ajax_nopriv_acf/fields/post_object/query',	array($this, 'ajax_query'));
		
	}
	
	
	/*
	*  query_posts
	*
	*  description
	*
	*  @type	function
	*  @date	24/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function ajax_query()
   	{
   		// options
   		$options = acf_parse_args( $_GET, array(
			'post_id'					=>	0,
			's'							=>	'',
			'lang'						=>	false,
			'field_key'					=>	'',
			'nonce'						=>	'',
		));
		
		
		// args
		$args = array(
			'posts_per_page'			=>	-1,
			'post_type'					=> 'post',
			'orderby'					=>	'menu_order title',
			'order'						=>	'ASC',
			'post_status'				=>	'any',
			'suppress_filters'			=>	false,
			'update_post_meta_cache'	=>	false,
		);
		
		
   		// vars
   		$r = array();
   		
		
		// validate
		if( ! wp_verify_nonce($options['nonce'], 'acf_nonce') )
		{
			die();
		}
		
		
		// WPML
		if( $options['lang'] )
		{
			global $sitepress;
			$sitepress->switch_lang( $options['lang'] );
		}
		
		
		// load field
		$field = acf_get_field( $options['field_key'] );
		
		if( !$field )
		{
			die();
		}
		
		
		// update post_type
		$args['post_type'] = $field['post_type'];
		
		
		// load all post types by default
		if( empty($field['post_type']) )
		{
			$args['post_type'] = acf_get_post_types();
		}
		
		
		// attachment doesn't work if it is the only item in an array???
		if( is_array($args['post_type']) && count($args['post_type']) == 1 )
		{
			$args['post_type'] = $args['post_type'][0];
		}
		
		
		// create tax queries
		if( !empty($field['taxonomy']) )
		{
			$args['tax_query'] = array();
			$taxonomies = array();
			
			foreach( $field['taxonomy'] as $term )
			{
				$term = explode(':', $term);
								
				if( empty($taxonomies[ $term[0] ]) )
				{
					$taxonomies[ $term[0] ] = array();
				}
				
				$taxonomies[ $term[0] ][] = $term[1];
			}
			
			
			// now create the tax queries
			foreach( $taxonomies as $taxonomy => $terms )
			{
				$args['tax_query'][] = array(
					'taxonomy'	=> $taxonomy,
					'field'		=> 'slug',
					'terms'		=> $terms,
				);
			}
		}
		
				
		// search
		if( $options['s'] )
		{
			$args['s'] = $options['s'];
		}
		
		
		// filters
		$args = apply_filters('acf/fields/post_object/query', $args, $field, $options['post_id']);
		$args = apply_filters('acf/fields/post_object/query/name=' . $field['name'], $args, $field, $options['post_id'] );
		$args = apply_filters('acf/fields/post_object/query/key=' . $field['key'], $args, $field, $options['post_id'] );
		
		
		// find array of post_type
		$post_types = $args['post_type'];
		
		if( !is_array($post_types) )
		{
			$post_types = array( $post_types );
		}
		
		
		// get posts
		$posts = get_posts( $args );
		
		foreach( $post_types as $post_type )
		{
			// vars
			$this_posts = array();
			$this_json = array();
			
			
			$keys = array_keys($posts);
			foreach( $keys as $key )
			{
				if( $posts[ $key ]->post_type == $post_type )
				{
					$this_posts[] = acf_extract_var( $posts, $key );
				}
			}
			
			
			// bail early if no posts for this post type
			if( empty($this_posts) )
			{
				continue;
			}
			
			
			// sort into hierachial order!
			if( is_post_type_hierarchical( $post_type ) )
			{
				// this will fail if a search has taken place because parents wont exist
				if( empty($args['s']) )
				{
					$this_posts = get_page_children( 0, $this_posts );
				}
			}
			
			
			foreach( $this_posts as $post )
			{
				// title
				$title = '';
				$ancestors = get_ancestors( $post->ID, $post->post_type );
				
				if( !empty($ancestors) )
				{
					foreach( $ancestors as $a )
					{
						$title .= '- ';
					}
				}
				
				
				// title
				$title .= get_the_title( $post->ID );
				
				
				// status
				if( get_post_status( $post->ID ) != "publish" )
				{
					$title .= ' (' . get_post_status( $post->ID ) . ')';
				}
							
				
				// filters
				$title = apply_filters('acf/fields/post_object/result', $title, $post, $field, $options['post_id']);
				$title = apply_filters('acf/fields/post_object/result/name=' . $field['name'] , $title, $post, $field, $options['post_id']);
				$title = apply_filters('acf/fields/post_object/result/key=' . $field['key'], $title, $post, $field, $options['post_id']);
				
				
				// add to json
				$this_json[] = array(
					'id'	=> $post->ID,
					'text'	=> $title
				);

			}
			
			
			// add as optgroup or results
			if( count($post_types) == 1 )
			{
				$r = $this_json;
			}
			else
			{
				$r[] = array(
					'text'		=> $post_type,
					'children'	=> $this_json
				);
			}
						
		}
		
		
		// return JSON
		echo json_encode( $r );
		die();
			
	}
	
	
	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field - an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function render_field( $field ){
		
		// Change Field into a select
		$field['type'] = 'select';
		$field['ui'] = 1;
		$field['ajax'] = 1;
		$field['choices'] = array();
		
		
		// populate choices
		if( is_array($field['value']) )
		{
			$posts = get_posts(array(
				'post_type'		=> acf_get_post_types(),
				'post_status'	=> 'any',
				'post__in'		=> $field['value'],
				'orderby'		=> 'post__in'
			));
			
			if( !empty($posts) )
			{
				foreach( $posts as $p )
				{
					$field['choices'][ $p->ID ] = get_the_title( $p->ID );
				}
			}
			
		}
		else
		{
			$field['choices'][ $field['value'] ] = get_the_title($field['value']);
		}
		
		
		acf_render_field( $field );
	}
	
	
	/*
	*  render_field_options()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field	- an array holding all the field's data
	*/
	
	function render_field_options( $field ) {
		
		
		// default_value
		/*
$choices = array_merge(acf_get_post_types(), array(
			'all' => __("All",'acf')
		));
*/
		acf_render_field_option( $this->name, array(
			'label'			=> __('Filter by Post Type','acf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'post_type',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['post_type'],
			'choices'		=> acf_get_post_types(),
			'multiple'		=> 1,
			'ui'			=> 1,
			'allow_null'	=> 1,
			'placeholder'	=> 'All post types',
		));
		
		
		// default_value
		/*
$choices = wp_parse_args(acf_get_taxonomy_terms(), array(
			'all' => __("All",'acf')
		));
*/
		acf_render_field_option( $this->name, array(
			'label'			=> __('Filter by Taxonomy','acf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'taxonomy',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['taxonomy'],
			'choices'		=> acf_get_taxonomy_terms(),
			'multiple'		=> 1,
			'ui'			=> 1,
			'allow_null'	=> 1,
			'placeholder'	=> 'No taxonomy filter',
		));
		
		
		// allow_null
		acf_render_field_option( $this->name, array(
			'label'			=> __('Allow Null?','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'allow_null',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['allow_null'],
			'choices'		=> array(
				1				=> __("Yes",'acf'),
				0				=> __("No",'acf'),
			),
			'layout'	=>	'horizontal',
		));
		
		
		// multiple
		acf_render_field_option( $this->name, array(
			'label'			=> __('Select multiple values?','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'multiple',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['multiple'],
			'choices'		=> array(
				1				=> __("Yes",'acf'),
				0				=> __("No",'acf'),
			),
			'layout'	=>	'horizontal',
		));
		
		
		// sortable
		acf_render_field_option( $this->name, array(
			'label'			=> __('Allow values to be sortable','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'sortable',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['sortable'],
			'choices'		=> array(
				1				=> __("Yes",'acf'),
				0				=> __("No",'acf'),
			),
			'layout'	=>	'horizontal',
		));
		
		
		// return_format
		acf_render_field_option( $this->name, array(
			'label'			=> __('Return Format','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'return_format',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['return_format'],
			'choices'		=> array(
				'object'		=> __("Post Object",'acf'),
				'id'			=> __("Post ID",'acf'),
			),
			'layout'	=>	'horizontal',
		));
		
		
		// ajax
		/*
acf_render_field_option( $this->name, array(
			'label'			=> __('Use AJAX to lazy load choices?','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'ajax',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['ajax'],
			'choices'		=> array(
				1				=> __("Yes",'acf'),
				0				=> __("No",'acf'),
			),
			'layout'	=>	'horizontal',
		));
*/
		
	}
	
	
	/*
	*  format_value()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is passed to the render_field action
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value	- the value which was loaded from the database
	*  @param	$post_id - the $post_id from which the value was loaded
	*  @param	$field	- the field array holding all the field options
	*
	*  @return	$value	- the modified value
	*/
	
	function format_value( $value, $post_id, $field )
	{
		// empty?
		if( empty($value) )
		{
			return $value;
		}
		
		
		// convert to int
		$value = array_map('intval', $value);
		
		
		// return value
		return $value;	
	}
	
	
	
	/*
	*  format_value_for_api()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is passed back to the api functions such as the_field
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value	- the value which was loaded from the database
	*  @param	$post_id - the $post_id from which the value was loaded
	*  @param	$field	- the field array holding all the field options
	*
	*  @return	$value	- the modified value
	*/
	
	function format_value_for_api( $value, $post_id, $field ) {
		
		// no value?
		if( empty($value) )
		{
			return false;
		}
		
		
		// null?
		if( $value == 'null' )
		{
			return false;
		}
		
		
		// multiple / single
		if( is_array($value) )
		{
			// find posts (DISTINCT POSTS)
			$posts = get_posts(array(
				'numberposts' => -1,
				'post__in' => $value,
				'post_type'	=>	apply_filters('acf/get_post_types', array()),
				'post_status' => array('publish', 'private', 'draft', 'inherit', 'future'),
			));
	
			
			$ordered_posts = array();
			foreach( $posts as $post )
			{
				// create array to hold value data
				$ordered_posts[ $post->ID ] = $post;
			}
			
			
			// override value array with attachments
			foreach( $value as $k => $v)
			{
				// check that post exists (my have been trashed)
				if( !isset($ordered_posts[ $v ]) )
				{
					unset( $value[ $k ] );
				}
				else
				{
					$value[ $k ] = $ordered_posts[ $v ];
				}
			}
			
		}
		else
		{
			$value = get_post($value);
		}
		
		
		// return the value
		return $value;
	}
	
	
	/*
	*  update_value()
	*
	*  This filter is appied to the $value before it is updated in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value - the value which will be saved in the database
	*  @param	$post_id - the $post_id of which the value will be saved
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$value - the modified value
	*/
	
	function update_value( $value, $post_id, $field ) {
		
		// validate
		if( empty($value) )
		{
			return $value;
		}
		
		
		if( is_string($value) )
		{
			// string
			$value = explode(',', $value);
			
			// save value as strings, so we can clearly search for them in SQL LIKE statements
			$value = array_map('strval', $value);
			
		}
		elseif( is_object($value) && isset($value->ID) )
		{
			// object
			$value = $value->ID;
			
		}
		elseif( is_array($value) )
		{
			// array
			foreach( $value as $k => $v ){
			
				// object?
				if( is_object($v) && isset($v->ID) )
				{
					$value[ $k ] = $v->ID;
				}
			}
			
			// save value as strings, so we can clearly search for them in SQL LIKE statements
			$value = array_map('strval', $value);
			
		}
		
		
		return $value;
	}
	
}

new acf_field_post_object();

?>