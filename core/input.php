<?php 

class acf_input {
	
	
	/*
	*  __construct
	*
	*  Initialize filters, action, variables and includes
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	5.0.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function __construct() {
		
		add_action('acf/input/admin_enqueue_scripts', 	array($this, 'admin_enqueue_scripts'), 0, 0);
		add_action('acf/input/admin_head', 				array($this, 'admin_head'), 0, 0);
		add_action('acf/input/admin_footer', 			array($this, 'admin_footer'), 0, 0);
		add_action('acf/input/form_data', 				array($this, 'form_data'), 0, 1);
		
		add_action('acf/save_post', 					array($this, 'save_post'), 0, 1);	
		
	}
	
	
	/*
	*  admin_enqueue_scripts
	*
	*  This functiln will enqueue all the required scripts / styles for ACF
	*
	*  @type	action (acf/input/admin_enqueue_scripts)
	*  @date	6/10/13
	*  @since	5.0.0
	*
	*  @param	n/a	
	*  @return	n/a
	*/
	
	function admin_enqueue_scripts()
	{

		// scripts
		wp_enqueue_script(array(
			'jquery',
			'jquery-ui-core',
			'jquery-ui-tabs',
			'jquery-ui-sortable',
			'wp-color-picker',
			'thickbox',
			'media-upload',
			'acf-input',
			'acf-datepicker',	
		));

		
		// 3.5 media gallery
		if( function_exists('wp_enqueue_media') && !did_action( 'wp_enqueue_media' ))
		{
			wp_enqueue_media();
		}
		
		
		// styles
		wp_enqueue_style(array(
			'thickbox',
			'wp-color-picker',
			'acf-global',
			'acf-input',
			'acf-datepicker',	
		));
	}
	
	
	/*
	*  admin_head
	*
	*  action called when rendering the head of an admin screen. Used primarily for passing PHP to JS
	*
	*  @type	action (acf/input/admin_head)
	*  @date	27/05/13
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function admin_head() {
		
	}
	
	
	/*
	*  form_data
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
	
	function form_data( $args ) {
	
		// global
		global $wp_version;
		
		
		// options
		$o = array(
			'post_id'		=> $args['post_id'],
			'nonce'			=> wp_create_nonce( 'acf_nonce' ),
			'admin_url'		=> admin_url(),
			'ajaxurl'		=> admin_url( 'admin-ajax.php' ),
			'wp_version'	=> $wp_version,
			'$form'			=> $args['$form'],
			'ajax'			=> $args['ajax']
		);
		
		
		// l10n
		$l10n = apply_filters( 'acf/input/admin_l10n', array(
			'core' => array(
				'expand_details' => __("Expand Details",'acf'),
				'collapse_details' => __("Collapse Details",'acf')
			),
			'validation' => array(
				'error' => __("Validation Failed. One or more fields below are required.",'acf')
			)
		));
		
		
		?>
		<script type="text/javascript">
		(function($) {
		
			acf.o = <?php echo json_encode( $o ); ?>;
			acf.l10n = <?php echo json_encode( $l10n ); ?>;
		
		})(jQuery);	
		</script>
		<input type="hidden" name="_acfnonce" value="<?php echo wp_create_nonce( $args['nonce'] ); ?>" />
		<input type="hidden" name="_acfchanged" value="0" />
		<?php
		
		/*
			Notes:
			
			_acfchanged is a JS hack to force WP to create a revision apon save
			// http://support.advancedcustomfields.com/forums/topic/preview-solution/#post-4106
		*/
		
	}
	
	
	/*
	*  admin_footer
	*
	*  description
	*
	*  @type	function
	*  @date	7/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function admin_footer() {
		
	}
	
	
	/*
	*  save_post
	*
	*  description
	*
	*  @type	function
	*  @date	8/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function save_post( $post_id ) {
		
		
		// loop through and save
		if( !empty($_POST['acf']) )
		{
			// loop through and save $_POST data
			foreach( $_POST['acf'] as $key => $value )
			{
				// get field
				$field = acf_get_field( $key );
				
				// update field
				acf_update_value( $value, $post_id, $field );
				
			}
			// foreach($fields as $key => $value)
		}
		// if($fields)
		
		
		return $post_id;

	}
	
}


// initialize
new acf_input();


/*
*  listener
*
*  This class will call all the neccessary actions during the page load for acf input to function
*
*  @type	class
*  @date	7/10/13
*  @since	5.0.0
*
*  @param	n/a
*  @return	n/a
*/

class acf_input_listener {
	
	function __construct() {
		
		do_action('acf/input/admin_enqueue_scripts');
		
		if( is_admin() )
		{
			add_action('admin_head', 			array( $this, 'admin_head') );
			add_action('admin_footer', 			array( $this, 'admin_footer') );
		}
		else
		{
			add_action('wp_head', 				array( $this, 'admin_head') );
			add_action('wp_footer', 			array( $this, 'admin_footer') );	
		}
	}
	
	function admin_head() {
		
		do_action('acf/input/admin_head');
	}
	
	function admin_footer() {
		
		do_action('acf/input/admin_footer');
	}
	
}


/*
*  acf_admin_init
*
*  This function is used to setup all actions / functionality for an admin page which will contain ACF inputs
*
*  @type	function
*  @date	6/10/13
*  @since	5.0.0
*
*  @param	n/a
*  @return	n/a
*/

function acf_enqueue_scripts() {
	
	// bail early if acf has already loaded
	if( acf_get_setting('enqueue_scripts') )
	{
		return;
	}
	
	
	// update setting
	acf_update_setting('enqueue_scripts', 1);
	
	
	// add actions
	new acf_input_listener();
}


/*
*  acf_form_data
*
*  description
*
*  @type	function
*  @date	15/10/13
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_form_data( $args = array() ) {
	
	// defaults
	$args = acf_parse_args($args, array(
		'post_id'	=> 0,
		'nonce'		=> 'post',
		'$form'		=> '#post',
		'ajax'		=> false
	));
	
	
	?>
	<div id="acf-form-data" class="acf-hidden">
		<?php do_action('acf/input/form_data', $args); ?>
	</div>
	<?php
}


/*
*  acf_save_post
*
*  description
*
*  @type	function
*  @date	8/10/13
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_save_post( $post_id = 0 ) {
	
	return do_action('acf/save_post', $post_id);
}


?>