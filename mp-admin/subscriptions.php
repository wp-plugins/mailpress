<?php 
MailPress::require_class('Admin_page');

class MP_AdminPage extends MP_Admin_page
{
	const screen 	= MailPress_page_subscriptions;
	const capability 	= 'MailPress_manage_subscriptions';
	const help_url	= false;

////  Body  ////

	public static function body()
	{
		include (MP_TMP . 'mp-admin/includes/subscriptions.php');
	}
}
?>