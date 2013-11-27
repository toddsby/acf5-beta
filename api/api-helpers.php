<?php 

/*
*  acf_get_setting
*
*  This function will return a value from the settings array found in the acf object
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$name (string) the setting name to return
*  @return	(mixed)
*/

function acf_get_setting( $name )
{
	$r = null;
	
	if( isset( acf()->settings[ $name ] ) )
	{
		$r = acf()->settings[ $name ];
	}
	
	return $r;
}


/*
*  acf_update_setting
*
*  This function will update a value into the settings array found in the acf object
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$name (string) the setting name to return
*  @return	(mixed)
*/

function acf_update_setting( $name, $value )
{
	acf()->settings[ $name ] = $value;
}


/*
*  acf_get_path
*
*  This function will return the path to a file within the ACF plugin folder
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$path (string) the relative path from the root of the ACF plugin folder
*  @return	(string)
*/

function acf_get_path( $path )
{
	return acf_get_setting('path') . $path;
}


/*
*  acf_get_dir
*
*  This function will return the url to a file within the ACF plugin folder
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$path (string) the relative path from the root of the ACF plugin folder
*  @return	(string)
*/

function acf_get_dir( $path )
{
	return acf_get_setting('dir') . $path;
}


/*
*  acf_parse_args
*
*  This function will merge together 2 arrays and also convert any numeric values to ints
*
*  @type	function
*  @date	18/10/13
*  @since	5.0.0
*
*  @param	$args (array)
*  @param	$defaults (array)
*  @return	$args (array)
*/

function acf_parse_args( $args, $defaults = array() ) {
	
	// $args may not be na array!
	if( !is_array($args) )
	{
		$args = array();
	}
	
	
	// parse args
	$args = wp_parse_args( $args, $defaults );
	
	
	// parse types
	$args = acf_parse_types( $args );
	
	
	// return
	return $args;
	
}


/*
*  acf_parse_types
*
*  This function will convert any numeric values to int and trim strings
*
*  @type	function
*  @date	18/10/13
*  @since	5.0.0
*
*  @param	$var (mixed)
*  @return	$var (mixed)
*/

function acf_parse_types( $var )
{
	// is value another array?
	if( is_array($var) )
	{
		// some keys are restricted
		$restricted = array(
			'label',
			'name',
			'value',
			'instructions'
		);
		
		
		// loop through $var carful not to parse any restricted keys
		foreach( array_keys($var) as $k )
		{
			// bail early for restricted pieces
			if( in_array($k, $restricted, true) )
			{
				continue;
			}
			
			$var[ $k ] = acf_parse_types( $var[ $k ] );
		}	
	}
	else
	{
		// string
		if( is_string($var) )
		{
			$var = trim( $var );
		}
		
		
		// numbers
		if( is_numeric($var) )
		{
			// float / int
			if( strpos($var,'.') !== false )
			{
				// leave decimal places alone
				// $value = floatval( $value );
			}
			else
			{
				$var = intval( $var );
			}
		}
	}
	
	
	// return
	return $var;
}


/*
*  acf_get_view
*
*  This function will load in a file from the 'admin/views' folder and allow variables to be passed through
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$view_name (string)
*  @param	$args (array)
*  @return	n/a
*/

function acf_get_view( $view_name = '', $args = array() ) {

	// vars
	$path = acf_get_path("admin/views/{$view_name}.php");
	
	if( file_exists($path) )
	{
		include( $path );
	}
}


/*
*  acf_render_field_wrap
*
*  This function will render the complete HTML wrap with label & field
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$field (array) must be a valid ACF field array
*  @param	$el (string) modifys the rendered wrapping elements. Default to 'div', but can be 'tr', 'ul', 'ol', 'dt' or custom
*  @param	$instruction (string) specifys the placement of the instructions. Default to 'label', but can be 'field'
*  @param	$atts (array) an array of custom attributes to render on the $el
*  @return	N/A
*/

