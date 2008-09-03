<?php
add_action(	'mp_action_viewadmin',		array('MP_Mail','mp_action_viewadmin'));
add_action(	'mp_action_view',			array('MP_Mail','mp_action_viewuser'));

// for ajax admin

add_action(	'mp_action_dim-mail',		array('MP_Mail','mp_action_send_mail'));
add_action(	'mp_action_delete-mail',	array('MP_Mail','mp_action_delete_mail'));
add_action(	'mp_action_add-mail',		array('MP_Mail','mp_action_add_mail'));

// for ajax mail-new

add_action(	'mp_action_autosave',		array('MP_Mail','mp_action_autosave'));
add_action(	'mp_action_get-previewlink',	array('MP_Mail','mp_action_get_previewlink'));
add_action(	'mp_action_iview',		array('MP_Mail','mp_action_iview'));

class MP_Mail
{

	function MP_Mail( $plug = MP_FOLDER )
	{
		$this->plug = $plug;
		$this->theme = new MP_Themes();
	}


////	MP_Mail send functions	////

	function send($args)
	{
		$this->args = (is_numeric($args)) ? $this->get_mail($args) : $args ;

		return $this->end( $this->start() );
	}

	function start()
	{

/** INIT **/
		$mail = $this->mail = (object) '';

// MP_Log
		$f = (isset($this->args->forcelog)) ? true : false; 
		$this->trace = new MP_Log('MP_Mail',ABSPATH . MP_PATH,$this->plug,$f);

		if (!$this->args)	
		{
$this->trace->log(__('*** ERROR *** Sorry invalid arguments in MP_Mail::send','MailPress'));
			return array(false,false);
		}

// general settings
		$mp_general = get_option('MailPress_general');

// charset
		$mail->charset = (isset($this->args->charset)) ? $this->args->charset : get_option('blog_charset');


/** BUILD $mail **/


// fromemail & fromname
		$mail->fromemail 	= (empty($this->args->fromemail)) ? $mp_general['fromemail'] : $this->args->fromemail;
		$mail->fromname 	= (empty($this->args->fromname))  ? $mp_general['fromname']  : $this->args->fromname;

// toemail & toname
		
		if (isset($this->args->replacements))
		{
			if (0 == count($this->args->replacements))
			{
$this->trace->log(__('*** WARNING *** No recipient','MailPress'));	
				return array(false,false);
			}
			$mail->toemail = $this->args->replacements;
			$mail->toname  = '';
			$batch = true;
		}
		else
		{
			if ( (isset($this->args->toemail)) && (!empty($this->args->toemail)) )
			{
				$mail->toemail 	= $this->args->toemail;
				$mail->toname 	= (empty($this->args->toname))  ? $this->args->toemail : $this->args->toname;
			}
			else
			{
$this->trace->log(__('*** ERROR *** No recipient','MailPress'));	
				return array(false,false);
			}
			$batch = false;
		}

// subject
		$mail->subject = (isset($this->args->subject)) ? $this->args->subject : false;
		$pt = $this->theme->get_page_templates_from((isset($this->args->Theme)) ? $this->args->Theme : $this->theme->themes[$this->theme->current_theme]['Template']);
		if (isset($this->args->Template)) if (isset($pt[$this->args->Template][1]) ) $mail->subject = $pt[$this->args->Template][1];
		$mail->subject = ($mail->subject) ? trim($this->mail_decorator($this->do_eval($mail->subject),get_object_vars($this->args))) : '';
// plaintext
		$mail->plaintext = trim(strip_tags($this->build_mail_content('plaintext')));
// html
		$mail->html = trim($this->build_mail_content('html'));
		$mail->html = ( '<br />' == $mail->html ) ? '' : $mail->html;

		$mail->theme	= $this->mail->theme;
		$mail->themedir	= $this->mail->themedir;
		$mail->template 	= $this->mail->template;

// mail empty ?
		if (!$mail->subject && !$mail->plaintext && !$mail->html)
		{
$this->trace->log(__('*** WARNING *** Mail is empty','MailPress'));
			return array(false,false);
		}

$x  = " \n\n ------------------- start of mail -------------------  ";
$x .= " \n From : " . $mail->fromname . " <" . $mail->fromemail . "> ";
$x .= " \n Subject : "                                               . $mail->subject;
$x .= " \n   ------------------- plaintext -------------------  \n " . $mail->plaintext;
$x .= " \n\n ------------------- text/html -------------------  \n " . $mail->html;
$x .= " \n\n ------------------ end of mail ------------------  \n\n";
$this->trace->log($x,$this->trace->levels[512]);

// no mail ?
		$rc = array (true,false);
		if (isset($this->args->nomail))
		{
$this->trace->log(__(':::: Mail not sent as required ::::','MailPress'));
		}
		else
		{

/** SWIFT MESSAGE **/

			try 
			{
// from
				$from = new Swift_Address($mail->fromemail, 	$mail->fromname);
// to 
				if ($batch)
				{
					$to = new Swift_RecipientList();
					foreach ($mail->toemail as $dest)
					{
						$email = $dest['{{toemail}}'];
						$to->addTo($email,$email);
					}
				}
				else
				{
					$to 	= new Swift_Address($mail->toemail, 	$mail->toname);
				}
// subject 
				$this->message 	 = new Swift_Message($mail->subject);
				$this->message->headers->setLanguage(substr(WPLANG,0,2));
				$this->message->headers->setCharset($mail->charset);
// plaintext 
				if ($mail->plaintext)
				{
					$plaintxt 	  = new Swift_Message_Part($mail->plaintext);
					$plaintxt->setCharset($mail->charset);
					$this->message->attach($plaintxt);
				}
// html
				if ($mail->html)
				{
					$htmltxt 	  = new Swift_Message_Part( $this->process_img($mail->html,$mail->themedir ) , 'text/html');
					$htmltxt->setCharset($mail->charset);
					$this->message->attach($htmltxt);
					$mail->html = $this->process_img($mail->html,$mail->themedir,'draft');
				}
			}
			catch (Swift_Message_MimeException $e) 
			{
$this->trace->log('**** SWIFT ERROR ****' . "There was an unexpected problem building the email:\n" . $e->getMessage());	
				return array(false,false);
			}

/** SWIFT CONNECTION **/

			try 
			{
				$Swift_Connection_type = apply_filters('MailPress_Swift_Connection_type','SMTP');

				switch ($Swift_Connection_type)
				{
					case 'SMTP' :
						require_once 'swift/Swift/Connection/SMTP.php';
						$smtp_config = get_option('MailPress_smtp_config');

						$enc 		= null;
						switch (true)
						{
							case ('ssl' == $smtp_config ['ssl']) :
								$enc = Swift_Connection_SMTP::ENC_SSL;
							break;
							case ('tls' == $smtp_config ['ssl']) :
								$enc = Swift_Connection_SMTP::ENC_TLS;
							break;
						}
						$conn = new Swift_Connection_SMTP($smtp_config ['server'], $smtp_config ['port'], $enc);

						if (isset($smtp_config['smtp-auth']) && (!empty($smtp_config['smtp-auth'])))
						{

							require_once 'swift/Swift/Authenticator/' . $smtp_config['smtp-auth'] . '.php';

							switch ($smtp_config['smtp-auth'])
							{
								case 'CRAMMD5' :
									$conn->attachAuthenticator(new Swift_Authenticator_CRAMMD5());
								break;
								case 'LOGIN' :
									$conn->attachAuthenticator(new Swift_Authenticator_LOGIN());
								break;
								case 'PLAIN' :
									$conn->attachAuthenticator(new Swift_Authenticator_PLAIN());
								break;
								case '@PopB4Smtp' :
									$conn->attachAuthenticator(new Swift_Authenticator_PopB4Smtp($smtp_config['pophost']));
								break;
							}
						}

						if (empty($smtp_config['username']) && empty($smtp_config['password']))
						{
$this->trace->log("**** Empty user/password for $Swift_Connection_type connection ****");	
						}
						else
						{
							$conn->setUsername($smtp_config ['username']);
							$conn->setPassword($smtp_config ['password']);
						}
					break;
					default :
						$conn = apply_filters('MailPress_Swift_Connection_' . $Swift_Connection_type , null );
					break;
				}
				$this->swift = new Swift($conn);
			}
			catch (Swift_Connection_Exception $e) 
			{
$this->trace->log('**** SWIFT ERROR ****' . "There was a problem connecting with $Swift_Connection_type :\n" . $e->getMessage());	
				return array(false,false);
			} 
			Swift_CacheFactory::setClassName('Swift_Cache_Disk');
			Swift_Cache_Disk::setSavePath(MP_TMP . "/tmp");

/** SWIFT SEND **/

			try 
			{
				if ($batch)
				{
					require_once 'swift/Swift/Plugin/Decorator.php';
					$this->swift->attachPlugin(new Swift_Plugin_Decorator($mail->toemail), 'decorator');

					if ($this->swift->batchSend($this->message, $to , $from)) 	$rc = array (true,false);
					else										$rc = array (false,true);
				}
				else
				{
					if ($this->swift->send($this->message, $to, $from)) 		$rc = array (true,true);
					else 										$rc = array (false,true);
				}
			}
			catch (Swift_Connection_Exception $e) 
			{
$this->trace->log('**** SWIFT ERROR ****' . "There was a problem sending with $Swift_Connection_type :\n" . $e->getMessage());	
				return array(false,false);
			} 
		}

/** MAIL SAVE **/

		if (!isset($this->args->noarchive) && (reset($rc))) 
		{ 
			global $wpdb;

			if (!isset($this->args->nostats)) MailPress::update_stats('t',$this->args->Template,(isset($this->args->replacements)) ? count($this->args->replacements) : 1);

			$now	  	= date('Y-m-d H:i:s');
			$user_id 	= MailPress::get_wp_user_id();

			if (isset($this->args->id)) $mail->id = $this->args->id;
			$mail->status = 'sent';
			if ($batch) $mail->toemail = serialize($mail->toemail);

			$mail->subject		= addslashes(trim($mail->subject));
			$mail->html			= addslashes(trim($mail->html));
			$mail->plaintext		= addslashes($mail->plaintext);

			$mail->created 		= $now;
			$mail->created_user_id 	= $user_id;
			$mail->sent 		= $now;
			$mail->sent_user_id 	= $user_id;

			if (isset($mail->id)) 
			{
				wp_cache_delete($mail->id,'mp_mail');
				$query = "UPDATE $wpdb->mp_mails SET status = '$mail->status', theme = '$mail->theme', themedir = '$mail->themedir', template = '$mail->template', fromemail = '$mail->fromemail', fromname = '$mail->fromname', toemail = '$mail->toemail', toname = '$mail->toname',  charset = '$mail->charset', subject = '$mail->subject', html = '$mail->html', plaintext = '$mail->plaintext', sent = '$mail->sent', sent_user_id = $mail->sent_user_id WHERE id = $mail->id ;";
			}
			else
			{
				$query = "INSERT INTO $wpdb->mp_mails (status, theme, themedir, template, fromemail, fromname, toemail, toname, charset, subject, html, plaintext, created, created_user_id, sent, sent_user_id ) values ('$mail->status', '$mail->theme', '$mail->themedir', '$mail->template', '$mail->fromemail', '$mail->fromname', '$mail->toemail', '$mail->toname', '$mail->charset', '$mail->subject', '$mail->html', '$mail->plaintext', '$mail->created', $mail->created_user_id, '$mail->sent', $mail->sent_user_id );";
			}

			$x = $wpdb->query( $query );

			if (!$x)
$this->trace->log(sprintf(__('*** ERROR *** Database error, Mail not saved : %1$s','MailPress'), $wpdb->last_error));
			else
$this->trace->log(__('::: MAIL SAVED :::','MailPress'));

			return ($x) ? array(true,end($rc)) : array(false,end($rc)) ;
		}
		else
		{
			if (isset($this->args->noarchive) && (isset($this->args->id))) MP_Mail::delete($this->args->id);
		}
		return $rc;
	}

