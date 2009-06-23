<?php
class MP_Mail_links extends MP_abstract
{
	public static function process() 
	{
		$errs[1] = __('unknown user', 'MailPress');
		$errs[2] = __('unknown user', 'MailPress');
		$errs[3] = __('cannot activate user', 'MailPress');
		$errs[4] = __('user already active', 'MailPress');
		$errs[5] = __('unknown user', 'MailPress');
		$errs[6] = __('user not a recipient', 'MailPress');
		$errs[7] = __('user not a recipient', 'MailPress');
		$errs[8] = __('unknown mail', 'MailPress');
		$errs[9] = __('unknown user', 'MailPress');

		switch (true)
		{
			case (isset($_GET['del'])) :
				$results = self::del($_GET['del']);
			break;
			case (isset($_GET['delall'])) :
				$results = self::delall($_GET['delall']);
			break;
			case (isset($_GET['add'])) :
				$results = self::add($_GET['add']);
			break;
			case (isset($_GET['view'])) :
				$results = self::view($_GET['view']);
			break;
			default :
				return false;
			break;
		}

		if (is_numeric($results))
		{
			$content  = '<p>' . sprintf(__('<p> ERROR # %1$s (%2$s) !</p>', 'MailPress'), $results, $errs[$results]) . "</p>\n";
			$content .= '<p>' . __('Check you are using the appropriate link.', 'MailPress') . "</p>\n";
			$content .= "<br />\n";

			return array('title' => '', 'content' => $content);
		}
		
		return $results;
	}
	
	public static function del($mp_confkey)
	{
		global $mp_subscriptions;
		if (isset($mp_subscriptions['subcomment'])) 
			self::require_class('Comment');

		self::require_class('Users');
		$mp_user_id = MP_Users::get_id($mp_confkey);
		if (!$mp_user_id) 						return 1;

		$mp_user = MP_Users::get($mp_user_id);
		$active = ('active' == $mp_user->status) ? true : false;

		$title    =  sprintf(__('Manage Subscription (%1$s)','MailPress'), $mp_user->email);
		$content = '';

		if (isset($_POST['cancel']))
		{
			$content .= '<p>' . __('Cancelled action', 'MailPress') ."</p>\n";
			$content .= "<br />\n";
			return array('title' => $title, 'content' => $content);
		}

		if (isset($_POST['delconf']))
		{
			if ($mp_user->name != $_POST['mp_user_name'])
			{
				MP_Users::update_name($mp_user->id, $_POST['mp_user_name']);
				$mp_user->name = $_POST['mp_user_name'];
			}
			if (isset($mp_subscriptions['subcomment']))
				MP_Comment::update_checklist($mp_user_id);

			if ($active)
			{
				MP_Newsletter::update_checklist($mp_user_id);
				if (class_exists('MailPress_mailinglist')) MailPress_mailinglist::update_checklist($mp_user_id);
			}
			$content .= "<div id='moderated' class='updated fade'><p>" . __('Subscriptions saved', 'MailPress') . "</p></div>\n";
		}

		$checklist_comments = $checklist_mailinglists = $checklist_newsletters = false;

		if (isset($mp_subscriptions['subcomment']))
			$checklist_comments = MP_Comment::get_checklist($mp_user_id);

		if ($active)
		{
			$checklist_newsletters = MP_Newsletter::get_checklist($mp_user_id);
			if (class_exists('MailPress_mailinglist')) $checklist_mailinglists = MailPress_mailinglist::get_checklist($mp_user_id);
		}
		
		$content .= "<form action='' method='post'>\n";

		$content .= "<h3>" . __('Name', 'MailPress') . "</h3>\n";
		$content .= "<input name='mp_user_name' type='text' value=\"" . self::input_text($mp_user->name) . "\" size='30' />\n";

		if ($checklist_comments) 	
		{
			$ok = true;
			$content .= "<h3>" . __('Comments') . "</h3>\n";
			$content .= $checklist_comments ;
		}

		if ($checklist_newsletters) 	
		{
			$ok = true;
			$content .= "<h3>" . __('Newsletters', 'MailPress') . "</h3>\n";
			$content .= $checklist_newsletters ;
		}

		if ($checklist_mailinglists)
		{
			$ok = true;
			$content .= "<h3>" . __('Mailing lists', 'MailPress') . "</h3>\n";
			$content .= $checklist_mailinglists ;
		}

		if ($ok)
		{
			$content .= "	<input type='hidden'                name='status' value='" . MP_Users::get_status($mp_user_id) . "' />\n";
			$content .= "	<br /><p><input class='button' type='submit' name='delconf' value='" . __('OK', 'MailPress') . "' />\n";
			$content .= "	<input class='button' type='submit' name='cancel'  value='" . __('Cancel', 'MailPress') . "' /></p>\n";
		}
		else
		{
			$content .= '<br /><br />';
			if ($active) 	$content .= __('Nothing to subscribe for ...', 'MailPress');
			else			$content .= __('Your email has been deactivated, ask the administrator ...', 'MailPress');
			$content .= '<br /><br />';
		}
		$content .= "</form>\n";
		$content .= "<br />\n";
		$content .= "<h3><a href='" . MP_Users::get_delall_url($mp_confkey) . "'>" . __('Delete Subscription', 'MailPress') . "</a></h3>\n";
		$content .= "<br />\n";
		return array('title' => $title, 'content' => $content);
	}

