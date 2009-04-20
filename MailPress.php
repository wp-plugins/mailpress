<?php
/*
Plugin Name: MailPress
Plugin URI: http://www.mailpress.org
Description: The WordPress mailing platform. 
Author: Andre Renaut
Requires at least: 2.7
Tested up to: 2.7
Version: 3.0.1
Author URI: http://www.mailpress.org
*/

class MailPress
{
	function MailPress() {
		global $wpdb, $mp_general;
// for mysql
		$wpdb->mp_users     = $wpdb->prefix . 'MailPress_users';
		$wpdb->mp_stats     = $wpdb->prefix . 'MailPress_stats';
		$wpdb->mp_mails     = $wpdb->prefix . 'MailPress_mails';
		$wpdb->mp_usermeta  = $wpdb->prefix . 'MailPress_usermeta';
		$wpdb->mp_mailmeta  = $wpdb->prefix . 'MailPress_mailmeta';
// for gettext
		load_plugin_textdomain('MailPress', MP_PATH . 'mp-includes/languages');
// for contextual help
		define ('MailPress_help_url',	'http://www.mailpress.org');
// for admin plugin pages

		define ('MailPress_page_mails',     'mailpress_mails');
		define ('MailPress_page_write',     'mailpress_write');
		define ('MailPress_page_edit',      MailPress_page_mails . '&file=mail_new');
		define ('MailPress_page_revision',	MailPress_page_mails . '&file=revision');
		define ('MailPress_page_mail',      MailPress_page_mails . '&file=mail');

		define ('MailPress_page_design',	'mailpress_design');
		define ('MailPress_page_settings',	'mailpress_settings');
		define ('MailPress_page_users',     'mailpress_users');
		define ('MailPress_page_user',      MailPress_page_users . '&file=uzer');

		add_filter('plugin_action_links', 	array(&$this,plugin_action_links), 10, 2 );
		add_action('plugins_loaded', 		array(&$this,'init'));
		add_action('shutdown',              array(&$this,'shutdown'));
	}

////	init & shutdown   ////

	function init() {
		global $mp_general;

// for admin plugin urls

		$file = 'admin.php';

		define ('MailPress_write',          $file . '?page=' 	. MailPress_page_write);
		define ('MailPress_edit',           $file . '?page=' 	. MailPress_page_edit);
		define ('MailPress_mails',          $file . '?page=' 	. MailPress_page_mails);
		define ('MailPress_mail',           $file . '?page=' 	. MailPress_page_mail);
		define ('MailPress_revision',       $file . '?page=' 	. MailPress_page_revision);

		if (isset($mp_general['menu']))
		{
			define ('MailPress_users',      $file . '?page=' 	. MailPress_page_users);
			define ('MailPress_user',       $file . '?page=' 	. MailPress_page_user);
			define ('MailPress_design',     $file . '?page=' 	. MailPress_page_design);
			define ('MailPress_settings',   $file . '?page=' 	. MailPress_page_settings);
		}
		else					
		{
			$file = ( current_user_can('edit_users') ) ? 'users.php' : 'profile.php';
			define ('MailPress_users',      $file . '?page='		. MailPress_page_users);
			define ('MailPress_user',       $file . '?page='		. MailPress_page_user);
			define ('MailPress_design',     'themes.php?page=' 	. MailPress_page_design);
			define ('MailPress_settings',   'options-general.php?page=' . MailPress_page_settings);
		}

// for shortcode
		add_shortcode('mailpress', array(&$this,'shortcode'));
// for post
		add_action('delete_post', array('MailPress','delete_stats_c'));
// for newsletters
		if (function_exists('mp_register_newsletter'))
			MP_Newsletter::register();
// for dashboard
		if (current_user_can('MailPress_edit_dashboard') && isset($mp_general['dashboard']))
			add_filter('wp_dashboard_setup', array(&$this,'wp_dashboard_setup'));

		do_action('MailPress_init');
	}

	function shutdown() {
		flush();

		global $mp_general;
		if (function_exists('ignore_user_abort')) 	ignore_user_abort(1);
		if (function_exists('set_time_limit')) 		if( !ini_get('safe_mode') ) set_time_limit(0);
		$mp_general  = get_option('MailPress_general');
		if ($mp_general)
		{
			$now = date('Ymd');
			$lastnewsletter 	= (isset($mp_general['lastnewsletter'])) ? $mp_general['lastnewsletter'] 		: '00000000' ;
			if ($now != $lastnewsletter) 
			{
				if (!wp_next_scheduled( 'mp_build_newsletters' )) 
					wp_schedule_single_event(time()+30, 'mp_build_newsletters');
				$mp_general['lastnewsletter'] = $now;
				update_option ('MailPress_general', $mp_general);
			}
		}
	}

////	subscription form	////

