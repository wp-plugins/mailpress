<?php
include (MP_TMP . '/mp-admin/includes/dashboard.php');

add_action('admin_init', 	array('MP_Admin','mp_redirect'));
add_action('admin_init', 	array('MP_Admin','title'));
add_action('admin_init', 	array('MP_Admin','register_scripts'));
add_action('admin_init', 	array('MP_Admin','enqueue_scripts'));
add_action('admin_init',	'mp_sidebar_admin_setup');

add_action('admin_head', 	array('MP_Admin','enqueue_css'));

add_action('admin_menu', 	array('MP_Admin','menu'),  8, 1);

class MP_Admin
{

// managing wp_redirect

	function mp_redirect() {
		global $mp_general;

		$page = MP_Admin::get_page();
		if (!$page) return;

		switch (true)
		{
			case ((MP_FOLDER . '/mp-admin/mails.php' == $page) && !empty( $_POST['delete_mails'] )) :		// MANAGING CHECKBOX REQUESTS

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
			case (MP_FOLDER . '/mp-admin/mails.phpmail' == $page) : 
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
						if (!isset($_POST['view']))
						{
							$id = (isset($_POST['id'])) ? $_POST['id'] : MP_Mail::get_id();
							MP_Mail::update_draft($id);
							$url = (strstr($_SERVER['HTTP_REFERER'],MailPress_write)) ? MailPress_write . "&saved=1&id=" . $id : MailPress_mail  . "&action=view&saved=1&id=" . $id;
							if (isset($_POST['send']))
							{
								$x = MP_Mail::send_draft($id);
								if (is_numeric($x))
									if (0 == $x)	$url = (strstr($_POST['referredby'],MailPress_write)) ? MailPress_write . "&sent=0&id=" . $id : MailPress_mail  . "&action=view&sent=0&id=" . $id;
									else			$url = MailPress_mail  . "&action=view&sent=$x&id=" . $id;
								else				$url = MailPress_write . "&nodest=0&id=" . $id;
							}
							wp_redirect($url);
						}
					break;
				}
			break;
			case (MP_FOLDER . '/mp-admin/themes.php' == $page) :
				$th = new MP_Themes();

				$url = (isset($mp_general['menu'])) ? 'admin.php' : 'themes.php';
				$page = MP_FOLDER . '/mp-admin/themes.php';

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
			case (MP_FOLDER . '/mp-admin/settings.php' == $page) :
			
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
							if (!$newmenu) 	wp_redirect('options-general.php?page=' . MP_FOLDER . '/mp-admin/settings.php&saveg=ok'); 
							else			wp_redirect('admin.php?page=' . MP_FOLDER . '/mp-admin/settings.php&saveg=ok');
						}
					break;
				}
			break;
			case ((MP_FOLDER . '/mp-admin/users.php' == $page) && !empty( $_POST['delete_users'] )) :		// MANAGING CHECKBOX REQUESTS
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
			case (MP_FOLDER . '/mp-admin/users.phpuser' == $page) :
				$list_url = MP_Admin::url(MailPress_users,false,MP_Admin::get_url_parms());

				if ( isset($_POST['action']) ) $action = $_POST['action'];
				elseif ( isset($_GET['action']) ) $action = $_GET['action'];  

				switch($action) 
				{
					case 'delete':
						$id = $_GET['id'];
						MP_User::set_user_status( $id, 'delete' );
					break;
					case 'activate':
						$id = $_GET['id'];
						MP_User::set_user_status( $id, 'active' );
					break;
					case 'deactivate':
						$id = $_GET['id'];
						MP_User::set_user_status( $id, 'waiting' );
					break;
				} 
				wp_redirect($list_url);
			break;
		}
	}

