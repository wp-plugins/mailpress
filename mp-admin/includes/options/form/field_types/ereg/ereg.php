<?php
MailPress::require_class('Forms_field_type_abstract');

class MP_Forms_field_type_ereg extends MP_Forms_field_type_abstract
{
	var $field_type 	= 'ereg';
	var $order		= 95;

	function __construct()
	{
		$this->description = __('Ereg[i] Input ', MP_TXTDOM);
		$this->settings	 = dirname(__FILE__) . '/settings.xml';
		parent::__construct();
	}

	function submitted($field)
	{
		$value	= trim($_POST[$this->prefix][$field->form_id][$field->id]);

		$required 	= (isset($field->settings['controls']['required']) && $field->settings['controls']['required']);
		$empty 	= empty($value);
		$ereg_ok 	= true;

		if ($required)
		{
			if ($empty)
			{
				$field->submitted['on_error'] = 1;
				return $field;
			}
		}

		$pattern 	= stripslashes($field->settings['options']['pattern']);
		if (!$empty && !empty($pattern)) $ereg_ok = (isset($field->settings['options']['ereg'])) ? @ereg($pattern, $value) : @eregi($pattern, $value);

		if (!$ereg_ok)
		{
			$field->submitted['on_error'] = 2;
			return $field;
		}
		return parent::submitted($field);
	}
}
$MP_Forms_field_type_ereg = new MP_Forms_field_type_ereg();
?>