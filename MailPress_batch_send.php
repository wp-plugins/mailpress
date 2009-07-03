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

// for batch mode
		$batch_send_config = get_option('MailPress_batch_send');
		if ('wpcron' == $batch_send_config['batch_mode'])	add_action('MailPress_schedule_batch_send', 	array('MailPress_batch_send', 'schedule'));

// for batch processing
		add_filter('MailPress_status_mail', 		array('MailPress_batch_send', 'status_mail'), 8, 1);

		add_filter('MailPress_swift_send', 			array('MailPress_batch_send', 'swift_send'), 8, 1);

		add_action('mp_action_batchsend', 			array(&$this, 'process'));
		add_action('mp_process_batch_send', 		array(&$this, 'process')); 

		if (is_admin())
		{
		// for install
			add_action('activate_' . MP_FOLDER . '/add_ons/MailPress_batch_send.php', 	array('MailPress_batch_send', 'install'));
		// for link on plugin page
			add_filter('plugin_action_links', 		array('MailPress_batch_send', 'plugin_action_links'), 10, 2 );
		// for settings
			add_filter('MailPress_scripts', 		array('MailPress_batch_send', 'scripts'), 8, 2);
			add_action('MailPress_settings_update', 	array('MailPress_batch_send', 'settings_update'));
			add_action('MailPress_settings_tab', 	array('MailPress_batch_send', 'settings_tab'), 8, 1);
			add_action('MailPress_settings_div', 	array('MailPress_batch_send', 'settings_div'));
			add_action('MailPress_settings_logs', 	array('MailPress_batch_send', 'settings_logs'), 8, 1);

		// for to mails column
			//add_filter('screen_options', 			array('MailPress_batch_send', 'screen_options'), 8, 2);
			add_filter('MailPress_to_mails_column', 	array('MailPress_batch_send', 'to_mails_column'), 8, 2);
		}
	}

// for batch mode

	public static function schedule()
	{
		$batch_send_config = get_option('MailPress_batch_send');

		if ('wpcron' == $batch_send_config['batch_mode']) 
			if (!wp_next_scheduled( 'mp_process_batch_send' )) 
			if (!wp_next_scheduled( 'mp_action_batchsend' ))
				wp_schedule_single_event(time()+$batch_send_config['every'], 'mp_process_batch_send');
	}

