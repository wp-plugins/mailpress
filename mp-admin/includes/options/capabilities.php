<?php
//
//
//	Capabilities
//

$capabilities	= array(	'MailPress_edit_dashboard' 	=> array(	'name'	=> __('Dashboard', MP_TXTDOM),
												'group'	=> 'admin',
												'menu'	=> false
											),
					'MailPress_edit_mails'		=> array(	'name'	=> __('Edit mails', MP_TXTDOM),
												'group'	=> 'mails',
												'menu'	=> 10,

												'parent'	=> false,
												'page_title'=> __('View all Mails', MP_TXTDOM),
												'menu_title'=> __('Mails', MP_TXTDOM),
												'page'	=> MailPress_page_mails,
												'func'	=> array('MP_AdminPage', 'body')
											),
					'MailPress_edit_others_mails' => array(	'name'	=> __('Edit others mails', MP_TXTDOM),
												'group'	=> 'mails',
												'menu'	=> false
											),
					'MailPress_send_mails'		=> array(	'name'	=> __('Send mails', MP_TXTDOM),
												'group'	=> 'mails',
												'menu'	=> false
											),
					'MailPress_delete_mails'	=> array(	'name'	=> __('Delete mails', MP_TXTDOM),
												'group'	=> 'mails',
												'menu'	=> false
											),
					'MailPress_mail_custom_fields'=> array(	'name'	=> __('Custom fields', MP_TXTDOM), 
												'group'	=> 'mails'
											),
					'MailPress_switch_themes'	=> array(	'name'	=> __('Themes', MP_TXTDOM),
												'group'	=> 'admin',
												'menu'	=> 80,

												'parent'	=> false,
												'page_title'=> __('MailPress Themes', MP_TXTDOM),
												'menu_title'=> __('Themes', MP_TXTDOM),
												'page'	=> MailPress_page_themes,
												'func'	=> array('MP_AdminPage', 'body')
											),
					'MailPress_manage_options'	=> array(	'name'	=> __('Settings', MP_TXTDOM),
												'group'	=> 'admin',
												'menu'	=> 90,

												'parent'	=> 'options-general.php',
												'page_title'=> __('MailPress Settings', MP_TXTDOM),
												'menu_title'=> 'MailPress',
												'page'	=> MailPress_page_settings,
												'func'	=> array('MP_AdminPage', 'body')
											),
					'MailPress_edit_users'		=> array(	'name'	=> __('Edit users', MP_TXTDOM),
												'group'	=> 'users',
												'menu'	=> 30,

												'parent'	=> false,
												'page_title'=> __('MailPress Users', MP_TXTDOM),
												'menu_title'=> __('Users', MP_TXTDOM),
												'page'	=> MailPress_page_users,
												'func'	=> array('MP_AdminPage', 'body')
											),
					'MailPress_delete_users'	=> array(	'name'	=> __('Delete users', MP_TXTDOM),
												'group'	=> 'users',
												'menu'	=> false
											),
					'MailPress_user_custom_fields'=> array(	'name'	=> __('Custom fields', MP_TXTDOM), 
												'group'	=> 'users'
											)
			);
?>