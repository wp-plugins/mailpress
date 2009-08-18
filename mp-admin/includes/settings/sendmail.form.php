<?php
$connection_sendmail = get_option('MailPress_connection_sendmail');
?>
<div id='fragment-MailPress_connection_sendmail' class='clear'>
	<form name='connection_sendmail.form' action='' method='post'  class='mp_settings'>
		<input type='hidden' name='formname' value='connection_sendmail.form' />
		<table class='form-table'>
			<tr valign='top'>
				<th scope='row'><?php _e('Connect','MailPress'); ?></th>
				<td class='field'>
					<input name='connection_sendmail[cmd]' type='radio'<?php checked($connection_sendmail['cmd'],'std'); ?>  value='std' class='connection_sendmail' />
					<?php _e("using '/usr/sbin/sendmail -bs'",'MailPress'); ?>
					<br />
					<input name='connection_sendmail[cmd]' id='sendmail-custom' type='radio'<?php checked($connection_sendmail['cmd'],'custom'); ?>  value='custom' class='connection_sendmail' />
					<?php _e('using a custom command','MailPress'); ?>
					&nbsp;&nbsp;
					<span id='sendmail-custom-cmd' <?php if ('custom' != $connection_sendmail['cmd']) echo " style='display:none;'"; ?>>
						<input type='text' size='40' name='connection_sendmail[custom]' value="<?php echo $connection_sendmail['custom']; ?>" />					
					</span>
				</td>
			</tr>
		</table>
<?php MP_AdminPage::save_button(); ?>
	</form>
</div>