	public static function delall($mp_confkey)
	{
		self::require_class('Users');
		$mp_user_id = MP_Users::get_id($mp_confkey);
		if (!$mp_user_id) 						return 2;

		$email 	= MP_Users::get_email($mp_user_id);

		$title = __('Delete Subscription', 'MailPress');
		$content = '';

		if (isset($_POST['delconf'])) 
		{
			if (MP_Users::set_status($mp_user_id, 'delete'))
			{
				$content .= sprintf(__('<p>We confirm that the email adress <b>%1$s</b> has been removed from the database.</p>', 'MailPress'), $email);
				$content .= "<br />\n";
				return array('title' => $title, 'content' => $content);
			}
		}
		elseif (isset($_POST['cancel']))
		{
			$content .= '<p>' . __('Cancelled action', 'MailPress') ."</p>\n";
			$content .= "<br />\n";
			return array('title' => $title, 'content' => $content);
		}
		else
		{
			$content .= '<p>' .sprintf(__('<p>Are you sure you want to unsubscribe <b>%1$s</b> from <b>%2$s</b>.</p>', 'MailPress'), $email, get_bloginfo('name')) ."</p>\n";
			$content .= "<br /><br />\n";
			$content .= "<form action='' method='post'>\n";
			$content .= "	<input class='button' type='submit' name='delconf' value='" . __('OK', 'MailPress') . "' />\n";
			$content .= "	<input class='button' type='submit' name='cancel'  value='" . __('Cancel', 'MailPress') . "' />\n";
			$content .= "</form>\n";
			$content .= "<br />\n";
			return array('title' => $title, 'content' => $content);
		}
	}

	public static function add($mp_confkey)
	{
		self::require_class('Users');
		$mp_user_id = MP_Users::get_id($mp_confkey);
		if (!$mp_user_id) 						return 5;
		if ('active' == MP_Users::get_status($mp_user_id)) 	return 4;
		if (!MP_Users::set_status($mp_user_id, 'active')) 	return 3;

		$email 	= MP_Users::get_email($mp_user_id);
		$url   = MP_Users::get_unsubscribe_url($_GET['add']);

		$title    = __('Subscription confirmed', 'MailPress');
		$content = '';

		$content .= sprintf(__('<p><b>%1$s</b> has been succesfully inserted in the database.</p>', 'MailPress'), $email);
		$content .= "<br />\n";
		$content .= "<h3>" . sprintf(__('<a href="%1$s">Manage Subscription</a>', 'MailPress'), $url) . "</h3>\n";
		$content .= "<br />\n";
		return array('title' => $title, 'content' => $content);
	}

