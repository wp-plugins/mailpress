<?php
$mp_general['tab'] = $mp_tab =  1;
$smtp_config	= $_POST['smtp_config'];

if ('custom' == $smtp_config['port']) 	$smtp_config ['port'] = $smtp_config['customport'];
unset($smtp_config['customport']);

$smtp_config['username'] = stripslashes($smtp_config['username']);

switch (true)
{
	case ( empty($smtp_config['server'] ) ) :
		$serverclass = true;
		MP_AdminPage::message(__('field should not be empty', 'MailPress'), false);
	break;
	case ( empty($smtp_config['username'] ) ) :
		$usernameclass = true;
		MP_AdminPage::message(__('field should not be empty', 'MailPress'), false);
	break;
	case ( ('@PopB4Smtp' == $smtp_config['smtp-auth']) && (empty($smtp_config['pophost'])) ) : 
		$pophostclass = true;
		MP_AdminPage::message(__('field should not be empty', 'MailPress'), false);
	break;
	default :
		if (!add_option ('MailPress_smtp_config', $smtp_config )) update_option ('MailPress_smtp_config', $smtp_config);
		if (!add_option ('MailPress_general', $mp_general )) update_option ('MailPress_general', $mp_general);
		MP_AdminPage::message(__('SMTP settings saved, Test it !!', 'MailPress'));
	break;
}
?>