	function end($rc)
	{
		if (end($rc)) $this->swift->disconnect();
		$this->trace->end(reset($rc));
		return reset($rc);
	}

	function do_eval($x)
	{
		ob_start();
			echo(eval('global $posts, $post, $wp_did_header, $wp_did_template_redirect, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID; ?>' . $x));
			$x = ob_get_contents();
		ob_end_clean();
		return $x;
	}

	function build_mail_content($type)
	{
		$x = $theme = $themedir = $templatefile = $content = '';
		$ct = $this->theme->current_theme;

//theme & themedir

		switch ($type)
		{
			case 'plaintext' :
				$this->theme->current_theme = 'plaintext';
				$x  = $this->theme->get_theme_by_template('plaintext');
				if (empty($x))
				{
if (isset($this->trace)) $this->trace->log('Missing theme : plaintext theme has been deleted.',$this->trace->levels[512]);
					$this->theme->current_theme = $ct;
					return '';
				}
				$themedir 					= $x['Template Dir'];
				$this->mail->theme	= $theme	= 'plaintext';
				if (isset($this->args->plaintext)) $content = $this->args->plaintext;
			break;
			default :
				if (isset($this->args->Theme))
				{
					$x  = $this->theme->get_theme_by_template($this->args->Theme);
					if (!empty($x))
					{
						$this->theme->current_theme 	= $x['Name'];
						$this->mail->theme		= $theme 	= $this->args->Theme;
						$this->mail->themedir		= $themedir = $x['Template Dir'];
					}
					else
if (isset($this->trace)) $this->trace->log(sprintf('Missing theme : %1$s. You should create this theme with the appropriate files (header, footer, ...) & templates.', $this->args->Theme),$this->trace->levels[512]);
				}
				if (empty($x))
				{
					$this->mail->theme 	= $theme 	= $this->theme->themes[$this->theme->current_theme]['Template'];
					$this->mail->themedir  	= $themedir = $this->theme->themes[$this->theme->current_theme]['Template Dir'];
				}
				if (isset($this->args->html)) $content = $this->args->html;
			break;
		}

//template
		$templatefile  		= '';
		$this->mail->template 	= '';
		$pt = $this->theme->get_page_templates_from($this->mail->theme);

		if (isset($this->args->Template)) 
		{		
			if (isset($pt[$this->args->Template]))
			{
				$templatefile  		= $pt[$this->args->Template][0];
				$this->mail->template 	= $this->args->Template;
			}	
			else
if (isset($this->trace)) $this->trace->log(sprintf('Missing template file %1$s. You should create the template in %2$s .', $this->args->Template, $themedir ),$this->trace->levels[512]);
		}

		$fname = ABSPATH . $themedir . '/' . $templatefile;
		if (is_file($fname))	$content = file_get_contents($fname);
		else
		{
			if ('' == $content)
				if (isset($this->args->content))
					$content = $this->args->content;
			if ('' != $content)
				$content = '<?php $this->get_header(); ?>' . $content . '<?php $this->get_footer(); ?>' ;
		}

		if ('' == $content) 
		{
			$this->theme->current_theme = $ct;
			return '';
		}
			
// functions.php
		$fname = ABSPATH . $themedir . '/' . 'functions.php';
		if (is_file($fname)) $content = "<?php include ('$fname'); ?>" . $content;

		$x = $this->do_eval($content);

		$this->theme->current_theme = $ct;

		return $this->mail_decorator($x,get_object_vars($this->args));
	}

