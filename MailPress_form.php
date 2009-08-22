<?php
/*
Plugin Name: MailPress_form
Plugin URI: http://www.mailpress.org
Description: This is just an addon for MailPress to manage forms
Author: Andre Renaut
Version: 4.0
Author URI: http://www.mailpress.org
*/

class MailPress_form
{
	const prefix = 'mp_';

	function __construct()
	{
		global $wpdb;
// for mysql
		$wpdb->mp_forms  = $wpdb->prefix . 'mailpress_forms';
		$wpdb->mp_fields = $wpdb->prefix . 'mailpress_formfields';

// for shortcode
		add_shortcode('mailpress_form', 	array('MailPress_form', 'shortcode'));
// for field_type captcha_gd
		add_action('mp_action_1ahctpac',	array('MailPress_form', 'mp_action_captcha_gd1'));
		add_action('mp_action_2ahctpac',	array('MailPress_form', 'mp_action_captcha_gd2'));
		add_action('mp_form_purge', 		array('MailPress_form', 'do_purge'));

// for admin plugin pages
		define ('MailPress_page_forms',	'mailpress_forms');
		define ('MailPress_page_fields',	MailPress_page_forms . '&file=fields');
		define ('MailPress_page_templates',	MailPress_page_forms . '&file=templates');
// for admin plugin urls
		$file = 'admin.php';
		define ('MailPress_forms',  	$file . '?page=' . MailPress_page_forms);
		define ('MailPress_fields', 	$file . '?page=' . MailPress_page_fields);
		define ('MailPress_templates',$file . '?page=' . MailPress_page_templates);
// for ajax
		add_action('mp_action_add_form', 			array('MailPress_form', 'mp_action_add_form'));
		add_action('mp_action_delete_form', 		array('MailPress_form', 'mp_action_delete_form'));
		add_action('mp_action_dim_form', 			array('MailPress_form', 'mp_action_dim_form'));
		add_action('mp_action_add_field', 			array('MailPress_form', 'mp_action_add_field'));
		add_action('mp_action_delete_field', 		array('MailPress_form', 'mp_action_delete_field'));
		add_action('mp_action_dim_field', 			array('MailPress_form', 'mp_action_dim_field'));

		add_action('mp_action_ifview', 			array('MailPress_form', 'mp_action_ifview'));

// for wp admin
		if (is_admin())
		{
		// install
			register_activation_hook(MP_FOLDER . '/MailPress_form.php', 	array('MailPress_form', 'install'));
			register_deactivation_hook(MP_FOLDER . '/MailPress_form.php', 	array('MailPress_form', 'uninstall'));
		// for role & capabilities
			add_filter('MailPress_capabilities', 		array('MailPress_form', 'capabilities'), 1, 1);
		// for load admin page
			add_action('MailPress_load_admin_page', 		array('MailPress_form', 'load_admin_page'), 10, 1);
		}
	}

////  Shortcode  ////

	public static function shortcode($options=false)
	{
		MailPress::require_class('Forms');
		return MP_Forms::form($options['id']);
	}

////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////

// install
	public static function install() 
	{
		wp_schedule_event(time(), 'daily', 'mp_form_purge');
		include ( MP_TMP . 'mp-admin/includes/install/form.php');
	}

