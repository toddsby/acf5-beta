<?php

class acf_field_wysiwyg extends acf_field
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
		$this->name = 'wysiwyg';
		$this->label = __("Wysiwyg Editor",'acf');
		$this->category = __("Content",'acf');
		$this->defaults = array(
			'toolbar'		=>	'full',
			'media_upload' 	=>	1,
			'default_value'	=>	'',
		);
		
		
		// do not delete!
    	parent::__construct();
    	
    	
    	// filters
    	add_filter( 'acf/fields/wysiwyg/toolbars', array( $this, 'toolbars'), 0, 1 );
	}
	
	
	/*
	*  toolbars()
	*
	*  This filter allowsyou to customize the WYSIWYG toolbars
	*
	*  @param	$toolbars - an array of toolbars
	*
	*  @return	$toolbars - the modified $toolbars
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*/
	
   	function toolbars( $toolbars ) {
   		
   		$editor_id = 'acf_settings';
   		
   		
   		// Full
   		$toolbars['Full'] = array();
   		$toolbars['Full'][1] = apply_filters('mce_buttons', array('bold', 'italic', 'strikethrough', 'bullist', 'numlist', 'blockquote', 'justifyleft', 'justifycenter', 'justifyright', 'link', 'unlink', 'wp_more', 'spellchecker', 'fullscreen', 'wp_adv' ), $editor_id);
   		$toolbars['Full'][2] = apply_filters('mce_buttons_2', array( 'formatselect', 'underline', 'justifyfull', 'forecolor', 'pastetext', 'pasteword', 'removeformat', 'charmap', 'outdent', 'indent', 'undo', 'redo', 'wp_help', 'code' ), $editor_id);
   		$toolbars['Full'][3] = apply_filters('mce_buttons_3', array(), $editor_id);
   		$toolbars['Full'][4] = apply_filters('mce_buttons_4', array(), $editor_id);
   		
   		
   		// Basic
   		$toolbars['Basic'] = array();
   		$toolbars['Basic'][1] = apply_filters( 'teeny_mce_buttons', array('bold', 'italic', 'underline', 'blockquote', 'strikethrough', 'bullist', 'numlist', 'justifyleft', 'justifycenter', 'justifyright', 'undo', 'redo', 'link', 'unlink', 'fullscreen'), $editor_id );
   		
   		
   		// Custom - can be added with acf/fields/wysiwyg/toolbars filter
   	
   		
	   	return $toolbars;
   	}
   	
   	
   	/*
	*  input_admin_footer
	*
	*  This function will render acf hidden data such as inputs, js and css
	*
	*  @type	function
	*  @date	8/10/13
	*  @since	5.0.0
	*
	*  @param	$args (array)
	*  @return	n/a
	*/
	
	function input_admin_footer( $args ) {
		
		// add some validation to check for a setting which will prevent the hidden WYWSIWYG + scripts from being registered
		
		
		// vars
		$t = array();
		$toolbars = apply_filters( 'acf/fields/wysiwyg/toolbars', array() );

		
		// loop through toolbars and populate $t
		if( is_array($toolbars) ){ foreach( $toolbars as $label => $rows ){
			
			$label = sanitize_title( $label );
			$label = str_replace('-', '_', $label);
			
			$t[ $label ] = array();
			
			if( is_array($rows) ){ foreach( $rows as $k => $v ){
				
				$t[ $label ][ 'theme_advanced_buttons' . $k ] = implode(',', $v);
				
			}}
		}}
		
		
		?>
		<script type="text/javascript">
		(function($) {
		
			acf.fields.wysiwyg.toolbars = <?php echo json_encode( $t ); ?>;
		
		})(jQuery);	
		</script>
		<div class="acf-hidden" style="display:none;">
			<?php wp_editor( '', 'acf_settings' ); ?>
		</div>
		<?php
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
	
		// global
		global $wp_version;
		
		
		// vars
		$id = 'wysiwyg-' . $field['id'] . '-' . uniqid();
		
		
		?>
		<div id="wp-<?php echo $id; ?>-wrap" class="acf-wysiwyg-wrap wp-editor-wrap" data-toolbar="<?php echo $field['toolbar']; ?>" data-upload="<?php echo $field['media_upload']; ?>">
			<?php if( user_can_richedit() && $field['media_upload'] ): ?>
				<div id="wp-<?php echo $id; ?>-editor-tools" class="wp-editor-tools">
					<div id="wp-<?php echo $id; ?>-media-buttons" class="hide-if-no-js wp-media-buttons">
						<?php do_action( 'media_buttons' ); ?>
					</div>
				</div>
			<?php endif; ?>
			<div id="wp-<?php echo $id; ?>-editor-container" class="wp-editor-container">
				<textarea id="<?php echo $id; ?>" class="wp-editor-area" name="<?php echo $field['name']; ?>" ><?php 
				
				if( user_can_richedit() )
				{
					echo wp_richedit_pre( $field['value'] );
				} 
				else
				{
					echo wp_htmledit_pre( $field['value'] );
				}
				
				?></textarea>
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
	
	function render_field_options( $field ) {
		// vars
		$toolbars = apply_filters( 'acf/fields/wysiwyg/toolbars', array() );
		$choices = array();
		
		if( is_array($toolbars) )
		{
			foreach( $toolbars as $k => $v )
			{
				$label = $k;
				$name = sanitize_title( $label );
				$name = str_replace('-', '_', $name);
				
				$choices[ $name ] = $label;
			}
		}
		
		
		// default_value
		acf_render_field_option( $this->name, array(
			'label'			=> __('Default Value','acf'),
			'instructions'	=> __('Appears when creating a new post','acf'),
			'type'			=> 'textarea',
			'name'			=> 'default_value',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['default_value'],
		));
		
		
		// toolbar
		acf_render_field_option( $this->name, array(
			'label'			=> __('Toolbar','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'toolbar',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['toolbar'],
			'layout'		=> 'horizontal',
			'choices'		=> $choices
		));
		
		
		// media_upload
		acf_render_field_option( $this->name, array(
			'label'			=> __('Show Media Upload Buttons?','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'media_upload',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['media_upload'],
			'layout'		=> 'horizontal',
			'choices'		=> array(
				1				=>	__("Yes",'acf'),
				0				=>	__("No",'acf'),
			)
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
		
		
		// apply filters
		$value = apply_filters( 'acf_the_content', $value );
		
		
		// follow the_content function in /wp-includes/post-template.php
		$value = str_replace(']]>', ']]&gt;', $value);
		
	
		return $value;
	}
	
}

new acf_field_wysiwyg();


// Create an acf version of the_content filter (acf_the_content)
if(	isset($GLOBALS['wp_embed']) )
{
	add_filter( 'acf_the_content', array( $GLOBALS['wp_embed'], 'run_shortcode' ), 8 );
	add_filter( 'acf_the_content', array( $GLOBALS['wp_embed'], 'autoembed' ), 8 );
}

add_filter( 'acf_the_content', 'capital_P_dangit', 11 );
add_filter( 'acf_the_content', 'wptexturize' );
add_filter( 'acf_the_content', 'convert_smilies' );
add_filter( 'acf_the_content', 'convert_chars' );
add_filter( 'acf_the_content', 'wpautop' );
add_filter( 'acf_the_content', 'shortcode_unautop' );
add_filter( 'acf_the_content', 'prepend_attachment' );
add_filter( 'acf_the_content', 'do_shortcode', 11);

?>