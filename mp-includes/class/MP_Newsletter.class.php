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
		unset($mp_registerd_newsletters[$id]);
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

add_action(	'mp_action_newslettersend', 	array('MP_Newsletter','process'));

class MP_Newsletter
{
	function schedule()
	{
		if ('wpcron' == $mp_general['batch_mode']) if (!wp_next_scheduled( 'mp_action_newslettersend' )) wp_schedule_single_event(time(), 'mp_action_newslettersend');
		else do_action('mp_action_newslettersend');
	}

	function process() {
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

	function send($newsletter,$qp=true) {

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

	}

	function have_post($post_id) {
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

	function have($newsletter) {
		$y = get_option($newsletter['threshold']['name']);

		if ($y && ($newsletter['threshold']['value'] <= $y['threshold']))	return false;

		$y['threshold'] = $newsletter['threshold']['value'];
		update_option($newsletter['threshold']['name'],$y);

		$newsletter['query_posts'] = $newsletter['threshold']['query_posts'];

		return $newsletter;
	}

	function get_yearweekofday($date)
	{
		global $wpdb;

		$w = (int) $wpdb->get_var("SELECT WEEK('" . $date . "',1)");
		if (10 > $w) $w = '0' . $w;
		return substr($date,0,4) . $w;
	}

	function get() {

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

	function get_active() {

		global $mp_general;

		$x = MP_Newsletter::get();

		foreach ($x as $k => $v) if (!isset($mp_general['newsletters'][$k]))	unset($x[$k]);

		return $x;
	}

	function get_mp_user_newsletters($mp_user_id) {

		global $mp_registered_newsletters;

		$x = MP_Newsletter::get_active();
		$a = MP_User::get_usermeta($mp_user_id,'_MailPress_newsletter');

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
}
?>