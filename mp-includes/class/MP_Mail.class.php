<?php
class MP_Mail
{
	const name_required = true;
	const get8BitEncoding = true;  // setting this to false can have an impact on perf see http://forums.devnetwork.net/viewtopic.php?f=52&t=96933

	function __construct( $plug = MP_FOLDER )
	{
		$this->plug = $plug;

		MailPress::require_class('Themes');
		$this->theme = new MP_Themes();

		$this->message 	= null;

		$this->args = new stdClass();
	}

////	MP_Mail send functions	////

	function send($args)
	{
		if (is_numeric($args))
		{
			MailPress::require_class('Mails');
			$this->args = MP_Mails::get($args);
		}
		else
		{
			$this->args = $args ;
		}

		return $this->end( $this->start() );
	}

	function start()
	{
		MailPress::no_abort_limit();

		$this->row = new stdClass();
		$this->mail = new stdClass();

		global $mp_general;
		$mp_general = get_option('MailPress_general');

////  Log it  ////

		MailPress::require_class('Log');
		$f = (isset($this->args->forcelog)) ? true : false; 
		$this->trace = new MP_Log('MP_Mail', ABSPATH . MP_PATH, $this->plug, $f, 'general');

		if (!$this->args)	
		{
/*trace*/		$this->trace->log('*** ERROR *** Sorry invalid arguments in MP_Mail::send');
			return false;
		}

////  Build it  ////

		if (!isset($this->args->id))
		{
			MailPress::require_class('Mails');
			$this->row->id = $this->args->id = MP_Mails::get_id( 'MP_Mail::start' );
		}
		else
			$this->row->id = $this->args->id;

	//¤ charset ¤//
		$this->row->charset = (isset($this->args->charset)) ? $this->args->charset : get_option('blog_charset');

	//¤ fromemail & fromname ¤//
		$this->row->fromemail 	= (empty($this->args->fromemail)) ? $mp_general['fromemail'] : $this->args->fromemail;
		$this->row->fromname 	= (empty($this->args->fromname))  ? stripslashes($mp_general['fromname']) : $this->args->fromname;
		$this->row->fromname 	= (empty($this->row->fromname))   ? self::display_name(stripslashes($mp_general['fromemail'])) : $this->row->fromname;

	//¤ recipients & replacements ¤//
		self::get_mail_replacements();

		$this->mail->recipients_count = $this->manage_recipients();
		if (!$this->mail->recipients_count)
		{
/*trace*/		$this->trace->log((0 === $this->mail->recipients_count) ? '*** ERROR *** No recipient' : '*** ERROR *** problems with recipients & replacements');
			return $this->mail->recipients_count;
		}

	//¤ subject ¤//
		$this->row->subject 	= (isset($this->args->subject)) ? $this->args->subject : false;
		$pt = $this->theme->get_page_templates_from((isset($this->args->Theme)) ? $this->args->Theme : $this->theme->themes[$this->theme->current_theme]['Template']);
		if (isset($this->args->Template)) if (isset($pt[$this->args->Template][1]) ) $this->row->subject = $pt[$this->args->Template][1];
		$this->row->subject 	= ($this->row->subject) ? trim($this->do_eval($this->row->subject)) : '';

	//¤ plaintext ¤//
		$this->row->plaintext 	= trim(strip_tags($this->build_mail_content('plaintext')));

	//¤ html ¤//
		$this->row->html		= trim($this->build_mail_content('html', (isset($this->args->draft)) ? true : false));
		$this->row->html		= ( '<br />' == $this->row->html ) ? '' : trim($this->row->html);

	//¤ attachements ¤//
		$this->mail->attachements = false;
		if (isset($this->args->main_id))
		{
			MailPress::require_class('Mailmeta');
			$metas = MP_Mailmeta::has( $this->args->main_id, '_MailPress_attached_file');
			if ($metas)
			{
				foreach($metas as $meta)
				{
					$meta_value = unserialize( $meta['meta_value'] );
					if (!is_file($meta_value['file_fullpath'])) continue;
					$this->mail->attachements = true;
					MP_Mailmeta::add( $this->row->id, '_MailPress_attached_file', $meta_value );
				}
			}
		}

		unset($this->theme);

	//¤ mail empty ? ¤//
		if (!$this->row->subject && !$this->row->plaintext && !$this->row->html && !$this->mail->attachements)
		{
/*trace*/		$this->trace->log(__('*** WARNING *** Mail is empty', 'MailPress'));
			return false;
		}

		$this->row->theme		= $this->mail->theme;
		$this->row->themedir	= $this->mail->themedir;
		$this->row->template 	= $this->mail->template;

	//¤ no tracking on unknown recipient ! ¤//
		if (!isset($this->mail->external_recipient))
		{
			$this->row = apply_filters('MailPress_mail', $this->row);
			$this->replace_mail_urls();
		}

	//¤ only one recipient ¤//
		if (1 == $this->mail->recipients_count)
		{
			$toname = '';
			if (isset($this->row->recipients[0])) $toemail = $this->row->recipients[0]; else foreach($this->row->recipients as $toemail => $toname) {};
			$this->row->toemail = $toemail;
			$this->row->toname  = $toname;

			$this->row->replacements[$toemail] = array_merge($this->mail->replacements, $this->row->replacements[$toemail]);

			foreach($this->row->replacements[$toemail] as $k => $v) 
			{
				$this->row->subject 	= str_replace($k, $v, $this->row->subject, $cs);
				$this->row->plaintext 	= str_replace($k, $v, $this->row->plaintext, $cp);
				$this->row->html 		= str_replace($k, $v, $this->row->html, $ch);
			}

			if (isset($this->row->replacements[$toemail]['{{_user_id}}'])) $this->row->mp_user_id = $this->row->replacements[$toemail]['{{_user_id}}'];
			MP_Mailmeta::delete_by_id($this->mail->mmid);
			unset($this->row->replacements, $this->row->recipients, $this->mail->replacements, $this->mail->mmid);
		}

/*trace*/	$x  = " \n\n ------------------- start of mail -------------------  ";
/*trace*/	$x .= " \n From : " . $this->row->fromname . " <" . $this->row->fromemail . "> ";
/*trace*/	if (isset($this->row->toemail))
/*trace*/		if (!empty($this->row->toname))
/*trace*/	$x .= " \n To   : " . $this->row->toname . " <" . $this->row->toemail . "> ";
/*trace*/		else
/*trace*/	$x .= " \n To   : " . $this->row->toemail;
/*trace*/	$x .= " \n Subject : " . $this->row->subject;
/*trace*/	if ($this->row->plaintext) 	$x .= " \n   ------------------- plaintext -------------------  \n " . $this->row->plaintext;
/*trace*/	if ($this->row->html) 		$x .= " \n\n ------------------- text/html -------------------  \n " . $this->row->html;
/*trace*/	$x .= " \n\n ------------------ end of mail ------------------  \n\n";
/*trace*/	$this->trace->log($x, $this->trace->levels[512]);

		$this->mail->swift_batchSend 		= (1 < $this->mail->recipients_count);
		$this->mail->mailpress_batch_send	= ($this->mail->swift_batchSend) ? apply_filters('MailPress_status_mail', false) : false;

////  Send it  ////

	//¤ no mail ? ¤//
		if (isset($this->args->nomail))
		{
/*trace*/		$this->trace->log(':::: Mail not sent as required ::::');
		}
		elseif ($this->mail->mailpress_batch_send)
		{
/*trace*/		$this->trace->log(':::: Mail batch processing as required ::::');
			do_action('MailPress_schedule_batch_send');
		}
		else
		{
			if (!$this->swift_processing())
			{
/*trace*/			$this->trace->log('');
/*trace*/			$this->trace->log('*** ERROR *** Mail could not be sent ***');
/*trace*/			$this->trace->log('');
				unset($this->message, $this->swift); 
				return false;
			}
		}
		unset($this->message, $this->swift); 

////  Archive it  ////

		if (isset($this->args->noarchive))
		{
/*trace*/		$this->trace->log(':::: Mail not saved as required ::::');
			MailPress::require_class('Mails');
			MP_Mails::delete($this->row->id);
		}
		else
		{ 
			global $wpdb;

			if (!isset($this->args->nostats)) MailPress::update_stats('t', isset($this->args->Template) ? $this->args->Template : '', $this->mail->recipients_count);

			$now		= date('Y-m-d H:i:s');
			$user_id 	= MailPress::get_wp_user_id();

			if ($this->mail->swift_batchSend) 
			{
				foreach ($this->row->replacements as $email => $r)
				{
					if (isset($this->row->recipients[$email]))
						$this->row->toemail[$email] = $this->row->replacements[$email];
					unset($this->row->recipients[$email], $this->row->replacements[$email]);
				}
				foreach ($this->row->recipients as $k => $email)
				{
					$this->row->toemail[$email] = $this->row->replacements[$email];
					unset($this->row->recipients[$k], $this->row->replacements[$email]);
				}
				unset($this->row->recipients, $this->row->replacements);
				$this->row->toemail = mysql_real_escape_string(serialize($this->row->toemail));
			}

	//¤ status ¤//
			$this->row->status = ($this->mail->mailpress_batch_send) ? (apply_filters('MailPress_status_mail', 'sent')) : 'sent';
			if (!isset($this->row->toname)) $this->row->toname = '';

			wp_cache_delete($this->row->id, 'mp_mail');
			$query = "UPDATE $wpdb->mp_mails 
					SET 	status 	= '" . $this->row->status . "', 
						theme 	= '" . $this->row->theme . "', 
						themedir 	= '" . $this->row->themedir . "', 
						template 	= '" . $this->row->template . "', 
						fromemail 	= '" . $this->row->fromemail . "', 
						fromname 	= '" . mysql_real_escape_string($this->row->fromname) . "', 
						toemail 	= '" . $this->row->toemail . "', 
						toname 	= '" . mysql_real_escape_string($this->row->toname) . "', 
						charset 	= '" . $this->row->charset . "', 
						subject 	= '" . mysql_real_escape_string($this->row->subject) . "', 
						html 		= '" . mysql_real_escape_string($this->row->html) . "', 
						plaintext 	= '" . mysql_real_escape_string($this->row->plaintext) . "',
						created 	= '" . $now . "', 
						created_user_id = " . $user_id . ",
						sent 		= '" . $now . "', 
						sent_user_id = " . $user_id . "
					WHERE id = " . $this->row->id . " ;";

			if ( !$wpdb->query( $query ) )
			{
/*trace*/			$this->trace->log(sprintf('*** ERROR *** Database error, Mail not saved : %1$s', $wpdb->last_error));
				return false;
			}
/*trace*/		$this->trace->log(':::: MAIL SAVED ::::');
		}

		return $this->mail->recipients_count;
	}

