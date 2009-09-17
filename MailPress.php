<?php
/*
Plugin Name: MailPress
Plugin URI: http://www.mailpress.org
Description: The WordPress mailing platform. 
Author: Andre Renaut
Requires at least: 2.8
Tested up to: 2.8
Version: 4.0
Author URI: http://www.mailpress.org
*/
//define ('MP_SCRIPT_DEBUG', true);

define ('MP_FOLDER', 	basename(dirname(__FILE__)));
define ('MP_PATH', 	PLUGINDIR . '/' . MP_FOLDER . '/' );
define ('MP_TMP', 	dirname(__FILE__) . '/');
define ('MP_Action_url', get_option('siteurl') . '/' . MP_PATH . 'mp-includes/action.php');

require_once(MP_TMP . 'mp-includes/class/MP_abstract.class.php');

class MailPress extends MP_abstract
{
	function __construct() 
	{
		if (defined('MP_SCRIPT_DEBUG')) { self::require_class('Log'); global $mp_debug_log; $mp_debug_log = new MP_Log('debug', ABSPATH . MP_PATH, MP_FOLDER, false, 'general'); }

		global $wpdb;
// for mysql
		$wpdb->mp_mails     = $wpdb->prefix . 'MailPress_mails';
		$wpdb->mp_mailmeta  = $wpdb->prefix . 'MailPress_mailmeta';
		$wpdb->mp_users     = $wpdb->prefix . 'MailPress_users';
		$wpdb->mp_usermeta  = $wpdb->prefix . 'MailPress_usermeta';
		$wpdb->mp_stats     = $wpdb->prefix . 'MailPress_stats';
// for gettext
		load_plugin_textdomain('MailPress', MP_PATH . 'mp-content/languages');
// for widget & comments & newsletters
		add_action('widgets_init', 	array('MailPress', 'widgets_init'));
		add_action('plugins_loaded', 	array('MailPress', 'plugins_loaded'));
// for shortcode
		add_shortcode('mailpress', 	array('MailPress', 'shortcode'));
// for post
		add_action('delete_post', 	array('MailPress', 'delete_stats_c'));
// for shutdown
		add_action('shutdown', 		array('MailPress', 'shutdown'));

// for admin plugin pages
		define ('MailPress_page_mails', 	'mailpress_mails');
		define ('MailPress_page_write', 	'mailpress_write');
		define ('MailPress_page_edit', 	MailPress_page_mails . '&file=write');
		define ('MailPress_page_revision', 	MailPress_page_mails . '&file=revision');
		define ('MailPress_page_mail', 	MailPress_page_mails . '&file=mail');
		define ('MailPress_page_themes', 	'mailpress_themes');
		define ('MailPress_page_settings', 	'mailpress_settings');
		define ('MailPress_page_users', 	'mailpress_users');
		define ('MailPress_page_user', 	MailPress_page_users . '&file=uzer');
// for admin plugin urls
		$file = 'admin.php';
		define ('MailPress_mails', 		$file . '?page=' 	. MailPress_page_mails);
		define ('MailPress_write', 		$file . '?page=' 	. MailPress_page_write);
		define ('MailPress_edit', 		$file . '?page=' 	. MailPress_page_edit);
		define ('MailPress_revision', 	$file . '?page=' 	. MailPress_page_revision);
		define ('MailPress_mail', 		$file . '?page=' 	. MailPress_page_mail);
		define ('MailPress_themes', 		$file . '?page=' 	. MailPress_page_themes);
		define ('MailPress_settings', 	'options-general.php' . '?page=' 	. MailPress_page_settings);
		define ('MailPress_users', 		$file . '?page=' 	. MailPress_page_users);
		define ('MailPress_user', 		$file . '?page=' 	. MailPress_page_user);
//¤ ajax ¤//
		add_action('mp_action_mp_meta_box_post', 	array('MP_Newsletter', 'mp_action_mp_meta_box_post'));

// for wp admin
		if (is_admin())
		{
		// for contextual help
			define ('MailPress_help_url', 	'http://www.mailpress.org/wiki');
		// for install
			register_activation_hook(MP_FOLDER . '/MailPress.php', array('MailPress', 'install'));
		// for link on plugin page
			add_filter('plugin_action_links', 	array('MailPress', 'plugin_action_links'), 10, 2 );
		// for favorite action
			add_filter('favorite_actions', 	array('MailPress', 'favorite_actions'), 8, 1);
		// for menu
			add_action('admin_menu', 		array('MailPress', 'menu'), 8, 1);

		// load admin page
			add_action('init', 			array('MailPress', 'load_admin_page'));

		// for meta box in write post
			add_action('do_meta_boxes', 		array('MailPress', 'meta_boxes'), 8, 3);
		}
		do_action('MailPress_init');
	}
	
////  Widget & Comments & Newsletters ////

