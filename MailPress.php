<?php
/*
Plugin Name: MailPress
Plugin URI: http://www.mailpress.org
Description: This is just a plugin, to manage mails, subscribers, and much more ... with style !
Author: Andre Renaut
Version: 1.9.3
Author URI: http://www.mailpress.org
*/

class MailPress
{
	function MailPress() {
		global $wpdb, $mp_general;
// for mysql
		$wpdb->mp_users	= $wpdb->prefix . 'MailPress_users';
		$wpdb->mp_stats	= $wpdb->prefix . 'MailPress_stats';
		$wpdb->mp_mails	= $wpdb->prefix . 'MailPress_mails';
		$wpdb->mp_usermeta= $wpdb->prefix . 'MailPress_usermeta';
		$wpdb->mp_mailmeta= $wpdb->prefix . 'MailPress_mailmeta';
// for gettext
		load_plugin_textdomain('MailPress', MP_PATH . 'mp-includes/languages');
// for plugin
		define ('MailPress_page_dashboard',	MP_FOLDER . '/mp-admin/dashboard.php');
		define ('MailPress_page_write',	MP_FOLDER . '/mp-admin/mail-new.php');
		define ('MailPress_page_revision',	MP_FOLDER . '/mp-admin/mail-new.php&file=revision');
		define ('MailPress_page_mails',	MP_FOLDER . '/mp-admin/mails.php');
		define ('MailPress_page_mail',	MP_FOLDER . '/mp-admin/mails.php&file=mail');
		define ('MailPress_page_design',	MP_FOLDER . '/mp-admin/themes.php');
		define ('MailPress_page_settings',	MP_FOLDER . '/mp-admin/settings.php');
		define ('MailPress_page_users',	MP_FOLDER . '/mp-admin/uzers.php');
		define ('MailPress_page_user',	MP_FOLDER . '/mp-admin/uzers.php&file=uzer');

		add_filter('plugin_action_links', 	array(&$this,plugin_action_links), 10, 2 );
		add_action('plugins_loaded', 		array(&$this,'init'));
		add_action('shutdown', 			array(&$this,'shutdown'));
	}

////	init & shutdown   ////

	function init() {
		global $mp_general;
// for urls

		if (isset($mp_general['menu']))
		{
			define ('MailPress_users',	'admin.php?page=' 	. MailPress_page_users);
			define ('MailPress_user',	'admin.php?page=' 	. MailPress_page_user);
			define ('MailPress_mails',	'admin.php?page=' 	. MailPress_page_mails);
			define ('MailPress_mail',	'admin.php?page=' 	. MailPress_page_mail);
			define ('MailPress_write',	'admin.php?page=' 	. MailPress_page_write);
			define ('MailPress_revision',	'admin.php?page=' 	. MailPress_page_revision);
			define ('MailPress_design',	'admin.php?page=' 	. MailPress_page_design);
			define ('MailPress_settings',	'admin.php?page=' 	. MailPress_page_settings);
		}
		else					
		{
			$users =  ( current_user_can('edit_users') ) ? 'users.php' : 'profile.php';

			define ('MailPress_users',	$users . '?page='		. MailPress_page_users);
			define ('MailPress_user',	$users . '?page='		. MailPress_page_user);

			define ('MailPress_mails',	'edit.php?page=' 		. MailPress_page_mails);
			define ('MailPress_mail',	'edit.php?page=' 		. MailPress_page_mail);
			define ('MailPress_write',	'post-new.php?page=' 	. MailPress_page_write);
			define ('MailPress_revision',	'post-new.php?page=' 	. MailPress_page_revision);

			define ('MailPress_design',	'themes.php?page=' 	. MailPress_page_design);
			define ('MailPress_settings',	'options-general.php?page=' . MailPress_page_settings);
		}
// for post
		if (isset($mp_general['newsletters']['new_post']))	
			add_action('publish_post', array('MP_Newsletter','have_post'), 8, 1);
		add_action( 'delete_post', array(&$this,'delete_stats_c'));
// for widget
		if ( function_exists('register_sidebar_widget') && function_exists('register_widget_control') )
		{
			register_sidebar_widget('MailPress', array(&$this,'widget'),'MailPress_widget');
			register_widget_control('MailPress', array(&$this,'widget_control'));
		}
// for newsletters
		if ( function_exists('mp_register_newsletter'))
			MailPress::newsletters();

		do_action('MailPress_init');
	}

	function shutdown() {
		global $mp_general;

		if ($mp_general)
		{
			$now = date('Ymd');
			$lastnewsletter 	= (isset($mp_general['lastnewsletter'])) ? $mp_general['lastnewsletter'] 		: '00000000' ;
		
			if ($now != $lastnewsletter) 
			{
				MP_Newsletter::process();
				$mp_general['lastnewsletter'] = $now;
				update_option ('MailPress_general', $mp_general);
			}
		}
	}

////	widget ////
	function widget($args) {
		extract($args);

		$options = get_option('MailPress_widget');
		$title = empty($options['title']) ? '' : apply_filters('widget_title', $options['title']);

		echo $before_widget;
		echo $before_title . $title . $after_title;
		MailPress::form($args); 
		echo $after_widget;
	}

