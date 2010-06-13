<?php
if (!isset($_POST['formname']) || ('logs.form' != $_POST['formname'])) $logs = get_option('MailPress_logs');
?>
<div>
	<form name='logs.form' action='' method='post'  class='mp_settings'>
		<input type='hidden' name='formname' value='logs.form' />
		<table class='form-table'>

<!-- MailPress -->

<?php MP_AdminPage::logs_sub_form('general', $logs, __('Mails', MP_TXTDOM) , __('Mailing log', MP_TXTDOM) , __('(for <b>ALL</b> mails send through MailPress)', MP_TXTDOM), __('Number of Log files : ', MP_TXTDOM)); ?>

<?php do_action('MailPress_settings_logs', $logs); ?>

		</table>
<?php MP_AdminPage::save_button(); ?>
	</form>
</div>
