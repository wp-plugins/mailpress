<?php
MailPress::require_class('Tracking_module_abstract');
class MP_Tracking_module_u002 extends MP_Tracking_module_abstract
{
	var $module = 'u002';

	function __construct()
	{
		$this->type  = basename(dirname(__FILE__));
		$this->title = __('Last 10 mails','MailPress');
		parent::__construct();
	}

	function meta_box($mp_user)
	{
		global $wpdb;
		$query = "SELECT * FROM $wpdb->mp_usermeta WHERE user_id = " . $mp_user->id . " AND meta_key = '_MailPress_mail_sent' ORDER BY umeta_id DESC LIMIT 10;";
		$tracks = $wpdb->get_results($query);
		if ($tracks)
		{
			MailPress::require_class('Mailmeta');
			foreach($tracks as $track)
			{
				$subject = $wpdb->get_var("SELECT subject FROM $wpdb->mp_mails WHERE id = " . $track->meta_value . ';');
				if ($subject)
				{
					$metas = MP_Mailmeta::get( $track->meta_value, '_MailPress_replacements');
					if ($metas) foreach($metas as $k => $v) $subject = str_replace($k, $v, $subject);
				}
				else $subject = __('(deleted)','MailPress');
				echo '(' . $track->meta_value . ') ' . $subject . '<br />';
			} 
		}
	}
}
$MP_Tracking_module_u002 = new MP_Tracking_module_u002();
?>