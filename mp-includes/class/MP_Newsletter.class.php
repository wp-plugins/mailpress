<?php
//
//
// mp_register_newsletter
//
//		id 			: string, try to make it unique
//		mp_subject 		: mail subject
//		mp_template 	: MailPress template for this newsletter (if you create it do not forget plaintext)
//		desc 			: description of newsletter displayed under Admin
//		display 		: description of newsletter displayed under blog or false (mailpress user cannot subscribe/unsubscribe)
//
//		threshold		: array	callback  : function that should return true or false if the newsletter is to be sent.
//							name : name of the threshold (usually stored in options) where is store the last threshold value processed
//							value: current value
//							query_posts : the value of the query_posts if any newsletter to be sent
//
//		in 			: default = false, means all active mailpress users will receive this newsletter by default.
//							true,  means (if 'display' is true) the active mailpress users have to agree to receive this newsletter 
//					  		if 'display' is true(through their individual subscription management panel)
//		args			: any args you should need
//		
//	If 'display' parm is false, 'in' parm should be false or the newsletter will always have an empty recipient list
//			unless you do not modify the mp_usermeta mysql table manually ...
//
//
function mp_register_newsletter($id, $mp_subject, $mp_template, $desc, $display = true, $threshold = false, $in = false, $args = false) {

	global $mp_registered_newsletters;

	$id = strtolower($id);

	if (empty($id))
	{
		unset($mp_registered_newsletters[$id]);
		return;
	}

	if ($threshold && $threshold['callback'] && !is_callable($threshold['callback']))
		return;

	$newsletter = array(	'id' 		=> $id,
					'desc' 	=> $desc,
					'template'	=> $mp_template,
					'subject'	=> $mp_subject,
					'threshold'	=> $threshold,
					'display'	=> $display,
					'in'		=> $in
				  );
	if ($args) $newsletter['params'] = $args;

	$mp_registered_newsletters[$id] = $newsletter;
}

// for post published
add_action('publish_post', 					array('MP_Newsletter','have_post'), 8, 1);
//for mailpress shutdown
add_action('mp_build_newsletters',				array('MP_Newsletter','process'));

// for shortcode
add_filter('MailPress_form_defaults',			array('MP_Newsletter','form_defaults'),8,1);
add_filter('MailPress_form_options',			array('MP_Newsletter','form_options'),8,1);
add_filter('MailPress_form_submit',  			array('MP_Newsletter','form_submit'),8,2);
add_action('MailPress_form',		  			array('MP_Newsletter','form'),1,2); 

class MP_Newsletter
{
// for newsletters
	function register()
	{
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
     							array ( 	'callback'      => array('MP_Newsletter', 'have'),
     									'name'          => 'MailPress_daily',
     									'value'         => date('Ymd'),
     									'query_posts'   => array(
														'm'	=> date('Ymd',mktime(0,0,0,date('m'),date('d') - 1, date('Y')))
													)
     								)
        				     );

		$w  = self::get_yearweekofday(date('Y-m-d',mktime(10,0,0,date('m'),date('d') - 7, date('Y'))));
		mp_register_newsletter (	'weekly',
     							sprintf( __('[%1$s] Weekly newsletter','MailPress'), get_bloginfo('name')),
     							'weekly',
     							__("Weekly",'MailPress'),
     							__('Weekly newsletter','MailPress'),
     							array ( 	'callback'      => array('MP_Newsletter', 'have'),
     									'name'          => 'MailPress_weekly',
     									'value'         => self::get_yearweekofday(date('Y-m-d')),
     									'query_posts'   => array (
														'w'	=> substr($w,4,2),
														'year'=> substr($w,0,4)
													)
           							)
    					     );

		$y  = date('Y'); $m = date('m') - 1; if (0 == $m) { $m = 12; $y--;} if (10 > $m) $m = '0' . $m;
		mp_register_newsletter (	'monthly',
	                                    sprintf( __('[%1$s] Monthly newsletter','MailPress'), get_bloginfo('name')),
      						'monthly',
      						__("Monthly",'MailPress'),
      						__('Monthly newsletter','MailPress'),
      						array ( 	'callback'      => array('MP_Newsletter', 'have'),
      								'name'          => 'MailPress_monthly',
      								'value'         => date('Ym'),
      								'query_posts'   => array (
														'm'	=> $y . $m
													)
      							)
					     );

		do_action('MailPress_register_newsletter');

		global $mp_general;
		if (isset($mp_general['default_newsletters']))
		{
			global $mp_registered_newsletters;
			foreach ($mp_registered_newsletters as $k => $v)
			{
				$mp_registered_newsletters[$k]['in'] = (isset($mp_general['default_newsletters'][$k])) ? false : true; 
			}
		}
	}

	function get_defaults()
	{
		global $mp_registered_newsletters;
		$x = array();
		foreach($mp_registered_newsletters as $mp_registered_newsletter) if (!$mp_registered_newsletter['in']) $x[$mp_registered_newsletter['id']] = $mp_registered_newsletter['id'];
		return $x;
	}

