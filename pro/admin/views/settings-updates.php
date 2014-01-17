<?php 

// extract
extract($args);


// vars
$active = $license ? true : false;
$nonce = $active ? 'deactivate_pro_licence' : 'activate_pro_licence';
$input = $active ? 'password' : 'text';
$button = $active ? 'Deactivate License' : 'Activate License';
$readonly = $active ? 1 : 0;

?>
<div class="wrap acf-settings-wrap">
	
	<h2>Updates</h2>
	
	<div class="acf-box">
		<div class="title">
			<h3><?php echo acf_get_setting('name'); ?> License</h3>
		</div>
		<div class="inner">
			<p>To unlock updates, please enter your license key bellow. If you don't have a licence key, you may <a href="#">purchase one</a>.</p>
			
			<form action="" method="post">
			<div class="acf-hidden">
				<input type="hidden" name="_acfnonce" value="<?php echo wp_create_nonce( $nonce ); ?>" />
			</div>
			<table class="form-table">
                <tbody>
                	<tr>
                    	<th>
                    		<label for="acf-field-acf_pro_licence">License Key</label>
                    	</th>
						<td>
							<?php 
							
							// render field
							acf_render_field(array(
								'type'		=> $input,
								'name'		=> 'acf_pro_licence',
								'value'		=> $license,
								'readonly'	=> $readonly
							));
							
							?>
						</td>
					</tr>
					<tr>
						<th></th>
						<td>
							<input type="submit" value="<?php echo $button; ?>" class="acf-button blue">
						</td>
					</tr>
				</tbody>
			</table>
			</form>
            
		</div>
		
	</div>
	
	<div class="acf-box">
		<div class="title">
			<h3>Update Information</h3>
		</div>
		<div class="inner">
			<table class="form-table">
                <tbody>
                	<tr>
                    	<th>
                    		<label>Current Version</label>
                    	</th>
						<td>
							<?php echo $current_version; ?>
						</td>
					</tr>
					<tr>
                    	<th>
                    		<label>Latest Version</label>
                    	</th>
						<td>
							<?php echo $remote_version; ?>
						</td>
					</tr>
					<tr>
                    	<th>
                    		<label>Update Available</label>
                    	</th>
						<td>
							<?php if( $update_available ): ?>
								
								<?php if( $active ): ?>
									Yes &nbsp;&nbsp; <a class="acf-button blue" href="<?php echo admin_url('plugins.php?s=Advanced+Custom+Fields+Pro'); ?>">Update Plugin</a>
								<?php else: ?>
									Yes &nbsp;&nbsp; <a class="acf-button" disabled="disabled" href="#">Please enter your license key above to unlock updates</a>
								<?php endif; ?>
								
							<?php else: ?>
								No &nbsp;&nbsp; <a class="acf-button" href="<?php echo add_query_arg('force-check', 1); ?>">Check Again</a>
							<?php endif; ?>
						</td>
					</tr>
					<?php if( $update_available ): ?>
					<tr>
                    	<th>
                    		<label>Changelog</label>
                    	</th>
						<td>
							<?php echo $changelog; ?>
						</td>
					</tr>
					<?php endif; ?>
				</tbody>
			</table>
			</form>
            
		</div>
		
		
	</div>
	
</div>
<style type="text/css">
	#acf-field-acf_pro_licence {
		width: 75%;
	}
</style>