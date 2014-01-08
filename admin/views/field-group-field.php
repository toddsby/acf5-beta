<?php 

global $post;


// get vars ($field)
extract( $args );


// vars
$field['prefix'] = "acf_fields[{$field['ID']}]";

$atts = array(
	'class' => "field field_type-{$field['type']}",
	'data-id'	=> $field['ID'],
	'data-key'	=> $field['key'],
	'data-type'	=> $field['type'],
);

?>
<div <?php echo acf_esc_attr( $atts ); ?>>
	
	<div class="field-meta acf-hidden">
		<input type="hidden" class="input-ID" name="<?php echo $field['prefix']; ?>[ID]" value="<?php echo $field['ID']; ?>" />
		<input type="hidden" class="input-key" name="<?php echo $field['prefix']; ?>[key]" value="<?php echo $field['key']; ?>" />
		<input type="hidden" class="input-parent" name="<?php echo $field['prefix']; ?>[parent]" value="<?php echo $field['parent']; ?>" />
		<input type="hidden" class="input-menu_order" name="<?php echo $field['prefix']; ?>[menu_order]" value="<?php echo $field['menu_order']; ?>" />
		<input type="hidden" class="input-changed" name="<?php echo $field['prefix']; ?>[changed]" value="0" />
	</div>
	
	<ul class="field-info acf-hl acf-tbody">
		<li class="li-field_order"><span class="acf-icon"><?php echo ($field['menu_order'] + 1); ?></span></li>
		<li class="li-field_label">
			<strong>
				<a class="edit-field" title="<?php _e("Edit field",'acf'); ?>" href="#"><?php echo $field['label']; ?></a>
			</strong>
			<div class="row-options">
				<span><a class="edit-field" title="<?php _e("Edit field",'acf'); ?>" href="#"><?php _e("Edit",'acf'); ?></a> |</span>
				<span><a class="clone-field" title="<?php _e("Duplicate field",'acf'); ?>" href="#"><?php _e("Duplicate",'acf'); ?></a> | </span>
				<span><a class="move-field" title="<?php _e("Move field to another group",'acf'); ?>" href="#"><?php _e("Move",'acf'); ?></a> | </span>
				<span><a class="delete-field" title="<?php _e("Delete field",'acf'); ?>" href="#"><?php _e("Delete",'acf'); ?></a></span>
			</div>
		</li>
		<li class="li-field_name"><?php echo $field['name']; ?></li>
		<li class="li-field_type">
			<?php if( acf_field_type_exists($field['type']) ): ?>
				<?php echo $field['type']; ?>
			<?php else: ?>
				<b><?php _e('Error', 'acf'); ?></b> <?php _e('Field type does not exist', 'acf'); ?>
			<?php endif; ?>
		</li>	
	</ul>
	
	<div class="field-options">			
		<table class="acf-table">
			<tbody>
				<?php 
		
				// label
				acf_render_field_wrap(array(
					'label'			=> __('Field Label','acf'),
					'instructions'	=> __('This is the name which will appear on the EDIT page','acf'),
					'required'		=> 1,
					'type'			=> 'text',
					'name'			=> 'label',
					'prefix'		=> $field['prefix'],
					'value'			=> $field['label'],
				), 'tr');
				
				
				// name
				acf_render_field_wrap(array(
					'label'			=> __('Field Name','acf'),
					'instructions'	=> __('Single word, no spaces. Underscores and dashes allowed','acf'),
					'required'		=> 1,
					'type'			=> 'text',
					'name'			=> 'name',
					'prefix'		=> $field['prefix'],
					'value'			=> $field['name'],
				), 'tr');
				
				
				// type
				acf_render_field_wrap(array(
					'label'			=> __('Field Type','acf'),
					'instructions'	=> '',
					'required'		=> 1,
					'type'			=> 'select',
					'name'			=> 'type',
					'prefix'		=> $field['prefix'],
					'value'			=> $field['type'],
					'choices' 		=> acf_get_field_types(),
				), 'tr');
				
				
				// instructions
				acf_render_field_wrap(array(
					'label'			=> __('Instructions','acf'),
					'instructions'	=> __('Instructions for authors. Shown when submitting data','acf'),
					'type'			=> 'textarea',
					'name'			=> 'instructions',
					'prefix'		=> $field['prefix'],
					'value'			=> $field['instructions'],
				), 'tr');
				
				
				// required
				acf_render_field_wrap(array(
					'label'			=> __('Required?','acf'),
					'instructions'	=> '',
					'type'			=> 'radio',
					'name'			=> 'required',
					'prefix'		=> $field['prefix'],
					'value'			=> $field['required'],
					'choices'		=> array(
										1	=> __("Yes",'acf'),
										0	=> __("No",'acf'),
					),
					'layout'		=> 'horizontal',
				), 'tr');
				
				
				// custom field options
				acf_render_field_options( $field );
				
				?>
				<tr class="field_save">
					<td class="acf-label"></td>
					<td class="acf-field">
						<ul class="acf-hl acf-clearfix">
							<li>
								<a class="edit-field acf-button grey" title="<?php _e("Close Field",'acf'); ?>" href="#"><?php _e("Close Field",'acf'); ?></a>
							</li>
						</ul>
					</td>
				</tr>
			</tbody>
		</table>
	</div>	

</div>