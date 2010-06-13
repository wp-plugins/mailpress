<?php
MailPress::require_class('Options');

class MP_Autoresponders_events extends MP_Options
{
	var $path = 'autoresponder/events';

	public static function get_all()
	{
		return apply_filters('MailPress_autoresponder_events_register', array());
	}
}
$MP_Autoresponders_events = new MP_Autoresponders_events();
?>