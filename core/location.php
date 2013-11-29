<?php 

class acf_location {

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
	
	function __construct()
	{
		// ajax
		//add_action('wp_ajax_acf/location/match_field_groups_ajax', array($this, 'match_field_groups_ajax'));
		
		
		// filters
		//add_filter('acf/location/match_field_groups', array($this, 'match_field_groups'), 10, 2);
		//add_filter( 'acf/get_field_groups',						array( $this, 'get_field_groups'), 999, 2 );
		
		// Basic
		add_filter( 'acf/location/rule_match/post_type',		array($this, 'rule_match_post_type'), 10, 3 );
		add_filter( 'acf/location/rule_match/user_type',		array($this, 'rule_match_user_type'), 10, 3 );
		
		// Page
		add_filter( 'acf/location/rule_match/page',				array($this, 'rule_match_post'), 10, 3 );
		add_filter( 'acf/location/rule_match/page_type',		array($this, 'rule_match_page_type'), 10, 3 );
		add_filter( 'acf/location/rule_match/page_parent',		array($this, 'rule_match_page_parent'), 10, 3 );
		add_filter( 'acf/location/rule_match/page_template',	array($this, 'rule_match_page_template'), 10, 3 );
		
		// Post
		add_filter( 'acf/location/rule_match/post',				array($this, 'rule_match_post'), 10, 3 );
		add_filter( 'acf/location/rule_match/post_category',	array($this, 'rule_match_post_category'), 10, 3 );
		add_filter( 'acf/location/rule_match/post_format',		array($this, 'rule_match_post_format'), 10, 3 );
		add_filter( 'acf/location/rule_match/post_status',		array($this, 'rule_match_post_status'), 10, 3 );
		add_filter( 'acf/location/rule_match/post_taxonomy',	array($this, 'rule_match_post_taxonomy'), 10, 3 );
		
		// User
		add_filter( 'acf/location/rule_match/user_form',		array($this, 'rule_match_user_form'), 10, 3 );
		add_filter( 'acf/location/rule_match/user_role',		array($this, 'rule_match_user_role'), 10, 3 );
		
		
		// Form
		add_filter( 'acf/location/rule_match/taxonomy',			array($this, 'rule_match_taxonomy'), 10, 3 );
		add_filter( 'acf/location/rule_match/attachment',		array($this, 'rule_match_attachment'), 10, 3 );
		add_filter( 'acf/location/rule_match/comment',			array($this, 'rule_match_comment'), 10, 3 );
		add_filter( 'acf/location/rule_match/widget',			array($this, 'rule_match_widget'), 10, 3 );
		
		
		// Options Page
		add_filter( 'acf/location/rule_match/options_page',		array($this, 'rule_match_options_page'), 10, 3 );
	}
	
	
	/*
	*  match_field_groups_ajax
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 3/01/13
	*/
	
	/*
function match_field_groups_ajax()
	{
		
		// vars
		$options = array(
			'nonce' => '',
			'ajax' => true
		);
		
		
		// load post options
		$options = array_merge($options, $_POST);
		
		
		// verify nonce
		if( ! wp_verify_nonce($options['nonce'], 'acf_nonce') )
		{
			die(0);
		}
		
		
		// return array
		$return = apply_filters( 'acf/location/match_field_groups', array(), $options );
		
		
		// echo json
		echo json_encode( $return );
		
		
		die();	
	}
	
*/
	
	
	/*
	*  rule_match_post_type
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 3/01/13
	*/
	
	function rule_match_post_type( $match, $rule, $options )
	{
		$post_type = $options['post_type'];

		if( !$post_type )
		{
			if( !$options['post_id'] )
			{
				return false;
			}
			
			$post_type = get_post_type( $options['post_id'] );
		}
		
		
        if( $rule['operator'] == "==" )
        {
        	$match = ( $post_type === $rule['value'] );
        }
        elseif( $rule['operator'] == "!=" )
        {
        	$match = ( $post_type !== $rule['value'] );
        }
        
	
		return $match;
	}
	
	
	/*
	*  rule_match_post
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 3/01/13
	*/
	
	function rule_match_post( $match, $rule, $options )
	{
		// validation
		if( !$options['post_id'] )
		{
			return false;
		}
		
		
		// translate $rule['value']
		// - this variable will hold the original post_id, but $options['post_id'] will hold the translated version
		//if( function_exists('icl_object_id') )
		//{
		//	$rule['value'] = icl_object_id( $rule['value'], $options['post_type'], true );
		//}
		
		
        if($rule['operator'] == "==")
        {
        	$match = ( $options['post_id'] == $rule['value'] );
        }
        elseif($rule['operator'] == "!=")
        {
        	$match = ( $options['post_id'] != $rule['value'] );
        }
        
        return $match;

	}
	
	
	/*
	*  rule_match_page_type
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 3/01/13
	*/
	