	public static function widgets_init()
	{
		self::require_class('Widget');
		register_widget('MP_Widget');
	}

	public static function plugins_loaded()
	{
		global $mp_subscriptions;

	// for comments
		if (isset($mp_subscriptions['subcomment']))
		{
			self::require_class('Comment');
			add_action('comment_form', 			array('MP_Comment', 'comment_form'));
			add_action('comment_post', 			array('MP_Comment', 'comment_post'), 8, 1);
			add_action('wp_set_comment_status', 	array('MP_Comment', 'approve_comment'));	
		}

	// for newsletters
		self::require_class('Newsletter');
	// for post published
		add_action('publish_post', 			array('MP_Newsletter', 'have_post'), 8, 1);
	// for mailpress shutdown
		add_action('mp_build_newsletters', 		array('MP_Newsletter', 'process'));
		add_action('mp_process_newsletters', 	array('MP_Newsletter', 'process'));
	// for shortcode
		add_filter('MailPress_form_defaults', 	array('MP_Newsletter', 'form_defaults'), 8, 1);
		add_filter('MailPress_form_options', 	array('MP_Newsletter', 'form_options'), 8, 1);
		add_filter('MailPress_form_submit', 	array('MP_Newsletter', 'form_submit'), 8, 2);
		add_action('MailPress_form', 		  	array('MP_Newsletter', 'form'), 1, 2); 

		MP_Newsletter::register_newsletters();
	}

////  Shortcode  ////

	public static function shortcode($options=false)
	{
		$options['widget_id'] = 'sc';

		ob_start();
			self::form($options);
			$x = ob_get_contents();
		ob_end_clean();
		return $x; 
	}

////  Post ////

	public static function delete_stats_c($postid)
	{
		global $wpdb;
		$x = $wpdb->get_var("SELECT sum(scount) FROM $wpdb->mp_stats WHERE stype = 'c' AND slib = '$postid';");
		if ($x && ($x > 0)) self::update_stats('c', $postid, ($x * -1));
	}

	public static function update_stats($stype, $slib, $scount) 
	{
		global $wpdb;
		$sdate  = date('Y-m-d');
		$query = "UPDATE $wpdb->mp_stats SET scount=scount+$scount WHERE sdate = '$sdate' AND stype = '$stype' AND slib = '$slib';";
		$results = $wpdb->query( $query );
		if (!$results)	$wpdb->insert($wpdb->mp_stats, compact('sdate', 'stype', 'slib', 'scount'));
	}

////	Shutdown   ////

	public static function shutdown() 
	{
		flush();

		global $mp_general;
		self::no_abort_limit();
		$mp_general = get_option('MailPress_general');
		if ($mp_general)
		{
			$now = date('Ymd');
			$lastnewsletter = (isset($mp_general['lastnewsletter'])) ? $mp_general['lastnewsletter'] : '00000000' ;
			if ($now != $lastnewsletter) 
			{
				if ((!wp_next_scheduled( 'mp_process_newsletters' )) || (!wp_next_scheduled( 'mp_build_newsletters' ))) 
					wp_schedule_single_event(time()+30, 'mp_process_newsletters');
				$mp_general['lastnewsletter'] = $now;
				update_option ('MailPress_general', $mp_general);
			}
		}
		if (defined('MP_SCRIPT_DEBUG')) { global $mp_debug_log; $mp_debug_log->end(true); }
	}

	public static function no_abort_limit()
	{
		if (function_exists('ignore_user_abort')) 	ignore_user_abort(1);
		if (function_exists('set_time_limit')) 		if( !ini_get('safe_mode') ) set_time_limit(0);
	}

////	user	////

	public static function get_wp_user_id() 
	{
		global $user_ID;
		if ( is_numeric($user_ID) ) return $user_ID;
		return 0;
	}

