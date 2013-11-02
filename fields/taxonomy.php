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
		$this->category = __("Relational",'acf');
		$this->defaults = array(
			'taxonomy' 			=> 'category',
			'field_type' 		=> 'checkbox',
			'allow_null' 		=> 0,
			'load_save_terms' 	=> 0,
			'multiple'			=> 0,
			'return_format'		=> 'id'
		);
		
		
		// do not delete!
    	parent::__construct();
    	
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
	
	function format_value_for_api( $value, $post_id, $field )
	{
		// no value?
		if( !$value )
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
		// vars
		$single_name = $field['name'];
			
			
		// multi select?
		if( $field['field_type'] == 'multi_select' )
		{
			$field['multiple'] = 1;
			$field['field_type'] = 'select';
			$field['name'] .= '[]';
		}
		elseif( $field['field_type'] == 'checkbox' )
		{
			$field['name'] .= '[]';
		}
		
		// value must be array!
		if( !is_array($field['value']) )
		{
			$field['value'] = array( $field['value'] );
		}
		
		
		// vars
		$args = array(
			'taxonomy'     => $field['taxonomy'],
			'hide_empty'   => false,
			'style'        => 'none',
			'walker'       => new acf_taxonomy_field_walker( $field ),
		);
		
		$args = apply_filters('acf/fields/taxonomy/wp_list_categories', $args, $field );
		
		?>
<div class="acf-taxonomy-field">
	<input type="hidden" name="<?php echo $single_name; ?>" value="" />
	
	<?php if( $field['field_type'] == 'select' ): ?>
		
		<select name="<?php echo $field['name']; ?>" <?php if( $field['multiple'] ): ?>multiple="multiple" size="5"<?php endif; ?>>
			<?php if( $field['allow_null'] ): ?>
				<option value=""><?php _e("None", 'acf'); ?></option>
			<?php endif; ?>
	
	<?php else: ?>
		<div class="categorychecklist-holder">
		<ul class="categorychecklist<?php if( !$field['load_save_terms'] ){ echo ' no-ajax'; } ?>">
			<?php if( $field['allow_null'] ): ?>
				<li>
					<label class="selectit">
						<input type="<?php echo $field['field_type']; ?>" name="<?php echo $field['name']; ?>" value="" /> <?php _e("None", 'acf'); ?>
					</label>
				</li>
			<?php endif; ?>
	
	<?php endif; ?>
			
			<?php wp_list_categories( $args ); ?>
	
	<?php if( $field['field_type'] == 'select' ): ?>
	
		</select>
	
	<?php else: ?>
	
		</ul>
		</div>
		
	<?php endif; ?>

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
		elseif( $this->field['field_type'] == 'select' )
		{
			$indent = str_repeat("&mdash;", $depth);
			$output .= '<option value="' . $term->term_id . '" ' . ($selected ? 'selected="selected"' : '') . '>' . $indent . ' ' . $term->name . '</option>';
		}
		
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
			$output .= '<ul class="children">' . "\n";
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