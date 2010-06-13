<?php
if ( (class_exists('MailPress')) && (is_admin()) )
{
/*
Plugin Name: MailPress_import
Plugin URI: http://www.mailpress.org
Description: This is just an addon for MailPress to provide an import API for files.
Author: Andre Renaut
Version: 4.0.2
Author URI: http://www.mailpress.org
*/

class MailPress_import
{
	function __construct()
	{
// for wp admin
		if (is_admin())
		{
		// for admin plugin pages
			define ('MailPress_page_import', 'mailpress_import');
		// for admin plugin urls
			$file = 'admin.php';
			define ('MailPress_import', $file . '?page=' . MailPress_page_import);
		// for link on plugin page
			add_filter('plugin_action_links', 		array(__CLASS__, 'plugin_action_links'), 10, 2 );
		// for role & capabilities
			add_filter('MailPress_capabilities', 	array(__CLASS__, 'capabilities'), 1, 1);
		// for settings
			add_action('MailPress_settings_logs', 	array(__CLASS__, 'settings_logs'), 8, 1);

		// for load admin page
			add_action('MailPress_load_admin_page', 	array(__CLASS__, 'load_admin_page'), 10, 1);
		}
	}

////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////

// for link on plugin page
	public static function plugin_action_links($links, $file)
	{
		return MailPress::plugin_links($links, $file, plugin_basename(__FILE__), '4');
	}

// for role & capabilities
	public static function capabilities($capabilities) 
	{
		$capabilities['MailPress_import'] = array(	'name'  => __('Import', MP_TXTDOM), 
								'group' => 'admin', 
								'menu'  => 95, 
								'parent'		=> false, 
								'page_title'	=> __('MailPress Import', MP_TXTDOM), 
								'menu_title'   	=> __('Import', MP_TXTDOM), 
								'page'  		=> MailPress_page_import, 
								'func'  		=> array('MP_AdminPage', 'body')
							);
		return $capabilities;
	}

// for settings
	public static function settings_logs($logs)
	{
		MP_AdminPage::logs_sub_form('import', $logs, __('Import', MP_TXTDOM), __('Import log', MP_TXTDOM), __('(for <b>ALL</b> imports through MailPress)', MP_TXTDOM), __('Number of Import log files : ', MP_TXTDOM));
	}

// for load admin page
	public static function load_admin_page($page)
	{
		if ($page != MailPress_page_import) return;
		include (MP_TMP . 'mp-admin/import.php');
	}
}
$MailPress_import = new MailPress_import();
}
?>