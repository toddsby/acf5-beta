<?php 

class acf_wpml_compatibility {
	
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
	
	function __construct() {
		
		// actions
		add_action('icl_make_duplicate',			array($this, 'icl_make_duplicate'), 10, 4);
		add_action('acf/field_group/admin_head',	array($this, 'admin_head'));
		add_action('acf/input/admin_head',			array($this, 'admin_head'));
		
	}
	
	
	
	/*
	*  icl_make_duplicate
	*
	*  description
	*
	*  @type	function
	*  @date	26/02/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function icl_make_duplicate( $master_post_id, $lang, $postarr, $id ) {
		
		// validate
		if( $postarr['post_type'] != 'acf-field-group' )
		{
			return;
		}
		
		
		// duplicate field group
		acf_duplicate_field_group( $master_post_id, $id );
		

	}
	
	
	/*
	*  admin_head
	*
	*  description
	*
	*  @type	function
	*  @date	27/02/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function admin_head() {
		
		?>
		<script type="text/javascript">
				
		acf.add_filter('prepare_for_ajax', function( args ){
			
			if( typeof icl_this_lang != 'undefined' )
			{
				args.lang = icl_this_lang;
			}
			
			return args;
			
		});
		
		</script>
		<?php
		
	}
	
}

new acf_wpml_compatibility();

?>