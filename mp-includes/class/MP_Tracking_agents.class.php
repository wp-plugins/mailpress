<?php
class MP_Tracking_agents extends MP_options_
{
	var $path = 'tracking/agents';

	public static function get_all()
	{
		return apply_filters('MailPress_tracking_agents_register', array());
	}
}