	function mail_decorator($text,$x='',$sepb='{{',$sepa='}}',$before='',$first=0)
	{
		foreach($x as $key => $value)
		{
			if (is_object($value) || is_array($value))
			{
				$abefore 	= (0 == $first) 		? $key 				: $before . '[' . $key . ']'; 
				$a = (is_object($value)) ? get_object_vars($value) : $value;
				$text = $this->mail_decorator($text, $a , $sepb , $sepa , $abefore , $first + 1 );
			}
			else 
			{
				$x 		= (0 == $first)		? $key 				: $before . '[' . $key . ']'; 
				$x = $sepb . $x . $sepa;
				$text = str_replace($x,$value,$text,$c);
				if (isset($this->trace))
				if (0 < $c ) 	$this->trace->log('Mail decorator : ' . $first . ' ' . $x . ' => ' . $value . " *** DONE ($c) *** ");
				//else 		$this->trace->log('Mail decorator : ' . $first . ' ' . $x . ' => ' . $value ,$this->trace->levels[8191]);
			}
		}
		return $text;	
	}

	function process_img($html,$path,$dest='mail')
	{
		$x		= $matches = array();
		$masks 	= array ('', $path . '/images/', $path . '/');

		$siteurl 	= get_option('siteurl') . '/';
		$fprefix 	= ('mail' == $dest) ? ABSPATH : $siteurl;

		$output = preg_match_all('/<img[^>]*>/Ui', $html, $imgtags, PREG_SET_ORDER); // all img tag

		foreach ($imgtags as $imgtag)
		{
			$output = preg_match_all('/src=[\'"]([^\'"]+)[\'"]/Ui', $imgtag[0], $src, PREG_SET_ORDER); // for src attribute
			$matches[] = array(0 => $imgtag[0], 1 => $src[0][1]);
		}

		foreach ($matches as $match)
		{
			$f = $u = false;

			if (stristr($match[1],$siteurl) && apply_filters('MailPress_process_img_url',false))
			{
				$imgs[$match[1]] = $match[1];
				continue;
			}
			elseif (stristr($match[1],$siteurl)) $u = true;
			elseif ((stristr($match[1],'http://')) || (stristr($match[1],'https://')))
			{
				$imgs[$match[1]] = $match[1];
				continue;
			}

			foreach ($masks as $mask)
			{
				if ($u) 	$file = str_ireplace($siteurl,'',$match[1]);
				else		$file = $mask . $match[1];

				if (is_file(ABSPATH . $file)) 
				{
					$f = true;
					$x[$match[1]] = $fprefix . $file;		// we can have the src/url image in different img tags ... so we embed it one time only
if ('mail' == $dest) $this->trace->log('Image found : ' . $file,$this->trace->levels[512]);
				}
				if ($f) break;
			}
if (('mail' == $dest) && (!$f)) $this->trace->log('Image NOT found : ' . $match[1],$this->trace->levels[512]);
		}

		if ('mail' == $dest)
		{
			foreach ($x as $key => $file)
			{
				try 
				{
					$y = new Swift_File($file);
					$z = new Swift_Message_Image($y);
					$imgs[$key] = $this->message->attach($z);
				}
				catch (Swift_FileException $e) 
				{
if (isset($this->trace))
$this->trace->log('**** SWIFT ERROR ****' . "There was a problem with this image: $file \n" . $e->getMessage());	
				} 
			}
		}
		else
		{
			foreach ($x as $key => $file)
			{
				$imgs[$key] = $file;
			}
		}

		foreach ($matches as $match)
		{
			$match[3]  = str_replace($match[1], $imgs[$match[1]], $match[0]); // and we retrieve it now with the proper <img ... />

			if ('html' != $dest) $match[3]  = apply_filters('MailPress_process_img',$match[3]); // apply_filters for 'mail','draft'

			$html      = str_replace($match[0], $match[3], $html);
		}

	return $html;
	}

