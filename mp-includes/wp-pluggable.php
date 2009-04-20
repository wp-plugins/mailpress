<?php
if ( !function_exists( 'wp_mail' ) ) :
/**
 * wp_mail() - Function to send mail
 */
function wp_mail( $to, $subject, $message, $headers = '' ) {
	// Compact the input, apply the filters, and extract them back out
	extract( apply_filters('wp_mail', compact( 'to', 'subject', 'message', 'headers' ) ) );

	// Headers
	if ( !empty( $headers ) && !is_array( $headers ) )
	{											// Explode the headers out, so this function can take both
												// string headers and an array of headers.
		$tempheaders = (array) explode( "\n", $headers );
		$headers = array();
		if ( !empty( $tempheaders ) ) 
		{										// Iterate through the raw headers
			foreach ( $tempheaders as $header ) 
			{
				if ( strpos($header, ':') === false ) continue;
												// Explode them out
				list( $name, $content ) = explode( ':', trim( $header ), 2 );
												// Cleanup crew
				$name = trim( $name );
				$content = trim( $content );
												// Mainly for legacy -- process a From: header if it's there
				switch (true)
				{
					case ( 'from' == strtolower($name) ) :
						if ( strpos($content, '<' ) !== false ) 
						{
												// So... making my life hard again?
							$from_name = substr( $content, 0, strpos( $content, '<' ) - 1 );
							$from_name = str_replace( '"', '', $from_name );
							$from_name = trim( $from_name );

							$from_email = substr( $content, strpos( $content, '<' ) + 1 );
							$from_email = str_replace( '>', '', $from_email );
							$from_email = trim( $from_email );
						} 
						else 
						{
							$from_name = trim( $content );
						}
					break;
					case ( 'content-type' == strtolower($name) ) :
						if ( strpos( $content,';' ) !== false ) 
						{
							list( $type, $charset ) = explode( ';', $content );
							$content_type = trim( $type );
							$charset = trim( str_replace( array( 'charset=', '"' ), '', $charset ) );
						} 
						else 
						{
							$content_type = trim( $content );
						}
					break;
					default :
						$headers[trim( $name )] = trim( $content );
					break;
				}
			}
		}
	}
													// From email and name
													// Set the from name and email
	if ( isset( $from_email ) ) 
	{
		$args->fromemail  = apply_filters('wp_mail_from', $from_email );
		$args->fromname = apply_filters('wp_mail_from_name', $from_name );
	}
													// Set destination address
	$args->toemail  = $to;
	$args->toname = $to;
													// Set mail's subject and body
	$args->subject = $subject;

	if (is_array($message))
	{
		$args->plaintext = $message['plaintext'];
		$args->html      = $message['html'];
	}
	else
	{
		$args->content = $message;
	}

	if (!empty( $headers )) $args->headers = $headers;

	if (apply_filters('MailPress_tracking',false))
	{
		if (MP_User::get_key_by_email($args->toemail))
		{
			$args->mp_confkey = MP_User::get_key_by_email($args->toemail);
			$args->id         = MP_Mail::get_id('wp_mail');
		}
	}
	$rc = MailPress::mail($args);
	if ((!$rc) && isset($args->id)) MP_Mail::delete($args->id);
	return $rc;
}
endif;

if ( ! function_exists('wp_notify_postauthor') ) :
/**
 * wp_notify_postauthor() - Notify an author of a comment/trackback/pingback to one of their posts
 */