	function end($rc)
	{
		$this->trace->end($rc);
		return $rc;
	}


////  Build it  ////


 //¤ mail replacements ¤//

	function get_mail_replacements()
	{
		$mail_replacements = $this->convert_all($this->args);
		$unsets = array( '{{subject}}', '{{content}}', '{{html}}', '{{plaintext}}', '{{id}}', '{{main_id}}', '{{recipients_query}}', '{{draft}}' );
		foreach ($unsets as $unset) unset($mail_replacements[$unset]);

		MailPress::require_class('Mailmeta');
		$mail_main_id = (!isset($this->args->main_id)) ? 0 : $this->args->main_id;
		$_mail_id = ($mail_main_id) ? $mail_main_id : $this->row->id;
		$m = MP_Mailmeta::get_replacements($_mail_id);
		if (!is_array($m)) $m = array();

		$this->mail->replacements = array_merge($m, $mail_replacements);

		$this->mail->mmid = MP_Mailmeta::add( $this->row->id, '_MailPress_replacements', $this->mail->replacements );
	}

	function convert_all($x='', $sepb='{{', $sepa='}}', $before='', $first=0, $array=array())
	{
		if (is_object($x)) $x = get_object_vars($x);
		if (empty($x)) return array();
		foreach($x as $key => $value)
		{
			if (!(is_object($value) || is_array($value)))
			{
				$x 		= (0 == $first) ? $key : $before . '[' . $key . ']'; 
				$x = $sepb . $x . $sepa;
				$array[$x] = $value;
			}
			else 
			{
				$abefore= (!$first) ? $key : $before . '[' . $key . ']'; 
				$array = array_merge($array, $this->convert_all($value , $sepb , $sepa , $abefore , $first + 1 ) );
			}
		}
		return $array;
	}

