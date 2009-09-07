<?php
class MP_Batch
{
	function __construct()
	{
		$this->batch_send_config 	= get_option('MailPress_batch_send');
		$this->batch_report		= array();

		MailPress::require_class('Log');
		$this->trace = new MP_Log('mp_process_batch_send', ABSPATH . MP_PATH, __CLASS__, false, 'batch_send');

		$this->process();
		if ($this->have_batch()) do_action('MailPress_schedule_batch_send');

		$this->report_batch();
		$this->trace->end(true);
	}

// have_batch
	function have_batch()
	{
		global $wpdb;
		return $wpdb->get_var("SELECT count(*) FROM $wpdb->mp_mails WHERE status = '" . MailPress_batch_send::status_mail() . "' ;");
	}

// process
	function process()
	{
	// select mail
		global $wpdb;
		$query = "SELECT * FROM $wpdb->mp_mails WHERE status = '" . MailPress_batch_send::status_mail() . "' ;";
		$mails = $wpdb->get_results($query);

		if (!$mails) { $this->alldone(); return; }

		$mail = $this->mail = $mailmeta = $this->mailmeta = false;
		$this->mailmeta['try'] = 10000;

		MailPress::require_class('Mailmeta');

		foreach ($mails as $mail)
		{
			$mailmeta = $this->get_mailmeta($mail);
			if (!$mailmeta) continue;

			if ($mailmeta['try'] < $this->mailmeta['try'])
			{
				$this->mail 	= $mail;
				$this->mailmeta	= $mailmeta;
			}
		}

		if (!$this->mail) { $this->alldone(); return; }
		unset($mails, $mail, $mailmeta);

		$this->mailmeta['pass']++;
		$this->batch_report['header'] = 'Batch Report mail #' . $this->mail->id . '  / count : ' . $this->mailmeta['count'] . ' / per_pass : ' . $this->batch_send_config['per_pass'] . ' / max_try : ' . $this->batch_send_config['max_retry'];
		$this->batch_report['start']  = $this->mailmeta;

	// select recipients
		$recipients = unserialize($this->mail->toemail);
		$this->toemail= array();

		if ($this->mailmeta['try'])
		{
			$this->toemail 	= array_slice($this->mailmeta['failed'], 	$this->mailmeta['offset'], $this->batch_send_config['per_pass'], true);
			foreach($this->toemail as $k => $v) $this->toemail[$k] = $recipients [$k];
		}
		else $this->toemail 	= array_slice($recipients, 			$this->mailmeta['offset'], $this->batch_send_config['per_pass'], true);

		$count_recipients = count($this->toemail);

	// processing
		if (!$count_recipients)
		{
			$this->batch_report['processing']  = array_merge($this->mailmeta, array( ">> WARNING >>" => 'No more recipient' ) );
			$this->mailmeta['processed'] = $this->mailmeta['offset'] = $this->mailmeta['pass'] = 0;
			$this->mailmeta['try']++;
		}
		else
		{
			$this->mailmeta['processed'] += $count_recipients;
			$this->batch_report['processing'] = $this->mailmeta;
			$this->report_batch();

	// saving context, if abort, current recipients will be on error & next recipients will be processed.
			$this->mailmeta['offset'] += $count_recipients;

			$maybe_failures = array_flip(array_keys($this->toemail));
			if ($this->mailmeta['try']) 	$this->mailmeta['failed'] = array_merge($maybe_failures, $this->mailmeta['failed']);
			else  				$this->mailmeta['failed'] = array_merge($this->mailmeta['failed'], $maybe_failures);

   			MP_Mailmeta::update( $this->mail->id, MailPress_batch_send::metakey, $this->mailmeta );
			$this->trace->restart();

	// sending
			$swiftfailed = $this->send();

	// results
			switch (true)
			{
				case (is_array($swiftfailed)) : 
					$ko = array_flip($swiftfailed);
					$ok = array_diff_key($this->toemail, $ko);
				break;
				case (!$swiftfailed) : 
					$ko = $maybe_failures ;
					$ok = array();
				break;
				default : 
					$ko = array();
					$ok = $maybe_failures ;
				break;
			}

			$count_sent  = count($ok);
			$this->mailmeta['sent'] += $count_sent;

			foreach ($ok as $k => $v)
			{
				unset($this->mailmeta['failed'][$k]);
				if ($this->mailmeta['try']) $this->mailmeta['offset']-- ;
			}
			if ($this->mailmeta['try'])	$this->mailmeta['failed'] = array_merge($ko, $this->mailmeta['failed']);
			else  $this->mailmeta['failed'] = array_merge($this->mailmeta['failed'], $ko);
		}
	// saving context
		$this->batch_report['end']  = $this->mailmeta;
		MP_Mailmeta::update( $this->mail->id, MailPress_batch_send::metakey, $this->mailmeta );


	// the end for this mail ?
		if ($this->mailmeta['sent'] == $this->mailmeta['count']) 				self::update_mail($this->mail->id);
		if ($this->mailmeta['try']  >= $this->batch_send_config['max_retry'] + 1) 	self::update_mail($this->mail->id, count($this->mailmeta['failed']));
	}

// get mailmeta
	function get_mailmeta($mail)
	{
		$mailmeta = MP_Mailmeta::get( $mail->id , MailPress_batch_send::metakey);

		if (!$mailmeta)
		{
			$mailmeta = array();

			if (is_serialized ($mail->toemail))	$mailmeta['count'] = count(unserialize($mail->toemail));
			else						$mailmeta['count'] = 1;

			$mailmeta['sent'] = $mailmeta['try'] = 0;
			$mailmeta['processed'] = $mailmeta['offset'] = $mailmeta['pass'] = 0;
			$mailmeta['failed'] = array();
			return $mailmeta;
		}

		if (isset($mailmeta['per_pass'])) // convert old format prior to mailpress 4.0
		{
			if (!$mailmeta['try']) $mailmeta['offset'] = ($mailmeta['pass']) ? ($mailmeta['pass'] - 1) * $mailmeta['per_pass'] : 0;
			unset($mailmeta['per_pass'], $mailmeta['max_try']);
		}

		$failed = (isset($mailmeta['failed'])) ? count($mailmeta['failed']) : 0;

		if ($mailmeta['sent'] == $mailmeta['count']) { self::update_mail($mail->id, $failed); return false; }

		$processed = ($mailmeta['try']) ? $mailmeta['offset'] : $mailmeta['processed'];
		$count     = ($mailmeta['try']) ? $failed : $mailmeta['count'];

		if ($processed >= $count) 
		{
			$mailmeta['processed'] = $mailmeta['offset'] = $mailmeta['pass'] = 0;
			$mailmeta['try']++;			
		}

		if ($mailmeta['try'] >= $this->batch_send_config['max_retry'] + 1) { self::update_mail($mail->id, $failed); return false; }
		if ($mailmeta['try'] && !$failed) 						 { self::update_mail($mail->id, $failed); return false; }

		return $mailmeta;
	}

// finish
	public static function update_mail($id, $failed = false)
	{
		global $wpdb;
				
		$query = "UPDATE $wpdb->mp_mails SET status = 'sent' WHERE id = $id";
		$x = $wpdb->query( $query );
		if (!$failed) MP_Mailmeta::delete( $id , MailPress_batch_send::metakey);
	}

// batch sending
	function send()
	{
// instaure the context
		MailPress::require_class('Mail');
		$_this = new MP_Mail(__CLASS__);

		$_this->trace 				= $this->trace;

		$_this->mail 				= new stdClass();
		$_this->mail->swift_batchSend 	= true;
		$_this->mail->mailpress_batch_send 	= true;

		$_this->row 				=  new stdClass();
		$_this->row 				= $this->mail;
		$_this->args 				= new stdClass();

		$_this->args->replacements 		= $this->toemail;
		$_this->get_old_recipients();

		MailPress::require_class('Mailmeta');
		$m = MP_Mailmeta::get($_this->row->id, '_MailPress_replacements');
		if (!is_array($m)) $m = array();
		$_this->mail->replacements = $m;

		add_filter('MailPress_swift_send', array(&$this, 'swift_send'), 8, 1);
		return $_this->swift_processing(); // will activate swift_send function
	}

// send
	function swift_send($_this)
	{
		if ($_this->mail->mailpress_batch_send)
		{
			$_this->mysql_disconnect('MailPress_batch_send');

			$_this->swift->registerPlugin(new Swift_Plugins_DecoratorPlugin($_this->row->replacements));
			if (!$_this->swift->batchSend($_this->message, $failures))
			{
				$_this->mysql_connect('MailPress_batch_send 2');
				return false;
			}
			$_this->mysql_connect('MailPress_batch_send');
			return $failures;
		}
		return true;
	}

//reports
	function alldone()
	{
		$this->batch_report['header2'] = 'Batch Report';
		$this->batch_report['alldone']  = true;
	}