function acf_render_field_wrap( $field, $el = 'div', $instruction = 'label', $atts = array() ) {
	
	// get valid field
	$field = acf_get_valid_field( $field );
	
	
	// el
	$elements = apply_filters('acf/render_field_wrap/elements', array(
		'div'	=> 'div',
		'tr'	=> 'td',
		'ul'	=> 'li',
		'ol'	=> 'li',
		'dl'	=> 'dt',
		'td'	=> 'div' // special case for sub field!
	));
	
	
	// validate $el
	if( !array_key_exists($el, $elements) )
	{
		$el = 'div';
	}
	
	
	// atts
	$atts = acf_parse_args($atts, array(
		'class'		=> '',
		'data-name'	=> $field['field_name'],
		'data-type'	=> $field['type'],
	));
	
	
	// add to atts
	$atts['class'] .= " acf-field field_type-{$field['type']}";
	
	
	// add key
	if( $field['key'] )
	{
		$atts['class'] .= " field_key-{$field['key']}";
		$atts['data-key'] = $field['key'];
	}
	
	
	// add required
	if( $field['required'] )
	{
		$atts['class'] .= ' required';
	}
	
	
	// vars
	$show_label = true;
	
	if( $el == 'td' )
	{
		$show_label = false;
	}
	
	
	?>
	<<?php echo $el; ?> <?php echo acf_esc_attr($atts); ?>>
		<?php if( $show_label ): ?>
		<<?php echo $elements[ $el ]; ?> class="acf-label">
			
			<label for="<?php echo $field['id']; ?>"><?php echo acf_get_field_label($field); ?></label>
			
			<?php if( $instruction == 'label' && $field['instructions'] ): ?>
				<p class="description"><?php echo $field['instructions']; ?></p>
			<?php endif; ?>
			
		</<?php echo $elements[ $el ]; ?>>
		<?php endif; ?>
		<<?php echo $elements[ $el ]; ?> class="acf-input">
		
			<?php acf_render_field( $field ); ?>
			
			<?php if( $instruction == 'field' && $field['instructions'] ): ?>
				<p class="description"><?php echo $field['instructions']; ?></p>
			<?php endif; ?>
			
		</<?php echo $elements[ $el ]; ?>>
	</<?php echo $el; ?>>
	<?php
}


/*
*  acf_render_fields
*
*  This function will render an array of fields for a given form.
*  Becasue the $field's values have not been loaded yet, this function will also load values
*
*  @type	function
*  @date	8/10/13
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_render_fields( $post_id = 0, $fields, $el = 'div', $instruction = 'label' ) {
		
	if( !empty($fields) )
	{
		foreach( $fields as $field )
		{
			// load value
			if( $post_id )
			{
				$field['value'] = acf_get_value( $post_id, $field, true );
			} 
			
			
			// set prefix for correct post name (prefix + key)
			$field['prefix'] = 'acf';
			
			
			// render
			acf_render_field_wrap( $field, $el, $instruction );
		}
	}
		
}


/*
*  acf_get_field_label
*
*  This function will return the field label with appropriate required label
*
*  @type	function
*  @date	4/11/2013
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_get_field_label( $field ) {
	
	// vars
	$r = $field['label'];
	
	
	if( $field['required'] )
	{
		$r .= ' <span class="acf-required">*</span>'; 
	}
	
	
	// return
	return $r;

}

function acf_the_field_label( $field ) {

	echo acf_get_field_label( $field );
	
}

/*
*  acf_render_option
*
*  This function will render a tr element containing a label and field cell
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$field (array)
*  @return	N/A
*/

/*
function acf_render_option( $field, $options = array() )
{
	$options = wp_parse_args($options, array(
		'class' => ''
	));
	
	
	// get valid field
	$field = acf_get_valid_field( $field );
	
	
	// vars
	$class = 'tr-' . sanitize_title_with_dashes( $field['label'] );
	
	if( $options['class'] )
	{
		$class .= ' ' . $options['class'];
	}
	
	acf_render_field_wrap( $field, 'tr' );
	
}
*/


/*
*  acf_render_field_option
*
*  This function will render a tr element containing a label and field cell, but also setting the tr_class for use with AJAX 
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$type (string)
*  @param	$field (array)
*  @return	N/A
*/

function acf_render_field_option( $type, $field )
{
	// vars
	$atts = array( 
		'data-option' => $type
	);
	
	
	// render
	acf_render_field_wrap( $field, 'tr', 'label', $atts );
}


/*
*  acf_get_field_types
*
*  This function will return all available field types
*
*  @type	function
*  @date	1/10/13
*  @since	5.0.0
*
*  @param	n/a
*  @return	(array)
*/

function acf_get_field_types()
{
	return apply_filters('acf/get_field_types', array());
}


/*
*  acf_field_type_exists
*
*  This function will check if the field_type is available
*
*  @type	function
*  @date	1/10/13
*  @since	5.0.0
*
*  @param	$field_type (string)
*  @return	(boolean)
*/