	public static function get_wp_user_email() 
	{
		switch (true)
		{
			case (isset($_POST['email'])) :
				return $_POST['email'];
			break;
			default :
				$u = self::get_wp_user_id();
				if ($u)
				{
					$user = get_userdata($u);
					return $user->user_email;
				}
				else
				{
					if (isset($_COOKIE['comment_author_email_' . COOKIEHASH])) return $_COOKIE['comment_author_email_' . COOKIEHASH];
				}
			break;
		}
		return '';
	}

////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////

////  Install  ////

	public static function install() 
	{
		global $wp_version; 

		$min_ver_php = '5.2.0';
		$min_ver_wp  = '2.8';
		$m = array();

		if (version_compare(PHP_VERSION, $min_ver_php, '<')) 	$m[] = sprintf(__('Your %1$s version is \'%2$s\', at least version \'%3$s\' required.', 'MailPress'), __('PHP'), PHP_VERSION, $min_ver_php );
		if (version_compare($wp_version, $min_ver_wp , '<'))	$m[] = sprintf(__('Your %1$s version is \'%2$s\', at least version \'%3$s\' required.', 'MailPress'), __('WordPress'), $wp_version , $min_ver_wp );
		if (!is_writable(MP_TMP . 'tmp'))				$m[] = sprintf(__('The directory \'%1$s\' is not writable.', 'MailPress'), MP_TMP . 'tmp');

		if (!empty($m))
		{
			$err  = sprintf(__('<b>Sorry, but you can\'t run this plugin : %1$s. </b>', 'MailPress'), $_GET['plugin']);
			$err .= '<ol><li>' . implode('</li><li>', $m) . '</li></ol>';

			if (isset($_GET['plugin'])) deactivate_plugins($_GET['plugin']);	
			trigger_error($err, E_USER_ERROR);
			return false;
		}
		include (MP_TMP . 'mp-admin/includes/install/mailpress.php');
	}

////  Settings  ////

	public static function plugin_action_links($links, $file)
	{
		return MailPress::plugin_links($links, $file, plugin_basename(__FILE__), '0');
	}

	public static function plugin_links($links, $file, $basename, $tab)
	{
		if ($file != $basename) return $links;

		$settings_link = "<a href='" . MailPress_settings . "#fragment-$tab'>" . __('Settings') . '</a>';
		array_unshift ($links, $settings_link);
		return $links;
	}

////  Favorite action  ////

	public static function favorite_actions($actions) 
	{
		$actions[MailPress_write] = array(__('New Mail', 'MailPress'), 'MailPress_edit_mails');
		return $actions;
	}

////	Menu	////

	public static function menu() 
	{
		$menus = array();
		$capabilities	= self::capabilities();

		foreach ($capabilities as $capability => $datas)
		{
			if (isset($datas['menu']) && $datas['menu'] && current_user_can($capability))
			{
				$datas['capability'] 	= $capability;
				$menus[]			= $datas;
			}
		}
		$count = count($menus);
		if (0 == $count) return;

		uasort($menus, create_function('$a, $b', 'return strcmp($a["menu"], $b["menu"]);'));

		$first = true;
		foreach ($menus as $menu)
		{
			if (!$menu['parent'])
			{
				if ($first)
				{
					$toplevel = $menu['page'];
					add_menu_page('', __('Mails', 'MailPress'), $menu['capability'], $menu['page'], $menu['func'], 'div');
				}
				$first = false;
			}

			$parent = ($menu['parent']) ? $menu['parent'] : $toplevel;
			add_submenu_page( $parent, $menu['page_title'], $menu['menu_title'], $menu['capability'], $menu['page'], $menu['func']);

			if ($menu['page'] == MailPress_page_mails)
			{
				add_submenu_page($toplevel, __('Write Mail', 'MailPress'), __('New Mail', 'MailPress'), 'MailPress_edit_mails', MailPress_page_write, array('MP_AdminPage', 'body'));
			}
		}
	}

////  Roles and capabilities  ////

	public static function roles_and_capabilities()
	{
		$capabilities	= self::capabilities();
		$role 		= get_role('administrator');
		foreach ($capabilities as $capability => $v) $role->add_cap($capability);
		do_action('MailPress_roles_and_capabilities');
	}