	public static function view($mp_confkey)
	{
		global $mp_general;

		self::require_class('Users');
		$mp_user_id = MP_Users::get_id($mp_confkey);
		if (!$mp_user_id) 						return 9;

		$email 	= MP_Users::get_email($mp_user_id);

		self::require_class('Mails');
		$mail_id = $_GET['id'];
		$mail = MP_Mails::get($mail_id);
		if (!$mail)								return 8;


		$content = '';

		$view_url = self::url(  MP_Action_url , array('action' => 'view', 'id' => $mail_id, 'key' => $mp_confkey));

		if (self::is_email($mail->toemail))
		{
			if ($email != $mail->toemail)				return 6;
			else
			{
				$title    = $mail->subject;
				$content .= sprintf(__('<p> From : <b>%1$s</b></p>', 'MailPress'), MP_Mails::display_name_email($mail->fromname, $mail->fromemail));
				$content .= sprintf(__('<p> To   : <b>%1$s</b></p>', 'MailPress'), MP_Mails::display_name_email($mail->toname, $mail->toemail));
				$content .= "<p><iframe id='mp' name='mp' style='width:800px;height:600px;border:none;' src='" . clean_url($view_url) . "'></iframe></p>";

				self::require_class('Mailmeta');
				$metas = MP_Mailmeta::has( $mail_id, '_MailPress_attached_file');
				if ($metas)
				{
					$content .= "<div id='attachements'><table><tr><td style='vertical-align:top;'>" . __('Attachements', 'MailPress') . "</td><td><table>";
					foreach($metas as $meta) $content .= "<tr><td>&nbsp;" . MP_Mails::get_attachement_link($meta, $mail->status) . "</td></tr>";
					$content .= "</table></td></tr></table></div>\n";
				}
				else  $content .= "<br />\n";

				if (isset($mp_general['fullscreen'])) self::mp_redirect($view_url);

				return array('title' => $title, 'content' => $content);
			}
		}
		else
		{
			$recipients = unserialize($mail->toemail);
			if (!(is_array($recipients) && (isset($recipients[$email]))))
			{
											return 7;
			}
			else
			{
				self::require_class('Mailmeta');
				$m = MP_Mailmeta::get($mail_id, '_MailPress_replacements');
				if (!is_array($m)) $m = array();

				$recipients = unserialize($mail->toemail);
				$recipient = array_merge($recipients[$email], $m);

				$title    = $mail->subject;
				foreach ($recipient as $k => $v) $title = str_replace($k, $v, $title);
				$content .= sprintf(__('<p> From : <b>%1$s</b></p>', 'MailPress'), MP_Mails::display_name_email($mail->fromname, $mail->fromemail));
				$content .= sprintf(__('<p> To   : <b>%1$s</b></p>', 'MailPress'), MP_Mails::display_name_email($email, $email));
				$content .= "<p><iframe id='mp' name='mp' style='width:800px;height:600px;border:none;' src='" . clean_url($view_url) . "'></iframe></p>";

				$metas = MP_Mailmeta::has( $mail_id, '_MailPress_attached_file');
				if ($metas)
				{
					$content .= "<div id='attachements'><table><tr><td style='vertical-align:top;'>" . __('Attachements', 'MailPress') . "</td><td><table>";
					foreach($metas as $meta)
					{
						$meta_value = unserialize( $meta['meta_value'] );
						$file_ok = (is_file($meta_value['file_fullpath'])) ? true : false;
						if (is_file($meta_value['file_fullpath']))
							$content .= "<tr><td>&nbsp;<a href='" . $meta_value['guid'] . "' style='text-decoration:none;'>" . $meta_value['name'] . "</a></td></tr>";
						else
							$content .= "<tr><td>&nbsp;<span>" . $meta_value['name'] . "</span></td></tr>";
					}
					$content .= "</table></td></tr></table></div>\n";
				}
				else
					$content .= "<br />\n";

				if (isset($mp_general['fullscreen'])) self::mp_redirect($view_url);

				return array('title' => $title, 'content' => $content);
			}
		}
	}
}
function mp_mail_links()
{
	return MP_Mail_links::process();
}
?>