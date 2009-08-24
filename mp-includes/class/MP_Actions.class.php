<?php
class MP_Actions
{
	function __construct()
	{
		switch (true)
		{
			case ( ( isset($_GET['tg']) ) && !( isset($_POST['action']) || isset($_GET['action']) ) ) :
				$action = 'tracking';
			break;
			case ( isset($_POST['action']) ) :
				$action = $_POST['action'];
			break;
			case ( isset($_GET['action']) ) :
				$action = $_GET['action'];
			break;
			default :
				MailPress::mp_die(-1);
			break;
		}
		$action = str_replace('-', '_', $action);

		if ( method_exists($this, $action) ) call_user_func_array( array($this, $action), array() );

		do_action('mp_action_' . $action );
	}


////  SUBSCRIPTION FORM  ////

	public static function add_user_fo() {

		list($message, $email, $name) = MP_Widget::insert();
		$xml = "<message><![CDATA[$message]]></message><id><![CDATA[" . $_POST['id'] . "]]></id><email><![CDATA[$email]]></email><name><![CDATA[$name]]></name>";

		ob_end_clean();
		header('Content-Type: text/xml');
		MailPress::mp_die("<?xml version='1.0' standalone='yes'?><wp_ajax>$xml</wp_ajax>");
	}

////  MAIL LINKS  ////

	public static function tracking()
	{
		MailPress::require_class('Mailmeta');
		$meta = MP_Mailmeta::get_by_id($_GET['mm']);
		if ($meta)
		{
			do_action('mp_action_tracking', $meta); // will activate if any !
			switch ($_GET['tg'])
			{
				case ('l') :
					switch ($meta->meta_value)
					{
						case '{{subscribe}}' :
							MailPress::require_class('Users');
							$url = MP_Users::get_subscribe_url($_GET['us']);
						break;
						case '{{unsubscribe}}' :
							MailPress::require_class('Users');
							$url = MP_Users::get_unsubscribe_url($_GET['us']);
						break;
						case '{{viewhtml}}' :
							MailPress::require_class('Users');
							$url = MP_Users::get_view_url($_GET['us'], $meta->mail_id);
						break;
						default :
							$url = $meta->meta_value;
						break;
					}
					MailPress::mp_redirect($url);
				break;
				case ('o') :
					self::download('_.gif', MP_TMP . 'mp-includes/images/_.gif', 'image/gif', 'gif_' . $_GET['us'] . '_' . $_GET['mm'] . '.gif');
				break;
			}
		}
		MailPress::mp_redirect(get_option('home'));
	}

	public static function mail_link() 
	{
		include (MP_TMP . 'mp-includes/html/mail_link.php');
	}

////  DELETE  ////

	public static function delete_mail() 
	{
		$id = isset($_POST['id'])? (int) $_POST['id'] : 0;
		MailPress::require_class('Mails');
		MailPress::mp_die( MP_Mails::delete( $id ) ? 1 : 0 );
	}

	public static function delete_user() 
	{
		$id = isset($_POST['id'])? (int) $_POST['id'] : 0;
		MailPress::require_class('Users');
		MailPress::mp_die( MP_Users::set_status( $id, 'delete' ) ? 1 : 0 );
	}

	public static function delete_mailmeta()
	{
		if ( !current_user_can( 'MailPress_mail_custom_fields') )	MailPress::mp_die(-1);

		$id = isset($_POST['id'])? (int) $_POST['id'] : 0;
		check_ajax_referer( "delete-mailmeta_$id" );

		MailPress::require_class('Mailmeta');
		MailPress::mp_die( MP_Mailmeta::delete_by_id( $id ) ? 1 : 0 );
	}

	public static function delete_usermeta()
	{
		if ( !current_user_can( 'MailPress_user_custom_fields') )	MailPress::mp_die(-1);

		$id = isset($_POST['id'])? (int) $_POST['id'] : 0;
		check_ajax_referer( "delete-usermeta_$id" );

		MailPress::require_class('Usermeta');
		MailPress::mp_die( MP_Usermeta::delete_by_id( $id ) ? 1 : 0 );
	}

	public static function delete_attachement()
	{
		if (!isset($_POST['mmeta_id'])) return;
		if (!is_numeric($_POST['mmeta_id'])) return;

		MailPress::require_class('Mailmeta');

		$mid = (int) $_POST['mmeta_id'];
		MP_Mailmeta::delete_by_id( $mid );
		MailPress::mp_die(1);
	}

//// DIM LIST ////

