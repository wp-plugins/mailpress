<?php
// for ajax admin

add_action('mp_action_dim-mail',		array('MP_Mail','mp_action_send_mail'));
add_action('mp_action_delete-mail',		array('MP_Mail','mp_action_delete_mail'));
add_action('mp_action_add-mail',		array('MP_Mail','mp_action_add_mail'));

// for ajax mail-new

add_action('mp_action_swfu_mail_attachement',array('MP_Mail','swfu_mail_attachement'));
add_action('mp_action_html_mail_attachement',array('MP_Mail','html_mail_attachement'));
add_action('mp_action_delete_attachement',array('MP_Mail','mp_action_delete_attachement'));
add_action('mp_action_autosave',		array('MP_Mail','mp_action_autosave'));
add_action('mp_action_get-previewlink',	array('MP_Mail','mp_action_get_previewlink'));
add_action('mp_action_iview',          	array('MP_Mail','mp_action_iview'));

// to view mail
add_action('mp_action_viewadmin',		array('MP_Mail','mp_action_viewadmin'));
add_action('mp_action_view',			array('MP_Mail','mp_action_viewuser'));

// for connection 
add_filter('MailPress_Swift_Connection_SMTP',array('MP_Mail','SMTP_connect'),8,2);	

// for recipients meta
add_filter('MailPress_get_recipients',	array('MP_Mail','get_meta_recipients'),10,3);

class MP_Mail
{

	function MP_Mail( $plug = MP_FOLDER )
	{
		$this->plug = $plug;
		$this->theme = new MP_Themes();
		$this->to = $this->from = $this->message = null;
	}

////	MP_Mail send functions	////

	function send($args)
	{
		$this->args = (is_numeric($args)) ? $this->get($args) : $args ;

		return $this->end( $this->start() );
	}

	function start()
	{
		if (function_exists('ignore_user_abort')) 	ignore_user_abort(1);
		if (function_exists('set_time_limit')) 		if( !ini_get('safe_mode') ) set_time_limit(0);

		$this->row = $this->mail = (object) '';

// MP_Log
		$f = (isset($this->args->forcelog)) ? true : false; 
		$this->trace = new MP_Log('MP_Mail',ABSPATH . MP_PATH,$this->plug,$f);


		if (!$this->args)	
		{
/*trace*/		$this->trace->log(__('*** ERROR *** Sorry invalid arguments in MP_Mail::send','MailPress'));
			return array(false,false);
		}

// general settings
		global $mp_general;
		$mp_general = get_option('MailPress_general');

// defaults
		$rc = true;
		$this->row->status = 'sent';

// charset
		$this->row->charset = (isset($this->args->charset)) ? $this->args->charset : get_option('blog_charset');

// fromemail & fromname
		$this->row->fromemail 	= (empty($this->args->fromemail)) ? $mp_general['fromemail'] : $this->args->fromemail;
		$this->row->fromname 	= (empty($this->args->fromname))  ? stripslashes($mp_general['fromname'])  : $this->args->fromname;

// toemail & toname
		if (isset($this->args->replacements) && (0 != count($this->args->replacements)))
		{
			$this->row->toemail = $this->args->replacements;
			$this->row->toname  = '';
			$this->batch = true;
		}
		elseif ( (isset($this->args->toemail)) && (!empty($this->args->toemail)) )
		{
			$this->row->toemail = $this->args->toemail;
			$this->row->toname  = (empty($this->args->toname))  ? $this->args->toemail : $this->args->toname;
			$this->batch = false;
		}
		else
		{
// no recipients ?
			$this->trace->log(__('*** ERROR *** No recipient','MailPress'));	
			return array(false,false);
		}

// subject
		$this->row->subject = (isset($this->args->subject)) ? $this->args->subject : false;
		$pt = $this->theme->get_page_templates_from((isset($this->args->Theme)) ? $this->args->Theme : $this->theme->themes[$this->theme->current_theme]['Template']);
		if (isset($this->args->Template)) if (isset($pt[$this->args->Template][1]) ) $this->row->subject = $pt[$this->args->Template][1];
		$this->row->subject = ($this->row->subject) ? trim($this->mail_decorator($this->do_eval($this->row->subject),get_object_vars($this->args))) : '';
		$this->row->subject = html_entity_decode($this->row->subject,ENT_QUOTES,get_option('blog_charset'));
// plaintext
		$this->row->plaintext   = trim(strip_tags($this->build_mail_content('plaintext')));
		$this->row->plaintext   = html_entity_decode($this->row->plaintext,ENT_QUOTES,get_option('blog_charset'));
// html
		$this->row->html        = trim($this->build_mail_content('html',(isset($this->args->draft)) ? true : false));
		$this->row->html        = ( '<br />' == $this->row->html ) ? '' : $this->row->html;

		$this->row->theme		= $this->mail->theme;
		$this->row->themedir	= $this->mail->themedir;
		$this->row->template 	= $this->mail->template;

// mail empty ?
		if (!$this->row->subject && !$this->row->plaintext && !$this->row->html)
		{
/*trace*/		$this->trace->log(__('*** WARNING *** Mail is empty','MailPress'));
			return array(false,false);
		}


/*trace*/	$x  = " \n\n ------------------- start of mail -------------------  ";
/*trace*/	$x .= " \n From : " . $this->row->fromname . " <" . $this->row->fromemail . "> ";
/*trace*/	$x .= $this->row->fromname;
/*trace*/	if (!isset($this->args->replacements))
/*trace*/	$x .= " \n To   : " . $this->row->toname . " <" . $this->row->toemail . "> ";
/*trace*/	$x .= " \n Subject : "                                               . $this->row->subject;
/*trace*/	if ($this->row->plaintext) 	$x .= " \n   ------------------- plaintext -------------------  \n " . $this->row->plaintext;
/*trace*/	if ($this->row->html) 		$x .= " \n\n ------------------- text/html -------------------  \n " . $this->row->html;
/*trace*/	$x .= " \n\n ------------------ end of mail ------------------  \n\n";
/*trace*/	$this->trace->log($x,$this->trace->levels[512]);

		if (isset($this->args->id)) $this->row->id = $this->args->id;
   		if (!isset($this->args->main_id)) $this->args->main_id = 0;



		if (apply_filters('MailPress_tracking',false) && is_array($this->row->toemail) && isset($this->row->id))
		{
			$tracking = MailPress_tracking::prepare($this->row->id, $this->row->toemail, $this->row->plaintext, $this->row->html);
			$this->row->toemail	= $tracking['toemail'];
			$this->row->html		= $tracking['html'];
			$this->row->plaintext	= $tracking['plaintext'];
		}



		$batchprocessing = false;
// no mail ?
		if (isset($this->args->nomail))
		{
/*trace*/		$this->trace->log(__(':::: Mail not sent as required ::::','MailPress'));
		}
		elseif (apply_filters('MailPress_batch_send_status',false) && $this->batch && (count($this->row->toemail) > 1))
		{
			$batchprocessing = true;
			$this->row->status = (apply_filters('MailPress_batch_send_status','sent'));
/*trace*/		$this->trace->log(':::: Mail batch processing as required ::::');
		}
		else
		{
			if (isset($this->args->mp_confkey) && isset($this->row->id))
			{
				$rep0 = apply_filters('MailPress_get_recipients',array(),MP_User::get_id($this->args->mp_confkey),$this->args->main_id);
				foreach($rep0 as $k => $v) $rep1[substr($k,2,strlen($k) - 4)] = $v;
				$this->row->html = $this->mail_decorator($this->row->html,$rep1);
				$this->row->plaintext = $this->mail_decorator($this->row->plaintext,$rep1);

				if (apply_filters('MailPress_tracking',false))
				{
					MP_Usermeta::add(MP_User::get_id($this->args->mp_confkey), '_MailPress_mail_sent', $this->row->id);
					$tracking = MailPress_tracking::prepare($this->row->id, array($this->row->toemail => array('mp_confkey' => $this->args->mp_confkey)), $this->row->plaintext, $this->row->html, '', '');
					$this->row->html 		= $this->mail_decorator($tracking['html'],$tracking['toemail'][$this->row->toemail]);
					$this->row->plaintext 	= $this->mail_decorator($tracking['plaintext'],$tracking['toemail'][$this->row->toemail]);
				}
			}

			$rc = $this->swift_processing();
		}

/** MAIL SAVE **/

		if (!isset($this->args->noarchive) && $rc) 
		{ 
			global $wpdb;

			if (!isset($this->args->nostats)) MailPress::update_stats('t',isset($this->args->Template) ? $this->args->Template : '',(isset($this->args->replacements)) ? count($this->args->replacements) : 1);

			$now	  	= date('Y-m-d H:i:s');
			$user_id 	= MailPress::get_wp_user_id();

			if ($this->batch) $this->row->toemail = mysql_real_escape_string(serialize($this->row->toemail));

			$this->row->subject 	= mysql_real_escape_string(trim($this->row->subject));
			$this->row->html		= mysql_real_escape_string(trim($this->row->html));
			$this->row->plaintext	= mysql_real_escape_string($this->row->plaintext);
			$this->row->fromname	= mysql_real_escape_string($this->row->fromname);
			$this->row->toname	= mysql_real_escape_string($this->row->toname);

			$this->row->created 		= $now;
			$this->row->created_user_id 	= $user_id;
			$this->row->sent 			= $now;
			$this->row->sent_user_id 	= $user_id;

			if (isset($this->row->id)) 
			{
				wp_cache_delete($this->row->id,'mp_mail');
				$query = "UPDATE $wpdb->mp_mails SET status = '" . $this->row->status . "', theme = '" . $this->row->theme . "', themedir = '" . $this->row->themedir . "', template = '" . $this->row->template . "', fromemail = '" . $this->row->fromemail . "', fromname = '" . $this->row->fromname . "', toemail = '" . $this->row->toemail . "', toname = '" . $this->row->toname . "',  charset = '" . $this->row->charset . "', subject = '" . $this->row->subject . "', html = '" . $this->row->html . "', plaintext = '" . $this->row->plaintext . "', sent = '" . $this->row->sent . "', sent_user_id = " . $this->row->sent_user_id . " WHERE id = " . $this->row->id . " ;";
			}
			else
			{
				$query = "INSERT INTO $wpdb->mp_mails (status, theme, themedir, template, fromemail, fromname, toemail, toname, charset, subject, html, plaintext, created, created_user_id, sent, sent_user_id ) values ('" . $this->row->status . "', '" . $this->row->theme . "', '" . $this->row->themedir . "', '" . $this->row->template . "', '" . $this->row->fromemail . "', '" . $this->row->fromname . "', '" . $this->row->toemail . "', '" . $this->row->toname . "', '" . $this->row->charset . "', '" . $this->row->subject . "', '" . $this->row->html . "', '" . $this->row->plaintext . "', '" . $this->row->created . "', " . $this->row->created_user_id . ", '" . $this->row->sent . "', " . $this->row->sent_user_id . " );";
			}

			$x = $wpdb->query( $query );

			if (!$x)
/*trace*/			$this->trace->log(sprintf(__('*** ERROR *** Database error, Mail not saved : %1$s','MailPress'), $wpdb->last_error));
			elseif ($batchprocessing)
			{
/*trace*/			$this->trace->log(':::: MAIL SAVED for batch processing ::::');
				do_action('MailPress_batch_send_schedule');
			}
			else
/*trace*/			$this->trace->log(__('::: MAIL SAVED :::','MailPress'));

			return ($x) ? true : false ;
		}
		else
		{
			if (isset($this->args->noarchive) && (isset($this->args->id))) self::delete($this->args->id);
		}
		return $rc;
	}

