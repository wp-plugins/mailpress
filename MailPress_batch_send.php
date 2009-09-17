<?php
if (class_exists('MailPress'))
{
/*
Plugin Name: MailPress_batch_send 
Plugin URI: http://www.mailpress.org
Description: This is just an addon for MailPress to send mail in batch mode.
Author: Andre Renaut
Version: 4.0
Author URI: http://www.mailpress.org
*/

class MailPress_batch_send
{
	const metakey = '_MailPress_batch_send';

	function __construct()
	{
// prepare mail
		add_filter('MailPress_status_mail', 		array('MailPress_batch_send', 'status_mail'));

// for batch mode
		add_action('mp_action_batchsend', 			array(&$this, 'process'));
		add_action('mp_process_batch_send', 		array(&$this, 'process'));

		$batch_send_config = get_option('MailPress_batch_send');
		if ('wpcron' == $batch_send_config['batch_mode'])
		{	
			add_action('MailPress_schedule_batch_send', 	array('MailPress_batch_send', 'schedule'));
		}
// for to mails column
		add_filter('MailPress_to_mails_column', 		array('MailPress_batch_send', 'to_mails_column'), 8, 2);

		if (is_admin())
		{
		// for install
			register_activation_hook(MP_FOLDER . '/MailPress_batch_send.php', 	array('MailPress_batch_send', 'install'));
			register_deactivation_hook(MP_FOLDER . '/MailPress_batch_send.php',	array('MailPress_batch_send', 'uninstall'));
		// for link on plugin page
			add_filter('plugin_action_links', 		array('MailPress_batch_send', 'plugin_action_links'), 10, 2 );
		// for settings
			add_filter('MailPress_scripts', 		array('MailPress_batch_send', 'scripts'), 8, 2);
			add_action('MailPress_settings_update', 	array('MailPress_batch_send', 'settings_update'));
			add_action('MailPress_settings_tab', 	array('MailPress_batch_send', 'settings_tab'), 8, 1);
			add_action('MailPress_settings_div', 	array('MailPress_batch_send', 'settings_div'));
			add_action('MailPress_settings_logs', 	array('MailPress_batch_send', 'settings_logs'), 8, 1);

			if ('wpcron' == $batch_send_config['batch_mode'])
			{	
			// for autorefresh
				add_filter('MailPress_autorefresh_js',	array('MailPress_batch_send', 'autorefresh_js'), 8, 1);
				add_filter('MailPress_autorefresh_every', array('MailPress_batch_send', 'autorefresh_every'), 8, 1);
			}
		}
	}

// prepare mail
	public static function status_mail()
	{
		return 'unsent';
	}

// process
	public static function process()
	{
		MailPress::no_abort_limit();

		MailPress::require_class('Batch');
		$MP_Batch = new MP_Batch();
	}

// schedule
	public static function schedule()
	{
		$batch_send_config = get_option('MailPress_batch_send');

		if (!wp_next_scheduled( 'mp_process_batch_send' )) 
		if (!wp_next_scheduled( 'mp_action_batchsend' ))
			wp_schedule_single_event(time()+$batch_send_config['every'], 'mp_process_batch_send');
	}

////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////

// for install
	public static function install() 
	{
		do_action('MailPress_schedule_batch_send');
		include ( MP_TMP . 'mp-admin/includes/install/batch_send.php');
	}

	public static function uninstall() 
	{
		wp_clear_scheduled_hook('mp_action_batchsend');
		wp_clear_scheduled_hook('mp_process_batch_send');
	}

// for link on plugin page
	public static function plugin_action_links($links, $file)
	{
		return MailPress::plugin_links($links, $file, plugin_basename(__FILE__), 'MailPress_batch_send');
	}

// for settings
	public static function scripts($scripts, $screen) 
	{
		if ($screen != MailPress_page_settings) return $scripts;

		wp_register_script( 'mp-batchsend', 	'/' . MP_PATH . 'mp-admin/js/settings_batch_send.js', array(), false, 1);
		$scripts[] = 'mp-batchsend';
		return $scripts;
	}

	public static function settings_update()
	{
		include (MP_TMP . 'mp-admin/includes/settings/batch_send.php');
	}

	public static function settings_tab($tab)
	{
		$t = ($tab=='MailPress_batch_send') ? " class='ui-tabs-selected'" : ''; 
		echo "\t\t\t<li $t><a href='#fragment-MailPress_batch_send'><span class='button-secondary'>" . __('Batch', 'MailPress') . "</span></a></li>\n";
	}

	public static function settings_div()
	{
		include (MP_TMP . 'mp-admin/includes/settings/batch_send.form.php');
	}

	public static function settings_logs($logs)
	{
		MP_AdminPage::logs_sub_form('batch_send', $logs, __('Batch', 'MailPress'), __('Batch log', 'MailPress'), __('(for <b>ALL</b> mails send through MailPress)', 'MailPress'), __('Number of Batch log files : ', 'MailPress'));
	}

// for mails list
	public static function to_mails_column($to, $mail)
	{
		MailPress::require_class('Mailmeta');
		$mailmeta = MP_Mailmeta::get( $mail->id , self::metakey);

		if ($mailmeta)
		{
			if ($mailmeta['sent'] != $mailmeta['count']) return sprintf( __ngettext( _c('%1$s on %2$s sent| Singular', 'MailPress'), _c('%1$s on %2$s sent| Plural', 'MailPress'), $mailmeta['sent'] ), $mailmeta['sent'], $mailmeta['count'] );
		}
		else
		{
			if (self::status_mail() == $mail->status) return __('Pending...', 'MailPress');
		}

		return $to;
	}

	public static function autorefresh_js($scripts)
	{
		$batch_send_config = get_option('MailPress_batch_send');
		$every   = apply_filters('MailPress_autorefresh_every', $batch_send_config['every']);

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
		$batch_send_config = get_option('MailPress_batch_send');
		return ($every < $batch_send_config['every']) ? $every : $batch_send_config['every'];
	}
}

$MailPress_batch_send = new MailPress_batch_send();
}
?>