	function get_header() {
		$path	= ABSPATH . $this->theme->themes[$this->theme->current_theme] ['Template Dir'];

		if ( file_exists( $path . '/header.php') )
			$this->load_template( $path . '/header.php');
		elseif ('plaintext' != $this->args->Theme)
			$this->load_template( ABSPATH . MP_PATH . 'mp-content/themes/default/header.php');
	}

	function get_footer() {
		$path	= ABSPATH . $this->theme->themes[$this->theme->current_theme] ['Template Dir'];

		if ( file_exists( $path . '/footer.php') )
			$this->load_template( $path .  '/footer.php');
		elseif ('plaintext' != $this->args->Theme)
			$this->load_template( ABSPATH . MP_PATH . 'mp-content/themes/default/footer.php');
	}

	function get_stylesheet() {
		$path	= ABSPATH . $this->theme->themes[$this->theme->current_theme] ['Stylesheet Dir'];

		if ( file_exists( $path . '/style.css') )
		{
			echo "<style type='text/css' media='all'>\n";
			$this->load_template( $path .  '/style.css');
			echo "</style>\n";
		}
		elseif ('plaintext' != $this->args->Theme)
		{
			echo "<style type='text/css' media='all'>\n";
			$this->load_template( ABSPATH . MP_PATH . 'mp-content/themes/default/style.css');
			echo "</style>\n";
		}
	}

	function get_sidebar( $name = null ) {
		$path	= ABSPATH . $this->theme->themes[$this->theme->current_theme] ['Template Dir'];

		if ( isset($name) && file_exists( $path . "/sidebar-{$name}.php") )
			$this->load_template(  $path  . "/sidebar-{$name}.php");
		elseif ( file_exists(  $path . '/sidebar.php') )
			$this->load_template(  $path  . '/sidebar.php');
		elseif ('plaintext' != $this->args->Theme)
			$this->load_template( ABSPATH . MP_PATH . 'mp-content/themes/default/sidebar.php');
	}

	function load_template($_template_file) 
	{
		global $posts, $post, $wp_did_header, $wp_did_template_redirect, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;
	
		if ( is_array($wp_query->query_vars) )
			extract($wp_query->query_vars, EXTR_SKIP);

		include($_template_file);
	}


////	MP_Mail mail functions	////

	function &get_mail(&$mail, $output = OBJECT) {
		global $wpdb;

		switch (true)
		{
			case ( empty($mail) ) :
				if ( isset($GLOBALS['mp_mail']) ) 	$_mail = & $GLOBALS['mp_mail'];
				else						$_mail = null;
			break;
			case ( is_object($mail) ) :
				wp_cache_add($mail->id, $mail, 'mp_mail');
				$_mail = $mail;
			break;
			default :
				if ( isset($GLOBALS['mp_mail']) && ($GLOBALS['mp_mail']->id == $mail) ) 
				{
					$_mail = & $GLOBALS['mp_mail'];
				} 
				elseif ( ! $_mail = wp_cache_get($mail, 'mp_mail') ) 
				{
					$_mail = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->mp_mails WHERE id = %d LIMIT 1", $mail));
					wp_cache_add($_mail->id, $_mail, 'mp_mail');
				}
			break;
		}

		if ( $output == OBJECT ) {
			return $_mail;
		} elseif ( $output == ARRAY_A ) {
			return get_object_vars($_mail);
		} elseif ( $output == ARRAY_N ) {
			return array_values(get_object_vars($_mail));
		} else {
			return $_mail;
		}
	}

	function delete($id)
	{
		global $wpdb;
		MP_Mail::delete_mail_meta( $id ); 
		$query = "DELETE FROM $wpdb->mp_mails WHERE id = $id ; ";
		return $wpdb->query( $query );
	}

	function display_toemail($toemail,$toname,$tolist='')
	{
		$return = '';
		$draft_dest = MP_User::get_mailing_lists();

		if 		(!empty($tolist)  && isset($draft_dest[$tolist]))	return "<b>" . $draft_dest[$tolist] . "</b>"; 
		elseif 	(!empty($toemail) && isset($draft_dest[$toemail]))	return "<b>" . $draft_dest[$toemail] . "</b>"; 
		elseif 	(MailPress::is_email($toemail))				return "$toemail &nbsp;&nbsp;&nbsp;&nbsp;&lt;$toname&gt;"; 
		else
		{
			$y = unserialize($toemail);
			if (is_array($y))
			{
				$return = '<select>';
				foreach ($y as $k => $v)
				{
					$return .= "<option>$k</option>";
				}
				$return .= '</select>';
				return $return;
			}
		}
		return false;
	}


////	MP_Mail draft functions	////

	function get_id()
	{
		global $wpdb;
		$x 	= md5(uniqid(rand(),1));
		$now	= date('Y-m-d H:i:s');
		$wp_user = MailPress::get_wp_user_id();

		$query = "INSERT INTO $wpdb->mp_mails (status, theme, themedir, template, fromemail, fromname, toemail, toname, subject, html, plaintext, created, created_user_id, sent, sent_user_id ) values ('', '$x', '', '', '', '', '', '', '', '', '', '$now', $wp_user, '0000-00-00 00:00:00', 0);";
		$wpdb->query( $query );
		return $wpdb->get_var( "SELECT id FROM $wpdb->mp_mails WHERE theme = '$x' ;" );
	}

