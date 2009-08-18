<?php
/*
m003 opened/day
*/

	function meta_box_tracking_mp_m003($mail)
	{
		global $wpdb;
		$query = "SELECT DATE(tmstp) as tmstp, count(*) as count FROM $wpdb->mp_tracks WHERE mail_id = " . $mail->id . " AND track = '" . MailPress_tracking_openedmail . "' GROUP BY 1 ORDER BY 1 DESC ;";
		$tracks = $wpdb->get_results($query);
		if ($tracks) foreach($tracks as $track) echo $track->tmstp . ' <b>' . $track->count . '</b><br />';
	}
?>