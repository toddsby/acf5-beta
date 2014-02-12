<?php 

class acf_field_group {

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
		add_action( 'admin_enqueue_scripts',							array( $this,'admin_enqueue_scripts' ) );
		add_action( 'save_post',										array( $this,'save_post' ) );
		
		// ajax
		add_action( 'wp_ajax_acf/field_group/render_field_options',		array( $this, 'ajax_render_field_options') );
		add_action( 'wp_ajax_acf/field_group/render_location_value',	array( $this, 'ajax_render_location_value') );
		add_action( 'wp_ajax_acf/field_group/move_field',				array( $this, 'ajax_move_field') );
		
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
	
	function validate_page() {
		
		// global
		global $pagenow, $typenow;
		

		// vars
		$r = false;
		
		
		// validate page
		if( in_array( $pagenow, array('post.php', 'post-new.php') ) )
		{
		
			// validate post type
			if( $typenow == 'acf-field-group' )
			{
				$r = true;
			}
			
		}
		
		
		// return
		return $r;
	}
	
	
	/*
	*  admin_enqueue_scripts
	*
	*  This function will add the already registered css
	*
	*  @type	function
	*  @date	28/09/13
	*  @since	5.0.0
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
		
		
		// no autosave
		wp_dequeue_script( 'autosave' );
		
		
		// custom scripts
		wp_enqueue_style( 'acf-field-group' );
		wp_enqueue_script( 'acf-field-group' );
    
		
		// disable JSON to avoid conflicts between DB and JSON
		acf_update_setting('json', false);
		
		
		// actions
		add_action( 'admin_head', array( $this,'admin_head' ) );
		
				
		// 3rd party hook
		do_action( 'acf/field_group/admin_enqueue_scripts' );
		
	}
	
	
	/*
	*  admin_head
	*
	*  This function will setup all functionality for the field group edit page to work
	*
	*  @type	action (admin_head)
	*  @date	23/06/12
	*  @since	3.1.8
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function admin_head() {
		
		// global
		global $post;
		
		
		// vars
		$l10n = array(
			'move_to_trash'		=>	__("Move to trash. Are you sure?",'acf'),
			'checked'			=>	__("checked",'acf'),
			'no_fields'			=>	__("No toggle fields available",'acf'),
			'title'				=>	__("Field group title is required",'acf'),
			'copy'				=>	__("copy",'acf'),
			'or'				=>	__("or",'acf'),
			'fields'			=>	__("Fields",'acf'),
			'parent_fields'		=>	__("Parent fields",'acf'),
			'sibling_fields'	=>	__("Sibling fields",'acf'),
			'hide_show_all'		=>	__("Hide / Show All",'acf'),
			'move_field'		=>	__("Move Custom Field",'acf')
		);
		
		$o = array(
			'post_id'			=>	$post->ID,
			'nonce'				=>	wp_create_nonce( 'acf-nonce' ),
			'admin_url'			=>	admin_url(),
			'ajaxurl'			=>	admin_url( 'admin-ajax.php' )
		);
		
		?>
		<script type="text/javascript">
		(function($) {
			
			acf.o = <?php echo json_encode( $o ); ?>;
			acf.l10n = <?php echo json_encode( $l10n ); ?>;
			
		})(jQuery);	
		</script>
		<?php
		
		
		// metaboxes
		add_meta_box('acf-field-group-fields', __("Fields",'acf'), array($this, 'mb_fields'), 'acf-field-group', 'normal', 'high');
		add_meta_box('acf-field-group-locations', __("Location",'acf'), array($this, 'mb_locations'), 'acf-field-group', 'normal', 'high');
		add_meta_box('acf-field-group-options', __("Options",'acf'), array($this, 'mb_options'), 'acf-field-group', 'normal', 'high');
		
		
		// add screen settings
		//add_filter('screen_settings', array($this, 'screen_settings'), 10, 1);
		
		
		// 3rd party hook
		do_action('acf/field_group/admin_head');
		
		
		// hidden $_POST data
		add_action( 'edit_form_after_title', array($this, 'edit_form_after_title') );
		
	}
	
	
	/*
	*  edit_form_after_title
	*
	*  This action will allow ACF to render metaboxes after the title
	*
	*  @type	action
	*  @date	17/08/13
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function edit_form_after_title() {
		
		?>
		<div id="acf-form-data" class="acf-hidden">
			<input type="hidden" name="_acfnonce" value="<?php echo wp_create_nonce( 'field_group' ); ?>" />
			<input type="hidden" name="_acf_delete_fields" value="0" id="input-delete-fields" />
			<?php do_action('acf/field_group/form_data'); ?>
		</div>
		<?php

	}
	
	
	/*
	*  save_post
	*
	*  This function will save all the field group data
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	1.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function save_post( $post_id ) {
		
		
		// do not save if this is an auto save routine
		if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
		{
			return $post_id;
		}
		
		
		// only save once! WordPress save's a revision as well.
		if( wp_is_post_revision($post_id) )
		{
	    	return $post_id;
        }
        
        
		// verify nonce
		if( !acf_verify_nonce('field_group') )
		{
			return $post_id;
		}
        
        
        // disable JSON to avoid conflicts between DB and JSON
		acf_update_setting('json', false);
		
        
        // delete fields
        if( $_POST['_acf_delete_fields'] )
        {
	    	$ids = explode('|', $_POST['_acf_delete_fields']);
	    	$ids = array_map( 'intval', $ids );
	    	
			foreach( $ids as $id )
			{
				if( $id != 0 )
				{
					acf_delete_field( $id );
				}
			}  
        }
        
        
        // save fields
		unset( $_POST['acf_fields']['acfcloneindex'] );
		
		if( !empty($_POST['acf_fields']) )
		{
			foreach( $_POST['acf_fields'] as $field )
			{
				// vars
				$specific = false;
				
				
				// only saved field if has changed
				if( ! acf_extract_var( $field, 'changed' ) )
				{
					$specific = array(
						'menu_order',
						'post_parent',
					);
				}
				
				
				// add args
				// set only field_group which allows the parent to be 0 (parent) or 123 (sub field)
				$field['field_group'] = $post_id;
				
				
				// save field
				acf_update_field( $field, $specific );
			}
		}
		
		
		// add args
        $_POST['acf_field_group']['ID'] = $post_id;
        $_POST['acf_field_group']['title'] = $_POST['post_title'];
        
        
		// save field group
        acf_update_field_group( $_POST['acf_field_group'] );
		
		
        // return
        return $post_id;
	}
	
	
	/*
	*  mb_fields
	*
	*  This function will render the HTML for the medtabox 'acf-field-group-fields'
	*
	*  @type	function
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function mb_fields() {
		
		// vars
		$field_group = acf_get_field_group();
		
		
		// get fields
		$view = array(
			'fields' => acf_get_fields_by_id( $field_group['ID'] )
		);
		
		
		// load view
		acf_get_view('field-group-fields', $view);
		
	}
	
	
	/*
	*  mb_options
	*
	*  This function will render the HTML for the medtabox 'acf-field-group-options'
	*
	*  @type	function
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function mb_options() {
		
		include( acf_get_path('admin/views/field-group-options.php') );
		
	}
	
	
	/*
	*  mb_locations
	*
	*  This function will render the HTML for the medtabox 'acf-field-group-locations'
	*
	*  @type	function
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function mb_locations() {
		
		include( acf_get_path('admin/views/field-group-locations.php') );
		
	}
	
	
	/*
	*  render_location_value
	*
	*  This function will render out an input containing location rule values for the given args
	*
	*  @type	function
	*  @date	30/09/13
	*  @since	5.0.0
	*
	*  @param	$options (array)
	*  @return	N/A
	*/
	
