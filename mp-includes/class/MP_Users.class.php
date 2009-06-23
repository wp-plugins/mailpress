<?php

class MP_Users extends MP_abstract
{

	public static function get($user, $output = OBJECT) 
	{
		global $wpdb;

		switch (true)
		{
			case ( empty($user) ) :
				if ( isset($GLOBALS['mp_user']) ) 	$_user = & $GLOBALS['mp_user'];
				else						$_user = null;
			break;
			case ( is_object($user) ) :
				wp_cache_add($user->id, $user, 'mp_user');
				$_user = $user;
			break;
			default :
				if ( isset($GLOBALS['mp_user']) && ($GLOBALS['mp_user']->id == $user) ) 
				{
					$_user = & $GLOBALS['mp_user'];
				} 
				elseif ( ! $_user = wp_cache_get($user, 'mp_user') ) 
				{
					$_user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->mp_users WHERE id = %d LIMIT 1", $user));
					if ($_user) wp_cache_add($_user->id, $_user, 'mp_user');
				}
			break;
		}

		if ( $output == OBJECT ) {
			return $_user;
		} elseif ( $output == ARRAY_A ) {
			return get_object_vars($_user);
		} elseif ( $output == ARRAY_N ) {
			return array_values(get_object_vars($_user));
		} else {
			return $_user;
		}
	}

	public static function is_user($email='', $userID=null) 
	{
		if ( '' != $email && '' != self::get_status_by_email($email) && 'delete' != self::get_status_by_email($email) ) return true; 
		return false;
	}

	public static function has_subscribed_to_comments($id) 
	{
		global $wpdb;
		return $wpdb->get_var("SELECT count(*) FROM $wpdb->postmeta WHERE meta_key = '_MailPress_subscribe_to_comments_' AND meta_value = '$id';");
	}

	public static function get_id($key) 
	{
		global $wpdb;
		return $wpdb->get_var("SELECT id FROM $wpdb->mp_users WHERE confkey = '$key';");
	}

	public static function get_email($id) 
	{
		global $wpdb;
		return $wpdb->get_var("SELECT email FROM $wpdb->mp_users WHERE id = '$id';");
	}

	public static function get_id_by_email($email) 
	{
		global $wpdb;
		return $wpdb->get_var("SELECT id FROM $wpdb->mp_users WHERE email = '$email';");
	}

	public static function get_status($id) 
	{
      	global $wpdb;
	      $result = $wpdb->get_var("SELECT status FROM $wpdb->mp_users WHERE id='$id' LIMIT 1");
		return ($result == NULL) ? 'deleted' : $result;
	}

	public static function get_status_by_email($email) 
	{
		global $wpdb;
		return $wpdb->get_var("SELECT status FROM $wpdb->mp_users WHERE email = '$email'");
	}

	public static function get_key_by_email($email) 
	{
		global $wpdb;
		return $wpdb->get_var("SELECT confkey FROM $wpdb->mp_users WHERE email = '$email'");
	}

	public static function get_flag_IP() 
	{
		global $mp_user;
		return ('ZZ' == $mp_user->created_country) ? '' : "<img class='flag' alt='" . strtolower($mp_user->created_country) . "' src='" . get_option('siteurl') . '/' . MP_PATH . 'mp-admin/images/flag/' . strtolower($mp_user->created_country) . ".gif' />\n";
	}

// Insert

	public static function add($email, $name) 
	{
		$return = array();

		$defaults = MP_Widget::form_defaults();

		if ( !self::is_email($email) )
		{
			$return['result']  = false;
			$return['message'] = $defaults['txtvalidemail'];
			return $return;
		}
		
		$status = self::get_status_by_email($email);								//Test if subscription already exists

		switch ($status)
		{
			case ('active') :
				$return['result']  = false;
				$return['message'] = $defaults['txtallready'];
				return $return;
			break;
			case ('waiting') :
				if ( self::send_confirmation_subscription($email, $name, self::get_key_by_email($email)) )
				{
					$return['result']  = true;
					$return['message'] = $defaults['txtwaitconf'] . ' <small>(2)</small>';
				}
				else
				{
					$return['result']  = false;
					$return['message'] = $defaults['txterrconf'] . ' <small>(2)</small>';
				}
				return $return;
			break;
			default :
				$key = md5(uniqid(rand(), 1));								//generate key
				if ( self::send_confirmation_subscription($email, $name, $key) )			//email was sent
				{
					if ( self::insert($email, $name, $key) )
					{
						$return['result']  = true;
						$return['message'] = $defaults['txtwaitconf'] ;
						return $return;
					}
					else
					{
						$return['result']  = false;
						$return['message'] = $defaults['txtdberror'];
						return $return;
					}
				}
				$return['result']  = false;
				$return['message'] = $defaults['txterrconf'];
				return $return;
			break;
		}
	}

