<?php
class MP_Tracking_module_m002 extends MP_Tracking_module_abstract
{
	var $module = 'm002';
	var $context = 'normal';
	var $file = __FILE__;

	function meta_box($mail)
	{
		global $wpdb;
		$tracks = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT DATE(tmstp) as tmstp, user_id FROM $wpdb->mp_tracks WHERE mail_id = %d ORDER BY 1 DESC LIMIT 10;", $mail->id) );
		MP_AdminPage::require_class('Users');
		if ($tracks) foreach($tracks as $track)
		{
			$mp_user = MP_Users::get($track->user_id);
			if (!$mp_user) continue;

			$tracking_url = clean_url(MailPress::url( MailPress_tracking_u, array('id' => $track->user_id) ));
			$action = "<a href='$tracking_url' target='_blank' title='" . __('See tracking results', MP_TXTDOM ) . "'>" . $mp_user->email . '</a>';

			echo $track->tmstp . ' ' . $action . '<br />';
		} 
	}
}
new MP_Tracking_module_m002(__('Last 10 users', MP_TXTDOM));