	function render_location_value( $options )
	{
		// vars
		$defaults = array(
			'group_id'	=> 0,
			'rule_id'	=> 0,
			'value'		=> null,
			'param'		=> null,
		);
		
		$options = wp_parse_args( $options, $defaults );
		
		
		// vars
		$choices = array();
		
		
		// some case's have the same outcome
		if( $options['param'] == "page_parent" )
		{
			$options['param'] = "page";
		}

		
		switch( $options['param'] )
		{
			case "post_type" :
				
				// all post types except attachment
				$choices = acf_get_post_types();

				break;
			
			
			case "page" :
				
				$post_types = get_post_types( array('capability_type'  => 'page') );
				unset( $post_types['attachment'], $post_types['revision'] , $post_types['nav_menu_item'], $post_types['acf-field'], $post_types['acf-field-group']  );
				
				if( $post_types )
				{
					foreach( $post_types as $post_type )
					{
						$posts = get_posts(array(
							'posts_per_page'			=>	-1,
							'post_type'					=> $post_type,
							'orderby'					=> 'menu_order title',
							'order'						=> 'ASC',
							'post_status'				=> 'any',
							'suppress_filters'			=> false,
							'update_post_meta_cache'	=> false,
						));
						
						if( $posts )
						{
							// sort into hierachial order!
							if( is_post_type_hierarchical( $post_type ) )
							{
								$posts = get_page_children( 0, $posts );
							}
							
							$choices[ $post_type ] = array();
							
							foreach( $posts as $p )
							{
								// vars
								$title = '';
								
								
								// ancestors?
								if( $ancestors = get_post_ancestors( $p ) )
								{
									$title .= str_repeat('-', count($ancestors) ) . ' ';
								}
								
								
								// title
								$title .= get_the_title( $p );
								
								
								// status
								if( get_post_status($p) != "publish" )
								{
									$title .= " ({$p->post_status})";
								}
								
								
								// append to choices
								$choices[ $post_type ][ $p->ID ] = $title;
								
							}
							// foreach($pages as $page)
						}
						// if( $pages )
					}
					// foreach( $post_types as $post_type )
					
					
					//  only 1 post type?
					if( count($post_types) == 1 )
					{
						$choices = array_pop( $choices );
					}
					
				}
				// if( $post_types )
				
				break;
			
			
			case "page_type" :
				
				$choices = array(
					'front_page'	=>	__("Front Page",'acf'),
					'posts_page'	=>	__("Posts Page",'acf'),
					'top_level'		=>	__("Top Level Page (parent of 0)",'acf'),
					'parent'		=>	__("Parent Page (has children)",'acf'),
					'child'			=>	__("Child Page (has parent)",'acf'),
				);
								
				break;
				
			case "page_template" :
				
				$choices = array(
					'default'	=>	__("Default Template",'acf'),
				);
				
				$templates = get_page_templates();
				
				foreach( $templates as $k => $v )
				{
					$choices[ $v ] = $k;
				}
				
				break;
			
			case "post" :
				
				$post_types = get_post_types( array('capability_type'  => 'post') );
				unset( $post_types['attachment'], $post_types['revision'] , $post_types['nav_menu_item'], $post_types['acf-field'], $post_types['acf-field-group']  );
				
				if( $post_types )
				{
					foreach( $post_types as $post_type )
					{
						
						$posts = get_posts(array(
							'numberposts' => '-1',
							'post_type' => $post_type,
							'post_status' => array('publish', 'private', 'draft', 'inherit', 'future'),
							'suppress_filters' => false,
						));
						
						
						if( $posts )
						{
							$choices[ $post_type ] = array();
							
							foreach( $posts as $p )
							{
								// title
								$title = get_the_title( $p );
								
								
								// status
								if( get_post_status($p) != "publish" )
								{
									$title .= " ({$p->post_status})";
								}
								
								
								// append to choices
								$choices[ $post_type ][ $p->ID ] = $title;
								
							}
							// foreach($pages as $page)
						}
						// if( $pages )
					}
					// foreach( $post_types as $post_type )
					
					
					//  only 1 post type?
					if( count($post_types) == 1 )
					{
						$choices = array_pop( $choices );
					}
					
				}
				// if( $post_types )
				
				
				break;
			
			case "post_category" :
				
				$category_ids = get_all_category_ids();
		
				foreach( $category_ids as $cat_id ) 
				{
					$cat_name = get_cat_name( $cat_id );
					$choices[ $cat_id ] = $cat_name;
				}
				
				break;
			
			case "post_format" :
				
				$choices = get_post_format_strings();
								
				break;
			
			case "post_status" :
				
				$choices = array(
					'publish'	=> __( 'Publish' ),
					'pending'	=> __( 'Pending Review' ),
					'draft'		=> __( 'Draft' ),
					'future'	=> __( 'Future' ),
					'private'	=> __( 'Private' ),
					'inherit'	=> __( 'Revision' ),
					'trash'		=> __( 'Trash' )
				);
								
				break;
			
			case "user_type" :
				
				global $wp_roles;
				
				$choices = $wp_roles->get_names();

				if( is_multisite() )
				{
					$choices['super_admin'] = __('Super Admin');
				}
								
				break;
			
			case "post_taxonomy" :
				
				$choices = array();
				//$simple_value = true;
				//$choices = apply_filters('acf/get_taxonomies_for_select', $choices, $simple_value);
								
				break;
			
			case "taxonomy" :
				
				$choices = array(
					'all' => __('All', 'acf')
				);
				
				
				// load available taxonomies
				$taxonomies = get_taxonomies( array('public' => true), 'objects' );
				
				foreach( $taxonomies as $taxonomy )
				{
					$choices[ $taxonomy->name ] = $taxonomy->labels->name;
				}
				
				
				// unset post_format (why is this a public taxonomy?)
				if( isset($choices['post_format']) )
				{
					unset( $choices['post_format']) ;
				}
			
								
				break;
				
			case "media" :
				
				$choices = array('all' => __('All', 'acf'));
			
				break;
			
			
			case "comment" :
				
				$choices = array('all' => __('All', 'acf'));
			
				break;
			
			
			case "user_role" :
				
				global $wp_roles;
				
				$choices = array_merge( array('all' => __('All', 'acf')), $wp_roles->get_names() );
			
				break;
				
				
			case "user_form" :
				
				$choices = array(
					'all' 		=> __('All', 'acf'),
					'edit' 		=> __('Add / Edit', 'acf'),
					'register' 	=> __('Register', 'acf')
				);
			
				break;
			
			case "widget" :
				
				global $wp_widget_factory;
				
				$choices = array(
					'all' 		=> __('All', 'acf'),
				);
				
				
				if( !empty( $wp_widget_factory->widgets ) )
				{
					foreach( $wp_widget_factory->widgets as $widget )
					{
						$choices[ $widget->id_base ] = $widget->name;
					}
					
				}
								
				break;
		}
		
		
		// allow custom location rules
		$choices = apply_filters( 'acf/location/rule_values/' . $options['param'], $choices );
							
		
		// create field
		acf_render_field(array(
			'type'		=> 'select',
			'prefix'	=> "acf_field_group[location][{$options['group_id']}][{$options['rule_id']}]",
			'name'		=> 'value',
			'value'		=> $options['value'],
			'choices'	=> $choices,
		));
		
	}
	
	
	/*
	*  ajax_render_location_value
	*
	*  This function can be accessed via an AJAX action and will return the result from the render_location_value function
	*
	*  @type	function (ajax)
	*  @date	30/09/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function ajax_render_location_value() {
		
		// verify nonce
		if( !isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'acf-nonce') )
		{
			die( 0 );
		}
		
		
		// call function
		$this->render_location_value( $_POST );
		
		
		// die
		die();
								
	}
	
	
	/*
	*  ajax_render_field_options
	*
	*  This function can be accessed via an AJAX action and will return the result from the acf_render_field_options function
	*
	*  @type	function (ajax)
	*  @date	30/09/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function ajax_render_field_options() {
		
		// vars
		$options = array(
			'post_id'	=> 0,
			'nonce'		=> '',
			'prefix'	=> '',
			'type'		=> '',
		);
		
		// load post options
		$options = wp_parse_args($_POST, $options);
		
		
		// verify nonce
		if( ! wp_verify_nonce($options['nonce'], 'acf-nonce') )
		{
			die(0);
		}
		
		
		// required
		if( ! $options['type'] )
		{
			die(0);
		}
		
				
		// render options
		$field = acf_get_valid_field(array(
			'type'		=> $options['type'],
			'name'		=> 'temp',
			'prefix'	=> $options['prefix'],
		));
		
		
		// render
		acf_render_field_options( $field );
		
		
		// die
		die();
								
	}
	
	/*
	*  ajax_move_field
	*
	*  description
	*
	*  @type	function
	*  @date	20/01/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function ajax_move_field() {
		
		// disable JSON to avoid conflicts between DB and JSON
		acf_update_setting('json', false);
		
		
		$args = acf_parse_args($_POST, array(
			'nonce'				=> '',
			'field_id'			=> 0,
			'field_group_id'	=> 0
		));
		
		
		// verify nonce
		if( ! wp_verify_nonce($args['nonce'], 'acf-nonce') )
		{
			die();
		}
		
		
		// confirm?
		if( $args['field_id'] && $args['field_group_id'] )
		{
			$field = acf_get_field($args['field_id']);
			$field_group = acf_get_field_group($args['field_group_id']);
			
			$field['parent'] = $field_group['ID'];
			
			acf_update_field($field);
			
			echo '<p><strong>' . __('Success', 'acf') . '</strong>. ' . sprintf( __('The field %s was moved to field group %s', 'acf'), "'{$field['name']}'", "'{$field_group['title']}'" ). '</p>';
			
			die();
			
		}
		
		
		// get all field groups
		$field_groups = acf_get_field_groups();
		$choices = array();
		
		
		if( !empty($field_groups) )
		{
			foreach( $field_groups as $field_group )
			{
				if( $field_group['ID'] )
				{
					$choices[ $field_group['ID'] ] = $field_group['title'];
				}
			}
		}
		
		// render options
		$field = acf_get_valid_field(array(
			'type'		=> 'select',
			'name'		=> 'acf_field_group',
			'choices'	=> $choices
		));
		
		
		echo '<p>' . __('Please select the field group you wish this field to move to', 'acf') . '</p>';
		
		echo '<form id="acf-move-field-form">';
		
			// render
			acf_render_field_wrap( $field );
			
			echo '<button type="submit" class="acf-button blue">Move Field</button>';
			
		echo '</form>';
		
		
		// die
		die();
		
	}
	
	
}


// initialize
new acf_field_group();

?>