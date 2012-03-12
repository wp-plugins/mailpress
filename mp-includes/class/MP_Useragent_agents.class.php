<?php
class MP_Useragent_agents extends MP_options_
{
	var $path = 'useragent/agents';

	function __construct()
	{
		parent::__construct();
		do_action('MailPress_load_Useragent_agents');
	}
}