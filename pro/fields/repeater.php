<?php

class acf_field_repeater extends acf_field
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
		$this->name = 'repeater';
		$this->label = __("Repeater",'acf');
		$this->category = 'layout';
		$this->defaults = array(
			'sub_fields'	=> array(),
			'min'			=> 0,
			'max'			=> 0,
			'layout' 		=> 'table',
			'button_label'	=> __("Add Row",'acf'),
		);
		$this->l10n = array(
			'min'	=>	__("Minimum rows reached ({min} rows)",'acf'),
			'max'	=>	__("Maximum rows reached ({max} rows)",'acf'),
		);
		
		
		// do not delete!
    	parent::__construct();
	}
		
	
	/*
	*  load_field()
	*
	*  This filter is appied to the $field after it is loaded from the database
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$field - the field array holding all the field options
	*/
	
	function load_field( $field ) {
		
		$field['sub_fields'] = acf_get_fields( $field );
		
		
		// return
		return $field;
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
	
	function render_field( $field ) {
		
		// value may be false
		if( !is_array($field['value']) )
		{
			$field['value'] = array();
		}
		
		
		// populate the empty row data (used for acfcloneindex and min setting)
		$empty_row = array();
		
		foreach( $field['sub_fields'] as $sub_field )
		{
			$sub_value = false;
			
			if( !empty($sub_field['default_value']) )
			{
				$sub_value = $sub_field['default_value'];
			}
			
			$empty_row[ $sub_field['key'] ] = $sub_value;
		}
		
		
		// If there are less values than min, populate the extra values
		if( $field['min'] )
		{
			for( $i = 0; $i < $field['min']; $i++ )
			{
				// continue if already have a value
				if( array_key_exists($i, $field['value']) )
				{
					continue;
				}
				
				
				// populate values
				$field['value'][ $i ] = $empty_row;
				
			}
		}
		
		
		// If there are more values than man, remove some values
		if( $field['max'] )
		{
			for( $i = 0; $i < count($field['value']); $i++ )
			{
				if( $i >= $field['max'] )
				{
					unset( $field['value'][ $i ] );
				}
			}
		}
		
		
		// setup values for row clone
		$field['value']['acfcloneindex'] = $empty_row;
		
		
		// show columns
		$show_order = true;
		$show_remove = true;
		
		
		if( $field['max'] )
		{
			if( $field['max'] == 1 )
			{
				$show_order = false;
			}
			
			if( $field['max'] <= $field['min'] )
			{
				$show_remove = false;
			}
		}
		
		
		// field wrap
		$el = 'td';
		if( $field['layout'] == 'row' )
		{
			$el = 'tr';
		}
		
		
		// hidden input
		acf_hidden_input(array(
			'type'	=> 'hidden',
			'name'	=> $field['name'],
		));
		
		?>
		<div <?php acf_esc_attr_e(array( 'class' => 'acf-repeater', 'data-min' => $field['min'], 'data-max'	=> $field['max'] )); ?>>
		<table <?php acf_esc_attr_e(array( 'class' => "acf-table acf-input-table {$field['layout']}-layout" )); ?>>
			
			<?php if( $field['layout'] == 'table' ): ?>
				<thead>
					<tr>
						<?php if( $show_order ): ?>
							<th class="order"></th>
						<?php endif; ?>
						
						<?php foreach( $field['sub_fields'] as $sub_field ): 
							
							$atts = array(
								'class'		=> "acf-th acf-th-{$sub_field['name']}",
								'data-key'	=> $sub_field['key'],
							);
							
							
							// Add custom width
							if( count($field['sub_fields']) > 1 && !empty($sub_field['width']) )
							{
								$atts['width'] = "{$sub_field['width']}%";
							}
								
							?>
							
							<th <?php acf_esc_attr_e( $atts ); ?>>
								<?php acf_the_field_label( $sub_field ); ?>
								<?php if( $sub_field['instructions'] ): ?>
									<p class="description"><?php echo $sub_field['instructions']; ?></p>
								<?php endif; ?>
							</th>
							
						<?php endforeach; ?>

						<?php if( $show_remove ): ?>
							<th class="remove"></th>
						<?php endif; ?>
					</tr>
				</thead>
			<?php endif; ?>
			
			<tbody>
				<?php foreach( $field['value'] as $i => $row ): ?>
					<tr class="acf-row<?php echo ($i === 'acfcloneindex') ? ' clone' : ''; ?>">
						
						<?php if( $show_order ): ?>
							<td class="order" title="<?php _e('Drag to reorder','acf'); ?>"><?php echo intval($i) + 1; ?></td>
						<?php endif; ?>
						
						<?php if( $field['layout'] == 'row' ): ?>
							<td class="acf-table-wrap">
								<table class="acf-table">
						<?php endif; ?>
						
						<?php foreach( $field['sub_fields'] as $sub_field ): 
							
							// prevent repeater field from creating multiple conditional logic items for each row
							if( $i !== 'acfcloneindex' )
							{
								$sub_field['conditional_logic'] = 0;
							}
							
							
							// add value
							if( !empty($row[ $sub_field['key'] ]) )
							{
								$sub_field['value'] = $row[ $sub_field['key'] ];
							}
							
							
							// update prefix to allow for nested values
							$sub_field['prefix'] = "{$field['name']}[{$i}]";
							
							
							// render input
							acf_render_field_wrap( $sub_field, $el ); ?>
							
						<?php endforeach; ?>
						
						<?php if( $field['layout'] == 'row' ): ?>
								</table>
							</td>
						<?php endif; ?>
						
						<?php if( $show_remove ): ?>
							<td class="remove">
								<a class="acf-icon small acf-repeater-add-row" href="#" data-before="1" title="<?php _e('Add row','acf'); ?>"><i class="acf-sprite-add"></i></a>
								<a class="acf-icon small acf-repeater-remove-row" href="#" title="<?php _e('Remove row','acf'); ?>"><i class="acf-sprite-remove"></i></a>
							</td>
						<?php endif; ?>
						
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<ul class="acf-hl acf-clearfix">
			<li class="acf-fr">
				<a href="#" class="acf-button blue acf-repeater-add-row"><?php echo $field['button_label']; ?></a>
			</li>
		</ul>
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
	
	function render_field_options( $field ) {
		
		// vars
		$args = array(
			'fields' => $field['sub_fields']
		);
		
		
		?>
		<tr class="acf-field" data-option="repeater" data-name="sub_fields">
			<td class="acf-label">
				<label><?php _e("Sub Fields",'acf'); ?></label>
				<p class="description"></p>		
			</td>
			<td class="acf-input">
				<?php 
				
				acf_get_view('field-group-fields', $args);
				
				?>
			</td>
		</tr>
		<?php
		
		
		// rows
		$field['min'] = empty($field['min']) ? '' : $field['min'];
		$field['max'] = empty($field['max']) ? '' : $field['max'];
		
		
		
		// min
		acf_render_field_option( $this->name, array(
			'label'			=> __('Minimum Rows','acf'),
			'instructions'	=> '',
			'type'			=> 'number',
			'name'			=> 'min',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['min'],
			'placeholder'	=> '0',
		));
		
		
		// max
		acf_render_field_option( $this->name, array(
			'label'			=> __('Maximum Rows','acf'),
			'instructions'	=> '',
			'type'			=> 'number',
			'name'			=> 'max',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['max'],
			'placeholder'	=> '0',
		));
		
		
		// layout
		acf_render_field_option( $this->name, array(
			'label'			=> __('Layout','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'layout',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['layout'],
			'layout'		=> 'horizontal',
			'choices'		=> array(
				'table'			=> __('Table','acf'),
				'row'			=> __('Block','acf')
			)
		));
		
		
		// button_label
		acf_render_field_option( $this->name, array(
			'label'			=> __('Button Label','acf'),
			'instructions'	=> '',
			'type'			=> 'text',
			'name'			=> 'button_label',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['button_label'],
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
		if( empty($value) ) {
			
			return $value;
			
		}
		
		
		// vars
		$values = array();
		$format = true;
		$format_template = $template;
		
		
		if( $value > 0 ) {
			
			// loop through rows
			for( $i = 0; $i < $value; $i++ ) {
				
				// create empty array
				$values[ $i ] = array();
				
				
				// loop through sub fields
				foreach( $field['sub_fields'] as $sub_field ) {
					
					// var
					$k = $template ? $sub_field['name'] : $sub_field['key'];
					
					
					// update full name
					$sub_field['name'] = "{$field['name']}_{$i}_{$sub_field['name']}";
					
					
					// get value
					$values[ $i ][ $k ] = acf_get_value( $post_id, $sub_field, $format, $format_template );
					
				}
				// foreach
				
			}
			// for
			
		}
		// if
		
		
		// return
		return $values;
	}
	
	
	/*
	*  validate_value
	*
	*  description
	*
	*  @type	function
	*  @date	11/02/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function validate_value( $valid, $value, $field, $input ){
		
		// remove acfcloneindex
		if( isset($value['acfcloneindex']) )
		{
			unset($value['acfcloneindex']);
		}
		
		
		// valid
		if( $field['required'] && empty($value) )
		{
			$valid = false;
		}
		
		
		// check sub fields
		if( !empty($field['sub_fields']) && !empty($value) )
		{
			$keys = array_keys($value);
			
			foreach( $keys as $i )
			{
				foreach( $field['sub_fields'] as $sub_field )
				{
					// vars
					$k = $sub_field['key'];
					
					
					// test sub field exists
					if( !isset($value[ $i ][ $k ]) )
					{
						continue;
					}
					
					
					// validate
					acf_validate_value( $value[ $i ][ $k ], $sub_field, "{$input}[{$i}][{$k}]" );
				}
				
			}
			
		}
		
		return $valid;
		
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
	
	function update_value( $value, $post_id, $field ) {
		
		// remove acfcloneindex
		if( isset($value['acfcloneindex']) )
		{
			unset($value['acfcloneindex']);
		}
		
		
		// vars
		$total = 0;
		
		
		// update sub fields
		if( !empty($value) ) {
			
			// $i
			$i = -1;
			
			
			// loop through rows
			foreach( $value as $row ) {	
				
				// $i
				$i++;
				
				
				// increase total
				$total++;
				
				
				// loop through sub fields
				if( !empty($field['sub_fields']) ) {
				
					foreach( $field['sub_fields'] as $sub_field ) {
						
						// value
						$v = false;
						
						
						// key (backend)
						if( isset($row[ $sub_field['key'] ]) ) {
							
							$v = $row[ $sub_field['key'] ];
							
						} elseif( isset($row[ $sub_field['name'] ]) ) {
							
							$v = $row[ $sub_field['name'] ];
							
						}
						
						
						// modify name for save
						$sub_field['name'] = "{$field['name']}_{$i}_{$sub_field['name']}";
						
						
						// update field
						acf_update_value( $v, $post_id, $sub_field );
						
					}
					// foreach
					
				}
				// if
				
			}
			// foreach
			
		}
		// if
		
		
		// remove old data
		$old_total = intval( acf_get_value( $post_id, $field ) );
		
		if( $old_total > $total ) {
			
			for ( $i = $total; $i < $old_total; $i++ ) {
				
				foreach( $field['sub_fields'] as $sub_field ) {
					
					acf_delete_value( $post_id, "{$field['name']}_{$i}_{$sub_field['name']}" );
				
				}
				// foreach
			
			}
			// for
			
		}
		// if

		
		// update $value and return to allow for the normal save function to run
		$value = $total;
		
		
		// return
		return $value;
	}

}

new acf_field_repeater();

?>