	public static function capabilities()
	{
		include (MP_TMP . 'mp-admin/includes/options/capabilities.php');
		$capabilities	= apply_filters('MailPress_capabilities', $capabilities);
		return $capabilities;
	}

	public static function capability_groups()
	{
		return array('admin' => __('Admin', 'MailPress'), 'mails' => __('Mails', 'MailPress'), 'users' => __('Users', 'MailPress'));
	}

////	Dashboard	////

	public static function wp_dashboard_setup()
	{
		include (MP_TMP . 'mp-admin/includes/dashboard.php');
	}

////  Load admin page  ////

	public static function get_page()
	{
		if (!isset($_GET['page'])) return false;
		$page = $_GET['page'];
		if (isset($_GET['file'])) $page .= '&file=' . $_GET['file'];
		return $page;
	}

	public static function load_admin_page()
	{
		global $mp_general;

// for roles & capabilities
		self::roles_and_capabilities();

// for dashboard
		if ( isset($mp_general['dashboard']) && current_user_can('MailPress_edit_dashboard') )
			add_filter('wp_dashboard_setup', 	array('MailPress', 'wp_dashboard_setup'));

// for global css
		$pathcss		= MP_TMP . 'mp-admin/css/colors_' . get_user_option('admin_color') . '.css';
		$css_url		= '/' . MP_PATH . 'mp-admin/css/colors_' . get_user_option('admin_color') . '.css';
		$css_url_default 	= '/' . MP_PATH . 'mp-admin/css/colors_fresh.css';
		$css_url		= (is_file($pathcss)) ? $css_url : $css_url_default;
		wp_register_style ( 'MailPress_colors', 	$css_url);
		wp_enqueue_style  ( 'MailPress_colors' );

// for specific mailpress page
		$admin_page = self::get_page();
		if (!$admin_page) return;

		$hub = array (	MailPress_page_mails 	=> 'mails', 
					MailPress_page_write 	=> 'write', 
					MailPress_page_edit 	=> 'write', 
					MailPress_page_revision => 'revision', 
					MailPress_page_mail 	=> 'mail', 
					MailPress_page_themes 	=> 'themes', 
					MailPress_page_settings => 'settings', 
					MailPress_page_users 	=> 'users', 
					MailPress_page_user 	=> 'user'
				);

		if (isset($hub[$admin_page])) require_once(MP_TMP . 'mp-admin/' . $hub[$admin_page] . '.php');
		else					do_action('MailPress_load_admin_page', $admin_page);

		if (class_exists('MP_AdminPage')) $MP_AdminPage = new MP_AdminPage();
	}

////  For meta box in write post  ////

	public static function meta_boxes($page, $type, $post) 
	{
		if ('post' != $page) return;
		if ('side' != $type) return;

		wp_register_script('mp-meta-box-post', 	'/' . MP_PATH . 'mp-includes/js/mp_meta_box_post.js', array('wp-ajax-response'), false, 1);
		wp_localize_script('mp-meta-box-post',	'mpMeta_box_postL10n', array( 
			'url' => MP_Action_url
		) );
		wp_enqueue_script('mp-meta-box-post');

		wp_register_script( 'mp-thickbox', 		'/' . MP_PATH . 'mp-includes/js/mp_thickbox.js', array('thickbox'), false, 1);
		wp_enqueue_script('mp-thickbox');

		add_meta_box('MailPress_post_test_div', __('MailPress test', 'MailPress'), array('MailPress', 'meta_box_post'), 'post', 'side', 'core');
	}

	public static function meta_box_post($post) 
	{
		include( MP_TMP . 'mp-includes/mp_meta_box_post.php');
	}

////	Subscription form	////

	public static function form($options = array())
	{
		static $cc = 0;

		$options['widget_id'] = (isset($options['widget_id'])) ?  $options['widget_id'] . '_' . $cc : 'mf_' . $cc;

		MP_Widget::widget_form($options);

		$cc++;
	}

////	THE MAIL

	public static function mail($args)
	{
		self::require_class('Mail');
		$x = new MP_Mail();
		return $x->send($args);
	}
}

$mp_general  	= get_option('MailPress_general');
$mp_subscriptions = get_option('MailPress_subscriptions');

// pluggable functions
if (isset($mp_general['wp_mail'])) 	include (MP_TMP . 'mp-includes/wp_pluggable.php');

$MailPress = new MailPress();
?>