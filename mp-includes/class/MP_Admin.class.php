<?php

register_activation_hook(MP_FOLDER . '/MailPress.php',	array('MP_Admin','install'));

add_action('init',			array('MP_Admin','roles_and_capabilities'));

add_action('admin_init',            array('MP_Admin','mp_redirect'));
add_action('admin_init',            array('MP_Admin','title'));
add_action('admin_print_styles',	array('MP_Admin','register_styles'));
add_action('admin_print_styles',	array('MP_Admin','enqueue_styles'));

add_action('admin_print_scripts' , 	array('MP_Admin','register_scripts'));
//add_action('admin_print_scripts' , 	array('MP_Admin','enqueue_scripts'));
add_action('admin_print_scripts' ,		array('MP_Admin','enqueue_header_scripts'));
add_action('admin_print_footer_scripts' , array('MP_Admin','enqueue_footer_scripts'));
add_action('admin_menu',            array('MP_Admin','menu'),8,1);
add_action('admin_head',            array('MP_Admin','screen_meta'));

add_filter('screen_meta_screen', 	array('MP_Admin','screen_meta_screen'),8,1);
add_filter('contextual_help',       array('MP_Admin','contextual_help'),8,1);
add_filter('favorite_actions',      array('MP_Admin','favorite_actions'),8,1);
add_filter('Mailpress_input_text', 	array('MP_Admin','input_text'), 8, 1 );

// for javascript plugin conflicts
add_filter('MailPress_deregister_scripts', array('MP_Admin','deregister_scripts'),10,1);

class MP_Admin
{
////	install plugin	////
	public static function system_requirements() {
		global $wp_version; 

		$min_ver_php = '5.2.0';
		$min_ver_wp  = '2.7';
		$m = array();

		if (version_compare(PHP_VERSION, $min_ver_php, '<')) 	$m[] = sprintf(__('Your %1$s version is \'%2$s\', at least version \'%3$s\' required.','MailPress'), __('PHP'), PHP_VERSION, $min_ver_php );

		if (version_compare($wp_version, $min_ver_wp , '<'))	$m[] = sprintf(__('Your %1$s version is \'%2$s\', at least version \'%3$s\' required.','MailPress'), __('WordPress'), $wp_version , $min_ver_wp );

		if (!is_writable(ABSPATH . MP_PATH . 'tmp'))		$m[] = sprintf(__('The directory \'%1$s\' is not writable.','MailPress'), ABSPATH . MP_PATH . 'tmp');

		return $m;
	}

	public static function install() {

		$m = self::system_requirements();

		if (array() == $m)
		{
			include ('../' . MP_PATH . 'mp-admin/includes/install.php');
		}
		else
		{
			$err  = sprintf(__('<b>Sorry, but you can\'t run this plugin : %1$s. </b>','MailPress'),$_GET['plugin']);
			$err .= '<ol><li>' . implode('</li><li>',$m) . '</li></ol>';

			if (isset($_GET['plugin'])) deactivate_plugins($_GET['plugin']);	
			trigger_error($err, E_USER_ERROR);
		}
	}

//// to get plugin page ////

	public static function get_page() {
		if (!isset($_GET['page'])) return false;
		$page = $_GET['page'];
		if (isset($_GET['file'])) $page .= '&file=' . $_GET['file'];
		return $page;
	}

//// roles and capabilities ////

	public static function roles_and_capabilities() {

		$x	= self::capabilities();

		$role = get_role('administrator');

		foreach ($x as $capability => $v) $role->add_cap($capability);

		do_action('MailPress_roles_and_capabilities');
	}

	public static function capability_groups()
	{
		return array('admin' => __('Admin','MailPress'),'mails' => __('Mails','MailPress'),'users' => __('Users','MailPress'));
	}

	public static function mailpress_mails() 		{ include (MP_TMP . '/mp-admin/mails.php'); }
	public static function mailpress_write() 		{ include (MP_TMP . '/mp-admin/mail_new.php'); }
	public static function mailpress_design()       { include (MP_TMP . '/mp-admin/themes.php'); }
	public static function mailpress_settings() 	{ include (MP_TMP . '/mp-admin/settings.php'); }
	public static function mailpress_users() 		{ include (MP_TMP . '/mp-admin/uzers.php'); }



