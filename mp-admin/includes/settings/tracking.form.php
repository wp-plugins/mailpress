<?php
$tracking = get_option('MailPress_tracking');
include(MP_TMP . '/mp-includes/mp_tracking/reports.php');

?>
<div id='fragment-MailPress_tracking'>
	<div>
		<form name='tracking.form' action='' method='post' class='mp_settings'>
			<input type='hidden' name='formname' value='tracking.form' />
			<table class='form-table'>
				<tr valign='top' class="rc_role" >
					<th scope='row'><strong><?php _e('User','MailPress'); ?></strong></th>
					<td class='field'>
<?php
global $mp_general;
$gmapkey = $mp_general['gmapkey'];
foreach ($tracking_reports['user'] as $k => $v)
{
?>
<input type='checkbox' name='tracking[<?php echo $k; ?>]' value='<?php echo $k; ?>' <?php if (isset($tracking[$k])) checked($k,$tracking[$k]); ?> />&nbsp;<?php echo $v['title']; ?><br />
<?php
}
?>
					</td>
				</tr>
				<tr valign='top' class="rc_role" >
					<th scope='row'><strong><?php _e('Mail','MailPress'); ?></strong></th>
					<td class='field'>

<?php
foreach ($tracking_reports['mail'] as $k => $v)
{
?>
<input type='checkbox' name='tracking[<?php echo $k; ?>]' value='<?php echo $k; ?>' <?php if (isset($tracking[$k])) checked($k,$tracking[$k]); ?> />&nbsp;<?php echo $v['title']; ?><br />
<?php
}
?>
					</td>
				</tr>
			</table>
<?php MP_AdminPage::save_button(); ?>
		</form>
	</div>
</div>