<?php 

class acf_update {

	/*
	*  __construct
	*
	*  A good place to add actions / filters
	*
	*  @type	function
	*  @date	11/08/13
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function __construct() {
		
		// actions
		add_action('admin_menu', array($this,'admin_menu'), 20);
		
		
		// ajax
		add_action('wp_ajax_acf/admin/data_upgrade',	array($this, 'ajax_upgrade'));
	}
	
	
	
	/*
	*  ajax_upgrade
	*
	*  description
	*
	*  @type	function
	*  @date	24/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function ajax_upgrade()
   	{
   		// options
   		$options = acf_parse_args( $_POST, array(
			'version'	=>	'',
			'nonce'		=>	'',
		));
		
		
		// validate
		if( ! wp_verify_nonce($options['nonce'], 'acf_nonce') )
		{
			wp_send_json_error();
		}
		
		
		// vars
		$path = acf_get_path("admin/updates/{$options['version']}.php");
		
		
		// load version
		if( !file_exists( $path ) )
		{
			wp_send_json_error();
		}
		
		
		// load any errors / feedback from update
		ob_start();
		
		
		// include
		include( $path );
		
		
		// get feedback
		$feedback = ob_get_clean();
		
		
		// update successful
		update_option('acf_version', $options['version'] );
		
		
		// check for relevant updates. If none are found, update this to the plugin version
		$updates = $this->get_relevant_updates();
		
		if( empty($updates) )
		{
			update_option('acf_version', acf_get_setting('version'));
		}
		
		
		// return
		wp_send_json_success(array(
			'feedback' => $feedback
		));			
	}
	
	
	/*
	*  get_updates
	*
	*  description
	*
	*  @type	function
	*  @date	19/02/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function get_updates() {
		
		// vars
		$versions = array();
		
		
		// add default
		$path = acf_get_path('admin/updates');
		
		
		// check that path exists
		if( !file_exists( $path ) )
		{
			return false;
		}
		
		
		$dir = opendir( $path );
    
	    while(false !== ( $file = readdir($dir)) )
	    {
	    	// only php files
	    	if( substr($file, -4) !== '.php' )
	    	{
		    	continue;
	    	}
	    	
	    	
	    	// read json
	    	$version = substr($file, 0, -4);
	    	
	    	
	    	// append to versions
	        $versions[] = $version;
	    }
	    
	    
	    // reverse
	    //$versions = array_reverse($versions);
	    
	    
	    // return
	    return $versions;
		
	}
	
	
	/*
	*  get_valid_updates
	*
	*  description
	*
	*  @type	function
	*  @date	19/02/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function get_relevant_updates() {
		
		// vars
		$updates = $this->get_updates();
		$plugin_version = acf_get_setting('version');
		$db_version = get_option('acf_version');
		
		
		// unset irrelevant updates
		foreach( $updates as $i => $update_version )
		{
			// unset if update is for a future version. May exist for testing
			if( version_compare( $update_version, $plugin_version, '>') )
			{
				unset($updates[ $i ]);
			}
			
			// unset if update has already been run
			if( version_compare( $update_version, $db_version, '<=') )
			{
				unset($updates[ $i ]);
			}
			
		}
		
		
		// return
		return $updates;
		
	}
	
	
	/*
	*  admin_menu
	*
	*  description
	*
	*  @type	function
	*  @date	19/02/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function admin_menu() {
		
		// update admin page
		$page = add_submenu_page('edit.php?post_type=acf-field-group', __('Upgrade','acf'), __('Upgrade','acf'), 'manage_options','acf-upgrade', array($this,'html') );
		
		
		// vars
		$plugin_version = acf_get_setting('version');
		$db_version = get_option('acf_version');

		
		// bail early if a new install
		if( empty($db_version) )
		{
			update_option('acf_version', $plugin_version );
			return;
		}
		
		
		// bail early if versions match
		if( $plugin_version == $db_version )
		{
			return;
		}
		
		
		// get updates
		$updates = $this->get_relevant_updates();
		
		
		// bail early if no updates
		if( empty($updates) )
		{
			update_option('acf_version', $plugin_version );
			return;
		}
		
		
		// vars
		$l10n = array(
			'h4'	=> __('Data Upgrade Required', 'acf'),
			'p'		=> sprintf(__('%s %s requires some updates to the database', 'acf'), acf_get_setting('name'), $plugin_version),
			'a'		=> __( 'Run the updater', 'acf' )
		);
		
		
		// add notice
		$message = '
		<h4>' . $l10n['h4'] . '</h4>
		<p>' . $l10n['p'] . '
			<a id="acf-run-the-updater" href="' . admin_url('edit.php?post_type=acf-field-group&page=acf-upgrade') . '" class="acf-button blue">
				' . $l10n['a'] . '
			</a>
		</p>
		<script type="text/javascript">
		(function($) {
			
			$("#acf-run-the-updater").on("click", function(){
		
				var answer = confirm("'. __( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'acf' ) . '");
				return answer;
		
			});
			
		})(jQuery);
		</script>';
		
		acf_add_admin_notice( $message, 'acf-update-notice', '' );
		
		
	}
	
	
	/*
	*  html
	*
	*  description
	*
	*  @type	function
	*  @date	19/02/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function html() {
		
		// view
		$view = array(
			'updates' => $this->get_relevant_updates()
		);
		
		
		// load view
		acf_get_view('update', $view);
		
	}
			
}

new acf_update();

?>