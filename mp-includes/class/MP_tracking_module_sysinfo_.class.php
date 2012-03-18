<?php
abstract class MP_tracking_module_sysinfo_ extends MP_tracking_module_
{
	var $query = true;

	function __construct($title)
	{
		if (!class_exists('MP_Tracking_agents', false)) new MP_Tracking_agents();
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
		$total = $z = 0;
		foreach($tracks as $track)
		{
			$agent[$track->agent] = $track->count;
			$total += $track->count;
		}

		$items = MP_Tracking_agents::get_all();
		$count = count($items);

		$int_width = 3;
		$width = (100 - ($int_width * ($count - 1)))/$count;

		echo '<table id ="tracking_mp_010"><tr>';
		foreach($items as $item => $desc)
		{
			echo "<th class='border' width='{$width}%'>$desc</th>";
			if (++$z != $count) echo "<th width='{$int_width}%'></th>";
		}
		echo '</tr><tr>';

		$z = 0;
		foreach($items as $item => $desc)
		{
			$x = array();
			foreach($agent as $k => $v)
			{
				$ug = apply_filters('MailPress_tracking_agents_' . $item . '_get',      $k);
				$key = $ug->name;
				if (isset($x[$key]['count'])) 	$x[$key]['count'] += $v;
				else 						$x[$key]['count']  = $v;
				if (isset($ug->icon_path) && !isset($x[$key]['img'])) $x[$key]['img'] = $ug->icon_path;
			}
			arsort($x);

			echo '<td class="border"><table>';
			foreach($x as $k => $v)
			{
				echo '<tr><td>';
				if (isset($v['img'])) echo '<img src="' . $v['img'] . '" alt="" /> ';
				echo (empty($k)) ? __('others', MP_TXTDOM) : $k;
				echo ' </td><td style="text-align:right;">' . sprintf("%01.2f %%",100 * $v['count']/$total );
				echo '</td></tr>';
			}
			echo '</table></td>';
			if (++$z != $count) echo '<td></td>';
		}
		echo '</tr></table>';
	}
}