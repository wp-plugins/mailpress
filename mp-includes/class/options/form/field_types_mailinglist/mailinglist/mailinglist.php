<?php
class MP_Form_field_type_mailinglist_mailinglist extends MP_form_field_type_
{
	var $file	= __FILE__;

	var $id 	= 'mailinglist_mailinglist';

	var $category = 'mailpress';
	var $order	= 200;

}
new MP_Form_field_type_mailinglist_mailinglist(__('Mailinglist', MP_TXTDOM));