<?php
/*
u002 last 10 mails
*/
	function meta_box_tracking_mp_u002($mp_user)
	{
		global $wpdb;
		$query = "SELECT * FROM $wpdb->mp_usermeta WHERE user_id = " . $mp_user->id . " AND meta_key = '_MailPress_mail_sent' ORDER BY umeta_id DESC LIMIT 10;";
		$tracks = $wpdb->get_results($query);
		if ($tracks) foreach($tracks as $track)
		{
			$subject = $wpdb->get_var("SELECT subject FROM $wpdb->mp_mails WHERE id = " . $track->meta_value . ';');
			$subject = ($subject) ? $subject : __('(deleted)','MailPress');
			echo '(' . $track->meta_value . ') ' . $subject . '<br />';
		} 
	}
?>