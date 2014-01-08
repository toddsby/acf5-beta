<div class="wrap">
	
	<h2 class="nav-tab-wrapper">
		<a class="nav-tab nav-tab-active" href="http://acf/wp-admin/admin.php?page=shopp-setup-core">Import / Export</a>
		<a class="nav-tab" href="http://acf/wp-admin/admin.php?page=shopp-setup-core">Add-ons</a>
		<a class="nav-tab" href="http://acf/wp-admin/admin.php?page=shopp-setup-core">Updates</a>
	</h2>
	
	<p><br /></p>
	
	<div class="acf-box">
		<div class="title">
			<h3>Export Field Groups</h3>
		</div>
		<div class="inner">
			<p>Select the field groups you would like to export. When you click the download button below, ACF will create a JSON file for you to save to your computer. Once you've saved the download file, you can use the Import tool to import the field groups into another website. You can also include this JSON file in a theme folder called 'acf-json' for auto import!</p>
			
			<form method="post" action="">
			<div class="acf-hidden">
				<input type="hidden" name="_acfnonce" value="<?php echo wp_create_nonce( 'export' ); ?>" />
			</div>
			<table class="form-table">
                <tbody>
                	<tr>
                    	<th>
                    		<label>Select Forms</label>
                    	</th>
						<td>
							<?php 
							
							// vars
							$choices = array();
							$field_groups = acf_get_field_groups();
							
							
							// populate choices
							if( !empty($field_groups) )
							{
								foreach( $field_groups as $field_group )
								{
									$choices[ $field_group['key'] ] = $field_group['title'];
								}
							}
							
							
							// render field
							acf_render_field(array(
								'type'		=> 'checkbox',
								'name'		=> 'acf_export_keys',
								'prefix'	=> false,
								'value'		=> false,
								'choices'	=> $choices,
							));
							
							?>
						</td>
					</tr>
					<tr>
						<th></th>
						<td>
							<input type="submit" class="acf-button blue" value="Download Export file" />
						</td>
					</tr>
				</tbody>
			</table>
			</form>
            
		</div>
		
		
	</div>
	
	<p><br /></p>
	
	<div class="acf-box">
		<div class="title">
			<h3>Import Field Groups</h3>
		</div>
		<div class="inner">
			<p>Select the Advanced Custom Fields JSON file you would like to import. When you click the import button below, ACF will import the field groups. </p>
			
			<form method="post" action="" enctype="multipart/form-data">
			<div class="acf-hidden">
				<input type="hidden" name="_acfnonce" value="<?php echo wp_create_nonce( 'import' ); ?>" />
			</div>
			<table class="form-table">
                <tbody>
                	<tr>
                    	<th>
                    		<label>Select File</label>
                    	</th>
						<td>
							<input type="file" name="acf_import_file">
						</td>
					</tr>
					<tr>
						<th></th>
						<td>
							<input type="submit" class="acf-button blue" value="Import" />
						</td>
					</tr>
				</tbody>
			</table>
			</form>
			
		</div>
		
		
	</div>
	
</div>