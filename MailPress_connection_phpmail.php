<?php
if (class_exists('MailPress'))
{
/*
Plugin Name: MailPress_connection_phpmail 
Plugin URI: http://www.mailpress.org
Description: This is just an addon for MailPress to replace default SMTP connection by native php mail connection.
Author: Andre Renaut
Version: 4.0.2
Author URI: http://www.mailpress.org
*/

class MailPress_connection_phpmail
{
	function __construct()
	{
// for connection type & settings
		add_filter('MailPress_Swift_Connection_type', 		array(__CLASS__, 'Swift_Connection_type'), 8, 1);
		add_filter('MailPress_Swift_Connection_settings', 	array(__CLASS__, 'settings_div'), 8, 1);

// for connection 
		add_filter('MailPress_Swift_Connection_PHP_MAIL', 	array(__CLASS__, 'connect'), 8, 1);

// for wp admin
		if (is_admin())
		{
		// for link on plugin page
			add_filter('plugin_action_links', 			array(__CLASS__, 'plugin_action_links'), 10, 2 );
		// for settings
			add_action('MailPress_settings_update', 		array(__CLASS__, 'settings_update'));
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
		return MailPress::plugin_links($links, $file, plugin_basename(__FILE__), '1');
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