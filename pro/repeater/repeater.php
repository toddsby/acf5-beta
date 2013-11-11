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
		$this->category = __("Layout",'acf');
		$this->defaults = array(
			'sub_fields'	=> array(),
			'min'			=> 0,
			'max'			=> 0,
			'layout' 		=> 'table',
			'button_label'	=> __("Add Row",'acf'),
		);
		$this->l10n = array(
			'min'	=>	__("Minimum rows reached ( {min} rows )",'acf'),
			'max'	=>	__("Maximum rows reached ( {max} rows )",'acf'),
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
		
		// If field has ID, load it's sub fields
		if( $field['ID'] )
		{
			$args = array(
				'parent' => $field['ID']
			);
			
			$field['sub_fields'] = acf_get_fields( $args );
		}
		
		
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
		if( count($field['value']) < $field['min'] )
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
		if( count($field['value']) > $field['max'] )
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
					<tr class="acf-row" data-id="<?php echo $i; ?>">
						
						<?php if( $show_order ): ?>
							<td class="order"><?php echo intval($i) + 1; ?></td>
						<?php endif; ?>
						
						<?php if( $field['layout'] == 'row' ): ?>
							<td class="">
								<table class="acf-table">
						<?php endif; ?>
						
						<?php foreach( $field['sub_fields'] as $sub_field ): 
							
							// prevent repeater field from creating multiple conditional logic items for each row
							if( $i !== 'acfcloneindex' )
							{
								$sub_field['conditional_logic']['status'] = 0;
								$sub_field['conditional_logic']['rules'] = array();
							}
							
							
							// add value
							if( !empty($row[ $sub_field['key'] ]) )
							{
								$sub_field['value'] = $row[ $sub_field['key'] ];
							}
							
							
							// update prefix to allow for nested values
							$sub_field['prefix'] = "{$field['name']}[{$i}]";
							
							
							// clear ID (needed for sub fields to work!)
							//unset( $sub_field['id'] );
							
							
							acf_render_field_wrap( $sub_field, $el ); ?>
							
						<?php endforeach; ?>
						
						<?php if( $field['layout'] == 'row' ): ?>
								</table>
							</td>
						<?php endif; ?>
						
						<?php if( $show_remove ): ?>
							<td class="remove">
								<a class="acf-icon small acf-repeater-add-row" href="#" data-before="1"><i class="acf-sprite-add"></i></a>
								<a class="acf-icon small acf-repeater-remove-row" href="#"><i class="acf-sprite-remove"></i></a>
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
		<tr class="acf-field" data-option="repeater">
			<td class="acf-label">
				<label>Children</label>
				<p class="description"></p>		
			</td>
			<td class="acf-input">
				<?php 
				
				acf_get_view('field-group-fields', $args);
				
				?>
			</td>
		</tr>
		<?php
		
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
		$total = 0;
		
		if( !empty($value) )
		{
			// remove dummy field
			unset( $value['acfcloneindex'] );
			
			$i = -1;
			
			// loop through rows
			foreach( $value as $row )
			{	
				$i++;
				
				// increase total
				$total++;
				
				// loop through sub fields
				foreach( $field['sub_fields'] as $sub_field )
				{
					// get sub field data
					$v = isset( $row[$sub_field['key']] ) ? $row[$sub_field['key']] : false;
					
					
					// modify name for save
					$sub_field['name'] = "{$field['name']}_{$i}_{$sub_field['name']}";
					
					
					// update field
					acf_update_value( $v, $post_id, $sub_field );
					
				}
			}
		}
		
		
		// remove old data
		$old_total = intval( acf_get_value( $post_id, $field ) );
		
		if( $old_total > $total )
		{
			for ( $i = $total; $i < $old_total; $i++ )
			{
				foreach( $field['sub_fields'] as $sub_field )
				{
					acf_delete_value( $post_id, "{$field['name']}_{$i}_{$sub_field['name']}" );
				}
			}
		}

		
		// update $value and return to allow for the normal save function to run
		$value = $total;
		
		
		return $value;
	}
	
	
	/*
	*  update_field()
	*
	*  This filter is appied to the $field before it is saved to the database
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - the field array holding all the field options
	*  @param	$post_id - the field group ID (post_type = acf)
	*
	*  @return	$field - the modified field
	*/

	/*
function update_field( $field, $post_id )
	{
		// format sub_fields
		if( $field['sub_fields'] )
		{
			// remove dummy field
			unset( $field['sub_fields']['field_clone'] );
			
			
			// loop through and save fields
			$i = -1;
			$sub_fields = array();
			
			
			foreach( $field['sub_fields'] as $key => $f )
			{
				$i++;
				
				
				// order
				$f['order_no'] = $i;
				$f['key'] = $key;
				
				
				// save
				$f = apply_filters('acf/update_field/type=' . $f['type'], $f, $post_id ); // new filter
				
				
				// add
				$sub_fields[] = $f;
			}
			
			
			// update sub fields
			$field['sub_fields'] = $sub_fields;
			
		}
		
		
		// return updated repeater field
		return $field;
	}
*/
	
	
	/*
	*  format_value()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is passed to the create_field action
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
	/*

	function format_value( $value, $post_id, $field )
	{
		// vars
		$values = array();


		if( $value > 0 )
		{
			// loop through rows
			for($i = 0; $i < $value; $i++)
			{
				// loop through sub fields
				foreach( $field['sub_fields'] as $sub_field )
				{
					// update full name
					$key = $sub_field['key'];
					$sub_field['name'] = $field['name'] . '_' . $i . '_' . $sub_field['name'];
					
					$v = apply_filters('acf/load_value', false, $post_id, $sub_field);
					$v = apply_filters('acf/format_value', $v, $post_id, $sub_field);
					
					$values[ $i ][ $key ] = $v;
					
				}
			}
		}
		
		
		// return
		return $values;
	}
*/
	
	
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
	/*

	function format_value_for_api( $value, $post_id, $field )
	{
		// vars
		$values = array();
		
		
		if( $value > 0 )
		{
			// loop through rows
			for($i = 0; $i < $value; $i++)
			{
				// loop through sub fields
				foreach( $field['sub_fields'] as $sub_field )
				{
					// update full name
					$key = $sub_field['name'];
					$sub_field['name'] = $field['name'] . '_' . $i . '_' . $sub_field['name'];
					
					$v = apply_filters('acf/load_value', false, $post_id, $sub_field);
					$v = apply_filters('acf/format_value_for_api', $v, $post_id, $sub_field);
					
					$values[ $i ][ $key ] = $v;
					
				}
			}
		}
		
		
		// return
		return $values;
	}
*/
	
}

new acf_field_repeater();

?>