 //¤ recipients & replacements ¤//

	function manage_recipients()
	{
		if (isset($this->args->replacements))
		{
			if (isset($this->args->recipients))
			{
				$this->row->replacements = $this->args->replacements;
				$this->row->recipients   = $this->args->recipients;
				return count($this->row->recipients);
			}
			else
			{
				$this->get_old_recipients();
				return count($this->row->recipients);
			}
		}

		if (!isset($this->args->recipients_query))
		{
			if (MailPress::is_email($this->args->toemail))
			{
				MailPress::require_class('Users');
				$mp_user_id = MP_Users::get_id_by_email($this->args->toemail);
				if ($mp_user_id)
				{
					global $wpdb;
					$this->args->recipients_query = "SELECT DISTINCT id, email, name, status, confkey FROM $wpdb->mp_users WHERE id = $mp_user_id ;";
				}
			}
		}

		if (isset($this->args->recipients_query))
		{
			$this->get_recipients($this->args->recipients_query);

			MailPress::require_class('Users');
			$this->args->viewhtml = MP_Users::get_view_url('{{_confkey}}', $this->row->id);
			if (isset($this->args->subscribe)) 	$this->args->subscribe   = MP_Users::get_subscribe_url('{{_confkey}}');
			else						$this->args->unsubscribe = MP_Users::get_unsubscribe_url('{{_confkey}}');

			return count($this->row->recipients);
		}

		if (MailPress::is_email($this->args->toemail))
		{
			$this->mail->external_recipient = true;
			$this->get_external_recipient();
			return 1;
		}

		return false;
	}

