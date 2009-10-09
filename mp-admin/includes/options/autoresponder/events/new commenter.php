<?php
MailPress::require_class('Autoresponders_event_abstract');
class MP_Autoresponder_event_new_commenter extends MP_Autoresponders_event_abstract
{
	var $id    = 2;
	var $event = 'MailPress_new commenter';

	function __construct()
	{
		$this->desc = __('New commenter', 'MailPress');
		parent::__construct();
	}
}
$MP_Autoresponder_event_new_commenter = new MP_Autoresponder_event_new_commenter();
?>