	function rule_match_page_type( $match, $rule, $options )
	{
		// validation
		if( !$options['post_id'] )
		{
			return false;
		}

		$post = get_post( $options['post_id'] );
		        
        if( $rule['value'] == 'front_page')
        {
        	
	        $front_page = (int) get_option('page_on_front');
	        
	        
	        if($rule['operator'] == "==")
	        {
	        	$match = ( $front_page == $post->ID );
	        }
	        elseif($rule['operator'] == "!=")
	        {
	        	$match = ( $front_page != $post->ID );
	        }
	        
        }
        elseif( $rule['value'] == 'posts_page')
        {
        
	        $posts_page = (int) get_option('page_for_posts');
	        
	        
	        if($rule['operator'] == "==")
	        {
	        	$match = ( $posts_page == $post->ID );
	        }
	        elseif($rule['operator'] == "!=")
	        {
	        	$match = ( $posts_page != $post->ID );
	        }
	        
        }
        elseif( $rule['value'] == 'top_level')
        {
        	$post_parent = $post->post_parent;
        	if( $options['page_parent'] )
        	{
	        	$post_parent = $options['page_parent'];
        	}
        	
        	
	        if($rule['operator'] == "==")
	        {
	        	$match = ( $post_parent == 0 );
	        }
	        elseif($rule['operator'] == "!=")
	        {
	        	$match = ( $post_parent != 0 );
	        }
	        
        }
        elseif( $rule['value'] == 'parent')
        {
        
        	$children = get_pages(array(
        		'post_type' => $post->post_type,
        		'child_of' =>  $post->ID,
        	));
        	
	        
	        if($rule['operator'] == "==")
	        {
	        	$match = ( count($children) > 0 );
	        }
	        elseif($rule['operator'] == "!=")
	        {
	        	$match = ( count($children) == 0 );
	        }
	        
        }
        elseif( $rule['value'] == 'child')
        {
        
        	$post_parent = $post->post_parent;
        	if( $options['page_parent'] )
        	{
	        	$post_parent = $options['page_parent'];
        	}
	        
	        
	        if($rule['operator'] == "==")
	        {
	        	$match = ( $post_parent != 0 );
	        }
	        elseif($rule['operator'] == "!=")
	        {
	        	$match = ( $post_parent == 0 );
	        }
	        
        }
        
        return $match;

	}
	
	
	/*
	*  rule_match_page_parent
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 3/01/13
	*/
	
	function rule_match_page_parent( $match, $rule, $options )
	{
		// validation
		if( !$options['post_id'] )
		{
			return false;
		}
		
		
		// vars
		$post = get_post( $options['post_id'] );
		
		$post_parent = $post->post_parent;
    	if( $options['page_parent'] )
    	{
        	$post_parent = $options['page_parent'];
    	}
        
        
        if($rule['operator'] == "==")
        {
        	$match = ( $post_parent == $rule['value'] );
        }
        elseif($rule['operator'] == "!=")
        {
        	$match = ( $post_parent != $rule['value'] );
        }
        
        
        return $match;

	}
	
	
	/*
	*  rule_match_page_template
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 3/01/13
	*/
	
	function rule_match_page_template( $match, $rule, $options )
	{
		$page_template = $options['page_template'];
		if( ! $page_template )
		{
			$page_template = get_post_meta( $options['post_id'], '_wp_page_template', true );
		}
		
		
		if( ! $page_template )
		{
			$post_type = $options['post_type'];

			if( !$post_type )
			{
				$post_type = get_post_type( $options['post_id'] );
			}
			
			if( $post_type == 'page' )
			{
				$page_template = "default";
			}
		}
		
		
		
        if($rule['operator'] == "==")
        {
        	$match = ( $page_template === $rule['value'] );
        }
        elseif($rule['operator'] == "!=")
        {
        	$match = ( $page_template !== $rule['value'] );
        }
                
        return $match;

	}
	
	
	/*
	*  rule_match_post_category
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 3/01/13
	*/
	
