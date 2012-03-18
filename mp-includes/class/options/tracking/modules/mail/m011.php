<?php
class MP_Tracking_module_m011 extends MP_tracking_module_
{
	var $id	= 'm011';
	var $context= 'normal';
	var $file 	= __FILE__;

	function __construct($title)
	{
		if (!class_exists('MP_Tracking_domains', false)) new MP_Tracking_domains();
		parent::__construct($title);
	}

	function meta_box($mail)
	{
		if (is_email($mail->toemail)) $m[$mail->toemail] = $mail->toemail;
		else $m = unserialize($mail->toemail);
		unset($m['MP_Mail']);
		$total = count($m);

		foreach($m as $email => $v)
		{
			$ug = apply_filters('MailPress_tracking_domains_domain_get', $email);
			$key = $ug->name;
			if (isset($x[$key]['count'])) 	$x[$key]['count']++;
			else 						$x[$key]['count'] = 1;
			if (isset($ug->icon_path) && !isset($x[$key]['img'])) $x[$key]['img'] = $ug->icon_path;
		}
		arsort($x);

		echo '<table id ="tracking_mp_010">';
		foreach($x as $k => $v)
		{
			echo '<tr><td>';
			if (isset($v['img'])) echo '<img src="' . $v['img'] . '" alt="" /> ';
			echo (empty($k)) ? __('others', MP_TXTDOM) : $k;
			echo ' </td><td style="text-align:right;">' . sprintf("%01.2f %%",100 * $v['count']/$total );
			echo '</td></tr>';
		}
		echo '</table>';
	}
}
new MP_Tracking_module_m011(__('Domain recipients', MP_TXTDOM));