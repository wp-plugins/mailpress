<?php
$mp_general		= get_option('MailPress_general');	
$mp_general['tab']= $mp_tab = '0';

$mp_general		= $_POST['general'];

switch ($mp_general['subscription_mngt'])
{
	case 'ajax' :
			$mp_general['id'] = '';
		break;
		default :
			$mp_general['id'] = $_POST[$mp_general['subscription_mngt']];
			if ('cat' == $mp_general['subscription_mngt']) $cvalue = $mp_general['id']; else $pvalue = $mp_general['id'];
		break;
}

switch (true)
{
	case ( !self::is_email($mp_general['fromemail']) ) :
		$fromemailclass = true;
		self::message(__('field should be an email', 'MailPress'), false);
	break;
	case ( empty($mp_general['fromname']) ) :
		$fromnameclass = true;
		self::message(__('field should be a name', 'MailPress'), false);
	break;
	case ( !isset($mp_general['subscription_mngt']) ) :
		$subscription_mngtclass = 'true';
	break;
	case (('ajax' != $mp_general['subscription_mngt']) && ( !is_numeric($mp_general['id']))) :
		('cat' == $mp_general['subscription_mngt']) ? $cclass = true: $pclass = true;
		self::message(__('field should be numeric', 'MailPress'), false);
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
	
		if (!add_option ('MailPress_general', $mp_general, 'MailPress - general settings' )) update_option ('MailPress_general', $mp_general);
		self::message(__('General settings saved','MailPress'));
	break;
}
?>