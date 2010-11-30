<?php
class MP_Tracking_module_m005 extends MP_Tracking_module_abstract
{
	var $module = 'm005';
	var $context = 'normal';
	var $file = __FILE__;

	function meta_box($mail)
	{
		global $wpdb;

		$tracks = $wpdb->get_results( $wpdb->prepare( "SELECT context, count(*) as count FROM $wpdb->mp_tracks WHERE mail_id = %d GROUP BY context ORDER BY context;", $mail->id) );
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
		$tracks = $wpdb->get_results( $wpdb->prepare( "SELECT agent, count(*) as count FROM $wpdb->mp_tracks WHERE mail_id = %d GROUP BY agent ORDER BY agent LIMIT 10;", $mail->id) );

		if ($tracks)
		{
			$total = 0;
			foreach($tracks as $track)
			{
				$agent[$track->agent] = $track->count;
				$total += $track->count;
			}
			foreach($agent as $k => $v)
			{
				echo MailPress_tracking::get_os($k) . ' ' . MailPress_tracking::get_browser($k) . ' : &nbsp;' . sprintf("%01.2f %%",100 * $v/$total ) . '<br />';
			}
		}
	}
}
new MP_Tracking_module_m005(__('System info', MP_TXTDOM));