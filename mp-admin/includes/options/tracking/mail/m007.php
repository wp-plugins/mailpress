<?php
MailPress::require_class('Tracking_module_abstract');
class MP_Tracking_module_m007 extends MP_Tracking_module_abstract
{
	var $module = 'm007';

	function __construct()
	{
		$this->type  = basename(dirname(__FILE__));
		$this->title = __('Opened, Clicks/day', MP_TXTDOM);
		parent::__construct();
	}

	function meta_box($mail)
	{
		global $wpdb;
		$query = "SELECT DATE(tmstp) as tmstp, track, count(*) as count FROM $wpdb->mp_tracks WHERE mail_id = " . $mail->id . " GROUP BY 1, 2 ORDER BY 1, 2 DESC ;";
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
<table id='tracking_mp_m007'>
	<thead>
		<tr>
			<th></th>
			<th><?php _e('Opened', MP_TXTDOM); ?></th>
			<th><?php _e('Clicks', MP_TXTDOM); ?></th>
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
}
$MP_Tracking_module_m007 = new MP_Tracking_module_m007();
?>