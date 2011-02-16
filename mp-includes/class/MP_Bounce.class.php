<?php
class MP_Bounce extends MP_Db_connect
{
	const bt = 132;

	function __construct()
	{
		$this->config = get_option(MailPress_bounce_handling::option_name);
		if (!$this->config) return;

		$this->trace = new MP_Log('mp_process_bounce_handling', MP_ABSPATH, __CLASS__, false, 'bounce_handling');

		$xmailboxstatus = array(	0	=>	'no changes',
							1	=>	'mark as read',
							2	=>	'delete' );

		$this->trace->log('!' . str_repeat( '-', self::bt) . '!');
		$bm = "Bounce Handling Report (Bounce in mailbox : " . $xmailboxstatus[$this->config['mailbox_status']] . ")";
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
		$pop3 = get_option(MailPress_bounce_handling::option_name_pop3);

		$this->pop3 = new MP_Pop3($pop3['server'], $pop3['port'], $pop3['username'], $pop3['password'], $this->trace);

		$bm = ' connecting ! ' . $pop3['server'] . ':' . $pop3['port'];
		$this->trace->log('!' . $bm . str_repeat( ' ', self::bt - strlen($bm)) . '!');

		if ($this->pop3->connect())
		{
			if ($this->pop3->get_list())
			{
				foreach($this->pop3->messages as $message_id) $this->process_message($message_id);
			}
			else
			{
				$v = ' *** ALL DONE ***       *** ALL DONE ***       *** ALL DONE *** '; 
				$this->trace->log('!' . str_repeat( '-', self::bt) . '!');
				$this->trace->log('!' . str_repeat( ' ', 15) . $v . str_repeat( ' ', self::bt -15 - strlen($v)) . '!');
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
		if (!isset($mail_id, $mp_user_id, $bounce_email)) return;

		$this->mysql_disconnect(__CLASS__);
		$this->mysql_connect(__CLASS__);

		$this->trace->log('!' . str_repeat( '-', self::bt) . '!');
		$bm = '            ! id         ! bounces   ! ' . $bounce_email;
		$this->trace->log('!' . $bm . str_repeat( ' ', self::bt - strlen($bm)) . '!');

		$user_logmess = $mail_logmess = '';
		$already_processed = $already_stored = false;

		if (!$mp_user = MP_Users::get($mp_user_id))
		{
			$user_logmess = '** WARNING ** user not in database'; 
			$usermeta['bounce'] = 0;
		}
		else
		{
			$bounce = array( 'message' => $this->pop3->message );

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

				if (!MP_Usermeta::add(    $mp_user_id, MailPress_bounce_handling::metakey, $usermeta, true))
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
				case ($usermeta['bounce'] >= $this->config['max_bounces']) :
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
			switch (true)
			{
				case (-1 == $mail_id) :
					$mail_logmess = '** WARNING ** mail unknown';
				break;
				case (!$mail = MP_Mails::get($mail_id)) :
					$mail_logmess = '** WARNING ** mail not in database';
				break;
				default :
					$mailmeta = MP_Mailmeta::get($mail_id, MailPress_bounce_handling::metakey);
					$mailmeta = ($mailmeta) ? $mailmeta++ : 1;
					if (!MP_Mailmeta::add($mail_id, MailPress_bounce_handling::metakey, $mailmeta , true))
						MP_Mailmeta::update($mail_id, MailPress_bounce_handling::metakey, $mailmeta );
					$metas = MP_Mailmeta::get( $mail_id, '_MailPress_replacements');
					$mail_logmess = $mail->subject;
					if ($metas) foreach($metas as $k => $v) $mail_logmess = str_replace($k, $v, $mail_logmess);
					if ( strlen($mail_logmess) > 50 )	$mail_logmess = substr($mail_logmess, 0, 49) . '...';
				break;
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
		$tags = array('Return-Path', 'Return-path', 'Received', 'To', 'X-Failed-Recipients', 'Final-Recipient');
		$this->pop3->get_headers_deep($message_id, $tags);

		$prefix 	= preg_quote(substr($this->config['Return-Path'], 0, strpos($this->config['Return-Path'], '@')) . '+');
		$domain 	= preg_quote(substr($this->config['Return-Path'], strpos($this->config['Return-Path'], '@') + 1 ));
		$user_mask	= preg_quote('{{_user_id}}');

		foreach($this->pop3->headers as $tag => $headers)
		{
			foreach($headers as $header)
			{
				if (strpos($header, $this->config['Return-Path']) !== false) continue;

				switch (true)
				{
					case (preg_match("#{$prefix}[0-9]*\+[0-9]*@{$domain}#", $header)) :
						preg_match_all("/{$prefix}([0-9]*)\+([0-9]*)@{$domain}/", $header, $matches, PREG_SET_ORDER);
						if (empty($matches)) continue;
						$bounce_email	= $matches[0][0];
						$mail_id		= $matches[0][1];
						$mp_user_id		= $matches[0][2];
					break;
					case (preg_match("#{$prefix}[0-9]*\+$user_mask@{$domain}#", $header)) :
						preg_match_all("/$prefix([0-9]*)\+$user_mask@$domain/", $header, $matches, PREG_SET_ORDER);
						if (empty($matches)) continue;
						$bounce_email	= $matches[0][0];
						$mail_id		= $matches[0][1];
						if (!$mail = MP_Mails::get($mail_id)) continue;
						if (!is_email($mail->toemail)) continue;
						$mp_user_id 	= MP_Users::get_id_by_email($mail->toemail);
						if (!$mp_user_id) continue;
					break;
					case (preg_match_all("/[a-zA-Z0-9!#$%&\'*+\/=?^_`{|}~\.-]+@[\._a-zA-Z0-9-]{2,}+/i", $header, $matches, PREG_SET_ORDER) && ($bounce_email = is_email($matches[0][0])) ) :
						switch($tag)
						{
							case 'X-Failed-Recipients' :
							case 'Final-Recipient' :
								$mail_id = -1;
								$mp_user_id = MP_Users::get_id_by_email($bounce_email);
								if (!$mp_user_id) continue;
							break;
							default :
								continue;
							break;
						}
					break;
					default :
						continue;
					break;
				}
			}

			switch ($this->config['mailbox_status'])
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
			return array($mail_id, $mp_user_id, $bounce_email);
			break;
		}
		return false;
	}
}