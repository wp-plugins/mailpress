<?php
include (MP_TMP . '/mp-admin/includes/dashboard.php');

register_activation_hook(MP_FOLDER . '/MailPress.php',	array('MP_Admin','install'));

add_action('init',		array('MP_Admin','roles_and_capabilities'));

add_action('admin_init', 	array('MP_Admin','mp_redirect'));
add_action('admin_init', 	array('MP_Admin','title'));
add_action('admin_init', 	array('MP_Admin','register_scripts'));
add_action('admin_init', 	array('MP_Admin','enqueue_scripts'));
add_action('admin_init',	'mp_sidebar_admin_setup');

add_action('admin_head', 	array('MP_Admin','enqueue_css'));

add_action('admin_menu', 	array('MP_Admin','menu'),  8, 1);

// for javascript plugin conflicts
add_filter('MailPress_deregister_scripts', array('MP_Admin','deregister_scripts'), 10, 1 );

class MP_Admin
{

////	install plugin	////
	function system_requirements() {
		global $wp_version; 

		$min_ver_php = '5.2.0';
		$min_ver_wp = '2.6';
		$max_ver_wp = '2.6.9';
		$m = array();

		if (version_compare(PHP_VERSION, $min_ver_php, '<')) 	$m[] = sprintf(__('Your %1$s version is \'%2$s\', at least version \'%3$s\' required.','MailPress'), __('PHP'), PHP_VERSION, $min_ver_php );

		if (version_compare($wp_version, $min_ver_wp, '<'))	$m[] = sprintf(__('Your %1$s version is \'%2$s\', at least version \'%3$s\' required.','MailPress'), __('WordPress'), $wp_version , $min_ver_wp );

		if (version_compare($wp_version, $max_ver_wp, '>'))	$m[] = sprintf(__('Your %1$s version is \'%2$s\', upgrade available at %3$s.','MailPress'), 'WordPress', $wp_version , "<a href='http://www.mailpress.org'>mailpress.org</a>");

		if (!is_writable(ABSPATH . MP_PATH . 'tmp'))		$m[] = sprintf(__('The directory \'%1$s\' is not writable.','MailPress'), ABSPATH . MP_PATH . 'tmp');

		return $m;
	}

