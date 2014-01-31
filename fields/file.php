<?php

class acf_field_file extends acf_field
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
		$this->name = 'file';
		$this->label = __("File",'acf');
		$this->category = __("Content",'acf');
		$this->defaults = array(
			'return_format'	=>	'array',
			'library' 		=>	'all'
		);
		$this->l10n = array(
			'select'		=>	__("Select File",'acf'),
			'edit'			=>	__("Edit File",'acf'),
			'update'		=>	__("Update File",'acf'),
			'uploadedTo'	=>	__("uploaded to this post",'acf'),
		);
		
		
		// do not delete!
    	parent::__construct();
    	
    	
    	// filters
		add_filter('get_media_item_args', array($this, 'get_media_item_args'));
		add_filter('wp_prepare_attachment_for_js', array($this, 'wp_prepare_attachment_for_js'), 10, 3);
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
	
	function render_field( $field )
	{
		// vars
		$o = array(
			'class'		=>	'acf-file-uploader acf-cf',
			'icon'		=>	'',
			'title'		=>	'',
			'size'		=>	'',
			'url'		=>	'',
			'name'		=>	'',
		);
		
		if( $field['value'] && is_numeric($field['value']) )
		{
			$file = get_post( $field['value'] );
			
			if( $file )
			{
				$o['class'] .= ' has-value';
				$o['icon'] = wp_mime_type_icon( $file->ID );
				$o['title']	= $file->post_title;
				$o['size'] = @size_format(filesize( get_attached_file( $file->ID ) ));
				$o['url'] = wp_get_attachment_url( $file->ID );
				
				$explode = explode('/', $o['url']);
				$o['name'] = end( $explode );				
			}
		}
		
		
		?>
<div <?php acf_esc_attr_e(array( 'class' => $o['class'], 'data-library' => $field['library'] )); ?>>
	<div class="acf-hidden">
		<input type="hidden" <?php acf_esc_attr_e(array( 'name' => $field['name'], 'value' => $field['value'], 'data-name' => 'id' )); ?> />	
	</div>
	<div class="show-if-value acf-soh">
		<ul class="acf-hl">
			<li>
				<img data-name="icon" src="<?php echo $o['icon']; ?>" alt=""/>
				<div class="acf-soh-target">
					<ul class="acf-bl acf-soh-target">
						<li><a class="acf-icon" data-name="remove-button" href="#"><i class="acf-sprite-delete"></i></a></li>
						<li><a class="acf-icon" data-name="edit-button" href="#"><i class="acf-sprite-edit"></i></a></li>
					</ul>
				</div>
			</li>
			<li>
				<p>
					<strong data-name="title"><?php echo $o['title']; ?></strong>
				</p>
				<p>
					<strong>Name:</strong>
					<a data-name="name" href="<?php echo $o['url']; ?>" target="_blank"><?php echo $o['name']; ?></a>
				</p>
				<p>
					<strong>Size:</strong>
					<span data-name="size"><?php echo $o['size']; ?></span>
				</p>
				
			</li>
		</ul>
	</div>
	<div class="hide-if-value">
		<p><?php _e('No File selected','acf'); ?> <a data-name="add-button" class="acf-button" href="#"><?php _e('Add File','acf'); ?></a></p>
	</div>
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
		// return_format
		acf_render_field_option( $this->name, array(
			'label'			=> __('Return Value','acf'),
			'instructions'	=> __('Specify the returned value on front end','acf'),
			'type'			=> 'radio',
			'name'			=> 'return_format',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['return_format'],
			'layout'		=> 'horizontal',
			'choices'		=> array(
				'array'			=> __("File Array",'acf'),
				'url'			=> __("File URL",'acf'),
				'id'			=> __("File ID",'acf')
			)
		));
		
		
		// library
		acf_render_field_option( $this->name, array(
			'label'			=> __('Library','acf'),
			'instructions'	=> __('Limit the media library choice','acf'),
			'type'			=> 'radio',
			'name'			=> 'library',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['library'],
			'layout'		=> 'horizontal',
			'choices' 		=> array(
				'all'			=> __('All', 'acf'),
				'uploadedTo'	=> __('Uploaded to post', 'acf')
			)
		));	
		
		
		
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

		// validate
		if( !$value )
		{
			return false;
		}
		
		
		// format
		if( $field['save_format'] == 'url' )
		{
			$value = wp_get_attachment_url($value);
		}
		elseif( $field['save_format'] == 'object' )
		{
			$attachment = get_post( $value );
			
			
			// validate
			if( !$attachment )
			{
				return false;	
			}
			
			
			// create array to hold value data
			$value = array(
				'id' => $attachment->ID,
				'alt' => get_post_meta($attachment->ID, '_wp_attachment_image_alt', true),
				'title' => $attachment->post_title,
				'caption' => $attachment->post_excerpt,
				'description' => $attachment->post_content,
				'url' => wp_get_attachment_url( $attachment->ID ),
			);
		}
		
		return $value;
	}
	
	
	/*
	*  get_media_item_args
	*
	*  @description: 
	*  @since: 3.6
	*  @created: 27/01/13
	*/
	
	function get_media_item_args( $vars )
	{
	    $vars['send'] = true;
	    return($vars);
	}
		
	
	/*
   	*  ajax_get_files
   	*
   	*  @description: 
   	*  @since: 3.5.7
   	*  @created: 13/01/13
   	*/
	
   	function ajax_get_files()
   	{
   		// vars
		$options = array(
			'nonce' => '',
			'files' => array()
		);
		$return = array();
		
		
		// load post options
		$options = array_merge($options, $_POST);
		
		
		// verify nonce
		if( ! wp_verify_nonce($options['nonce'], 'acf_nonce') )
		{
			die(0);
		}
		
		
		if( $options['files'] )
		{
			foreach( $options['files'] as $id )
			{
				$o = array();
				$file = get_post( $id );
					
				$o['id'] = $file->ID;
				$o['icon'] = wp_mime_type_icon( $file->ID );
				$o['title']	= $file->post_title;
				$o['size'] = size_format(filesize( get_attached_file( $file->ID ) ));
				$o['url'] = wp_get_attachment_url( $file->ID );
				$o['name'] = end(explode('/', $o['url']));				
				
				$return[] = $o;
			}
		}
		
		
		// return json
		echo json_encode( $return );
		die;
		
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
	
	function update_value( $value, $post_id, $field )
	{
		// array?
		if( is_array($value) && isset($value['id']) )
		{
			$value = $value['id'];	
		}
		
		// object?
		if( is_object($value) && isset($value->ID) )
		{
			$value = $value->ID;
		}
		
		return $value;
	}
	
	
	/*
	*  wp_prepare_attachment_for_js
	*
	*  this filter allows ACF to add in extra data to an attachment JS object
	*
	*  @type	function
	*  @date	1/06/13
	*
	*  @param	{int}	$post_id
	*  @return	{int}	$post_id
	*/
	
	function wp_prepare_attachment_for_js( $response, $attachment, $meta )
	{
		// default
		$fs = '0 kb';
		
		
		// supress PHP warnings caused by corrupt images
		if( $i = @filesize( get_attached_file( $attachment->ID ) ) )
		{
			$fs = size_format( $i );
		}
		
		
		// update JSON
		$response['filesize'] = $fs;
		
		
		// return
		return $response;
	}
	
}

new acf_field_file();

?>