	public static function dim_mail() 
	{
		MailPress::require_class('Mails');
		require_once(MP_TMP . 'mp-admin/mails.php');

		$id 		= isset($_POST['id']) 	? (int) $_POST['id'] : 0;
		$x 		= MP_Mails::send_draft($id, true);
        
		$url_parms 	= MP_AdminPage::get_url_parms();

		switch (true)
		{
			case (is_array($x)) :
				ob_start();
					MP_AdminPage::get_row( $x[0], $url_parms );
					$html = ob_get_contents();
				ob_end_clean();

				$xml = "<rc><![CDATA[2]]></rc><id><![CDATA[" . $x[0] . "]]></id><item><![CDATA[$html]]></item>";
			break;
			case (is_numeric($x)) :
				if (0 == $x) MailPress::mp_die(-1);

				ob_start();
					MP_AdminPage::get_row( $id, $url_parms );
					$html = ob_get_contents();
				ob_end_clean();
				header('Content-Type: text/xml');
				$xml = "<rc><![CDATA[1]]></rc><id><![CDATA[$id]]></id><item><![CDATA[$html]]></item>";
			break;
			default :
				ob_start();
					MP_AdminPage::get_row( $id, $url_parms, __('no recipient', 'MailPress') );
					$html = ob_get_contents();
				ob_end_clean();
				header('Content-Type: text/xml');
				$xml = "<rc><![CDATA[0]]></rc><id><![CDATA[$id]]></id><item><![CDATA[$html]]></item>";
			break;
		}

		ob_end_clean();
		header('Content-Type: text/xml');
		MailPress::mp_die("<?xml version='1.0' standalone='yes'?><mp_action>$xml</mp_action>");
	}

	public static function dim_user() 
	{
		MailPress::require_class('Users');

		$id = isset($_POST['id'])? (int) $_POST['id'] : 0;
		switch (MP_Users::get_status($id))
		{
			case 'waiting' : 
				$now = MP_Users::set_status( $id, 'active' );
				if ($now) self::dim_user2($id, $now);
			break;
			case 'active' : 
				$now = MP_Users::set_status( $id, 'waiting' );
				if ($now) self::dim_user2($id, $now);
			break;
			default :
				MailPress::mp_die();
			break;
		}
		MailPress::mp_die(-1);
	}

	public static function dim_user2($id, $now) 
	{
		$t_time = mysql2date(__('Y/m/d g:i:s A'), $now);
		$m_time = $now;
		$time   = mysql2date(__('U'), $now);

		$time_diff = time() - $time; 

		if ( $time_diff > 0 && $time_diff < 24*60*60 )	$h_time = sprintf( __('%s ago'), human_time_diff( $time ) );
		elseif ( $time_diff == 0 )				$h_time = __('now', 'MailPress');
		else								$h_time = mysql2date(__('Y/m/d'), $m_time);

		$xml = "<id><![CDATA[$id]]></id><now><![CDATA[$t_time]]></now><time><![CDATA[$h_time]]></time>";

		ob_end_clean();
		header('Content-Type: text/xml');
		MailPress::mp_die("<?xml version='1.0' standalone='yes'?><wp_ajax>$xml</wp_ajax>");
	}

//// ADD LIST ////

	public static function add_mail() 
	{
		MailPress::require_class('Mails');
		require_once(MP_TMP . 'mp-admin/mails.php');

		$url_parms = MP_AdminPage::get_url_parms(array('mode', 'status', 's'));

		$start = (isset($_POST['apage'])) ? intval($_POST['apage']) * 25 - 1 : 24;

		list($mails, $total) = MP_AdminPage::get_list( $start, 1, $url_parms );

		if ( !$mails ) MailPress::mp_die(1);

		$x = new WP_Ajax_Response();
		foreach ( (array) $mails as $mail ) 
		{
			MP_Mails::get( $mail );
			ob_start();
				MP_AdminPage::get_row( $mail->id, $url_parms );
				$html = ob_get_contents();
			ob_end_clean();
			$x->add( array(
				'what' 	=> 'mail', 
				'id' 		=> $mail->id, 
				'data' 	=> $html
			) );
		}
		$x->send();
	}

