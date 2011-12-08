<?php
if (class_exists('MailPress') && !class_exists('MailPress_connection_sendmail') )
{
/*
Plugin Name: MailPress_connection_sendmail 
Plugin URI: http://www.mailpress.org/wiki/index.php?title=Add_ons:Sendmail
Description: This is just an add-on for MailPress to use Sendmail connection.
Author: Andre Renaut
Version: 5.2
Author URI: http://www.mailpress.org
*/

class MailPress_connection_sendmail
{
	const option_name = 'MailPress_connection_sendmail';

	function __construct()
	{
		new MP_Connection_sendmail();

// for wp admin
		if (is_admin())
		{
		// for link on plugin page
			add_filter('plugin_action_links', 			array(__CLASS__, 'plugin_action_links'), 10, 2 );
		// for settings
			add_filter('MailPress_scripts', 			array(__CLASS__, 'scripts'), 8, 2);
		}
	}

////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////

// for link on plugin page
	public static function plugin_action_links($links, $file)
	{
		return MailPress::plugin_links($links, $file, plugin_basename(__FILE__), 'connection_sendmail');
	}

// for settings
	public static function scripts($scripts, $screen) 
	{
		if ($screen != MailPress_page_settings) return $scripts;

		wp_register_script( 'mp-sendmail', 	'/' . MP_PATH . 'mp-admin/js/settings_sendmail.js', array(), false, 1);
		$scripts[] = 'mp-sendmail';

		return $scripts;
	}
}
new MailPress_connection_sendmail();
}