	function rule_match_post_category( $match, $rule, $options )
	{
		// validate
		if( !$options['post_id'] )
		{
			return false;
		}

		
		// post type
		if( !$options['post_type'] )
		{
			$options['post_type'] = get_post_type( $options['post_id'] );
		}
		
		
		
		// vars
		$taxonomies = get_object_taxonomies( $options['post_type'] );
		$terms = $options['post_taxonomy'];
		
		
		// not AJAX 
		if( !$options['ajax'] )
		{
			// no terms? Load them from the post_id
			if( empty($terms) )
			{
				$all_terms = get_the_terms( $options['post_id'], 'category' );
				if($all_terms)
				{
					foreach($all_terms as $all_term)
					{
						$terms[] = $all_term->term_id;
					}
				}
			}
			
			
			// no terms at all? 
			if( empty($terms) )
			{
				// If no ters, this is a new post and should be treated as if it has the "Uncategorized" (1) category ticked
				if( is_array($taxonomies) && in_array('category', $taxonomies) )
				{
					$terms[] = '1';
				}
			}
		}
		

        
        if($rule['operator'] == "==")
        {
        	$match = false;
        	
        	if($terms)
			{
				if( in_array($rule['value'], $terms) )
				{
					$match = true; 
				}
			}
  
        }
        elseif($rule['operator'] == "!=")
        {
        	$match = true;
        	
        	if($terms)
			{
				if( in_array($rule['value'], $terms) )
				{
					$match = false; 
				}
			}

        }
    
        
        return $match;
        
    }
    
    
    /*
	*  rule_match_user_type
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 3/01/13
	*/
	
	function rule_match_user_type( $match, $rule, $options )
	{
		$user = wp_get_current_user();
 
        if( $rule['operator'] == "==" )
		{
			if( $rule['value'] == 'super_admin' )
			{
				$match = is_super_admin( $user->ID );
			}
			else 
			{
				$match = in_array( $rule['value'], $user->roles );
			}
			
		}
		elseif( $rule['operator'] == "!=" )
		{
			if( $rule['value'] == 'super_admin' )
			{
				$match = !is_super_admin( $user->ID );
			}
			else 
			{
				$match = ( ! in_array( $rule['value'], $user->roles ) );
			}
		}
        
        return $match;
        
    }
    
    
    /*
	*  rule_match_user_type
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 3/01/13
	*/
	
	function rule_match_options_page( $match, $rule, $options )
	{
		global $plugin_page;
		    	
		    	
		// older location rules may be "options-pagename"
		if( substr($rule['value'], 0, 8) == 'options-' )
		{
			$rule['value'] = 'acf-' . $rule['value'];
		}
		
		
		// older location ruels may be "Pagename"
		if( substr($rule['value'], 0, 11) != 'acf-options' )
		{
			$rule['value'] = 'acf-options-' . sanitize_title( $rule['value'] );
			
			// value may now be wrong (acf-options-options)
			if( $rule['value'] == 'acf-options-options' )
			{
				$rule['value'] = 'acf-options';
			}
		}
		
		
		if($rule['operator'] == "==")
        {
        	$match = ( $plugin_page === $rule['value'] );
        }
        elseif($rule['operator'] == "!=")
        {
        	$match = ( $plugin_page !== $rule['value'] );
        }
        
        
        return $match;
        
    }
    
    
    /*
	*  rule_match_post_format
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 3/01/13
	*/
	
	function rule_match_post_format( $match, $rule, $options )
	{
		// vars
		$post_format = $options['post_format'];
		if( !$post_format )
		{	
			// validate
			if( !$options['post_id'] )
			{
				return false;
			}
			
			
			// post type
			if( !$options['post_type'] )
			{
				$options['post_type'] = get_post_type( $options['post_id'] );
			}
			
		
			// does post_type support 'post-format'
			if( post_type_supports( $options['post_type'], 'post-formats' ) )
			{
				$post_format = get_post_format( $options['post_id'] );
				
				if( $post_format === false )
				{
					$post_format = 'standard';
				}
			}
		}

       	
       	if($rule['operator'] == "==")
        {
        	$match = ( $post_format === $rule['value'] );
        	 
        }
        elseif($rule['operator'] == "!=")
        {
        	$match = ( $post_format !== $rule['value'] );
        }
        
        
        
        return $match;
        
    }
    
    
    /*
	*  rule_match_post_status
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 3/01/13
	*/
	
