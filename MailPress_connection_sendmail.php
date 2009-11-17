<?php
if (class_exists('MailPress'))
{
/*
Plugin Name: MailPress_connection_sendmail 
Plugin URI: http://www.mailpress.org
Description: This is just an addon for MailPress to replace default SMTP connection by SendMail connection.
Author: Andre Renaut
Version: 4.0.1
Author URI: http://www.mailpress.org
*/

class MailPress_connection_sendmail
{
	function __construct()
	{
// for connection type & settings
		add_filter('MailPress_Swift_Connection_type', 		array('MailPress_connection_sendmail', 'Swift_Connection_type'), 8, 1);

// for connection 
		add_filter('MailPress_Swift_Connection_SENDMAIL', 	array('MailPress_connection_sendmail', 'connect'), 8, 2);

// for wp admin
		if (is_admin())
		{
		// for link on plugin page
			add_filter('plugin_action_links', 			array('MailPress_connection_sendmail', 'plugin_action_links'), 10, 2 );
		// for settings
			add_filter('MailPress_scripts', 			array('MailPress_connection_sendmail', 'scripts'), 8, 2);
			add_action('MailPress_settings_update', 		array('MailPress_connection_sendmail', 'settings_update'));
			add_filter('MailPress_Swift_Connection_settings', 	array('MailPress_connection_sendmail', 'settings_div'), 8, 1);
		}
	}

////  Connection type & settings  ////

	public static function Swift_Connection_type($x)
	{
		return 'SENDMAIL';
	}

////  Connection  ////

	public static function connect($x, $y)
	{
		$sendmail_settings = get_option('MailPress_connection_sendmail');

		switch ($sendmail_settings['cmd'])
		{
			case 'custom' :
				$conn = Swift_SendmailTransport::newInstance($sendmail_settings['custom']);
			break;
			default :
				$conn = Swift_SendmailTransport::newInstance();
			break;
		}
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
	public static function scripts($scripts, $screen) 
	{
		if ($screen != MailPress_page_settings) return $scripts;

		wp_register_script( 'mp-sendmail', 	'/' . MP_PATH . 'mp-admin/js/sendmail.js', array(), false, 1);
		$scripts[] = 'mp-sendmail';

		return $scripts;
	}

	public static function settings_update()
	{
		include (MP_TMP . 'mp-admin/includes/settings/sendmail.php');
	}

	public static function settings_div($x)
	{
		return ABSPATH . MP_PATH . 'mp-admin/includes/settings/sendmail.form.php';
	}
}

$MailPress_connection_sendmail = new MailPress_connection_sendmail();
}
?>