	public static function uninstall() 
	{
		wp_clear_scheduled_hook('mp_form_purge');
	}
	
// for role & capabilities
	public static function capabilities($capabilities)
	{
		$capabilities['MailPress_manage_forms'] = array(	'name'  	=> __('Forms', 'MailPress'),
											'group' 	=> 'admin',
											'menu'  	=> 85,
	
											'parent'	=> false,
											'page_title'=> __('MailPress Forms', 'MailPress'),
											'menu_title'=> __('Forms', 'MailPress'),
											'page'  	=> MailPress_page_forms,
											'func'	=> array('MP_AdminPage', 'body')
									);
		return $capabilities;
	}

// for load admin page
	public static function load_admin_page($page)
	{
		$hub = array (	MailPress_page_forms		=> 'forms',
					MailPress_page_fields 		=> 'form_fields',
					MailPress_page_templates 	=> 'form_templates'
				);

		if (isset($hub[$page])) require_once(MP_TMP . 'mp-admin/' . $hub[$page] . '.php');
	}

// for ajax in forms page
	public static function mp_action_add_form() 
	{
		if (!current_user_can('MailPress_manage_forms')) die('-1');

		if ( '' === trim($_POST['label']) )
		{
			$x = new WP_Ajax_Response( array(	'what' => 'form', 
									'id' => new WP_Error( 'label', __('You did not enter a valid description.', 'MailPress') )
								   ) );
			$x->send();
		}

		MailPress::require_class('Forms');
		$form = MP_Forms::insert( $_POST );

		if ( !$form )
		{
			$x = new WP_Ajax_Response( array(	'what' => 'form', 
									'id'   => $form
								  ) );
			$x->send();
		}

		if ( !$form || (!$form = MP_Forms::get( $form )) ) 	MailPress::mp_die('0');

		$form = MP_Forms::get($form->id);

		include (MP_TMP . 'mp-admin/forms.php');
		$x = new WP_Ajax_Response( array(	'what' => 'form', 
								'id' => $form->id, 
								'data' => MP_AdminPage::get_row( $form->id, array() ), 
								'supplemental' => array('name' => $form->description, 'show-link' => sprintf(__( 'form <a href="#%s">%s</a> added' , 'MailPress'), 'form-' . $form->id, $form->description))
							  ) );
		$x->send();
		break;
	}

	public static function mp_action_dim_form() // duplicate
	{
		$id = isset($_POST['id'])? (int) $_POST['id'] : 0;
		MailPress::require_class('Forms');

		$form = MP_Forms::duplicate($id);

		if ( !$form )
		{
			$x = new WP_Ajax_Response( array(	'what' => 'form', 
									'id' => new WP_Error( __CLASS__ . '::mp_action_dim_form', __('Problems trying to duplicate form.', 'MailPress'), array( 'form' => 'form_description' ) ), 
								  ) );
			$x->send();
		}

		if ( !$form || (!$form = MP_Forms::get( $form )) ) 	MailPress::mp_die('0');

		include (MP_TMP . 'mp-admin/forms.php');
		$x = new WP_Ajax_Response( array(	'what' => 'form', 
								'id' => $form->id, 
								'data' => MP_AdminPage::get_row( $form->id, array() ), 
								'supplemental' => array('name' => $form->description, 'show-link' => sprintf(__( 'form <a href="#%s">%s</a> added' , 'MailPress'), 'form-' . $form->id, $form->description))
							  ) );
		$x->send();
		break;
	}

	public static function mp_action_delete_form() 
	{
		$id = isset($_POST['id'])? (int) $_POST['id'] : 0;
		MailPress::require_class('Forms');
		MailPress::mp_die( MP_Forms::delete($id) ? '1' : '0' );
	}

// for ajax in fields page
	public static function mp_action_add_field() 
	{
		if (!current_user_can('MailPress_manage_forms')) die('-1');

		if ( '' === trim($_POST['label']) )
		{
			$x = new WP_Ajax_Response( array(	'what' => 'field', 
									'id' => new WP_Error( 'label', __('You did not enter a valid description.', 'MailPress') )
								   ) );
			$x->send();
		}

		MailPress::require_class('Forms_fields');
		$field = MP_Forms_fields::insert( $_POST );

		if ( !$field )
		{
			$x = new WP_Ajax_Response( array(	'what' => 'field', 
									'id'   => $field
								  ) );
			$x->send();
		}

		if ( !$field || (!$field = MP_Forms_fields::get( $field )) ) 	MailPress::mp_die('0');

		MailPress::require_class('Forms');
		$form = MP_Forms::get($field->form_id);
		if (isset($form->settings['visitor']['mail']) && ($form->settings['visitor']['mail'] != '0'))
			add_filter('MailPress_form_columns_form_fields', array('MP_AdminPage', 'add_incopy_column'), 1, 1);

		include (MP_TMP . 'mp-admin/form_fields.php');
		$x = new WP_Ajax_Response( array(	'what' => 'field', 
								'id' => $field->id, 
								'data' => MP_AdminPage::get_row( $field->id, array() ), 
								'supplemental' => array('name' => $field->description, 'show-link' => sprintf(__( 'field <a href="#%s">%s</a> added' , 'MailPress'), "field-$field->id", $field->description))
							  ) );
		$x->send();
		break;
	}

