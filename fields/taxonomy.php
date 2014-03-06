<?php

class acf_field_taxonomy extends acf_field
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
		$this->name = 'taxonomy';
		$this->label = __("Taxonomy",'acf');
		$this->category = 'relational';
		$this->defaults = array(
			'taxonomy' 			=> 'category',
			'field_type' 		=> 'checkbox',
			'allow_null' 		=> 0,
			'load_save_terms' 	=> 0,
			'return_format'		=> 'id'
		);
		
		
		// do not delete!
    	parent::__construct();
    	
    	
    	// extra
		add_action('wp_ajax_acf/fields/taxonomy/query',			array($this, 'ajax_query'));
		add_action('wp_ajax_nopriv_acf/fields/taxonomy/query',	array($this, 'ajax_query'));
    	
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
			'field_key'					=>	'',
			'nonce'						=>	'',
		));
		
		
		$args = array(
			'hide_empty'	=>	false,
		);
		
		
   		// vars
   		$r = array();
   		
		
		// validate
		if( ! wp_verify_nonce($options['nonce'], 'acf_nonce') )
		{
			die();
		}
		
		
		// load field
		$field = acf_get_field( $options['field_key'] );
		
		if( !$field )
		{
			die();
		}
		
				
		// search
		if( $options['s'] )
		{
			$args['search'] = $options['s'];
		}
		
		
		// filters
		$args = apply_filters('acf/fields/taxonomy/query', $args, $field, $options['post_id']);
		$args = apply_filters('acf/fields/taxonomy/query/name=' . $field['name'], $args, $field, $options['post_id'] );
		$args = apply_filters('acf/fields/taxonomy/query/key=' . $field['key'], $args, $field, $options['post_id'] );
			
		
		// get posts
		$terms = get_terms( $field['taxonomy'], $args );
		
		
		// sort into hierachial order!
		if( is_taxonomy_hierarchical( $field['taxonomy'] ) )
		{
			// this will fail if a search has taken place because parents wont exist
			if( empty($args['search']) )
			{
				$terms = _get_term_children( 0, $terms, $field['taxonomy'] );
			}
		}
		
		
		/// append to r
		foreach( $terms as $term )
		{
			// add to json
			$r[] = array(
				'id'	=> $term->term_id,
				'text'	=> $this->get_result( $term, $field, $options['post_id'] )
			);

		}
		
		
		// return JSON
		echo json_encode( $r );
		die();
			
	}
	
	
	/*
	*  get_result
	*
	*  This function returns the HTML for a result
	*
	*  @type	function
	*  @date	1/11/2013
	*  @since	5.0.0
	*
	*  @param	$post (object)
	*  @param	$field (array)
	*  @param	$post_id (int) the post_id to which this value is saved to
	*  @return	(string)
	*/
	
	function get_result( $term, $field, $post_id = 0 ) {
		
		// get post_id
		if( !$post_id )
		{
			$post_id = get_the_ID();
		}
		
		
		// vars
		$title = '';
		
		
		// ancestors
		$ancestors = get_ancestors( $term->term_id, $field['taxonomy'] );
		
		if( !empty($ancestors) )
		{
			foreach( $ancestors as $a )
			{
				$title .= '- ';
			}
		}
		
		
		// title
		$title .= $term->name;
				
		
		// filters
		$title = apply_filters('acf/fields/taxonomy/result', $title, $term, $field, $post_id);
		$title = apply_filters('acf/fields/taxonomy/result/name=' . $field['name'] , $title, $term, $field, $post_id);
		$title = apply_filters('acf/fields/taxonomy/result/key=' . $field['key'], $title, $term, $field, $post_id);
		
		
		// return
		return $title;
	}
	
	
	/*
	*  load_value()
	*
	*  This filter is appied to the $value after it is loaded from the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value - the value found in the database
	*  @param	$post_id - the $post_id from which the value was loaded from
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$value - the value to be saved in te database
	*/
	
	function load_value( $value, $post_id, $field )
	{
		if( $field['load_save_terms'] )
		{
			$value = array();
			
			$terms = get_the_terms( $post_id, $field['taxonomy'] );
			
			if( is_array($terms) ){ foreach( $terms as $term ){
				
				$value[] = $term->term_id;
				
			}}
			
		}
		
		
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
	*  @param	$field - the field array holding all the field options
	*  @param	$post_id - the $post_id of which the value will be saved
	*
	*  @return	$value - the modified value
	*/
	
	function update_value( $value, $post_id, $field )
	{
		// vars
		if( is_array($value) )
		{
			$value = array_filter($value);
		}
		
		
		if( $field['load_save_terms'] )
		{
			// Parse values
			$value = acf_parse_types( $value );
		
			wp_set_object_terms( $post_id, $value, $field['taxonomy'], false );
		}
		
		
		return $value;
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
		if( empty($value) || !is_string($value) )
		{
			return $value;
		}
		
		
		// bail early if not formatting for template use
		if( !$template )
		{
			return $value;
		}
		
		
		// temp convert to array
		$is_array = true;
		
		if( !is_array($value) )
		{
			$is_array = false;
			$value = array( $value );
		}
		
		
		// format
		if( $field['return_format'] == 'object' )
		{
			foreach( $value as $k => $v )
			{
				$value[ $k ] = get_term( $v, $field['taxonomy'] );
			}
		}
		
		
		// de-convert from array
		if( !$is_array && isset($value[0]) )
		{
			$value = $value[0];
		}
		
		// Note: This function can be removed if not used
		return $value;
	}

	
	function render_field_select( $field ) {
		
		// Change Field into a select
		$field['type'] = 'select';
		$field['ui'] = 1;
		$field['ajax'] = 1;
		$field['choices'] = array();
		
		
		// populate choices
		if( !empty($field['value']) )
		{
			if( is_array($field['value']) )
			{
				//$taxonomies = acf_force_type_array( $field['taxonomy'] );
				
				$terms = get_terms($field['taxonomy'], array(
					'include'		=> $field['value'],
				));
				
				if( !empty($terms) )
				{
					foreach( $terms as $term )
					{
						$field['choices'][ $term->term_id ] = $this->get_result( $term, $field );
					}
				}
				
			}
			else
			{
				$term = get_term( $field['value'], $field['taxonomy'] );
				
				if( !empty($term) )
				{
					$field['choices'][ $term->term_id ] = $this->get_result( $term, $field );
				}
				
			}
		}
		
		acf_render_field( $field );
			
	}
	
	
	function render_field_checkbox( $field ) {
		
		acf_hidden_input(array(
			'type'	=> 'hidden',
			'name'	=> $field['name'],
		));
		
		
		// update name
		if( $field['field_type'] == 'checkbox' )
		{
			$field['name'] .= '[]';
		}
		
		
		// value must be array!
		$field['value'] = acf_force_type_array( $field['value'] );
		
		
		// vars
		$args = array(
			'taxonomy'     => $field['taxonomy'],
			'hide_empty'   => false,
			'style'        => 'none',
			'walker'       => new acf_taxonomy_field_walker( $field ),
		);
		
		
		// filter for 3rd party customization
		$args = apply_filters('acf/fields/taxonomy/wp_list_categories', $args, $field );
		
		?>
	
		<div class="categorychecklist-holder">
		
			<ul class="acf-checkbox-list acf-bl">
			
				<?php if( $field['field_type'] == 'radio' && $field['allow_null'] ): ?>
					<li>
						<label class="selectit">
							<input type="<?php echo $field['field_type']; ?>" name="<?php echo $field['name']; ?>" value="" /> <?php _e("None", 'acf'); ?>
						</label>
					</li>
				<?php endif; ?>
				
				<?php wp_list_categories( $args ); ?>
		
			</ul>
			
		</div>
		
		<?php

		
	}
	
	
	
	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - an array holding all the field's data
	*/
	
	function render_field( $field )
	{	
		?>
		<div class="acf-taxonomy-field" data-load_save="<?php echo $field['load_save_terms']; ?>">
		<?php
		
			if( $field['field_type'] == 'select' )
			{
				$field['multiple'] = 0;
				
				$this->render_field_select( $field );
			}
			elseif( $field['field_type'] == 'multi_select' )
			{
				$field['multiple'] = 1;
				
				$this->render_field_select( $field );
			}
			elseif( $field['field_type'] == 'radio' )
			{
				$this->render_field_checkbox( $field );
			}
			elseif( $field['field_type'] == 'checkbox' )
			{
				$this->render_field_checkbox( $field );
			}
		
		?>
		</div>
		<?php
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
	
	function render_field_options( $field )
	{
		// default_value
		acf_render_field_option( $this->name, array(
			'label'			=> __('Taxonomy','acf'),
			'type'			=> 'select',
			'name'			=> 'taxonomy',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['taxonomy'],
			'choices'		=> acf_get_taxonomies(),
		));
		
		
		// field_type
		acf_render_field_option( $this->name, array(
			'label'			=> __('Field Type','acf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'field_type',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['field_type'],
			'optgroup'		=> true,
			'choices'		=> array(
				__("Multiple Values",'acf') => array(
					'checkbox' => __('Checkbox', 'acf'),
					'multi_select' => __('Multi Select', 'acf')
				),
				__("Single Value",'acf') => array(
					'radio' => __('Radio Buttons', 'acf'),
					'select' => __('Select', 'acf')
				)
			)
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
		
		
		// allow_null
		acf_render_field_option( $this->name, array(
			'label'			=> __('Load & Save Terms to Post','acf'),
			'instructions'	=> '',
			'type'			=> 'true_false',
			'name'			=> 'load_save_terms',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['load_save_terms'],
			'message'		=> __("Load value based on the post's terms and update the post's terms on save",'acf')
		));
		
		
		// return_format
		acf_render_field_option( $this->name, array(
			'label'			=> __('Return Value','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'return_format',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['return_format'],
			'choices'		=> array(
				'object'		=>	__("Term Object",'acf'),
				'id'			=>	__("Term ID",'acf')
			),
			'layout'	=>	'horizontal',
		));
		
	}
	
		
}

new acf_field_taxonomy();


class acf_taxonomy_field_walker extends Walker
{
	// vars
	var $field = null,
		$tree_type = 'category',
		$db_fields = array ( 'parent' => 'parent', 'id' => 'term_id' );


	// construct
	function __construct( $field )
	{
		$this->field = $field;
	}

	
	// start_el
	function start_el( &$output, $term, $depth = 0, $args = array(), $current_object_id = 0)
	{
		// vars
		$selected = in_array( $term->term_id, $this->field['value'] );
		
		if( $this->field['field_type'] == 'checkbox' )
		{
			$output .= '<li><label class="selectit"><input type="checkbox" name="' . $this->field['name'] . '" value="' . $term->term_id . '" ' . ($selected ? 'checked="checked"' : '') . ' /> ' . $term->name . '</label>';
		}
		elseif( $this->field['field_type'] == 'radio' )
		{
			$output .= '<li><label class="selectit"><input type="radio" name="' . $this->field['name'] . '" value="' . $term->term_id . '" ' . ($selected ? 'checked="checkbox"' : '') . ' /> ' . $term->name . '</label>';
		}
		/*
elseif( $this->field['field_type'] == 'select' )
		{
			$indent = str_repeat("&mdash;", $depth);
			$output .= '<option value="' . $term->term_id . '" ' . ($selected ? 'selected="selected"' : '') . '>' . $indent . ' ' . $term->name . '</option>';
		}
*/
		
	}
	
	
	//end_el
	function end_el( &$output, $term, $depth = 0, $args = array() )
	{
		if( in_array($this->field['field_type'], array('checkbox', 'radio')) )
		{
			$output .= '</li>';
		}
		
		$output .= "\n";
	}
	
	
	// start_lvl
	function start_lvl( &$output, $depth = 0, $args = array() )
	{
		// indent
		//$output .= str_repeat( "\t", $depth);
		
		
		// wrap element
		if( in_array($this->field['field_type'], array('checkbox', 'radio')) )
		{
			$output .= '<ul class="children acf-bl">' . "\n";
		}
	}

	
	// end_lvl
	function end_lvl( &$output, $depth = 0, $args = array() )
	{
		// indent
		//$output .= str_repeat( "\t", $depth);
		
		
		// wrap element
		if( in_array($this->field['field_type'], array('checkbox', 'radio')) )
		{
			$output .= '</ul>' . "\n";
		}
	}
	
}

?>