	function rule_match_post_status( $match, $rule, $options )
	{
		// validate
		if( !$options['post_id'] )
		{
			return false;
		}
		
					
		// vars
		$post_status = get_post_status( $options['post_id'] );
	    
	    
	    // auto-draft = draft
	    if( $post_status == 'auto-draft' )
	    {
		    $post_status = 'draft';
	    }
	    
	    
	    // match
	    if($rule['operator'] == "==")
        {
        	$match = ( $post_status === $rule['value'] );
        	 
        }
        elseif($rule['operator'] == "!=")
        {
        	$match = ( $post_status !== $rule['value'] );
        }
        
        
        // return
	    return $match;
        
    }
    
    
    /*
	*  rule_match_post_taxonomy
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 3/01/13
	*/
	
	function rule_match_post_taxonomy( $match, $rule, $options )
	{
		// validate
		if( !$options['post_id'] )
		{
			return false;
		}
		
		
		// post type
		if( !$options['post_type'] )
		{
			$options['post_type'] = get_post_type( $options['post_id'] );
		}
		
		
		// vars
		$taxonomies = get_object_taxonomies( $options['post_type'] );
		$terms = $options['taxonomy'];
		
		
		// not AJAX 
		if( !$options['ajax'] )
		{
			// no terms? Load them from the post_id
			if( empty($terms) )
			{
	        	if( is_array($taxonomies) )
	        	{
		        	foreach( $taxonomies as $tax )
					{
						$all_terms = get_the_terms( $options['post_id'], $tax );
						if($all_terms)
						{
							foreach($all_terms as $all_term)
							{
								$terms[] = $all_term->term_id;
							}
						}
					}
				}
			}
			
			
			// no terms at all? 
			if( empty($terms) )
			{
				// If no ters, this is a new post and should be treated as if it has the "Uncategorized" (1) category ticked
				if( is_array($taxonomies) && in_array('category', $taxonomies) )
				{
					$terms[] = '1';
				}
			}
		}

        
        if($rule['operator'] == "==")
        {
        	$match = false;
        	
        	if($terms)
			{
				if( in_array($rule['value'], $terms) )
				{
					$match = true; 
				}
			}
  
        }
        elseif($rule['operator'] == "!=")
        {
        	$match = true;
        	
        	if($terms)
			{
				if( in_array($rule['value'], $terms) )
				{
					$match = false; 
				}
			}

        }
    
        
        return $match;
        
    }
    
    
    /*
	*  rule_match_taxonomy
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 3/01/13
	*/
	
	function rule_match_taxonomy( $match, $rule, $options )
	{
	
		$taxonomy = $options['taxonomy'];
		
		
		if( $taxonomy )
		{
			if($rule['operator'] == "==")
	        {
	        	$match = ( $taxonomy == $rule['value'] );
	        	
	        	// override for "all"
		        if( $rule['value'] == "all" )
				{
					$match = true;
				}
				
	        }
	        elseif($rule['operator'] == "!=")
	        {
	        	$match = ( $taxonomy != $rule['value'] );
	        		
	        	// override for "all"
		        if( $rule['value'] == "all" )
				{
					$match = false;
				}
				
	        }
		}
		
        
        return $match;
        
    }
    
    
    /*
	*  rule_match_user_role
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 3/01/13
	*/
	
	function rule_match_user_role( $match, $rule, $options ) {
		
		// vars
		$user_id = $options['user_id'];
		
		
		if( $user_id )
		{
			if($rule['operator'] == "==")
	        {
	        	if( $user_id === 'new' )
	        	{
	        		// case: add user
		        	$match = ( $rule['value'] == get_option('default_role') );
	        	}
	        	else
	        	{
	        		// case: edit user
		        	$match = ( user_can($user_id, $rule['value']) );
	        	}
	        	
	        	
	        	// override for "all"
		        if( $rule['value'] === 'all' )
				{
					$match = true;
				}
	        }
	        elseif($rule['operator'] == "!=")
	        {
	        	if( $user_id === 'new' )
	        	{
	        		// case: add user
		        	$match = ( $rule['value'] != get_option('default_role') );
	        	}
	        	else
	        	{
	        		// case: edit user
		        	$match = ( !user_can($user_id, $rule['value']) );
	        	}
	        	
	        	
	        	// override for "all"
		        if( $rule['value'] === 'all' )
				{
					$match = false;
				}
	        }
		}
		
        
        return $match;
        
    }
    
    
    /*
    *  rule_match_user_form
    *
    *  description
    *
    *  @type	function
    *  @date	28/11/2013
    *  @since	5.0.0
    *
    *  @param	$post_id (int)
    *  @return	$post_id (int)
    */
    
