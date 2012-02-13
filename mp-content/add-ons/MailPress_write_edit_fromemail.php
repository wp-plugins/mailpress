<?php
if (class_exists('MailPress') && !class_exists('MailPress_write_edit_fromemail'))
{
/*
Plugin Name: MailPress_write_edit_fromemail
Plugin URI: http://www.mailpress.org/wiki/index.php
Description: This is just an add-on for MailPress to add new capability MailPress_write_edit_fromemail
Version: 5.2.1
*/

class MailPress_write_edit_fromemail
{
	function __construct()
	{
	// for role & capabilities
		add_filter('MailPress_capabilities', 		array(__CLASS__, 'capabilities'), 1, 1);
	}

////  Admin  ////

// for role & capabilities
	public static function capabilities($capabilities)
	{
		$capabilities['MailPress_write_edit_fromemail'] = array(	'name'	=> __('Edit fromemail', MP_TXTDOM),
										'group'	=> 'mails'
		);
		return $capabilities;
	}
}
new MailPress_write_edit_fromemail();
}