	function install() {

		$m = MP_Admin::system_requirements();

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

	function tableExists($table) {
		global $wpdb;
		return strcasecmp($wpdb->get_var("show tables like '$table'"), $table) == 0;
	}

// manage roles and capabilities

	function capabilities()
	{
		$x	= array(	'MailPress_edit_dashboard' 	=> array('name' => __('Dashboard','MailPress'),'group' => 'admin'),
					'MailPress_edit_mails'		=> array('name' => __('Edit mails','MailPress'),'group' => 'mails'),
					'MailPress_edit_others_mails'	=> array('name' => __('Edit others mails','MailPress'),'group' => 'mails'),
					'MailPress_send_mails'		=> array('name' => __('Send mails','MailPress'),'group' => 'mails'),
					'MailPress_delete_mails'	=> array('name' => __('Delete mails','MailPress'),'group' => 'mails'),
					'MailPress_switch_themes'	=> array('name' => __('Design','MailPress'),'group' => 'admin'),
					'MailPress_manage_options'	=> array('name' => __('Settings','MailPress'),'group' => 'admin'),
					'MailPress_edit_users'		=> array('name' => __('Edit users','MailPress'),'group' => 'users'),
					'MailPress_delete_users'	=> array('name' => __('Delete users','MailPress'),'group' => 'users')
				);

		$x	= apply_filters('MailPress_capabilities',$x);

		return $x;
	}

	function roles_and_capabilities() {

		$x	= MP_Admin::capabilities();

		$role = get_role('administrator');

		foreach ($x as $capability => $v) $role->add_cap($capability);

		do_action('MailPress_roles_and_capabilities');
	}

// managing wp_redirect

	function mp_redirect() {
		global $mp_general;

		$page = MP_Admin::get_page();
		if (!$page) return;

		switch (true)
		{
			case (MailPress_page_write . 'revision' == $page) :						// MANAGING REVISION

				$action      = $_GET['action'];
				$revision_id = absint($_GET['revision']);
				$id 		 = absint($_GET['id']);
				$diff        = absint($_GET['diff']);
				$left        = absint($_GET['left']);
				$right       = absint($_GET['right']);

				$redirect_to = MailPress_write;

				switch ( $action )
				{
					case 'delete' :
					break;
					case 'edit' :
					break;
					case 'restore' :
						if (!$revision = MP_Mail::get_mail($revision_id)) break;

						$mail = MP_Mail::get_mail($id);

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
						if ( !$left_revision  = MP_Mail::get_mail( $left ) )
							break;
						if ( !$right_revision = MP_Mail::get_mail( $right ) )
							break;

						if ( strtotime($right_revision->created) < strtotime($left_revision->created) ) 
						{
							$redirect_to = MailPress_revision;
							$redirect_to .= "&action=diff&id=$id&left=$right_revision->id&right=$left_revision->id";
							break;
						}

						if ($left_revision->id  == $id) $left_ok = true;
						if ($right_revision->id == $id) $right_ok = true;
						$rev_ids = MP_Mail::get_mail_meta($id,'_MailPress_mail_revisions');
						foreach ($rev_ids as $v) if ($left_revision->id  == $v) $left_ok = true;
						foreach ($rev_ids as $v) if ($right_revision->id == $v) $right_ok = true;
						if (!($left_ok && $right_ok)) break;
						$redirect_to = false;
					break;
					case 'view' :
					default :
 						if ( !$revision = MP_Mail::get_mail( $revision_id ) )
							break;
						if ( !$mail = MP_Mail::get_mail( $id ) )
							break;

						$redirect_to = false;
					break;
				}
				if ($redirect_to) wp_redirect( $redirect_to );
			break;
			case ((MailPress_page_mails == $page) && !empty( $_POST['delete_mails'] )) :		// MANAGING CHECKBOX REQUESTS

				$url_parms = MP_Admin::get_url_parms();
				$deleted = $sent = $notsent = 0;
				foreach ($_POST['delete_mails'] as $id) : 							
					switch (true)
					{
						case ( isset( $_POST['deleteit'] )):
							MP_Mail::delete($id);
							$deleted++;
						break;
						case (isset( $_POST['sendit'] )):
							$x = MP_Mail::send_draft($id);
							$url = (is_numeric($x))	? $sent += $x : $notsent++ ;
						break;
					}
				endforeach;
				$redirect_to  = MailPress_mails;
			 	$redirect_to .= ($deleted) 	? 	'&deleted='	. $deleted : '';
			 	$redirect_to .= ($sent) 	?	'&sent=' 	. $sent : '';
			 	$redirect_to .= ($notsent) 	?	'&notsent='	. $notsent : '';
				$redirect_to = MP_Admin::url($redirect_to ,false,$url_parms);
				wp_redirect( $redirect_to );
			break;
			case (MailPress_page_mails . 'mail' == $page) : 
				$list_url = MP_Admin::url(MailPress_mails,false,MP_Admin::get_url_parms());
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
						$id = (0 == $_POST['id']) ? MP_Mail::get_id() : $_POST['id'];
						MP_Mail::update_draft($id);
/* revisions */
/*
						if ($rev_ids = MP_Mail::get_mail_meta($id,'_MailPress_mail_revisions'))
						{
							global $current_user;
							if (isset($rev_ids[$current_user->ID]))
							{
								MP_Mail::delete($rev_ids[$current_user->ID]);
								unset($rev_ids[$current_user->ID]);
								if (array() == $rev_ids)	MP_Mail::delete_mail_meta($id,'_MailPress_mail_revisions');
								else					MP_Mail::update_mail_meta($id,'_MailPress_mail_revisions',$rev_ids);
							}
						}
*/
						$url = (strstr($_SERVER['HTTP_REFERER'],MailPress_write)) ? MailPress_write . "&saved=1&id=" . $id : MailPress_mail  . "&action=view&saved=1&id=" . $id;
						if (isset($_POST['send']))
						{
							$x = MP_Mail::send_draft($id);
							if (is_numeric($x))
								if (0 == $x)	$url = (strstr($_POST['referredby'],MailPress_write)) ? MailPress_write . "&sent=0&id=" . $id : MailPress_mail  . "&action=view&sent=0&id=" . $id;
								else			$url = MailPress_write . "&sent=$x&id=" . $id;
							else				$url = MailPress_write . "&nodest=0&id=" . $id;
						}
						wp_redirect($url);
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
			case (MailPress_page_settings == $page) :
			
				if ($_POST['formname'] != 'generalform') return;

				$oldmenu = (isset($mp_general['menu'])) 		? $mp_general['menu'] : false;
				$newmenu = (isset($_POST['general']['menu']))	? $_POST['general']['menu'] : false;

				$mp_general			= $_POST['general'];
				$mp_general['tab']	= 0;

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
			case ((MailPress_page_users == $page) && !empty( $_POST['delete_users'] )) :		// MANAGING CHECKBOX REQUESTS
				$url_parms = MP_Admin::get_url_parms();
				$deleted = $activated = $deactivated = 0;
				foreach ($_POST['delete_users'] as $id) : 							
					switch (true)
					{
						case ( isset( $_POST['deleteit'] )):
							MP_User::set_user_status($id, 'delete');
							$deleted++;
						break;
						case (isset( $_POST['activateit'] )):
							MP_User::set_user_status($id, 'active');
							$activated++;
						break;
						case (isset( $_POST['deactivateit'] )):
							MP_User::set_user_status($id, 'waiting');
							$deactivated++;
						break;
					}
				endforeach;
				$redirect_to  = MailPress_users;
				$redirect_to .= ($deleted) 		? '&deleted=' 	. $deleted : '';
				$redirect_to .= ($activated) 		? '&activated=' 	. $activated : '';
				$redirect_to .= ($deactivated) 	? '&deactivated=' . $deactivated : '';
				$redirect_to = MP_Admin::url($redirect_to ,false,$url_parms);
				wp_redirect( $redirect_to );
			break;
			case (MailPress_page_users . 'uzer' == $page) :
				$list_url = MP_Admin::url(MailPress_users,false,MP_Admin::get_url_parms());

				if ( isset($_POST['action']) ) $action = $_POST['action'];
				elseif ( isset($_GET['action']) ) $action = $_GET['action'];  

				switch($action) 
				{
					case 'delete':
						$id = $_GET['id'];
						MP_User::set_user_status( $id, 'delete' );
						wp_redirect($list_url);
					break;
					case 'activate':
						$id = $_GET['id'];
						MP_User::set_user_status( $id, 'active' );
						wp_redirect($list_url);
					break;
					case 'deactivate':
						$id = $_GET['id'];
						MP_User::set_user_status( $id, 'waiting' );
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

// for appropriate title

	function title() {

		$page = MP_Admin::get_page();
		if (!$page) return;
		$page .= (isset($_GET['id']))   ? 'id' 		: '';

		$x = array(
				MailPress_page_write . 'revisionid'	=> __('MailPress Mail Revisions','MailPress'),
				MailPress_page_write . 'id'		=> __('MailPress Edit','MailPress'),
				MailPress_page_mails . 'mail'		=> __('MailPress View Draft','MailPress'),
				MailPress_page_mails . 'mailid'	=> __('MailPress View','MailPress'),
				MailPress_page_users . 'uzerid'	=> __('MailPress User','MailPress')
			);

		$x = apply_filters('MailPress_title',$x);

		if (!isset($x[$page])) return;
		
		global $title;
		$title = $x[$page];
	}

// for javascript

	function register_scripts() {

		wp_register_script( 'mp-autosave', 	'/' . MP_PATH . 'mp-includes/js/autosave.js');
		wp_localize_script( 'mp-autosave', 	'autosaveL10n', array	( 	'autosaveInterval'=> '60',
													'previewMailText'	=>  __('Preview this mail','MailPress'),
													'requestFile' 	=> get_option('siteurl') . '/' . MP_PATH . 'mp-includes/action.php',
													'savingText'	=> __('Save this draft','MailPress') ) );

		wp_register_script( 'mp-mail-new', 	'/' . MP_PATH . 'mp-admin/js/mail-new.js');
		wp_localize_script( 'mp-mail-new', 	'mailnewL10n', array( 'errmess' => __('Enter a valid email !','MailPress') ) );

		wp_register_script( 'mp-mails', 	'/' . MP_PATH . 'mp-admin/js/mails.js');
		wp_localize_script( 'mp-mails', 	'adminmailsL10n', array('pending' => __('%i% pending') ) );

		wp_register_script( 'mp-mail', 	'/' . MP_PATH . 'mp-admin/js/mail.js');

		wp_register_script( 'mp-themes', 	'/' . MP_PATH . 'mp-admin/js/themes.js', array( 'thickbox', 'jquery' ));

		wp_register_script( 'mp-settings', 	'/' . MP_PATH . 'mp-admin/js/settings.js');

		wp_register_script( 'mp-users', 	'/' . MP_PATH . 'mp-admin/js/users.js');
		wp_localize_script( 'mp-users', 	'adminusersL10n', array('pending' => __('%i% pending') ) );

		wp_register_script( 'mp-user', 	'/' . MP_PATH . 'mp-admin/js/user.js');

		wp_register_script( 'mp-postbox', 	'/wp-admin/js/postbox.js', array('jquery'));
		wp_localize_script( 'mp-postbox', 	'postboxL10n', array( 'requestFile' => get_option('siteurl') . '/' . MP_PATH . 'mp-includes/action.php' ) );

		wp_register_script( 'mp-ajax-response', 	'/wp-includes/js/wp-ajax-response.js');
		wp_localize_script( 'mp-ajax-response', 'wpAjax', array( 'noPerm' => __('Email was not sent AND/OR Update database failed','MailPress'), 'broken' => __('An unidentified error has occurred.') ) );

		wp_register_script( 'mp-lists', 	'/' . MP_PATH . 'mp-includes/js/mp-lists.js');
		wp_localize_script( 'mp-lists', 	'wpListL10n', array( 'url' => get_option( 'siteurl' ) . '/' . MP_PATH . 'mp-includes/action.php' ) );

		do_action('MailPress_register_scripts');
	}

// for appropriate javascript

	function enqueue_scripts() {
		global $mp_general;

		$page = MP_Admin::get_page();
		if (!$page) return;

		$x = array	(
				MailPress_page_write		=> array	(	'jquery-ui-tabs',
												'jquery-color',
												'jquery-schedule',
												'mp-ajax-response',
												'mp-lists',
												'mp-postbox',
												'media-upload',
												'schedule',
												'mp-mail-new',
												'mp-autosave'
											),
				MailPress_page_mails		=> array	(	'admin-forms',
												'mp-ajax-response',
												'mp-lists',
												'thickbox',
												'mp-mails'
											),
				MailPress_page_mails . 'mail'	=> array	(	'jquery-ui-tabs',
												'mp-mail'
											),

				MailPress_page_design		=> array	(	'jquery',
												'thickbox',
												'mp-themes'	
											),

				MailPress_page_settings		=> array	(	'jquery-ui-tabs',
												'mp-settings'
											),
				MailPress_page_users		=> array	(	'admin-forms',
												'mp-ajax-response',
												'mp-lists',
												'mp-users'
											),
				MailPress_page_users . 'uzer'	=> array	(	'jquery-ui-tabs',
												'mp-postbox',
												'mp-user'
											)
				);

		if ( user_can_richedit() )	$x[MailPress_page_write][] = 'editor';

		$x = apply_filters('MailPress_enqueue_scripts',$x);

		if (!isset($x[$page])) return;
		foreach ($x[$page] as $src )	wp_enqueue_script($src);
	}

// for appropriate css

	function enqueue_css() {

		$page = MP_Admin::get_page();
		if (!$page) return;

		$x = array	(
				MailPress_page_dashboard	=> array	(	'css/dashboard.css',
												get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/dashboard.css'
											),
				MailPress_page_write		=> array	(	//'css/media.css',
												get_option('siteurl') . '/wp-includes/js/thickbox/thickbox.css',
												get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/mail-new.css'
											),
				MailPress_page_mails		=> array	(	get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/admin' . get_user_option('admin_color') . '.css',
												get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/mails.css',
												get_option('siteurl') . '/wp-includes/js/thickbox/thickbox.css'
											),
				MailPress_page_mails . 'mail'	=> array	(	get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/mail.css'
											),
				MailPress_page_design		=> array	(	get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/themes.css',
												get_option('siteurl') . '/wp-includes/js/thickbox/thickbox.css'
											),
				MailPress_page_settings		=> array	(	get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/admin' . get_user_option('admin_color') . '.css',
												get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/settings.css'
											),
				MailPress_page_users		=> array	(	get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/admin' . get_user_option('admin_color') . '.css',
												get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/users.css'
											),
				MailPress_page_users . 'uzer'	=> array	(	get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/admin' . get_user_option('admin_color') . '.css',
												get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/user.css'
											)
				);

		$x = apply_filters('MailPress_enqueue_css',apply_filters('MailPress_admin_css',$x));

		if (!isset($x[$page])) return;
		foreach ($x[$page] as $src)	echo "<link rel='stylesheet' href='$src' type='text/css' title='MailPress' media='all' />\n";
	}

////	menus	////

	function menu() {
		global $mp_general;

		$x = (isset($_POST['general']['menu']) || isset($mp_general['menu'])) ? true : false;
		if ($x)
		{
			add_menu_page('MailPress', 'MailPress', 8, MailPress_page_dashboard);
			MP_Admin::general_menu( __('MailPress Dashboard','MailPress'),		__('Dashboard'), 	'MailPress_edit_dashboard', 	MailPress_page_dashboard);
			MP_Admin::general_menu( __('MailPress Create New Mail','MailPress'),	__('Write'), 	'MailPress_edit_mails', 	MailPress_page_write);
			MP_Admin::general_menu( __('MailPress Mails','MailPress'),			__('Manage'), 	'MailPress_edit_mails', 	MailPress_page_mails);
			MP_Admin::general_menu( __('MailPress Themes','MailPress'),			__('Design'), 	'MailPress_switch_themes', 	MailPress_page_design);
			do_action('MailPress_menu',$x);
			MP_Admin::general_menu( __('MailPress Settings','MailPress'),		__('Settings'), 	'MailPress_manage_options', 	MailPress_page_settings);
			MP_Admin::general_menu( __('MailPress Users','MailPress'),			__('Users'), 	'MailPress_edit_users', 	MailPress_page_users);
		}
		else					
		{
			add_submenu_page('index.php' , 	__('MailPress Dashboard','MailPress'), 		'MailPress', 'MailPress_edit_dashboard', 	MailPress_page_dashboard);
			add_submenu_page('post-new.php' , 	__('MailPress Create New Mail','MailPress'), 	'MailPress', 'MailPress_edit_mails', 	MailPress_page_write);
			add_management_page(			__('MailPress Mails','MailPress'), 			'MailPress', 'MailPress_edit_mails', 	MailPress_page_mails);
			add_theme_page(				__('MailPress Themes','MailPress'), 		'MailPress', 'MailPress_switch_themes', 	MailPress_page_design);
			do_action('MailPress_menu',$x);
			add_options_page(				__('MailPress Settings','MailPress'),		'MailPress', 'MailPress_manage_options', 	MailPress_page_settings);
			add_users_page(				__('MailPress Users','MailPress'), 			'MailPress', 'MailPress_edit_users', 	MailPress_page_users);
		}
		do_action('MailPress_menu_end',$x);
	}
	
	function general_menu($page_title, $menu_title, $access_level, $file, $function = '' )
	{
		add_submenu_page( MailPress_page_dashboard, $page_title , $menu_title , $access_level , $file , $function );
	}

	function deregister_scripts($x)
	{
		$x[] = MailPress_page_write;
		$x[] = MailPress_page_mails;
		$x[] = MailPress_page_mails .  'mail';
		$x[] = MailPress_page_design;
		$x[] = MailPress_page_settings;
		$x[] = MailPress_page_users;
		$x[] = MailPress_page_users . 'uzer';
		return $x;
	}

// to get plugin page

	function get_page() {
		if (!isset($_GET['page'])) return false;
		$page = $_GET['page'];
		if (isset($_GET['file'])) $page .= $_GET['file'];
		return $page;
	}

// to print messages

	function message($s, $b = true, $id = 'moderated'){
		if ( $b ) 	echo "<div id='$id' class='updated fade'><p>$s</p></div>";
	 	else 		echo "<div id='$id' class='error'><p>$s</p></div>";
	}

// for select

	function select_option($list, $selected)
	{
		foreach($list as $key=>$value)
		{
			echo("\t\t\t\t\t\t\t\t\t\t\t\t"); 
?>
<option <?php selected( (string) $key, (string) $selected); ?> value='<?php echo $key; ?>'><?php echo $value; ?></option>
<?php
		}
	}

	function select_number($start,$max,$selected,$tick=1)
	{
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
	}

// for parms & urls

	function get_url_parms($parms = array('mode','status','s','apage','author','startwith'))
	{
		foreach ($parms as $v)
		{
			if (isset($_GET[$v]))  $url_parms[$v] = attribute_escape($_GET[$v]);
			if (isset($_POST[$v])) $url_parms[$v] = attribute_escape($_POST[$v]);
		}
		if (1 == $url_parms['apage']) 	unset($url_parms['apage']);
		if (0 == $url_parms['author'])	unset($url_parms['author']);
		$url_parms['mode'] = (isset($url_parms['mode'])) ? $url_parms['mode'] : 'detail';
		return $url_parms;
	}

	function post_url_parms($url_parms,$parms = array('mode','status','s','apage','author','startwith'))
	{
		foreach ($parms as $v)
		{
			if (!isset($url_parms[$v])) continue;
?>
		<input type='hidden' name='<?php echo $v; ?>' value='<?php echo $url_parms[$v]; ?>'/>
<?php
		}
	}

	function url($url,$wpnonce=false,$url_parms) {
		foreach ($url_parms as $key => $value) if ($value) if ('' == $url) $url .= '?' . $key . '=' . $value; else $url .= '&' . $key . '=' . $value;	
		if ($wpnonce) $url = clean_url( wp_nonce_url( $url, $wpnonce ) );
		return $url;
	}

// for admin lists

	function update_cache($xs,$y) {
		foreach ( (array) $xs as $x ) wp_cache_add($x->id, $x, $y);
	}
}
?>