	public static function insert($email, $name, $key=false, $status='waiting', $import = false) 
	{
		global $wpdb;

		MailPress::update_stats('u', 'waiting', 1);
		if ('active' == $status) MailPress::update_stats('u', 'active', 1);

		if ($key === false)
		{
		 	$key = md5(uniqid(rand(), 1));	
			MailPress::update_stats('u', 'comment', 1);
		}

		$now	  	= date('Y-m-d H:i:s');
		$userid 	= MailPress::get_wp_user_id();
		$ip		= $_SERVER['REMOTE_ADDR'];
		$agent	= trim(strip_tags($_SERVER['HTTP_USER_AGENT']));

		$name 	= mysql_real_escape_string($name);

		$ip2country = self::get_ip2country($ip);

		$ip2USstate = ('US' == $ip2country) ? self::get_ip2USstate($ip) : 'ZZ' ;

		$query = "INSERT INTO $wpdb->mp_users (email, name, status, confkey, created, created_IP, created_agent, created_user_id, created_country, created_US_state) ";
		$query .= "VALUES ('$email', '$name', '$status', '$key', '$now', '$ip', '$agent', $userid, '$ip2country', '$ip2USstate');";
      	$results = $wpdb->query( $query );

		$mp_user_id = self::get_id_by_email($email);
		do_action('MailPress_insert_user', $mp_user_id);
		if (('active' == $status) && !$import) do_action('MailPress_activate_user', $mp_user_id, 'MailPress_activate_user');

		return ($results !== FALSE);
	}

////  Ip  ////

	public static function get_ip2country($ip)
	{
		//if ('127.0.0.1' == $ip) return 'ZZ';

		self::require_class('Ip');

		return MP_Ip::get_country($ip);
	}

	public static function get_ip2USstate($ip)
	{
		//if ('127.0.0.1' == $ip) return 'ZZ';

		self::require_class('Ip');

		return MP_Ip::get_USstate($ip);
	}

// Update

	public static function set_status($id, $status) 
	{
		switch($status) 
		{
			case 'active':
					return self::activate($id);
			break;
			case 'waiting':
					return self::deactivate($id);
			break;
			case 'delete':
					return self::delete($id);
			break;
		}
		return true;
	}

	public static function activate($id) 
	{
		global $wpdb;
		
		$query  = "SELECT * FROM $wpdb->mp_users WHERE id='$id';";
		$mp_user = $wpdb->get_row( $query );
		$now	  = date('Y-m-d H:i:s');

		if ( $mp_user && 'waiting' == $mp_user->status )
		{
			MailPress::update_stats('u', 'active', 1);
			if (self::has_subscribed_to_comments($id)) MailPress::update_stats('u', 'comment', -1);

			$userid 	= MailPress::get_wp_user_id();
			$ip		= $_SERVER['REMOTE_ADDR'];
			$agent	= trim(strip_tags($_SERVER['HTTP_USER_AGENT']));

			$query = "UPDATE $wpdb->mp_users SET status = 'active', laststatus = '$now', laststatus_IP = '$ip', laststatus_agent = '$agent', laststatus_user_id = $userid WHERE id='$id';";
			$update = $wpdb->query( $query );

			if ($update) 	
			{
				self::send_succesfull_subscription($mp_user->email, $mp_user->name, $mp_user->confkey); 
				do_action('MailPress_activate_user', $id, 'MailPress_activate_user'); 
				return $now;
			}
			else 
				return false;
		}
		return true;
	}

	public static function deactivate($id) 
	{
		global $wpdb;
		
		$query  = "SELECT * FROM $wpdb->mp_users WHERE id='$id';";
		$mp_user = $wpdb->get_row( $query );
		$now	  = date('Y-m-d H:i:s');

		if ( $mp_user && 'active' == $mp_user->status )
		{
			MailPress::update_stats('u', 'active', -1);
			if (self::has_subscribed_to_comments($id)) MailPress::update_stats('u', 'comment', 1);

			$userid 	= MailPress::get_wp_user_id();
			$ip		= $_SERVER['REMOTE_ADDR'];
			$agent	= trim(strip_tags($_SERVER['HTTP_USER_AGENT']));

			$query = "UPDATE $wpdb->mp_users SET status = 'waiting', laststatus = '$now', laststatus_IP = '$ip', laststatus_agent = '$agent', laststatus_user_id = $userid WHERE id='$id';";
			$update = $wpdb->query( $query );

			return ($update) ? $now : false;
		}
		return true;
	}

	public static function update_name($id, $name) 
	{
		global $wpdb;
		$query  = "UPDATE $wpdb->mp_users SET name = '$name' WHERE id='$id';";
		do_action('MailPress_update_name', $id, $name);
		return $wpdb->query( $query );
	}