	function update_draft($id,$status='draft')
	{
		global $wpdb;
		$now = date('Y-m-d H:i:s');
		if ('' != $_POST['to_list'])
		{
			$_POST['toemail'] = $_POST['to_list'];
			$_POST['toname']  = '';
		}
		if (isset($_POST['content'])) $_POST['html'] = $_POST['content'];
		$query = "UPDATE $wpdb->mp_mails SET status = '$status', theme = '', toemail = '" . trim($_POST['toemail']) . "', toname = '" . trim($_POST['toname']) . "', subject = '" . trim($_POST['subject']) . "', html = '" . trim($_POST['html']) . "', plaintext = '" . trim($_POST['plaintext']) . "', created = '$now', created_user_id = " . MailPress::get_wp_user_id() . " WHERE id = $id ;";
		return $wpdb->query( $query );
	}

	function send_draft($id=NULL,$ajax=false) {

		if (NULL == $id) return false;
		$draft = MP_Mail::get_mail($id);
		if ('draft' != $draft->status) return false;

		$mail->id = MP_Mail::get_id();

		$count = 0;

		$query = MP_User::get_mailing_lists_query($draft->toemail);

		if ($query)
		{
			$mail->replacements = MP_User::get_recipients($query,$mail->id);
			$mail->unsubscribe  = '{{unsubscribe}}';
			$mail->viewhtml	  = '{{viewhtml}}';
			$count = count($mail->replacements);
		}
		else
		{
			if 	(!MailPress::is_email($draft->toemail)) return 'y';
			$mail->toemail 		 = $draft->toemail;
			$mail->toname		 = $draft->toname;
			$key 				 = MP_User::get_key_by_email($draft->toemail);
			if ($key)
			{
				$mail->subscribe 	 = MP_User::get_subscribe_url($key);
				$mail->unsubscribe = MP_User::get_unsubscribe_url($key);
				$mail->viewhtml	 = MP_User::get_view_url($key,$mail->id);
			}
			$count = 1;
		}

// Set mail's subject and body
		$mail->subject	= stripslashes($draft->subject);
		$mail->html		= stripslashes(apply_filters('the_content', $draft->html));
		$mail->plaintext	= stripslashes($draft->plaintext);

		$mail->draft 	= true;

		if (0 == $count)				return 'x';				// no recipient

		if (MailPress::mail($mail))							// ok
		{
			if ($ajax) 	return array($mail->id);
			else		return $count;
		}
		return 0;										// ko
	}

	function check_mail_lock( $id ) {
		global $current_user;

		if ( !$mail = MP_Mail::get_mail( $id ) )
			return false;

		$lock = MP_Mail::get_mail_meta( $mail->id, '_edit_lock', true );
		$last = MP_Mail::get_mail_meta( $mail->id, '_edit_last', true );

		$time_window = apply_filters( 'mp_check_mail_lock_window', AUTOSAVE_INTERVAL * 2 );
	
		if ( $lock && $lock > time() - $time_window && $last != $current_user->ID )	return $last;
		return false;
	}

	function set_mail_lock( $id ) {
		global $current_user;
		if ( !$mail = MP_Mail::get_mail( $id ) )		return false;
		if ( !$current_user || !$current_user->ID )	return false;

		$now = time();

		MP_Mail::update_mail_meta( $mail->id, '_edit_lock', $now );
		MP_Mail::update_mail_meta( $mail->id, '_edit_last', $current_user->ID );
	}


////	MP_Mail meta functions	////

	function delete_mail_meta( $mp_mail_id, $meta_key = '' , $meta_value = '' ) 
	{
		global $wpdb;
		if ( !is_numeric( $mp_mail_id ) ) return false;
		$meta_key = preg_replace('|[^a-z0-9_]|i', '', $meta_key);

		if ( is_array($meta_value) || is_object($meta_value) ) $meta_value = serialize($meta_value);
		$meta_value = trim( $meta_value );

		if ( ! empty($meta_value) ) 		$wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->mp_mailmeta WHERE mail_id = %d AND meta_key = %s AND meta_value = %s", $mp_mail_id, $meta_key, $meta_value) );
		elseif ( ! empty($meta_key) ) 	$wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->mp_mailmeta WHERE mail_id = %d AND meta_key = %s", $mp_mail_id, $meta_key) );
		else  					$wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->mp_mailmeta WHERE mail_id = %d", $mp_mail_id) );

		return true;
	}

	function get_mail_meta( $mp_mail_id, $meta_key = '', $meta_value = '') 
	{
		global $wpdb;
		$mp_mail_id = (int) $mp_mail_id;

		if ( !$mp_mail_id ) return false;

		if ( !empty($meta_key) ) 
		{
			$meta_key = preg_replace('|[^a-z0-9_]|i', '', $meta_key);
			if ( !empty($meta_value) ) 
			{
				$metas = $wpdb->get_col( $wpdb->prepare("SELECT meta_value FROM $wpdb->mp_mailmeta WHERE mail_id = %d AND meta_key = %s AND meta_value = %x", $mp_mail_id, $meta_key, $meta_value) );
			}
			else
			{
				
				$metas = $wpdb->get_col( $wpdb->prepare("SELECT meta_value FROM $wpdb->mp_mailmeta WHERE mail_id = %d AND meta_key = %s", $mp_mail_id, $meta_key) );
			}
		}
		else
		{
		$metas = $wpdb->get_col( $wpdb->prepare("SELECT meta_value FROM $wpdb->mp_mailmeta WHERE mail_id = %d", $mp_mail_id) );
		}

		if ( empty($metas) ) 
		{
		 	if ( empty($meta_key) ) return array();
			else			return '';
		}

		$metas = array_map('maybe_unserialize', $metas);

		if ( count($metas) == 1 ) 	return $metas[0];
		else					return $metas;
	}

	function add_mail_meta( $mp_mail_id, $meta_key, $meta_value ) 
	{
		global $wpdb;
		if ( !is_numeric( $mp_mail_id ) ) return false;
		$meta_key = preg_replace('|[^a-z0-9_]|i', '', $meta_key);

// FIXME: mp_mailmeta data is assumed to be already escaped
		if ( is_string($meta_value) )$meta_value = stripslashes($meta_value);
		$meta_value = maybe_serialize($meta_value);

		if (empty($meta_value)) return MP_Mail::delete_mail_meta($mp_mail_id, $meta_key);

		$wpdb->query( $wpdb->prepare("INSERT INTO $wpdb->mp_mailmeta ( mail_id, meta_key, meta_value ) VALUES ( %d, %s, %s )", $mp_mail_id, $meta_key, $meta_value) );

		return true;
	}

