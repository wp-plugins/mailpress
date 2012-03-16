<?php
class MP_Tracking_module_u005 extends MP_tracking_module_sys_info_
{
	var $id	= 'u005';
	var $context= 'side';
	var $file 	= __FILE__;

	var $item_id = 'user_id';
	var $query = false;

	function extended_meta_box($mp_user)
	{
		global $wpdb;
		$tracks = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT agent, ip FROM $wpdb->mp_tracks WHERE user_id = %d LIMIT 10;", $mp_user->id) );
		if (empty($tracks)) return;

		foreach($tracks as $track) 
		{
			$os      = apply_filters('MailPress_useragent_os_get_info',      $track->agent);
			$browser = apply_filters('MailPress_useragent_browser_get_info', $track->agent);
			echo $os . ' ' . $browser . '&#160;&#160;&#160;@&#160;' . $track->ip . '<br />'; 
		}
	}
}
new MP_Tracking_module_u005(__('System info', MP_TXTDOM));