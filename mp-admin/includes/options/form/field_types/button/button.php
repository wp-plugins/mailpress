<?php
MailPress::require_class('Forms_field_type_abstract');

class MP_Forms_field_type_button extends MP_Forms_field_type_abstract
{
	var $field_type 	= 'button';
	var $order		= 100;

	function __construct()
	{
		$this->description = __('Button', 'MailPress');
		$this->settings	 = dirname(__FILE__) . '/settings.xml';
		parent::__construct();
	}

	function submitted($field)
	{
		if (!isset($_POST[$this->prefix][$field->form_id][$field->id]))
		{
			$field->submitted['value'] = false;
			$field->submitted['text']  = __('not selected', 'MailPress');
			return $field;
		}
		return parent::submitted($field);
	}
}
$MP_Forms_field_type_button = new MP_Forms_field_type_button();
?>