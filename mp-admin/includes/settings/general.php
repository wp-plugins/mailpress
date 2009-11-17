<?php
$mp_general		= get_option('MailPress_general');	
$mp_general['tab']= $mp_tab = '0';

$mp_general		= $_POST['general'];

switch (true)
{
	case ( !MP_AdminPage::is_email($mp_general['fromemail']) ) :
		$fromemailclass = true;
		MP_AdminPage::message(__('field should be an email', MP_TXTDOM), false);
	break;
	case ( empty($mp_general['fromname']) ) :
		$fromnameclass = true;
		MP_AdminPage::message(__('field should be a name', MP_TXTDOM), false);
	break;
	case (('ajax' != $mp_general['subscription_mngt']) && ( !is_numeric($mp_general['id']))) :
		$idclass = true;
		MP_AdminPage::message(__('field should be numeric', MP_TXTDOM), false);
	break;
	default :
		if (isset($_POST['sync_wordpress_user_on']))	// so we don't delete settings if addon deactivated !
		{
			$sync_wordpress_user = $_POST['sync_wordpress_user'];
			if (!add_option ('MailPress_sync_wordpress_user', $sync_wordpress_user )) update_option ('MailPress_sync_wordpress_user', $sync_wordpress_user);
		}

		if (isset($_POST['default_mailinglist_on']))	// so we don't delete settings if addon deactivated !
		{
			$default_mailinglist 	= $_POST['default_mailinglist'];
			if (!add_option ('MailPress_default_mailinglist', $default_mailinglist )) update_option ('MailPress_default_mailinglist', $default_mailinglist);
		}

		if ('ajax' == $mp_general['subscription_mngt']) $mp_general['id'] = '';
		if (!add_option ('MailPress_general', $mp_general, 'MailPress - general settings' )) update_option ('MailPress_general', $mp_general);
		MP_AdminPage::message(__('General settings saved', MP_TXTDOM));
	break;
}
?>