	function get_recipients($query)
	{
		global $wpdb;
		$mp_users = $wpdb->get_results( $query );

		$this->row->recipients = array();
		if ($mp_users)
		{
			foreach ($mp_users as $mp_user)
			{
				$this->row->replacements[$mp_user->email] = $this->get_user_replacements($mp_user);

				if (isset($this->args->toname)) if (count($mp_users) == 1) $mp_user->name = $this->args->toname;
				if ( empty($mp_user->name) )
					if (self::name_required)
						$this->row->recipients[$mp_user->email] 	= trim(str_replace('.', ' ', substr($mp_user->email, 0, strpos($mp_user->email, '@'))));
					else
						$this->row->recipients[] 			= $mp_user->email;
				else
					$this->row->recipients[$mp_user->email] 		= $mp_user->name;
			}
		}
	}

	function get_external_recipient()
	{
/*trace*/	$this->trace->log(':::: external recipient ::::');
		$this->row->replacements[$this->args->toemail] = array();

		if (isset($this->args->toname))
			if (!empty($this->args->toname))
				$this->row->recipients[$this->args->toemail] = $this->args->toname;
			else
				if (self::name_required)
					$this->row->recipients[$this->args->toemail] 	= trim(str_replace('.', ' ', substr($this->args->toemail, 0, strpos($this->args->toemail, '@'))));
				else
					$this->row->recipients[] = $this->args->toemail;
		else
			$this->row->recipients[] = $this->args->toemail;
	}

	function get_old_recipients()
	{
		$this->row->replacements = $this->args->replacements;

		foreach($this->row->replacements as $email => $v)
		{
			if (isset($v['{{toname}}']) && !empty($v['{{toname}}']))
				$this->row->recipients[$email] = $v['{{toname}}'];
			else
			{
				if (!self::name_required)
					$this->row->recipients[] = $email;
				else
				{
					$name = trim(str_replace('.', ' ', substr($email, 0, strpos($email, '@'))));
					$this->row->recipients[$email] = $this->row->replacements[$email]['{{toname}}'] =  $name;
				}
			}
		}
	}

	function get_user_replacements($mp_user)
	{
		MailPress::require_class('Usermeta');

		if (is_numeric($mp_user))
		{
			MailPress::require_class('Users');
			$mp_user = MP_Users::get($mp_user);
            if (!$mp_user) return array();
		}

		$replacements = MP_Usermeta::get_replacements($mp_user->id);

		$replacements ['{{toemail}}']	= $mp_user->email;
		$replacements ['{{toname}}']	= $mp_user->name;
		$replacements ['{{_user_id}}']= $mp_user->id;
	//¤ always last ¤//
		$replacements ['{{_confkey}}']= $mp_user->confkey;
		return $replacements;
	}

	function replace_mail_urls()
	{
		$r = array();
		if (isset($this->args->viewhtml)) 	$r['{{viewhtml}}'] 	= $this->args->viewhtml;
		if (isset($this->args->subscribe))	$r['{{subscribe}}'] 	= $this->args->subscribe;
		if (isset($this->args->unsubscribe))$r['{{unsubscribe}}'] 	= $this->args->unsubscribe;

		foreach($r as $k => $v)
		{
			$this->row->subject 	= str_replace($k, $v, $this->row->subject, $cs);
			$this->row->plaintext 	= str_replace($k, $v, $this->row->plaintext, $cp);
			$this->row->html 		= str_replace($k, $v, $this->row->html, $ch);
		}
	}

