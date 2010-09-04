<?php
if (!extension_loaded('gd')) return;

class MP_Forms_field_type_captcha_gd1 extends MP_Forms_field_type_abstract
{
	var $field_type 	= 'captcha_gd1';
	var $order		= 90;
	var $file		= __FILE__;

	function submitted($field)
	{
		$value	= trim($_POST[$this->prefix][$field->form_id][$field->id]);

		$required 	= true;
		$empty = empty($value);

		if ($empty)
		{
			$field->submitted['on_error'] = 1;
			return $field;
		}
		else
		{
			@session_start();
			include($_SESSION['cryptogra']['settings']);
			$code = ($difuplow) ? $value : strtoupper($value);

			switch (strtolower($cryptsecure)) 
			{	case 'md5' : $code = md5($code);  break;
				case 'sha1': $code = sha1($code); break;
			}
			if ((!$_SESSION['cryptogra']['code']) || ($_SESSION['cryptogra']['code'] != $code))
			{
				$field->submitted['on_error'] = 1;
				return $field;
			}
		}

		$field->submitted['value'] = 1;
		$field->submitted['text']  = __('ok', MP_TXTDOM);

		return $field;
	}

	function attributes_filter($no_reset)
	{
		if (!$no_reset) return;
		
		$this->attributes_filter_css();
	}

	function build_tag()
	{
		$id_input 	= $this->get_id($this->field);
		$tag_input 	= parent::build_tag();

		$id_img 	= $id_input . '_img';
		$tag_img 	= "<img id='$id_img' src='" . clean_url(MP_Action_url . '?id=' . $this->field->id . '&action=1ahctpac') . "' alt='' />";

		$this->field->type = $this->field_type;

		$form_format =  '{{img}}<br />{{input}}';

		MailPress::require_class('Forms');
		$form_template = MP_Forms::get_template($this->field->form_id);
		if ($form_template)
		{
			MailPress::require_class('Forms_templates');
			$form_templates = new MP_Forms_templates();
			$f = $form_templates->get_composite_template($form_template, $this->field_type);
			if (!empty($f)) $form_format = $f;
		}

		$search[] = '{{img}}';		$replace[] = '%1$s';
		$search[] = '{{id_img}}'; 	$replace[] = '%2$s';
		$search[] = '{{input}}'; 	$replace[] = '%3$s';
		$search[] = '{{id_input}}';	$replace[] = '%4$s';

		$html = str_replace($search, $replace,  $form_format);

		return sprintf($html, $tag_img, $id_img, $tag_input, $id_input);
	}
}
new MP_Forms_field_type_captcha_gd1(__('Captcha 1', MP_TXTDOM));