	public static function add_user() 
	{
		MailPress::require_class('Users');
		require_once(MP_TMP . 'mp-admin/users.php');

		$url_parms = MP_AdminPage::get_url_parms(array('mode', 'status', 's'));

		$start = isset($_POST['apage']) ? intval($_POST['apage']) * 25 - 1: 24;

		list($users, $total) = MP_AdminPage::get_list( $start, 1, $url_parms );

		if ( !$users ) MailPress::mp_die(1);

		$x = new WP_Ajax_Response();
		foreach ( (array) $users as $user ) {
			MP_Users::get( $user );
			ob_start();
				MP_AdminPage::get_row( $user->id, $url_parms, false );
				$html = ob_get_contents();
			ob_end_clean();
			$x->add( array(
				'what' 	=> 'user', 
				'id' 		=> $user->id, 
				'data' 	=> $html
			) );
		}
		$x->send();
	}

//// CUSTOM FIELDS ////

	public static function add_mailmeta()
	{
		if ( !current_user_can( 'MailPress_mail_custom_fields') )	MailPress::mp_die(-1);

		check_ajax_referer( 'add-mailmeta' );

		$c = 0;
		$uid = (int) $_POST['mail_id'];
		if ($uid === 0) MailPress::mp_die();

		MailPress::require_class('Mailmeta');
		if ( isset($_POST['metakeyselect']) || isset($_POST['metakeyinput']) ) 
		{
			if (isset($_POST['metakeyselect']) && ('#NONE#' == $_POST['metakeyselect']) && empty($_POST['metakeyinput']) )	MailPress::mp_die(1);
			if ( !$mid = MP_Mailmeta::add_meta( $uid ) ) 												MailPress::mp_die();

			$meta = MP_Mailmeta::get_by_id( $mid );
			$uid = (int) $meta->mail_id;
			$meta = get_object_vars( $meta );
			require_once(MP_TMP . 'mp-admin/write.php');
			$x = new WP_Ajax_Response( array(
				'what' => 'mailmeta', 
				'id' => $mid, 
				'data' => MP_AdminPage::meta_box_customfield_row( $meta, $c ), 
				'position' => 1, 
				'supplemental' => array('mail_id' => $uid)
			) );
		}
		else
		{
			$mid   = (int) array_pop(array_keys($_POST['mailmeta']));
			$key   = $_POST['mailmeta'][$mid]['key'];
			$value = $_POST['mailmeta'][$mid]['value'];

			if ( !$meta = MP_Mailmeta::get_by_id( $mid ) )			MailPress::mp_die();
			if ( !MP_Mailmeta::update_by_id($mid , $key, $value) )	MailPress::mp_die(1);
			$meta = MP_Mailmeta::get_by_id( $mid );
			require_once(MP_TMP . 'mp-admin/write.php');
			$x = new WP_Ajax_Response( array(
				'what' 	=> 'mailmeta', 
				'id' 		=> $mid, 
				'old_id' 	=> $mid, 
				'data' 	=> MP_AdminPage::meta_box_customfield_row( array( 'meta_key' => $meta->meta_key, 'meta_value' => $meta->meta_value, 'mmeta_id' => $mid ), $c ), 
				'position' 	=> 0, 
				'supplemental' => array('mail_id' => $meta->mail_id)
			) );
		}
		$x->send();
	}

