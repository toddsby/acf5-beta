<?php 

class acf_controller_taxonomy {
	
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
		add_action( 'admin_enqueue_scripts',		array( $this, 'admin_enqueue_scripts' ) );
		
		
		// save
		add_action( 'create_term',					array( $this, 'save_term'), 10, 3 );
		add_action( 'edited_term',					array( $this, 'save_term'), 10, 3 );
		
		
		// delete
		add_action( 'delete_term',					array( $this, 'delete_term'), 10, 4 );
		
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
		if( $pagenow == 'edit-tags.php' )
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
		
		
		// vars
		$screen = get_current_screen();
		$taxonomy = $screen->taxonomy;
		
		
		// load acf scripts
		acf_enqueue_scripts();
		
		
		// render
		add_action( "{$taxonomy}_add_form_fields", 		array( $this, 'add_term' ), 10, 1 );
		add_action( "{$taxonomy}_edit_form", 			array( $this, 'edit_term' ), 10, 2 );
		
	}
	
	
	/*
	*  add_term
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
	
	function add_term( $taxonomy ) {
		
		// vars
		$post_id = "{$taxonomy}_0";
		$args = array(
			'taxonomy' => $taxonomy
		);
		
		
		// get field groups
		$field_groups = acf_get_field_groups( $args );
		
		
		// render
		if( !empty($field_groups) ):
			
			acf_form_data(array( 
				'post_id'	=> $post_id, 
				'nonce'		=> 'taxonomy',
			));
			
			foreach( $field_groups as $field_group ): 
				
				$fields = acf_get_fields( array('field_group' => $field_group['ID']) );

				acf_render_fields( $post_id, $fields, 'div', 'field' );
				
			endforeach; 
			
			?>
			<script type="text/javascript">
			(function($) {
			
				$(document).ready(function(){
					
					// update acf validation class
					acf.validation.error_class = 'form-invalid';
					
					
					// events
					$('#submit').on('click', function( e ){
						
						// bail early if this form does not contain ACF data
						if( ! $('#addtag').find('#acf-form-data').exists() )
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
						acf.validation.fetch( $('#addtag') );
						
						
						// stop all other click events on this input
						return false;
					});
				
				});

				
			})(jQuery);	
			</script>
			<?php
			
		endif;
		
	}
	
	
	/*
	*  edit_term
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
	
	function edit_term( $term, $taxonomy )
	{
		// vars
		$post_id = "{$taxonomy}_{$term->term_id}";
		$args = array(
			'taxonomy' => $taxonomy
		);
		
		
		// get field groups
		$field_groups = acf_get_field_groups( $args );
		
		
		// render
		if( !empty($field_groups) ):
			
			acf_form_data(array( 
				'post_id'	=> $post_id, 
				'nonce'		=> 'taxonomy' 
			));
			
			foreach( $field_groups as $field_group ): 
				
				$fields = acf_get_fields( array('field_group' => $field_group['ID']) );
				
				?>
				<?php if( $field_group['style'] == 'default' ): ?>
					<h3><?php echo $field_group['title']; ?></h3>
				<?php endif; ?>
				<table class="form-table">
					<tbody>
						<?php acf_render_fields( $post_id, $fields, 'tr', 'field' ); ?>
					</tbody>
				</table>
				<?php 
				
			endforeach; 
		
		endif;
		
	}
	
	
	/*
	*  save_term
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
	
	function save_term( $term_id, $tt_id, $taxonomy ) {
		
		// verify and remove nonce
		if( ! acf_verify_nonce('taxonomy') )
		{
			return $term_id;
		}
		
	    
	    // save data
		acf_save_post( "{$taxonomy}_{$term_id}" );	
		
			
	}
	
	
	/*
	*  delete_term
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
	
	function delete_term( $term, $tt_id, $taxonomy, $deleted_term )
	{
		global $wpdb;
		
		$values = $wpdb->query($wpdb->prepare(
			"DELETE FROM $wpdb->options WHERE option_name LIKE %s",
			'%' . $taxonomy . '_' . $term . '%'
		));
	}
			
}

new acf_controller_taxonomy();

?>