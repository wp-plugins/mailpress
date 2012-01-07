<?php
class MP_Form_field_type_newsletter_newsletter extends MP_form_field_type_
{
	var $file	= __FILE__;

	var $id 	= 'newsletter_newsletter';

	var $category = 'mailpress';
	var $order	= 300;

}
new MP_Form_field_type_newsletter_newsletter(__('Newsletter', MP_TXTDOM));