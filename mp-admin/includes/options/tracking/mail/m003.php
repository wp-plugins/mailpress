<?php
MailPress::require_class('Tracking_module_abstract');
class MP_Tracking_module_m003 extends MP_Tracking_module_abstract
{
	var $module = 'm003';

	function __construct()
	{
		$this->type  = basename(dirname(__FILE__));
		$this->title = __('Opened/day', MP_TXTDOM);
		parent::__construct();
	}

	function meta_box($mail)
	{
		global $wpdb;
		$query = "SELECT DATE(tmstp) as tmstp, count(*) as count FROM $wpdb->mp_tracks WHERE mail_id = " . $mail->id . " AND track = '" . MailPress_tracking_openedmail . "' GROUP BY 1 ORDER BY 1 DESC ;";
		$tracks = $wpdb->get_results($query);
		if ($tracks) foreach($tracks as $track) echo $track->tmstp . ' <b>' . $track->count . '</b><br />';
	}
}
$MP_Tracking_module_m003 = new MP_Tracking_module_m003();
?>