	function end($rc)
	{
		$this->trace->end($rc);
		return $rc;
	}

	function do_eval($x)
	{
		ob_start();
			echo(eval('global $posts, $post, $wp_did_header, $wp_did_template_redirect, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID; ?>' . $x));
			$x = ob_get_contents();
		ob_end_clean();
		return $x;
	}

	function build_mail()
	{
// from
		$this->from = new Swift_Address($this->row->fromemail, $this->row->fromname);
// to 
		if (!$this->batch) $this->to = new Swift_Address($this->row->toemail, 	$this->row->toname);
		else
		{
			$this->to = new Swift_RecipientList();
			foreach ($this->row->toemail as $dest)	$this->to->addTo($dest['{{toemail}}'],  self::display_name(stripslashes($dest['{{toemail}}'])));
		}
// subject 
		$this->message 	 = new Swift_Message($this->row->subject);
		$this->message->headers->setLanguage(substr(WPLANG,0,2));
		$this->message->headers->setCharset($this->row->charset);
// plaintext 
		if ($this->row->plaintext)
		{
			$plaintxt 	  = new Swift_Message_Part($this->row->plaintext);
			$plaintxt->setCharset($this->row->charset);
			$this->message->attach($plaintxt);
		}
// html
		if ($this->row->html)
		{
			$htmltxt 	  = new Swift_Message_Part( $this->process_img($this->row->html,$this->row->themedir ) , 'text/html');
			$htmltxt->setCharset($this->row->charset);
			$this->message->attach($htmltxt);
			$this->row->html = $this->process_img($this->row->html,$this->row->themedir,'draft');
		}
// attachements
		if (isset($this->row->id))
		{
			$metas = MP_Mailmeta::has( $this->row->id, '_MailPress_attached_file');
			if ($metas)
			{
				foreach($metas as $meta)
				{
					$meta_value = unserialize( $meta['meta_value'] );
					if (!is_file($meta_value['file_fullpath'])) continue;
					$attachement = new Swift_Message_Attachment(new Swift_File($meta_value['file_fullpath']), $meta_value['name'], $meta_value['mime_type']);
					$this->message->attach($attachement);
				}        
			}
		}
	}

	function build_mail_content($type,$filter=false)
	{
		$current_theme = $this->theme->current_theme;

		$this->mail->theme = $this->mail->themedir = $this->mail->template = '';

		$this->build = (object) '';
		$this->build->plaintext = ('plaintext' == $type) ? true : false;
		$this->build->filter = $filter;

		$content = '';
		$content_default = '<?php $this->get_header(); $this->the_content(); $this->get_footer(); ?>';
		$template_file   = 'default.php';

// find the theme and themedir

		$this->mail->theme 	= $this->theme->themes[$this->theme->current_theme]['Template'];
		$this->mail->themedir  	= $this->theme->themes[$this->theme->current_theme]['Template Dir'];
		if (isset($this->args->Theme))
		{
			$x  = $this->theme->get_theme_by_template($this->args->Theme);
			if (!empty($x))
			{
				$this->theme->current_theme 	= $x['Name'];
				$this->mail->theme		= $this->args->Theme;
				$this->mail->themedir		= $x['Template Dir'];
			}
			else
/*trace*/			if (isset($this->trace)) $this->trace->log(sprintf('Missing theme : %1$s. You should create this theme with the appropriate files (header, footer, ...) & templates.', $this->args->Theme),$this->trace->levels[512]);
		}

// find the templates

		$pt 			= ($this->build->plaintext) ? $this->theme->get_page_plaintext_templates_from($this->mail->theme) : $this->theme->get_page_templates_from($this->mail->theme);
		$this->build->dir = ($this->build->plaintext) ? ABSPATH . $this->theme->themes[$this->theme->current_theme] ['Plaintext Template Dir'] :  ABSPATH . $this->theme->themes[$this->theme->current_theme] ['Template Dir'];

// find the template

		if (isset($this->args->Template)) 
		{		
			if (isset($pt[$this->args->Template]))
			{
				$template_file = $pt[$this->args->Template][0];
				$this->mail->template = $this->args->Template;
			}
			else
/*trace*/			if (isset($this->trace) && (!$this->build->plaintext)) $this->trace->log(sprintf('Missing template file %1$s. You should create the template in the appropriate folder %2$s .', $this->args->Template, $this->build->dir),$this->trace->levels[512]);
		}

		$fname = $this->build->dir . '/' . $template_file;
		if (is_file($fname))	$content = file_get_contents($fname);
		else
		{
			if ($this->build->plaintext)
			{
				$this->theme->current_theme = 'plaintext';
				$x  = $this->theme->get_theme_by_template('plaintext');
				if (empty($x))
				{
/*trace*/				if (isset($this->trace)) $this->trace->log('Missing theme : plaintext theme has been deleted.',$this->trace->levels[512]);
					$this->theme->current_theme = $current_theme;
					return '';
				}
				$template_file = 'default.php';
				$pt = $this->theme->get_page_templates_from('plaintext');
				$this->build->dir = ABSPATH . $x['Template Dir'];
				if (isset($this->args->Template)) 
				{		
					if (isset($pt[$this->args->Template]))
					{
						$template_file = $pt[$this->args->Template][0];
						$this->mail->template = $this->args->Template;
					}
				}
				$fname = $this->build->dir . '/' . $template_file;
				$content = (is_file($fname)) ? file_get_contents($fname) : $content_default;
			}
			else
			{
				$content = $content_default;
			}
		}

		if (empty($content)) 
		{
			$this->theme->current_theme = $current_theme;
			return '';
		}
		
// functions.php

		$fname = $this->build->dir . '/' . 'functions.php';
		if (is_file($fname)) $content = "<?php require_once ('$fname'); ?>" . $content;

		$x = $this->do_eval($content);

		$this->theme->current_theme = $current_theme;

		return $this->mail_decorator($x,get_object_vars($this->args));
	}

