<?php
class MP_Bounce_II extends MP_bounce_
{
	public $option_name	= MailPress_bounce_handling_II::option_name;
	public $option_name_pop3= MailPress_bounce_handling_II::option_name_pop3;
	public $meta_key	= MailPress_bounce_handling_II::meta_key;

	public $class		= __CLASS__;
	public $log_name 	= 'mp_process_bounce_handling_II';
	public $log_option_name = 'bounce_handling_II';
	public $log_title 	= 'Bounce Handling II Report (Bounce in mailbox : %1$s )';

	public $cron_name 	= 'MailPress_schedule_bounce_handling_II';


	private $subject_bounce= array();
	private $subject_regex = '';

	private $body_bounce   = array();
	private $body_regex    = '';

	function __construct()
	{
		$root = MP_CONTENT_DIR . 'advanced/bounces';
		$root = apply_filters('MailPress_advanced_bounces_root', $root);
		$file	= "$root/II.xml";

		$y = '';

		if (!is_file($file)) return;

		$x = file_get_contents($file);
		if ($xml = simplexml_load_string($x))

		foreach ($xml->subject->text as $text) $this->subject_bounce[] = preg_quote((string) $text, '~');
		$this->subject_regex = '~(' . implode('|', $this->subject_bounce) . ')~i';

		$this->body_regex = (string) $xml->body->regex;
		foreach ($xml->body->text as $text) $this->body_bounce[] = (string) $text;

		parent::__construct();
	}

	function is_bounce()
	{
		$tags = array('Subject', 'X-MailPress-blog-id', 'X-MailPress-mail-id', 'X-MailPress-user-id');
		$this->pop3->get_headers_deep($this->message_id, $tags);

		$blog_id = $this->get_tag('X-MailPress-blog-id', 1);
		if (false === $blog_id) return false;
		global $wpdb;
		if ($blog_id != $wpdb->blogid) return false;

		$mail_id = $this->get_tag('X-MailPress-mail-id', 1);
		if (false === $mail_id) return false;

		$mp_user_id = $this->get_tag('X-MailPress-user-id', 1);
		if (false === $mp_user_id)
		{
			if (!$mail = MP_Mail::get($mail_id)) return false;

			if (!is_email($mail->toemail)) return false;

			$mp_user_id = MP_User::get_id_by_email($mail->toemail);
                
			if (!$mp_user_id) return false;
		}

		// detect message is bounce

		$subject = $this->get_tag('Subject');
		$subject = trim($subject);
		$subject = strtolower($subject);
		if (empty($subject) || !preg_match($this->subject_regex, $subject)) return false;

		if (!$this->parse_body($this->get_body(0))) return false;

		$this->process_mailbox_status();

		return array($mail_id, $mp_user_id, "mail : $mail_id & user : $mp_user_id");
	}

	function parse_body($body)
	{
		$status = array();
		if (preg_match($this->body_regex, $body, $matches)) 
		{
			if (3 == count($matches)) preg_match('/Status\s*?\:\s*?([2|4|5]+)\.(\d{1,3}).(\d{1,3})/is', $matches[2], $status);
			unset($matches);
		}

		if (4 == count($status)) 
		{
			$bounce = false;
			switch ($status[1]) 
			{
				case 2:
					$bounce = true;
					break;
				case 4:
					$bounce = $this->RFC_3463_4($status[2], $status[3]);
					break;
				case 5:
					if (5 == $status[2] && 0 == $status[3]) break;
					$bounce = $this->RFC_3463_5($status[2], $status[3]);
					break;
			}
			if ($bounce) return true;
		}
		// -----

		foreach ($this->body_bounce as $rule) if (preg_match('%' . preg_quote($rule) . '%is', $body)) return true;

		return false;
	}

	// RFC 3463 - Enhanced Mail System Status Codes

	function RFC_3463_4($code, $subcode)
	{
		if ((5 == $code) && (3 == $subcode)) return true;
		return false;
	}

	function RFC_3463_5($code, $subcode)
	{
		switch ($code)
		{
			case '1': 
				if (in_array($subcode, array('0','1','2','3','4','5','6'))) return true;
				break;
			default :
				if (in_array($code, array('2','3','4','5','6','7'))) return true;
				break;
		}
		return false;
	}
}