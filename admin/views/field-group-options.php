<?php 

// global
global $post;


// vars
$field_group = acf_get_field_group( $post );

?>
<table class="acf-table">
	<tbody>
		<?php 
		
		// menu_order
		acf_render_field_wrap(array(
			'label'			=> __('Order No.','acf'),
			'instructions'	=> __('Field groups are created in order <br />from lowest to highest','acf'),
			'type'			=> 'number',
			'name'			=> 'acf_field_group[menu_order]',
			'value'			=> $field_group['menu_order'],
		), 'tr');
		
		
		// position
		acf_render_field_wrap(array(
			'label'			=> __('Position','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'acf_field_group[position]',
			'value'			=> $field_group['position'],
			'choices' 		=> array(
				'acf_after_title'	=> __("High (after title)",'acf'),
				'normal'			=> __("Normal (after content)",'acf'),
				'side' 				=> __("Side",'acf'),
			),
			'default_value'	=> 'normal'
		), 'tr');
		
		
		// style
		acf_render_field_wrap(array(
			'label'			=> __('Style','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'acf_field_group[layout]',
			'value'			=> $field_group['layout'],
			'choices' 		=> array(
				'seamless'			=>	__("No Metabox",'acf'),
				'default'			=>	__("Standard Metabox",'acf'),
			)
		), 'tr');
		
		
		// hide on screen
		acf_render_field_wrap(array(
			'label'			=> __('Hide on screen','acf'),
			'instructions'	=> __('<b>Select</b> items to <b>hide</b> them from the edit screen','acf') . '</p><p>' . __("If multiple field groups appear on an edit screen, the first field group's options will be used. (the one with the lowest order number)",'acf'),
			'type'			=> 'checkbox',
			'name'			=> 'acf_field_group[hide_on_screen]',
			'value'			=> $field_group['hide_on_screen'],
			'choices' => array(
				'the_content'		=>	__("Content Editor",'acf'),
				'excerpt'			=>	__("Excerpt"),
				'custom_fields'		=>	__("Custom Fields"),
				'discussion'		=>	__("Discussion"),
				'comments'			=>	__("Comments"),
				'revisions'			=>	__("Revisions"),
				'slug'				=>	__("Slug"),
				'author'			=>	__("Author"),
				'format'			=>	__("Format"),
				'featured_image'	=>	__("Featured Image"),
				'categories'		=>	__("Categories"),
				'tags'				=>	__("Tags"),
				'send-trackbacks'	=>	__("Send Trackbacks"),
			)
		), 'tr');
		
		?>
	</tbody>
</table>