// for appropriate title

	function title() {

		$page = MP_Admin::get_page();
		if (!$page) return;
		$page .= (isset($_GET['id']))   ? 'id' 		: '';

		$x = array(
				MP_FOLDER . '/mp-admin/mail-new.phpid'		=> __('MailPress Edit','MailPress'),
				MP_FOLDER . '/mp-admin/mails.phpmail'		=> __('MailPress View Draft','MailPress'),
				MP_FOLDER . '/mp-admin/mails.phpmailid'		=> __('MailPress View','MailPress')
			);

		if (!isset($x[$page])) return;
		
		global $title;
		$title = $x[$page];
	}

// for javascript

	function register_scripts() {

		wp_register_script( 'mp-mail-new', '/' . MP_PATH . 'mp-admin/js/mail-new.js');
		wp_localize_script( 'mp-mail-new', 'mailnewL10n', array( 'errmess' => __('Enter a valid email !','MailPress') ) );

		wp_register_script( 'mp-mails', '/' . MP_PATH . 'mp-admin/js/mails.js');
		wp_localize_script( 'mp-mails', 'adminmailsL10n', array('pending' => __('%i% pending') ) );

		wp_register_script( 'mp-mail', '/' . MP_PATH . 'mp-admin/js/mail.js');

		wp_register_script( 'mp-settings', '/' . MP_PATH . 'mp-admin/js/settings.js');

		wp_register_script( 'mp-users', '/' . MP_PATH . 'mp-admin/js/users.js');
		wp_localize_script( 'mp-users', 'adminusersL10n', array('pending' => __('%i% pending') ) );

		wp_register_script( 'mp-postbox', '/wp-admin/js/postbox.js', array('jquery'));
		wp_localize_script( 'mp-postbox', 'postboxL10n', array( 'requestFile' => get_option('siteurl') . '/' . MP_PATH . 'mp-includes/action.php' ) );
		wp_register_script( 'mp-ajax-response', '/wp-includes/js/wp-ajax-response.js');
		wp_localize_script( 'mp-ajax-response', 'wpAjax', array( 'noPerm' => __('Email was not sent AND/OR Update database failed','MailPress'), 'broken' => __('An unidentified error has occurred.') ) );
		wp_register_script( 'mp-lists', '/' . MP_PATH . 'mp-includes/js/mp-lists.js');
		wp_localize_script( 'mp-lists', 'wpListL10n', array( 'url' => get_option( 'siteurl' ) . '/' . MP_PATH . 'mp-includes/action.php' ) );
	}

// for appropriate javascript

	function enqueue_scripts() {
		global $mp_general;

		$page = MP_Admin::get_page();
		if (!$page) return;

		$x = array	(
				MP_FOLDER . '/mp-admin/mail-new.php'		=> array	(	'jquery-ui-tabs',
															'mp-postbox',
															'mp-mail-new'
														),
				MP_FOLDER . '/mp-admin/mails.php'			=> array	(	'admin-forms',
															'mp-mails',
															'mp-ajax-response',
															'mp-lists'
														),
				MP_FOLDER . '/mp-admin/mails.phpmail'		=> array	(	'jquery-ui-tabs',
															'mp-mail'
														),
				MP_FOLDER . '/mp-admin/settings.php'		=> array	(	'jquery-ui-tabs',
															'mp-settings'
														),
				MP_FOLDER . '/mp-admin/users.php' 			=> array	(	'admin-forms',
															'mp-users',
															'mp-ajax-response',
															'mp-lists'
														)
				);

		if (!isset($x[$page])) return;
		foreach ($x[$page] as $src )	wp_enqueue_script($src);
	}

