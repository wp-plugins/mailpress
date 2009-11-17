<?php
MailPress::require_class('Autoresponders_event_abstract');
class MP_Autoresponder_event_activate_mp_user extends MP_Autoresponders_event_abstract
{
	var $id    = 1;
	var $event = 'MailPress_activate_user';

	function __construct()
	{
		$this->desc = __('Subscription activated', MP_TXTDOM);
		parent::__construct();
	}
}
$MP_Autoresponder_event_activate_mp_user = new MP_Autoresponder_event_activate_mp_user();
?>