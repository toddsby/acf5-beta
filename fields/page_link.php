<?php

class acf_field_page_link extends acf_field
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
		$this->name = 'page_link';
		$this->label = __("Page Link",'acf');
		$this->category = 'relational';
		$this->defaults = array(
			'post_type'		=> array(),
			'taxonomy'		=> array(),
			'allow_null' 	=> 0,
			'multiple'		=> 0,
		);
		
		
		// do not delete!
    	parent::__construct();
    	
    	
    	// extra
		add_action('wp_ajax_acf/fields/page_link/query',			array($this, 'ajax_query'));
		add_action('wp_ajax_nopriv_acf/fields/page_link/query',		array($this, 'ajax_query'));
		
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
			'post_id'					=> 0,
			's'							=> '',
			'lang'						=> false,
			'field_key'					=> '',
			'nonce'						=> '',
		));
		
		
		// args
		$args = array(
			'posts_per_page'			=> -1,
			'post_type'					=> 'post',
			'orderby'					=> 'menu_order title',
			'order'						=> 'ASC',
			'post_status'				=> 'any',
			'suppress_filters'			=> false,
			'update_post_meta_cache'	=> false,
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
		$args = apply_filters('acf/fields/page_link/query', $args, $field, $options['post_id']);
		$args = apply_filters('acf/fields/page_link/query/name=' . $field['name'], $args, $field, $options['post_id'] );
		$args = apply_filters('acf/fields/page_link/query/key=' . $field['key'], $args, $field, $options['post_id'] );
		
		
		// find array of post_type
		$post_types = $args['post_type'];
		
		if( !is_array($post_types) )
		{
			$post_types = array( $post_types );
		}
		
		
		// add archives to $r
		$archives = array();
		$archives[] = array(
			'id'	=> home_url(),
			'text'	=> home_url()
		);
		
		foreach( $post_types as $post_type )
		{
			$archive_link = get_post_type_archive_link( $post_type );
			
			if( $archive_link )
			{
				$archives[] = array(
					'id'	=> $archive_link,
					'text'	=> $archive_link
				);
			}
		}
		
		$r[] = array(
			'text'		=> __('Archives', 'acf'),
			'children'	=> $archives
		);
		
		
		// get posts
		$posts = get_posts( $args );
		
		foreach( $post_types as $post_type )
		{
			// vars
			$post_type_object = get_post_type_object( $post_type );
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
				$title = apply_filters('acf/fields/page_link/result', $title, $post, $field, $options['post_id']);
				$title = apply_filters('acf/fields/page_link/result/name=' . $field['name'] , $title, $post, $field, $options['post_id']);
				$title = apply_filters('acf/fields/page_link/result/key=' . $field['key'], $title, $post, $field, $options['post_id']);
				
				
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
					'text'		=> $post_type_object->labels->singular_name,
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
		
		
		// value
		if( !empty($field['value']) )
		{
			// force value to array
			$field['value'] = acf_force_type_array( $field['value'] );
			
			
			// load posts in 1 query to save multiple DB calls from following code
			$posts = get_posts(array(
				'posts_per_page'	=> -1,
				'post_type'			=> acf_get_post_types(),
				'post_status'		=> 'any',
				'post__in'			=> $field['value'],
				'orderby'			=> 'post__in'
			));
			
			
			foreach( $field['value'] as $k => $v )
			{
				if( is_numeric($v) )
				{
					$field['choices'][ $v ] = get_the_title( $v );
				}
				else
				{
					$field['choices'][ $v ] = $v;
				}
			}
			
			
			// convert back from array if neccessary
			if( !$field['multiple'] )
			{
				$field['value'] = array_shift($field['value']);
			}
		}
		
		
		// render
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
		
		// post_type
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
			'placeholder'	=> __("All post types",'acf'),
		));
		
		
		// taxonomy
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
			'placeholder'	=> __("No taxonomy filter",'acf'),
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
	*  @param	$value (mixed) the value which was loaded from the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @param	$template (boolean) true if value requires formatting for front end template function
	*
	*  @return	$value (mixed) the modified value
	*/
	
	function format_value( $value, $post_id, $field, $template ) {
		
		// bail early if no value
		if( empty($value) )
		{
			return $value;
		}
		
		
		// bail early if not formatting for template use
		if( !$template )
		{
			return $value;
		}
		
		
		// force value to array
		$value = acf_force_type_array( $value );
		
		
		// load posts in 1 query to save multiple DB calls from following code
		$posts = get_posts(array(
			'posts_per_page'	=> -1,
			'post_type'			=> acf_get_post_types(),
			'post_status'		=> 'any',
			'post__in'			=> $value,
			'orderby'			=> 'post__in'
		));
		
		
		foreach( $value as $k => $v )
		{
			if( is_numeric($v) )
			{
				$value[ $k ] = get_post( $v );
			}
			else
			{
				//$field['choices'][ $v ] = $v;
			}
		}
		
		
		// convert back from array if neccessary
		if( !$field['multiple'] )
		{
			$value = array_shift($value);
		}
				
		
		// return value
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
		
		
		// force value to array
		$value = acf_force_type_array( $value );
		
					
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
			
			
		// convert back from array if neccessary
		if( !$field['multiple'] )
		{
			$value = array_shift($value);
		}
		
		
		return $value;
	}
	
}

new acf_field_page_link();

?>