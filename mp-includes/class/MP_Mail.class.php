<?php
add_action(	'mp_action_viewadmin',		array('MP_Mail','mp_action_viewadmin'));
add_action(	'mp_action_view',			array('MP_Mail','mp_action_viewuser'));

// for ajax admin

add_action(	'mp_action_dim-mail',		array('MP_Mail','mp_action_send_mail'));
add_action(	'mp_action_delete-mail',	array('MP_Mail','mp_action_delete_mail'));
add_action(	'mp_action_add-mail',		array('MP_Mail','mp_action_add_mail'));

class MP_Mail
{

	function MP_Mail( $plug = MP_FOLDER )
	{
		$this->plug = $plug;
// MP_Themes
		$this->theme = new MP_Themes();
	}

	function send($args)
	{
		$this->args = (is_numeric($args)) ? $this->get_mail($args) : $args ;

		return $this->end( $this->start() );
	}

	function start()
	{

/** INIT **/

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
$this->trace->log(__('*** ERROR *** No recipient','MailPress'));	
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
		$mail->subject = ($mail->subject) ? $this->do_eval($mail->subject) : '';
// plaintext
		$mail->plaintext = strip_tags($this->build_mail_content('plaintext'));
// html
		$mail->html = $this->build_mail_content('html');

		$mail->theme	= $this->mail->theme;
		$mail->themedir	= $this->mail->themedir;
		$mail->template 	= $this->mail->template;

// mail empty ?
		if (!$mail->subject && !$mail->plaintext && !$mail->html)
		{
$this->trace->log(__('*** ERROR *** Mail is empty','MailPress'));
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
				$user = $smtp_config ['username'];
				$conn->setUsername($user);
				$pass = $smtp_config ['password'];
				$conn->setPassword($pass);
				$this->swift = new Swift($conn);
			}
			catch (Swift_Connection_Exception $e) 
			{
$this->trace->log('**** SWIFT ERROR ****' . "There was a problem connecting with SMTP:\n" . $e->getMessage());	
				return array(false,false);
			} 
			Swift_CacheFactory::setClassName('Swift_Cache_Disk');
			Swift_Cache_Disk::setSavePath(MP_TMP . "/tmp");

/** SWIFT SEND **/

			try 
			{
				if ($batch)
				{
					require_once MP_TMP . '/mp-includes/class/swift/Swift/Plugin/Decorator.php';
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
$this->trace->log('**** SWIFT ERROR ****' . "There was a problem sending with SMTP :\n" . $e->getMessage());	
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
				$query = "UPDATE $wpdb->mp_mails SET status = '$mail->status', theme = '$mail->theme', themedir = '$mail->themedir', template = '$mail->template', fromemail = '$mail->fromemail', fromname = '$mail->fromname', toemail = '$mail->toemail', toname = '$mail->toname',  charset = '$mail->charset', subject = '$mail->subject', html = '$mail->html', plaintext = '$mail->plaintext', sent = '$mail->sent', sent_user_id = $mail->sent_user_id WHERE id = $mail->id ;";
			else
			{
				$query = "INSERT INTO $wpdb->mp_mails (status, theme, themedir, template, fromemail, fromname, toemail, toname, charset, subject, html, plaintext, created, created_user_id, sent, sent_user_id ) values ('$mail->status', '$mail->theme', '$mail->themedir', '$mail->template', '$mail->fromemail', '$mail->fromname', '$mail->toemail', '$mail->toname', '$mail->charset', '$mail->subject', '$mail->html', '$mail->plaintext', '$mail->created', $mail->created_user_id, '$mail->sent', $mail->sent_user_id );";
			}

			$x = $wpdb->query( $query );

			if (!$x)
$this->trace->log(__('*** ERROR *** Database error, Mail not saved','MailPress'));
			else
$this->trace->log(__('::: MAIL SAVED :::','MailPress'));

			wp_cache_delete($mail->id,'mp_mail');
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
				if (0 == $first) 	$before = $key ;
				else			$before = $before . '[' . $key . ']'; 
				$a = (is_object($value)) ? get_object_vars($value) : $value;
				$text = $this->mail_decorator($text, $a , $sepb , $sepa , $before , $first + 1 );
			}
			else 
			{
				if (0 == $first) 	$x = $key ;
				else			$x = $before . '[' . $key . ']'; 
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
		$masks = array ('', $path . '/images/', $path . '/'); 

		$x		= array();
		$file 	= '';
		$fprefix 	= ('mail' == $dest) ? ABSPATH : get_option('siteurl') . '/' ;

		$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*\/>/i', $html, $matches, PREG_SET_ORDER);

		foreach ($matches as $match)
		{
			$f = true;
			if ((strstr($match[1],'http://')) || (strstr($match[1],'https://'))) continue;
			foreach ($masks as $mask)
			{
				if (is_file(ABSPATH . $mask . $match[1])) 
				{
					$f = false;
					$file = $fprefix . $mask . $match[1];
					$x[$match[1]] = $file;		// we can have the src/url image in different img tags or url css ... so we embed it one time only
if ('mail' == $dest) $this->trace->log('Image found : ' . $file,$this->trace->levels[512]);
					continue;
				}
			}
if (('mail' == $dest) && ($f)) $this->trace->log('Image NOT found : ' . $match[1],$this->trace->levels[512]);
		}

		if ('html' == $dest)
		{
			foreach ($x as $key => $file)
			{
				$html  = str_replace($key, $file, $html);
			}
		}
		else
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
			foreach ($matches as $match)
			{
				$match[3]  = str_replace($match[1], $imgs[$match[1]], $match[0]); // and we retrieve it now with the proper <img ... />	
				$html      = str_replace($match[0], $match[3], $html);
			}
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

// view

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

	function mp_action_viewadmin() {
		$id 	= $_GET['id'];
		$type	= (isset($_GET['type'])) ? $_GET['type'] : 'html';

		$x = new MP_Mail();
		if ('html' == $type) 		$x->viewhtml($id);
		elseif ('plaintext' == $type) $x->viewplaintext($id);
	}

	function viewhtml($id)
	{
		$x = $this->get_mail($id);
		if ('draft' == $x->status)
		{
			$this->args->html = stripslashes($x->html);
			$x->html 		= $this->build_mail_content('html');
			$x->themedir 	= $this->mail->themedir;
		}
		echo $this->process_img($x->html,$x->themedir,'html');
	}

	function viewplaintext($id)
	{
		$x = $this->get_mail($id);
		if ('draft' == $x->status) 
		{
			$this->args->plaintext 	= stripslashes($x->plaintext);
			$x->plaintext 		= strip_tags($this->build_mail_content('plaintext'));
		}
		echo '<pre>' . $x->plaintext . '</pre>';
	}

// admin list

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

	function get_row( $id, $url_parms, $checkbox = true, $xtra=false ) {

		global $mp_mail;

		$draft_dest = array( '0' => '', '1' => __('active blog','MailPress'), '2' => __('active comments','MailPress'), '3' => __('active blog + comments','MailPress'), '4' => __('active + not active','MailPress')	);

		$mp_mail = $mail = MP_Mail::get_mail( $id );
		$the_mail_status = $mail->status;

// url's
		$view_url    	= clean_url(MP_Admin::url( MailPress_mail  ."&action=view&id=$id",	false,	$url_parms ));
		$edit_url    	= clean_url(MP_Admin::url( MailPress_write ."&id=$id",			false,	$url_parms ));

		if ('draft' == $mail->status) 
		{ 
			$delete_url = clean_url(MP_Admin::url( MailPress_mail	."&action=delete&id=$id",	"delete-mail_$id" ,	$url_parms ));
			$send_url 	= clean_url(MP_Admin::url( MailPress_mail	."&action=send&id=$id",		"send-mail_$id",		$url_parms ));

			$actions = array();

			$actions['send']   = "<a href='$send_url' 	class='dim:the-mail-list:mail-$id:unapproved:e7e7d3:e7e7d3:?mode=" . $url_parms['mode'] . "' title='" . __('Send this mail','MailPress' ) . "'>" 	. __( 'Send','MailPress' ) . '</a> | ';
			$actions['delete'] = "<a href='$delete_url' 	class='delete:the-mail-list:mail-$id delete'>" 										. __('Delete','MailPress') . '</a>';
		}
// data
		$class = ('draft' == $the_mail_status) ? 'draft' : '';
//to
		$email = false;
		if 	($xtra)						$email_display = "<blink style='color:red;font-weight:bold;'>" . $xtra . '</blink>';
		elseif (is_numeric($mail->toemail))			$email_display = $draft_dest[$mail->toemail];
		elseif (is_array(unserialize($mail->toemail)))	$email_display = '(' . count(unserialize($mail->toemail)) . ')'; 
		elseif (MailPress::is_email($mail->toemail))
		{
			$email = true;
			$email_display = $mail->toemail;
			if ( strlen($email_display) > 40 )	$email_display = substr($email_display, 0, 39) . '...';
			$mail_url = MailPress_write . '&amp;toemail=' . $mail->toemail;

			$mail_url = MP_Admin::url(MailPress_mails, false, $url_parms);
			$mail_url = remove_query_arg('s',$mail_url);
			$mail_url = clean_url( $mail_url . '&s=' . $mail->toemail );
			
		}
		else $email_display = "<span style='color:red;font-weight:bold;'>" . __('(unknown)','MailPress') . '</span>';
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
		<td style='text-align:left;'>
			<input style='margin:0 0 0 8px;'type='checkbox' name='delete_mails[]' value='<?php echo $id; ?>'/>
		</td>
<?php 	} else { ?>
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
<?php 	if ('draft' == $mail->status) 
		{
			foreach ( $actions as $action => $link ) echo "<span class='$action'>$link</span>";
		}
?>
		</td>
	  </tr>
<?php
	}

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
					$_mail = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->mp_mails WHERE id = %d AND status <> '' LIMIT 1", $mail));
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

	function mail_date( $d = '' ) {
		echo MP_Mail::get_mail_date( $d );
	}

	function get_mail_date( $d = '' ) {
		global $mp_mail;
		$x = ($mp_mail->sent >= $mp_mail->created) ? $mp_mail->sent : $mp_mail->created;
		$date  = ( '' == $d ) ? mysql2date( get_option('date_format'), $x) : mysql2date($d, $x);
		return apply_filters('get_comment_date', $date, $d);
	}

////	ADMIN mail ajax 	////

	function mp_action_delete_mail() {
		$id = isset($_POST['id'])? (int) $_POST['id'] : 0;
		die( MP_Mail::delete( $id ) ? '1' : '0' );
	}

	function mp_action_send_mail() {
		$id 		= isset($_POST['id']) 	? (int) $_POST['id'] : 0;
		$url_parms 	= MP_Admin::get_url_parms();
		$x 		= MP_Mail::send_draft($id);
		$url 		= (is_numeric($x))	? $list_url . '&sent=' . $x : $list_url . '&notsent=1';
		if (is_numeric($x))
		{
			if (0 == $x)
			{
				die(-1);
			}
			else
			{
				ob_end_clean();
				ob_start();
					MP_Mail::get_row( $id, $url_parms, false );
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
				MP_Mail::get_row( $id, $url_parms, false, __('no recipient','MailPress') );
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
				MP_Mail::get_row( $mail->id, $url_parms, false );
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

// draft

	function send_draft($id=NULL) {
		global $wpdb;

		if (NULL == $id) return false;
		$draft = MP_Mail::get_mail($id);
		if ('draft' != $draft->status) return false;

		$mail->id = $draft->id;

		$count = 0;
		switch ($draft->toemail)
		{
			case '1' :
				$query  = "SELECT id, email, status, confkey FROM $wpdb->mp_users WHERE status = 'active';";
			break;
			case '2' :
				$query  = "SELECT DISTINCT id, email, status, confkey FROM $wpdb->mp_users a, $wpdb->postmeta b WHERE a.id = b.meta_value and b.meta_key = '_MailPress_subscribe_to_comments_';";
			break;
			case '3' :
				$query  = "SELECT id, email, status, confkey FROM $wpdb->mp_users WHERE status = 'active'";
				$query .= " UNION ";
				$query .= "SELECT DISTINCT id, email, status, confkey FROM $wpdb->mp_users a, $wpdb->postmeta b WHERE a.id = b.meta_value and a.status = 'waiting' and b.meta_key = '_MailPress_subscribe_to_comments_';";
			break;
			case '4' :
				$query  = "SELECT id, email, status, confkey FROM $wpdb->mp_users ;";
			break;
			default :
				if 	(!MailPress::is_email($draft->toemail)) return 'y';
				$mail->toemail 		= $draft->toemail;
				$mail->toname		= $draft->toname;
				$key 				= MP_User::get_key_by_email($draft->toemail);
				if ($key)
				{
					$mail->subscribe 		= MP_User::get_subscribe_url($key);
					$mail->unsubscribe 	= MP_User::get_unsubscribe_url($key);
					$mail->viewhtml	 	= MP_User::get_view_url($key,$draft->id);
				}
				$count = 1;
			break;
		}
		if (isset($query)) 
		{
			$mail->replacements = MP_User::get_recipients($query,$mail->id);
			$mail->unsubscribe  = '{{unsubscribe}}';
			$mail->viewhtml	  = '{{viewhtml}}';
			$count = count($mail->replacements);
		}

// Set mail's subject and body
		$mail->subject	= stripslashes($draft->subject);
		$mail->html		= stripslashes($draft->html);
		$mail->plaintext	= stripslashes($draft->plaintext);

		if (0 == $count)				return 'x';				// no recipient

		if (MailPress::mail($mail))		return $count;			// ok
	
		return 0;										// ko
	}

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

	function update_draft($id)
	{
		global $wpdb;
		$now	  	= date('Y-m-d H:i:s');
		if ('' != $_POST['to_list'])
		{
			$_POST['toemail'] = $_POST['to_list'];
			$_POST['toname']  = '';
		}
		$query = "UPDATE $wpdb->mp_mails SET status = 'draft', theme = '', toemail = '" . trim($_POST['toemail']) . "', toname = '" . trim($_POST['toname']) . "', subject = '" . trim($_POST['subject']) . "', html = '" . trim($_POST['html']) . "', plaintext = '" . trim($_POST['plaintext']) . "', created = '$now', created_user_id = " . MailPress::get_wp_user_id() . " WHERE id = $id ;";
		return $wpdb->query( $query );
	}

	function delete($id)
	{
		global $wpdb;
		$query = "DELETE FROM $wpdb->mp_mails WHERE id = $id ; ";
		return $wpdb->query( $query );
	}

	function display_toemail($toemail,$toname,$tolist='')
	{
		$return = '';
		$draft_dest = array( '0' => '', '1' => __('active blog','MailPress'), '2' => __('active comments','MailPress'), '3' => __('active blog + comments','MailPress'), '4' => __('active + not active','MailPress')	);

		if 		(is_numeric($tolist))			return "<b>" . $draft_dest[$tolist] . "</b>"; 
		elseif 	(is_numeric($toemail))			return "<b>" . $draft_dest[$toemail] . "</b>"; 
		elseif 	(MailPress::is_email($toemail))	return "$toemail &nbsp;&nbsp;&nbsp;&nbsp;&lt;$toname&gt;"; 
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
			else							return false;
		}
	}
}
?>