<?php
/*
u001 last 10 actions
*/
	function meta_box_tracking_mp_u001($mp_user)
	{
		global $wpdb;
		$query = "SELECT * FROM $wpdb->mp_tracks WHERE user_id = " . $mp_user->id . " ORDER BY tmstp DESC LIMIT 10;";
		$tracks = $wpdb->get_results($query);
		if ($tracks) foreach($tracks as $track) echo $track->tmstp . ' (' . $track->mail_id . ') ' . MailPress_tracking::translate_track($track->track, $track->mail_id) . '<br />';
	}
?>