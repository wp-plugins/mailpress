<?php 
MailPress::require_class('Admin_screen');

class MP_AdminPage extends MP_Admin_screen
{
	const screen 	= MailPress_page_subscriptions;
	const capability 	= 'MailPress_manage_subscriptions';

////  Body  ////

	public static function body()
	{
		include (MP_TMP . 'mp-admin/includes/subscriptions.php');
	}
}
?>