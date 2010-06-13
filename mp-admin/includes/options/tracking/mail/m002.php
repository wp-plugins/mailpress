<?php
MailPress::require_class('Tracking_module_abstract');
class MP_Tracking_module_m002 extends MP_Tracking_module_abstract
{
	var $module = 'm002';

	function __construct()
	{
		$this->type  = basename(dirname(__FILE__));
		$this->title = __('Last 10 users', MP_TXTDOM);
		parent::__construct();
	}

	function meta_box($mail)
	{
		global $wpdb;
		$query = "SELECT DISTINCT DATE(tmstp) as tmstp, user_id FROM $wpdb->mp_tracks WHERE mail_id = " . $mail->id . " ORDER BY 1 DESC LIMIT 10;";
		$tracks = $wpdb->get_results($query);
		MP_AdminPage::require_class('Users');
		if ($tracks) foreach($tracks as $track)
		{
			$mp_user = MP_Users::get($track->user_id);
			echo $track->tmstp . ' ' . $mp_user->email . '<br />';
		} 
	}
}
$MP_Tracking_module_m002 = new MP_Tracking_module_m002();
?>