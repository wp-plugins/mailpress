<?php
class MP_Bounce
{
	const bt = 132;

	function __construct()
	{
		$this->bounce_handling_config 	= get_option('MailPress_bounce_handling');
		if (!$this->bounce_handling_config) return;

		MailPress::require_class('Log');
		$this->trace = new MP_Log('mp_process_bounce_handling', ABSPATH . MP_PATH, __CLASS__, false, 'bounce_handling');

		$xmailboxstatus = array(	0	=>	'no changes',
							1	=>	'mark as read',
							2	=>	'delete' );

		$this->trace->log('!' . str_repeat( '-', self::bt) . '!');
		$bm = "Bounce Handling Report (Bounce in mailbox : " . $xmailboxstatus[$this->bounce_handling_config['mailbox_status']] . ")";
		$this->trace->log('!' . str_repeat( ' ', 5) . $bm . str_repeat( ' ', self::bt - 5 - strlen($bm)) . '!');
		$this->trace->log('!' . str_repeat( '-', self::bt) . '!');
		$bm = " start      !";
		$this->trace->log('!' . $bm . str_repeat( ' ', self::bt - strlen($bm)) . '!');

		$return = $this->process();

		do_action('MailPress_schedule_bounce_handling');

		$this->trace->log('!' . str_repeat( '-', self::bt) . '!');
		$this->trace->end($return);
	}

// process
	function process()
	{
		$return = true;

		MailPress::require_class('Pop3');
		$this->pop3 = new MP_Pop3($this->bounce_handling_config['server'], $this->bounce_handling_config['port'], $this->bounce_handling_config['username'], $this->bounce_handling_config['password'], $this->trace);

		$bm = ' connecting ! ' . $this->bounce_handling_config['server'] . ':' . $this->bounce_handling_config['port'];
		$this->trace->log('!' . $bm . str_repeat( ' ', self::bt - strlen($bm)) . '!');

		if ($this->pop3->connect())
		{
			if ($this->pop3->get_list())
			{
				foreach($this->pop3->messages as $message_id) $this->process_message($message_id);
			}
			else
			{
				$v = ' *** all done ***       *** all done ***       *** all done *** '; 
				$this->trace->log('!' . str_repeat( '-', self::bt) . '!');
				$this->trace->log('!' . str_repeat( ' ', 10) . $v . str_repeat( ' ', self::bt -10 - strlen($v)) . '!');
				$this->trace->log('!' . str_repeat( '-', self::bt) . '!');
				$this->trace->log('!' . str_repeat( ' ', 15) . $v . str_repeat( ' ', self::bt -15 - strlen($v)) . '!');
				$this->trace->log('!' . str_repeat( '-', self::bt) . '!');
				$this->trace->log('!' . str_repeat( ' ', 20) . $v . str_repeat( ' ', self::bt -20 - strlen($v)) . '!');
				$this->trace->log('!' . str_repeat( '-', self::bt) . '!');
				$return = false;
			}
			if (!$this->pop3->disconnect()) $return = false;
		}
		else $return = false;

		if ($return)
		{
			$bm = " end        !";
			$this->trace->log('!' . $bm . str_repeat( ' ', self::bt - strlen($bm)) . '!');
		}
		return $return;
	}

	function process_message($message_id)
	{
		if (!list($mail_id, $mp_user_id, $bounce_email) = $this->is_bounce($message_id)) return;

		$this->mysql_disconnect($x = 'MailPress_bounce_handling');
		$this->mysql_connect('MailPress_bounce_handling');

		$this->trace->log('!' . str_repeat( '-', self::bt) . '!');
		$bm = '            ! id         ! bounces   ! ' . $bounce_email;
		$this->trace->log('!' . $bm . str_repeat( ' ', self::bt - strlen($bm)) . '!');

		$user_logmess = $mail_logmess = '';
		$already_processed = $already_stored = false;

		MailPress::require_class('Users');

		if (!$mp_user = MP_Users::get($mp_user_id))
		{
			$user_logmess = '** WARNING ** user not in database'; 
			$usermeta['bounce'] = 0;
		}
		else
		{
			$bounce = array( 'message' => $this->pop3->message );

			MailPress::require_class('Usermeta');
			$usermeta = MP_Usermeta::get($mp_user_id, MailPress_bounce_handling::metakey);
			if (!$usermeta)
			{
				$usermeta = array();
				$usermeta['bounce'] = 1;
				$usermeta['bounces'][$mail_id][] = $bounce;	
				MP_Usermeta::add($mp_user_id, MailPress_bounce_handling::metakey, $usermeta);
			}
			else
			{
				if (!is_array($usermeta)) $usermeta = array();

				if (!isset($usermeta['bounces'][$mail_id])) 
				{
					$usermeta['bounces'][$mail_id] = array();

					if (!isset($usermeta['bounce'])) 		$usermeta['bounce'] = 1;
					elseif (!is_numeric($usermeta['bounce'])) $usermeta['bounce'] = 1;
					else $usermeta['bounce']++;
				}
				else
				{
					$already_processed = true;
					foreach($usermeta['bounces'][$mail_id] as $bounces)
					{
						if ($bounces['message'] == $bounce['message'])
						{
							$already_stored = true;
							break;
						}
					}
				}

				if (!$already_stored) $usermeta['bounces'][$mail_id][] = $bounce;

				MP_Usermeta::update($mp_user_id, MailPress_bounce_handling::metakey, $usermeta);
			}

			switch (true)
			{
				case $already_processed :
					$user_logmess = '-- notice -- bounce previously processed';
				break;
				case ('bounced' == $mp_user->status) :
					$user_logmess = ' <' . $mp_user->email . '> already ** BOUNCED **';
				break;
				case ($usermeta['bounce'] >= $this->bounce_handling_config['max_bounces']) :
					MP_Users::set_status($mp_user_id, 'bounced');
					$user_logmess = '** BOUNCED ** <' . $mp_user->email . '>';
				break;
				default :
					$user_logmess = 'new bounce for <' . $mp_user->email . '>';
				break;
			}
		}

		$bm  = ' user       ! ';
		$bm .= str_repeat(' ', 10 - strlen($mp_user_id) ) . $mp_user_id . ' !';
		$bm .= str_repeat(' ', 10 - strlen($usermeta['bounce']) ) . (($usermeta['bounce']) ? $usermeta['bounce'] : '') . ' !';
		$bm .= " $user_logmess";
		$this->trace->log('!' . $bm . str_repeat( ' ', self::bt - strlen($bm)) . '!');

		$mailmeta = '';
		if (!$already_processed)
		{
			MailPress::require_class('Mails');
			if (!$mail = MP_Mails::get($mail_id))
			{
				$mail_logmess = '** WARNING ** mail not in database';
			}
			else
			{
				MailPress::require_class('Mailmeta');
    
				$mailmeta = MP_Mailmeta::get($mail_id, MailPress_bounce_handling::metakey);
				$mailmeta = ($mailmeta) ? $mailmeta++ : 1;	
				MP_Mailmeta::update($mail_id, MailPress_bounce_handling::metakey, $mailmeta );
		
				$metas = MP_Mailmeta::get( $mail_id, '_MailPress_replacements');
				$mail_logmess = $mail->subject;
				if ($metas) foreach($metas as $k => $v) $mail_logmess = str_replace($k, $v, $mail_logmess);
				if ( strlen($mail_logmess) > 50 )	$mail_logmess = substr($mail_logmess, 0, 49) . '...';
			}
		}
		$bm  = ' mail       ! ';
		$bm .= str_repeat(' ', 10 - strlen($mail_id) )  . $mail_id . ' !';
		$bm .= str_repeat(' ', 10 - strlen($mailmeta) ) . $mailmeta . ' !';
		$bm .= " $mail_logmess";
		$this->trace->log('!' . $bm . str_repeat( ' ', self::bt - strlen($bm)) . '!');
	
		$this->trace->log('!' . str_repeat( '-', self::bt) . '!');
	}