	public static function add_usermeta()
	{
		if ( !current_user_can( 'MailPress_user_custom_fields') )	MailPress::mp_die(-1);

		check_ajax_referer( 'add-usermeta' );

		$c = 0;
		$uid = (int) $_POST['mp_user_id'];
		if ($uid === 0) MailPress::mp_die();

		MailPress::require_class('Usermeta');
		if ( isset($_POST['metakeyselect']) || isset($_POST['metakeyinput']) ) 
		{
			if (isset($_POST['metakeyselect']) && ('#NONE#' == $_POST['metakeyselect']) && empty($_POST['metakeyinput']) )	MailPress::mp_die(1);
			if ( !$mid = MP_Usermeta::add_meta( $uid ) ) 												MailPress::mp_die();

			$meta = MP_Usermeta::get_by_id( $mid );
			$uid = (int) $meta->user_id;
			$meta = get_object_vars( $meta );
			require_once(MP_TMP . 'mp-admin/user.php');
			$x = new WP_Ajax_Response( array(
				'what' => 'usermeta', 
				'id' => $mid, 
				'data' => MP_AdminPage::meta_box_customfield_row( $meta, $c ), 
				'position' => 1, 
				'supplemental' => array('mp_user_id' => $uid)
			) );
		}
		else
		{
			$mid   = (int) array_pop(array_keys($_POST['usermeta']));
			$key   = $_POST['usermeta'][$mid]['key'];
			$value = $_POST['usermeta'][$mid]['value'];

			if ( !$meta = MP_Usermeta::get_by_id( $mid ) )			MailPress::mp_die();
			if ( !MP_Usermeta::update_by_id($mid , $key, $value) )	MailPress::mp_die(1); // We know meta exists; we also know it's unchanged (or DB error, in which case there are bigger problems).
			$meta = MP_Usermeta::get_by_id( $mid );
			require_once(MP_TMP . 'mp-admin/user.php');
			$x = new WP_Ajax_Response( array(
				'what' 	=> 'usermeta', 
				'id' 		=> $mid, 
				'old_id' 	=> $mid, 
				'data' 	=> MP_AdminPage::meta_box_customfield_row( array( 'meta_key' => $meta->meta_key, 'meta_value' => $meta->meta_value, 'umeta_id' => $mid ), $c ), 
				'position' => 0, 
				'supplemental' => array('mp_user_id' => $meta->user_id)
			) );
		}
		$x->send();
	}

////  VIEW MAIL/THEME in thickbox  ////

	public static function get_previewlink()
	{
		$args		= array();
		$args['action'] 	= 'iview';
		$args['id']		= (isset($_POST['id'])) ? intval($_POST['id']) : 0;
		$args['main_id']	= (isset($_POST['main_id'])) ? intval($_POST['main_id']) : 0;
		$args['KeepThis'] = 'true';
		$args['TB_iframe'] = 'true';

		$url = clean_url(add_query_arg( $args, MP_Action_url ));
		MailPress::mp_die($url);
	}

	public static function iview()
	{
		MailPress::require_class('Mailmeta');
		MailPress::require_class('Mails');

		$mail 	= MP_Mails::get($_GET['id']);
		$mp_general = get_option('MailPress_general');
	// from
		$from 	= ('sent' == $mail->status) ? MP_Mails::display_toemail($mail->fromemail, stripslashes($mail->fromname)) : MP_Mails::display_toemail($mp_general['fromemail'], stripslashes($mp_general['fromname']));
	// to
		$to 		= MP_Mails::display_toemail($mail->toemail, stripslashes($mail->toname));
	// subject
		if ('sent' == $mail->status)
		{
			$subject 	= $mail->subject;
		}
		else
		{
			MailPress::require_class('Mail');
			$x = new MP_Mail();
			$subject 	= $x->do_eval($mail->subject);
			
		}
	// content
		$args			= array();
		$args['action'] 	= 'viewadmin';
		$args['id'] 	= $_GET['id'];
		$args['main_id'] 	= (isset($_GET['main_id'])) ? $_GET['main_id'] : $args['id'];
		if ((isset($_GET['theme'])) && (!empty($_GET['theme']))) $args['theme'] = $_GET['theme'];

		$args['type'] 	= 'html';
		$html 		= "<iframe id='ihtml'		style='width:100%;border:0;height:550px' src='" . clean_url(add_query_arg( $args, MP_Action_url )) . "'></iframe>";

		$args['type'] 	= 'plaintext';
		$plaintext 		= "<iframe id='iplaintext' 	style='width:100%;border:0;height:550px' src='" . clean_url(add_query_arg( $args, MP_Action_url )) . "'></iframe>";
	// attachements
		$attachements = '';
		$metas = MP_Mailmeta::has( $args['main_id'], '_MailPress_attached_file');
		if ($metas) foreach($metas as $meta) $attachements .= "<tr><td>&nbsp;" . MP_Mails::get_attachement_link($meta, $mail->status) . "</td></tr>";
		$view = true;
		include(MP_TMP . '/mp-includes/html/mail.php');
	}

	public static function viewadmin() 
	{
		MailPress::require_class('Mail');

		$id 		= $_GET['id'];
		$main_id 	= $_GET['main_id'];
		$type		= (isset($_GET['type']))  ? $_GET['type']  : 'html';
		$theme	= (isset($_GET['theme'])) ? $_GET['theme'] : false;

		$x = new MP_Mail();
		if 	 ('html' == $type) 	$x->viewhtml($id, $main_id, $theme);
		elseif ('plaintext' == $type) $x->viewplaintext($id, $main_id, $theme);
	}

