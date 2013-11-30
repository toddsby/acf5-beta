<?php 

class acf_controller_widget {
	
	/*
	*  Constructor
	*
	*  This function will construct all the neccessary actions and filters
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	3.1.8
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function __construct()
	{
		// actions
		add_action( 'admin_enqueue_scripts',			array( $this, 'admin_enqueue_scripts' ) );
		
		
		// actions
		add_action( 'in_widget_form', 					array($this, 'in_widget_form'), 10, 3 );
		add_filter( 'widget_update_callback',			array($this, 'widget_update_callback'), 10, 4 );
		
	}
	
	
	/*
	*  validate_page
	*
	*  This function will check if the current page is for a post/page edit form
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	3.1.8
	*
	*  @param	N/A
	*  @return	(boolean)
	*/
	
	function validate_page()
	{
		// global
		global $pagenow;
		
		
		// vars
		$r = false;
		
		
		// validate page
		if( $pagenow == 'widgets.php' )
		{
			$r = true;
		}
		
		
		// return
		return $r;
	}
	
	
	/*
	*  admin_enqueue_scripts
	*
	*  This action is run after post query but before any admin script / head actions. 
	*  It is a good place to register all actions.
	*
	*  @type	action (admin_enqueue_scripts)
	*  @date	26/01/13
	*  @since	3.6.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function admin_enqueue_scripts() {
		
		// validate page
		if( ! $this->validate_page() )
		{
			return;
		}
		
		
		// load acf scripts
		acf_enqueue_scripts();
		
		
		// actions
		add_action('acf/input/admin_footer', array($this, 'admin_footer'));
	}
	
	
	function in_widget_form( $widget, $return, $instance ) {
		
		// vars
		$post_id = 0;
		
		
		if( $widget->number !== '__i__' )
		{
			$post_id = "widget_{$widget->id}";
		}
		
		
	
		// render post data
		acf_form_data(array( 
			'post_id'	=> $post_id, 
			'nonce'		=> 'widget' 
		));
		
		
		// get field groups
		$field_groups = acf_get_field_groups(array(
			'widget' => $widget->id_base
		));
		
		
		// render
		if( !empty($field_groups) ):
			
			foreach( $field_groups as $field_group ): 
				
				$fields = acf_get_fields( array('field_group' => $field_group['ID']) );
				
				acf_render_fields( $post_id, $fields, 'div', 'field' );
				
				if( $widget->updated ): ?>
				<script type="text/javascript">
				(function($) {
					
					// vars
					$widget = $('[id^="widget"][id$="<?php echo $widget->id; ?>"]');
					
					acf.do_action('append', $widget );
					
				})(jQuery);	
				</script>
				<?php endif;
				
			endforeach; 
		
		endif;
		
	}
	
	
	function widget_update_callback( $instance, $new_instance, $old_instance, $widget ) {
		
		// verify and remove nonce
		if( ! acf_verify_nonce('widget') )
		{
			return $instance;
		}
		
	    
	    // save data
	    if( acf_validate_save_post() )
		{
			acf_save_post( "widget_{$widget->id}" );		
		}
		
		return $instance;
		
	}
	
	function admin_footer() {
		
?>
<script type="text/javascript">
(function($) {
	
	acf.add_action('ready', function(){
		
		$('#widgets-right').on('click', '.widget-control-save', function( e ){
		
			// vars
			var $form = $(this).closest('form');
			
			
			// bail early if this form does not contain ACF data
			if( ! $form.find('#acf-form-data').exists() )
			{
				return true;
			}
			
			
			// ignore this submit?
			if( acf.validation.ignore == 1 )
			{
				acf.validation.ignore = 0;
				return true;
			}
			
	
			// bail early if disabled
			if( acf.validation.active == 0 )
			{
				return true;
			}
	
			
			// stop WP JS validation
			e.stopImmediatePropagation();
			
			
			// store submit trigger so it will be clicked if validation is passed
			acf.validation.$trigger = $(this);
			
			
			// run validation
			acf.validation.fetch( $form );
			
			
			// stop all other click events on this input
			return false;
			
		});
		
	});
	
	acf.add_action('load', function(){
		
		$('div.widgets-sortables').on('sortstop', function(e, ui){
			
			// vars
			var add_new = ui.item.find('.add_new').val();
			
			
			if( add_new == 'multi' )
			{
				console.log( 'div.widgets-sortables %o ', ui.item );
				
				
				setTimeout(function(){
					
					// this is a newly dragged in widget
					acf.do_action('append', ui.item );
					
				}, 250);
			}

		});
		
	});
	
})(jQuery);	
</script>
<?php
		
	}
	
}

new acf_controller_widget();

?>