 //¤ plaintext, html ¤//

	function build_mail_content($type, $filter=false)
	{
		$current_theme = $this->theme->current_theme;

		if (!isset($this->mail)) $this->mail = new stdClass();
		$this->mail->theme = $this->mail->themedir = $this->mail->template = null;

		$this->build = new stdClass();
		$this->build->plaintext = ('plaintext' == $type) ? true : false;
		$this->build->filter = $filter;

		$content = '';
		$content_default 	= '<?php $this->get_header(); $this->the_content(); $this->get_footer(); ?>';
		$template_file	= 'default.php';

	//¤ find the theme and themedir ¤//
		$this->mail->theme 	= $this->theme->themes[$this->theme->current_theme]['Template'];
		$this->mail->themedir	= $this->theme->themes[$this->theme->current_theme]['Template Dir'];
		if (isset($this->args->Theme))
		{
			$x = $this->theme->get_theme_by_template($this->args->Theme);
			if (!empty($x))
			{
				$this->theme->current_theme 	= $x['Name'];
				$this->mail->theme		= $this->args->Theme;
				$this->mail->themedir		= $x['Template Dir'];
			}
			else
/*trace*/			if (isset($this->trace) && !empty($this->args->Theme)) $this->trace->log(sprintf('Missing theme : >> %1$s <<. You should create this theme with the appropriate files (header, footer, ...) & templates.', $this->args->Theme), $this->trace->levels[512]);
		}

	//¤ find the templates ¤//
		$pt 			= ($this->build->plaintext) ? $this->theme->get_page_plaintext_templates_from($this->mail->theme) : $this->theme->get_page_templates_from($this->mail->theme);
		$this->build->dir = ($this->build->plaintext) ? ABSPATH . $this->theme->themes[$this->theme->current_theme] ['Plaintext Template Dir'] . '/' : ABSPATH . $this->theme->themes[$this->theme->current_theme] ['Template Dir'] . '/' ;

	//¤ find the template ¤//
		if (isset($this->args->Template)) 
		{		
			if (isset($pt[$this->args->Template]))
			{
				$template_file = $pt[$this->args->Template][0];
				$this->mail->template = $this->args->Template;
			}
			else
/*trace*/			if (isset($this->trace) && (!$this->build->plaintext)) $this->trace->log(sprintf('Missing template file >> %1$s <<. You should create the template in the appropriate folder %2$s .', $this->args->Template, $this->build->dir), $this->trace->levels[512]);
		}

		$fname = $this->build->dir . $template_file;

		if (is_file($fname))	$content = '<?php $this->load_template(\'' . $fname . '\'); ?>';
		else
		{
			if ($this->build->plaintext)
			{
				$this->theme->current_theme = 'plaintext';
				$x = $this->theme->get_theme_by_template('plaintext');
				if (empty($x))
				{
/*trace*/				if (isset($this->trace)) $this->trace->log('Missing theme : plaintext theme has been deleted.', $this->trace->levels[512]);
					$this->theme->current_theme = $current_theme;
					unset($this->build);
					return '';
				}
				$template_file = 'default.php';
				$pt = $this->theme->get_page_templates_from('plaintext');
				$this->build->dir = ABSPATH . $x['Template Dir'] . '/' ;
				if (isset($this->args->Template)) 
				{		
					if (isset($pt[$this->args->Template]))
					{
						$template_file = $pt[$this->args->Template][0];
						$this->mail->template = $this->args->Template;
					}
				}
				$fname = $this->build->dir . $template_file;
				$content = (is_file($fname)) ? '<?php $this->load_template(\'' . $fname . '\'); ?>' : $content_default;
			}
			else
			{
				$content = $content_default;
			}
		}

		if (empty($content)) 
		{
			$this->theme->current_theme = $current_theme;
			unset($this->build);
			return '';
		}
		
	//¤ functions.php ¤//
		$fname = $this->build->dir . 'functions.php';
		if (is_file($fname)) $content = "<?php require_once ('$fname'); ?>" . $content;

		$x = $this->do_eval($content);

		$this->theme->current_theme = $current_theme;
		unset($this->build);
		return $x;
	}

