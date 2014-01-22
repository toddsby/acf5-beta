<?php

class acf_field_oembed extends acf_field
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
		$this->name = 'oembed';
		$this->label = __("oEmbed",'acf');
		$this->category = __("Content",'acf');
		$this->defaults = array(
			'width'		=> '',
			'height'	=> '',
		);
		$this->default_values = array(
			'width' 	=> 720,
			'height'	=> 480
		);

		
		// do not delete!
    	parent::__construct();
    	
    	
    	// extra
		add_action('wp_ajax_acf/fields/oembed/search',			array($this, 'ajax_search'));
		add_action('wp_ajax_nopriv_acf/fields/oembed/search',	array($this, 'ajax_search'));
	}
	
	
	/*
	*  ajax_search
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
	
	function ajax_search()
   	{
   		// options
   		$args = acf_parse_args( $_POST, array(
			's'			=> '',
			'nonce'		=> '',
			'width'		=> $this->default_values['width'],
			'height'	=> $this->default_values['height'],
		));
		
		
		// get embed res
		$res = acf_extract_vars( $args, array('width', 'height') );
		
		
		// validate
		if( ! wp_verify_nonce($args['nonce'], 'acf_nonce') )
		{
			die();
		}
		
		
		// get oembed
		$json = array(
			'url'		=> $args['s'],
			'embed'		=> @wp_oembed_get( $args['s'], $res )
		);
		
		
		// also send back a serialized version
		$json['serialized'] = maybe_serialize($json);
		
		
		// return HTML
		echo json_encode($json);
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
	
	function render_field( $field ) {
		
		// value
		$field['value'] = acf_parse_args($field['value'], array(
			'url'	=> '',
			'embed'	=> '',
		));
		
		
		// default options
		foreach( $this->default_values as $k => $v )
		{
			if( empty($field[ $k ]) )
			{
				$field[ $k ] = $v;
			}	
		}
		
		
		// atts
		$atts = array(
			'class'			=> 'acf-oembed',
			'data-width'	=> $field['width'],
			'data-height'	=> $field['height']
		);
		
		if( !empty($field['value']['url']) )
		{
			$atts['class'] .= ' has-value';
		}
		
		?>
		<div <?php acf_esc_attr_e($atts) ?>>
			<div class="acf-hidden">
				<input type="hidden" data-name="value-input" name="<?php echo esc_attr($field['name']); ?>" value="<?php echo esc_attr(maybe_serialize($field['value'])); ?>" />
			</div>
			<div class="title">
				
				<div class="title-value">
					<a data-name="clear-button" href="#" class="acf-icon full">
						<i class="acf-sprite-delete" href="#"></i>
					</a>
					<h4 data-name="value-title"><?php echo $field['value']['url']; ?></h4>
				</div>
				
				<div class="title-search">
					<a data-name="search-button" href="#" class="acf-icon full">
						<i class="acf-sprite-submit" href="#"></i>
					</a>
					<input data-name="search-input" type="text" placeholder="Search..." autocomplete="off" />
				</div>
				
			</div>
			<div class="canvas">
				
				<div class="canvas-loading">
					<i class="acf-loading"></i>
				</div>
				
				<div class="canvas-error">
					<p><strong>Error</strong>. No embed found for the given URL</p>
				</div>
				
				<div class="canvas-media" data-name="value-embed">
					<?php echo $field['value']['embed']; ?>
				</div>
				
				<i class="acf-sprite-media hide-if-value"></i>
				
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
	*  @param	$field	- an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function render_field_options( $field )
	{
		// width
		acf_render_field_option( $this->name, array(
			'label'			=> __('Embed Size','acf'),
			'type'			=> 'text',
			'name'			=> 'width',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['width'],
			'append'		=> 'px',
			'placeholder'	=> $this->default_values['width']
		));
		
		
		// height
		acf_render_field_option( $this->name, array(
			'label'			=> __('Embed Size','acf'),
			'type'			=> 'text',
			'name'			=> 'height',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['height'],
			'append'		=> 'px',
			'placeholder'	=> $this->default_values['height']
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
	*  @param	$value	- the value which was loaded from the database
	*  @param	$post_id - the $post_id from which the value was loaded
	*  @param	$field	- the field array holding all the field options
	*
	*  @return	$value	- the modified value
	*/
	
	function format_value( $value, $post_id, $field )
	{
		
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
		
		
		
		return $value;
	}
	
}

new acf_field_oembed();

?>