	public static function capabilities() {
		global $mp_general;
		$m  = (isset($mp_general['menu'])) ? true : false;
		$pu = ( current_user_can('edit_users') ) ? 'users.php' : 'profile.php';

		$x	= array(	'MailPress_edit_dashboard' 	=> array(	'name'  	=> __('Dashboard','MailPress'),
            										'group' 	=> 'admin',
	            									'menu'  	=> false
                        						),
					'MailPress_edit_mails'		=> array(	'name'  	=> __('Edit mails','MailPress'),
            										'group' 	=> 'mails',
            										'menu'  	=> 10,

            										'parent'	=> ($m) ? false : false,
            										'page_title'=> __('Mails','MailPress'),
            										'menu_title'=> ($m) ? __('Mails','MailPress') : __('Mails','MailPress'),
            										'page'  	=> MailPress_page_mails,
            										'func'  	=> array('MP_Admin', MailPress_page_mails)
                                    					),
					'MailPress_edit_others_mails' => array(	'name'  	=> __('Edit others mails','MailPress'),
      	                                             			'group' 	=> 'mails',
            										'menu'  	=> false
                									),
					'MailPress_send_mails'		=> array(	'name'  	=> __('Send mails','MailPress'),
                										'group' 	=> 'mails',
            										'menu'  	=> false
            									),
					'MailPress_delete_mails'	=> array(	'name'  	=> __('Delete mails','MailPress'),
            										'group' 	=> 'mails',
            										'menu'  	=> false
            									),
					'MailPress_switch_themes'	=> array(	'name'  	=> __('Design','MailPress'),
            										'group' 	=> 'admin',
            										'menu'  	=> 20,

            										'parent'	=> ($m) ? false : 'themes.php',
            										'page_title'=> __('MailPress Themes','MailPress'),
            										'menu_title'=> ($m) ? __('Themes','MailPress') : __('MailPress Themes','MailPress'),
            										'page'  	=> MailPress_page_design,
            										'func'  	=> array('MP_Admin', MailPress_page_design)
            									),
					'MailPress_manage_options'	=> array(	'name'  	=> __('Settings','MailPress'),
            										'group' 	=> 'admin',
            										'menu'  	=> 40,

     												'parent'	=> ($m) ? false : 'options-general.php',
     												'page_title'=> __('MailPress Settings','MailPress'),
     												'menu_title'=> ($m) ? __('Settings','MailPress') : __('MailPress Settings','MailPress'),
     												'page'  	=> MailPress_page_settings,
     												'func'  	=> array('MP_Admin', MailPress_page_settings)
     											),
					'MailPress_edit_users'		=> array(	'name'  	=> __('Edit users','MailPress'),
     												'group' 	=> 'users',
     												'menu'  	=> 30,

     												'parent'	=> ($m) ? false : $pu,
     												'page_title'=> __('MailPress Users','MailPress'),
     												'menu_title'=> ($m) ? __('Users','MailPress') : __('MailPress Users','MailPress'),
     												'page'  	=> MailPress_page_users,
     												'func'  	=> array('MP_Admin', MailPress_page_users)
     											),
					'MailPress_delete_users'	=> array(	'name'  	=> __('Delete users','MailPress'),
     												'group' 	=> 'users',
     												'menu'  	=> false
     											)
                );

		$x	= apply_filters('MailPress_capabilities',$x);

		return $x;
	}

//// wp_redirect ////