	public static function form($args=false) {
		MP_Widget::form($args);
	}

//// shortcode ////

	function shortcode($options=false)
	{
		ob_start();
			self::form($options);
			$x = ob_get_contents();
		ob_end_clean();
		return $x; 
	}

////	dashboard	////

	function wp_dashboard_setup()
	{
		include (MP_TMP . '/mp-admin/includes/dashboard.php');
	}

//// settings ////

	function plugin_action_links($links, $file)
	{
		static $this_plugin;
		if( !$this_plugin ) $this_plugin = plugin_basename(__FILE__);

		if( $file == $this_plugin ){
			$settings_link = '<a href="' . MailPress_settings . '#fragment-1">' . __('Settings') . '</a>';
			array_unshift ($links, $settings_link);
		}
		return $links;
	}

////	email	////

	public static function is_email($email)
	{
		if(!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $email)) return false;
		return true;
	}

////	user	////

	public static function get_wp_user_id() {
		global $user_ID;
		if ( is_numeric($user_ID) ) return $user_ID;
		return 0;
	}

	public static function get_wp_user_email() {
		$email = '';
		switch (true)
		{
			case (isset($_POST['email'])) :
				$email = $_POST['email'];
			break;
			default :
				$u = self::get_wp_user_id();
				if ($u)
				{
					$user = get_userdata($u);
					$email = $user->user_email;
				}
				else
				{
					$email  = $_COOKIE['comment_author_email_' . COOKIEHASH];
				}
			break;
		}
		return $email;
	}

////	stats functions 	////

	public static function update_stats($type,$lib,$count) {
		global $wpdb;
		$now	  = date('Y-m-d');
		$query = "UPDATE $wpdb->mp_stats SET scount=scount+$count WHERE sdate = '$now' AND stype = '$type' AND slib = '$lib';";
		$results = $wpdb->query( $query );
		if (!$results)
		{
			$query = "INSERT INTO $wpdb->mp_stats (sdate, stype, slib, scount) VALUES ('$now','$type','$lib', $count);";
			$results = $wpdb->query( $query );
		}
	}

	function delete_stats_c($postid)
	{
		global $wpdb;
		$x = $wpdb->get_var("SELECT sum(scount) FROM $wpdb->mp_stats WHERE stype = 'c' AND slib = '$postid';");

		if ($x && ($x > 0)) self::update_stats('c',$postid,($x * -1));
	}

////	send subscription mail functions 	////

	public static function send_confirmation_subscription($email,$key) {
		global $mp_general;

		$url 		= get_bloginfo('siteurl');

		$args->Template 		= 'new_subscriber';
		$args->id			= MP_Mail::get_id('send_confirmation_subscription');

		$args->toemail 		= $email;
		$args->toname		= $email;
		$args->subscribe		= MP_User::get_subscribe_url($key);
		$args->viewhtml	 	= MP_User::get_view_url($key,$args->id);
		$args->mp_confkey		= $key;

		$args->subject		= sprintf( __('[%1$s] Waiting for %2$s','MailPress'), get_bloginfo('name'), $email );

		$message  = sprintf( __('Please, confirm your subscription to %1$s emails by clicking the following link :','MailPress'), get_bloginfo('name') );
		$message .= "\n\n";
		$message .= $args->subscribe;
		$message .= "\n\n";
		$message .= __('If you do not want to receive more emails, ignore this one !','MailPress');
		$message .= "\n\n";
		$args->plaintext   	= $message;

		$message  = sprintf( __('Please, confirm your subscription to %1$s emails by clicking the following link :','MailPress'), "<a href='$url'>" . get_bloginfo('name') . "</a>" );
		$message .= '<br /><br />';
		$message .= "<a href='" . $args->subscribe . "'>" . __('Confirm','MailPress') . "</a>";
		$message .= '<br /><br />';
		$message .= __('If you do not want to receive more emails, ignore this one !','MailPress');
		$message .= '<br /><br />';
		$args->html    		= $message;

		if (self::mail($args)) return true;

		MP_Mail::delete($args->id);
		return false;
	}

	public static function send_succesfull_subscription($email,$key) {
		global $mp_general;

		$url 		= get_bloginfo('siteurl');

		$args->Template 		= 'confirmed';
		$args->id			= MP_Mail::get_id('send_succesfull_subscription');

		$args->toemail 		= $email;
		$args->toname		= $email;
		$args->unsubscribe 	= MP_User::get_unsubscribe_url($key);
		$args->viewhtml	 	= MP_User::get_view_url($key,$args->id);
		$args->mp_confkey		= $key;

		$args->subject		= sprintf( __('[%1$s] Successful subscription for %2$s','MailPress'), get_bloginfo('name'), $email );

		$message  = sprintf(__('We confirm your subscription to %1$s emails','MailPress'), get_bloginfo('name') );
		$message .= "\n\n";
		$message .= __('Congratulations !','MailPress');
		$message .= "\n\n";
		$args->plaintext   	= $message;

		$message  = sprintf(__('We confirm your subscription to %1$s emails','MailPress'), "<a href='$url'>" . get_bloginfo('name') . "</a>" );
		$message .= '<br /><br />';
		$message .= __('Congratulations !','MailPress');
		$message .= '<br /><br />';
		$args->html    		= $message;

		if (self::mail($args)) return true;

		MP_Mail::delete($args->id);
		return false;
	}

