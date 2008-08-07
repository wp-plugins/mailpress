<?php
	function mp_mail_links() {

		global $mp_registered_newsletters;

		$content 	= '';
		$err 		= 0;

		switch (true)
		{
			case (isset($_GET['del'])) :

				$del 		= $_GET['del'];
				$id    	= MP_User::get_user_id($del);
				$email 	= MP_User::get_user_email($id);

				if ('active' == MP_User::get_user_status($id)) $active = true;

				$title    =  sprintf(__('Manage Subscription (%1$s)','MailPress'), $email);

				if ($id)
				{
					if (isset($_POST['cancel']))
					{
						$content .= '<p>' . __('Cancelled action','MailPress') ."</p>\n";
						$content .= "<br/>\n";
						return array('title'=>$title,'content'=>$content);
					}
					if (isset($_POST['delconf']))
					{
						$comment_subs = MP_USER::get_comment_subs($id);
						foreach ($comment_subs as $comment_sub)
						{
							if (isset($_POST['keep_comment_sub'][$comment_sub->meta_id])) continue;
							delete_post_meta($comment_sub->post_id, '_MailPress_subscribe_to_comments_', $id);
							MailPress::update_stats('c',$comment_sub->post_id,-1);
						}

						if ($active)
						{
							$newsletters = MP_Newsletter::get_active();
							MP_User::delete_usermeta($id,'_MailPress_newsletter');
							foreach ($newsletters as $k => $v) 
							{
								if ($mp_registered_newsletters[$k]['in']) 
								{
									if (isset($_POST['keep_newsletters'][$k])) MP_User::add_usermeta($id,'_MailPress_newsletter',$k);
								}
								else
								{
									if (!isset($_POST['keep_newsletters'][$k])) MP_User::add_usermeta($id,'_MailPress_newsletter',$k);
								}
							}
						}
					}

					$comment_subs = MP_USER::get_comment_subs($id);
					foreach ($comment_subs as $comment_sub)
					{
						$comments .= "<input type='checkbox' name='keep_comment_sub[" . $comment_sub->meta_id . "]' checked='checked' />&nbsp;" . apply_filters( 'the_title', $comment_sub->post_title ) . "<br/>\n";
					}

					if ($active)
					{
						$newsletters= MP_Newsletter::get_active();
						$in  		= MP_Newsletter::get_mp_user_newsletters($id);
						foreach ($newsletters as $k => $v)
						{
							$checked = (isset($in[$k])) ? " checked='checked'" : '';
							if ($mp_registered_newsletters[$k]['display'])
								$blog .= "<input type='checkbox' name='keep_newsletters[$k]' $checked />&nbsp;&nbsp;" . $mp_registered_newsletters[$k]['display'] . "<br/>\n";
						}
					}
		
					$content .= "<form action='' method='post'>\n";

					if (isset($comments)) 	
					{
						$content .= "<h3>" . __('Comments') . "</h3><br/>\n";
						$content .= $comments;
					}

					$content .= "<h3>" . __('Newsletters','MailPress') . "</h3><br/>\n";
					$content .= $blog;

					$content .= "	<input type='hidden'                name='status' value='" . MP_User::get_user_status($id) . "' />\n";
					$content .= "	<br/><p><input class='button' type='submit' name='delconf' value='" . __('OK','MailPress') . "' />\n";
					$content .= "	<input class='button' type='submit' name='cancel'  value='" . __('Cancel','MailPress') . "'/></p>\n";
					$content .= "</form>\n";
					$content .= "<br/>\n";
					$content .= "<h3><a href='" . MP_User::get_delall_url($del) . "'>" . __('Delete Subscription','MailPress') . "</a></h3>\n";
					$content .= "<br/>\n";
					return array('title'=>$title,'content'=>$content);
				}
				else $err = 1;
			break;
			case (isset($_GET['delall'])) :
				$id    = MP_User::get_user_id($_GET['delall']);
				$email = MP_User::get_user_email($id);

				$title = __('Delete Subscription','MailPress');

				if ($id)
				{
					if (isset($_POST['delconf'])) 
					{
						if (MP_User::set_user_status($id, 'delete'))
						{
							$content .= sprintf(__('<p>We confirm that the email adress <b>%1$s</b> has been removed from the database.</p>','MailPress'), $email);
							$content .= "<br/>\n";
							return array('title'=>$title,'content'=>$content);
						}
					}
					elseif (isset($_POST['cancel']))
					{
						$content .= '<p>' . __('Cancelled action','MailPress') ."</p>\n";
						$content .= "<br/>\n";
						return array('title'=>$title,'content'=>$content);
					}
					else
					{
						$content .= '<p>' .sprintf(__('<p>Are you sure you want to unsubscribe <b>%1$s</b> from <b>%2$s</b>.</p>','MailPress'), $email, get_bloginfo('name')) ."</p>\n";
						$content .= "<br/><br/>\n";
						$content .= "<form action='' method='post'>\n";
						$content .= "	<input class='button' type='submit' name='delconf' value='" . __('OK','MailPress') . "' />\n";
						$content .= "	<input class='button' type='submit' name='cancel'  value='" . __('Cancel','MailPress') . "'/>\n";
						$content .= "</form>\n";
						$content .= "<br/>\n";
						return array('title'=>$title,'content'=>$content);
					}
				}
				else $err = 2;
			break;
			case (isset($_GET['add'])) :
				$id    = MP_User::get_user_id($_GET['add']);
				$email = MP_User::get_user_email($id);
				$url   = MP_User::get_unsubscribe_url($_GET['add']);

				$title    = __('Confirm Subscription','MailPress');

				if ($id)
				{
					if ('active' != MP_User::get_user_status($id))
					{
						if (MP_User::set_user_status($id, 'active'))
						{	
							$content .= sprintf(__('<p><b>%1$s</b> has been succesfully inserted in the database.</p>','MailPress'), $email);
							$content .= "<br/>\n";
							$content .= "<h3>" . sprintf(__('<a href="%1$s">Manage Subscription</a>','MailPress'), $url) . "</h3>\n";
							$content .= "<br/>\n";
							return array('title'=>$title,'content'=>$content);
						}
						else $err = 3;
					}
					else $err = 4;
				}
				else $err = 5;
			break;
			case (isset($_GET['view'])) :
				$view  = $_GET['view'];
				$id    = MP_User::get_user_id($view);
				$email = MP_User::get_user_email($id);	

				if ($id)
				{
					$mail_id = $_GET['id'];
					$mail = MP_Mail::get_mail($mail_id);

					if ($mail)
					{

						$title    = $mail->subject;

						if (MailPress::is_email($mail->toemail))
						{
							if ($email == $mail->toemail)
							{
								$content .= sprintf(__('<p> From : <b>%1$s</b> &lt;<b>%2$s</b>&gt; </p>','MailPress'), $mail->fromemail, $mail->fromname);
								$content .= sprintf(__('<p> To   : <b>%1$s</b> &lt;<b>%2$s</b>&gt; </p>','MailPress'), $mail->toemail, $mail->toname);
								$content .= "<p><iframe id='mp' style='width:700px;' src='" . get_option('siteurl') . '/' . MP_PATH . "mp-includes/action.php?action=view&id=$mail_id&key=$view' onload='resizeIframe()'></iframe></p>";
								$content .= "<br/>\n";
								return array('title'=>$title,'content'=>$content);
							}
							else $err = 6;
						}
						else
						{
							$recipients = unserialize($mail->toemail);
							if (is_array($recipients) && (isset($recipients[$email])))
							{
								$content .= sprintf(__('<p> From : <b>%1$s</b> &lt;<b>%2$s</b>&gt; </p>','MailPress'), $mail->fromemail, $mail->fromname);
								$content .= sprintf(__('<p> To   : <b>%1$s</b> &lt;<b>%2$s</b>&gt; </p>','MailPress'), $email, $email);
								$content .= "<p><iframe id='mp' style='width:700px;' src='" . get_option('siteurl') . '/' . MP_PATH . "mp-includes/action.php?action=view&id=$mail_id&key=$view' onload='resizeIframe()'></iframe></p>";
								$content .= "<br/>\n";
								return array('title'=>$title,'content'=>$content);
							}
							else  $err = 7;
							unset($recipients);
						}
					}
					else $err = 8;
				}
				else $err = 9;
			break;
		}

		$content .= '<p>' . sprintf(__('<p> ERROR # %1$s !</p>','MailPress'), $err) . "</p>\n";
		$content .= (8 == $err) ? '<p>' . __('Mail has been deleted.','MailPress') . "</p>\n" : '<p>' . __('Check you are using the appropriate link.','MailPress') . "</p>\n<p>" . __('Incorrect link or already processed.','MailPress') . "</p>\n";
		$content .= "<br/>\n";

		return array('title'=>$title,'content'=>$content);
	}
?>