	public static function mp_redirect() {
		global $mp_general;

		$page = self::get_page();
		if (!$page) return;

		switch (true)
		{
			case ((MailPress_page_mails == $page) && !empty( $_GET['delete_mails'] )) :		// MANAGING CHECKBOX REQUESTS
				$url_parms = self::get_url_parms();
				$deleted = $sent = $notsent = 0;
				foreach ($_GET['delete_mails'] as $id) : 							
					switch (true)
					{
						case ( isset( $_GET['deleteit'] )):
							MP_Mail::delete($id);
							$deleted++;
						break;
						case (isset( $_GET['sendit'] )):
							$x = MP_Mail::send_draft($id);
							$url = (is_numeric($x))	? $sent += $x : $notsent++ ;
						break;
					}
				endforeach;
				$redirect_to  = MailPress_mails;
			 	$redirect_to .= ($deleted) 	? 	'&deleted='	. $deleted : '';
			 	$redirect_to .= ($sent) 	?	'&sent=' 	. $sent : '';
			 	$redirect_to .= ($notsent) 	?	'&notsent='	. $notsent : '';
				$redirect_to = self::url($redirect_to ,false,$url_parms);
				wp_redirect( $redirect_to );
			break;

			case (MailPress_page_mail == $page) : 
				$list_url = self::url(MailPress_mails,false,self::get_url_parms());
				if ( isset($_POST['action']) )    $action = $_POST['action'];
				elseif ( isset($_GET['action']) ) $action = $_GET['action'];  
				switch($action) 
				{
					case 'delete' :
						$id = $_GET['id'];
						MP_Mail::delete($id);
						wp_redirect($list_url . '&deleted=1');
					break;
					case 'send' :
						$id = $_GET['id'];
						$x = MP_Mail::send_draft($id);
						$url = (is_numeric($x))	? $list_url . '&sent=' . $x : $list_url . '&notsent=1';
						wp_redirect($url);
					break;
					case 'draft' :
						$id = (0 == $_POST['id']) ? MP_Mail::get_id('self::mp_redirect') : $_POST['id'];
						MP_Mail::update_draft($id);

						$parm = "&saved=1";

						if (isset($_POST['send']))
						{
							$x = MP_Mail::send_draft($id);
							if (is_numeric($x))
								if (0 == $x)	$parm = "&sent=0";
								else			$parm = "&sent=$x";
							else				$parm = "&nodest=0";
						}

						if     (strstr($_SERVER['HTTP_REFERER'],MailPress_edit))	$url = MailPress_edit  . "$parm&id=$id";
						elseif (strstr($_SERVER['HTTP_REFERER'],MailPress_write))	$url = MailPress_write . "$parm&id=$id";
						else										$url = MailPress_mail  . "&action=view$parm&id=$id";

						wp_redirect($url);
					break;
				}
			break;

			case (MailPress_page_revision == $page) :						// MANAGING REVISION

				$action      = $_GET['action'];
				$revision_id = absint($_GET['revision']);
				$id 		 = absint($_GET['id']);
				$diff        = absint($_GET['diff']);
				$left        = absint($_GET['left']);
				$right       = absint($_GET['right']);

				$redirect_to = MailPress_edit;

				switch ( $action )
				{
					case 'delete' :
					break;
					case 'edit' :
					break;
					case 'restore' :
						if (!$revision = MP_Mail::get($revision_id)) break;
						if (!$mail     = MP_Mail::get($id))          break;

						$_POST = get_object_vars($mail);
						foreach(array('toname','subject','html','content','plaintext') as $k) if ($_POST[$k]) $_POST[$k] = addcslashes($_POST[$k],"'");
						MP_Mail::update_draft($revision_id,'');

						$_POST = get_object_vars($revision);
						unset($_POST['created']);
						foreach(array('toname','subject','html','content','plaintext') as $k) if ($_POST[$k]) $_POST[$k] = addcslashes($_POST[$k],"'");
						MP_Mail::update_draft($id);

						$redirect_to .= "&id=$id&revision=$revision_id&message=5&time=" . urlencode($revision->created);
					break;
					case 'diff' :
						if ( $left == $right ) 
							break;
						if ( !$left_revision  = MP_Mail::get( $left ) )
							break;
						if ( !$right_revision = MP_Mail::get( $right ) )
							break;

						if ( strtotime($right_revision->created) < strtotime($left_revision->created) ) 
						{
							$redirect_to = MailPress_revision;
							$redirect_to .= "&action=diff&id=$id&left=$right_revision->id&right=$left_revision->id";
							break;
						}

						if ($left_revision->id  == $id) $left_ok = true;
						if ($right_revision->id == $id) $right_ok = true;
						$rev_ids = MP_Mailmeta::get($id,'_MailPress_mail_revisions');
						foreach ($rev_ids as $v) if ($left_revision->id  == $v) $left_ok = true;
						foreach ($rev_ids as $v) if ($right_revision->id == $v) $right_ok = true;
						if (!($left_ok && $right_ok)) break;
						$redirect_to = false;
					break;
					case 'view' :
					default :
 						if ( !$revision = MP_Mail::get( $revision_id ) )
							break;
						if ( !$mail = MP_Mail::get( $id ) )
							break;

						$redirect_to = false;
					break;
				}
				if ($redirect_to) wp_redirect( $redirect_to );
			break;

			case (MailPress_page_settings == $page) :
			
				if ($_POST['formname'] != 'generalform') return;

				$old_default_newsletters = (isset($mp_general['default_newsletters'])) ? $mp_general['default_newsletters'] : MP_Newsletter::get_defaults();
				if (!isset($_POST['general']['default_newsletters'])) $_POST['general']['default_newsletters'] = array();

				$oldmenu = (isset($mp_general['menu'])) 		? $mp_general['menu'] : false;
				$newmenu = (isset($_POST['general']['menu']))	? $_POST['general']['menu'] : false;

				$mp_general		= $_POST['general'];
				$mp_general['tab']= $mp_tab = '0';

				$diff_default_newsletters = array();
				foreach($mp_general['default_newsletters'] as $k => $v) if (!isset($old_default_newsletters[$k])) $diff_default_newsletters[$k] = true;
				foreach($old_default_newsletters as $k => $v) if (!isset($mp_general['default_newsletters'][$k])) $diff_default_newsletters[$k] = true;
				foreach ($diff_default_newsletters as $k => $v) MP_Newsletter::reverse_subscriptions($k);
				MP_Newsletter::register();

				switch ($mp_general['subscription_mngt'])
				{
					case 'ajax' :
						$mp_general['id'] = '';
					break;
					default :
						$mp_general['id'] = $_POST[$mp_general['subscription_mngt']];
					break;
				}
				switch (true)
				{
					case (('ajax' != $mp_general['subscription_mngt']) && ( !is_numeric($mp_general['id']))) :
					break;
					case ( !MailPress::is_email($mp_general['fromemail']) ) :
					break;
					default :
						if (!add_option ('MailPress_general', $mp_general, 'MailPress - general settings' )) update_option ('MailPress_general', $mp_general);
						if ($newmenu !== $oldmenu)
						{
							if (!$newmenu) 	wp_redirect('options-general.php?page=' . MailPress_page_settings . '&saveg=ok'); 
							else			wp_redirect('admin.php?page=' . MailPress_page_settings . '&saveg=ok');
						}
					break;
				}
			break;

			case (MailPress_page_design == $page) :
				$th = new MP_Themes();

				$url = (isset($mp_general['menu'])) ? 'admin.php' : 'themes.php';

				if ( isset($_GET['action']) ) 
				{
					check_admin_referer('switch-theme_' . $_GET['template']);
					if ('activate' == $_GET['action']) 
					{
						$th->switch_theme($_GET['template'], $_GET['stylesheet']);
						$page .= '&activated=true';
						wp_redirect($url . '?page=' . $page);
					}
				}
			break;

			case ((MailPress_page_users == $page) && !empty( $_GET['delete_users'] )) :		// MANAGING CHECKBOX REQUESTS
				$url_parms = self::get_url_parms();
				$deleted = $activated = $deactivated = 0;
				foreach ($_GET['delete_users'] as $id) : 							
					switch (true)
					{
						case ( isset( $_GET['deleteit'] )):
							MP_User::set_status($id, 'delete');
							$deleted++;
						break;
						case (isset( $_GET['activateit'] )):
							MP_User::set_status($id, 'active');
							$activated++;
						break;
						case (isset( $_GET['deactivateit'] )):
							MP_User::set_status($id, 'waiting');
							$deactivated++;
						break;
					}
				endforeach;
				$redirect_to  = MailPress_users;
				$redirect_to .= ($deleted) 		? '&deleted=' 	. $deleted : '';
				$redirect_to .= ($activated) 		? '&activated=' 	. $activated : '';
				$redirect_to .= ($deactivated) 	? '&deactivated=' . $deactivated : '';
				$redirect_to = self::url($redirect_to ,false,$url_parms);
				wp_redirect( $redirect_to );
			break;

			case (MailPress_page_user == $page) :
				$list_url = self::url(MailPress_users,false,self::get_url_parms());

				if ( isset($_POST['action']) ) $action = $_POST['action'];
				elseif ( isset($_GET['action']) ) $action = $_GET['action'];  

				switch($action) 
				{
					case 'delete':
						$id = $_GET['id'];
						MP_User::set_status( $id, 'delete' );
						wp_redirect($list_url);
					break;
					case 'activate':
						$id = $_GET['id'];
						MP_User::set_status( $id, 'active' );
						wp_redirect($list_url);
					break;
					case 'deactivate':
						$id = $_GET['id'];
						MP_User::set_status( $id, 'waiting' );
						wp_redirect($list_url);
					break;
					default :
						do_action('MailPress_mp_redirect',$page);
					break;
				} 
			break;

			default :
				do_action('MailPress_mp_redirect',$page);
			break;
		}
	}

//// title ////