// for appropriate css

	function enqueue_css() {

		$page = MP_Admin::get_page();
		if (!$page) return;

		$x = array	(
				MP_FOLDER . '/mp-admin/dashboard.php'		=> array	(	'css/dashboard.css',
															get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/dashboard.css'
														),
				MP_FOLDER . '/mp-admin/mail-new.php'		=> array	(	get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/mail-new.css'
														),
				MP_FOLDER . '/mp-admin/mails.php'			=> array	(	get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/admin' . get_user_option('admin_color') . '.css',
															get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/mails.css'
														),
				MP_FOLDER . '/mp-admin/mails.phpmail'		=> array	(	get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/mail.css'
														),
				MP_FOLDER . '/mp-admin/themes.php'			=> array	(	get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/themes.css'
														),
				MP_FOLDER . '/mp-admin/settings.php'		=> array	(	get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/admin' . get_user_option('admin_color') . '.css',
															get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/settings.css'
														),
				MP_FOLDER . '/mp-admin/users.php' 			=> array	(	get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/admin' . get_user_option('admin_color') . '.css',
															get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/users.css'
														)
				);

		if (!isset($x[$page])) return;
		foreach ($x[$page] as $src)	echo "<link rel='stylesheet' type='text/css' href='$src' title='MailPress' media='all' />\n";
	}

////	menus	////

	function menu() {
		global $mp_general;

		if (isset($_POST['general']['menu']) || isset($mp_general['menu']))
		{
			add_menu_page('MailPress', 'MailPress', 8, MP_FOLDER . '/mp-admin/dashboard.php');
			add_submenu_page(MP_FOLDER . '/mp-admin/dashboard.php', __('MailPress Dashboard','MailPress'),	__('Dashboard'), 		8, MP_FOLDER . '/mp-admin/dashboard.php');
			add_submenu_page(MP_FOLDER . '/mp-admin/dashboard.php', __('MailPress Create New Mail','MailPress'),	__('Write'), 	8, MP_FOLDER . '/mp-admin/mail-new.php');
			add_submenu_page(MP_FOLDER . '/mp-admin/dashboard.php', __('MailPress Mails','MailPress'),	__('Manage'), 		8, MP_FOLDER . '/mp-admin/mails.php');
			add_submenu_page(MP_FOLDER . '/mp-admin/dashboard.php', __('MailPress Themes','MailPress'),	__('Design'), 		8, MP_FOLDER . '/mp-admin/themes.php');
			add_submenu_page(MP_FOLDER . '/mp-admin/dashboard.php', __('MailPress Settings','MailPress'),	__('Settings'), 		8, MP_FOLDER . '/mp-admin/settings.php');
			add_submenu_page(MP_FOLDER . '/mp-admin/dashboard.php', __('MailPress Users','MailPress'),	__('Users'), 		8, MP_FOLDER . '/mp-admin/users.php');
		}
		else					
		{
			add_submenu_page('index.php' , 	__('MailPress Dashboard','MailPress'), 		'MailPress', 				8, MP_FOLDER . '/mp-admin/dashboard.php');
			add_submenu_page('post-new.php' , 	__('MailPress Create New Mail','MailPress'), 	'MailPress', 				8, MP_FOLDER . '/mp-admin/mail-new.php');
			add_submenu_page('edit.php',		__('MailPress Mails','MailPress'), 			'MailPress', 				8, MP_FOLDER . '/mp-admin/mails.php');
			add_submenu_page('themes.php', 	__('MailPress Themes','MailPress'), 		'MailPress', 				8, MP_FOLDER . '/mp-admin/themes.php');
			add_options_page(				__('MailPress Settings','MailPress'),		'MailPress', 				8, MP_FOLDER . '/mp-admin/settings.php');
			add_submenu_page('users.php',		__('MailPress Users','MailPress'), 			'MailPress', 				8, MP_FOLDER . '/mp-admin/users.php');
		}
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
	 	else 		echo "<div id='$id' class='error   fade'><p>$s</p></div>";
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

	function select_number($start,$max,$selected)
	{
		while ($start <= $max)
		{
			echo("\t\t\t\t\t\t\t\t\t\t\t\t"); 
?>
<option <?php selected($start,$selected); ?> value='<?php echo $start; ?>'><?php echo $start; ?></option>
<?php
		$start++;
		}
	}

// for parms & urls

	function get_url_parms($parms = array('mode','status','s','apage','author'))
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

	function post_url_parms($url_parms,$parms = array('mode','status','s','apage','author'))
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