	function do_eval($x)
	{
		$x = 'global $posts, $post, $wp_did_header, $wp_did_template_redirect, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID; ?>' . "\n $x";
		ob_start();
			echo(eval($x));
			$r = ob_get_contents();
		ob_end_clean();
		return $r;
	}

 //¤ could be called in the do_eval ¤//
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
			if (isset($this->args->html)	  && !empty($this->args->html))		$content = $this->args->html;
			if ($this->build->filter) 								$content = apply_filters('the_content', $this->do_eval($content));
		}
		echo $content;
	}
	
	function get_header() 
	{
		if ( file_exists( $this->build->dir . 'header.php') )
		{
			$this->load_template( $this->build->dir . 'header.php');
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
		if ( file_exists( $this->build->dir . 'footer.php') )
		{
			$this->load_template( $this->build->dir . 'footer.php');
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
		if ( file_exists( $this->build->dir . "sidebar-{$name}.php") )
		{
			$this->load_template( $this->build->dir . "sidebar-{$name}.php");
			return;
		}
		if ( file_exists( $this->build->dir . 'sidebar.php') )
		{
			$this->load_template( $this->build->dir . 'sidebar.php');
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
		if ( file_exists( $this->build->dir . 'style.css') )
		{
			echo "<style type='text/css' media='all'>\n";
			$this->load_template( $this->build->dir . 'style.css');
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
	
		if (isset($wp_query->query_vars) && is_array($wp_query->query_vars) ) extract($wp_query->query_vars, EXTR_SKIP);

		include($_template_file);
	}

	function classes($classes)
	{
		$fname = $this->build->dir . 'style.php';
		if (!is_file($fname)) return '';

		include ($fname);

		$count = 1;

		while ($count) $classes = str_replace('  ', ' ', $classes, $count);
		$a_classes = explode(' ', trim($classes));

		$style = '';

		foreach($a_classes as $class) if (isset($_classes[$class])) $style .=  $this->clean_style($_classes[$class]);

		if ('' != $style) echo "style=\"" . $style . "\"";
	}

	function clean_style($style)
	{
		$style = trim($style);
		$style = str_replace("\t",'',$style);
		$style = str_replace("\n",'',$style);
		$style = str_replace("\r",'',$style);
		if (strlen($style)) if ($style[strlen($style) -1] != ';') $style .=';';
		return $style;
	}


////  Send it  ////


	function swift_processing()
	{
		require_once (MP_TMP . 'mp-includes/class/swift/swift_required.php');

	//¤ Swift message ¤//
		try 
		{
			$this->build_swift_message();
		}
		catch (Swift_SwiftException $e) 
		{
/*trace*/		$this->trace->log('**** SWIFT ERROR **** ' . "There was an unexpected problem building the mail:\n\n" . $e->getMessage() . "\n\n");	
			return false;
		}

	//¤ Swift connection ¤//
		try 
		{
			add_filter('MailPress_Swift_Connection_SMTP', 	array('MP_Mail', 'SMTP_connect'), 8, 2);

			$Swift_Connection_type = apply_filters('MailPress_Swift_Connection_type', 'SMTP');

			$conn = apply_filters('MailPress_Swift_Connection_' . $Swift_Connection_type , null, $this->trace );

			$this->swift = Swift_Mailer::newInstance($conn);
		}
		catch (Swift_SwiftException $e) 
		{
/*trace*/		$this->trace->log('**** SWIFT ERROR **** ' . "There was a problem connecting with $Swift_Connection_type :\n\n" . $e->getMessage() . "\n\n");	
			return false;
		} 

		Swift_Preferences::getInstance()->setTempDir(MP_TMP . "tmp")->setCacheType('disk');

	//¤ Swift sending ... ¤//
		try 
		{
			$this->swift = apply_filters('MailPress_PopBeforeSmtp', $this->swift);
		//¤ batch processing ¤//
			if ($this->mail->mailpress_batch_send)
				return apply_filters('MailPress_swift_send', $this);

			$this->mysql_disconnect('MP_Mail');

		//¤ swift batchSend ¤//
			if ($this->mail->swift_batchSend)
			{
				$this->swift->registerPlugin(new Swift_Plugins_DecoratorPlugin($this->row->replacements));
				if (!$this->swift->batchSend($this->message))
				{
					$this->mysql_connect('MP_Mail batchSend');
					return false;
				}
			}

		//¤ swift send ¤//
			else
			{
				if (!$this->swift->send($this->message)) 
				{
					$this->mysql_connect('MP_Mail send');
					return false;
				}
			}

			$this->mysql_connect('MP_Mail');

			return true;
		}
		catch (Swift_SwiftException $e) 
		{
/*trace*/		$this->trace->log('**** SWIFT ERROR **** ' . "There was a problem sending with $Swift_Connection_type :\n\n" . $e->getMessage() . "\n\n");	
			return false;
		}
		return true;
	}

	function build_swift_message()
	{

	//¤ charset ¤//
		Swift_Preferences::getInstance()->setCharset($this->row->charset);

	//¤ message ¤//
		$this->message 	 = Swift_Message::newInstance();

		//$this->message->setLanguage(substr(WPLANG, 0, 2));
		if (self::get8BitEncoding) $this->message->setEncoder(Swift_Encoding::get8BitEncoding());

	//¤ from ¤//
		$this->message->setFrom(array($this->row->fromemail => $this->row->fromname));

	//¤ to & replacements ¤//
		if (!$this->mail->swift_batchSend) 
			$this->message->setTo(array($this->row->toemail => $this->row->toname));
		else
		{
			$this->message->setTo($this->row->recipients);
			foreach($this->row->replacements as $k => $v) $this->row->replacements[$k] = array_merge($this->mail->replacements, $this->row->replacements[$k]);
		}

	//¤ subject ¤//
		$this->row->subject 	= html_entity_decode($this->row->subject, ENT_QUOTES, get_option('blog_charset'));
		$this->message->setSubject($this->row->subject);

	//¤ filter headers ¤//
		$this->message	= apply_filters('MailPress_swift_message_headers', $this->message, $this->row);

	//¤ html ¤//
		if ($this->row->html)
		{
			$this->message->setBody($this->process_img( $this->row->html, $this->row->themedir ), 'text/html');
		}

	//¤ plaintext ¤//
		if ($this->row->plaintext)
		{
			$this->row->plaintext 	= html_entity_decode($this->row->plaintext, ENT_QUOTES, get_option('blog_charset'));
			if ($this->row->html)
				$this->message->addPart($this->row->plaintext);
			else
				$this->message->setBody($this->row->plaintext);
		}


	//¤ attachements ¤//
		MailPress::require_class('Mailmeta');
		$metas = MP_Mailmeta::has( $this->row->id, '_MailPress_attached_file');
		if ($metas)
		{
			foreach($metas as $meta)
			{
				$meta_value = unserialize( $meta['meta_value'] );
				if (!is_file($meta_value['file_fullpath'])) continue;
				$this->message->attach(Swift_Attachment::fromPath($meta_value['file_fullpath'], $meta_value['mime_type'])->setFilename($meta_value['name']));
			}
		}
	}

	function process_img($html, $path, $dest='mail')
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

		$imgs = array();
		foreach ($matches as $match)
		{
			$f = $u = false;

			if (stristr($match[1], $siteurl) && apply_filters('MailPress_img_mail_keepurl', false))
			{
				$imgs[$match[1]] = $match[1];
				continue;
			}
			elseif (stristr($match[1], $siteurl)) $u = true;
			elseif ((stristr($match[1], 'http://')) || (stristr($match[1], 'https://')))
			{
				$imgs[$match[1]] = $match[1];
				continue;
			}

			foreach ($masks as $mask)
			{
				if ($u) 	$file = str_ireplace($siteurl, '', $match[1]);
				else		$file = $mask . $match[1];

				if (is_file(ABSPATH . $file)) 
				{
					$f = true;
					$x[$match[1]] = $fprefix . $file;		// we can have the src/url image in different img tags ... so we embed it one time only
/*trace*/				if (isset($this->trace)) if ('mail' == $dest) $this->trace->log('Image found : ' . $file, $this->trace->levels[512]);
				}
				if ($f) break;
				
			}
/*trace*/ //if (isset($this->trace)) if (('mail' == $dest) && (!$f)) $this->trace->log('Image NOT found : ' . $match[1], $this->trace->levels[512]);
		}

		if ('mail' == $dest)
		{
			foreach ($x as $key => $file)
			{
				try 
				{
					$imgs[$key] = $this->message->embed(Swift_Image::fromPath($file));
				}
				catch (Swift_SwiftException $e) 
				{
/*trace*/				if (isset($this->trace)) $this->trace->log('**** SWIFT ERROR **** ' . "There was a problem with this image: $file \n\n" . $e->getMessage() . "\n\n");
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
			$match[3]	= (isset($imgs[$match[1]])) ? str_replace($match[1], $imgs[$match[1]], $match[0]) : $match[0]; // and we retrieve it now with the proper <img ... />
			if ('html' != $dest) $match[3] = apply_filters('MailPress_img_mail', $match[3]); // apply_filters for 'mail', 'draft'
			$html		= str_replace($match[0], $match[3], $html);
		}
		return $html;
	}

	function mysql_connect($x = '0')
	{
		global $wpdb;
		if (isset($this->trace)) $this->trace->log("Connecting to " . DB_NAME . " ($x)");

		$wpdb->__construct(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);

		if (isset($this->trace)) $this->trace->log("Connected ($x)");
	}

	function mysql_disconnect($x = '0')
	{
		global $wpdb;
		if (isset($this->trace)) $this->trace->log("Disconnecting from " . DB_NAME . " ($x)");
		mysql_close($wpdb->dbh);
		if (isset($this->trace)) $this->trace->log("Disconnected ($x)");
	}


////	MP_Mail SMTP functions	////


	public static function SMTP_connect($x, $y)
	{
		$smtp_settings = get_option('MailPress_smtp_config');

		$conn = Swift_SmtpTransport::newInstance();

		$conn->setHost($smtp_settings['server']);
		$conn->setPort($smtp_settings['port']);

		if (!empty($smtp_settings ['ssl']))
			$conn->setEncryption($smtp_settings ['ssl']);

		if (empty($smtp_settings['username']) && empty($smtp_settings['password']))
/* trace */		$y->log("**** Empty user/password for SMTP connection ****");	
		else
			$conn->setUsername($smtp_settings ['username']);
			$conn->setPassword($smtp_settings ['password']);

// eventually popb4smtp (other authentications are detected automatically)
		if (isset($smtp_settings['smtp-auth']) && (!empty($smtp_settings['smtp-auth'])))
		{
			switch ($smtp_settings['smtp-auth'])
			{
				case '@PopB4Smtp' :
					add_filter('MailPress_PopBeforeSmtp', array('MP_Mail', 'MailPress_PopBeforeSmtp'), 8, 1);
				break;
			}
		}

		return $conn;
	}

	public static function MailPress_PopBeforeSmtp($_this_swift)
	{
		$smtp_settings = get_option('MailPress_smtp_config');
		$_this_swift->registerPlugin(new Swift_Plugins_PopBeforeSmtpPlugin($smtp_settings['pophost'], $smtp_settings['popport']));
		return $_this_swift;
	}


/// DISPLAYING E-MAILS & NAMES ///


	public static function display_name($name, $for_mail=true)
	{
		$default = '_';
		if ( MailPress::is_email($name) )	$name = trim(str_replace('.', ' ', substr($name, 0, strpos($name, '@'))));
		if ( $for_mail ) 
		{ if ( empty($name) ) 	$name = $default; }
		else
		{ if ($default == $name)$name = '';}
		return $name;									
	}

	function viewhtml($id, $main_id, $theme = false)
	{
		MailPress::require_class('Mails');
		$x = MP_Mails::get($id);
		$y = array('sent', 'unsent');
		if (!in_array($x->status, $y))
		{
			$this->args = new stdClass();
			$this->args->id	= $id;
			$this->args->main_id	= $main_id;
			if ($theme) $this->args->Theme = $theme;
			$this->args->html = stripslashes($x->html);
			$x->html 		= $this->build_mail_content('html', true);
			$x->themedir 	= $this->mail->themedir;
		}
		echo $this->process_img($x->html, $x->themedir, 'draft');
	}

	function viewplaintext($id, $main_id, $theme = false)
	{
		MailPress::require_class('Mails');
		$x = MP_Mails::get($id);
		$y = array('sent', 'unsent');
		if (!in_array($x->status, $y))
		{
			$this->args = new stdClass();
			$this->args->id		= $id;
			$this->args->main_id	= $main_id;
			if ($theme) $this->args->Theme = $theme;
			$this->args->plaintext 	= stripslashes($x->plaintext);
			$x->plaintext 		= strip_tags($this->build_mail_content('plaintext'));
		}
		include MP_TMP . '/mp-includes/html/plaintext.php';
	}
}
?>