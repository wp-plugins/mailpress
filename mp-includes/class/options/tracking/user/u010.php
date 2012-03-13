<?php
class MP_Tracking_module_u010 extends MP_tracking_module_
{
	var $id	= 'u010';
	var $context= 'side';
	var $file 	= __FILE__;

	function __construct($title)
	{
		if (!class_exists('MP_Useragent_agents', false)) new MP_Useragent_agents();
		parent::__construct($title);
	}

	function meta_box($mp_user)
	{
		global $wpdb;

		$tracks = $wpdb->get_results( $wpdb->prepare( "SELECT context, count(*) as count FROM $wpdb->mp_tracks WHERE user_id = %d AND mail_id <> 0 GROUP BY context ORDER BY context;", $mp_user->id) );

		if ($tracks)
		{
			$total = 0;
			foreach($tracks as $track)
			{
				$context[$track->context] = $track->count;
				$total += $track->count;
			}
			foreach($context as $k => $v)
			{
				echo '<b>' . $k . '</b> : &#160;' . sprintf("%01.2f %%",100 * $v/$total ) . '&#160;&#160;&#160;&#160;';
			}
			echo '<br />';
		}
		echo '<br />';
		$tracks = $wpdb->get_results( $wpdb->prepare( "SELECT agent, count(*) as count FROM $wpdb->mp_tracks WHERE user_id = %d GROUP BY agent ORDER BY agent;", $mp_user->id) );

		if ($tracks)
		{
			$total = 0;
			foreach($tracks as $track)
			{
				$agent[$track->agent] = $track->count;
				$total += $track->count;
			}
//os //browser 
			foreach($agent as $k => $v)
			{
				$ug = apply_filters('MailPress_useragent_os_get',      $k);
				$key = $ug->name;
				if (isset($os[$key]['count'])) 	$os[$key]['count'] += $v;
				else 						$os[$key]['count']  = $v;
				if (isset($ug->icon_path) && !isset($os[$key]['img'])) $os[$key]['img'] = $ug->icon_path;

				$ug = apply_filters('MailPress_useragent_browser_get',      $k);
				$key = $ug->name;
				if (isset($browser[$key]['count']))	$browser[$key]['count'] += $v;
				else 							$browser[$key]['count']  = $v;
				if (isset($ug->icon_path) && !isset($browser[$key]['img'])) $browser[$key]['img'] = $ug->icon_path;
			}
			arsort($os); arsort($browser);

			echo '<table width="100%"><tr><th width="45%">' . __('os', MP_TXTDOM) . '</th><th width="10%"></th><th width="45%">' . __('browser', MP_TXTDOM) . '</th></tr><tr><td style="vertical-align:top;"><table width="100%">';
			foreach($os as $k => $v)
			{
				echo '<tr><td><img src="' . $v['img'] . '" alt="" /> ' . $k . '</td><td style="text-align:right;">' . sprintf("%01.2f %%",100 * $v['count']/$total ) . '</td></tr>';
			}
			echo '</table></td><td></td><td style="vertical-align:top;"><table width="100%">';
			foreach($browser as $k => $v)
			{
				echo '<tr><td><img src="' . $v['img'] . '" alt="" /> ' . $k . '</td><td style="text-align:right;">' . sprintf("%01.2f %%",100 * $v['count']/$total ) . '</td></tr>';
			}
			echo '</table></td></tr></table>';
		}
	}
}
new MP_Tracking_module_u010(__('System info bis', MP_TXTDOM));