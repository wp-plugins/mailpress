<?php
//
//
//	Capabilities
//

$capabilities	= array(	'MailPress_edit_dashboard' 	=> array(	'name'	=> __('Dashboard','MailPress'),
												'group'	=> 'admin',
												'menu'	=> false
											),
					'MailPress_edit_mails'		=> array(	'name'	=> __('Edit mails','MailPress'),
												'group'	=> 'mails',
												'menu'	=> 10,

												'parent'	=> false,
												'page_title'=> __('View all Mails','MailPress'),
												'menu_title'=> __('Mails','MailPress'),
												'page'	=> MailPress_page_mails,
												'func'	=> array('MP_AdminPage', 'body')
											),
					'MailPress_edit_others_mails' => array(	'name'	=> __('Edit others mails','MailPress'),
												'group'	=> 'mails',
												'menu'	=> false
											),
					'MailPress_send_mails'		=> array(	'name'	=> __('Send mails','MailPress'),
												'group'	=> 'mails',
												'menu'	=> false
											),
					'MailPress_delete_mails'	=> array(	'name'	=> __('Delete mails','MailPress'),
												'group'	=> 'mails',
												'menu'	=> false
											),
					'MailPress_mail_custom_fields'=> array(	'name'	=> __('Custom fields', 'MailPress'), 
												'group'	=> 'mails'
											),
					'MailPress_switch_themes'	=> array(	'name'	=> __('Themes','MailPress'),
												'group'	=> 'admin',
												'menu'	=> 80,

												'parent'	=> false,
												'page_title'=> __('MailPress Themes','MailPress'),
												'menu_title'=> __('Themes','MailPress'),
												'page'	=> MailPress_page_themes,
												'func'	=> array('MP_AdminPage', 'body')
											),
					'MailPress_manage_options'	=> array(	'name'	=> __('Settings','MailPress'),
												'group'	=> 'admin',
												'menu'	=> 90,

												'parent'	=> 'options-general.php',
												'page_title'=> __('MailPress Settings','MailPress'),
												'menu_title'=> __('MailPress'),
												'page'	=> MailPress_page_settings,
												'func'	=> array('MP_AdminPage', 'body')
											),
					'MailPress_edit_users'		=> array(	'name'	=> __('Edit users','MailPress'),
												'group'	=> 'users',
												'menu'	=> 30,

												'parent'	=> false,
												'page_title'=> __('MailPress Users','MailPress'),
												'menu_title'=> __('Users','MailPress'),
												'page'	=> MailPress_page_users,
												'func'	=> array('MP_AdminPage', 'body')
											),
					'MailPress_delete_users'	=> array(	'name'	=> __('Delete users','MailPress'),
												'group'	=> 'users',
												'menu'	=> false
											),
					'MailPress_user_custom_fields'=> array(	'name'	=> __('Custom fields', 'MailPress'), 
												'group'	=> 'users'
											)
			);
?>