	public static function mp_action_dim_field() // duplicate
	{
		$id = isset($_POST['id'])? (int) $_POST['id'] : 0;
		MailPress::require_class('Forms_fields');

		$field = MP_Forms_fields::duplicate($id);

		if ( is_wp_error($field) )  
		{
			$x = new WP_Ajax_Response( array(	'what' => 'field', 
									'id' => new WP_Error( __CLASS__ . '::mp_action_dim_field', __('Problems trying to duplicate field.', 'MailPress'), array( 'form-field' => 'field_description' ) ), 
								  ) );
			$x->send();
		}

		if ( !$field || (!$field = MP_Forms_fields::get( $field )) ) 	MailPress::mp_die('0');

		include (MP_TMP . 'mp-admin/form_fields.php');
		$x = new WP_Ajax_Response( array(	'what' => 'field', 
								'id' => $field->id, 
								'data' => MP_AdminPage::get_row( $field->id, array() ), 
								'supplemental' => array('name' => $field->description, 'show-link' => sprintf(__( 'field <a href="#%s">%s</a> added' , 'MailPress'), "field-$field->id", $field->description))
							  ) );
		$x->send();
		break;
	}

	public static function mp_action_delete_field() 
	{
		$id = isset($_POST['id'])? (int) $_POST['id'] : 0;
		MailPress::require_class('Forms_fields');
		MailPress::mp_die( MP_Forms_fields::delete($id) ? '1' : '0' );
	}

// for preview
	public static function mp_action_ifview()
	{
		MailPress::require_class('Forms');
		$form = MP_Forms::get($_GET['id']);

		$form_url = clean_url(admin_url(MailPress_forms . '&action=edit&id=' . $form->id));
		$field_url = clean_url(admin_url(MailPress_fields . '&form_id=' . $form->id));
		$template_url = clean_url(admin_url(MailPress_fields . '&form_id=' . $form->id));

		$actions['form'] 		= "<a href='$form_url' 		class='button'>" . __('Edit form', 'MailPress') . '</a>';
		$actions['field'] 	= "<a href='$field_url' 	class='button'>" . __('Edit fields', 'MailPress') . '</a>';
		$actions['template'] 	= "<a href='$template_url' 	class='button'>" . __('Edit template', 'MailPress') . '</a>';
		$sep = ' / ';

		add_action('admin_init', array('MailPress_form', 'ifview_title'));

		include(MP_TMP . '/mp-includes/html/form.php');
	}
	public static function ifview_title()
	{
		MailPress::require_class('Forms');
		$form = MP_Forms::get($_GET['id']);
		global $title; $title = sprintf(__('Preview "%1$s"','MailPress'), stripslashes($form->label));
	}

	public static function mp_action_captcha_gd1()
	{
		include ( MP_TMP . 'mp-admin/includes/options/form/field_types/captcha_gd1/captcha/captcha.php');
	}
	public static function mp_action_captcha_gd2()
	{
		include ( MP_TMP . 'mp-admin/includes/options/form/field_types/captcha_gd2/captcha/captcha.php');
	}
	public static function do_purge ()
	{
		$path = MP_TMP . 'tmp';
		$files = array();
		$dir = opendir($path);
		if ($dir) while ( ($file = readdir($dir)) !== false ) if ( ($file[0] != '.') && (strstr($file, 'MP_Forms_')) && ( filemtime($file) < (time() - 20*24*60*60) )  ) $files[] = $file;
		@closedir($dir);
		if (empty($files)) return true;

	 	foreach ($files as $file) unlink($path . '/' . $file);

		return true;
	}
}
$MailPress_form = new MailPress_form();
?>