	function update_mail_meta($mail_id, $meta_key, $meta_value, $prev_value = '') 
	{
		global $wpdb;

		// expected_slashed ($meta_key)
		$meta_key = stripslashes($meta_key);

		if ( ! $wpdb->get_var( $wpdb->prepare( "SELECT meta_key FROM $wpdb->mp_mailmeta WHERE meta_key = %s AND mail_id = %d", $meta_key, $mail_id ) ) ) {
			return MP_Mail::add_mail_meta($mail_id, $meta_key, $meta_value);
		}

		$meta_value = maybe_serialize($meta_value);

		$data  = compact( 'meta_value' );
		$where = compact( 'meta_key', 'mail_id' );

		if ( !empty( $prev_value ) ) 
		{
			$prev_value = maybe_serialize($prev_value);
			$where['meta_value'] = $prev_value;
		}

		$wpdb->update( $wpdb->mp_mailmeta, $data, $where );
		return true;
	}


////	MP_Mail admin list functions	////

	function get_list( $url_parms, $start, $num ) {
		global $wpdb;

		$start = abs( (int) $start );
		$num = (int) $num;

		$where = " status <> '' ";
		if (isset($url_parms['s']) && !empty($url_parms['s']))
		{
			$s = $wpdb->escape($url_parms['s']);
			if (!empty($where)) $where = $where . ' AND ';
			if ($s) $where .= " ((theme LIKE '%$s%') OR (themedir LIKE '%$s%') OR (template LIKE '%$s%') OR (toemail LIKE '%$s%') OR (subject LIKE '%$s%') OR (html LIKE '%$s%') OR (plaintext LIKE '%$s%') OR (created like '%$s%') OR (sent like '%$s%')) "; 
		}
		if (isset($url_parms['status']) && !empty($url_parms['status']))
		{
			if (!empty($where)) $where = $where . ' AND ';
			$where .= "status = '" . $url_parms['status'] . "'";
		}
		if (isset($url_parms['author']) && !empty($url_parms['author']))
		{
			if (!empty($where)) $where = $where . ' AND ';
			$where .= "( created_user_id = " . $url_parms['author'] . "  OR sent_user_id = " . $url_parms['author'] . " ) ";
		}
		if ($where) $where = ' WHERE ' . $where;

		$mails = $wpdb->get_results( "SELECT SQL_CALC_FOUND_ROWS * FROM $wpdb->mp_mails  $where ORDER BY created DESC LIMIT $start, $num" );

		MP_Admin::update_cache($mails,'mp_mail');

		$total = $wpdb->get_var( "SELECT FOUND_ROWS()" );

		return array($mails, $total);
	}

	function get_row( $id, $url_parms, $xtra=false) {

		global $mp_mail;

		$mp_mail = $mail = MP_Mail::get_mail( $id );
		$the_mail_status = $mail->status;

// url's
		$view_url    	= clean_url(MP_Admin::url( MailPress_mail  ."&action=view&id=$id",	false,	$url_parms ));
		$edit_url    	= clean_url(MP_Admin::url( MailPress_write ."&id=$id",			false,	$url_parms ));

		$actions = array();

		if ('draft' == $mail->status) 
		{ 
			$send_url 	= clean_url(MP_Admin::url( MailPress_mail	."&action=send&id=$id",		"send-mail_$id",		$url_parms ));
			$actions['send']   = "<a href='$send_url' 	class='dim:the-mail-list:mail-$id:unapproved:e7e7d3:e7e7d3:?mode=" . $url_parms['mode'] . "' title='" . __('Send this mail','MailPress' ) . "'>" 	. __( 'Send','MailPress' ) . '</a> | ';
			$delete_url = clean_url(MP_Admin::url( MailPress_mail	."&action=delete&id=$id",	"delete-mail_$id" ,	$url_parms ));
			$actions['delete'] = "<a href='$delete_url' 	class='delete:the-mail-list:mail-$id delete'>" 										. __('Delete','MailPress') . '</a>';
		}
		elseif ( current_user_can( 'level_10') ) 
		{ 
			$delete_url = clean_url(MP_Admin::url( MailPress_mail	."&action=delete&id=$id",	"delete-mail_$id" ,	$url_parms ));
			$actions['delete'] = "<a href='$delete_url' 	class='delete:the-mail-list:mail-$id delete'>" 										. __('Delete','MailPress') . '</a>';
		}
// data
		$class = ('draft' == $the_mail_status) ? 'draft' : '';
//to
		$draft_dest = MP_User::get_mailing_lists();

		$email = false;
		if 	($xtra)										$email_display = "<blink style='color:red;font-weight:bold;'>" . $xtra . '</blink>';
		elseif (is_array(unserialize($mail->toemail)))					$email_display = '(' . count(unserialize($mail->toemail)) . ')'; 
		elseif (MailPress::is_email($mail->toemail))
		{
			$email = true;
			$email_display = $mail->toemail;
			if ( strlen($email_display) > 40 )						$email_display = substr($email_display, 0, 39) . '...';
			$mail_url = MailPress_write . '&amp;toemail=' . $mail->toemail;

			$mail_url = MP_Admin::url(MailPress_mails, false, $url_parms);
			$mail_url = remove_query_arg('s',$mail_url);
			$mail_url = clean_url( $mail_url . '&s=' . $mail->toemail );
			
		}
		elseif (isset($draft_dest[$mail->toemail]))	$email_display = $draft_dest[$mail->toemail];
		else
		{
			$email_display = "<span style='color:red;font-weight:bold;'>" . __('(unknown)','MailPress') . '</span>';
			unset($actions['send']);
		}
//by
		$author = ( 0 == $mail->sent_user_id) ? $mail->created_user_id : $mail->sent_user_id;
		if ($author != 0 && is_numeric($author)) {
			unset($url_parms['author']);
			$wp_user 		= get_userdata($author);
			$author_url 	= clean_url(MP_Admin::url( MailPress_mails . "&author=" . $author, false, $url_parms ));
		}

		$subject_display = htmlspecialchars($mail->subject,ENT_QUOTES); ;
		if ( strlen($subject_display) > 40 )	$subject_display = substr($subject_display, 0, 39) . '...';
?>
	<tr id="mail-<?php echo $id; ?>" class='<?php echo $class; ?>'>

<?php 	if ('draft' == $mail->status) { ?>
		<th class='check-column' scope='row'>
			<input type='checkbox' name='delete_mails[]' value='<?php echo $id; ?>'/>
		</th><td></td>
<?php 	} else { ?>
		<th class='check-column' scope='row'>
			<?php if ( current_user_can( 'level_10' ) ) { ?><input type='checkbox' name='delete_mails[]' value='<?php echo $id; ?>'/><?php } ?>
		</th>
		<td>
			<abbr title="<?php MP_Mail::mail_date('Y/m/d H:i:s'); ?>"><?php MP_Mail::mail_date('Y/m/d'); ?></abbr>
		</td>
<?php 	} ?>

		<td>
			<a class='row-title' href='<?php echo ('draft' == $mail->status) ? $edit_url : $view_url; ?>' title='<?php printf( ('draft' == $mail->status) ?  __('Edit "%1$s"','MailPress') : __('View "%1$s"','MailPress') , ( '' == $mail->subject) ? __('(no subject)','MailPress') : htmlspecialchars($mail->subject, ENT_QUOTES) ); ?>'>
				<?php echo ( '' == $subject_display) ? __('(no subject)','MailPress') : $subject_display; ?>
			</a>
		</td>
		<td>
<?php 	if ($author != 0 && is_numeric($author)) { ?>
				<a href='<?php echo $author_url; ?>' title='<?php printf( __('Mails by "%1$s"','MailPress'), $wp_user->display_name); ?>'><?php echo $wp_user->display_name; ?></a>
<?php 	} else  	_e("(unknown)",'MailPress');
?>
		</td>
		<td>
			<strong>
<?php 	if ($email) { ?>
				<a class='row-title' href='<?php echo $mail_url; ?>'  title='<?php printf( __('Search "%1$s"','MailPress'), $mail->toemail); ?>'>
					<?php if ( ('detail' == $url_parms['mode']) && (get_option('show_avatars') ) ) echo get_avatar( $mail->toemail, 32 );?>
					<?php echo $email_display; ?>
				</a>
<?php 	} else { ?>
				<?php echo $email_display; ?>
<?php 	} ?>
			</strong>
		</td>
		<td>
			<?php echo $mail->theme; ?>
			<br/>
			<?php if ('detail' == $url_parms['mode']) echo ('' == $mail->template) ? "" : "(" . $mail->template . ")"; ?>
		</td>
		<td>
<?php foreach ( $actions as $action => $link ) echo "<span class='$action'>$link</span>"; ?>
		</td>
	  </tr>
<?php
	}