	public static function view() 
	{
		MailPress::require_class('Users');
		MailPress::require_class('Mails');
		MailPress::require_class('Mail');
		$id 		= $_GET['id'];
		$key		= $_GET['key'];
		$email 	= MP_Users::get_email(MP_Users::get_id($key));
		$mail 	= MP_Mails::get($id);

		$x = new MP_Mail();

		if (MailPress::is_email($mail->toemail))
		{
			echo $x->process_img($mail->html, $mail->themedir, 'draft');
		}
		else
		{
			MailPress::require_class('Mailmeta');
			$m = MP_Mailmeta::get($id, '_MailPress_replacements');
			if (!is_array($m)) $m = array();

			$recipients = unserialize($mail->toemail);
			$replacements = array_merge($recipients[$email], $m);
			foreach($replacements as $k => $v) 
				$mail->html	= str_replace($k, $v, $mail->html, $ch);
			echo $x->process_img($mail->html, $mail->themedir, 'draft');
		}
	}

////  THEMES  ////

	public static function theme_preview() 
	{
		$args			= array( 'action'	=> 'previewtheme', 'template' => $_GET['template'], 'stylesheet'=> $_GET['stylesheet'] );

		$args['type'] 	= 'html';
		$html 		= "<iframe id='ihtml'		style='width:100%;border:0;height:550px' src='" . clean_url(add_query_arg( $args, MP_Action_url )) . "'></iframe>";

		$args['type'] 	= 'plaintext';
		$plaintext 		= "<iframe id='iplaintext' 	style='width:100%;border:0;height:550px' src='" . clean_url(add_query_arg( $args, MP_Action_url )) . "'></iframe>";

		unset($view);
		include (MP_TMP . '/mp-includes/html/mail.php');
	}

	public static function previewtheme() 
	{
		$url 			= get_option('home');

		$mail			= new stdClass();
		$mail->Theme 	= $_GET['template'];
		$mail->Template 	= 'confirmed';

		$message  = sprintf(__('We confirm your subscription to %1$s emails', 'MailPress'), get_bloginfo('name') );
		$message .= "\n\n";
		$message .= __('Congratulations !', 'MailPress');
		$message .= "\n\n";
		$mail->plaintext 	= $message;

		$message  = sprintf(__('We confirm your subscription to %1$s emails', 'MailPress'), "<a href='$url'>" . get_bloginfo('name') . "</a>" );
		$message .= '<br /><br />';
		$message .= __('Congratulations !', 'MailPress');
		$message .= '<br /><br />';
		$mail->html 	= $message;

		$mail->unsubscribe= __('"Subscription management link"', 'MailPress');
		$mail->viewhtml 	= __('"Trouble reading link"', 'MailPress');

		MailPress::require_class('Mail');
		$x = new MP_Mail();
		$x->args = new stdClass();
		$x->args = $mail;

		if ('html' == $_GET['type'])
		{
			$x->args->html = stripslashes(apply_filters('the_content', $x->args->html));
			$x->html 	   = $x->build_mail_content('html');
			echo $x->process_img($x->html, $x->mail->themedir, 'draft');
		}
		else
		{
			$x->args->plaintext 	= stripslashes($x->args->plaintext);
			$x->plaintext 		= strip_tags($x->build_mail_content('plaintext'));
			include MP_TMP . '/mp-includes/html/plaintext.php';
		}
	}


////  WRITE  ////