	function report_batch($zz = 12)
	{
		$order = array('sent', 'processed', 'try', 'pass', 'offset', 'failed');
		$unsets = array(  'count', 'per_pass', 'max_try' );
		$t = (count($order) + 1) * ($zz + 1) -1;

		foreach($this->batch_report as $k => $v)
		{
			switch ($k)
			{
				case 'header' :
					$this->trace->log('!' . str_repeat( '-', $t) . '!');
					$l = strlen($v);
					$this->trace->log('!' . str_repeat( ' ', 5) . $v . str_repeat( ' ', $t - 5 - $l) . '!');
					$this->trace->log('!' . str_repeat( '-', $t) . '!');
					$s = '!            !';
					foreach($order as $o)
					{
						$l = strlen($o);
						$s .= " $o" . str_repeat( ' ', $zz - $l -1) . '!';
					}
					$this->trace->log($s);
					$this->trace->log('!' . str_repeat( '-', $t) . '!');
				break;
				case 'header2' :
					$t = count($order) * 15;
					$this->trace->log('!' . str_repeat( '-', $t) . '!');
					$l = strlen($v);
					$this->trace->log('!' . str_repeat( ' ', 5) . $v . str_repeat( ' ', $t - 5 - $l) . '!');
					$this->trace->log('!' . str_repeat( '-', $t) . '!');
				break;
				case 'alldone' :
					$t = count($order) * 15;
					$v = ' *** all done ***       *** all done ***       *** all done *** '; 
					$l = strlen($v);
					$this->trace->log('!' . str_repeat( ' ', 10) . $v . str_repeat( ' ', $t -10 - $l) . '!');
					$this->trace->log('!' . str_repeat( '-', $t) . '!');
					$this->trace->log('!' . str_repeat( ' ', 15) . $v . str_repeat( ' ', $t -15 - $l) . '!');
					$this->trace->log('!' . str_repeat( '-', $t) . '!');
					$this->trace->log('!' . str_repeat( ' ', 20) . $v . str_repeat( ' ', $t -20 - $l) . '!');
				break;
				case 'end' :
					$this->trace->log('!' . str_repeat( '-', $t) . '!');
				default :
					foreach ($unsets as $unset) unset($v[$unset]);
					$c = 0;
					$l = strlen($k);
					$s = "! $k" . str_repeat( ' ', $zz - $l -1) . '!';
					foreach($order as $o)
					{
						if (isset($v[$o])) { if (is_array($v[$o])) $v[$o] = count($v[$o]); $l = strlen($v[$o]); $s .= str_repeat( ' ', $zz - $l -1) . $v[$o] .  ' !'; unset($v[$o]); $c++;}
					}
					if ($c < count($order)) do { $s.= str_repeat( ' ', $zz) . '!'; $c++;} while($c <  count($order));
					$this->trace->log($s);
					if (!empty($v)) foreach($v as $a => $b) $this->trace->log("$a $b");
				break;
			}
		}
		$this->trace->log('!' . str_repeat( '-', $t) . '!');
		$this->batch_report = array();
	}
}
?>