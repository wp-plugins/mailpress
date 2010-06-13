<?php
MailPress::require_class('Tracking_module_abstract');
class MP_Tracking_module_u004 extends MP_Tracking_module_abstract
{
	var $module = 'u004';

	function __construct()
	{
		$this->type  = basename(dirname(__FILE__));
		$this->title = __('Clicks/day', MP_TXTDOM);
		parent::__construct();
	}

	function meta_box($mp_user)
	{
		global $wpdb;
		$query = "SELECT DATE(tmstp) as tmstp, count(*) as count FROM $wpdb->mp_tracks WHERE user_id = " . $mp_user->id . " AND track <> '" . MailPress_tracking_openedmail . "' GROUP BY 1 ORDER BY 1 DESC ;";
		$tracks = $wpdb->get_results($query);
		if ($tracks) foreach($tracks as $track) echo $track->tmstp . ' <b>' . $track->count . '</b><br />';
	}
}
$MP_Tracking_module_u004 = new MP_Tracking_module_u004();
?>