// for batch processing

	public static function status_mail($x = false)
	{
		return 'unsent';
	}

	public static function process()
	{
		MailPress::no_abort_limit();

		self::update_batch_env();

		extract(self::select_mail());

		if ($send) 	self::batch($mail, $mailmetas);
		else		self::alldone();

		self::update_batch_env(!$send);
	}

	public static function update_batch_env($done=true)
	{
		global $wpdb;
		$status 			= self::status_mail();
		$batch_send_config 	= get_option('MailPress_batch_send');
		
		$query = "SELECT id, toemail FROM $wpdb->mp_mails WHERE status = '$status' ;";
		$mails = $wpdb->get_results($query);

		if ($mails)
		{
			foreach ($mails as $mail)
			{
				MailPress::require_class('Mailmeta');
				$mailmetas 		= MP_Mailmeta::get( $mail->id , self::metakey);
				if ($mailmetas)
				{
					switch (true)
					{
						case ($mailmetas['try'] == $mailmetas['max_try'] ) :
							self::update_mail($mail->id, count($mailmetas['failed']));
						break;
						case ($mailmetas['sent'] == $mailmetas['count']) :
							self::update_mail($mail->id, count($mailmetas['failed']));
						break;
					}
				}
				else
				{
					$mailmetas['per_pass'] 	= $batch_send_config['per_pass'];
					$mailmetas['max_try']	= $batch_send_config['max_retry'] + 1;
					$mailmetas['try'] 	= 0;
					$mailmetas['pass'] 	= 0;

					$mailmetas['processed'] = 0;

					if (is_serialized ($mail->toemail))
					{
						$u = unserialize($mail->toemail);
						$mailmetas['count'] = count($u);
					}
					else
						$mailmetas['count'] = 1;

					$mailmetas['sent'] 	= 0;
					$mailmetas['failed'] 	= array();
					MP_Mailmeta::update( $mail->id, self::metakey, $mailmetas );
				}		
			}
		}
		if (!$done) self::schedule();
	}

	public static function update_mail($id, $failed)
	{
		global $wpdb;
				
		$query = "UPDATE $wpdb->mp_mails SET status = 'sent' WHERE id = $id";
		$x = $wpdb->query( $query );
		if (!$failed)
		{
			MailPress::require_class('Mailmeta');
			MP_Mailmeta::delete( $id , self::metakey);
		}
	}

	public static function select_mail()
	{
		global $wpdb;

		$send = $mail = $mailmetas = false;
		$current_mail = '';

		$status 			= self::status_mail();

		$query = "SELECT * FROM $wpdb->mp_mails WHERE status = '$status' ;";
		$mails = $wpdb->get_results($query);

		if ($mails)
		{
			foreach ($mails as $mail)
			{
				MailPress::require_class('Mailmeta');
				$mailmetas 		= MP_Mailmeta::get( $mail->id , self::metakey);

				if ($mailmetas['count'] == $mailmetas['sent']) continue;

				$send = true;

				if (empty($current_mail))
				{
					$current_mail 	= $mail;
					$current_mailmetas= $mailmetas;
				}

				if ($mailmetas['try'] < $current_mailmetas['try'])
				{
					$current_mail 	= $mail;
					$current_mailmetas= $mailmetas;
				}
			}
			$mail 	= $current_mail;
			$mailmetas	= $current_mailmetas;
		}
		return array('mail' => $mail, 'mailmetas' => $mailmetas, 'send' => $send);
	}

	public static function batch($mail, $mailmetas)
	{
		$rc = true;
// MP_Log
$trace = new MP_Log('mp_process_batch_send', ABSPATH . MP_PATH, __CLASS__, false, 'batch_send');

		$batch_report['header'] = 'Batch Report mail #' . $mail->id . '            count : ' . $mailmetas['count'] . '  per_pass : ' . $mailmetas['per_pass'] . '  max_try : ' . $mailmetas['max_try'];
		$batch_report['start']  = $mailmetas;

		$recipients = unserialize($mail->toemail);

		if ($mailmetas['sent'] != $mailmetas['count'])
		{
			if (0 == $mailmetas['try'])
			{
				$offset 	= $mailmetas['pass'] * $mailmetas['per_pass'];
				$length 	= $mailmetas['per_pass'];

				$toemail 	= array_slice($recipients, $offset, $length, true);
				$mailmetas['processed'] += count($toemail);
			}
			else
			{
				$offset 	= (isset($mailmetas['offset'])) ? $mailmetas['offset'] : 0;
				$length 	= $mailmetas['per_pass'];

				$j = 0;
				$i = 0;

				if (count($mailmetas['failed']) > 0)
				{
					foreach ($mailmetas['failed'] as $k => $v)
					{
						$i++;
						if ($i < $offset) continue;
						if ($j < $length)
						{
							$toemail[$k] = $recipients [$k];
							$j++;
						}
						else break;
					}
				}
				else	$toemail 	= array_slice($recipients, $offset, $length, true);

				$mailmetas['processed'] = $mailmetas['sent'] + $offset + count($toemail);
			}

			if (0 == count($toemail))
			{
				$mailmetas['try']++;
				$mailmetas['processed'] = 0;
				$mailmetas['pass'] = 0;
				$mailmetas['offset'] = 0;

				$batch_report['processing']  = array_merge($mailmetas, array('offset'=>$offset, 'length'=>$length, ">> WARNING >>" => 'No more recipient' ) );
			}
			else
			{
				$batch_report['processing']  = array_merge($mailmetas, array('offset'=>$offset, 'length'=>$length) );

// instaure the context
				MailPress::require_class('Mail');
				$_this = new MP_Mail(__CLASS__);

				$_this->trace 				= $trace;

				$_this->mail 				= (object) null;
				$_this->mail->swift_batchSend 	= true;
				$_this->mail->mailpress_batch_send 	= true;

				$_this->row 				= (object) null;
				$_this->row 				= $mail;
				$_this->args 				= (object) null;

				$_this->args->replacements 		= $toemail;
				$_this->get_old_recipients();

				MailPress::require_class('Mailmeta');
				$m = MP_Mailmeta::get($_this->row->id, '_MailPress_replacements');
				if (!is_array($m)) $m = array();
				$_this->mail->replacements = $m;


				$swiftfailed = $_this->swift_processing(); // will activate swift_send function


				$rc = true;
				if (!is_array($swiftfailed))
				{
					$rc = $swiftfailed;
					$swiftfailed = array();
				}

				if ($rc)
				{
					$failed = $successfull = array();
					$failed = array_flip($swiftfailed);
					$successfull = array_diff_key($toemail, $failed);

					foreach ($successfull as $k => $v) 
					{
						unset($mailmetas['failed'][$k]);
						$mailmetas['sent']++ ;
					}
					foreach ($failed as $k => $v) 
					{
						if (!isset($mailmetas['failed'][$k])) $mailmetas['failed'][$k] = null;
						if (0 != $mailmetas['try']) $mailmetas['offset']++;
					}
				}
			}
			$mailmetas['pass']++;
			if ($mailmetas['processed'] >= $mailmetas['count']) 
			{
				$mailmetas['try']++;
				$mailmetas['processed'] = 0;
				$mailmetas['pass'] = 0;
				$mailmetas['offset'] = 0;
			}
		}

		if ($mailmetas['sent'] == $mailmetas['count']) $mailmetas['try'] = $mailmetas['max_try'];

		MailPress::require_class('Mailmeta');
		MP_Mailmeta::update( $mail->id, self::metakey, $mailmetas );

		$batch_report['end']  = $mailmetas;
		self::batch_report($batch_report, $trace);
// MP_Log
$trace->end($rc);
	}

	public static function swift_send($_this)
	{
		if ($_this->mail->mailpress_batch_send)
		{
			$_this->mysql_disconnect('MailPress_batch_send');

			$_this->swift->registerPlugin(new Swift_Plugins_DecoratorPlugin($_this->row->replacements));
			if (!$_this->swift->batchSend($_this->message, $failures))
			{
				$_this->mysql_connect('MailPress_batch_send 2');
				return false;
			}
			$_this->mysql_connect('MailPress_batch_send');
			return $failures;
		}
		return true;
	}

	public static function alldone()
	{
// MP_Log
$trace = new MP_Log('mp_process_batch_send', ABSPATH . MP_PATH, __CLASS__, false, 'batch_send');
		$batch_report['header2'] = 'Batch Report';
		$batch_report['alldone']  = true;
		self::batch_report($batch_report, $trace);
// MP_Log
$trace->end(true);
	}

	public static function batch_report($batch_report, $trace, $zz = 12)
	{
		$order = array( 'sent', 'failed', 'processed', 'try', 'pass', 'offset', 'length');
		$unsets = array(  'count', 'per_pass', 'max_try' );
		$t = (count($order) + 1) * ($zz + 1) -1;

		foreach($batch_report as $k => $v)
		{
			switch ($k)
			{
				case 'header' :
					$trace->log('!' . str_repeat( '-', $t) . '!');
					$l = strlen($v);
					$trace->log('!' . str_repeat( ' ', 5) . $v . str_repeat( ' ', $t - 5 - $l) . '!');
					$trace->log('!' . str_repeat( '-', $t) . '!');
					$s = '!            !';
					foreach($order as $o)
					{
						$l = strlen($o);
						$s .= " $o" . str_repeat( ' ', $zz - $l -1) . '!';
					}
					$trace->log($s);
					$trace->log('!' . str_repeat( '-', $t) . '!');
				break;
				case 'header2' :
					$t = 103;
					$trace->log('!' . str_repeat( '-', $t) . '!');
					$l = strlen($v);
					$trace->log('!' . str_repeat( ' ', 5) . $v . str_repeat( ' ', $t - 5 - $l) . '!');
					$trace->log('!' . str_repeat( '-', $t) . '!');
				break;
				case 'alldone' :
					$t = 103;
					$v = ' *** all done ***       *** all done ***       *** all done *** '; 
					$l = strlen($v);
					$trace->log('!' . str_repeat( ' ', 10) . $v . str_repeat( ' ', $t -10 - $l) . '!');
					$trace->log('!' . str_repeat( '-', $t) . '!');
					$trace->log('!' . str_repeat( ' ', 15) . $v . str_repeat( ' ', $t -15 - $l) . '!');
					$trace->log('!' . str_repeat( '-', $t) . '!');
					$trace->log('!' . str_repeat( ' ', 20) . $v . str_repeat( ' ', $t -20 - $l) . '!');
				break;
				default :
						foreach ($unsets as $unset) unset($v[$unset]);
						$c = 0;
						$l = strlen($k);
						$s = "! $k" . str_repeat( ' ', $zz - $l -1) . '!';
						foreach($order as $o)
						{
							if (isset($v[$o])) { if (is_array($v[$o])) $v[$o] = count($v[$o]); $l = strlen($v[$o]); $s .= str_repeat( ' ', $zz - $l -1) . $v[$o] .  ' !'; unset($v[$o]); $c++;}
						}
						if ($c < count($order)) do { $s.= str_repeat( ' ', $zz) . '!'; $c++;} while($c <  count($order));
						$trace->log($s);
						if (!empty($v)) foreach($v as $a => $b) $trace->log("$a $b");
				break;
			}
		}
		$trace->log('!' . str_repeat( '-', $t) . '!');
	}

