<?php 

class acf_field_groups {

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
		
		// actions
		add_action('admin_menu',		array($this,'admin_menu'));
		
		add_action('trashed_post',		array($this,'trashed_post'));
		add_action('untrashed_post',	array($this,'untrashed_post'));
		add_action('deleted_post',		array($this,'deleted_post'));
		
	}
	
	
	/*
	*  admin_menu
	*
	*  This function will validate the page and setup actions & filters for the field-groups admin page
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	5.0.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function admin_menu() {
		
		// validate page
		if( ! $this->validate_page() )
		{
			return;
		}
		
		
		// actions
		add_action( 'admin_enqueue_scripts',						array( $this, 'admin_enqueue_scripts') );
		add_action( 'admin_footer',									array( $this, 'admin_footer' ) );
		
		
		// columns
		add_filter( 'manage_edit-acf-field-group_columns',			array( $this,'field_group_columns' ), 10, 1 );
		add_action( 'manage_acf-field-group_posts_custom_column',	array( $this,'field_group_columns_html' ), 10, 2 );
		
		
		// duplicate
		$this->check_duplicate();
		
	}
	
	
	/*
	*  trashed_post
	*
	*  description
	*
	*  @type	function
	*  @date	8/01/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function trashed_post( $post_id ) {
		
		// validate post type
		if( get_post_type($post_id) != 'acf-field-group' )
		{
			return;
		}
		
		
		// validate action run only once
		remove_action('trashed_post', array($this, 'trashed_post'));
		
		
		// trash field group
		acf_trash_field_group( $post_id );
		
	}
	
	
	/*
	*  untrashed_post
	*
	*  description
	*
	*  @type	function
	*  @date	8/01/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function untrashed_post( $post_id ) {
		
		// validate post type
		if( get_post_type($post_id) != 'acf-field-group' )
		{
			return;
		}
		
		
		// validate action run only once
		remove_action('untrashed_post', array($this, 'untrashed_post'));
		
		
		// trash field group
		acf_untrash_field_group( $post_id );
		
	}
	
	
	/*
	*  deleted_post
	*
	*  description
	*
	*  @type	function
	*  @date	8/01/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function deleted_post( $post_id ) {
		
		// validate post type
		if( get_post_type($post_id) != 'acf-field-group' )
		{
			return;
		}
		
		
		// validate action run only once
		remove_action('deleted_post', array($this, 'deleted_post'));
		
		
		// trash field group
		acf_delete_field_group( $post_id );
		
	}
	
	
	
	/*
	*  check_duplicate
	*
	*  This function will run a duplciate field group function if the correct $_GET parameter exists
	*
	*  @type	function
	*  @date	17/10/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function check_duplicate()
	{
		// message
		if( !empty($_GET['duplicate_complete']) )
		{
			// vars
			$id = filter_var( $_GET['duplicate_complete'], FILTER_SANITIZE_NUMBER_INT );
			
			
			// message
			acf_add_admin_notice('Field group duplicated! Edit the new "<a href="' . get_edit_post_link( $id ) . '">' . get_the_title( $id ) . '</a>" field group.');
		}
		
		
		// duplicate
		if( !empty($_GET['duplicate']) )
		{
			// vars
			$id = filter_var( $_GET['duplicate'], FILTER_SANITIZE_NUMBER_INT );
			
			
			// duplciate
			$field_group = acf_duplicate_field_group( $id );
			
			
			// redirect so the $_GET param is not visible to user
			wp_redirect( admin_url("edit.php?post_type=acf-field-group&duplicate_complete={$field_group['ID']}") );
			exit;
		}
		
	}
	
	
	/*
	*  validate_page
	*
	*  This function will loop at the current page and return true if it is the acf-field-groups edit page
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	3.2.6
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
		if( $pagenow == 'edit.php' )
		{
		
			// validate post type
			if( isset($_GET['post_type']) && $_GET['post_type'] == 'acf-field-group' )
			{
				$r = true;
			}
			
			
			if( isset($_GET['page']) )
			{
				$r = false;
			}
			
		}
		
		
		// return
		return $r;
	}
	
	
	/*
	*  admin_enqueue_scripts
	*
	*  This function will add the already registered css & js
	*
	*  @type	function
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function admin_enqueue_scripts() {
		
		// scripts
		wp_enqueue_script(array(
			'jquery',
			'thickbox',
		));
		
		
		// styles
		wp_enqueue_style(array(
			'thickbox',
		));
	}
		
	
	/*
	*  field_group_columns
	*
	*  This function will customize the columns for the field group table
	*
	*  @type	filter (manage_edit-acf-field-group_columns)
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	$columns (array)
	*  @return	$columns (array)
	*/
	
	function field_group_columns( $columns )
	{
		$columns = array(
			'cb'	 	=> '<input type="checkbox" />',
			'title' 	=> __('Title'),
			'fields' 	=> __('Fields', 'acf'),
		);
		
		return $columns;
	}
	
	
	/*
	*  field_group_columns_html
	*
	*  This function will render the HTML for each table cell
	*
	*  @type	action (manage_posts_custom_column)
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	$column (string)
	*  @param	$post_id (int)
	*  @return	N/A
	*/
	
	function field_group_columns_html( $column, $post_id )
	{
		// vars
		if( $column == 'fields' )
	    {
            echo acf_get_field_count( $post_id );
	    }
	}
	
	
	/*
	*  admin_footer
	*
	*  @description: 
	*  @since 3.1.8
	*  @created: 23/06/12
	*/
	
	function admin_footer() {
		?>
<script type="text/html" id="tmpl-acf-col-side">
<div id="acf-col-side">

	<div class="acf-box">
		<div class="inner">
			<h2><?php _e("Advanced Custom Fields",'acf'); ?> <?php echo acf_get_setting('version'); ?></h2>

			<h3><?php _e("Changelog",'acf'); ?></h3>
			<p><?php _e("See what's new in",'acf'); ?> <a href="<?php echo admin_url('edit.php?post_type=acf-field-group&info=changelog'); ?>"><?php _e("version",'acf'); ?> <?php echo acf_get_setting('version'); ?></a>
			
			<h3><?php _e("Resources",'acf'); ?></h3>
			<ul>
				<li><a href="http://www.advancedcustomfields.com/resources/#getting-started" target="_blank"><?php _e("Getting Started",'acf'); ?></a></li>
				<li><a href="http://www.advancedcustomfields.com/resources/#field-types" target="_blank"><?php _e("Field Types",'acf'); ?></a></li>
				<li><a href="http://www.advancedcustomfields.com/resources/#functions" target="_blank"><?php _e("Functions",'acf'); ?></a></li>
				<li><a href="http://www.advancedcustomfields.com/resources/#actions" target="_blank"><?php _e("Actions",'acf'); ?></a></li>
				<li><a href="http://www.advancedcustomfields.com/resources/#filters" target="_blank"><?php _e("Filters",'acf'); ?></a></li>
				<li><a href="http://www.advancedcustomfields.com/resources/#how-to" target="_blank"><?php _e("'How to' guides",'acf'); ?></a></li>
				<li><a href="http://www.advancedcustomfields.com/resources/#tutorials" target="_blank"><?php _e("Tutorials",'acf'); ?></a></li>
			</ul>
		</div>
		<div class="footer footer-blue">
			<ul class="acf-hl">
				<li><?php _e("Created by",'acf'); ?> Elliot Condon</li>
			</ul>
		</div>
	</div>
</div>
</script>
<script type="text/javascript">
(function($){
	
	// wrap
	$('#wpbody .wrap').attr('id', 'acf-field-group-list');
	
	
	// wrap column main
	$('#acf-field-group-list').wrapInner('<div id="acf-col-main" />');
	
	
	// add column side
	$('#acf-field-group-list').prepend( $('#tmpl-acf-col-side').html() );
	
	
	// wrap columns
	$('#acf-field-group-list').wrapInner('<div id="acf-col-wrap" class="acf-clearfix" />');
		
	
	// take out h2 + icon
	$('#acf-col-main > .icon32').insertBefore('#acf-col-wrap');
	$('#acf-col-main > h2').insertBefore('#acf-col-wrap');
	
	
	// modify row actions
	$('#acf-field-group-list .row-actions').each(function(){
		
		// vars
		var id		= $(this).closest('tr').attr('id').replace('post-', ''),
			$span	= $('<span><a href="<?php echo admin_url('edit.php?post_type=acf-field-group&duplicate='); ?>' + id + '"class="acf-duplicate-field-group"><?php _e('Duplicate', 'acf'); ?></a> | </span>');
		
		$(this).find('.inline').replaceWith( $span );

		
	});
	
})(jQuery);
</script>
		<?php
	}
			
}

new acf_field_groups();

?>