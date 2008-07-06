<?php
	function mp_mail_links() {

		$content 	= '';

		switch (true)
		{
			case (isset($_POST['delconf'])) :
				$del   = $_POST['del'];
				$id    = MP_User::get_user_id($del);

				if ($id)
				{
					if (MP_User::set_user_status($id, 'delete'))
					{
						$title    = __('Deleted Subscription','MailPress');
						$content .= sprintf(__('<p>We confirm that the email adress <b>%1$s</b> has been removed from the database.</p>','MailPress'), $_POST['email']);
					}
					else
					{
						$title    = sprintf(__('<p> ERROR # %1$s !</p>','MailPress'), '1');
						$content .= '<p>' . __('An error as occured !','MailPress') . "</p>\n";
						$content .= '<p>' . __('Please contact the administrator of this site.','MailPress') . "</p>\n";
					}
				}
				else
				{
					$title    = sprintf(__('<p> ERROR # %1$s !</p>','MailPress'), '2');
					$content .= '<p>' . __('An error as occured !','MailPress') . "</p>\n";
					$content .= '<p>' . __('Subscriber already deleted.','MailPress') . "</p>\n";
				}
			break;
			case (isset($_POST['cancel'])) :
			break;
			case (isset($_GET['del'])) :
				$del   = $_GET['del'];
				$id    = MP_User::get_user_id($del);
				$email = MP_User::get_user_email($id);

				if ($id)
				{
					$title    = __('Subscription management','MailPress');
		
					$content .= '<p>' .sprintf(__('<p>Delete Subscription Approval for <b>%1$s</b>.</p>','MailPress'), $email) ."</p>\n";
					$content .= "<div style='text-align:center'>\n";
					$content .= '<p>' .sprintf(__('<p>Are you sure you want to unsubscribe from <b>%1$s</b>.</p>','MailPress'), get_bloginfo('name')) ."</p>\n";
					$content .= "<br/><br/>\n";
					$content .= "<form action='' method='post'>\n";
					$content .= "	<input type='hidden'                name='del' value='" . $del . "' />\n";
					$content .= "	<input type='hidden'                name='email' value='" . $email . "' />\n";
					$content .= "	<input class='button' type='submit' name='delconf' value='" . __('OK','MailPress') . "' />\n";
					$content .= "	<input class='button' type='submit' name='cancel'  value='" . __('Cancel','MailPress') . "'/>\n";
					$content .= "</form>\n";
					$content .= "</div>\n";
				}
				else
				{
					$title    = sprintf(__('<p> ERROR # %1$s !</p>','MailPress'), '3');

					$content .= '<p>' . __('Incorrect subscription link or subscription already removed.','MailPress') . "</p>\n";
					$content .= '<p>' . __('Check you are using the appropriate link.','MailPress') . "</p>\n";
					$content .= '<p>' . __('Ask for a new subscription and we will send you a new mail','MailPress') . "</p>\n";
				}
			break;
			case (isset($_GET['add'])) :
				$add   = $_GET['add'];
				$id    = MP_User::get_user_id($add);
				$email = MP_User::get_user_email($id);
	
				if ('active' == MP_User::get_user_status($id))
				{
					$title    = sprintf(__('<p> ERROR # %1$s !</p>','MailPress'), '4');
					$content .= sprintf(__('<p><b>%1$s</b> has already subscribed.</p>','MailPress'), $email);
				}
				else
				{
					if ($id)
					{
						if (MP_User::set_user_status($id, 'active'))
						{	
							$email = MP_User::get_user_email($id);		
	
							$title    = __('Subscription confirmation','MailPress');	

							$content .= sprintf(__('<p><b>%1$s</b> has been succesfully inserted in the database.</p>','MailPress'), $email);
						}
						else
						{
							$title    = sprintf(__('<p> ERROR # %1$s !</p>','MailPress'), '5');

							$content .= '<p>' . __('An error as occured !','MailPress') . "</p>\n";
							$content .= '<p>' . __('Please contact the administrator of this site.','MailPress') . "</p>\n";
						}
					}
					else
					{
						$title    = sprintf(__('<p> ERROR # %1$s !</p>','MailPress'), '6');

						$content .= '<p>' . __('Incorrect subscription link or subscription already removed.','MailPress') . "</p>\n";
						$content .= '<p>' . __('Check you are using the appropriate link.','MailPress') . "</p>\n";
						$content .= '<p>' . __('Ask for a new subscription and we will send you a new mail','MailPress') . "</p>\n";
					}
				}
			break;
			case (isset($_GET['view'])) :
				$view   = $_GET['view'];

				$id    = MP_User::get_user_id($view);
				$email = MP_User::get_user_email($id);	
				if ($id)
				{
					$mail_id = $_GET['id'];
					$mail = MP_Mail::get_mail($mail_id);
					if ($mail)
					{
						if (MailPress::is_email($mail->toemail))
						{
							if ($email == $mail->toemail)
							{
								$title    = $mail->subject;
								$content .= sprintf(__('<p> From : <b>%1$s</b> &lt;<b>%2$s</b>&gt; </p>','MailPress'), $mail->fromemail, $mail->fromname);
								$content .= sprintf(__('<p> To   : <b>%1$s</b> &lt;<b>%2$s</b>&gt; </p>','MailPress'), $mail->toemail, $mail->toname);
								$content .= "<p><iframe style='width:100%;border:0;height:500px' src='" . get_option('siteurl') . '/' . MP_PATH . "mp-includes/action.php?action=view&id=$mail_id&key=$view'></iframe></p>";
							}
							else
							{	// not a recipient
								$title    = sprintf(__('<p> ERROR # %1$s !</p>','MailPress'), '7');
								$content .= sprintf(__('<p><b>%1$s</b> not authorized to be here ! (%2$s)</p>','MailPress'), $email, '<small>' . __('unknown reference','MailPress') . '</small>');
							}
						}
						else
						{
							$recipients = unserialize($mail->toemail);
							if (is_array($recipients) && (isset($recipients[$email])))
							{
								$recipient = $recipients[$email];
								$title    = $mail->subject;
								$content .= sprintf(__('<p> From : <b>%1$s</b> &lt;<b>%2$s</b>&gt; </p>','MailPress'), $mail->fromemail, $mail->fromname);
								$content .= sprintf(__('<p> To   : <b>%1$s</b> &lt;<b>%2$s</b>&gt; </p>','MailPress'), $email, $email);
								$content .= "<p><iframe style='width:100%;border:0;height:500px' src='" . get_option('siteurl') . '/' . MP_PATH . "mp-includes/action.php?action=view&id=$mail_id&key=$view'></iframe></p>";
							}
							else
							{	// not a recipient
								$title    = sprintf(__('<p> ERROR # %1$s !</p>','MailPress'), '8');
								$content .= sprintf(__('<p><b>%1$s</b> not authorized to be here ! (%2$s)</p>','MailPress'), $email, '<small>' . __('unknown reference','MailPress') . '</small>');
							}
							unset($recipients);
						}
					}
					else
					{	// no archive
						$title    = sprintf(__('<p> ERROR # %1$s !</p>','MailPress'), '9');
						$content .= sprintf(__('<p><b>%1$s</b> not authorized to be here ! (%2$s)</p>','MailPress'), $email, '<small>' . __('unknown reference','MailPress') . '</small>');
					}
				}
				else
				{	// user unknown
					$title    = sprintf(__('<p> ERROR # %1$s !</p>','MailPress'), '10');
					$content .= sprintf(__('<p><b>%1$s</b> not authorized to be here ! (%2$s)</p>','MailPress'), $email, '<small>' . __('unknown reference','MailPress') . '</small>');
				}
			break;
			default :
				// everything is wrong
				$title    = sprintf(__('<p> ERROR # %1$s !</p>','MailPress'), '999');
				$content .= '<p>' . __('You are not supposed to be here ! Lost ?','MailPress') . "</p>\n";
			break;
		}
		return array('title'=>$title,'content'=>$content);
	}
?>