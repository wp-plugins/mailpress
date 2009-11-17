<?php
MailPress::require_class('Tracking_module_abstract');
class MP_Tracking_module_m001 extends MP_Tracking_module_abstract
{
	var $module = 'm001';

	function __construct()
	{
		$this->type  = basename(dirname(__FILE__));
		$this->title = __('Last 10 actions','MailPress');
		parent::__construct();
	}

	function meta_box($mail)
	{
		global $wpdb;
		$query = "SELECT * FROM $wpdb->mp_tracks WHERE mail_id = " . $mail->id . " ORDER BY tmstp DESC LIMIT 10;";
		$tracks = $wpdb->get_results($query);
		if ($tracks) 
		{
			echo '<table>';

MP_AdminPage::require_class('Users');
foreach($tracks as $track) echo '<tr><td>' . $track->tmstp . '</td><td>&nbsp;' . MP_Users::get_email($track->user_id) . '</td><td>&nbsp;' . MailPress_tracking::translate_track($track->track, $track->mail_id) . '</td></tr>';
			echo '</table>';
		}
	}
}
$MP_Tracking_module_m001 = new MP_Tracking_module_m001();
?>