	public static function autosave()
	{
		global $current_user;

		$id = 0;
		$mail 	= new stdClass();
		$do_lock 	= true;
		$supplemental = array();
		$supplemental['tipe'] = $data = '';

		$message['revision'] = sprintf( __('Revision saved at %s.', 'MailPress'), 	date( __('g:i:s a'), current_time( 'timestamp', true ) ) );
		$message['draft']    = sprintf( __('Draft saved at %s.', 'MailPress'), 		date( __('g:i:s a'), current_time( 'timestamp', true ) ) );

		$do_autosave= (bool) $_POST['autosave'];
		$main_id 	= $_POST['id'];

		MailPress::require_class('Mails');

		$id 	= $mail->id = (int) $_POST['id'];

		if ( -1 == $_POST['revision'])
		{
			if ( $do_autosave ) 
			{
				$id = (0 == $_POST['id']) ? MP_Mails::get_id(__CLASS__ . ' 1 ' . __METHOD__) : $_POST['id'];
				$mail->id = $id;

				MP_Mails::update_draft($id);
				$data = $message['draft'];
				$supplemental['tipe'] = 'mail';
			}
		}
		else
		{
			$mail = MP_Mails::get($id);

			if ( $last = MP_Mails::check_mail_lock( $mail->id ) ) 
			{
				$do_autosave 	= $do_lock = false;
				$last_user 		= get_userdata( $last );
				$last_user_name 	= ($last_user) ? $last_user->display_name : __( 'Someone' );	
				$data 		= new WP_Error( 'locked', sprintf( __( 'Autosave disabled: %s is currently editing this mail.' ) , wp_specialchars( $last_user_name )	) );
				$supplemental['disable_autosave'] = 'disable';
			}
			if ( $do_autosave ) 
			{
				$id = (0 == $_POST['revision']) ? MP_Mails::get_id(__CLASS__ . ' 2 ' . __METHOD__) : $_POST['revision'];

				if (0 == $_POST['revision'])
				{
					MailPress::require_class('Mailmeta');
					$mailmetas 		= MP_Mailmeta::get( $mail->id , '_MailPress_mail_revisions');
					$mailmetas[$current_user->ID] = $id;
					MP_Mailmeta::update($mail->id , '_MailPress_mail_revisions', $mailmetas);
				}

				MP_Mails::update_draft($id, '');
				$data = $message['revision'];
				$supplemental['tipe'] = 'revision';
			}
			else
			{
				if (0 != $_POST['revision']) $id = $_POST['revision'];
			}
		}

		if ( $do_lock && $id ) MP_Mails::set_mail_lock( $mail->id );

		$x = new WP_Ajax_Response( array	(
								'what' => 'autosave', 
								'id' => $id, 
								'old_id' => $main_id, 
								'type' => false, 
								'data' => $id ? $data : '', 
								'supplemental' => $supplemental
								)
						 );
		$x->send();
	}

////  ATTACHEMENTS  UPLOAD  ////

	public static function upload_iframe_html()
	{
		$id 		= $_GET['id'];
		$draft_id 	= $_GET['draft_id'];
		$bytes 	= apply_filters('import_upload_size_limit', wp_max_upload_size() );
		//$size 	= wp_convert_bytes_to_hr( $bytes );

		wp_register_script( 'upload_iframe', '/' . MP_PATH . 'mp-includes/js/fileupload/upload_iframe.js', array('jquery'), false, 1);
		wp_localize_script( 'upload_iframe', 'uploadhtmlL10n', array( 
			'id' => $id
		) );
		wp_enqueue_script('upload_iframe');

		include MP_TMP . '/mp-includes/html/upload_iframe.php';
	}

	public static function swfu_mail_attachement() 
	{
		// Flash often fails to send cookies with the POST or upload, so we need to pass it in GET or POST instead
		if ( is_ssl() && empty($_COOKIE[SECURE_AUTH_COOKIE]) && !empty($_REQUEST['auth_cookie']) )
			$_COOKIE[SECURE_AUTH_COOKIE] = $_REQUEST['auth_cookie'];
		elseif ( empty($_COOKIE[AUTH_COOKIE]) && !empty($_REQUEST['auth_cookie']) )
			$_COOKIE[AUTH_COOKIE] = $_REQUEST['auth_cookie'];

		$xml = self::mail_attachement();

		ob_end_clean();
		header('Content-Type: text/xml');
		echo $xml;
		die();
	}

	public static function html_mail_attachement() 
	{
		$draft_id 	= $_REQUEST['draft_id'];
		$id		= $_REQUEST['id'];
		$file		= $_REQUEST['file'];

		$xml = self::mail_attachement();

		$xml = str_replace('>', '&gt;', $xml);
		$xml = str_replace('<', '&lt;', $xml);

		wp_register_script( 'upload_iframe_xml', '/' . MP_PATH . 'mp-includes/js/fileupload/upload_iframe_xml.js', array('jquery'), false, 1);
		wp_localize_script( 'upload_iframe_xml', 'uploadxmlL10n', array(
			'id'		=> $id,
			'draft_id' 	=> $draft_id,
			'file' 	=> $file
		) );
		wp_enqueue_script('upload_iframe_xml');

		ob_end_clean();
		ob_start();
			include MP_TMP . '/mp-includes/html/upload_iframe_xml.php';
			$html = ob_get_contents();
		ob_end_clean();

		MailPress::mp_die($html);
	}

