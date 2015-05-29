<?php

$xevery = array (	10 	=> sprintf(__('%1$s days', MP_TXTDOM), '10'), 
			15 	=> sprintf(__('%1$s days', MP_TXTDOM), '15'),  
			30 	=> sprintf(__('%1$s days', MP_TXTDOM), '30'),  
			60 	=> sprintf(__('%1$s days', MP_TXTDOM), '60'), 
			90 	=> sprintf(__('%1$s days', MP_TXTDOM), '90'), 
			120 	=> sprintf(__('%1$s days', MP_TXTDOM), '120'), 
			360 	=> sprintf(__('%1$s days', MP_TXTDOM), '360'),  ); 

if (!isset($batch_delete_old_mails)) $batch_delete_old_mails = get_option(MailPress_delete_old_mails::option_name);
?>
<tr>
	<th class='thtitle'><?php _e('Deleting Old Mails', MP_TXTDOM); ?></th>
	<td></td>
</tr>
<tr>
	<th><?php _e('Keep sent mails since', MP_TXTDOM); ?></th>
	<td class='field'>
		<select name='batch_delete_old_mails[days]'>
<?php MP_AdminPage::select_option($xevery, $batch_delete_old_mails['days']);?>
		</select>
	</td>
</tr>
<tr>
	<th><?php _e('Submit batch with', MP_TXTDOM); ?></th>
	<td>
		<table class='general'>
			<tr>
				<td class='pr10'>
					<label for='batch_delete_old_mails_wp_cron'>
						<input value='wpcron' name='batch_delete_old_mails[batch_mode]' id='batch_delete_old_mails_wp_cron' class='submit_batch_delete_old_mails tog' type='radio' <?php checked('wpcron', $batch_delete_old_mails['batch_mode']); ?> />
						&#160;&#160;
						<?php _e('WP_Cron', MP_TXTDOM); ?>
					</label>
				</td>
				<td class='delete_old_mails_wpcron pr10 toggl4<?php if ('wpcron' != $batch_delete_old_mails['batch_mode']) echo ' hide'; ?>' style='padding-left:10px;vertical-align:bottom;'>
					<?php _e('Every', MP_TXTDOM); ?>
					&#160;&#160;
					<select name='batch_delete_old_mails[every]' id='every' >
<?php MP_AdminPage::select_option($xevery, $batch_delete_old_mails['every']);?>
					</select>
				</td>
			</tr>
			<tr>
				<td class='pr10'>
					<label for='batch_delete_old_mails_other'>
						<input value='other' name='batch_delete_old_mails[batch_mode]' id='batch_delete_old_mails_other' class='submit_batch_delete_old_mails tog' type='radio' <?php checked('other', $batch_delete_old_mails['batch_mode']); ?> />
						&#160;&#160;
						<?php _e('Other', MP_TXTDOM); ?>
					</label>
				</td>
				<td class='delete_old_mails_other pr10 toggl4<?php if ('other' != $batch_delete_old_mails['batch_mode']) echo ' hide'; ?>' >
					<?php printf(__('see sample in "%1$s"', MP_TXTDOM), '<code>' . MP_CONTENT_DIR . 'xtras/mp_batch_delete_old_mails' . '</code>'); ?>
				</td>
			</tr>
		</table>
	</td>
</tr>
<tr class='mp_sep' style='line-height:2px;padding:0;'><td style='line-height:2px;padding:0;'></td><td></td></tr>