	function is_bounce($message_id)
	{
		$this->pop3->get_headers_deep($message_id);

		if (isset($this->pop3->headers['To']))
		{
			if (is_array($this->pop3->headers['To']))
			{ 
				foreach($this->pop3->headers['To'] as $To) if (strpos ($To, $this->bounce_handling_config['Return-Path']) !== false) return false;
			}
			else if (strpos ( $this->pop3->headers['To'], $this->bounce_handling_config['Return-Path'] ) !== false) return false;
		}

		$prefix 	= preg_quote(substr($this->bounce_handling_config['Return-Path'], 0, strpos($this->bounce_handling_config['Return-Path'], '@')) . '+');
		$domain 	= preg_quote(substr($this->bounce_handling_config['Return-Path'], strpos($this->bounce_handling_config['Return-Path'], '@') + 1 ));

		$user_mask	= preg_quote('{{_user_id}}');

		$headers = array('Return-Path', 'To', 'Received');
		$_headers = array();
		
		foreach ($headers as $header)
		{
			if (isset($this->pop3->headers[$header]))
			{
				if (!is_array($this->pop3->headers[$header])) 			$_headers[] = $this->pop3->headers[$header];
				else foreach($this->pop3->headers[$header] as $_header) 	$_headers[] = $_header;
			}
		}

		foreach($_headers as $_header)
		{
			$pattern = $prefix . "[0-9]*\+[0-9]*@$domain";
			if (ereg($pattern, $_header))
			{
				$pattern = "/$prefix([0-9]*)\+([0-9]*)@$domain/";
				preg_match_all($pattern, $_header, $matches, PREG_SET_ORDER);
				if (empty($matches)) continue;
				$mail_id    = $matches[0][1];
				$mp_user_id = $matches[0][2];
			}
			else
			{
				$pattern = $prefix . "[0-9]*\+$user_mask@$domain";

				if (!ereg($pattern, $_header)) continue;

				$pattern = "/$prefix([0-9]*)\+$user_mask@$domain/";
				preg_match_all($pattern, $_header, $matches, PREG_SET_ORDER);

				if (empty($matches)) continue;

		        	$mail_id = $matches[0][1];
				MailPress::require_class('Mails');

				if (!$mail = MP_Mails::get($mail_id)) continue;

				if (!MailPress::is_email($mail->toemail)) continue;

				MailPress::require_class('Users');
				$mp_user_id = MP_Users::get_id_by_email($mail->toemail);
                
				if (!$mp_user_id) continue;
			}

			switch ($this->bounce_handling_config['mailbox_status'])
			{
				case 1 :
					$this->pop3->get_message($message_id);
				break;
				case 2 :
					$this->pop3->delete($message_id);
				break;
				default :
				break;
			}
			return array($mail_id, $mp_user_id, $matches[0][0]);
			break;
		}
		return false;
	}

	function mysql_disconnect($x = '0')
	{
		global $wpdb;
		if (isset($this->trace)) $this->trace->log("Disconnecting from " . DB_NAME . " ($x)");
		mysql_close($wpdb->dbh);
		if (isset($this->trace)) $this->trace->log("Disconnected ($x)");
	}

	function mysql_connect($x = '0')
	{
		global $wpdb;
		if (isset($this->trace)) $this->trace->log("Connecting to " . DB_NAME . " ($x)");

		$wpdb->__construct(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);

		if (isset($this->trace)) $this->trace->log("Connected ($x)");
	}
}
?>