	public static function mail_attachement()
	{
		$data = self::handle_upload('async-upload', $_REQUEST['draft_id']);

		if (is_wp_error($data)) 
		{
			$xml  = "<error><![CDATA[" . $data->get_error_message() . "]]></error>";
		}
		else
		{
			$xml  = "<id><![CDATA[" . $data['id'] . "]]></id>";
			$xml .= "<url><![CDATA[" . $data['url'] . "]]></url>";
			$xml .= "<file><![CDATA[" . $data['file'] . "]]></file>";
		}

		return "<?xml version='1.0' standalone='yes'?><mp_fileupload>$xml</mp_fileupload>";
	}

	public static function handle_upload($file_id, $draft_id) 
	{
		$overrides = array('test_form'=>false, 'unique_filename_callback' => 'mp_unique_filename_callback');
		$time = current_time('mysql');

		$uploaded_file = wp_handle_upload($_FILES[$file_id], $overrides, $time);

		if ( isset($uploaded_file['error']) )
			return new WP_Error( 'upload_error', $uploaded_file['error'] );

// Check file path is ok
		$uploads = wp_upload_dir();
		if ( $uploads && (false === $uploads['error']) ) 							// Get upload directory
		{ 	
			if ( 0 === strpos($uploaded_file['file'], $uploads['basedir']) ) 				// Check that the upload base exists in the file path
			{
				$file = str_replace($uploads['basedir'], '', $uploaded_file['file']); 		// Remove upload dir from the file path
				$file = ltrim($file, '/');
			}
		}

// Construct the attachment array
		$object = array(
					'name' 	=> $_FILES['async-upload']['name'], 
					'mime_type'	=> $uploaded_file['type'], 
					'file'	=> $file, 
					'file_fullpath'	=> str_replace("\\", "/", $uploaded_file['file']), 
					'guid' 	=> $uploaded_file['url']
				);
// Save the data
		MailPress::require_class('Mailmeta');
		$id = MP_Mailmeta::add( $draft_id, '_MailPress_attached_file', $object );

		$href = clean_url(add_query_arg( array('action' => 'attach_download', 'id' => $id), MP_Action_url ));
		return array('id' => $id, 'url' => $href, 'file' => $object['file_fullpath']);
	}


	public static function attach_download()
	{
		$mmeta_id 	= $_GET['id'];

		MailPress::require_class('Mailmeta');
		$meta = MP_Mailmeta::get_by_id($mmeta_id);
		$meta_value = unserialize( $meta->meta_value );

		if (!$meta_value)						MailPress::mp_die(__('Cannot Open Attachement 1!', 'MailPress'));
		if (!is_file($meta_value['file_fullpath']))	MailPress::mp_die(__('Cannot Open Attachement 2! ' . $meta_value['file_fullpath'], 'MailPress'));

		self::download($meta_value['name'], $meta_value['file_fullpath'], $meta_value['mime_type']);
	}


////  MISC  ////

	public static function download($file, $file_fullpath, $mime_type, $name = false)
	{
		if (!$name) $name = $file;
		if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) $file = preg_replace('/\./', '%2e', $file, substr_count($file, '.') - 1);

		if(!$fdl = @fopen($file_fullpath, 'r')) 	MailPress::mp_die(__('Cannot Open File !', 'MailPress'));

		header("Cache-Control: ");# leave blank to avoid IE errors
		header("Pragma: ");# leave blank to avoid IE errors
		header("Content-type: " . $mime_type);
		header("Content-Disposition: attachment; filename=\"".$file."\"");
		header("Content-length:".(string)(filesize($file_fullpath)));
		sleep(1);
		fpassthru($fdl);
		MailPress::mp_die();
	}


	public static function map_settings()
	{
		global $user_ID;
		update_usermeta( $user_ID, '_MailPress_' . $_POST['prefix'], $_POST['settings'] );
		MailPress::mp_die();
	}
}
$MP_Actions = new MP_Actions();
?>