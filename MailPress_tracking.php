<?php
if ( class_exists('MailPress') )
{
/*
Plugin Name: MailPress_tracking
Plugin URI: http://www.mailpress.org
Description: This is just an addon for MailPress to track the mails/users activity.
Author: Andre Renaut
Version: 4.0
Author URI: http://www.mailpress.org
*/

class MailPress_tracking
{
	function __construct()
	{
		define ('MailPress_tracking_openedmail', 	'_MailPress_mail_opened');

		global $wpdb;
// for mysql
		$wpdb->mp_tracks = $wpdb->prefix . 'mailpress_tracks';
// for plugin
		add_filter('MailPress_mail',				array('MailPress_tracking', 'mail'), 8, 1);
		add_action('mp_action_tracking', 			array('MailPress_tracking', 'tracking'), 8, 1);
// for referential integrity
		add_action('MailPress_delete_mail',  		array('MailPress_tracking', 'delete_mail'), 1, 1);
		add_action('MailPress_delete_user',  		array('MailPress_tracking', 'delete_user'), 1, 1);

		if (is_admin())
		{
		// for admin plugin pages
			define ('MailPress_page_tracking', MailPress_page_mails . '&file=tracking');
		// for admin plugin urls
			$file = 'admin.php';
			define ('MailPress_tracking', 	$file . '?page=' 	. MailPress_page_tracking);
		// install
			register_activation_hook(MP_FOLDER . '/MailPress_tracking.php', 	array('MailPress_tracking', 'install'));
		// for init
			add_action('init', 				array('MailPress_tracking', 'init'));
		// for link on plugin page
			add_filter('plugin_action_links', 		array('MailPress_tracking', 'plugin_action_links'), 10, 2 );
		// for role & capabilities
			add_filter('MailPress_capabilities',  	array('MailPress_tracking', 'capabilities'), 1, 1);
		// for settings
			add_action('MailPress_settings_update', 	array('MailPress_tracking', 'settings_update'));
			add_action('MailPress_settings_tab', 	array('MailPress_tracking', 'settings_tab'), 8, 1);
			add_action('MailPress_settings_div', 	array('MailPress_tracking', 'settings_div'));

			add_filter('MailPress_is_tracking',  	array('MailPress_tracking', 'is_tracking'), 1, 1);

		// for load admin page
			add_action('MailPress_load_admin_page', 	array('MailPress_tracking', 'load_admin_page'), 10, 1);
		}
	}

// for plugin
	public static function is_tracking($x)
	{
		return true;
	}


// we got one !
	public static function tracking($meta)
	{
		switch ($_GET['tg'])
		{
			case ('l') :
				self::save($meta);
			break;
			case ('o') :
				self::save($meta);
			break;
			default :
				$meta->meta_value = '404';
				self::save($meta);
			break;
		}
	}

// save tracking
	public static function save($meta)
	{
		global $wpdb;

		$now	  	= date('Y-m-d H:i:s');

		MailPress::require_class('Users');
		$mp_user_id = MP_Users::get_id($_GET['us']);

		$context 	= ('h' == $_GET['co']) ? 'html' : 'plaintext';

		$mail_id	= $meta->mail_id;
		$mmeta_id	= $meta->mmeta_id;
		$track	= mysql_real_escape_string($meta->meta_value);

		$ip		= mysql_real_escape_string(trim($_SERVER['REMOTE_ADDR']));
		$agent	= mysql_real_escape_string(trim($_SERVER['HTTP_USER_AGENT']));
		$referrer   = (isset($_SERVER['HTTP_REFERER'])) ? mysql_real_escape_string(trim($_SERVER['HTTP_REFERER'])) : '';

		$open_mmeta_id 	= (MailPress_tracking_openedmail == $meta->meta_value) ? $mmeta_id : self::get_mmid($mail_id, MailPress_tracking_openedmail, MailPress_tracking_openedmail);
		$query 		= "SELECT count(*) FROM $wpdb->mp_tracks WHERE user_id = $mp_user_id AND mail_id = $mail_id AND mmeta_id = $open_mmeta_id ;";
		$opened_mail	= $wpdb->get_var($query);

		if ((MailPress_tracking_openedmail == $meta->meta_value) && ($opened_mail)) return;

		$query = "INSERT INTO $wpdb->mp_tracks (user_id, mail_id, mmeta_id, track, context, ip, agent, referrer, tmstp) VALUES ($mp_user_id, $mail_id, $mmeta_id, '$track', '$context', '$ip', '$agent', '$referrer', '$now');";
		$wpdb->query( $query );

		if (MailPress_tracking_openedmail == $meta->meta_value) $opened_mail = true;
		if ($opened_mail) return;

		$query = "INSERT INTO $wpdb->mp_tracks (user_id, mail_id, mmeta_id, track, context, ip, agent, referrer, tmstp) VALUES ($mp_user_id, $mail_id, $open_mmeta_id, '" . MailPress_tracking_openedmail . "', '$context', '$ip', '$agent', '$referrer', '$now');";
		$wpdb->query( $query );
	}

// prepare mail for tracking
	public static function mail($mail)
	{
		foreach($mail->recipients as $k => $v)
		{
			$toemail = (MailPress::is_email($k)) ? $k : $v;
			if (isset($mail->replacements[$toemail]['{{_confkey}}']))
			{
				MailPress::require_class('Usermeta');
				MailPress::require_class('Users');
				MP_Usermeta::add(MP_Users::get_id_by_email($toemail), '_MailPress_mail_sent', $mail->id);
			}
		}

		$output = preg_match_all('/<a.+href=[\'""]([^\'""]+)[\'""].*>([^\'""]+)<\/a>/i', $mail->html, $matches, PREG_SET_ORDER);

		if ($matches)
		{
			foreach ($matches as $match)
			{
				if (strpos($match[1], 'mailto:') !== false) continue;
				$mmeta_id = self::get_mmid($mail->id, '_MailPress_mail_link', $match[1]);

				$search = $match[1];
				$replace = MP_Action_url . "?tg=l&mm=$mmeta_id";
				$subject = $match[0];
				$count = 1;
				$x = self::str_replace_count($search, $replace . '&co=h&us={{_confkey}}', $subject, $count);
				$mail->html 	= str_replace($subject, $x, 						$mail->html);
				$mail->plaintext 	= str_replace($search,  $replace . '&co=p&us={{_confkey}}', $mail->plaintext);
			}
			$mmeta_id = self::get_mmid($mail->id, MailPress_tracking_openedmail, MailPress_tracking_openedmail);
			$mail->html = str_ireplace('</body>', "\n<img src='" . MP_Action_url . "?tg=o&mm=$mmeta_id&co=h&us={{_confkey}}' alt='' style='margin:0;padding:0;border:none;' /></body>", $mail->html);
		}
		return $mail;
	}

	public static function str_replace_count($search, $replace, $subject, $times=1) 
	{
		$subject_original=$subject;

		$len=strlen($search);
		$pos=0;
		for ($i=1;$i<=$times;$i++) 
		{
			$pos=strpos($subject, $search, $pos);
			if($pos!==false) 
			{
				$subject=substr($subject_original, 0, $pos);
				$subject.=$replace;
				$subject.=substr($subject_original, $pos+$len);
				$subject_original=$subject;
			}
			else
			{
				break;
			}
		}
		return($subject);
	}

	public static function get_mmid($mail_id, $meta_key, $meta_value)
	{
		global $wpdb;
		$mmeta_id = $wpdb->get_var("SELECT mmeta_id FROM $wpdb->mp_mailmeta WHERE mail_id = $mail_id AND meta_key = '$meta_key' AND meta_value = '$meta_value';");
		if ($mmeta_id) return $mmeta_id;
		MailPress::require_class('Mailmeta');
		return MP_Mailmeta::add( $mail_id, $meta_key, $meta_value);
	}



////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////

// install
	public static function install() 
	{
		include ( MP_TMP . 'mp-admin/includes/install/tracking.php');
	}
	
// for init
	public static function init() 
	{
	// for mails list
		if ( current_user_can('MailPress_tracking_mails') )
		{
			add_filter('MailPress_columns_mails', 		array('MailPress_tracking', 'columns_mails'), 8, 1);
			add_action('MailPress_get_row_mails',  		array('MailPress_tracking', 'get_row_mails'), 1, 3);
		}
	// for user page
		if ( current_user_can('MailPress_tracking_users') )
			add_action('MailPress_add_meta_boxes_user',  	array('MailPress_tracking', 'meta_boxes_user'), 1, 2); 
	}

// for link on plugin page
	public static function plugin_action_links($links, $file)
	{
		return MailPress::plugin_links($links, $file, plugin_basename(__FILE__), 'MailPress_tracking');
	}

// for role & capabilities
	public static function capabilities($capabilities) 
	{
		$capabilities['MailPress_tracking_mails'] = array(	'name'  	=> __('View tracking', 'MailPress'), 
            									'group' 	=> 'mails', 
            									'menu'  	=> false
            								);
		$capabilities['MailPress_tracking_users'] = array(	'name'  	=> __('View tracking', 'MailPress'), 
            									'group' 	=> 'users', 
            									'menu'  	=> false
            								);
		return $capabilities;
	}

// for settings
	public static function settings_update()
	{
		include (MP_TMP . 'mp-admin/includes/settings/tracking.php');
	}

	public static function settings_tab($tab)
	{
		$t = ($tab=='MailPress_tracking') ? " class='ui-tabs-selected'" : ''; 
		echo "\t\t\t<li $t><a href='#fragment-MailPress_tracking'><span class='button-secondary'>" . __('Tracking', 'MailPress') . "</span></a></li>\n";
	}

	public static function settings_div()
	{
		include (MP_TMP . 'mp-admin/includes/settings/tracking.form.php');
	}


// for mails list
	//¤ for load admin page ¤//
	public static function load_admin_page($page)
	{
		if ($page != MailPress_page_tracking) return;
		include (MP_TMP . 'mp-admin/tracking.php');
	}

	public static function columns_mails($x)
	{
		$date = array_pop($x);
		$x['tracking_openrate']	=  __('Open rate', 'MailPress');
		$x['tracking_clicks']	=  __('Clicks', 'MailPress');
		$x['date']		= $date;
		return $x;
	}

	public static function get_row_mails($column_name, $mail, $url_parms)
	{
		global $wpdb;
		switch ($column_name)
		{
			case 'tracking_openrate' :
                if (MailPress::is_email($mail->toemail)) $total = 1;
                elseif(is_serialized($mail->toemail)) $total = count(unserialize($mail->toemail));
                else return;
				//$total = (MailPress::is_email($mail->toemail)) ? 1 : count(unserialize($mail->toemail));
				$query = "SELECT DISTINCT user_id FROM $wpdb->mp_tracks WHERE mail_id = " . $mail->id . " AND track = '" . MailPress_tracking_openedmail . "' ;";
				$result = $wpdb->get_results($query);
				if ($result) if ($total > 0) printf("%01.2f %%", 100 * count($result)/$total );
			break;
			case 'tracking_clicks' :
				$query = "SELECT count(*) FROM $wpdb->mp_tracks WHERE mail_id = " . $mail->id . " AND track <> '" . MailPress_tracking_openedmail . "' ;";
				$result = $wpdb->get_var($query);
				if ($result) echo "<div class='num post-com-count-wrapper'><a class='post-com-count'><span class='comment-count'>$result</span></a></div>";
			break;
		}
	}

// for user page
	public static function meta_boxes_user($mp_user_id, $mp_screen)
	{
		$tracking = get_option('MailPress_tracking');
		if (!is_array($tracking)) return;
		include (MP_TMP . 'mp-admin/includes/options/tracking/reports.php');
		foreach($tracking as $k => $v)
		{
			if (!isset($tracking_reports['user'][$k])) continue;
			add_meta_box($k . 'div', $tracking_reports['user'][$k]['title'] , "meta_box_tracking_mp_$k", $mp_screen, 'normal', 'low');
			include(MP_TMP . "mp-admin/includes/options/tracking/$k/$k.php");
		}
	}

// for referential integrity
	public static function delete_mail($mail_id)
	{
		global $wpdb;
		$query = "DELETE FROM $wpdb->mp_tracks WHERE mail_id = $mail_id; ";
		$wpdb->query($query);
		$query = "DELETE FROM $wpdb->mp_usermeta WHERE meta_key = '_MailPress_mail_sent' AND meta_value = $mail_id;";
		$wpdb->query($query);
	}

	public static function delete_user($mp_user_id)
	{
		global $wpdb;
		$query = "DELETE FROM $wpdb->mp_tracks WHERE user_id = $mp_user_id; ";
		$wpdb->query($query);
	}

// for reports
	public static function get_browser($useragent)
	{
		$file = MP_TMP . '/mp-admin/xml/browsers.xml';
		$x = file_get_contents($file);

		$br = __('Unknown','MailPress');

		if ($x)
		{
			$xml = new SimpleXMLElement($x);

			foreach ($xml->browser as $browser)
			{
    				if (preg_match($browser->pattern, $useragent, $regmatch))
				{
					$version = false;
					$br = '';
					if (isset($browser->version))
					{
						foreach($browser->version as $attrs) $vp = (int) $attrs['pattern'];
						if (!empty($browser->version))
						{
							preg_match($browser->version, $useragent, $regmatch);
							$version = $regmatch[$vp];
						}
						else
						{
							$version = $regmatch[$vp];
						}
					}
					$version = ($version) ? $browser->name . " $version" : $browser->name;
					if (isset($browser->icon) && !empty($browser->icon))
						$br .= "<img src='" . get_option('siteurl') . '/' . MP_PATH . 'mp-admin/images' . $browser->icon . "' alt='' />";
					if (isset($browser->link))
						$br .= "&nbsp<a href='" . $browser->link . "' title='" . $version . "' >" . $browser->name . '</a>';
					else
						$br .= '&nbsp;' . $version;
					break;
				}
			}
		}
		return $br;
	}

	public static function get_os($useragent)
	{
		$file = MP_TMP . '/mp-admin/xml/oss.xml';
		$x = file_get_contents($file);

		$se = __('Unknown','MailPress');

		if ($x)
		{
			$xml = new SimpleXMLElement($x);

			foreach ($xml->os as $os)
			{
    				if (preg_match($os->pattern, $useragent, $regmatch))
				{
					$version = false;
					$se = '';
					if (isset($os->versions))
					{
						foreach($os->versions as $attrs) $vp = (int) $attrs['pattern'];

						if (isset($os->versions->version))
						{
							foreach($os->versions->version as $ver)
							{
								if (preg_match($ver->pattern, $regmatch[$vp]))
								{
									$version = $ver->name;
									break;
								}
							}
						}
						else
						{
							$version =  $regmatch[$vp];
						}
					}
					$version = ($version) ? $os->name . " $version" : $os->name;
					if (isset($os->icon) && !empty($os->icon))
						$se .= "<img src='" . get_option('siteurl') . '/' . MP_PATH . 'mp-admin/images' . $os->icon . "' alt='' />";
					if (isset($se->link))
						$se .= "&nbsp<a href='" . $os->link . "' title='" . $version . "' >" . $os->name . '</a>';
					else
						$se .= '&nbsp;' . $version;
					break;
				}
			}
		}
		return $se;
	}

	public static function translate_track($track, $mail_id)
	{
		switch ($track)
		{
			case '{{subscribe}}' :
				return __('subscribe', 'MailPress');
			break;
			case '{{unsubscribe}}' :
				return __('unsubscribe', 'MailPress');
			break;
			case '{{viewhtml}}' :
				return __('view html', 'MailPress');
			break;
			case MailPress_tracking_openedmail :
				return __('mail opened', 'MailPress');
			break;
			default :
				MailPress::require_class('Users');
				$url = MP_Users::get_subscribe_url('#µ$&$µ#');
				$url = str_replace('#µ$&$µ#', '', $url);
				if (stripos($track, $url) !== false) {return __('subscribe', 'MailPress');}
				$url = MP_Users::get_unsubscribe_url('#µ$&$µ#');
				$url = str_replace('#µ$&$µ#', '', $url);
				if (stripos($track, $url) !== false) {return __('unsubscribe', 'MailPress');}
				$url = MP_Users::get_view_url('#µ$&$µ#', $mail_id);
				$url = str_replace('#µ$&$µ#&id=' . $mail_id, '', $url);
				if (stripos($track, $url) !== false) {return __('view html', 'MailPress');}
			break;
		}
		global $wpdb;
		$title = $wpdb->get_var("SELECT post_title FROM $wpdb->posts WHERE guid = '$track';");
		$title = $display_title = ($title) ? $title : $track;
		$display_title = (substr($display_title, 0, 7) == 'http://') ? substr($display_title, 7) : $display_title;
		$display_title = (substr($display_title, 0, 8) == 'https://') ? substr($display_title, 8) : $display_title;
		$display_title = (strlen($display_title) > 20) ? substr($display_title, 0, 18) . '...' : $display_title;
		return "<a href='$track' title='$title'>$display_title</a>";
	}


}

$MailPress_tracking = new MailPress_tracking();
}
?>