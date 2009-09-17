<?php
MailPress::require_class('Tracking_module_abstract');
class MP_Tracking_module_m005 extends MP_Tracking_module_abstract
{
	var $module = 'm005';

	function __construct()
	{
		$this->type  = basename(dirname(__FILE__));
		$this->title = __('System info','MailPress');
		parent::__construct();
	}

	function meta_box($mail)
	{
		global $wpdb;

     		$query = "SELECT context, count(*) as count FROM $wpdb->mp_tracks WHERE mail_id = " . $mail->id . " GROUP BY context ORDER BY context;";
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
		$query = "SELECT agent, count(*) as count FROM $wpdb->mp_tracks WHERE mail_id = " . $mail->id . "  GROUP BY agent ORDER BY agent LIMIT 10;";
		$tracks = $wpdb->get_results($query);

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
$MP_Tracking_module_m005 = new MP_Tracking_module_m005();
?>