////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////

// for install
	public static function install() 
	{
		include ( MP_TMP . 'mp-admin/includes/install/batch_send.php');
	}

// for link on plugin page
	public static function plugin_action_links($links, $file)
	{
		return MailPress::plugin_links($links, $file, plugin_basename(__FILE__), 'MailPress_batch_send');
	}

// for settings
	public static function scripts($scripts, $screen) 
	{
		switch ($screen)
		{
			case MailPress_page_settings : 
				wp_register_script( 'mp-batchsend', 	'/' . MP_PATH . 'mp-admin/js/settings_batch_send.js', array(), false, 1);
				$scripts[] = 'mp-batchsend';
			break;
			case MailPress_page_mails :
				$batch_send_config = get_option('MailPress_batch_send');
				if ('wpcron' != $batch_send_config['batch_mode']) return $scripts;

				$checked = (isset($_GET['autorefresh'])) ?  " checked='checked'" : '';
				$time    = (isset($_GET['autorefresh'])) ?  $_GET['autorefresh'] : $batch_send_config['every'];
				$time    = (is_numeric($time) && ($time > $batch_send_config['every'])) ? $time : $batch_send_config['every'];
				$time    = "<input type='text' value='$time' maxlength='3' id='MP_Refresh_every' class='screen-per-page'/>";
				$option  = '<h5>' . __('Auto refresh for WP_Cron', 'MailPress') . '</h5>';
				$option .= "<div><input id='MP_Refresh' type='checkbox'$checked style='margin:0 5px 0 2px;' /><span class='MP_Refresh'>" . sprintf(__('%1$s Autorefresh %2$s every %3$s sec', 'MailPress'), "<label for='MP_Refresh' style='vertical-align:inherit;'>", '</label>', $time) . "</span></div>";


				wp_register_script( 'mp-refresh', 	'/' . MP_PATH . 'mp-includes/js/mp_refresh.js', array('schedule'), false, 1);
				wp_localize_script( 'mp-refresh', 	'adminMpRefreshL10n', array(
					'screen' 	=> $screen,
					'every' 	=> $batch_send_config['every'],

					'message' 	=> __('Autorefresh in %i% sec', 'MailPress'), 

					'option'	=> $option,
					'l10n_print_after' => 'try{convertEntities(adminmailsL10n);}catch(e){};'
				) );

				$scripts[] = 'mp-refresh';
			break;
		}
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

// for to mails column
	public static function screen_options($result, $screen)
	{
		if ($screen != MailPress_page_mails) return $result;

		$batch_send_config = get_option('MailPress_batch_send');

		if ('wpcron' != $batch_send_config['batch_mode']) return $result;

		$checked = (isset($_GET['autorefresh'])) ?  " checked='checked'" : '';
		$time    = (isset($_GET['autorefresh'])) ?  $_GET['autorefresh'] : $batch_send_config['every'];
		$time    = (is_numeric($time) && ($time > $batch_send_config['every'])) ? $time : $batch_send_config['every'];

		$time = "<input type='text' value='$time' maxlength='3' id='MailPress_batch_send_every' class='screen-per-page'/>";
		$result .= '<h5>' . __('Auto refresh for MailPress batch send', 'MailPress') . '</h5>';

		$result .= "<div><label for='MailPress_batch_send'><input id='MailPress_batch_send' type='checkbox'$checked style='margin:0 5px 0 2px;' /><span class='MailPress_batch_send'>" . sprintf(__('Autorefresh every %1$s sec', 'MailPress'), $time) . "</span></label></div>";
		return $result;
	}

	public static function to_mails_column($to, $mail)
	{
		if (self::status_mail() != $mail->status) return $to;

		MailPress::require_class('Mailmeta');
		$mailmetas = MP_Mailmeta::get( $mail->id , self::metakey);

		if (!$mailmetas) return __('Pending...', 'MailPress');

		return sprintf( __ngettext( _c('%1$s on %2$s sent| Singular', 'MailPress'), _c('%1$s on %2$s sent| Plural', 'MailPress'), $mailmetas['sent'] ), $mailmetas['sent'], $mailmetas['count'] );
	}
}

$MailPress_batch_send = new MailPress_batch_send();
}
?>