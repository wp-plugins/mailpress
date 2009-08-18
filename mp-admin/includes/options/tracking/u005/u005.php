<?php
/*
u005
*/
	function meta_box_tracking_mp_u005($mp_user)
	{
		global $wpdb;

     		$query = "SELECT context, count(*) as count FROM $wpdb->mp_tracks WHERE user_id = " . $mp_user->id . " GROUP BY context ORDER BY context;";
		$tracks = $wpdb->get_results($query);
		$total = 0;
		if ($tracks)
		{
			foreach($tracks as $track)
			{
				$context[$track->context] = $track->count;
				$total += $track->count;
			}
			foreach($context as $k => $v)
			{
				echo '<b>' . $k . '</b> : &nbsp;' . sprintf("%01.2f %%",100 * $v/$total ) . '&nbsp;&nbsp;&nbsp;&nbsp;';
			}
			echo '<br />';
		}
		echo '<br />';
		$query = "SELECT DISTINCT agent, ip FROM $wpdb->mp_tracks WHERE user_id = " . $mp_user->id . " LIMIT 10;";
		$tracks = $wpdb->get_results($query);
		if ($tracks) foreach($tracks as $track) {echo MailPress_tracking::get_os($track->agent) . ' ' . MailPress_tracking::get_browser($track->agent) . '&nbsp;&nbsp;&nbsp;@&nbsp;' . $track->ip . '<br />'; }
	}
?>