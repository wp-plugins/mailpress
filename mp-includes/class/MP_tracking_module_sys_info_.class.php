<?php
abstract class MP_tracking_module_sys_info_ extends MP_tracking_module_
{
	var $query = true;

	function __construct($title)
	{
		if (!class_exists('MP_Useragent_agents', false)) new MP_Useragent_agents();
		parent::__construct($title);
	}

	function meta_box($item)
	{
		global $wpdb;

		$tracks = $wpdb->get_results( $wpdb->prepare( "SELECT context, count(*) as count FROM $wpdb->mp_tracks WHERE $this->item_id = %d AND mail_id <> 0 GROUP BY context ORDER BY context;", $item->id) );

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

		if (!$this->query) return $this->extended_meta_box($item);

		$tracks = $wpdb->get_results( $wpdb->prepare( "SELECT agent, count(*) as count FROM $wpdb->mp_tracks WHERE $this->item_id = %d GROUP BY agent ORDER BY count DESC;", $item->id) );
		if ($tracks) $this->extended_meta_box($tracks);
	}

	function _010($tracks)
	{
		echo '<br />';
		$total = 0; $first = true;
		foreach($tracks as $track)
		{
			$agent[$track->agent] = $track->count;
			$total += $track->count;
		}

		echo '<table width="100%"><tr><th width="45%">' . __('os', MP_TXTDOM) . '</th><th width="10%"></th><th width="45%">' . __('browser', MP_TXTDOM) . '</th></tr><tr><td style="vertical-align:top;"><table width="100%">';
		foreach(array('os', 'browser') as $device)
		{
			$x = array();
			foreach($agent as $k => $v)
			{
				$ug = apply_filters('MailPress_useragent_' . $device . '_get',      $k);
				$key = $ug->name;
				if (isset($x[$key]['count'])) 	$x[$key]['count'] += $v;
				else 						$x[$key]['count']  = $v;
				if (isset($ug->icon_path) && !isset($x[$key]['img'])) $x[$key]['img'] = $ug->icon_path;
			}
			arsort($x);
			foreach($x as $k => $v)
			{
				if (empty($k)) $k = __('others', MP_TXTDOM);
				echo '<tr><td><img src="' . $v['img'] . '" alt="" /> ' . $k . '</td><td style="text-align:right;">' . sprintf("%01.2f %%",100 * $v['count']/$total ) . '</td></tr>';
			}
			if ($first) echo '</table></td><td></td><td style="vertical-align:top;"><table width="100%">';
			$first = false;
		}
		echo '</table></td></tr></table>';
	}
}