	function mail_date( $d = '' ) {
		echo MP_Mail::get_mail_date( $d );
	}

	function get_mail_date( $d = '' ) {
		global $mp_mail;
		$x = ($mp_mail->sent >= $mp_mail->created) ? $mp_mail->sent : $mp_mail->created;
		$date  = ( '' == $d ) ? mysql2date( get_option('date_format'), $x) : mysql2date($d, $x);
		return apply_filters('get_comment_date', $date, $d);
	}


////	MP_Mail admin list ajax functions	////

	function mp_action_delete_mail() {
		$id = isset($_POST['id'])? (int) $_POST['id'] : 0;
		die( MP_Mail::delete( $id ) ? '1' : '0' );
	}

	function mp_action_send_mail() {
		$id 		= isset($_POST['id']) 	? (int) $_POST['id'] : 0;
		$url_parms 	= MP_Admin::get_url_parms();
		$x 		= MP_Mail::send_draft($id,true);
		if (is_array($x))
		{
			ob_end_clean();
			ob_start();
				MP_Mail::get_row( $x[0], $url_parms );
				$item = ob_get_contents();
			ob_end_clean();
			header('Content-Type: text/xml');
			echo "<?xml version='1.0' standalone='yes'?><mp_action>";
			echo "<rc><![CDATA[2]]></rc>";
			echo "<id><![CDATA[$id]]></id>";
			echo "<item><![CDATA[$item]]></item>";
			echo '</mp_action>';
			die(0);
		}
		elseif (is_numeric($x))
		{
			if (0 == $x)
			{
				die(-1);
			}
			else
			{
				ob_end_clean();
				ob_start();
					MP_Mail::get_row( $id, $url_parms );
					$item = ob_get_contents();
				ob_end_clean();
				header('Content-Type: text/xml');
				echo "<?xml version='1.0' standalone='yes'?><mp_action>";	
				echo "<rc><![CDATA[1]]></rc>";
				echo "<id><![CDATA[$id]]></id>";
				echo "<item><![CDATA[$item]]></item>";
				echo '</mp_action>';
				die(1);
			}
		}
		else
		{
			ob_end_clean();
			ob_start();
				MP_Mail::get_row( $id, $url_parms, __('no recipient','MailPress') );
				$item = ob_get_contents();
			ob_end_clean();
			header('Content-Type: text/xml');
			echo "<?xml version='1.0' standalone='yes'?><mp_action>";
			echo "<rc><![CDATA[0]]></rc>";
			echo "<id><![CDATA[$id]]></id>";
			echo "<item><![CDATA[$item]]></item>";
			echo '</mp_action>';
			die(0);
		}
	}

	function mp_action_add_mail() {
		$url_parms = MP_Admin::get_url_parms(array('mode','status','s'));

		$start = isset($_POST['apage']) ? intval($_POST['apage']) * 25 - 1: 24;

		list($mails, $total) = MP_Mail::get_list( $url_parms, $start, 1 );

		if ( !$mails ) die('1');

		$x = new WP_Ajax_Response();
		foreach ( (array) $mails as $mail ) {
			MP_Mail::get_mail( $mail );
			ob_start();
				MP_Mail::get_row( $mail->id, $url_parms );
				$mail_list_item = ob_get_contents();
			ob_end_clean();
			$x->add( array(
				'what' 	=> 'mail',
				'id' 		=> $mail->id,
				'data' 	=> $mail_list_item
			) );
		}
		$x->send();
	}


////	MP_Mail mail-new mp_action functions	////

