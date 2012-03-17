<?php
class MP_Useragent_agents extends MP_options_
{
	var $path = 'useragent/agents';

	public static function get_all()
	{
		return apply_filters('MailPress_useragent_agents_register', array());
	}
}