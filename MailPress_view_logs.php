<?php
if ( (class_exists('MailPress')) && (is_admin()) )
{
/*
Plugin Name: MailPress_view_logs
Plugin URI: http://www.mailpress.org
Description: This is just an add-on for MailPress to view logs
Author: Andre Renaut
Version: 4.0
Author URI: http://www.mailpress.org
*/

class MailPress_view_logs
{
	function __construct()
	{
	// for admin plugin pages
		define ('MailPress_page_view_logs', 	'mailpress_viewlogs');
		define ('MailPress_page_view_log', 		MailPress_page_view_logs . '&file=view_log');
	// for admin plugin urls
		$file = 'admin.php';
		define ('MailPress_view_logs', 	$file . '?page=' . MailPress_page_view_logs);
		define ('MailPress_view_log', 	$file . '?page=' . MailPress_page_view_log);
	// for role & capabilities
		add_filter('MailPress_capabilities',  		array('MailPress_view_logs', 'capabilities'), 1, 1);

	// for load admin page
		add_action('MailPress_load_admin_page', 		array('MailPress_view_logs', 'load_admin_page'), 10, 1);
	}

////  Admin  ////

// for role & capabilities
	public static function capabilities($capabilities)
	{
		$capabilities['MailPress_view_logs'] = array(	'name'	=> __('Logs', 'MailPress'),
										'group'	=> 'admin',
										'menu'	=> 99,

										'parent'	=> false,
										'page_title'=> __('MailPress View logs', 'MailPress'),
										'menu_title'=> __('Logs', 'MailPress'),
										'page'	=> MailPress_page_view_logs,
										'func'	=> array('MP_AdminPage', 'body')
									);
		return $capabilities;
	}

// for load admin page
	public static function load_admin_page($page)
	{
		$hub = array (	MailPress_page_view_logs => 'view_logs',
					MailPress_page_view_log  => 'view_log'
				);

		if (isset($hub[$page])) require_once(MP_TMP . 'mp-admin/' . $hub[$page] . '.php');
	}
}

$MailPress_view_logs = new MailPress_view_logs();
}
?>