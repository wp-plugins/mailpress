<?php 
require_once(MP_TMP . 'mp-admin/class/MP_Admin_abstract.class.php');

class MP_AdminPage extends MP_Admin_abstract
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