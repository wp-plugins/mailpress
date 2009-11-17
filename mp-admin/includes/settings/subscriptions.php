<?php
$mp_general['tab']	= $mp_tab =  3;
$subscriptions		= $_POST['subscriptions'];
if (!isset($subscriptions['default_newsletters'])) $subscriptions['default_newsletters'] = array();
$old_subscriptions	= get_option('MailPress_subscriptions');
	
if (!isset($_POST['mailinglist']['on']))  // so we don't delete settings if addon deactivated !
{
	$subscriptions['display_mailinglists'] = $old_subscriptions['display_mailinglists'];
}
	
$old_default_newsletters = (isset($old_subscriptions ['default_newsletters'])) ? $old_subscriptions ['default_newsletters'] : MP_Newsletter::get_defaults();

$diff_default_newsletters = array();
foreach($subscriptions ['default_newsletters'] as $k => $v) if (!isset($old_default_newsletters[$k])) $diff_default_newsletters[$k] = true;
foreach($old_default_newsletters as $k => $v) if (!isset($subscriptions ['default_newsletters'][$k])) $diff_default_newsletters[$k] = true;
foreach ($diff_default_newsletters as $k => $v) MP_Newsletter::reverse_subscriptions($k);
	
global $mp_subscriptions;
$mp_subscriptions = $subscriptions;
MP_Newsletter::register_newsletters();
	
if (!add_option ('MailPress_subscriptions', $subscriptions )) update_option ('MailPress_subscriptions', $subscriptions );
if (!add_option ('MailPress_general', $mp_general )) update_option ('MailPress_general', $mp_general);
MP_AdminPage::message(__('"Subscriptions" settings saved', MP_TXTDOM));
?>