	function the_content() 
	{
		$content = '';

		if ($this->build->plaintext)
		{
			if (isset($this->args->content)   && !empty($this->args->content)) 	$content = $this->args->content;
			if (isset($this->args->plaintext) && !empty($this->args->plaintext))	$content = $this->args->plaintext;
			$content = $this->do_eval($content);
		}
		else
		{
			if (isset($this->args->content) && !empty($this->args->content)) 		$content = $this->args->content;
			if (isset($this->args->html)    && !empty($this->args->html))		$content = $this->args->html;
			if ($this->build->filter) 								$content = apply_filters('the_content', $this->do_eval($content));
		}
		echo $content;
	}
	
	function get_header() 
	{
		if ( file_exists( $this->build->dir . '/header.php') )
		{
		      $this->load_template( $this->build->dir . '/header.php');
			return;
		}
		if ($this->build->plaintext)
		{
      		$this->load_template( ABSPATH . MP_PATH . 'mp-content/themes/plaintext/header.php');
			return;
		}

		$this->load_template( ABSPATH . MP_PATH . 'mp-content/themes/default/header.php');
	}

	function get_footer() 
	{
		if ( file_exists( $this->build->dir . '/footer.php') )
		{
		      $this->load_template( $this->build->dir . '/footer.php');
			return;
		}
		if ($this->build->plaintext)
		{
      		$this->load_template( ABSPATH . MP_PATH . 'mp-content/themes/plaintext/footer.php');
			return;
		}

		$this->load_template( ABSPATH . MP_PATH . 'mp-content/themes/default/footer.php');
	}

	function get_sidebar( $name = null )
	{
		if ( file_exists( $this->build->dir . "/sidebar-{$name}.php") )
		{
		      $this->load_template( $this->build->dir . "/sidebar-{$name}.php");
			return;
		}
		if ( file_exists( $this->build->dir . '/sidebar.php') )
		{
		      $this->load_template( $this->build->dir . '/sidebar.php');
			return;
		}
		if ($this->build->plaintext)
		{
      		$this->load_template( ABSPATH . MP_PATH . 'mp-content/themes/plaintext/sidebar.php');
			return;
		}

		$this->load_template( ABSPATH . MP_PATH . 'mp-content/themes/default/sidebar.php');
	}

	function get_stylesheet() 
	{
		if ( file_exists( $this->build->dir . '/style.css') )
		{
			echo "<style type='text/css' media='all'>\n";
			$this->load_template( $this->build->dir .  '/style.css');
			echo "</style>\n";
			return;
		}
		if ($this->build->plaintext)
		{
			return;
		}

		echo "<style type='text/css' media='all'>\n";
		$this->load_template( ABSPATH . MP_PATH . 'mp-content/themes/default/style.css');
		echo "</style>\n";
	}

	function load_template($_template_file) 
	{
		global $posts, $post, $wp_did_header, $wp_did_template_redirect, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;
	
		if ( is_array($wp_query->query_vars) )  extract($wp_query->query_vars, EXTR_SKIP);

		include($_template_file);
	}

	function mail_decorator($text,$x='',$sepb='{{',$sepa='}}',$before='',$first=0)
	{
		if (empty($x)) $x = array();

		foreach($x as $key => $value)
		{
			if (is_object($value) || is_array($value))
			{
				$abefore= (0 == $first) 	? $key 				: $before . '[' . $key . ']'; 
				$a = (is_object($value)) ? get_object_vars($value) : $value;
				$text = $this->mail_decorator($text, $a , $sepb , $sepa , $abefore , $first + 1 );
			}
			else 
			{
				$x 		= (0 == $first)		? $key 				: $before . '[' . $key . ']'; 
				$x = $sepb . $x . $sepa;
				$text = str_replace($x,$value,$text,$c);
/*trace*/			if (isset($this->trace))
	                    if (0 < $c ) 	$this->trace->log('Mail decorator : ' . $first . ' ' . $x . ' => ' . $value . " *** DONE ($c) *** ");
			}
		}
		return $text;	
	}

	function process_img($html,$path,$dest='mail')
	{
		$x		= $matches = $imgtags = array();
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
/*trace*/           		if (isset($this->trace)) if ('mail' == $dest) $this->trace->log('Image found : ' . $file,$this->trace->levels[512]);
				}
				if ($f) break;
				
			}
/*trace*/ //if (isset($this->trace)) if (('mail' == $dest) && (!$f)) $this->trace->log('Image NOT found : ' . $match[1],$this->trace->levels[512]);
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
/*trace*/           		if (isset($this->trace)) $this->trace->log('**** SWIFT ERROR ****' . "There was a problem with this image: $file \n" . $e->getMessage());
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
			$match[3]  = (isset($imgs[$match[1]])) ? str_replace($match[1], $imgs[$match[1]], $match[0]) : $match[0]; // and we retrieve it now with the proper <img ... />
			if ('html' != $dest) $match[3]  = apply_filters('MailPress_process_img',$match[3]); // apply_filters for 'mail','draft'
			$html      = str_replace($match[0], $match[3], $html);
		}

	return $html;
	}

////	MP_Mail SWIFT functions	////

	function swift_processing()
	{

/** SWIFT MESSAGE **/
		try 
		{
			$this->build_mail();
		}
		catch (Swift_Message_MimeException $e) 
		{
/*trace*/		$this->trace->log('**** SWIFT ERROR ****' . "There was an unexpected problem building the mail:\n" . $e->getMessage());	
			return false;
		}

/** SWIFT CONNECTION **/
		try 
		{
			$Swift_Connection_type = apply_filters('MailPress_Swift_Connection_type','SMTP');

			$conn = apply_filters('MailPress_Swift_Connection_' . $Swift_Connection_type , null, $this->trace );

			$this->swift = new Swift($conn);
		}
		catch (Swift_ConnectionException $e) 
		{
/*trace*/		$this->trace->log('**** SWIFT ERROR ****' . "There was a problem connecting with $Swift_Connection_type :\n" . $e->getMessage());	
			return false;
		} 

		Swift_CacheFactory::setClassName('Swift_Cache_Disk');
		Swift_Cache_Disk::setSavePath(MP_TMP . "/tmp");

/** SWIFT SEND **/
		try 
		{
			switch (true)
			{
				case (true  === $this->batch) :
					require_once MP_TMP . '/mp-includes/class/swift/Swift/Plugin/Decorator.php';
					$this->swift->attachPlugin(new Swift_Plugin_Decorator($this->row->toemail), 'decorator');

					if ($this->swift->batchSend($this->message, $this->to , $this->from)) break;

					$this->swift->disconnect(); 
					return false;
				break;
				case (false === $this->batch) :
					if (!$this->swift->send($this->message, $this->to , $this->from)) {$this->swift->disconnect(); return false;}
					$this->swift->disconnect();
				break;
				default :
					return apply_filters('MailPress_swift_send',true,$this);
				break;
			}
		}
		catch (Swift_ConnectionException $e) 
		{
/*trace*/		$this->trace->log('**** SWIFT ERROR ****' . "There was a problem sending with $Swift_Connection_type :\n" . $e->getMessage());	
			return false;
		}
		return true;
	}

