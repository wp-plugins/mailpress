<?php
class MP_Connection_spool extends MP_connection_
{
	public $Swift_Connection_type = 'spool';

	function connect($mail_id, $y)
	{
		$settings = get_option(MailPress_batch_spool_send::option_name);

		$p = false;
		if (!empty($settings['path'])) $p = MailPress_batch_spool_send::is_path($settings['path'] . "/$mail_id");
		if (!$p) $p = MailPress_batch_spool_send::is_path(MP_ABSPATH . 'tmp/spool/' . $mail_id); 

		$y->log("**** Spool path is : $p ****");

		$spool =  new Swift_FileSpool($p);

		return Swift_SpoolTransport::newInstance($spool);
	}
}