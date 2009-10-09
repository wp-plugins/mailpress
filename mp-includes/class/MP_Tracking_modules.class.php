<?php
MailPress::require_class('Options');

class MP_Tracking_modules extends MP_Options
{
	var $path = 'tracking/';

	function __construct($type, $settings = false)
	{
		if ($settings === false)
		{
			$settings = get_option('MailPress_tracking');
			if (!is_array($settings)) return;
			$this->includes = $settings;
		}

		$this->path .= $type;
		parent::__construct();
	}

	function get_all($type)
	{
		return apply_filters('MailPress_tracking_module_register', array(), $type);
	}
}
?>