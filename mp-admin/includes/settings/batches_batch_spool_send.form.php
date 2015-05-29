<?php

$xevery = array (	30 	=> sprintf(__('%1$s seconds', MP_TXTDOM), '30'), 
			45 	=> sprintf(__('%1$s seconds', MP_TXTDOM), '45'), 
			60 	=> sprintf(__('%1$s minute' , MP_TXTDOM) , ''), 
			120 	=> sprintf(__('%1$s minutes', MP_TXTDOM), '2'), 
			300 	=> sprintf(__('%1$s minutes', MP_TXTDOM), '5'), 
			900 	=> sprintf(__('%1$s minutes', MP_TXTDOM), '15'), 
			1800 	=> sprintf(__('%1$s minutes', MP_TXTDOM), '30'), 
			3600 	=> sprintf(__('%1$s hour', 	MP_TXTDOM), '') ); 

if (!isset($batch_spool_send)) $batch_spool_send = get_option(MailPress_batch_spool_send::option_name);
?>
<tr>
	<th class='thtitle'><?php _e('Sending Mails from spool', MP_TXTDOM); ?></th>
	<td></td>
</tr>
<tr<?php if (isset($spoolpath)) echo " class='form-invalid'"; ?>>
	<th><?php _e('Spool Path', MP_TXTDOM); ?></th>
	<td class='field'>
		<input type='text' size='100' name='batch_spool_send[path]' value="<?php if (isset($batch_spool_send['path'])) echo $batch_spool_send['path']; ?>" />
		<br /><?php printf(__('If empty, default path is %s but can be deleted anytime by automatic upgrade', MP_TXTDOM), '"<code>' . MP_ABSPATH . 'tmp/spool</code>"'  ); ?>
	</td>
</tr>

<tr>
	<th><?php _e('Time limit in seconds', MP_TXTDOM); ?></th>
	<td class='field'>
		<select name='batch_spool_send[time_limit]'>
<?php MP_AdminPage::select_option($xevery, $batch_spool_send['time_limit']);?>
		</select>
	</td>
</tr>
<tr>
	<th><?php _e('Max mails sent per batch', MP_TXTDOM); ?></th>
	<td class='field'>
		<select name='batch_spool_send[per_pass]'>
<?php MP_AdminPage::select_number(1, 10, $batch_spool_send['per_pass'], 1);?>
<?php MP_AdminPage::select_number(11, 100, $batch_spool_send['per_pass'], 10);?>
<?php MP_AdminPage::select_number(101, 1000, $batch_spool_send['per_pass'], 100);?>
<?php MP_AdminPage::select_number(1001, 10000, $batch_spool_send['per_pass'], 1000);?>
		</select>
	</td>
</tr>
<tr>
	<th><?php _e('Max retries', MP_TXTDOM); ?></th>
	<td class='field'>
		<select name='batch_spool_send[max_retry]'  style='width:4em;'>
<?php MP_AdminPage::select_number(0, 5, $batch_spool_send['max_retry']);?>
		</select>
	</td>
</tr>
<tr>
	<th><?php _e('Submit batch with', MP_TXTDOM); ?></th>
	<td>
		<table class='general'>
			<tr>
				<td class='pr10'>
					<label for='batch_spool_send_wp_cron'>
						<input value='wpcron' name='batch_spool_send[batch_mode]' id='batch_spool_send_wp_cron' class='submit_spool tog' type='radio' <?php checked('wpcron', $batch_spool_send['batch_mode']); ?> />
						&#160;&#160;
						<?php _e('WP_Cron', MP_TXTDOM); ?>
					</label>
				</td>
				<td class='wpcron pr10 toggl2<?php if ('wpcron' != $batch_spool_send['batch_mode']) echo ' hide'; ?>' style='padding-left:10px;vertical-align:bottom;'>
					<?php _e('Every', MP_TXTDOM); ?>
					&#160;&#160;
					<select name='batch_spool_send[every]' id='every' >
<?php MP_AdminPage::select_option($xevery, $batch_spool_send['every']);?>
					</select>
				</td>
			</tr>
			<tr>
				<td class='pr10'>
					<label for='batch_spool_send_other'>
						<input value='other' name='batch_spool_send[batch_mode]' id='batch_spool_send_other' class='submit_spool tog' type='radio' <?php checked('other', $batch_spool_send['batch_mode']); ?> />
						&#160;&#160;
						<?php _e('Other', MP_TXTDOM); ?>
					</label>
				</td>
				<td class='other pr10 toggl2<?php if ('other' != $batch_spool_send['batch_mode']) echo ' hide'; ?>' >
					<?php printf(__('see sample in "%1$s"', MP_TXTDOM), '<code>' . MP_CONTENT_DIR . 'xtras/mp_batch_spool_send' . '</code>'); ?>
				</td>
			</tr>
		</table>
	</td>
</tr>
<tr class='mp_sep' style='line-height:2px;padding:0;'><td style='line-height:2px;padding:0;'></td><td></td></tr>