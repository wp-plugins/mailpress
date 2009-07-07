<?php
if ( (class_exists('MailPress')) && (is_admin()) )
{
/*
Plugin Name: MailPress_roles_and_capabilities
Plugin URI: http://www.mailpress.org
Description: This is just an addon for MailPress to manage roles & capabilities.
Author: Andre Renaut
Version: 4.0
Author URI: http://www.mailpress.org
*/

class MailPress_roles_and_capabilities
{
	function __construct()
	{
	// for link on plugin page
		add_filter('plugin_action_links', 			array('MailPress_roles_and_capabilities', 'plugin_action_links'), 10, 2 );
	// for role & capabilities
		add_action('MailPress_roles_and_capabilities', 	array('MailPress_roles_and_capabilities', 'roles_and_capabilities'));
	// for settings
		add_filter('MailPress_styles', 			array('MailPress_roles_and_capabilities', 'styles'), 8, 2);
		add_filter('MailPress_scripts', 			array('MailPress_roles_and_capabilities', 'scripts'), 8, 2);
		add_action('MailPress_settings_update', 		array('MailPress_roles_and_capabilities', 'settings_update'));
		add_action('MailPress_settings_tab', 		array('MailPress_roles_and_capabilities', 'settings_tab'), 8, 1);
		add_action('MailPress_settings_div', 		array('MailPress_roles_and_capabilities', 'settings_div'));
	//for ajax
		add_action('mp_action_r_and_c',			array('MailPress_roles_and_capabilities', 'mp_action_r_and_c'));
	}

////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////

// for link on plugin page
	public static function plugin_action_links($links, $file)
	{
		return MailPress::plugin_links($links, $file, plugin_basename(__FILE__), 'MailPress_roles_and_capabilities');
	}

// for role & capabilities
	public static function roles_and_capabilities()
	{
		global $wp_roles;
		$capabilities = MailPress::capabilities();

		foreach($wp_roles->role_names as $role => $name)
		{
			if ('administrator' == $role) continue;

			$r = get_role($role);
			$rcs = get_option('MailPress_r&c_' . $role);

			foreach ($capabilities as $capability => $v)
			{
				if (isset($rcs[$capability])) 	$r->add_cap($capability);
				else						$r->remove_cap($capability);
			}
		}
	}

// for settings

	public static function styles($styles, $screen) 
	{
		if ($screen != MailPress_page_settings) return $styles;

		wp_register_style ( 'mp-r-and-c', get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/settings_roles_and_capabilities.css');
		$styles[] = 'mp-r-and-c';

		return $styles;
	}

	public static function scripts($scripts, $screen) 
	{
		if ($screen != MailPress_page_settings) return $scripts;

		wp_register_script( 'mp-r-and-c', '/' . MP_PATH . 'mp-admin/js/settings_roles_and_capabilities.js', array('jquery'), false, 1);

		$scripts[] = 'mp-r-and-c';

		return $scripts;
	}

	public static function settings_update()
	{
		include (MP_TMP . 'mp-admin/includes/settings/roles_and_capabilities.php');
	}

	public static function settings_tab($tab)
	{
		$t = ($tab=='MailPress_roles_and_capabilities') ? " class='ui-tabs-selected'" : ''; 
		echo "\t\t\t<li $t><a href='#fragment-MailPress_roles_and_capabilities'><span class='button-secondary'>" . __('R&amp;C', 'MailPress') . "</span></a></li>\n";
	}

	public static function settings_div()
	{
		include (MP_TMP . 'mp-admin/includes/settings/roles_and_capabilities.form.php');
	}

	public static function mp_action_r_and_c()
	{
		$rcs_option = 'MailPress_r&c_' . $_POST['role'];
		$r = get_role($_POST['role']);

		$rcs = get_option($rcs_option);
		if (empty($rcs)) $rcs = array();

		if ($_POST['add'])
		{
			$rcs[$_POST['capability']] = 'on';
			if ($r) $r->add_cap($_POST['capability']);
		}
		else
		{
			unset ($rcs[$_POST['capability']] );
			if ($r) $r->remove_cap($_POST['capability']);
		}
		if (!add_option ($rcs_option, $rcs )) update_option ($rcs_option, $rcs);

		MailPress::mp_die(1);
	}
}

$MailPress_roles_and_capabilities = new MailPress_roles_and_capabilities();
}
?>