////	MP_Mail SMTP functions	////

	public static function SMTP_connect($x,$y)
	{
		require_once MP_TMP . '/mp-includes/class/swift/Swift/Connection/SMTP.php';

		$smtp_settings = get_option('MailPress_smtp_config');

		$enc 		= null;
		switch (true)
		{
			case ('ssl' == $smtp_settings ['ssl']) :
				$enc = Swift_Connection_SMTP::ENC_SSL;
			break;
			case ('tls' == $smtp_settings ['ssl']) :
				$enc = Swift_Connection_SMTP::ENC_TLS;
			break;
		}
		$conn = new Swift_Connection_SMTP($smtp_settings ['server'], $smtp_settings ['port'], $enc);

		if (isset($smtp_settings['smtp-auth']) && (!empty($smtp_settings['smtp-auth'])))
		{
			require_once MP_TMP . '/mp-includes/class/swift/Swift/Authenticator/' . $smtp_settings['smtp-auth'] . '.php';
			switch ($smtp_settings['smtp-auth'])
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
					$conn->attachAuthenticator(new Swift_Authenticator_PopB4Smtp($smtp_settings['pophost']));
				break;
			}
		}

		if (empty($smtp_settings['username']) && empty($smtp_settings['password']))
		{
$y->log("**** Empty user/password for SMTP connection ****");	
		}
		else
		{
			$conn->setUsername($smtp_settings ['username']);
			$conn->setPassword($smtp_settings ['password']);
		}
		return $conn;
	}

/// RECIPIENTS ///

	public static function get_meta_recipients($replacements,$user_id,$mail_main_id) 
	{
		if (!$mail_main_id) return $replacements;

		$metas = MP_Mailmeta::get( $mail_main_id );

		if (!is_array($metas)) $metas = array($metas);

		foreach ($metas as $meta)
		{
			if ($meta->meta_key[0] == '_') 					continue;
			if (isset($replacements['{{' . $meta->meta_key . '}}'] )) 	continue;

			$replacements['{{' . $meta->meta_key . '}}'] = $meta->meta_value;
		}
		return $replacements;
	}