function wp_notify_postauthor($comment_id, $comment_type='') {
	$comment = get_comment($comment_id);
	$post    = get_post($comment->comment_post_ID);
	$user    = get_userdata( $post->post_author );

	if ('' == $user->user_email) return false; 					// If there's no email to send the comment to

	$comment_author_domain = @gethostbyaddr($comment->comment_author_IP);

	$blogname = get_option('blogname');

	switch ($comment_type)
	{
		case 'trackback' :
			$notify_message  = sprintf( __('New trackback on your post #%1$s "%2$s"'), $comment->comment_post_ID, $post->post_title ) . "<br />\r\n";
			$notify_message .= sprintf( __('Website: %1$s (IP: %2$s , %3$s)'), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "<br />\r\n";
			$notify_message .= sprintf( __('URL    : %s'), $comment->comment_author_url ) . "<br />\r\n";
			$notify_message .= __('Excerpt: ') . "<br />\r\n" . $comment->comment_content . "<br />\r\n<br />\r\n";
			$notify_message .= __('You can see all trackbacks on this post here: ') . "<br />\r\n";
	
			$subject = sprintf( __('[%1$s] Trackback: "%2$s"'), $blogname, $post->post_title );
		break;
		case 'pingback' :
			$notify_message  = sprintf( __('New pingback on your post #%1$s "%2$s"'), $comment->comment_post_ID, $post->post_title ) . "<br />\r\n";
			$notify_message .= sprintf( __('Website: %1$s (IP: %2$s , %3$s)'), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "<br />\r\n";
			$notify_message .= sprintf( __('URL    : %s'), $comment->comment_author_url ) . "<br />\r\n";
			$notify_message .= __('Excerpt: ') . "<br />\r\n" . sprintf('[...] %s [...]', $comment->comment_content ) . "<br />\r\n<br />\r\n";
			$notify_message .= __('You can see all pingbacks on this post here: ') . "<br />\r\n";

			$subject = sprintf( __('[%1$s] Pingback: "%2$s"'), $blogname, $post->post_title );
		break;
		default: //Comments
			$notify_message  = sprintf( __('New comment on your post #%1$s "%2$s"'), $comment->comment_post_ID, $post->post_title ) . "<br />\r\n";
			$notify_message .= sprintf( __('Author : %1$s (IP: %2$s , %3$s)'), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "<br />\r\n";
			$notify_message .= sprintf( __('E-mail : %s'), $comment->comment_author_email ) . "<br />\r\n";
			$notify_message .= sprintf( __('URL    : %s'), $comment->comment_author_url ) . "<br />\r\n";
			$notify_message .= sprintf( __('Whois  : http://ws.arin.net/cgi-bin/whois.pl?queryinput=%s'), $comment->comment_author_IP ) . "<br />\r\n";
			$notify_message .= __('Comment: ') . "<br />\r\n" . $comment->comment_content . "<br />\r\n<br />\r\n";
			$notify_message .= __('You can see all comments on this post here: ') . "<br />\r\n";

			$subject = sprintf( __('[%1$s] Comment: "%2$s"'), $blogname, $post->post_title );
		break;
	}

	$notify_message .= get_permalink($comment->comment_post_ID) . "#comments<br />\r\n<br />\r\n";
	$notify_message .= sprintf( __('Delete it: %s'), get_option('siteurl')."/wp-admin/comment.php?action=cdc&c=$comment_id" ) . "<br />\r\n";
	$notify_message .= sprintf( __('Spam it: %s'), get_option('siteurl')."/wp-admin/comment.php?action=cdc&dt=spam&c=$comment_id" ) . "<br />\r\n";

	$notify_message = apply_filters('comment_notification_text', $notify_message, $comment_id);
	$subject = apply_filters('comment_notification_subject', $subject, $comment_id);
	$message_headers = apply_filters('comment_notification_headers', $message_headers, $comment_id);

	$args->Template	= 'moderate';
	$args->toemail 	= $user->user_email;
	$args->toname 	= $user->user_email;
	$args->subject 	= $subject;
	$args->content 	= $notify_message;

			$args->c->id		= $comment_id;
			$args->c->post_ID 	= $comment->comment_post_ID;
			$args->c->author 	= $comment->comment_author;
			$args->c->author_IP = $comment->comment_author_IP;
			$args->c->email 	= $comment->comment_author_email;
			$args->c->url 		= $comment->comment_author_url;
			$args->c->domain 	= $comment_author_domain;
			$args->c->content 	= $comment->comment_content;
			$args->p->title 	= $post->post_title; 

			$args->c->type		= $comment_type;

	if (apply_filters('MailPress_tracking',false))
	{
		if (MP_User::get_key_by_email($args->toemail))
		{
			$args->mp_confkey = MP_User::get_key_by_email($args->toemail);
			$args->id         = MP_Mail::get_id('wp_notify_postauthor');
		}
	}
	$rc = MailPress::mail($args);
	if ((!$rc) && isset($args->id)) MP_Mail::delete($args->id);
	return $rc;
}
endif;

if ( !function_exists('wp_notify_moderator') ) :
/**
 * wp_notify_moderator() - Notifies the moderator of the blog about a new comment that is awaiting approval
 */
function wp_notify_moderator($comment_id) {
	global $wpdb;
	$admin_email = get_option('admin_email');

	if( get_option( "moderation_notify" ) == 0 )
		return true;

	$comment = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->comments WHERE comment_ID=%d LIMIT 1", $comment_id));
	$post = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE ID=%d LIMIT 1", $comment->comment_post_ID));

	$comment_author_domain = @gethostbyaddr($comment->comment_author_IP);
	$comments_waiting = $wpdb->get_var("SELECT count(comment_ID) FROM $wpdb->comments WHERE comment_approved = '0'");

	switch ($comment->comment_type)
	{
		case 'trackback':
			$notify_message  = sprintf( __('A new trackback on the post #%1$s "%2$s" is waiting for your approval'), $post->ID, $post->post_title ) . "<br />\r\n";
			$notify_message .= get_permalink($comment->comment_post_ID) . "<br />\r\n<br />\r\n";
			$notify_message .= sprintf( __('Website : %1$s (IP: %2$s , %3$s)'), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "<br />\r\n";
			$notify_message .= sprintf( __('URL    : %s'), $comment->comment_author_url ) . "<br />\r\n";
			$notify_message .= __('Trackback excerpt: ') . "<br />\r\n" . $comment->comment_content . "<br />\r\n<br />\r\n";

			break;
		case 'pingback':
			$notify_message  = sprintf( __('A new pingback on the post #%1$s "%2$s" is waiting for your approval'), $post->ID, $post->post_title ) . "<br />\r\n";
			$notify_message .= get_permalink($comment->comment_post_ID) . "<br />\r\n<br />\r\n";
			$notify_message .= sprintf( __('Website : %1$s (IP: %2$s , %3$s)'), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "<br />\r\n";
			$notify_message .= sprintf( __('URL    : %s'), $comment->comment_author_url ) . "<br />\r\n";
			$notify_message .= __('Pingback excerpt: ') . "<br />\r\n" . $comment->comment_content . "<br />\r\n<br />\r\n";

			break;
		default: //Comments
			$notify_message  = sprintf( __('A new comment on the post #%1$s "%2$s" is waiting for your approval'), $post->ID, $post->post_title ) . "<br />\r\n";
			$notify_message .= get_permalink($comment->comment_post_ID) . "<br />\r\n<br />\r\n";
			$notify_message .= sprintf( __('Author : %1$s (IP: %2$s , %3$s)'), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "<br />\r\n";
			$notify_message .= sprintf( __('E-mail : %s'), $comment->comment_author_email ) . "<br />\r\n";
			$notify_message .= sprintf( __('URL    : %s'), $comment->comment_author_url ) . "<br />\r\n";
			$notify_message .= sprintf( __('Whois  : http://ws.arin.net/cgi-bin/whois.pl?queryinput=%s'), $comment->comment_author_IP ) . "<br />\r\n";
			$notify_message .= __('Comment: ') . "<br />\r\n" . $comment->comment_content . "<br />\r\n<br />\r\n";
			
			break;
	}

	$notify_message .= sprintf( __('Approve it: %s'),  get_option('siteurl')."/wp-admin/comment.php?action=mac&c=$comment_id" ) . "<br />\r\n";
	$notify_message .= sprintf( __('Delete it: %s'), get_option('siteurl')."/wp-admin/comment.php?action=cdc&c=$comment_id" ) . "<br />\r\n";
	$notify_message .= sprintf( __('Spam it: %s'), get_option('siteurl')."/wp-admin/comment.php?action=cdc&dt=spam&c=$comment_id" ) . "<br />\r\n";

	$strCommentsPending = sprintf( __ngettext('%s comment', '%s comments', $comments_waiting), $comments_waiting );
	$notify_message .= sprintf( __('Currently %s are waiting for approval. Please visit the moderation panel:'), $strCommentsPending ) . "<br />\r\n";
	$notify_message .= get_option('siteurl') . "/wp-admin/edit-comments.php?comment_status=moderated<br />\r\n";

	$subject = sprintf( __('[%1$s] Please moderate: "%2$s"'), get_option('blogname'), $post->post_title );

	$notify_message = apply_filters('comment_moderation_text', $notify_message, $comment_id);
	$subject = apply_filters('comment_moderation_subject', $subject, $comment_id);

	$args->Template	= 'moderate';
	$args->toemail 	= $admin_email;
	$args->toname 	= $admin_email;
	$args->subject 	= $subject;
	$args->content 	= $notify_message;

			$args->c->id		= $comment_id;
			$args->c->post_ID 	= $comment->comment_post_ID;
			$args->c->author 	= $comment->comment_author;
			$args->c->author_IP = $comment->comment_author_IP;
			$args->c->email 	= $comment->comment_author_email;
			$args->c->url 		= $comment->comment_author_url;
			$args->c->domain 	= $comment_author_domain;
			$args->c->content 	= $comment->comment_content;
			$args->p->title 	= $post->post_title; 

	if (apply_filters('MailPress_tracking',false))
	{
		if (MP_User::get_key_by_email($args->toemail))
		{
			$args->mp_confkey = MP_User::get_key_by_email($args->toemail);
			$args->id         = MP_Mail::get_id('wp_notify_moderator');
		}
	}
	$rc = MailPress::mail($args);
	if ((!$rc) && isset($args->id)) MP_Mail::delete($args->id);
	return $rc;
}
endif;


if ( !function_exists('wp_new_user_notification') ) :
/**
 * wp_new_user_notification() - Notify the blog admin of a new user, normally via email
 *
 * @since 2.0
 *
 * @param int $user_id User ID
 * @param string $plaintext_pass Optional. The user's plaintext password
 */
function wp_new_user_notification($user_id, $plaintext_pass = '') {
	$user = new WP_User($user_id);

	$user_login = stripslashes($user->user_login);
	$user_email = stripslashes($user->user_email);

	$message  = sprintf(__('New user registration on your blog %s:'), get_option('blogname')) . "<br />\r\n<br />\r\n";
	$message .= sprintf(__('Username: %s'), $user_login) . "<br />\r\n<br />\r\n";
	$message .= sprintf(__('E-mail: %s'), $user_email) . "<br />\r\n";

	$args->Template	= 'new_user';
	$args->toemail 	= get_option('admin_email');
	$args->toname 	= get_option('admin_email');
	$args->subject 	= sprintf(__('[%s] New User Registration'), get_option('blogname'));
	$args->content 	= $message;

	$args->u->login	= $user_login;
	$args->u->email	= $user_email;

	$args->admin 	= '';

	if (apply_filters('MailPress_tracking',false))
	{
		if (MP_User::get_key_by_email($args->toemail))
		{
			$args->mp_confkey = MP_User::get_key_by_email($args->toemail);
			$args->id         = MP_Mail::get_id('wp_new_user_notification_1');
		}
	}
	$rc = MailPress::mail($args);
	if ((!$rc) && isset($args->id)) MP_Mail::delete($args->id);

	if ( empty($plaintext_pass) )
		return;

	$message  = sprintf(__('Username: %s'), $user_login) . "<br />\r\n";
	$message .= sprintf(__('Password: %s'), $plaintext_pass) . "<br />\r\n";
	$message .= get_option('siteurl') . "/wp-login.php <br />\r\n";

	unset($args);
	$args->Template	= 'new_user';
	$args->toemail 	= $user_email;
	$args->toname 	= $user_email;
	$args->subject 	= sprintf(__('[%s] Your username and password'), get_option('blogname'));
	$args->content 	= $message;

	$args->u->login	= $user_login;
	$args->u->pwd	= $plaintext_pass;

	if (apply_filters('MailPress_tracking',false))
	{
		if (MP_User::get_key_by_email($args->toemail))
		{
			$args->mp_confkey = MP_User::get_key_by_email($args->toemail);
			$args->id         = MP_Mail::get_id('wp_new_user_notification_2');
		}
	}
	$rc = MailPress::mail($args);
	if ((!$rc) && isset($args->id)) MP_Mail::delete($args->id);
	return $rc;
}
endif;
?>