	public static function title() {

		$page = self::get_page();
		if (!$page) return;
		$page .= (isset($_GET['id']))   ? 'id' 		: '';

		$x = array(
				MailPress_page_write		=> __('Write Mail','MailPress'),
				MailPress_page_edit . 'id'	=> __('Edit Mail','MailPress'),
				MailPress_page_mail . 'id'	=> __('View Mail','MailPress'),
				MailPress_page_revision . 'id'=> __('Mail Revisions','MailPress'),
				MailPress_page_user . 'id'	=> __('MailPress User','MailPress')
			);

		$x = apply_filters('MailPress_title',$x);

		if (!isset($x[$page])) return;
		
		global $title;
		$title = $x[$page];
	}

//// styles ////

	public static function register_styles() {

		$pathcss            = MP_TMP . '/mp-admin/css/colors-' . get_user_option('admin_color') . '.css';
		$css_url            = get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/colors-' . get_user_option('admin_color') . '.css';
		$css_url_default 	= get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/colors-fresh.css';
		$css_url = (is_file($pathcss)) ? $css_url : $css_url_default;

		wp_register_style ( 'MailPress_colors', 	$css_url );

		wp_register_style ( MailPress_page_mails,       get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/mails.css',       array('thickbox') );
		wp_register_style ( MailPress_page_mail, 		get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/mail.css' );
		wp_register_style ( MailPress_page_write,       get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/mail_new.css', 	array('thickbox') );
		wp_register_style ( MailPress_page_settings, 	get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/settings.css' );
		wp_register_style ( MailPress_page_users,       get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/users.css' );
		wp_register_style ( MailPress_page_user, 		get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/user.css' );

		do_action('MailPress_register_styles');
	}

// for appropriate style

	public static function enqueue_styles() {

		$x = array	(	'MailPress_colors'		=> array 	(	'MailPress_colors'	),
					MailPress_page_mails		=> array	(	MailPress_page_mails	),
					MailPress_page_mail		=> array	(	MailPress_page_mail	),
					MailPress_page_write		=> array	(	MailPress_page_write	),
					MailPress_page_design		=> array	(	'thickbox'			),
					MailPress_page_settings		=> array	(	MailPress_page_settings	),
					MailPress_page_users		=> array	(	MailPress_page_users	),
					MailPress_page_user		=> array	(	MailPress_page_user	)
				);

		$x = apply_filters('MailPress_enqueue_styles',$x);

		$x[MailPress_page_edit] = $x[MailPress_page_revision] = $x[MailPress_page_write];

		$page = self::get_page();
		if (isset($x[$page])) foreach ($x[$page] as $src )			wp_enqueue_style($src);
		if (isset($x['MailPress_colors'])) 	foreach ($x['MailPress_colors'] as $src )	wp_enqueue_style($src);
	}

//// scripts ////

	public static function deregister_scripts($x)
	{
		$x[] = MailPress_page_mails;
		$x[] = MailPress_page_write;
		$x[] = MailPress_page_edit;
		$x[] = MailPress_page_revision;
		$x[] = MailPress_page_mail;
		$x[] = MailPress_page_design;
		$x[] = MailPress_page_settings;
		$x[] = MailPress_page_users;
		$x[] = MailPress_page_user;
		return $x;
	}

	public static function register_scripts() {
		global $hook_suffix, $mp_screen;
		
		$mp_screen = self::screen_meta_screen($hook_suffix);

		wp_register_script( 'mp-ajax-response', 		'/' . MP_PATH . 'mp-includes/js/mp-ajax-response.js', array('jquery'), false, 1);
		wp_localize_script( 'mp-ajax-response', 		'wpAjax', array( 
			'noPerm' => __('Email was not sent AND/OR Update database failed','MailPress'), 
			'broken' => __('An unidentified error has occurred.'), 
			'l10n_print_after' => 'try{convertEntities(wpAjax);}catch(e){};' 
		) );

		wp_register_script( 'mp-autosave', 			'/' . MP_PATH . 'mp-includes/js/autosave.js', array('schedule', 'mp-ajax-response'), false, 1);
		wp_localize_script( 'mp-autosave', 			'autosaveL10n', array	( 	
			'autosaveInterval'=> '60',
			'previewMailText'	=>  __('Preview'),
			'requestFile' 	=> get_option('siteurl') . '/' . MP_PATH . 'mp-includes/action.php',
			'savingText'	=> __('Saving draft...','MailPress')
		) );

		wp_register_script( 'mp-lists', 			'/' . MP_PATH . 'mp-includes/js/mp-lists.js', array('mp-ajax-response'), false, 1);
		wp_localize_script( 'mp-lists', 			'wpListL10n', array( 
			'url' => get_option( 'siteurl' ) . '/' . MP_PATH . 'mp-includes/action.php'
		) );

		wp_register_script( 'mp_swf_upload', '/' . MP_PATH . 'mp-includes/js/fileupload/swf.js', array('swfupload'), false, 1);
	// these error messages came from the sample swfupload js, they might need changing.
		wp_localize_script( 'mp_swf_upload', 'swfuploadL10n', array(
			'queue_limit_exceeded' => __('You have attempted to queue too many files.'),
			'file_exceeds_size_limit' => sprintf(__('This file is too big. Your php.ini upload_max_filesize is %s.'), @ini_get('upload_max_filesize')),
			'zero_byte_file' => __('This file is empty. Please try another.'),
			'invalid_filetype' => __('This file type is not allowed. Please try another.'),
			'default_error' => __('An error occurred in the upload. Please try again later.'),
			'missing_upload_url' => __('There was a configuration error. Please contact the server administrator.'),
			'upload_limit_exceeded' => __('You may only upload 1 file.'),
			'http_error' => __('HTTP error.'),
			'upload_failed' => __('Upload failed.'),
			'io_error' => __('IO error.'),
			'security_error' => __('Security error.'),
			'file_cancelled' => __('File cancelled.'),
			'upload_stopped' => __('Upload stopped.'),
			'dismiss' => __('Dismiss'),
			'crunching' => __('Crunching&hellip;'),
			'deleted' => __('Deleted'),
			'l10n_print_after' => 'try{convertEntities(swfuploadL10n);}catch(e){};'
		) );

		wp_register_script( 'mp_html_sifiles', '/' . MP_PATH . 'mp-includes/js/fileupload/si.files.js', array(), false, 1);
		wp_register_script( 'mp_html_upload', '/' . MP_PATH . 'mp-includes/js/fileupload/htm.js', array('mp_html_sifiles'), false, 1);
		wp_localize_script( 'mp_html_upload', 'htmuploadL10n', array(
			'img' => get_option('siteurl') . '/' . MP_PATH . 'mp-includes/images/htmloading.gif',
			'iframeurl' => get_option( 'siteurl' ) . '/' . MP_PATH . 'mp-includes/action.php',
			'uploading' => __('Uploading ...','MailPress'),
			'attachfirst' => __('Attach a file','MailPress'),
			'attachseveral' => __('Attach another file','MailPress'),
			'l10n_print_after' => 'try{convertEntities(htmuploadL10n);}catch(e){};' 
		) );

		wp_register_script( MailPress_page_mails, 	'/' . MP_PATH . 'mp-admin/js/mails.js', array('thickbox', 'mp-lists'), false, 1);
		wp_localize_script( MailPress_page_mails, 	'adminmailsL10n', array(
			'pending' => __('%i% pending'),
			'screen' => $mp_screen,
			'l10n_print_after' => 'try{convertEntities(adminmailsL10n);}catch(e){};' 
		) );

		wp_register_script( MailPress_page_mail, 		'/' . MP_PATH . 'mp-admin/js/mail.js', array('jquery-ui-tabs'), false, 1);

		$w = array('quicktags', 'mp-autosave', 'mp-lists', 'postbox');
		if ( user_can_richedit() )	$w[] = 'editor';
		$w[] = 'thickbox';
		$w[] = (MP_Mail::flash()) ? 'mp_swf_upload' : 'mp_html_upload';
		wp_register_script( MailPress_page_write, 	'/' . MP_PATH . 'mp-admin/js/mail_new.js', $w, false, 1);
		wp_localize_script( MailPress_page_write, 	'mailnewL10n', array( 	
			'errmess' => __('Enter a valid email !','MailPress'),
			'screen' => $mp_screen,
			'l10n_print_after' => 'try{convertEntities(mailnewL10n);}catch(e){};' 
		) );

		wp_register_script( MailPress_page_design, 	'/' . MP_PATH . 'mp-admin/js/themes.js', array( 'thickbox', 'jquery' ), false, 1);

		wp_register_script( MailPress_page_settings, 	'/' . MP_PATH . 'mp-admin/js/settings.js', array('jquery-ui-tabs'), false, 1);

		wp_register_script( MailPress_page_users, 	'/' . MP_PATH . 'mp-admin/js/users.js', array('mp-lists'), false, 1);
		wp_localize_script( MailPress_page_users, 	'adminusersL10n', array(
			'pending' => __('%i% pending'),
			'screen' => $mp_screen,
			'l10n_print_after' => 'try{convertEntities(adminusersL10n);}catch(e){};' 
		) );

		wp_register_script( MailPress_page_user, 		'/' . MP_PATH . 'mp-admin/js/user.js', array('jquery-ui-tabs', 'postbox'), false, 1);
		wp_localize_script( MailPress_page_user, 		'adminuserL10n', array(
			'screen' => $mp_screen
		) );

		do_action('MailPress_register_scripts');
	}

// for appropriate javascript
	public static function enqueue_header_scripts() { self::enqueue_scripts(false); }
	public static function enqueue_footer_scripts() { self::enqueue_scripts(true); }
	public static function enqueue_scripts($is_footer) {

		global $mp_general;

		$page = self::get_page();
		if (!$page) return;

		$x = array(	MailPress_page_mails		=> array	(	MailPress_page_mails	),
				MailPress_page_mail         	=> array	(	MailPress_page_mail	),
				MailPress_page_write		=> array	(	MailPress_page_write	),
				MailPress_page_design		=> array	(	MailPress_page_design	),
				MailPress_page_settings		=> array	(	MailPress_page_settings	),
				MailPress_page_users		=> array	(	MailPress_page_users	),
				MailPress_page_user         	=> array	(	MailPress_page_user	)
				);

		$x = apply_filters('MailPress_enqueue_scripts',$x);

		$x[MailPress_page_edit] = $x[MailPress_page_write];

		if (!isset($x[$page])) return;
		foreach ($x[$page] as $src )	wp_enqueue_script($src);

		if (( MailPress_page_write != $page) && ( MailPress_page_edit != $page)) return;

		$_footer_enabled = ($GLOBALS['wp_version'] >= '2.8') ? true : false;
		$action = ($_footer_enabled && $is_footer) ? 'admin_footer' : 'admin_head' ;

		if ($_footer_enabled && !$is_footer) return;
		add_action($action,'wp_tiny_mce');
		if (MP_Mail::flash()) add_action($action,array('MP_Mail','swfupload'));
	}

////	menus	////

	public static function menu() {

		global $mp_general;
		$m = (isset($mp_general['menu'])) ? true : false;
		$x = self::capabilities();
		$i = 0;
		$y = array();

		foreach ($x as $k => $v)
		{
			if ($v['menu'] && current_user_can($k)) 
			{
				$y[$i]['capability'] = $k;
				foreach ($v as $vk => $vv)
				{
					$y[$i][$vk] = $vv;
				}
				$i++;
			}
		}
		$county = count($y);
		if (0 == $county) return;

		if ($county > 1 )
		{
			do
			{
				$sorting = '';
				for ($i = 0; $i <= ($county - 2); $i++) 
				{
					if ($y[$i]['menu'] > $y[$i + 1]['menu'])
					{
						$sorting = $y[$i];
						$y[$i] = $y[$i + 1];
						$y[$i + 1] = $sorting;
					}
				}
			} while ($sorting != '');
		}

		$first = true;
		foreach ($y as $vy)
		{
			if (!$vy['parent'])
			{
				if ($first)
				{
					$toplevel = $vy['page'];
					if ($m) 								add_menu_page('toto', __('Mails','MailPress'), $vy['capability'], $vy['page'], $vy['func'], 'div');
					elseif ('MailPress_edit_mails' == $vy['capability']) 	add_menu_page('toto', __('Mails','MailPress'), $vy['capability'], $vy['page'], $vy['func'], 'div');
				}
				$first = false;
			}

			if ($vy['page'] == MailPress_page_mails)
			{
				add_submenu_page($toplevel, __('View All Mails','MailPress'),	__('Edit'), 			'MailPress_edit_mails', MailPress_page_mails, array('MP_Admin', MailPress_page_mails));
				add_submenu_page($toplevel, __('Write Mail','MailPress'),		__('New Mail','MailPress'), 	'MailPress_edit_mails', MailPress_page_write, array('MP_Admin', MailPress_page_write));
			}
			else
			{
				$parent = ($vy['parent']) ? $vy['parent'] : $toplevel;
				add_submenu_page( $parent, $vy['page_title'], $vy['menu_title'], $vy['capability'], $vy['page'], $vy['func']);
			}
		}
	}

//// Screen Options ////

	public static function screen_meta() {

		global $mp_screen;

		$page = self::get_page();
		if (!$page) return;

		switch ($page)
		{
			case MailPress_page_write :
				add_meta_box('submitdiv', 		__('Send','MailPress'), 		array('MP_Mail', 'submit_meta_box'), 	$mp_screen, 'side', 'core');
				add_meta_box('plaintextbox', 		__('Plain Text','MailPress'), 	array('MP_Mail', 'plaintext_meta_box'), 	$mp_screen, 'normal', 'high');
				if (isset($_GET['id']))
				{
					$draft 	= MP_Mail::get($_GET['id']);
					$rev_ids 	= MP_Mailmeta::get($draft->id,'_MailPress_mail_revisions');
				}
				if ($rev_ids)
					add_meta_box('revisionbox', 	__('Mail Revisions','MailPress'), 	array('MP_Mail', 'revision_meta_box'), 	$mp_screen, 'normal', 'high');
				do_action('MailPress_mailnew_boxes',$_GET['id'],$mp_screen);

				add_meta_box('attachementsdiv',	__('Attachements','MailPress'), 	array('MP_Mail', 'attachements_meta_box'),$mp_screen, 'normal', 'core');
				$help	= sprintf(__('<a href="%1$s" target="_blank">Documentation</a>','MailPress'),MailPress_help_url);
				$help	.= '<br />' . sprintf(__('<a href="%1$s" target="_blank">Support Forum</a>','MailPress'),'http://groups.google.com/group/mailpress');
				add_contextual_help($mp_screen, $help);
			break;
			case MailPress_page_revision :
				$help	= sprintf(__('<a href="%1$s" target="_blank">Documentation</a>','MailPress'),MailPress_help_url);
				$help	.= '<br />' . sprintf(__('<a href="%1$s" target="_blank">Support Forum</a>','MailPress'),'http://groups.google.com/group/mailpress');
				add_contextual_help($mp_screen, $help);
			break;
			case MailPress_page_edit :
				add_meta_box('submitdiv', 		__('Send','MailPress'), 		array('MP_Mail', 'submit_meta_box'), 	$mp_screen, 'side', 'core');
				add_meta_box('plaintextbox', 		__('Plain Text','MailPress'), 	array('MP_Mail', 'plaintext_meta_box'), 	$mp_screen, 'normal', 'high');
				if (isset($_GET['id']))
				{
					$draft 	= MP_Mail::get($_GET['id']);
					$rev_ids 	= MP_Mailmeta::get($draft->id,'_MailPress_mail_revisions');
				}
				if ($rev_ids)
					add_meta_box('revisionbox', 	__('Mail Revisions','MailPress'), 	array('MP_Mail', 'revision_meta_box'), 	$mp_screen, 'normal', 'high');
				do_action('MailPress_mailnew_boxes',$_GET['id'],$mp_screen);

				add_meta_box('attachementsdiv',	__('Attachements','MailPress'), 	array('MP_Mail', 'attachements_meta_box'),$mp_screen, 'normal', 'core');
				$help	= sprintf(__('<a href="%1$s" target="_blank">Documentation</a>','MailPress'),MailPress_help_url);
				$help	.= '<br />' . sprintf(__('<a href="%1$s" target="_blank">Support Forum</a>','MailPress'),'http://groups.google.com/group/mailpress');
				add_contextual_help($mp_screen, $help);
			break;
			case MailPress_page_user :
				$mp_user = (isset($_POST['id'])) ? MP_User::get( $_POST['id'] ) : MP_User::get( $_GET['id'] );

				add_meta_box('submitdiv', 		__('Save','MailPress'), array('MP_User','submit_meta_box'), $mp_screen, 'side', 'core');
				$metas = MP_Usermeta::get($mp_user->id);
				if ($metas) 
				{
					if (!is_array($metas)) $metas = array($metas);
					foreach ($metas as $meta)
					{
						if ($meta->meta_key[0] == '_') continue;
						if (class_exists('MailPress_custom_fields')) if ( current_user_can( 'MailPress_custom_fields') ) continue;
						if (class_exists('MailPress_user_custom_fields')) if ( current_user_can('MailPress_user_custom_fields') ) continue;
						add_meta_box('mp_usermetadiv', __('Custom Fields') , array('MP_User','usermeta_meta_box'), $mp_screen, 'advanced', 'low');
						break;
					}
				}
				do_action('MailPress_user_boxes',$mp_user->id,$mp_screen);

				$help	= sprintf(__('<a href="%1$s" target="_blank">Documentation</a>','MailPress'),MailPress_help_url);
				$help	.= '<br />' . sprintf(__('<a href="%1$s" target="_blank">Support Forum</a>','MailPress'),'http://groups.google.com/group/mailpress');
				add_contextual_help($mp_screen, $help);
			break;
			case MailPress_page_users :
				add_filter('manage_' . $mp_screen . '_columns',array('MP_User','manage_list_columns'));

				$help	= sprintf(__('<a href="%1$s" target="_blank">Documentation</a>','MailPress'),MailPress_help_url);
				$help	.= '<br />' . sprintf(__('<a href="%1$s" target="_blank">Support Forum</a>','MailPress'),'http://groups.google.com/group/mailpress');
				add_contextual_help($mp_screen, $help);
			break;
			case MailPress_page_mails :
				add_filter('manage_' . $mp_screen . '_columns',array('MP_Mail','manage_list_columns'));

				$help	= sprintf(__('<a href="%1$s" target="_blank">Documentation</a>','MailPress'),MailPress_help_url);
				$help	.= '<br />' . sprintf(__('<a href="%1$s" target="_blank">Support Forum</a>','MailPress'),'http://groups.google.com/group/mailpress');
				add_contextual_help($mp_screen, $help);
			break;
			case MailPress_page_settings :
				$help	= sprintf(__('<a href="%1$s" target="_blank">Documentation</a>','MailPress'),MailPress_help_url . '#fragment-3');
				$help	.= '<br />' . sprintf(__('<a href="%1$s" target="_blank">Support Forum</a>','MailPress'),'http://groups.google.com/group/mailpress');
				add_contextual_help($mp_screen, $help);
			break;
			case MailPress_page_design :
				$help	= sprintf(__('<a href="%1$s" target="_blank">Building your own MailPress theme</a>','MailPress'),MailPress_help_url . '#fragment-4');
				$help	.= '<br />' . sprintf(__('<a href="%1$s" target="_blank">Support Forum</a>','MailPress'),'http://groups.google.com/group/mailpress');
				add_contextual_help($mp_screen, $help);
			break;
			default :
				do_action('MailPress_screen_meta',$page,$mp_screen);
			break;
		}
	}

	public static function screen_meta_screen($screen)
	{
		global $mp_screen;

		$page = self::get_page();
		if (!$page) return $screen;

		$mp_screen = $screen;

		switch ($page)
		{
			case MailPress_page_write :
				$mp_screen = MailPress_page_write;
			break;
			case MailPress_page_revision :
				$mp_screen = MailPress_page_write;
			break;
			case MailPress_page_edit :
				$mp_screen = MailPress_page_write;
			break;
			case MailPress_page_user :
				$mp_screen = MailPress_page_users;
			break;
			case MailPress_page_users :
				$mp_screen = MailPress_page_users;
			break;
			case MailPress_page_mails :
				$mp_screen = MailPress_page_mails;
			break;
			case MailPress_page_settings :
				$mp_screen = MailPress_page_settings;
			break;
			case MailPress_page_design :
				$mp_screen = MailPress_page_design;
			break;
			default :
				$mp_screen = apply_filters('MailPress_screen_meta_screen', $mp_screen, $page);
			break;
		}
		return $mp_screen;
	}

	public static function contextual_help($x) 
	{
		global $mp_screen, $_wp_contextual_help, $title;

		if ( !isset($_wp_contextual_help) )	$_wp_contextual_help = array();

		if ( !isset($_wp_contextual_help[$mp_screen]) ) return $x;

		$contextual_help = '';
		$contextual_help .= "<small style='float:right;padding:10px 15px 0;'>" . __('(Please Donate)','MailPress') . '</small>';
		$contextual_help .= ( !empty($title) ) ? '<h5>' . sprintf(__('Get help with "%s"'), $title) . '</h5>' : '<h5>' . __('Get help with this page') . '</h5>';

		$contextual_help .= '<div class="metabox-prefs">';
		$contextual_help .= $_wp_contextual_help[$mp_screen];
		$contextual_help .= "</div>\n";
		return $contextual_help;
	}

// for appropriate favorite actions

	public static function favorite_actions($actions) {
		$actions[MailPress_write] = array(__('New Mail','MailPress'),'MailPress_edit_mails');
		return $actions;
	}

//// GENERIC ADMIN FUNCTIONS ////

	public static function tableExists($table) {
		global $wpdb;
		return strcasecmp($wpdb->get_var("show tables like '$table'"), $table) == 0;
	}

// for input text

	public static function input_text($x) 
	{  
		return str_replace('"',"&QUOT;",stripslashes($x));
	}

// to print messages

	public static function message($s, $b = true, $id = 'moderated'){
		if ( $b ) 	echo "<div id='$id' class='updated fade'><p>$s</p></div>";
	 	else 		echo "<div id='$id' class='error'><p>$s</p></div>";
	}


// for parms & urls

	public static function get_url_parms($parms = array('mode','status','s','apage','author','startwith'))
	{
		foreach ($parms as $v)
		{
			if (isset($_GET[$v]))  $url_parms[$v] = attribute_escape($_GET[$v]);
			if (isset($_POST[$v])) $url_parms[$v] = attribute_escape($_POST[$v]);
		}
		if (1 == $url_parms['apage']) 	unset($url_parms['apage']);
		if (0 == $url_parms['author'])	unset($url_parms['author']);
		$url_parms['mode'] = (isset($url_parms['mode'])) ? $url_parms['mode'] : 'list';
		return $url_parms;
	}

	public static function post_url_parms($url_parms,$parms = array('mode','status','s','apage','author'))
	{
		foreach ($parms as $v)
		{
			if (!isset($url_parms[$v])) continue;
?>
		<input type='hidden' name='<?php echo $v; ?>' value='<?php echo $url_parms[$v]; ?>' />
<?php
		}
	}

	public static function url($url,$wpnonce=false,$url_parms) {
		foreach ($url_parms as $key => $value) if ($value) if ('' == $url) $url .= '?' . $key . '=' . $value; else $url .= '&' . $key . '=' . $value;	
		if ($wpnonce) $url = clean_url( wp_nonce_url( $url, $wpnonce ) );
		return $url;
	}

// for admin lists

	public static function update_cache($xs,$y) {
		foreach ( (array) $xs as $x ) wp_cache_add($x->id, $x, $y);
	}


// for select

	public static function select_option($list, $selected, $echo=true)
	{
		if (!$echo)	ob_start();
		foreach($list as $key=>$value)
		{
			echo("\t\t\t\t\t\t\t\t\t\t\t\t"); 
?>
<option <?php selected( (string) $key, (string) $selected); ?> value='<?php echo $key; ?>'><?php echo $value; ?></option>
<?php
		}
		if (!$echo)
		{
				$x = ob_get_contents();
			ob_end_clean();
			return $x; 
		}
	}

	public static function select_number($start, $max, $selected, $tick=1, $echo=true)
	{
		if (!$echo)	ob_start();
		while ($start <= $max)
		{
			if (intval ($start/$tick) == $start/$tick ) 
			{
			echo("\t\t\t\t\t\t\t\t\t\t\t\t"); 
?>
<option <?php selected($start,$selected); ?> value='<?php echo $start; ?>'><?php echo $start; ?></option>
<?php
			}
		$start++;
		}
		if (!$echo)
		{
				$x = ob_get_contents();
			ob_end_clean();
			return $x; 
		}
	}

	public static function usort($a,$b,$key,$sort='asc') 
	{  
		switch (true)
		{
			case  ($a[$key] == $b[$key]) :
				$x = 0;
			break;
			case  ($a[$key] > $b[$key]) :
				$x = 1;
			break;
			default :
				$x = -1;
			break;
		}
		$s = ('desc' == $sort) ? -1 : 1;

		return $x * $s;
	}

	public static function print_scripts_l10n_val($val0,$before="")
	{
		if (is_array($val0))
		{
			$eol = "\t\t";
			$text =  "{\n\t$before";
			foreach($val0 as $var => $val)
			{
				$text .=  "$eol$var: " . self::print_scripts_l10n_val($val, "\t" . $before );
				$eol = ",\n$before\t\t\t";
			}
			$text .= "\n\t\t$before}";
		}
		else
		{
			$quot = (stripos($val0,'"') === false) ? '"' : "'";
			$text = "$quot$val0$quot";
		}
		return $text;
	}
}
?>