    function rule_match_user_form( $match, $rule, $options ) {
		
		// vars
		$user_form = $options['user_form'];
		
		
		// add is treated the same as edit
		if( $user_form === 'add' )
		{
			$user_form = 'edit';
		}
		
		
		// compare
		if( $user_form )
		{
			if($rule['operator'] == "==")
	        {
	        	$match = ( $user_form == $rule['value'] );
	        	
	        	
	        	// override for "all"
		        if( $rule['value'] === 'all' )
				{
					$match = true;
				}
	        }
	        elseif($rule['operator'] == "!=")
	        {
	        	$match = ( $user_form != $rule['value'] );
	        	
	        	
	        	// override for "all"
		        if( $rule['value'] === 'all' )
				{
					$match = false;
				}
	        }
		}
		
        
        // return
        return $match;
    }
    
    
    /*
	*  rule_match_comment
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 3/01/13
	*/
	
	function rule_match_comment( $match, $rule, $options )
	{
		$comment = $options['comment'];
		
		
		if( $comment )
		{
			if($rule['operator'] == "==")
	        {
	        	
	        	$match = ( $comment == $rule['value'] );
	        	
	        	// override for "all"
		        if( $rule['value'] == "all" )
				{
					$match = true;
				}
				
	        }
	        elseif($rule['operator'] == "!=")
	        {
	        	$match = ( $comment != $rule['value'] );
	        		
	        	// override for "all"
		        if( $rule['value'] == "all" )
				{
					$match = false;
				}
				
	        }
		}
		
		
        
        return $match;
        
    }
    
    
    /*
    *  rule_match_widget
    *
    *  description
    *
    *  @type	function
    *  @date	28/11/2013
    *  @since	5.0.0
    *
    *  @param	$post_id (int)
    *  @return	$post_id (int)
    */
    
    function rule_match_widget( $match, $rule, $options ) {
		
		// vars
		$widget = $options['widget'];
		
		
		// compare
		if( $widget )
		{
			if($rule['operator'] == "==")
	        {
	        	$match = ( $widget == $rule['value'] );
	        	
	        	
	        	// override for "all"
		        if( $rule['value'] === 'all' )
				{
					$match = true;
				}
	        }
	        elseif($rule['operator'] == "!=")
	        {
	        	$match = ( $widget != $rule['value'] );
	        	
	        	
	        	// override for "all"
		        if( $rule['value'] === 'all' )
				{
					$match = false;
				}
	        }
		}
		
        
        // return
        return $match;
    }
			
}

new acf_location();


/*
*  acf_get_field_group_visibility
*
*  This function will look at the given field group's location rules and compare them against
*  the args given to see if this field group is to be shown or not.
*
*  @type	function
*  @date	7/10/13
*  @since	5.0.0
*
*  @param	$field group (array)
*  @param	$args (array)
*  @return	(boolean)
*/

function acf_get_field_group_visibility( $field_group, $args = array() )
{
	// vars
	$args = acf_parse_args($args, array(
		'post_id'		=> 0,
		'post_type'		=> 0,
		'page_template'	=> 0,
		'page_parent'	=> 0,
		'page_type'		=> 0,
		'post_category'	=> array(),
		'post_format'	=> 0,
		'post_taxonomy'	=> array(),
		'taxonomy'		=> 0,
		'user_id'		=> 0,
		'user_form'		=> 0,
		'attachment'	=> 0,
		'comment'		=> 0,
		'widget'		=> 0,
		'lang'			=> 0,
		'ajax'			=> false
	));
	
	
	// WPML
	if( defined('ICL_LANGUAGE_CODE') )
	{
		$args['lang'] = ICL_LANGUAGE_CODE;
		
		//global $sitepress;
		//$sitepress->switch_lang( $options['lang'] );
	}
	
	
	// vars
	$visibility = false;
	
	
	// loop through location rules
	foreach( $field_group['location'] as $group_id => $group )
	{
		// start of as true, this way, any rule that doesn't match will cause this varaible to false
		$match_group = true;
		
		if( is_array($group) )
		{
			foreach( $group as $rule_id => $rule )
			{
				// $match = true / false
				$match = apply_filters( 'acf/location/rule_match/' . $rule['param'] , false, $rule, $args );
				
				if( !$match )
				{
					$match_group = false;
					break;
				}
				
			}
		}
		
		
		// all rules must havematched!
		if( $match_group )
		{
			$visibility = true;
		}
			
	}

	
	// return
	return $visibility;
}

?>