<?php 

// global
global $post;


// vars
$fields = acf_get_fields(array( 'field_group' => $post->ID ));


// add clone
$fields[] = acf_get_valid_field(array(
	'ID'	=> 'field_clone',
	'key'	=> 'field_clone',
	'label'	=> __('New Field','acf'),
	'name'	=> 'new_field',
	'type'	=> 'text',
));

?>
<div class="acf-hidden">
	<input type="hidden" name="_acfnonce" value="<?php echo wp_create_nonce( 'field_group' ); ?>" />
	<input id="input-delete-fields" type="hidden" name="_acf_delete_fields" value="0" />
</div>

<ul class="acf-hl acf-clearfix acf-thead">
	<li class="li-field_order"><?php _e('Order','acf'); ?></li>
	<li class="li-field_label"><?php _e('Label','acf'); ?></li>
	<li class="li-field_name"><?php _e('Name','acf'); ?></li>
	<li class="li-field_type"><?php _e('Type','acf'); ?></li>
</ul>

<div class="acf-field-list">
	
	<div class="no-fields-message" <?php if(count($fields) > 1){ echo 'style="display:none;"'; } ?>>
		<?php _e("No fields. Click the <strong>+ Add Field</strong> button to create your first field.",'acf'); ?>
	</div>
	
	<?php foreach( $fields as $field ): ?>
		
		<?php acf_get_view('field-group-field', array( 'field' => $field )); ?>
		
	<?php endforeach; ?>
	
</div>

<ul class="acf-hl acf-clearfix acf-tfoot">
	<li class="comic-sans">
		<i class="acf-sprite-arrow"></i><?php _e('Drag and drop to reorder','acf'); ?>
	</li>
	<li class="acf-fr">
		<a href="#" id="add-field" class="acf-button blue"><?php _e('+ Add Field','acf'); ?></a>
	</li>
</ul>