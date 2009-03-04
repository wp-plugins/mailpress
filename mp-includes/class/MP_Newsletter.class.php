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
	if ( isset($mp_registered_newsletters[$id]) )
		return;

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

// for shortcode
add_filter('MailPress_form_defaults',			array('MP_Newsletter','form_defaults'),8,1);
add_filter('MailPress_form_options',			array('MP_Newsletter','form_options'),8,1);
add_filter('MailPress_form_submit',  			array('MP_Newsletter','form_submit'),8,2);
add_action('MailPress_form',		  			array('MP_Newsletter','form'),1,2); 

class MP_Newsletter
{

// for shortcode
	function form_defaults($x) { $x['newsletter'] = false; return $x; }
	function form_options($x)  { return $x; }
	function form_submit($shortcode_message, $email)  
	{ 
		if (!isset($_POST['newsletter']))  return $shortcode_message;

		$shortcode = 'shortcode';
		$mp_user_id = MP_User::get_user_id_by_email($email);
		$newsletter_id = $_POST['newsletter'];

		$_POST[$shortcode] = MP_Newsletter::get_mp_user_newsletters($mp_user_id);
		$_POST[$shortcode][$newsletter_id] = true;

		MP_Newsletter::update_mp_user_newsletters($mp_user_id, $shortcode);

		return $shortcode_message . __('<br/>Newsletter added','MailPress');
	}
	function form($email,$options)  
	{
		if (!$options['newsletter']) return;
		global $mp_registered_newsletters;
		if (!isset($mp_registered_newsletters[$options['newsletter']])) return;
		echo "<input type='hidden' name='newsletter' value='" . $options['newsletter'] . "'/>\n";
	}

	public static function process() {
		global $mp_registered_newsletters;
		$newsletters= MP_Newsletter::get_active();
		foreach ($newsletters as $k => $v)
		{
			$newsletter = $mp_registered_newsletters[$k];
			if ($newsletter['threshold'])
			{
				$args = array();
				array_unshift($args,$newsletter);
				if ($newsletter = call_user_func_array ($newsletter['threshold']['callback'],$args)) 
				{
					MP_Newsletter::send($newsletter);
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
		$mail->id 	= $id	= MP_Mail::get_id();
		$mail->replacements = MP_User::get_recipients($query,$mail->id);

		if (array() == $mail->replacements) return;

		$mail = apply_filters('MailPress_send_newsletter',$mail);

		add_filter( 'post_limits', array('MP_Newsletter','post_limits'),8,1);

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
		global $mp_registered_newsletters;

		$post_meta = '_MailPress_published';

		if (get_post_meta($post_id, $post_meta, true)) return true;
		add_post_meta($post_id, $post_meta, 'yes', true);

		$newsletter 			= $mp_registered_newsletters['new_post'];
		$newsletter['subject'] 		= sprintf( __('[%1$s] New post (%2$s)','MailPress'), get_bloginfo('name'),  $post_id );
		$newsletter['query_posts'] 	= "p=$post_id";

		$post = &get_post($post_id);
		$newsletter['the_title'] =  apply_filters( 'the_title', $post->post_title );

		MP_Newsletter::send($newsletter,false);
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

		$x = MP_Newsletter::get();

		foreach ($x as $k => $v) if (!isset($mp_general['newsletters'][$k]))	unset($x[$k]);

		return $x;
	}

	public static function get_mp_user_newsletters($mp_user_id = false) {

		global $mp_registered_newsletters;

		$x = MP_Newsletter::get_active();
		$a = ($mp_user_id) ? MP_User::get_meta($mp_user_id,'_MailPress_newsletter') : '';

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
						'htmlend'	=> "<br/>\n"
					);
		$r = wp_parse_args( $args, $defaults );
		extract( $r );

		global $mp_registered_newsletters;
		$checklist = false;

		$x = MP_Newsletter::get_active();

		if ($mp_user_id) 	$in = MP_Newsletter::get_mp_user_newsletters($mp_user_id);
		else			$in = array(); //$in = MP_Newsletter::get_mp_user_newsletters();

		$lib_nl = ($admin) ? 'desc' : 'display';
		foreach ($x as $k => $v)
		{
			$checked = (isset($in[$k])) ? " checked='checked'" : '';
			if ($mp_registered_newsletters[$k][$lib_nl])
				switch ($type)
				{
					case 'checkbox' :
						$checklist .= "$htmlstart<input type='checkbox' name='" . $name . "[$k]'$checked/>$htmlmiddle" . $mp_registered_newsletters[$k][$lib_nl] . $htmlend;
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

		$x = MP_Newsletter::get_active();
		MP_User::delete_meta($mp_user_id,'_MailPress_newsletter');

		foreach ($x as $k => $v) 
		{
			if ($mp_registered_newsletters[$k]['in']) 
			{
				if (isset($_POST[$name][$k])) MP_User::add_meta($mp_user_id,'_MailPress_newsletter',$k);
			}
			else
			{
				if (!isset($_POST[$name][$k])) MP_User::add_meta($mp_user_id,'_MailPress_newsletter',$k);
			}
		}
	}
}
?>