////	MP_Mail mail functions	////
////	MP_Mail mail functions	////
////	MP_Mail mail functions	////

	public static function &get(&$mail, $output = OBJECT) {
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

	public static function delete($id)
	{
		global $wpdb;

		do_action('MailPress_delete_mail',$id);

		MP_Mailmeta::delete( $id );
		$query = "DELETE FROM $wpdb->mp_mails WHERE id = $id ; ";
		return $wpdb->query( $query );
	}


/// DRAFT ///


	public static function get_id($from='inconnu')
	{
		global $wpdb;
		$x 	= md5(uniqid(rand(),1));
		$now	= date('Y-m-d H:i:s');
		$wp_user = MailPress::get_wp_user_id();

		$query = "INSERT INTO $wpdb->mp_mails (status, theme, themedir, template, fromemail, fromname, toemail, toname, subject, html, plaintext, created, created_user_id, sent, sent_user_id ) values ('', '$x', '', '', '', '', '', '', '$from', '', '', '$now', $wp_user, '0000-00-00 00:00:00', 0);";
		$wpdb->query( $query );
		return $wpdb->get_var( "SELECT id FROM $wpdb->mp_mails WHERE theme = '$x' ;" );
	}

	function update_draft($id,$status='draft')
	{
		global $wpdb;

		wp_cache_delete($id,'mp_mail');

// process attachements
		if (isset($_POST['type_of_upload']))
		{
			$attach = '';
			if (isset($_POST['Files'])) foreach ($_POST['Files'] as $k => $v) if (is_numeric($k)) $attach .= (empty($attach)) ? "$k" : ", $k";
			$query = (empty($attach)) ? "SELECT mmeta_id FROM $wpdb->mp_mailmeta WHERE mail_id = $id AND meta_key = '_MailPress_attached_file';" : "SELECT mmeta_id FROM $wpdb->mp_mailmeta WHERE mail_id = $id AND meta_key = '_MailPress_attached_file' AND mmeta_id NOT IN ($attach);";
			$file_exits = $wpdb->get_results($query);
			if ($file_exits) foreach($file_exits as $entry) MP_Mailmeta::delete_by_id( $entry->mmeta_id );
		}
// mail
		$now = isset($_POST['created']) ? $_POST['created'] : date('Y-m-d H:i:s');
		if ('' != $_POST['to_list'])
		{
			$_POST['toemail'] = $_POST['to_list'];
			$_POST['toname']  = '';
		}
		if (isset($_POST['content'])) $_POST['html'] = $_POST['content'];
		$query = "UPDATE $wpdb->mp_mails SET status = '$status', theme = '', toemail = '" . trim($_POST['toemail']) . "', toname = '" . trim($_POST['toname']) . "', subject = '" . trim($_POST['subject']) . "', html = '" . trim($_POST['html']) . "', plaintext = '" . trim($_POST['plaintext']) . "', created = '$now', created_user_id = " . MailPress::get_wp_user_id() . " WHERE id = $id ;";
		return $wpdb->query( $query );
	}

	public static function send_draft($id=NULL,$ajax=false,$_toemail=false,$_toname=false) {

		if (NULL == $id) return false;
		$draft = self::get($id);
		if ('draft' != $draft->status) return false;

		$mail = (object) '';
		$mail->main_id	= $id;
		$mail->id = self::get_id('send_draft');

		$count = 0;
		if ($_toemail) $draft->toemail = $_toemail;
		if ($_toname)  $draft->toname  = $_toname;

		$query = MP_User::get_mailing_lists_query($draft->toemail);

		if ($query)
		{
			$mail->replacements = MP_User::get_recipients($query,$mail->id,$mail->main_id);
			$mail->unsubscribe  = '{{unsubscribe}}';
			$mail->viewhtml	  = '{{viewhtml}}';
			$count = count($mail->replacements);
		}
		else
		{
			if 	(!MailPress::is_email($draft->toemail)) return 'y';
			$mail->toemail 		 = $draft->toemail;
			$mail->toname		 = stripslashes($draft->toname);
			$key 				 = MP_User::get_key_by_email($draft->toemail);
			if ($key)
			{
				$mail->subscribe 	 = MP_User::get_subscribe_url($key);
				$mail->unsubscribe = MP_User::get_unsubscribe_url($key);
				$mail->viewhtml	 = MP_User::get_view_url($key,$mail->id);
				$mail->mp_confkey	 = $key;
			}
			$metas = MP_Usermeta::get(MP_User::get_id_by_email($mail->toemail));
	            if ($metas)
	            {
       			if (!is_array($metas)) $metas = array($metas);
    				foreach ($metas as $meta)
	    			{
	    				if ($meta->meta_key[0] == '_') continue;
	    				$xx = $meta->meta_key;
	    				if (isset($mail->{$xx})) continue;
	    				$mail->{$xx} = $meta->meta_value;
	    			}
	            }
			$count = 1;
		}

// duplicate attachements
		$metas = MP_Mailmeta::has( $mail->main_id, '_MailPress_attached_file');
		if ($metas)
		{
			foreach($metas as $meta)
			{
				$meta_value = unserialize( $meta['meta_value'] );
				if (!is_file($meta_value['file_fullpath'])) continue;
				MP_Mailmeta::add( $mail->id, '_MailPress_attached_file', $meta_value );
			}        
		}

// Set mail's subject and body
		$mail->subject	= stripslashes($draft->subject);
		$mail->html		= stripslashes($draft->html);
		$mail->plaintext	= stripslashes($draft->plaintext);

		$mail->draft 	= true;

		if (isset($_POST['Theme'])) $mail->Theme = $_POST['Theme'];

		if (0 == $count)				return 'x';				// no recipient

		if (MailPress::mail($mail))							// ok
		{
			if ($ajax) 	return array($mail->id);
			else		return $count;
		}
		return 0;										// ko
	}

	public static function check_mail_lock( $id ) {
		global $current_user;

		if ( !$mail = self::get( $id ) )
			return false;

		$lock = MP_Mailmeta::get( $mail->id, '_edit_lock', true );
		$last = MP_Mailmeta::get( $mail->id, '_edit_last', true );

		$time_window = apply_filters('mp_check_mail_lock_window', AUTOSAVE_INTERVAL * 2 );
	
		if ( $lock && $lock > time() - $time_window && $last != $current_user->ID )	return $last;
		return false;
	}

	public static function set_mail_lock( $id ) {
		global $current_user;
		if ( !$mail = self::get( $id ) )		return false;
		if ( !$current_user || !$current_user->ID )	return false;

		$now = time();

		MP_Mailmeta::update( $mail->id, '_edit_lock', $now );
		MP_Mailmeta::update( $mail->id, '_edit_last', $current_user->ID );
	}

/// DISPLAYING E-MAILS & NAMES ///

	public static function display_toemail($toemail,$toname,$tolist='')
	{
		$return = '';
		$draft_dest = MP_User::get_mailing_lists();

		if 		(!empty($tolist)  && isset($draft_dest[$tolist]))	return "<b>" . $draft_dest[$tolist] . "</b>"; 
		elseif 	(!empty($toemail) && isset($draft_dest[$toemail]))	return "<b>" . $draft_dest[$toemail] . "</b>"; 
		elseif 	(MailPress::is_email($toemail))
		{
				return self::display_name_email($toname,$toemail);
		}
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

	public static function display_name_email($name, $email)
	{
		if (empty($name)) return $email;
		return self::display_name($name) . " &lt;$email&gt;";
	}

	public static function display_name($name)
	{
		if (MailPress::is_email($name))				return str_replace('@','(at)',$name); 
		else									return $name;
	}

/// AJAX ///


////	MP_Mail admin list ajax functions	////

	public static function mp_action_delete_mail() {
		$id = isset($_POST['id'])? (int) $_POST['id'] : 0;
		die( self::delete( $id ) ? '1' : '0' );
	}

	public static function mp_action_send_mail() {
		$id 		= isset($_POST['id']) 	? (int) $_POST['id'] : 0;
		$url_parms 	= MP_Admin::get_url_parms();
		$x 		= self::send_draft($id,true);
		if (is_array($x))
		{
			ob_end_clean();
			ob_start();
				self::get_row( $x[0], $url_parms );
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
					self::get_row( $id, $url_parms );
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
				self::get_row( $id, $url_parms, __('no recipient','MailPress') );
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

	public static function mp_action_add_mail() {
		$url_parms = MP_Admin::get_url_parms(array('mode','status','s'));

		$start = isset($_POST['apage']) ? intval($_POST['apage']) * 25 - 1: 24;

		list($mails, $total) = self::get_list( $url_parms, $start, 1 );

		if ( !$mails ) die('1');

		$x = new WP_Ajax_Response();
		foreach ( (array) $mails as $mail ) {
			self::get( $mail );
			ob_start();
				self::get_row( $mail->id, $url_parms );
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

	public static function mp_action_autosave()
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

		$main_id = $_POST['id'];

		if ( -1 == $_POST['revision'])
		{
			$id 	= $mail->id = (int) $_POST['id'];
			if ( $do_autosave ) 
			{
				$id = (0 == $_POST['id']) ? self::get_id('mp_action_autosave1') : $_POST['id'];
				$mail->id = $id;
				self::update_draft($id);
				$data = $message['draft'];
				$supplemental['tipe'] = 'mail';
			}
		}
		else
		{
			$id 	= $mail->id = (int) $_POST['id'];
			$mail = self::get($id);

			if ( $last = self::check_mail_lock( $mail->id ) ) 
			{
				$do_autosave 	= $do_lock = false;
				$last_user 		= get_userdata( $last );
				$last_user_name 	= $last_user ? $last_user->display_name : __( 'Someone' );	
				$data 		= new WP_Error( 'locked', sprintf( __( 'Autosave disabled: %s is currently editing this mail.' ) , wp_specialchars( $last_user_name )	) );
				$supplemental['disable_autosave'] = 'disable';
			}
			if ( $do_autosave ) 
			{
				$id = (0 == $_POST['revision']) ? self::get_id('mp_action_autosave2') : $_POST['revision'];

				if (0 == $_POST['revision'])
				{
					$mailmetas 		= MP_Mailmeta::get( $mail->id ,'_MailPress_mail_revisions');
					$mailmetas[$current_user->ID] = $id;
					MP_Mailmeta::update($mail->id ,'_MailPress_mail_revisions',$mailmetas);
				}

				self::update_draft($id,'');
				$data = $message['revision'];
				$supplemental['tipe'] = 'revision';
			}
			else
			{
				if (0 != $_POST['revision']) $id = $_POST['revision'];
			}
		}

		if ( $do_lock && $id ) self::set_mail_lock( $mail->id );

		$x = new WP_Ajax_Response( array	(
								'what' => 'autosave',
								'id' => $id,
								'old_id' => $main_id,
								'type' => $type,
								'data' => $id ? $data : '',
								'supplemental' => $supplemental
								)
						 );
		$x->send();
	}

	public static function mp_action_get_previewlink()
	{
		$mail_id = isset($_POST['id'])? intval($_POST['id']) : 0;
		$mail_main_id = isset($_POST['main_id'])? intval($_POST['main_id']) : 0;
		$preview_url= clean_url(get_option('siteurl') . '/' . MP_PATH . "mp-includes/action.php?action=iview&id=$mail_id&main_id=$mail_main_id&KeepThis=true&TB_iframe=true");
		die($preview_url);
	}


////	mailpress_mails	////


	public static function manage_list_columns() {
		$disabled = (!current_user_can('MailPress_delete_mails') && !current_user_can('MailPress_send_mails')) ? " disabled='disabled'" : '';
		$mails_columns = array(	'cb' 		=> "<input type='checkbox'$disabled />",
						'title' 	=> __('Subject','MailPress'),
						'author' 	=> __('Author'),
						'theme' 	=> __('Theme','MailPress'),
						'to' 		=> __('To','MailPress'),
						'date'	=> __('Date') );
		$mails_columns = apply_filters('MailPress_manage_mails_columns', $mails_columns);
		return $mails_columns;
	}

	public static function get_list( $url_parms, $start, $num ) {
		global $wpdb;
		global $user_ID;

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
		if (!current_user_can('MailPress_edit_others_mails'))
		{
			if (!empty($where)) $where = $where . ' AND ';
			$where .= "( created_user_id = " . $user_ID . " ) ";
		}
		if ($where) $where = ' WHERE ' . $where;

		$mails = $wpdb->get_results( "SELECT SQL_CALC_FOUND_ROWS * FROM $wpdb->mp_mails  $where ORDER BY created DESC LIMIT $start, $num" );

		MP_Admin::update_cache($mails,'mp_mail');

		$total = $wpdb->get_var( "SELECT FOUND_ROWS()" );

		return array($mails, $total);
	}

	public static function get_row( $id, $url_parms, $xtra=false, $no_actions=false) {

		global $mp_mail;
		global $mp_screen;

		$disabled = (!current_user_can('MailPress_delete_mails') && !current_user_can('MailPress_send_mails')) ? " disabled='disabled'" : '';

		$mp_mail = $mail = self::get( $id );
		$the_mail_status = $mail->status;
// url's
		$view_url		= clean_url(get_option('siteurl') . '/' . MP_PATH . "mp-includes/action.php?action=iview&id=$id&KeepThis=true&TB_iframe=true&width=600&height=400");
		$edit_url    	= clean_url(MP_Admin::url( MailPress_edit . "&id=$id",			false,	$url_parms ));

		$tracking_url = clean_url(MP_Admin::url( MailPress_mail	."&action=tracking&id=$id",	false ,	$url_parms ));

		$actions = array();

		if ('draft' == $mail->status) 
		{ 
			$actions['edit']    = "<a href='$edit_url'   title='" . sprintf( __('Edit "%1$s"','MailPress') , ( '' == $mail->subject) ? __('(no subject)','MailPress') : htmlspecialchars($mail->subject, ENT_QUOTES) ) . "'>"				. __('Edit') . '</a>';
			if (current_user_can('MailPress_send_mails'))
			{
				$send_url 	= clean_url(MP_Admin::url( MailPress_mail	."&action=send&id=$id",		"send-mail_$id",		$url_parms ));
				$actions['send']    = "<a href='$send_url' 	class='dim:the-mail-list:mail-$id:unapproved:e7e7d3:e7e7d3:?mode=" . $url_parms['mode'] . "' title='" . __('Send this mail','MailPress' ) . "'>" 					. __( 'Send','MailPress' ) . '</a>';
			}
			if (current_user_can('MailPress_delete_mails'))
			{
				$delete_url = clean_url(MP_Admin::url( MailPress_mail	."&action=delete&id=$id",	"delete-mail_$id" ,	$url_parms ));
				$actions['delete']  = "<a href='$delete_url' 	class='delete:the-mail-list:mail-$id submitdelete' title='" . __('Delete this mail','MailPress' ) . "'>" 												. __('Delete','MailPress') . '</a>';
			}
			$preview_url = $view_url;
			$actions['view'] = "<a href='$preview_url' class='thickbox'  title='" . __('View','MailPress' ) . "'>"																					. __('View','MailPress') . '</a>';
		}
		elseif (current_user_can('MailPress_delete_mails'))
		{ 
			$delete_url = clean_url(MP_Admin::url( MailPress_mail	."&action=delete&id=$id",	"delete-mail_$id" ,	$url_parms ));
			$actions['delete'] = "<a href='$delete_url' 	class='delete:the-mail-list:mail-$id submitdelete' title='" . __('Delete this mail','MailPress' ) . "'>" 			. __('Delete','MailPress') . '</a>';
			$actions['view'] = "<a href='$view_url' class='thickbox'  title='" . sprintf( __('View "%1$s"','MailPress') , ( '' == $mail->subject) ? __('(no subject)','MailPress') : htmlspecialchars($mail->subject, ENT_QUOTES) ) . "'>"	. __('View','MailPress') . '</a>';
			if (apply_filters('MailPress_tracking',false) && current_user_can('MailPress_tracking_mails'))
				$actions['tracking'] = 	"<a href='$tracking_url' 	title='" . __('See tracking results','MailPress' ) . "'>" . __('Tracking','MailPress') . '</a>';
		}
		else
		{
			$actions['view'] = "<a href='$view_url' class='thickbox'  title='" . sprintf( __('View "%1$s"','MailPress') , ( '' == $mail->subject) ? __('(no subject)','MailPress') : htmlspecialchars($mail->subject, ENT_QUOTES) ) . "'>"	. __('View','MailPress') . '</a>';
			if (apply_filters('MailPress_tracking',false) && current_user_can('MailPress_tracking_mails'))
				$actions['tracking'] = 	"<a href='$tracking_url' 	title='" . __('See tracking results','MailPress' ) . "'>" . __('Tracking','MailPress') . '</a>';
		}

// data
		$class = '';
		if ('draft' == $the_mail_status) $class = 'draft';
		if ('unsent' == $the_mail_status) $class = 'unsent';
//to
		$draft_dest = MP_User::get_mailing_lists();

		$email = false;
		if 	($xtra)										$email_display = "<blink style='color:red;font-weight:bold;'>" . $xtra . '</blink>';
		elseif (is_array(unserialize($mail->toemail)))					$email_display = "<div class='num post-com-count-wrapper'><a class='post-com-count'><span class='comment-count'>" . count(unserialize($mail->toemail)) . "</span></a></div>"; 
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
		elseif (isset($draft_dest[$mail->toemail]))	$email_display = "<strong>" . $draft_dest[$mail->toemail] . "</strong>";
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
//attachements
		$attach = false;
		$metas = MP_Mailmeta::has( $id, '_MailPress_attached_file');
		if ($metas)
		{
			foreach($metas as $meta)
			{
				$meta_value = unserialize( $meta['meta_value'] );
				if ($the_mail_status == 'sent')
				{
					$attach = true;
					break;
				}
				elseif (is_file($meta_value['file_fullpath']))
				{
					$attach = true;
					break;
				}
			}
		}
?>
	<tr id="mail-<?php echo $id; ?>" class='<?php echo $class; ?>'>
<?php

		$mails_columns = self::manage_list_columns();
		$hidden = (array) get_user_option( "manage" . $mp_screen . "columnshidden" );

		foreach ( $mails_columns as $column_name=>$column_display_name ) 
		{
			$class = "class=\"$column_name column-$column_name\"";

			$style = '';
			if ('unsent' == $mail->status) 		$style .= 'font-style:italic;';
			if ( in_array($column_name, $hidden) ) 	$style .= 'display:none;';
			$style = ' style="' . $style . '"';

			$attributes = "$class$style";

			switch ($column_name) 
			{

				case 'cb':
					if (('unsent' == $mail->status) || ($no_actions)) {
?>
		<th class='check-column' scope='row'>
		</th>
<?php 				}
					else 
					{ 
?>
		<th class='check-column' scope='row'>
			<input type='checkbox' name='delete_mails[]' value='<?php echo $id; ?>'<?php echo $disabled; ?> />
		</th>
<?php
	 				} 
				break;
				case 'title':
					$attributes = 'class="post-title column-title"' . $style;
?>
		<td  <?php echo $attributes ?>>
<?php
			if ($attach) :
?>
			<img class='attach' alt="<?php _e('Attachements','MailPress'); ?>" title="<?php _e('Attachements','MailPress'); ?>"  src='<?php echo get_option('siteurl') . '/' . MP_PATH; ?>/mp-includes/images/clip.gif' />
<?php
			endif;
			do_action('MailPress_mails_list_icon',$id);
?>
			<strong>
				<a class='row-title<?php echo ('draft' == $mail->status) ? '' : ' thickbox'; ?>' href='<?php echo ('draft' == $mail->status) ? $edit_url : $view_url; ?>' title='<?php printf( ('draft' == $mail->status) ?  __('Edit "%1$s"','MailPress') : __('View "%1$s"','MailPress') , ( '' == $mail->subject) ? __('(no subject)','MailPress') : htmlspecialchars($mail->subject, ENT_QUOTES) ); ?>'>
					<?php echo ( '' == $subject_display) ? __('(no subject)','MailPress') : $subject_display; ?>
				</a>
<?php if ('draft' == $mail->status) echo ' - ' . apply_filters('MailPress_mails_list_status',__('Draft'),$id); ?>
			</strong>
<?php
					if (!$no_actions)
					{
					if ('unsent' != $mail->status)
					{
						$action_count = count($actions);
						$i = 0;
						echo "			<div class='row-actions'>";
						foreach ( $actions as $action => $link ) {
							++$i;
							( $i == $action_count ) ? $sep = '' : $sep = ' | ';
							echo "				<span class='$action'>$link</span>$sep\n";
						}
						echo "			</div>";
					}
					}
?>
		</td>
<?php
				break;
				case 'date':

					$t_time = self::get_mail_date(__('Y/m/d g:i:s A'));
					$m_time = self::get_mail_date_raw();
					$time   = self::get_mail_date('U');

					$time_diff = time() - $time; 

					if ( $time_diff > 0 && $time_diff < 24*60*60 )	$h_time = sprintf( __('%s ago'), human_time_diff( $time ) );
					elseif ( $time_diff == 0 )				$h_time = __('now','MailPress');
					else								$h_time = mysql2date(__('Y/m/d'), $m_time);
					
					$attributes = 'class="date column-date"' . $style;
?>
		<td  <?php echo $attributes ?>>
			<abbr title="<?php echo $t_time; ?>"><?php echo $h_time; ?></abbr>
		</td>
<?php
				break;
				case 'author':
?>
		<td  <?php echo $attributes ?>>
<?php
					if ($author != 0 && is_numeric($author)) { 
?>
			<a href='<?php echo $author_url; ?>' title='<?php printf( __('Mails by "%1$s"','MailPress'), $wp_user->display_name); ?>'><?php echo $wp_user->display_name; ?></a>
<?php 				} else { 	
			_e("(unknown)",'MailPress');
					}
?>
		</td>
<?php
				break;
				case 'to':
?>
		<td  <?php echo $attributes ?>>
<?php 				if ($email) { ?>
			<strong>
				<a class='row-title' href='<?php echo $mail_url; ?>'  title='<?php printf( __('Search "%1$s"','MailPress'), $mail->toemail); ?>'>
					<?php if ( ('detail' == $url_parms['mode']) && (get_option('show_avatars') ) ) echo get_avatar( $mail->toemail, 32 );?>
					<?php echo $email_display; ?>
				</a>
			</strong>
<?php 				} else { ?>
			<?php echo $email_display; ?>
<?php 				} ?>
		</td>
<?php
				break;
				case 'theme':
?>
		<td  <?php echo $attributes ?>>
			<?php echo $mail->theme; ?>
			<?php if ('' != $mail->template) echo "<br />(" . $mail->template . ")"; ?>
		</td>
<?php
				break;
				default:
?>
		<td  <?php echo $attributes ?>>
			<?php	do_action('MailPress_manage_mails_custom_column', $column_name, $mail, $url_parms); ?>
		</td>
<?php
				break;
			}
		}
?>
	  </tr>
<?php
	}

	public static function mail_date( $d = '' ) {
		echo  self::get_mail_date($d);
	}

	public static function get_mail_date($d = '' ) {
		$x = self::get_mail_date_raw();
		return ( '' == $d ) ? mysql2date( get_option('date_format'), $x) : mysql2date($d, $x);
	}

	public static function get_mail_date_raw() {
		global $mp_mail;
		$x = ($mp_mail->sent >= $mp_mail->created) ? $mp_mail->sent : $mp_mail->created;
		return $x;
	}


/// mailpress_write && mailpress_edit ///


	public static function submit_meta_box($draft) 
	{
		if ($draft)
		{
			if ($draft->id)
			{
				if (current_user_can('MailPress_delete_mails')) $delete_url = clean_url(MailPress_mail  ."&amp;action=delete&amp;id=$draft->id");
				$preview_url= clean_url(get_option('siteurl') . '/' . MP_PATH . "mp-includes/action.php?action=iview&id=$draft->id&KeepThis=true&TB_iframe=true");
				$preview	= "<a class='preview button' target='_blank' href='$preview_url'>" . __('Preview') . "</a>";
			}
		}
?>
<div class="submitbox" id="submitpost">
	<div id="minor-publishing">
		<div id="minor-publishing-actions">
			<input type='submit' name='save' id='save-post' class='button button-highlighted' 	value="<?php _e('Save Draft','MailPress'); ?>"  />
			<span id='previewview27'><?php if ($preview) echo $preview; ?></span>
		</div>
		<div class="clear"><br /><br /><br /><br /><br /></div>
	</div>
	<div id="major-publishing-actions">
		<div id="delete-action">
<?php 	if ($delete_url) : ?>
			<a class='submitdelete' href='<?php echo $delete_url ?>' onclick="if (confirm('<?php echo(js_escape(sprintf( __("You are about to delete this draft '%s'\n  'Cancel' to stop, 'OK' to delete."), $draft->id ))); ?>')) return true; return false;">
				<?php _e('Delete','MailPress'); ?>
			</a>
<?php		endif; ?>
		</div>
		<div id="publishing-action">
<?php 	if (current_user_can('MailPress_send_mails')) : ?><input id='publish' type='submit' name='send' class='button-primary' value="<?php _e('Send','MailPress'); ?>" /><?php endif; ?>
		</div>
		<div class="clear"></div>
	</div>
</div>
<?php
	}






	public static function attachements_meta_box($draft) 
	{
		if ($draft) $draft_id = (isset($draft->id)) ? $draft->id : 0;
		if (self::flash()) 
		{
			$divid = 'flash-upload-ui';
			$divs  = "<div><div id='flash-browse-button'></div></div>";
			$url   = clean_url(add_query_arg('flash',0));
			$txt   = __('homemade uploader','MailPress');
		}
		else
		{
			$divid = 'html-upload-ui';
			$divs  = "<div class='mp_fileupload_txt'><span class='mp_fileupload_txt'></span></div><div class='mp_fileupload_file' id='mp_fileupload_file_div'></div>";
			$url   = clean_url(remove_query_arg('flash'));
			$txt   = __('Flash uploader','MailPress');
		}
?>
<script type="text/javascript">
<!--
var draft_id = <?php echo $draft_id; ?>;
//-->
</script>
<div id="attachement-items">
<?php 	self::get_attachements_list($draft_id); ?>
</div>
<div><span id='attachement-errors'></span></div>

<div id='<?php echo $divid; ?>'><?php echo $divs; ?>
	<br class="clear" />
	<p>
		<input type='hidden' name='type_of_upload' value="<?php echo $divid; ?>" />
		<?php printf(__('Problems?  Try the %s.','MailPress'), sprintf ("<a id='mp_loader_link' href='%1s'>%2s</a>", $url ,$txt )); ?>
	</p>
</div>
<?php
	}

	public static function flash() 
	{
		// If Mac and mod_security, no Flash. :(
		$flash = (isset($_GET['flash'])) ? $_GET['flash'] : true;
		$flash = ( false !== strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'mac') && apache_mod_loaded('mod_security') ) ? false : $flash;
		return $flash;
	}

// swfupload
	public static function swfupload() 
	{
		$m = array('mp_swfupload' => array(
				'flash_url' 			=> includes_url('js/swfupload/swfupload.swf'),

				'button_text' 			=> "<span class='mp_button'>" .  js_escape(__('Attach a file','MailPress')) . "</span>",
				'button_text_style' 		=> '.mp_button { text-align: left; color: #21759B; text-decoration: underline; font-family:"Lucida Grande","Lucida Sans Unicode",Tahoma,Verdana,sans-serif; } .mp_button:hover {cursor:pointer;}',
				'another_button_text'		=> "<span class='mp_button'>" .  js_escape(__('Attach another file','MailPress')) . "</span>",


				'button_height'			=> '24',
				'button_width'			=> '132',
				'button_image_url'		=> get_option('siteurl') . '/' . MP_PATH . 'mp-admin/images/upload.png',
				'button_placeholder_id'		=> 'flash-browse-button',

				'upload_url' 			=> get_option('siteurl') . '/' . MP_PATH . 'mp-includes/action.php',

				'file_post_name'			=> 'async-upload',
				'file_types'			=> '*.*',
				'file_size_limit'			=> wp_max_upload_size() . 'b',

				'post_params'			=> array (
										'action'		=> 'swfu_mail_attachement',
										'auth_cookie'	=> (is_ssl()) ? $_COOKIE[SECURE_AUTH_COOKIE] : $_COOKIE[AUTH_COOKIE],
										'_wpnonce'		=> wp_create_nonce('mp_attachement')
									),

				'custom_settings'			=> array (
										'degraded_element_id' => 'html-upload-ui', // id of the element displayed when swfupload is unavailable
										'swfupload_element_id'=> 'flash-upload-ui' // id of the element displayed when swfupload is available
									),

				'debug'				=> false
			));
		echo "<script type='text/javascript'>\n/* <![CDATA[ */\n";
		$eol = "";
		foreach ( $m as $var => $val ) {
			echo "var $var = " . MP_Admin::print_scripts_l10n_val($val);
			$eol = ",\n\t\t";
		}
		echo ";\n";
		echo "/* ]]> */\n</script>";
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

		die(0);
	}

// html upload
	public static function html_mail_attachement() 
	{
		$draft_id = $_REQUEST['draft_id'];
		$id       = $_REQUEST['id'];
		$file     = $_REQUEST['file'];

		$xml = self::mail_attachement();

		ob_end_clean();
		$xml = str_replace('>','&gt;',$xml);
		$xml = str_replace('<','&lt;',$xml);
		include MP_TMP . '/mp-includes/upload-iframe-xml.html.php';

		die(0);
	}

// standard upload functions
	public static function mail_attachement()
	{
		$data = self::handle_upload('async-upload', $_REQUEST['draft_id']);

		$xml = "<?xml version='1.0' standalone='yes'?><mp_fileupload>";

		if (is_wp_error($data)) 
		{
			$xml .= "<error><![CDATA[" . $data->get_error_message() . "]]></error>";
		}
		else
		{
			$xml .= "<id><![CDATA[" . $data['id'] . "]]></id>";
			$xml .= "<url><![CDATA[" . $data['url'] . "]]></url>";
			$xml .= "<file><![CDATA[" . $data['file'] . "]]></file>";
		}
		$xml .= '</mp_fileupload>';

		return $xml;
	}

	public static function handle_upload($file_id, $draft_id) 
	{
		$overrides = array('test_form'=>false, 'unique_filename_callback' => 'mp_unique_filename_callback');
		$time = current_time('mysql');

		$file = wp_handle_upload($_FILES[$file_id], $overrides, $time);

		if ( isset($file['error']) )
			return new WP_Error( 'upload_error', $file['error'] );

		$url 		= $file['url'];
		$type 	= $file['type'];
		$file 	= $file['file'];
		$fx = str_replace("\\", "/", $file);

// Construct the attachment array
		$object = array(
						'name' 	=> $_FILES['async-upload']['name'],
						'mime_type'	=> $type,
						'file'	=> '',
						'file_fullpath'	=> $fx,
						'guid' 	=> $url
					);
// Save the data
		$id = self::insert_attachment($object, $file, $draft_id);

		$href = clean_url(get_option('siteurl') . '/' . MP_PATH . "mp-includes/action.php?action=attach_download&id=" . $id);
		return array('id'=>$id, 'url'=>$href, 'file'=>$fx);
	}

	function get_attachements_list($draft_id)
	{
		$metas = MP_Mailmeta::has( $draft_id, '_MailPress_attached_file');
		if ($metas) foreach($metas as $meta) self::get_attachement_row($meta);
	}

	function get_attachement_row($meta)
	{
		$meta_value = unserialize( $meta['meta_value'] );
		if (!is_file($meta_value['file_fullpath'])) return;
		$href = clean_url(get_option('siteurl') . '/' . MP_PATH . "mp-includes/action.php?action=attach_download&id=" . $meta['mmeta_id']);
?>
	<div id='attachement-item-<?php echo $meta['mmeta_id']; ?>' class='attachement-item child-of-<?php echo $draft_id; ?>'>
		<table cellspacing='0'>
			<tr>
				<td>
					<input type='checkbox' class='mp_fileupload_cb' checked='checked' name='Files[<?php echo $meta['mmeta_id']; ?>]' value='<?php echo $meta['mmeta_id']; ?>' />
				</td>
				<td>&nbsp;<a href='<?php echo $href; ?>' style='text-decoration:none;'><?php echo $meta_value['name']; ?></a></td>
			</tr>
		</table>
	</div>

<?php
	}

	function get_attachement_link($meta, $mail_status)
	{
		$meta_value = unserialize( $meta['meta_value'] );
		$href = clean_url(get_option('siteurl') . '/' . MP_PATH . "mp-includes/action.php?action=attach_download&id=" . $meta['mmeta_id']);
		if ($mail_status == 'sent')
		{
			if (is_file($meta_value['file_fullpath']))
			{
				return "<a href='" . $href . "' style='text-decoration:none;'>" . $meta_value['name'] . "</a>";
			}
			else
			{
				return "<span>" . $meta_value['name'] . "</span>";
			}
		}
		else
		{
			if (is_file($meta_value['file_fullpath']))
			{
				return "<a href='" . $href . "' style='text-decoration:none;'>" . $meta_value['name'] . "</a>";
			}
		}
	}

	function insert_attachment($object, $file = false, $draft_id) 
	{
		if ( $file )
		{
// Make the file path relative to the upload dir
			if ( ($uploads = wp_upload_dir()) && false === $uploads['error'] ) 				// Get upload directory
			{ 	
				if ( 0 === strpos($file, $uploads['basedir']) ) 						// Check that the upload base exists in the file path
				{
					$file = str_replace($uploads['basedir'], '', $file); 					// Remove upload dir from the file path
					$file = ltrim($file, '/');
				}
			}
			$object['file'] = $file;
			return MP_Mailmeta::add( $draft_id, '_MailPress_attached_file', $object );
		}
		return $draft_id;
	}

	function mp_action_delete_attachement()
	{
		if (!isset($_POST['mmeta_id'])) return;
		if (!is_numeric($_POST['mmeta_id'])) return;
		$mid = (int) $_POST['mmeta_id'];
		MP_Mailmeta::delete_by_id( $mid );
		echo '1';
		die();
	}






	public static function plaintext_meta_box($draft)
	{
?>
<textarea id='plaintext' name='plaintext' cols='40' rows='1'><?php echo htmlspecialchars(stripslashes($draft->plaintext), ENT_QUOTES); ?></textarea>
<?php
	}

	public static function revision_meta_box($draft)
	{
		mp_list_mail_revisions($draft->id);
	}


/// THICKBOX ///


	public static function mp_action_iview()
	{
		$theme = (isset($_GET['theme'])) ? '&theme=' . $_GET['theme'] : '';
		$id 		= $_GET['id'];
		$main_id	= (isset($_GET['main_id'])) ? $_GET['main_id'] : $id;
		$_main_id	= "&main_id=" . $main_id;
		$mail 	= self::get($id);
		$mp_general = get_option('MailPress_general');
		$from 	= ('sent' == $mail->status) ? self::display_toemail($mail->fromemail,stripslashes($mail->fromname)) :  self::display_toemail($mp_general['fromemail'],stripslashes($mp_general['fromname']));
		$to 		= self::display_toemail($mail->toemail,stripslashes($mail->toname));
		$subject 	= ('sent' == $mail->status) ? $mail->subject : self::do_eval(stripslashes($mail->subject));
		$html_url 	= clean_url(get_option('siteurl') . '/' . MP_PATH . "mp-includes/action.php?action=viewadmin&id=$id$_main_id&type=html$theme");
		$html 	= "<iframe id='ihtml' style='width:100%;border:0;height:550px' src='" . $html_url . "'></iframe>";
		$plaintext_url = clean_url(get_option('siteurl') . '/' . MP_PATH . "mp-includes/action.php?action=viewadmin&id=$id$_main_id&type=plaintext$theme");
		$plaintext 	= "<iframe id='iplaintext' style='width:100%;border:0;height:550px' src='" . $plaintext_url . "'></iframe>";

		$attachements = '';
		$metas = MP_Mailmeta::has( $main_id, '_MailPress_attached_file');
		if ($metas) foreach($metas as $meta) $attachements = "<tr><td>&nbsp;" . self::get_attachement_link($meta, $mail->status) . "</td></tr>";
		include MP_TMP . '/mp-includes/viewthickbox.html.php';
	}


////	IFRAMES	////


	public static function mp_action_viewuser() {
		$id 		= $_GET['id'];
		$key		= $_GET['key'];
		$email 	= MP_User::get_email(MP_User::get_id($key));
		$mail 	= self::get($id);

		$x = new MP_Mail();

		if (MailPress::is_email($mail->toemail))
		{
			echo $x->process_img($mail->html,$mail->themedir,'draft');
		}
		else
		{
			$recipients = unserialize($mail->toemail);
			$recipient = $recipients[$email];
			echo $x->process_img($x->mail_decorator($mail->html,$recipient,'',''),$mail->themedir,'draft');
		}
	}

	public static function mp_action_viewadmin() {
		$id 	= $_GET['id'];
		$main_id 	= $_GET['main_id'];
		$type	= (isset($_GET['type'])) ? $_GET['type'] : 'html';
		$theme= (isset($_GET['theme'])) ? $_GET['theme'] : false;

		$x = new MP_Mail();
		if ('html' == $type) 		$x->viewhtml($id, $main_id, $theme);
		elseif ('plaintext' == $type) $x->viewplaintext($id, $main_id, $theme);
	}

	function viewhtml($id, $main_id, $theme=false)
	{
		$x = self::get($id);
		$y = array('sent','unsent');
		if (!in_array($x->status, $y))
		{
			$this->args->id	= $id;
			$this->args->main_id	= $main_id;
			if ($theme) $this->args->Theme = $theme;
			$this->args->html = stripslashes($x->html);
			$x->html 		= $this->build_mail_content('html',true);
			$x->themedir 	= $this->mail->themedir;
		}
		echo $this->process_img($x->html,$x->themedir,'draft');
	}

	function viewplaintext($id, $main_id, $theme=false)
	{
		$x = self::get($id);
		$y = array('sent','unsent');
		if (!in_array($x->status, $y))
		{
			$this->args->id		= $id;
			$this->args->main_id	= $main_id;
			if ($theme) $this->args->Theme = $theme;
			$this->args->plaintext 	= stripslashes($x->plaintext);
			$x->plaintext 		= strip_tags($this->build_mail_content('plaintext'));
		}
		include MP_TMP . '/mp-includes/viewplaintext.html.php';
	}

	function end_of_tracking()
	{
		$meta = MP_Mailmeta::get_by_id($_GET['mm']);
		if ($meta)
		{
			switch ($_GET['tg'])
			{
				case ('l') :
					switch ($meta->meta_value)
					{
						case '{{subscribe}}' :
							$url = MP_User::get_subscribe_url($_GET['us']);
						break;
						case '{{unsubscribe}}' :
							$url = MP_User::get_unsubscribe_url($_GET['us']);
						break;
						case '{{viewhtml}}' :
							$url = MP_User::get_view_url($_GET['us'], $meta->mail_id);
						break;
						default :
							$url = $meta->meta_value;
						break;
					}
					wp_redirect($url);
					die();
				break;
				case ('o') :
					$file = '_.gif';
					$file_fullpath = MP_TMP . '/mp-includes/images/' . $file;
					$name	= 'gif_' . $_GET['us'] . '_' . $_GET['mm'] . '.gif';

					if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) $file = preg_replace('/\./', '%2e', $file, substr_count($file, '.') - 1);
					if(!$fdl=@fopen($file_fullpath,'r')) 	die(__('Cannot Open File !','MailPress'));
					header("Cache-Control: ");# leave blank to avoid IE errors
					header("Pragma: ");# leave blank to avoid IE errors
					header("Content-type: image/gif");
					header("Content-Disposition: attachment; filename=\"".$name."\"");
					header("Content-length:".(string)(filesize($file_fullpath)));
					sleep(1);
					fpassthru($fdl);
					die();
				break;
			}
		}
		wp_redirect(get_option('home'));
		die();
	}
}

function mp_unique_filename_callback($dir, $name) 
{
	return 'mp_' . md5('_mp_' . mktime() . $name) . '_' . $name . '.spc';
}
?>