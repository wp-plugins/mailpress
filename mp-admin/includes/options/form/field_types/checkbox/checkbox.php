<?php
MailPress::require_class('Forms_field_type_abstract');

class MP_Forms_field_type_checkbox extends MP_Forms_field_type_abstract
{
	var $field_type 	= 'checkbox';
	var $order		= 40;

	function __construct()
	{
		$this->description = __('Checkbox', MP_TXTDOM);
		$this->settings	 = dirname(__FILE__) . '/settings.xml';
		parent::__construct();
	}

	function submitted($field)
	{
		if (!isset($_POST[$this->prefix][$field->form_id][$field->id]))
		{
			$field->submitted['value'] = false;
			$field->submitted['text']  = __('not checked', MP_TXTDOM);
			return $field;
		}
		return parent::submitted($field);
	}

	function attributes_filter($no_reset)
	{
		if (!$no_reset) return;

		unset($this->field->settings['attributes']['checked']);
		if (isset($_POST[$this->prefix][$this->field->form_id][$this->field->id])) $this->field->settings['attributes']['checked'] = 'checked';

		$this->attributes_filter_css();
	}
}
$MP_Forms_field_type_checkbox = new MP_Forms_field_type_checkbox();
?>