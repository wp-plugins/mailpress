<?php
$logs	 	= get_option('MailPress_logs');
?>
<div>
	<form name='logs.form' action='' method='post'  class='mp_settings'>
		<input type='hidden' name='formname' value='logs.form' />
		<table class='form-table'>

<!-- MailPress -->

<?php MP_AdminPage::logs_sub_form('general', $logs, __('Mails', 'MailPress') , __('Mailing log', 'MailPress') , __('(for <b>ALL</b> mails send through MailPress)', 'MailPress'), __('Number of Log files : ', 'MailPress')); ?>

<?php do_action('MailPress_settings_logs', $logs); ?>

		</table>
<?php MP_AdminPage::save_button(); ?>
	</form>
</div>
