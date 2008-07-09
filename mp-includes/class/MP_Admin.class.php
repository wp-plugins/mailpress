<?php
include (MP_TMP . '/mp-admin/includes/dashboard.php');

add_action('admin_init',	'mp_sidebar_admin_setup');
add_action('admin_init', 	array('MP_Admin','title'));
add_action('admin_init', 	array('MP_Admin','register_scripts'));

add_action('admin_init', 	array('MP_Admin','enqueue_scripts'));
add_action('admin_head', 	array('MP_Admin','enqueue_css'));

add_action('admin_menu', 	array('MP_Admin','menu'),  8, 1);



class MP_Admin
{

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

// for appropriate title

	function title() {
		if (!isset($_GET['page'])) return;
		else $page = $_GET['page'];

		$x = array(
				MP_FOLDER . '/mp-admin/mail-new.phpid'		=> __('MailPress Edit','MailPress'),
				MP_FOLDER . '/mp-admin/mails.phpmail'		=> __('MailPress View Draft','MailPress'),
				MP_FOLDER . '/mp-admin/mails.phpmailid'		=> __('MailPress View','MailPress')
			);

		$page .= (isset($_GET['file'])) ? $_GET['file'] : '';
		$page .= (isset($_GET['id']))   ? 'id' 		: '';

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

		if (!isset($_GET['page'])) return;
		else $page = $_GET['page'];

		$f = '';
		if (isset($_GET['file'])) 
		{
			$f	= '&file=' . $_GET['file']; 
			$page .= $_GET['file'];
		}

		$x = array(
				MP_FOLDER . '/mp-admin/mail-new.php'		=> array(			'jquery-ui-tabs' 	=> 'e',
																'mp-postbox' 	=> 'e',
																'mp-mail-new' 	=> 'e'
																),
				MP_FOLDER . '/mp-admin/mails.php'			=> array(			'admin-forms' 	=> 'e',
																'mp-mails' 		=> 'e',
																'mp-ajax-response' => 'e',
																'mp-lists' 		=> 'e'
																),
				MP_FOLDER . '/mp-admin/mails.phpmail'		=> array(			'jquery-ui-tabs' 	=> 'e',
																'mp-mail' 		=> 'e'
																),
				MP_FOLDER . '/mp-admin/settings.php'		=> array(			'jquery-ui-tabs' 	=> 'e',
																'mp-settings' 	=> 'e'
																),
				MP_FOLDER . '/mp-admin/users.php' 			=> array(			'admin-forms' 	=> 'e',
																'mp-users' 		=> 'e',
																'mp-ajax-response' => 'e',
																'mp-lists' 		=> 'e'
																)
			);

		if (!isset($x[$page])) return;
		foreach ($x[$page] as $src => $type)
		{
			switch ($type)
			{
				case 'e' :
					wp_enqueue_script($src);
				break;
			}
		}
	}

// for appropriate css

	function enqueue_css() {

		if (!isset($_GET['page'])) return;
		else $page = $_GET['page'];

		$f = '';
		if (isset($_GET['file'])) 
		{
			$f	= '&file=' . $_GET['file']; 
			$page .= $_GET['file'];
		}

		$x = array(
				MP_FOLDER . '/mp-admin/dashboard.php'		=> array(			'css/dashboard.css' => 'c',
																get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/dashboard.css' 	=> 'c'
																),
				MP_FOLDER . '/mp-admin/mail-new.php'		=> array(			get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/mail-new.css' 	=> 'c'
																),
				MP_FOLDER . '/mp-admin/mails.php'			=> array(			get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/admin' . get_user_option('admin_color') . '.css'	=> 'c',
																get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/mails.css' 	=> 'c'
																),
				MP_FOLDER . '/mp-admin/mails.phpmail'		=> array(			get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/mail.css' 	=> 'c'
																),
				MP_FOLDER . '/mp-admin/themes.php'			=> array(			get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/themes.css' 	=> 'c'
																),
				MP_FOLDER . '/mp-admin/settings.php'		=> array(			get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/admin' . get_user_option('admin_color') . '.css'	=> 'c',
																get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/settings.css' 	=> 'c'
																),
				MP_FOLDER . '/mp-admin/users.php' 			=> array(			get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/admin' . get_user_option('admin_color') . '.css'	=> 'c',
																get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/users.css' 	=> 'c'
																)
			);

		if (!isset($x[$page])) return;
		foreach ($x[$page] as $src => $type)
		{
			switch ($type)
			{
				case 'c' :
					echo "<link rel='stylesheet' type='text/css' href='$src' title='MailPress' media='all' />\n";
				break;
			}
		}
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