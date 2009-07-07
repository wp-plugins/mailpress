<?php
/*
u007 opened/day
*/
	function meta_box_tracking_mp_u007($mp_user)
	{
		global $wpdb;
		$query = "SELECT DATE(tmstp) as tmstp, track, count(*) as count FROM $wpdb->mp_tracks WHERE user_id = $mp_user->id GROUP BY 1, 2 ORDER BY 1, 2 DESC ;";
		$tracks = $wpdb->get_results($query);
		if ($tracks)
		{
			$x = array();
			foreach($tracks as $track)
			{
				if ( MailPress_tracking_openedmail == $track->track )	$x[$track->tmstp]['o'] = $track->count;
				else									$x[$track->tmstp]['c'] = $track->count;
			}
?>
<table id='tracking_mp_u007'>
	<thead>
		<tr>
			<th></th>
			<th><?php _e('Opened', 'MailPress'); ?></th>
			<th><?php _e('Clicks', 'MailPress'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
			foreach($x as $k => $v)
			{
				echo "<tr><td>$k</td>";
				echo (isset($v['o'])) ? '<td class="number">' . $v['o'] . '</td>' : '<td class="number"></td>';
				echo (isset($v['c'])) ? '<td class="number">' . $v['c'] . '</td>' : '<td class="number"></td>';
				echo "</tr>";
			}
?>
	</tbody>
</table>
<?php
		}
	}
?>