	function mp_action_autosave()
	{
		global $current_user;

		$do_autosave = (bool) $_POST['autosave'];
		$do_lock = true;

		$data = '';
		$message['revision'] = sprintf( __('Revision saved at %s.','MailPress'), date( __('g:i:s a'), current_time( 'timestamp', true ) ) );
		$message['draft']    = sprintf( __('Draft saved at %s.','MailPress'), date( __('g:i:s a'), current_time( 'timestamp', true ) ) );

		$supplemental = array();

		$id = 0;
		$supplemental['tipe'] = '';

		if ( -1 == $_POST['revision'])
		{
			$id 	= $mail->id = (int) $_POST['id'];
			if ( $do_autosave ) 
			{
				$id = (0 == $_POST['id']) ? MP_Mail::get_id() : $_POST['id'];
				$mail->id = $id;
				MP_Mail::update_draft($id);
				$data = $message['draft'];
				$supplemental['tipe'] = 'mail';
			}
		}
		else
		{
			$id 	= $mail->id = (int) $_POST['id'];
			$mail = MP_Mail::get_mail($id);

			if ( $last = MP_Mail::check_mail_lock( $mail->id ) ) 
			{
				$do_autosave 	= $do_lock = false;
				$last_user 		= get_userdata( $last );
				$last_user_name 	= $last_user ? $last_user->display_name : __( 'Someone' );	
				$data 		= new WP_Error( 'locked', sprintf( __( 'Autosave disabled: %s is currently editing this mail.' ) , wp_specialchars( $last_user_name )	) );
				$supplemental['disable_autosave'] = 'disable';
			}
			if ( $do_autosave ) 
			{
				$id = (0 == $_POST['revision']) ? MP_Mail::get_id() : $_POST['revision'];

				if (0 == $_POST['revision'])
				{
					$mailmetas 		= MP_Mail::get_mail_meta( $mail->id ,'_MailPress_mail_revisions');
					$mailmetas[$current_user->ID] = $id;
					MP_Mail::update_mail_meta($mail->id ,'_MailPress_mail_revisions',$mailmetas);
				}

				MP_Mail::update_draft($id,'');
				$data = $message['revision'];
				$supplemental['tipe'] = 'revision';
			}
			else
			{
				if (0 != $_POST['revision']) $id = $_POST['revision'];
			}
		}

		if ( $do_lock && $id ) MP_Mail::set_mail_lock( $mail->id );

		$x = new WP_Ajax_Response( array	(
								'what' => 'autosave',
								'id' => $id,
								'type' => $type,
								'data' => $id ? $data : '',
								'supplemental' => $supplemental
								)
						 );
		$x->send();
	}

	function mp_action_get_previewlink()
	{
		$mail_id = isset($_POST['id'])? intval($_POST['id']) : 0;
		$preview_url= clean_url(get_option('siteurl') . '/' . MP_PATH . "mp-includes/action.php?action=iview&id=$mail_id&KeepThis=true&TB_iframe=true");
		die($preview_url);
	}

	function mp_action_iview()
	{
		$id 		= $_GET['id'];
		$mail 	= MP_Mail::get_mail($id);
		$mp_general = get_option('MailPress_general');
		$from 	= ('send' == $mail->status) ? $mail->fromemail . '&nbsp;&nbsp;&nbsp;&nbsp;< ' . $mail->fromname . ' >' : $mp_general['fromemail'] . '&nbsp;&nbsp;&nbsp;&nbsp;< ' . $mp_general['fromname'] . ' >';
		$to 		= MP_Mail::display_toemail($mail->toemail,$mail->toname);
		$subject 	= ('send' == $mail->status) ? $mail->subject : MP_Mail::do_eval(stripslashes($mail->subject));
		$html_url 	= clean_url(get_option('siteurl') . '/' . MP_PATH . "mp-includes/action.php?action=viewadmin&id=$id&type=html");
		$html 	= "<iframe style='width:100%;border:0;height:500px' src='" . $html_url . "'></iframe>";
		$plaintext_url = clean_url(get_option('siteurl') . '/' . MP_PATH . "mp-includes/action.php?action=viewadmin&id=$id&type=plaintext");
		$plaintext 	= "<iframe style='width:100%;border:0;height:500px' src='" . $plaintext_url . "'></iframe>";

		include MP_TMP . '/mp-includes/viewthickbox.html.php';
	}


////	MP_Mail admin view iframe functions	////

	function mp_action_viewadmin() {
		$id 	= $_GET['id'];
		$type	= (isset($_GET['type'])) ? $_GET['type'] : 'html';

		$x = new MP_Mail();
		if ('html' == $type) 		$x->viewhtml($id);
		elseif ('plaintext' == $type) $x->viewplaintext($id);
	}

	function viewhtml($id)
	{
		$dest 	= 'html';
		$x 		= $this->get_mail($id);
		if ('sent' != $x->status)
		{
			$this->args->html = stripslashes(apply_filters('the_content', $x->html));
			$x->html 		= $this->build_mail_content('html');
			$x->themedir 	= $this->mail->themedir;
			$dest			= 'draft';
		}
		echo $this->process_img($x->html,$x->themedir,$dest);
	}

	function viewplaintext($id)
	{
		$x = $this->get_mail($id);
		if ('sent' != $x->status) 
		{
			$this->args->plaintext 	= stripslashes($x->plaintext);
			$x->plaintext 		= strip_tags($this->build_mail_content('plaintext'));
		}
		include MP_TMP . '/mp-includes/viewplaintext.html.php';
	}


////	MP_Mail user view iframe functions	////

	function mp_action_viewuser() {
		$id 		= $_GET['id'];
		$key		= $_GET['key'];
		$email 	= MP_User::get_user_email(MP_User::get_user_id($key));
		$mail 	= MP_Mail::get_mail($id);

		$x = new MP_Mail();

		if (MailPress::is_email($mail->toemail))
		{
			echo $x->process_img($mail->html,$mail->themedir,'html');
		}
		else
		{
			$recipients = unserialize($mail->toemail);
			$recipient = $recipients[$email];
			echo $x->process_img($x->mail_decorator($mail->html,$recipient,'',''),$mail->themedir,'html');
		}
	}
}
?>