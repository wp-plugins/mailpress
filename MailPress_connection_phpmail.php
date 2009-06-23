<?php
if (class_exists('MailPress'))
{
/*
Plugin Name: MailPress_connection_phpmail 
Plugin URI: http://www.mailpress.org
Description: This is just an addon for MailPress to replace default SMTP connection by native php mail connection.
Author: Andre Renaut
Version: 4.0
Author URI: http://www.mailpress.org
*/

class MailPress_connection_phpmail
{
	function __construct()
	{
// for connection type & settings
		add_filter('MailPress_Swift_Connection_type', 		array('MailPress_connection_phpmail', 'Swift_Connection_type'), 8, 1);
		add_filter('MailPress_Swift_Connection_settings', 	array('MailPress_connection_phpmail', 'settings_div'), 8, 1);

// for connection 
		add_filter('MailPress_Swift_Connection_PHP_MAIL', 	array('MailPress_connection_phpmail', 'connect'), 8, 1);

// for wp admin
		if (is_admin())
		{
		// for link on plugin page
			add_filter('plugin_action_links', 				array('MailPress_connection_phpmail', 'plugin_action_links'), 10, 2 );
		// for settings
			add_action('MailPress_settings_update', 			array('MailPress_connection_phpmail', 'settings_update'));
		}

	}

////  Connection type & settings  ////

	public static function Swift_Connection_type($x)
	{
		return 'PHP_MAIL';
	}

////  Connection  ////

	public static function connect($x)
	{
		$phpmail_settings = get_option('MailPress_connection_phpmail');

		$conn = Swift_MailTransport::newInstance($phpmail_settings['addparm']);

		return $conn;
	}

////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////

// for link on plugin page
	public static function plugin_action_links($links, $file)
	{
		if ($file != plugin_basename(__FILE__)) return $links;

		$settings_link = '<a href="' . MailPress_settings . '#fragment-1">' . __('Settings') . '</a>';
		array_unshift ($links, $settings_link);
		return $links;
	}

// for settings
	public static function settings_update()
	{
		include (MP_TMP . 'mp-admin/includes/settings/phpmail.php');
	}

	public static function settings_div($x)
	{
		return ABSPATH . MP_PATH . 'mp-admin/includes/settings/phpmail.form.php';
	}
}

$MailPress_connection_phpmail = new MailPress_connection_phpmail();
}
?>