	function widget_control() {
		$options = $newoptions = get_option('MailPress_widget');
		if ( $_POST["MailPress-submit"] ) {
			$newoptions['title'] = strip_tags(stripslashes($_POST["MailPress-title"]));
			$newoptions['jQ']    = $_POST["MailPress-jQ"];
			$newoptions['urlsubmgt']    = $_POST["MailPress-urlsubmgt"];
			if ( empty($newoptions['title']) ) $newoptions['title'] = '';
			if ( empty($newoptions['jQ']) )    $newoptions['jQ'] = false;
			if ( empty($newoptions['urlsubmgt']) )    $newoptions['urlsubmgt'] = false;
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			if (!update_option('MailPress_widget', $options)) add_option('MailPress_widget', $options);
		}
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		$jQ   =  $options['jQ'];
		$urlsubmgt   =  $options['urlsubmgt'];
?>
<p>
	<label for="MailPress-title">
		<?php _e('Title:'); ?> 
		<input style="width: 250px;" id="MailPress-title" name="MailPress-title" type="text" value="<?php echo $title; ?>" />
	</label>
	<br/>
	<label for="MailPress-title">
		<input id="MailPress-jQ" name="MailPress-jQ" <?php checked($jQ,true); ?> type="checkbox"> <?php _e('jQuery already loaded','MailPress'); ?> 
	</label>
	<br/>
	<label for="MailPress-title">
		<input id="MailPress-urlsubmgt" name="MailPress-urlsubmgt" <?php checked($urlsubmgt,true); ?> type="checkbox"> <?php _e("\"Manage your subscription\" link ?",'MailPress'); ?> 
	</label>
</p>
<input type="hidden" id="MailPress-submit" name="MailPress-submit" value="1" />
<?php
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

////  newsletters ////
	function newsletters() {

		mp_register_newsletter (	'new_post',
							'',
							'single',
							__("Per post",'MailPress'),
							__("For each new post",'MailPress')
					     );

		mp_register_newsletter (	'daily',
							sprintf( __('[%1$s] Daily newsletter','MailPress'), get_bloginfo('name')),
							'daily',
							__("Daily",'MailPress'),
							__('Daily newsletter','MailPress'),
							array ( 	'callback'	 => array('MP_Newsletter', 'have'),
									'name'	 => 'MailPress_daily',
									'value'	 => date('Ymd'),
									'query_posts' => 'm=' . date('Ymd',mktime(0,0,0,date('m'),date('d') - 1, date('Y')))
								)
					     );

		$w  = MP_Newsletter::get_yearweekofday(date('Y-m-d',mktime(10,0,0,date('m'),date('d') - 7, date('Y'))));
		mp_register_newsletter (	'weekly',
							sprintf( __('[%1$s] Weekly newsletter','MailPress'), get_bloginfo('name')),
							'weekly',
							__("Weekly",'MailPress'),
							__('Weekly newsletter','MailPress'),
							array ( 	'callback'	 => array('MP_Newsletter', 'have'),
									'name'	 => 'MailPress_weekly',
									'value'	 => MP_Newsletter::get_yearweekofday(date('Y-m-d')),
									'query_posts' => 'w=' . substr($w,4,2) . '&year=' . substr($w,0,4)
								)
					     );

		$y  = date('Y'); $m = date('m') - 1; if (0 == $m) { $m = 12; $y--;} if (10 > $m) $m = '0' . $m;
		mp_register_newsletter (	'monthly',
							sprintf( __('[%1$s] Monthly newsletter','MailPress'), get_bloginfo('name')),
							'monthly',
							__("Monthly",'MailPress'),
							__('Monthly newsletter','MailPress'),
							array ( 	'callback'	 => array('MP_Newsletter', 'have'),
									'name'	 => 'MailPress_monthly',
									'value'	 => date('Ym'),
									'query_posts' => 'm=' . $y . $m
								)
					     );
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
				$u = MailPress::get_wp_user_id();
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

////	subscription form	////

	public static function form($args=null) {
		MP_User::form($args);
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

		if ($x && ($x > 0)) MailPress::update_stats('c',$postid,($x * -1));
	}

////	send subscription mail functions 	////

	function send_confirmation_subscription($email,$key) {
		global $mp_general;

		$url 		= get_bloginfo('siteurl');

		$args->Template 		= 'new_subscriber';
		$args->id			= MP_Mail::get_id();

		$args->toemail 		= $email;
		$args->toname		= $email;
		$args->subscribe	 	= MP_User::get_subscribe_url($key);
		$args->viewhtml	 	= MP_User::get_view_url($key,$args->id);

		$args->subject		= sprintf( __('[%1$s] Waiting for %2$s','MailPress'), get_bloginfo('name'), $email );

		$message  = sprintf( __('Please, confirm your subscription to %1$s emails by clicking the following link :','MailPress'), get_bloginfo('name') );
		$message .= "\n\n";
		$message .= $args->subscribe;
		$message .= "\n\n";
		$message .= __('If you do not want to receive more emails, ignore this one !','MailPress');
		$message .= "\n\n";
		$args->plaintext   	= $message;

		$message  = sprintf( __('Please, confirm your subscription to %1$s emails by clicking the following link :','MailPress'), "<a href='$args->subscribe'>" . get_bloginfo('name') . "</a>" );
		$message .= '<br/><br/>';
		$message .= "<a href='$url'>" . __('Confirm','MailPress') . "</a>";
		$message .= '<br/><br/>';
		$message .= __('If you do not want to receive more emails, ignore this one !','MailPress');
		$message .= '<br/><br/>';
		$args->html    		= $message;

		if (MailPress::mail($args)) return true;

		MP_Mail::delete($args->id);
		return false;
	}

	function send_succesfull_subscription($email,$key) {
		global $mp_general;

		$url 		= get_bloginfo('siteurl');

		$args->Template 		= 'confirmed';
		$args->id			= MP_Mail::get_id();

		$args->toemail 		= $email;
		$args->toname		= $email;
		$args->unsubscribe 	= MP_User::get_unsubscribe_url($key);
		$args->viewhtml	 	= MP_User::get_view_url($key,$args->id);

		$args->subject		= sprintf( __('[%1$s] Successful subscription for %2$s','MailPress'), get_bloginfo('name'), $email );

		$message  = sprintf(__('We confirm your subscription to %1$s emails','MailPress'), get_bloginfo('name') );
		$message .= "\n\n";
		$message .= __('Congratulations !','MailPress');
		$message .= "\n\n";
		$args->plaintext   	= $message;

		$message  = sprintf(__('We confirm your subscription to %1$s emails','MailPress'), "<a href='$url'>" . get_bloginfo('name') . "</a>" );
		$message .= '<br/><br/>';
		$message .= __('Congratulations !','MailPress');
		$message .= '<br/><br/>';
		$args->html    		= $message;

		if (MailPress::mail($args)) return true;

		MP_Mail::delete($args->id);
		return false;
	}

////	comment functions 	////

	function approve_comment($id) {
		global $wpdb, $comment;

		$comment 		= $wpdb->get_row("SELECT * FROM $wpdb->comments WHERE comment_ID = $id LIMIT 1");
		if ('1' != $comment->comment_approved) return true;
		$rc = true;

		$args->Template 	= 'comments';
		$args->id 		= MP_Mail::get_id();

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

			if (MailPress::mail($args)) 	return true;
			$rc = false;
		}

		MP_Mail::delete($args->id);
		return $rc;
	}

	function update_mp_user_comments($mp_user_id)
	{
		$comment_subs = MP_USER::get_comment_subs($mp_user_id);
		foreach ($comment_subs as $comment_sub)
		{
			if (isset($_POST['keep_comment_sub'][$comment_sub->meta_id])) continue;
			delete_post_meta($comment_sub->post_id, '_MailPress_subscribe_to_comments_', $mp_user_id);
			MailPress::update_stats('c',$comment_sub->post_id,-1);
		}
	}

	function checklist_mp_user_comments($mp_user_id)
	{
		$check_comments = false;

		$comment_subs = MP_USER::get_comment_subs($mp_user_id);
		foreach ($comment_subs as $comment_sub)
		{
			$check_comments .= "<input type='checkbox' name='keep_comment_sub[" . $comment_sub->meta_id . "]' checked='checked' />&nbsp;" . apply_filters( 'the_title', $comment_sub->post_title ) . "<br/>\n";
		}
		return $check_comments;
	}

// // // // // // // // // // // // // 				THE MAIL

	function mail($args)
	{
		$x = new MP_Mail();

		return $x->send($args);
	}
}

$mp_general  = get_option('MailPress_general');
$smtp_config = get_option('MailPress_smtp_config');

define ('MP_FOLDER', 	basename(dirname(__FILE__)));
define ('MP_PATH', 	'wp-content/plugins/' . MP_FOLDER . '/' );
define ('MP_TMP', 	dirname(__FILE__));

// for swift
require MP_TMP . "/mp-includes/class/swift/Swift.php";

//  classes and misc
if (!class_exists('MP_Mail')) 	include (MP_TMP . '/mp-includes/class/MP_Mail.class.php');
if (!class_exists('MP_User')) 	include (MP_TMP . '/mp-includes/class/MP_User.class.php');
if (!class_exists('MP_Themes')) 	include (MP_TMP . '/mp-includes/class/MP_Themes.class.php');
if (!class_exists('MP_Log')) 		include (MP_TMP . '/mp-includes/class/MP_Log.class.php');
if (!class_exists('MP_Admin')) 	include (MP_TMP . '/mp-includes/class/MP_Admin.class.php');
if (!class_exists('MP_Newsletter')) include (MP_TMP . '/mp-includes/class/MP_Newsletter.class.php');

do_action('MailPress_pluggable',(isset($mp_general['wp_mail'])) ? true : false);

// pluggable functions
if (isset($mp_general['wp_mail'])) 	include (MP_TMP . '/mp-includes/wp-pluggable.php');

$MailPress = new MailPress();
?>