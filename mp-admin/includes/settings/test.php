<?php
$mp_general['tab'] = $mp_tab =  2;
	
$test 	= $_POST['test'];
$test['template'] = $test['th'][$test['theme']]['tm'];
unset($test['th']);

switch (true)
{
	case ( !self::is_email($test['toemail']) ) :
		$toemailclass = true;
		self::message(__('field should be an email', 'MailPress'), false);
	break;
	case ( empty($test['toname']) ) :
		$tonameclass = true;
		self::message(__('field should be a name', 'MailPress'), false);
	break;
	default :
		if (!add_option ('MailPress_test', $test )) update_option ('MailPress_test', $test);
		if (!add_option ('MailPress_general', $mp_general )) update_option ('MailPress_general', $mp_general);
		if (isset($_POST['Submit']))
		{
			self::message(__('Test settings saved', 'MailPress'));
		}
		else
		{
			$url   = get_option('home');
			$title = get_bloginfo('name');

			$mail = (object) null;
			$mail->Theme = $test['theme'];
			if ('0' != $test['template']) $mail->Template = $test['template'];

			self::require_class('Mails');
			$mail->id		= MP_Mails::get_id('settings test');

		// Set the from name and email
			$mail->fromemail 	= $mp_general['fromemail'];
			$mail->fromname	= stripslashes($mp_general['fromname']);

		// Set destination address
			$mail->toemail 	= $test['toemail'];
			$mail->toname	= MP_Mails::display_name(stripslashes($test['toname']));
			self::require_class('Users');
			$key = MP_Users::get_key_by_email($mail->toemail);
			if ($key)
			{
				$mail->viewhtml	 = MP_Users::get_view_url($key, $mail->id);
				$mail->unsubscribe = MP_Users::get_unsubscribe_url($key);
				$mail->subscribe 	 = MP_Users::get_subscribe_url($key);
			}

		// Set mail's subject and body
			$mail->subject	= __('Connection test - MailPress - ', 'MailPress') . ' ' . get_bloginfo('name');

			$mail->plaintext   =  "\n\n" . __('This is a test message of MailPress from', 'MailPress') . ' ' . $url . "\n\n";

			$message  = "<div style='font-family: verdana, geneva;'><br /><br />";
			$message .=  sprintf(__('This is a <blink>test</blink> message of %1$s from %2$s. <br /><br />', 'MailPress'), ' <b>MailPress</b> ', "<a href='" .  $url . "'>$title</a>");
			$message .= "<br /><br /></div>";

			$mail->html       = $message;

			if (isset($test['forcelog'])) 	$mail->forcelog = '';
			if (!isset($test['fakeit'])) 		$mail->nomail = '';
			if (!isset($test['archive'])) 	$mail->noarchive = '';
			if (!isset($test['stats'])) 		$mail->nostats = '';

			if (isset($mail->Template) && (!in_array($mail->Template, array('comments', 'confirmed', 'moderate', 'new_subscriber', 'new_user')))) 
			{
				$query = "SELECT ID FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'post' ORDER BY RAND() LIMIT 1;";
				$posts = $wpdb->get_results( $query );
				if ($posts)
				{
					query_posts('p='. $posts[0]->ID);
				}
			}

			if (MailPress::mail($mail))
				if (!isset($test['fakeit'])) 	self::message(__('Test settings saved, Mail not send as required', 'MailPress'));
				else					self::message(__('Test successfull, CONGRATULATIONS !', 'MailPress'));
			else
				self::message(__('FAILED. Check your logs & settings !', 'MailPress'), false);
		}
	break;
}
?>