function acf_field_type_exists( $field_type )
{
	// vars
	$field_types = acf_get_field_types();
	
	
	// loop through categories
	foreach( $field_types as $category )
	{
		if( isset( $category[ $field_type ] ) )
		{
			return true;
		}
	}
	
	
	// return
	return false;
}


/*
*  acf_esc_attr
*
*  This function will return a render of an array of attributes to be used in markup
*
*  @type	function
*  @date	1/10/13
*  @since	5.0.0
*
*  @param	$atts (array)
*  @return	n/a
*/

function acf_esc_attr( $atts )
{
	// is string?
	if( is_string($atts) )
	{
		$atts = trim( $atts );
		return esc_attr( $atts );
	}
	
	
	// validate
	if( empty($atts) )
	{
		return '';
	}
	
	
	// vars
	$e = array();
	
	
	// loop through and render
	foreach( $atts as $k => $v )
	{
		$v = trim( $v );
		$e[] = $k . '="' . esc_attr( $v ) . '"';
	}
	
	
	// echo
	return implode(' ', $e);
}

function acf_esc_attr_e( $atts ) {
	echo acf_esc_attr( $atts );
}


/*
*  acf_extract_var
*
*  This function will remove the var from the array, and return the var
*
*  @type	function
*  @date	2/10/13
*  @since	5.0.0
*
*  @param	$array (array)
*  @param	$key (string)
*  @return	(mixed)
*/

function acf_extract_var( &$array, $key ) {

	$r = null;
	
	
	if( array_key_exists($key, $array) )
	{
		$r = $array[ $key ];
		unset( $array[ $key ] );
	}

	return $r;
}


/*
*  acf_extract_vars
*
*  This function will remove the vars from the array, and return the vars
*
*  @type	function
*  @date	8/10/13
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_extract_vars( &$array, $keys )
{
	$r = array();
	
	foreach( $keys as $key )
	{
		$r[ $key ] = acf_extract_var( $array, $key );
	}
	
	return $r;
}


/*
*  acf_get_post_types
*
*  This function will return an array of available post types
*
*  @type	function
*  @date	7/10/13
*  @since	5.0.0
*
*  @param	$exclude (array)
*  @param	$include (array)
*  @return	(array)
*/

function acf_get_post_types( $exclude = array(), $include = array() )
{
	// get all custom post types
	$post_types = get_post_types();
	
	
	// core exclude
	$exclude = wp_parse_args( $exclude, array( 'acf-field', 'acf-field-group', 'revision', 'nav_menu_item' ) );
	
	
	// include
	if( !empty($include) )
	{
		foreach( $include as $p )
		{					
			if( post_type_exists($p) )
			{							
				$post_types[ $p ] = $p;
			}
		}
	}
	
	
	// exclude
	foreach( $exclude as $p )
	{
		unset( $post_types[ $p ] );
	}
	
	
	// return
	return $post_types;
	
}


/*
*  acf_extract_post_id
*
*  This functoin will return an array containing the ID and type for a given post_id
*
*  @type	function
*  @date	15/10/13
*  @since	5.0.0
*
*  @param	$post_id (mixed)
*  @return	(array)
*/

/*
function acf_extract_post_id( $post_id = 0 ) {
	
	
	
}
*/


/*
*  acf_verify_nonce
*
*  This function will look at the $_POST['_acfnonce'] value and return true or false
*
*  @type	function
*  @date	15/10/13
*  @since	5.0.0
*
*  @param	$nonce (string)
*  @return	(boolean)
*/

function acf_verify_nonce( $nonce, $post_id = 0 ) {
	
	// vars
	$r = false;
	
	
	// check
	if( isset($_POST['_acfnonce']) && wp_verify_nonce($_POST['_acfnonce'], $nonce) )
	{
		$r = true;
		
		
		// remove potential for inifinite loops
		$_POST['_acfnonce'] = false;
		
		
		// if we are currently saving a revision, allow it's parent to bypass this validation
		if( $post_id )
		{
			if( $parent = wp_is_post_revision($post_id) )
			{
				// revision: set parent post_id
				$_POST['_acfnonce'] = $parent;
			}
			else
			{
				// parent: compare parent post_id
				if( $_POST['_acfnonce'] === $post_id )
				{
					$r = true;
					
					// remove potential for inifinite loops
					$_POST['_acfnonce'] = false;
				}
			}
		}
	}
	
	
	// return
	return $r;
	
}


