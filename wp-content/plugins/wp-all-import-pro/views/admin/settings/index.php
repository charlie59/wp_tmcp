<form class="settings" method="post" action="" enctype="multipart/form-data">

	<div class="wpallimport-header">
		<div class="wpallimport-logo"></div>
		<div class="wpallimport-title">
			<p><?php _e('WP All Import', 'wp_all_import_plugin'); ?></p>
			<h3><?php _e('Settings', 'wp_all_import_plugin'); ?></h3>			
		</div>	
	</div>

	<h2></h2>
	
	<div class="wpallimport-setting-wrapper">
		<?php if ($this->errors->get_error_codes()): ?>
			<?php $this->error() ?>
		<?php endif ?>
		
		<h3><?php _e('Import/Export Templates', 'wp_all_import_plugin') ?></h3>
		<?php $templates = new PMXI_Template_List(); $templates->getBy()->convertRecords() ?>
		<?php wp_nonce_field('delete-templates', '_wpnonce_delete-templates') ?>				
		<?php if ($templates->total()): ?>
			<table>
				<?php foreach ($templates as $t): ?>
					<tr>
						<td>
							<label class="selectit" for="template-<?php echo $t->id ?>"><input id="template-<?php echo $t->id ?>" type="checkbox" name="templates[]" value="<?php echo $t->id ?>" /> <?php echo $t->name ?></label>
						</td>				
					</tr>
				<?php endforeach ?>
			</table>
			<p class="submit-buttons">				
				<input type="submit" class="button-primary" name="delete_templates" value="<?php _e('Delete Selected', 'wp_all_import_plugin') ?>" />
				<input type="submit" class="button-primary" name="export_templates" value="<?php _e('Export Selected', 'wp_all_import_plugin') ?>" />
			</p>	
		<?php else: ?>
			<em><?php _e('There are no templates saved', 'wp_all_import_plugin') ?></em>
		<?php endif ?>
		<p>
			<input type="hidden" name="is_templates_submitted" value="1" />
			<input type="file" name="template_file"/>
			<input type="submit" class="button-primary" name="import_templates" value="<?php _e('Import Templates', 'wp_all_import_plugin') ?>" />
		</p>
	</div>

</form>

