<?php
MailPress::require_class('Tracking_module_abstract');
class MP_Tracking_module_u001 extends MP_Tracking_module_abstract
{
	var $module = 'u001';

	function __construct()
	{
		$this->type  = basename(dirname(__FILE__));
		$this->title = __('Last 10 actions', MP_TXTDOM);
		parent::__construct();
	}

	function meta_box($mp_user)
	{
		global $wpdb;
		$query = "SELECT * FROM $wpdb->mp_tracks WHERE user_id = " . $mp_user->id . " ORDER BY tmstp DESC LIMIT 10;";
		$tracks = $wpdb->get_results($query);
		if ($tracks) foreach($tracks as $track) echo $track->tmstp . ' (' . $track->mail_id . ') ' . MailPress_tracking::translate_track($track->track, $track->mail_id) . '<br />';
	}
}
$MP_Tracking_module_u001 = new MP_Tracking_module_u001();
?>