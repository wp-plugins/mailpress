<?php
if ( class_exists('MailPress') )
{
/*
Plugin Name: MailPress_bounce_handling
Plugin URI: http://www.mailpress.org
Description: This is just an addon for MailPress to handle bounce mails (based on <a href='http://en.wikipedia.org/wiki/VERP'>VERP</a>).
Author: Andre Renaut
Version: 4.0.1
Author URI: http://www.mailpress.org
*/

class MailPress_bounce_handling
{
	const metakey = '_MailPress_bounce_handling';

	const bt = 100;

	function __construct()
	{
// prepare mail
		add_filter('MailPress_swift_message_headers',  	array('MailPress_bounce_handling', 'swift_message_headers'), 8, 2);

// for batch mode
		add_action('mp_process_bounce_handling', 		array('MailPress_bounce_handling', 'process'));

		$bounce_handling_config = get_option('MailPress_bounce_handling');
		if ('wpcron' == $bounce_handling_config['batch_mode'])
		{	
			add_action('MailPress_schedule_bounce_handling', 	array('MailPress_bounce_handling', 'schedule'));
		}
// for mails list
		add_filter('MailPress_columns_mails', 		array('MailPress_bounce_handling', 'columns_mails'), 10, 1);
		add_action('MailPress_get_row_mails',  		array('MailPress_bounce_handling', 'get_row_mails'), 10, 3);
// view bounce
		add_action('mp_action_view_bounce', 		array('MailPress_bounce_handling', 'mp_action_view_bounce')); 

		if (is_admin())
		{
		// for install
			register_activation_hook(MP_FOLDER . '/MailPress_bounce_handling.php', 	array('MailPress_bounce_handling', 'install'));
			register_deactivation_hook(MP_FOLDER . '/MailPress_bounce_handling.php',array('MailPress_bounce_handling', 'uninstall'));
		// for link on plugin page
			add_filter('plugin_action_links', 		array('MailPress_bounce_handling', 'plugin_action_links'), 10, 2 );
		// for settings
			add_filter('MailPress_scripts', 		array('MailPress_bounce_handling', 'scripts'), 8, 2);
			add_action('MailPress_settings_update', 	array('MailPress_bounce_handling', 'settings_update'));
			add_action('MailPress_settings_tab', 	array('MailPress_bounce_handling', 'settings_tab'), 8, 1);
			add_action('MailPress_settings_div', 	array('MailPress_bounce_handling', 'settings_div'));
			add_action('MailPress_settings_logs', 	array('MailPress_bounce_handling', 'settings_logs'), 8, 1);

			if ('wpcron' == $bounce_handling_config['batch_mode'])
			{	
			// for autorefresh
				add_filter('MailPress_autorefresh_js',	array('MailPress_bounce_handling', 'autorefresh_js'), 8, 1);
				add_filter('MailPress_autorefresh_every', array('MailPress_bounce_handling', 'autorefresh_every'), 8, 1);
			}

		// for users list
			add_action('MailPress_get_icon_users', 	array('MailPress_bounce_handling', 'get_icon_users'), 8, 1);
		// for meta box in user page
			add_action('MailPress_add_meta_boxes_user',array('MailPress_bounce_handling', 'meta_boxes_user'), 8, 2);
		}
	}

// prepare mail
	public static function swift_message_headers($message, $row)
	{
		$bounce_handling = get_option('MailPress_bounce_handling');

		if (!MailPress::is_email($bounce_handling['Return-Path'])) return $message;

		$prefix = substr($bounce_handling['Return-Path'], 0, strpos($bounce_handling['Return-Path'], '@'));
		$domain = substr($bounce_handling['Return-Path'], strpos($bounce_handling['Return-Path'], '@') + 1 );

		$ReturnPath = $prefix . '+' . $row->id . '+' . '{{_user_id}}' . '@' . $domain;
		if (isset($row->mp_user_id)) $ReturnPath = str_replace('{{_user_id}}', $row->mp_user_id, $ReturnPath);

		$message->setReturnPath($ReturnPath);

		return $message;
	}

// process
	public static function process()
	{
		MailPress::no_abort_limit();

		MailPress::require_class('Bounce');
		$MP_Bounce = new MP_Bounce();
	}

// schedule
	public static function schedule()
	{
		$bounce_handling_config = get_option('MailPress_bounce_handling');

		if (!wp_next_scheduled( 'mp_process_bounce_handling' )) 
			wp_schedule_single_event(time()+$bounce_handling_config['every'], 'mp_process_bounce_handling');
	}



////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////

// install
	public static function install() 
	{
		do_action('MailPress_schedule_bounce_handling');
		include ( MP_TMP . 'mp-admin/includes/install/bounce_handling.php');
	}

	public static function uninstall() 
	{
		wp_clear_scheduled_hook('mp_process_bounce_handling');
	}

// for link on plugin page
	public static function plugin_action_links($links, $file)
	{
		return MailPress::plugin_links($links, $file, plugin_basename(__FILE__), 'MailPress_bounce_handling');
	}

// for settings
	public static function scripts($scripts, $screen) 
	{
		if ($screen != MailPress_page_settings) return $scripts;

		wp_register_script( 'mp-bounce-handling', 	'/' . MP_PATH . 'mp-admin/js/settings_bounce_handling.js', array(), false, 1);
		$scripts[] = 'mp-bounce-handling';
		return $scripts;
	}

	public static function settings_update()
	{
		include (MP_TMP . 'mp-admin/includes/settings/bounce_handling.php');
	}

	public static function settings_tab($tab)
	{
		$t = ($tab=='MailPress_bounce_handling') ? " class='ui-tabs-selected'" : ''; 
		echo "\t\t\t<li $t><a href='#fragment-MailPress_bounce_handling'><span class='button-secondary'>" . __('Bounces', 'MailPress') . "</span></a></li>\n";
	}

	public static function settings_div()
	{
		include (MP_TMP . 'mp-admin/includes/settings/bounce_handling.form.php');
	}

	public static function settings_logs($logs)
	{
		MP_AdminPage::logs_sub_form('bounce_handling', $logs, __('Bounces', 'MailPress'), __('Bounces log', 'MailPress'), __('(for <b>ALL</b> mails send through MailPress)', 'MailPress'), __('Number of Bounces log files : ', 'MailPress'));
	}

// for mails list
	public static function columns_mails($x)
	{
		$date = array_pop($x);
		$x['bounce_handling']	=  __('Bounce rate', 'MailPress');
		$x['date']			= $date;
		return $x;
	}

	public static function get_row_mails($column_name, $mail, $url_parms)
	{
		global $wpdb;
		switch ($column_name)
		{
			case 'bounce_handling' :
				if (MailPress::is_email($mail->toemail)) $total = 1;
				elseif(is_serialized($mail->toemail)) $total = count(unserialize($mail->toemail));
				else return;

				MailPress::require_class('Mailmeta');
				$result = MP_Mailmeta::get($mail->id, self::metakey);
				if ($result) if ($total > 0) printf("%01.2f %%", 100 * $result/$total );
			break;
		}
	}

// for users list
	public static function get_icon_users($mp_user)
	{
		if ('bounced' != $mp_user->status) return;
//		MailPress::require_class('Usermeta');
//		if (!MP_Usermeta::get($mp_user->id, self::metakey)) return;
?>
			<img class='bounced' alt="<?php _e('Bounced', 'MailPress'); ?>" title="<?php _e('Bounced', 'MailPress'); ?>" src='<?php echo get_option('siteurl') . '/' . MP_PATH; ?>/mp-admin/images/bounce_handling.png' />
<?php
	}

// for user page
	public static function meta_boxes_user($mp_user_id, $screen)
	{
		MailPress::require_class('Usermeta');
		$usermeta = MP_Usermeta::get($mp_user_id, self::metakey);
		if (!$usermeta) return;

		add_meta_box('bouncehandlingdiv', __('Bounces', 'MailPress'), array('MailPress_bounce_handling', 'meta_box_user'), $screen, 'side', 'core');
	}

	public static function meta_box_user($mp_user)
	{
		MailPress::require_class('Usermeta');
		$usermeta = MP_Usermeta::get($mp_user->id, self::metakey);
		if (!$usermeta) return;

		global $wpdb;
		echo '<b>' . __('Bounces', 'MailPress') . '</b> : &nbsp;' . $usermeta['bounce'] . '<br />';
		foreach($usermeta['bounces'] as $mail_id => $messages)
		{
			foreach($messages as $k => $message)
			{
				echo '<br />';
				$subject = $wpdb->get_var("SELECT subject FROM $wpdb->mp_mails WHERE id = " . $mail_id . ';');
				$subject = ($subject) ? $subject : __('(deleted)','MailPress');

				$view_url		= clean_url(add_query_arg( array('action' => 'view_bounce', 'user_id' => $mp_user->id, 'mail_id' => $mail_id, 'id' => $k, 'KeepThis' => 'true', 'TB_iframe' => 'true', 'width' => '600', 'height' => '400'), MP_Action_url ));
				$actions['view'] = "<a href='$view_url' class='thickbox'  title='" . __('View', 'MailPress') . "'>" . $subject . '</a>';

				echo '(' . $mail_id . ') ' . $actions['view'];
			}
		}
	}

	public static function mp_action_view_bounce()
	{
		$mp_user_id = $_GET['user_id'];
		$mail_id    = $_GET['mail_id'];
		$bounce_id  = $_GET['id'];

		MailPress::require_class('Usermeta');
		$usermeta = MP_Usermeta::get($mp_user_id, self::metakey);
		if (!$usermeta) return;

		$x = new stdClass();
		$x->plaintext = htmlspecialchars($usermeta['bounces'][$mail_id][$bounce_id]['message']);

		include(MP_TMP . 'mp-includes/html/plaintext.php');
	}

	public static function autorefresh_js($scripts)
	{
		$bounce_handling_config = get_option('MailPress_bounce_handling');
		$every   = apply_filters('MailPress_autorefresh_every', $bounce_handling_config['every']);

		$checked = (isset($_GET['autorefresh'])) ?  " checked='checked'" : '';
		$time    = (isset($_GET['autorefresh'])) ?  $_GET['autorefresh'] : $every;
		$time    = (is_numeric($time) && ($time > $every)) ? $time : $every;
		$time    = "<input type='text' value='$time' maxlength='3' id='MP_Refresh_every' class='screen-per-page'/>";
		$option  = '<h5>' . __('Auto refresh for WP_Cron', 'MailPress') . '</h5>';
		$option .= "<div><input id='MP_Refresh' type='checkbox'$checked style='margin:0 5px 0 2px;' /><span class='MP_Refresh'>" . sprintf(__('%1$s Autorefresh %2$s every %3$s sec', 'MailPress'), "<label for='MP_Refresh' style='vertical-align:inherit;'>", '</label>', $time) . "</span></div>";

		wp_register_script( 'mp-refresh', 	'/' . MP_PATH . 'mp-includes/js/mp_refresh.js', array('schedule'), false, 1);
		wp_localize_script( 'mp-refresh', 	'adminMpRefreshL10n', array(
				'every' 	=> $every,

				'message' 	=> __('Autorefresh in %i% sec', 'MailPress'), 

				'option'	=> $option,
				'l10n_print_after' => 'try{convertEntities(adminmailsL10n);}catch(e){};'
		) );
		$scripts[] = 'mp-refresh';
		return $scripts;
	}

	public static function autorefresh_every($every)
	{
		$bounce_handling_config = get_option('MailPress_bounce_handling');
		return ($every < $bounce_handling_config['every']) ? $every : $bounce_handling_config['every'];
	}
}

$MailPress_bounce_handling = new MailPress_bounce_handling();
}
?>