<?php
class MP_Tracking_domains extends MP_options_
{
	var $path = 'tracking/domains';

	public static function get_all()
	{
		return apply_filters('MailPress_tracking_domains_register', array());
	}
}