////	comment functions 	////

	public static function approve_comment($id) {
		global $wpdb, $comment;

		$comment 		= $wpdb->get_row("SELECT * FROM $wpdb->comments WHERE comment_ID = $id LIMIT 1");
		if ('1' != $comment->comment_approved) return true;
		$rc = true;

		$args->Template	= 'comments';
		$args->id		= MP_Mail::get_id('approve_comment');

		$query = "SELECT c.email, c.confkey from $wpdb->comments a,  $wpdb->postmeta b, $wpdb->mp_users c WHERE a.comment_ID = $id AND a.comment_post_ID  = b.post_id AND b.meta_value = c.id AND b.meta_key = '_MailPress_subscribe_to_comments_' AND a.comment_author_email <> c.email" ;
		$args->replacements = MP_User::get_recipients($query,$args->id);

		if (array() != $args->replacements)
		{
			$args->toemail 	 = '{{toemail}}'; 
			$args->toname	 = '{{toemail}}'; 
			$args->unsubscribe = '{{unsubscribe}}';
			$args->viewhtml	 = '{{viewhtml}}';

			$args->subject	= sprintf( __('[%1$s] New Comment (%2$s)','MailPress'), get_bloginfo('name'),  $id);

			$args->content	= apply_filters('comment_text', get_comment_text() );

			$args->p->id	= $comment->comment_post_ID;
			$args->c->id   	= $id;

			if (self::mail($args)) 	return true;
			$rc = false;
		}

		MP_Mail::delete($args->id);
		return $rc;
	}

	public static function update_mp_user_comments($mp_user_id)
	{
		$comment_subs = MP_USER::get_comment_subs($mp_user_id);
		foreach ($comment_subs as $comment_sub)
		{
			if (isset($_POST['keep_comment_sub'][$comment_sub->meta_id])) continue;
			delete_post_meta($comment_sub->post_id, '_MailPress_subscribe_to_comments_', $mp_user_id);
			MailPress::update_stats('c',$comment_sub->post_id,-1);
		}
	}

	public static function checklist_mp_user_comments($mp_user_id)
	{
		$check_comments = false;

		$comment_subs = MP_USER::get_comment_subs($mp_user_id);
		foreach ($comment_subs as $comment_sub)
		{
			$check_comments .= "<input type='checkbox' name='keep_comment_sub[" . $comment_sub->meta_id . "]' checked='checked' />&nbsp;" . apply_filters('the_title', $comment_sub->post_title ) . "<br />\n";
		}
		return $check_comments;
	}

// // // // // // // // // // // // // 				THE MAIL

	public static function mail($args)
	{
		$x = new MP_Mail();

		return $x->send($args);
	}
}

$mp_general  = get_option('MailPress_general');

define ('MP_FOLDER', 	basename(dirname(__FILE__)));
define ('MP_PATH', 	'wp-content/plugins/' . MP_FOLDER . '/' );
define ('MP_TMP', 	dirname(__FILE__));

//  classes
require MP_TMP . '/mp-includes/class_loader.php';

// pluggable functions
do_action('MailPress_pluggable',(isset($mp_general['wp_mail'])) ? true : false);
if (isset($mp_general['wp_mail'])) 	include (MP_TMP . '/mp-includes/wp-pluggable.php');

$MailPress = new MailPress();
?>