	public static function delete($id) 
	{
		global $wpdb;

		do_action('MailPress_delete_user', $id);

		$x = self::has_subscribed_to_comments($id);
		if ($x)
		{
			$query = "SELECT post_id FROM $wpdb->postmeta    WHERE meta_key = '_MailPress_subscribe_to_comments_' and meta_value = '$id';";
			$posts = $wpdb->get_results( $query );
			foreach ($posts as $post) MailPress::update_stats('c', $post->post_id, -1);
			$query = "DELETE FROM $wpdb->postmeta    WHERE meta_key = '_MailPress_subscribe_to_comments_' and meta_value = '$id';";
			$results = $wpdb->query( $query );
		}

		if ('active' == self::get_status($id)) 		MailPress::update_stats('u', 'active', -1);
		elseif ($x)							MailPress::update_stats('u', 'comment', -1);
		MailPress::update_stats('u', 'waiting', -1);

		$query = "DELETE FROM $wpdb->mp_users WHERE id = $id;";
		$results = $wpdb->query( $query );
		$query = "DELETE FROM $wpdb->mp_usermeta WHERE user_id = $id;";
		$results = $wpdb->query( $query );

		wp_cache_delete($id, 'mp_user');
		return true;
	}

//// Mailing lists ////

	public static function get_mailinglists()
	{
		$draft_dest = array (	''  => '&nbsp;', 
						'1' => __('active blog', 'MailPress'), 
						'2' => __('active comments', 'MailPress'), 
						'3' => __('active blog + comments', 'MailPress'), 
						'4' => __('active + not active', 'MailPress') 
					  );
		return apply_filters('MailPress_mailinglists', $draft_dest);
	}

/// MAIL URLs ///

	private static function _get_url($action, $key){
		global $wp_rewrite;
		global $mp_general;
		
		switch($mp_general['subscription_mngt'])
		{
			case 'ajax':
				return add_query_arg( array('action' => 'mail_link', $action => $key), MP_Action_url );
			case 'page_id':
				$p = get_post($mp_general['id']);
				$s = ($wp_rewrite->get_page_permastruct() != '' && isset($p->post_status) && 'draft' != $p->post_status)? '?':'&';
				return get_permalink($mp_general['id']) . $s . $action . '=' . $key ;
			case 'cat':
				$a = $wp_rewrite->get_category_permastruct();
				$s = (!empty($a))? '?':'&';
				return get_category_link($mp_general['id']) . $s . $action . '=' . $key ;
			default:
				return get_option('home') . '/?' . $mp_general['subscription_mngt'] . '=' . $mp_general['id'] . '&' . $action . '=' . $key ;
		}
	}
 
 	public static function get_subscribe_url($key)
 	{
		return self::_get_url('add', $key);
 	}
 
 	public static function get_unsubscribe_url($key)
 	{
		return self::_get_url('del', $key);
 	}
 
 	public static function get_delall_url($key)
 	{
		return self::_get_url('delall', $key);
 	}
 
 	public static function get_view_url($key, $id)
 	{
		return self::_get_url('view', $key).'&id=' . $id;
 	}

////	send subscription mail functions 	////

	public static function send_confirmation_subscription($email, $name, $key) 
	{
		$url				= get_option('home');

		$mail				= (object) null;
		$mail->Template 		= 'new_subscriber';

		$mail->toemail 		= $email;
		$mail->toname		= $name;

		$mail->subscribe		= self::get_subscribe_url($key);

		$mail->subject		= sprintf( __('[%1$s] Waiting for %2$s', 'MailPress'), get_bloginfo('name'), $mail->toname );

		$message  = sprintf( __('Please, confirm your subscription to %1$s emails by clicking the following link :', 'MailPress'), get_bloginfo('name') );
		$message .= "\n\n";
		$message .= '{{subscribe}}';
		$message .= "\n\n";
		$message .= __('If you do not want to receive more emails, ignore this one !', 'MailPress');
		$message .= "\n\n";
		$mail->plaintext   	= $message;

		$message  = sprintf( __('Please, confirm your subscription to %1$s emails by clicking the following link :', 'MailPress'), "<a href='$url'>" . get_bloginfo('name') . "</a>" );
		$message .= '<br /><br />';
		$message .= "<a href='{{subscribe}}'>" . __('Confirm', 'MailPress') . "</a>";
		$message .= '<br /><br />';
		$message .= __('If you do not want to receive more emails, ignore this one !', 'MailPress');
		$message .= '<br /><br />';
		$mail->html    		= $message;

		return MailPress::mail($mail);
	}

	public static function send_succesfull_subscription($email, $name, $key) 
	{
		$url 		= get_option('home');

		$mail				= (object) null;
		$mail->Template 		= 'confirmed';

		$mail->toemail 		= $email;
		$mail->toname		= $name;

		$mail->subject		= sprintf( __('[%1$s] Successful subscription for %2$s', 'MailPress'), get_bloginfo('name'), $email );

		$message  = sprintf(__('We confirm your subscription to %1$s emails', 'MailPress'), get_bloginfo('name') );
		$message .= "\n\n";
		$message .= __('Congratulations !', 'MailPress');
		$message .= "\n\n";
		$mail->plaintext   	= $message;

		$message  = sprintf(__('We confirm your subscription to %1$s emails', 'MailPress'), "<a href='$url'>" . get_bloginfo('name') . "</a>" );
		$message .= '<br /><br />';
		$message .= __('Congratulations !', 'MailPress');
		$message .= '<br /><br />';
		$mail->html    		= $message;

		return MailPress::mail($mail);
	}
}
?>