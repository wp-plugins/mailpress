<?php
	function mp_mail_links() {

		global $mp_registered_newsletters;

		$content 	= '';
		$err 		= 0;

		switch (true)
		{
			case (isset($_GET['del'])) :

				$del 		= $_GET['del'];
				$mp_user_id = MP_User::get_id($del);
				$email 	= MP_User::get_email($mp_user_id);

				if ('active' == MP_User::get_status($mp_user_id)) $active = true;

				$title    =  sprintf(__('Manage Subscription (%1$s)','MailPress'), $email);

				if ($mp_user_id)
				{
					if (isset($_POST['cancel']))
					{
						$content .= '<p>' . __('Cancelled action','MailPress') ."</p>\n";
						$content .= "<br />\n";
						return array('title'=>$title,'content'=>$content);
					}
					if (isset($_POST['delconf']))
					{
						MailPress::update_mp_user_comments($mp_user_id);
						if ($active)
						{
							MP_Newsletter::update_mp_user_newsletters($mp_user_id);
							if (class_exists('MailPress_mailing_lists')) MailPress_mailing_lists::update_mp_user_mailinglists($mp_user_id);
						}
						$content .= "<div id='moderated' class='updated fade'><p>" . __('Subscription saved','MailPress') . "</p></div>\n";
					}

					$check_comments = MailPress::checklist_mp_user_comments($mp_user_id);
					if ($active)
					{
						$check_newsletters = MP_Newsletter::checklist_mp_user_newsletters($mp_user_id);
						if (class_exists('MailPress_mailing_lists')) $checklist_mailinglists = MailPress_mailing_lists::checklist_mp_user_mailinglists($mp_user_id);
					}
		
					$content .= "<form action='' method='post'>\n";

					if ($check_comments) 	
					{
						$ok = true;
						$content .= "<h3>" . __('Comments') . "</h3>\n";
						$content .= $check_comments ;
					}

					if ($check_newsletters) 	
					{
						$ok = true;
						$content .= "<h3>" . __('Newsletters','MailPress') . "</h3>\n";
						$content .= $check_newsletters ;
					}

					if ($checklist_mailinglists)
					{
						$ok = true;
						$content .= "<h3>" . __('Mailing lists','MailPress_mailing_lists') . "</h3>\n";
						$content .= $checklist_mailinglists ;
					}

					if ($ok)
					{
						$content .= "	<input type='hidden'                name='status' value='" . MP_User::get_status($mp_user_id) . "' />\n";
						$content .= "	<br /><p><input class='button' type='submit' name='delconf' value='" . __('OK','MailPress') . "' />\n";
						$content .= "	<input class='button' type='submit' name='cancel'  value='" . __('Cancel','MailPress') . "' /></p>\n";
					}
					else
					{
						$content .= '<br /><br />';
						if ($active) 	$content .= __('Nothing to subscribe for ...','MailPress');
						else			$content .= __('Your email has been deactivated, ask the administrator ...','MailPress');
						$content .= '<br /><br />';
					}
					$content .= "</form>\n";
					$content .= "<br />\n";
					$content .= "<h3><a href='" . MP_User::get_delall_url($del) . "'>" . __('Delete Subscription','MailPress') . "</a></h3>\n";
					$content .= "<br />\n";
					return array('title'=>$title,'content'=>$content);
				}
				else $err = 1;
			break;
			case (isset($_GET['delall'])) :
				$mp_user_id    = MP_User::get_id($_GET['delall']);
				$email = MP_User::get_email($mp_user_id);

				$title = __('Delete Subscription','MailPress');

				if ($mp_user_id)
				{
					if (isset($_POST['delconf'])) 
					{
						if (MP_User::set_status($mp_user_id, 'delete'))
						{
							$content .= sprintf(__('<p>We confirm that the email adress <b>%1$s</b> has been removed from the database.</p>','MailPress'), $email);
							$content .= "<br />\n";
							return array('title'=>$title,'content'=>$content);
						}
					}
					elseif (isset($_POST['cancel']))
					{
						$content .= '<p>' . __('Cancelled action','MailPress') ."</p>\n";
						$content .= "<br />\n";
						return array('title'=>$title,'content'=>$content);
					}
					else
					{
						$content .= '<p>' .sprintf(__('<p>Are you sure you want to unsubscribe <b>%1$s</b> from <b>%2$s</b>.</p>','MailPress'), $email, get_bloginfo('name')) ."</p>\n";
						$content .= "<br /><br />\n";
						$content .= "<form action='' method='post'>\n";
						$content .= "	<input class='button' type='submit' name='delconf' value='" . __('OK','MailPress') . "' />\n";
						$content .= "	<input class='button' type='submit' name='cancel'  value='" . __('Cancel','MailPress') . "' />\n";
						$content .= "</form>\n";
						$content .= "<br />\n";
						return array('title'=>$title,'content'=>$content);
					}
				}
				else $err = 2;
			break;
			case (isset($_GET['add'])) :
				$mp_user_id    = MP_User::get_id($_GET['add']);
				$email = MP_User::get_email($mp_user_id);
				$url   = MP_User::get_unsubscribe_url($_GET['add']);

				$title    = __('Subscription confirmed','MailPress');

				if ($mp_user_id)
				{
					if ('active' != MP_User::get_status($mp_user_id))
					{
						if (MP_User::set_status($mp_user_id, 'active'))
						{	
							$content .= sprintf(__('<p><b>%1$s</b> has been succesfully inserted in the database.</p>','MailPress'), $email);
							$content .= "<br />\n";
							$content .= "<h3>" . sprintf(__('<a href="%1$s">Manage Subscription</a>','MailPress'), $url) . "</h3>\n";
							$content .= "<br />\n";
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
				$mp_user_id    = MP_User::get_id($view);
				$email = MP_User::get_email($mp_user_id);	

				if ($mp_user_id)
				{
					$mail_id = $_GET['id'];
					$mail = MP_Mail::get($mail_id);

					if ($mail)
					{

						$title    = $mail->subject;

						if (MailPress::is_email($mail->toemail))
						{
							if ($email == $mail->toemail)
							{
								if (MailPress::is_email($mail->fromname))				str_replace('@','(at)',$toname) . " &lt;$toemail&gt;"; 
								$content .= sprintf(__('<p> From : <b>%1$s</b></p>','MailPress'), MP_Mail::display_name_email($mail->fromname, $mail->fromemail));
								$content .= sprintf(__('<p> To   : <b>%1$s</b></p>','MailPress'), MP_Mail::display_name_email($mail->toname, $mail->toemail));
								$content .= "<p><iframe id='mp' style='width:700px;' src='" . get_option('siteurl') . '/' . MP_PATH . "mp-includes/action.php?action=view&id=$mail_id&key=$view' onload='resizeIframe()'></iframe></p>";

								$metas = MP_Mailmeta::has( $mail_id, '_MailPress_attached_file');
								if ($metas)
								{
									$content .= "<div id='attachements'><table><tr><td style='vertical-align:top;'>" . __('Attachements','MailPress') . "</td><td><table>";
									foreach($metas as $meta) $content .= "<tr><td>&nbsp;" . MP_Mail::get_attachement_link($meta, $mail->status) . "</td></tr>";
									$content .= "</table></td></tr></table></div>\n";
								}
								else  $content .= "<br />\n";

								return array('title'=>$title,'content'=>$content);
							}
							else $err = 6;
						}
						else
						{
							$recipients = unserialize($mail->toemail);
							if (is_array($recipients) && (isset($recipients[$email])))
							{
								$content .= sprintf(__('<p> From : <b>%1$s</b></p>','MailPress'), MP_Mail::display_name_email($mail->fromname, $mail->fromemail));
								$content .= sprintf(__('<p> To   : <b>%1$s</b></p>','MailPress'), MP_Mail::display_name_email($email, $email));
								$content .= "<p><iframe id='mp' style='width:700px;' src='" . get_option('siteurl') . '/' . MP_PATH . "mp-includes/action.php?action=view&id=$mail_id&key=$view' onload='resizeIframe()'></iframe></p>";

								$metas = MP_Mailmeta::has( $mail_id, '_MailPress_attached_file');
								if ($metas)
								{
									$content .= "<div id='attachements'><table><tr><td style='vertical-align:top;'>" . __('Attachements','MailPress') . "</td><td><table>";
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

		$errs[1] = __('unknown user','MailPress');
		$errs[2] = __('unknown user','MailPress');
		$errs[3] = __('cannot activate user','MailPress');
		$errs[4] = __('user already active','MailPress');
		$errs[5] = __('unknown user','MailPress');
		$errs[6] = __('user not a recipient','MailPress');
		$errs[7] = __('user not a recipient','MailPress');
		$errs[8] = __('unknown mail','MailPress');
		$errs[9] = __('unknown user','MailPress');

		$content .= '<p>' . sprintf(__('<p> ERROR # %1$s (%2$s) !</p>','MailPress'), $err, $errs[$err]) . "</p>\n";
		$content .= '<p>' . __('Check you are using the appropriate link.','MailPress') . "</p>\n";
		$content .= "<br />\n";

		return array('title'=>'','content'=>$content);
	}
?>