/*
*  acf_add_admin_notice
*
*  This function will add the notice data to a setting in the acf object for the admin_notices action to use
*
*  @type	function
*  @date	17/10/13
*  @since	5.0.0
*
*  @param	$text (string)
*  @param	$class (string)
*  @return	(int) message ID (array position)
*/

function acf_add_admin_notice( $text, $class = 'updated' )
{
	// vars
	$admin_notices = acf_get_admin_notices();
	
	
	// add to array
	$admin_notices[] = array(
		'text'	=> $text,
		'class'	=> $class
	);
	
	
	// update
	acf_update_setting( 'admin_notices', $admin_notices );
	
	
	// return
	return ( count( $admin_notices ) - 1 );
	
}


/*
*  acf_get_admin_notices
*
*  This function will return an array containing any admin notices
*
*  @type	function
*  @date	17/10/13
*  @since	5.0.0
*
*  @param	n/a
*  @return	n/a
*/

function acf_get_admin_notices()
{
	// vars
	$admin_notices = acf_get_setting( 'admin_notices' );
	
	
	// validate
	if( !$admin_notices )
	{
		$admin_notices = array();
	}
	
	
	// return
	return $admin_notices;
}


/*
*  acf_get_image_sizes
*
*  This function will return an array of available image sizes
*
*  @type	function
*  @date	23/10/13
*  @since	5.0.0
*
*  @param	n/a
*  @return	(array)
*/

function acf_get_image_sizes() {
	
	// vars
	$sizes = array(
		'thumbnail'	=>	__("Thumbnail",'acf'),
		'medium'	=>	__("Medium",'acf'),
		'large'		=>	__("Large",'acf'),
		'full'		=>	__("Full",'acf')
	);


	// find all sizes
	$all_sizes = get_intermediate_image_sizes();
	
	
	// add extra registered sizes
	foreach( $all_sizes as $size )
	{
		if( !isset($sizes[ $size ]) )
		{
			$sizes[ $size ] = ucwords( str_replace('-', ' ', $size) );
		}
	}
	
	
	// return
	return $sizes;
	
}


/*
*  acf_get_taxonomies
*
*  This function will return an array of available taxonomies
*
*  @type	function
*  @date	7/10/13
*  @since	5.0.0
*
*  @param	$exclude (array)
*  @param	$include (array)
*  @return	(array)
*/

function acf_get_taxonomies() {

	// get all taxonomies
	$taxonomies = get_taxonomies( false, 'objects' );
	$r = array();
	
	
	// populate $r
	foreach( $taxonomies as $taxonomy )
	{
		$r[ $taxonomy->name ] = "{$taxonomy->labels->singular_name}"; // ({$taxonomy->name})
	}
		
	
	// remove
	unset($r['post_format']);
	
	
	// return
	return $r;
	
}


/*
*  acf_get_taxonomy_terms
*
*  This function will return an array of available taxonomy terms
*
*  @type	function
*  @date	7/10/13
*  @since	5.0.0
*
*  @param	$exclude (array)
*  @param	$include (array)
*  @return	(array)
*/

function acf_get_taxonomy_terms( $taxonomies = false ) {

	// load all taxonomies if not specified in args
	if( !$taxonomies )
	{
		$taxonomies = acf_get_taxonomies();
	}
	
	
	// vars
	$r = array();
	
	
	// populate $r
	foreach( $taxonomies as $taxonomy => $label )
	{
		$terms = get_terms( $taxonomy, array( 'hide_empty' => false ) );
		
		if( !empty($terms) )
		{
			$r[ $label ] = array();
			
			foreach( $terms as $term )
			{
				$k = "{$taxonomy}:{$term->slug}"; 
				$r[ $label ][ $k ] = $term->name;
			}
		}

	}
	
	
	// return
	return $r;
	
}


function acf_decode_taxonomy_terms( $terms = false ) {
	
	// load all taxonomies if not specified in args
	if( !$terms )
	{
		$terms = acf_get_taxonomy_terms();
	}
	
	
	// vars
	$r = array();
	
	
	foreach( $terms as $term )
	{
		// vars
		$term = explode(':', $term);
		
		
		// create empty array
		if( !array_key_exists($term[0], $r) )
		{
			$r[ $term[0] ] = array();
		}
		
		
		// append to taxonomy
		$r[ $term[0] ][] = $term[1];
	}
	
	
	// return
	return $r;
	
}


?>