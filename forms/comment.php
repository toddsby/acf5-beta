<?php 

class acf_controller_comment {
	
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
		
		
		// render
		add_action( 'comment_form_logged_in_after',		array( $this, 'comment_form_after_fields') );
		add_action( 'comment_form_after_fields',		array( $this, 'comment_form_after_fields') );

		
		// save
		add_action( 'edit_comment', 					array( $this, 'save_comment' ), 10, 1 );
		add_action( 'comment_post', 					array( $this, 'save_comment' ), 10, 1 );
		
	}
	
	
	/*
	*  comment_form_after_fields
	*
	*  This function will add fields to the front end comment form
	*
	*  @type	function
	*  @date	19/10/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function comment_form_after_fields() {
		
		// vars
		$post_id = "comment_0";

		
		// render post data
		acf_form_data(array( 
			'post_id'	=> $post_id, 
			'nonce'		=> 'comment' 
		));
		
		
		// get field groups
		$field_groups = acf_get_field_groups(array(
			'comment' => 'new'
		));
		
		
		if( !empty($field_groups) ):
			
			foreach( $field_groups as $field_group ): 
				
				$fields = acf_get_fields( $field_group );
				
				?>
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
		if( $pagenow == 'comment.php' )
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
		add_action( 'add_meta_boxes_comment', array($this, 'add_meta_boxes_comment'), 10, 1 );

	}
	
	
	/*
	*  add_meta_boxes_comment
	*
	*  This function is run on the admin comment.php page and will render the ACF fields within custom metaboxes to look native
	*
	*  @type	function
	*  @date	19/10/13
	*  @since	5.0.0
	*
	*  @param	$comment (object)
	*  @return	n/a
	*/
	
	function add_meta_boxes_comment( $comment ) {
		
		// vars
		$post_id = "comment_{$comment->comment_ID}";

		
		// render post data
		acf_form_data(array( 
			'post_id'	=> $post_id, 
			'nonce'		=> 'comment' 
		));
		
		
		// get field groups
		$field_groups = acf_get_field_groups(array(
			'comment' => $comment->comment_ID
		));
		
		
		// render
		if( !empty($field_groups) ):
			
			foreach( $field_groups as $field_group ): 
				
				$this->render_meta_box( $comment, $field_group );
				
			endforeach; 
		
		endif;
		
	}
	
	
	/*
	*  render_meta_box
	*
	*  This function is used by 'add_meta_boxes_comment' to create the native looking metaboxes
	*
	*  @type	function
	*  @date	19/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function render_meta_box( $comment, $field_group ) {
		
		// vars
		$post_id = "comment_{$comment->comment_ID}";
		
		
		// load fields
		$fields = acf_get_fields( $field_group );
		
		?>
		<div id="acf-<?php echo $field_group['ID']; ?>" class="stuffbox editcomment">
			<h3><?php echo $field_group['title']; ?></h3>
			<div class="inside">
				<table class="form-table">
					<tbody>
						<?php acf_render_fields( $post_id, $fields, 'tr', 'field' ); ?>
					</tbody>
				</table>
			</div>
		</div>
		<?php
		
	}
	
	
	/*
	*  save_comment
	*
	*  description
	*
	*  @type	function
	*  @date	19/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function save_comment( $comment_id ) {
		
		// verify and remove nonce
		if( ! acf_verify_nonce('comment') )
		{
			return $comment_id;
		}
		
	    
	    // save data
	    if( acf_validate_save_post(true) )
		{
			acf_save_post( "comment_{$comment_id}" );		
		}
		
	}
			
}

new acf_controller_comment();

?>