<form name="settings" method="post" action="" class="settings">

	<h3><?php _e('Cron Imports', 'wp_all_import_plugin') ?></h3>
	
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><label><?php _e('Secret Key', 'wp_all_import_plugin'); ?></label></th>
				<td>
					<input type="text" class="regular-text" name="cron_job_key" value="<?php echo esc_attr($post['cron_job_key']); ?>"/>
					<p class="description"><?php _e('Changing this will require you to re-create your existing cron jobs.', 'wp_all_import_plugin'); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label><?php _e('Cron Processing Time Limit', 'wp_all_import_plugin'); ?></label></th>
				<td>
					<input type="text" class="regular-text" name="cron_processing_time_limit" value="<?php echo esc_attr($post['cron_processing_time_limit']); ?>"/>
					<p class="description"><?php _e('Leave blank to use your server\'s limit on script run times.', 'wp_all_import_plugin'); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label><?php _e('Cron Sleep', 'wp_all_import_plugin'); ?></label></th>
				<td>
					<input type="text" class="regular-text" name="cron_sleep" value="<?php echo esc_attr($post['cron_sleep']); ?>"/>
					<p class="description"><?php _e('Sleep the specified number of seconds between each post created, updated, or deleted with cron. Leave blank to not sleep. Only necessary on servers  that are slowed down by the cron job because they have very minimal processing power and resources.', 'wp_all_import_plugin'); ?></p>
				</td>
			</tr>
		</tbody>
	</table>	

	<div class="clear"></div>

	<h3><?php _e('Files', 'wp_all_import_plugin') ?></h3>
	
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><label><?php _e('Secure Mode', 'wp_all_import_plugin'); ?></label></th>
				<td>
					<fieldset style="padding:0;">
						<legend class="screen-reader-text"><span><?php _e('Secure Mode', 'wp_all_import_plugin'); ?></span></legend>
						<input type="hidden" name="secure" value="0"/>
						<label for="secure"><input type="checkbox" value="1" id="secure" name="secure" <?php echo (($post['secure']) ? 'checked="checked"' : ''); ?>><?php _e('Randomize folder names', 'wp_all_import_plugin'); ?></label>																				
					</fieldset>														
					<p class="description">
						<?php
							$wp_uploads = wp_upload_dir();
						?>
						<?php printf(__('Imported files, chunks, logs and temporary files will be placed in a folder with a randomized name inside of %s.', 'wp_all_import_plugin'), $wp_uploads['basedir'] . DIRECTORY_SEPARATOR . WP_ALL_IMPORT_UPLOADS_BASE_DIRECTORY ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label><?php _e('Log Storage', 'wp_all_import_plugin'); ?></label></th>
				<td>
					<input type="text" class="regular-text" name="log_storage" value="<?php echo esc_attr($post['log_storage']); ?>"/>
					<p class="description"><?php _e('Number of logs to store for each import. Enter 0 to never store logs.', 'wp_all_import_plugin'); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label><?php _e('Clean Up Temp Files', 'wp_all_import_plugin'); ?></label></th>
				<td>
					<a class="button-primary wpallimport-clean-up-tmp-files" href="<?php echo add_query_arg(array('action' => 'cleanup'), $this->baseUrl); ?>"><?php _e('Clean Up', 'wp_all_import_plugin'); ?></a>
					<p class="description"><?php _e('Attempt to remove temp files left over by imports that were improperly terminated.', 'wp_all_import_plugin'); ?></p>
				</td>
			</tr>
		</tbody>
	</table>	

	<div class="clear"></div>

	<h3><?php _e('Advanced Settings', 'wp_all_import_plugin') ?></h3>
	
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><label><?php _e('Chunk Size', 'wp_all_import_plugin'); ?></label></th>
				<td>
					<input type="text" class="regular-text" name="large_feed_limit" value="<?php echo esc_attr($post['large_feed_limit']); ?>"/>
					<p class="description"><?php _e('Split file into chunks containing the specified number of records.', 'wp_all_import_plugin'); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label><?php _e('WP_IMPORTING', 'wp_all_import_plugin'); ?></label></th>
				<td>
					<fieldset style="padding:0;">						
						<input type="hidden" name="pingbacks" value="0"/>
						<label for="pingbacks"><input type="checkbox" value="1" id="pingbacks" name="pingbacks" <?php echo (($post['pingbacks']) ? 'checked="checked"' : ''); ?>><?php _e('Enable WP_IMPORTING', 'wp_all_import_plugin'); ?></label>																				
					</fieldset>														
					<p class="description"><?php _e('Setting this constant avoids triggering pingback.', 'wp_all_import_plugin'); ?></p>
				</td>
			</tr>	
			<tr>
				<th scope="row"><label><?php _e('Add Port To URL', 'wp_all_import_plugin'); ?></label></th>
				<td>
					<input type="text" class="regular-text" name="port" value="<?php echo esc_attr($post['port']); ?>"/>
					<p class="description"><?php _e('Specify the port number to add if you\'re having problems continuing to Step 2 and are running things on a custom port. Default is blank.', 'wp_all_import_plugin'); ?></p>
				</td>
			</tr>		
			<?php do_action('pmxi_settings_advanced', $post); ?>
		</tbody>
	</table>	

	<h3><?php _e('Licenses', 'wp_all_import_plugin') ?></h3>
	
	<table class="form-table">
		<tbody>

			<?php foreach ($addons as $class => $addon) : if ( ! $addon['active'] ) continue; ?>		
				<tr>
					<th scope="row"><label><?php _e('License Key', 'wp_all_import_plugin'); ?></label></th>
					<td>
						<input type="text" class="regular-text" name="licenses[<?php echo $class; ?>]" value="<?php if (!empty($post['licenses'][$class])) esc_attr_e( $post['licenses'][$class] ); ?>"/>
						<?php if( ! empty($post['licenses'][$class]) ) { ?>

							<?php if( ! empty($post['statuses'][$class]) && $post['statuses'][$class] == 'valid' ) { ?>
								<p style="color:green; display: inline-block;"><?php _e('Active', 'wp_all_import_plugin'); ?></p>							
							<?php } else { ?>													
								<input type="submit" class="button-secondary" name="pmxi_license_activate[<?php echo $class; ?>]" value="<?php _e('Activate License', 'wp_all_import_plugin'); ?>"/>
								<span style="line-height: 28px;"><?php echo $post['statuses'][$class]; ?></span>
							<?php } ?>
						
						<?php } ?>
						<p class="description"><?php _e('A license key is required to access plugin updates. You can use your license key on an unlimited number of websites. Do not distribute your license key to 3rd parties. You can get your license key in the <a target="_blank" href="http://www.wpallimport.com/portal">customer portal</a>.', 'wp_all_import_plugin'); ?></p>
					</td>
				</tr>				
			<?php endforeach; ?>					
		</tbody>
	</table>			

	<div class="clear"></div>

	<p class="submit-buttons">
		<?php wp_nonce_field('edit-settings', '_wpnonce_edit-settings') ?>
		<input type="hidden" name="is_settings_submitted" value="1" />
		<input type="submit" class="button-primary" value="Save Settings" />
	</p>

	<a href="http://soflyy.com/" target="_blank" class="wpallimport-created-by"><?php _e('Created by', 'wp_all_import_plugin'); ?> <span></span></a>

</form>