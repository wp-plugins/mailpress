<?php
/*
m002 last 10 users
*/
	function meta_box_tracking_mp_m002($mail)
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
?>