// for shortcode
	function form_defaults($x) { $x['newsletter'] = false; return $x; }
	function form_options($x)  { return $x; }
	function form_submit($shortcode_message, $email)  
	{ 
		if (!isset($_POST['newsletter']))  return $shortcode_message;

		$shortcode = 'shortcode';
		$mp_user_id = MP_User::get_user_id_by_email($email);
		$newsletter_id = $_POST['newsletter'];

		$_POST[$shortcode] = self::get_mp_user_newsletters($mp_user_id);
		$_POST[$shortcode][$newsletter_id] = true;

		self::update_mp_user_newsletters($mp_user_id, $shortcode);

		return $shortcode_message . __('<br />Newsletter added','MailPress');
	}
	function form($email,$options)  
	{
		if (!$options['newsletter']) return;
		global $mp_registered_newsletters;
		if (!isset($mp_registered_newsletters[$options['newsletter']])) return;
		echo "<input type='hidden' name='newsletter' value='" . $options['newsletter'] . "' />\n";
	}

	public static function process() {
		global $mp_registered_newsletters;

		if (function_exists('ignore_user_abort'))	ignore_user_abort(1);
		if (function_exists('set_time_limit'))	if( !ini_get('safe_mode') ) set_time_limit(0);

		$newsletters= self::get_active();
		foreach ($newsletters as $k => $v)
		{
			$newsletter = $mp_registered_newsletters[$k];
			if ($newsletter['threshold'])
			{
				$args = array();
				array_unshift($args,$newsletter);
				if ($newsletter = call_user_func_array ($newsletter['threshold']['callback'],$args)) 
				{
					self::send($newsletter);
				}
			}
		}
	}

	public static function post_limits($limits) {
		global $mp_general;

		if (isset($mp_general['post_limits']) && ($mp_general['post_limits'])) return 'LIMIT 0, ' . $mp_general['post_limits'];

		return $limits;
	}

	public static function send($newsletter,$qp=true) {

		if (!isset($newsletter['query_posts'])) return;

		$mail->Template 	= $newsletter['template'];
		$mail->toemail 	= '{{toemail}}'; 
		$mail->toname	= '{{toemail}}'; 
		$mail->unsubscribe= '{{unsubscribe}}';
		$mail->viewhtml	= '{{viewhtml}}';
		$mail->subject	= $newsletter['subject'];
		$mail->newsletter = $newsletter;

		$in 			= ($newsletter['in']) ? '' : 'NOT';
		$query 		= MP_User::get_newsletters_query($newsletter['id'],$in);
		$mail->id 	= $id	= MP_Mail::get_id('self::send');
		$mail->replacements = MP_User::get_recipients($query,$mail->id);

		if (array() == $mail->replacements)
		{
			MP_Mail::delete($id);
			return;
		}

		$mail = apply_filters('MailPress_send_newsletter',$mail);

		add_filter('post_limits', array('MP_Newsletter','post_limits'),8,1);

		if ($qp)
		{
			query_posts($newsletter['query_posts']);
				while (have_posts()) { $qp = false; break; }	
			wp_reset_query();

		}
		if (!$qp)
		{
			query_posts($newsletter['query_posts']);
				if (!MailPress::mail($mail)) MP_Mail::delete($id);
			wp_reset_query();
		}

		remove_filter( 'post_limits', array('MP_Newsletter','post_limits'),8,1);
	}

	public static function have_post($post_id) {
		if (get_post_meta($post_id, '_MailPress_prior_to_install')) return true;

		global $mp_registered_newsletters, $mp_general;

		$post_meta = '_MailPress_published';
		if (get_post_meta($post_id, $post_meta, true)) return true;
		add_post_meta($post_id, $post_meta, 'yes', true);

		if (!isset($mp_general['newsletters']['new_post'])) return true;

		$newsletter 			= $mp_registered_newsletters['new_post'];
		$newsletter['subject'] 		= sprintf( __('[%1$s] New post (%2$s)','MailPress'), get_bloginfo('name'),  $post_id );
		$newsletter['query_posts'] 	= array( 'p'	=>	$post_id );

		$post = &get_post($post_id);
		$newsletter['the_title'] =  apply_filters('the_title', $post->post_title );

		self::send($newsletter,false);
	}

	public static function have($newsletter) {
		$y = get_option($newsletter['threshold']['name']);

		if ($y && ($newsletter['threshold']['value'] <= $y['threshold']))	return false;

		$y['threshold'] = $newsletter['threshold']['value'];
		update_option($newsletter['threshold']['name'],$y);

		$newsletter['query_posts'] = $newsletter['threshold']['query_posts'];

		return $newsletter;
	}

	public static function get_yearweekofday($date)
	{
		global $wpdb;

		$w = (int) $wpdb->get_var("SELECT WEEK('" . $date . "',1)");
		if (10 > $w) $w = '0' . $w;
		return substr($date,0,4) . $w;
	}

	public static function get() {

		global $mp_registered_newsletters;

		$x = array();
		if (is_array($mp_registered_newsletters))
		{
			foreach ($mp_registered_newsletters as $y)
			{
				$x[$y['id']] = $y['desc'];
			}
		}
		return $x;
	}

	public static function get_active() {

		global $mp_general;

		$x = self::get();

		foreach ($x as $k => $v) if (!isset($mp_general['newsletters'][$k]))		unset($x[$k]);

		return $x;
	}

	public static function get_default_newsletters() {

		global $mp_general;

		$x = self::get_active();

		foreach ($x as $k => $v) if (!isset($mp_general['default_newsletters'][$k])) 	unset($x[$k]);

		return $x;
	}

	public static function get_mp_user_newsletters($mp_user_id = false) {

		global $mp_registered_newsletters;

		$x = self::get_active();
		$a = ($mp_user_id) ? MP_Usermeta::get($mp_user_id,'_MailPress_newsletter') : '';

		if (is_array($a)) foreach($a as $b) $y[$b]='';			// array_flip($y);
		else			$y[$a] = '';

		foreach ($x as $k => $v)
		{
			if ($mp_registered_newsletters[$k]['in'])
			{
				if (!isset($y[$k])) unset($x[$k]);
			}
			else
			{
				if (isset($y[$k]))  unset($x[$k]);
			}
		}
		return $x;
	}

	public static function checklist_mp_user_newsletters($mp_user_id = false, $args = '') {

		$defaults = array (	'name' 	=> 'keep_newsletters',
						'admin' 	=> 0,
						'selected' 	=> false,
						'type'	=> 'checkbox',
						'show_option_all' => false,
						'htmlmiddle'=> '&nbsp;&nbsp;',
						'htmlend'	=> "<br />\n"
					);
		$r = wp_parse_args( $args, $defaults );
		extract( $r );

		global $mp_registered_newsletters;
		$checklist = false;

		$x = self::get_active();

		if ($mp_user_id) 	$in = self::get_mp_user_newsletters($mp_user_id);
		else			$in = self::get_default_newsletters();

		$lib_nl = ($admin) ? 'desc' : 'display';
		foreach ($x as $k => $v)
		{
			$checked = (isset($in[$k])) ? " checked='checked'" : '';
			if ($mp_registered_newsletters[$k][$lib_nl])
				switch ($type)
				{
					case 'checkbox' :
						$checklist .= "$htmlstart<input type='checkbox' name='" . $name . "[$k]'$checked />$htmlmiddle" . $mp_registered_newsletters[$k][$lib_nl] . $htmlend;
					break;
					case 'select' :
						if (empty($checklist)) $checklist = "\n" . $htmlstart . "\n<select name='" . $name . "'>\n";
						if ($show_option_all)
						{
							$checklist .= "<option value=''>" . $show_option_all . "</option>\n";
							$show_option_all = false;
						}
						$sel = ($k == $selected) ? " selected='selected'" : '';
						$checklist .= "<option value=\"$k\"$sel>" . $mp_registered_newsletters[$k][$lib_nl]  . "</option>\n";
					break;
				}
		}
		if ('select' == $type) $checklist .= "</select>\n" . $htmlend . "\n";

		return $checklist;
	}

	public static function update_mp_user_newsletters($mp_user_id, $name = 'keep_newsletters') {

		global $mp_registered_newsletters;

		$x = self::get_active();
		MP_Usermeta::delete($mp_user_id,'_MailPress_newsletter');

		foreach ($x as $k => $v) 
		{
			if ($mp_registered_newsletters[$k]['in']) 
			{
				if (isset($_POST[$name][$k])) MP_Usermeta::add($mp_user_id,'_MailPress_newsletter',$k);
			}
			else
			{
				if (!isset($_POST[$name][$k])) MP_Usermeta::add($mp_user_id,'_MailPress_newsletter',$k);
			}
		}
	}

	public static function reverse_subscriptions($id) {

		global $wpdb;

		$meta_key = '_MailPress_newsletter';
		$not_in   = '';

		$query = "SELECT user_id AS id FROM $wpdb->mp_usermeta WHERE meta_key = '$meta_key' AND meta_value = '$id'";
		$to_be_reversed = $wpdb->get_results( $query );

		if ($to_be_reversed)
		{
			foreach($to_be_reversed as $mp_user)
			{
				if (!empty($not_in)) $not_in .= ',';
				$not_in .= $mp_user->id;
			}
		}
		if (!empty($not_in)) $not_in = 'WHERE id NOT IN (' . $not_in . ')';

		$query = "DELETE FROM  $wpdb->mp_usermeta WHERE meta_key = '$meta_key' AND meta_value = '$id'";
		$wpdb->query($query);

		$query = "INSERT INTO $wpdb->mp_usermeta (user_id, meta_key, meta_value) SELECT id, '$meta_key', '$id' FROM $wpdb->mp_users $not_in;";
		$wpdb->query($query);
	}
}
?>