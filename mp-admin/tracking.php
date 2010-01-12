<?php
MailPress::require_class('Admin_page');

class MP_AdminPage extends MP_Admin_page
{
	const screen 	= 'mailpress_tracking';
	const capability 	= 'MailPress_tracking_mails';
	const help_url	= 'http://www.mailpress.org/wiki/index.php?title=Add_ons:Tracking';

////  Xmlns  ////

	public static function admin_xml_ns()
	{
		MailPress::require_class('Tracking_modules');
		$MP_Tracking_modules = new MP_Tracking_modules('mail');

		global $mp_general;
		if (isset($mp_general['gmapkey']) && !empty($mp_general['gmapkey'])) echo "xmlns:v=\"urn:schemas-microsoft-com:vml\"";
	}

////  Title  ////

	public static function title() { global $title; $title = __('Tracking', MP_TXTDOM); }

////  Styles  ////

	public static function print_styles($styles = array()) 
	{
		$styles[] = 'dashboard';

		wp_register_style ( 'mp_mail', 	'/' . MP_PATH . 'mp-admin/css/mails.css', array('thickbox') );
		$styles[] = 'mp_mail';

		wp_register_style ( self::screen, 	'/' . MP_PATH . 'mp-admin/css/tracking.css' );

		$styles[] = self::screen;
		parent::print_styles($styles);
	}

////  Scripts  ////

	public static function print_scripts() 
	{
		wp_register_script( 'mp-thickbox', 		'/' . MP_PATH . 'mp-includes/js/mp_thickbox.js', array('thickbox'), false, 1);

		$every   = 30;
		$checked = (isset($_GET['autorefresh'])) ?  " checked='checked'" : '';
		$time    = (isset($_GET['autorefresh'])) ?  $_GET['autorefresh'] : $every;
		$time    = (is_numeric($time) && ($time > $every)) ? $time : $every;
		$time    = "<input type='text' value='$time' maxlength='3' id='MP_Refresh_every' class='screen-per-page'/>";
		$option  = '<h5>' . __('Auto refresh', MP_TXTDOM) . '</h5>';
		$option .= "<div><input id='MP_Refresh' type='checkbox'$checked style='margin:0 5px 0 2px;' /><span class='MP_Refresh'>" . sprintf(__('%1$s Autorefresh %2$s every %3$s sec', MP_TXTDOM), "<label for='MP_Refresh' style='vertical-align:inherit;'>", '</label>', $time) . "</span></div>";

		wp_register_script( 'mp-refresh', 	'/' . MP_PATH . 'mp-includes/js/mp_refresh.js', array('schedule'), false, 1);
		wp_localize_script( 'mp-refresh', 	'adminMpRefreshL10n', array(
				'every' 	=> $every,
				'message' 	=> __('Autorefresh in %i% sec', MP_TXTDOM), 
				'option'	=> $option,
				'l10n_print_after' => 'try{convertEntities(adminmailsL10n);}catch(e){};'
		) );

		wp_register_script( self::screen, 		'/' . MP_PATH . 'mp-admin/js/tracking.js', array('mp-thickbox', 'mp-refresh', 'postbox'), false, 1);
		wp_localize_script( self::screen, 		'MP_AdminPageL10n',  array(
			'screen' => self::screen
		));

		$scripts[] = self::screen;

		parent::print_scripts($scripts);
	}

////  Metaboxes  ////

	public static function screen_meta() 
	{
		do_action('MailPress_tracking_add_meta_box', self::screen);
		parent::screen_meta();
	}

//// Columns ////

	public static function get_columns() 
	{
		$columns = array(	'title' 	=> __('Subject', MP_TXTDOM), 
					'author' 	=> __('Author'), 
					'theme' 	=> __('Theme', MP_TXTDOM), 
					'to' 		=> __('To', MP_TXTDOM), 
					'date'	=> __('Date') );
		$columns = apply_filters('MailPress_columns_mails', $columns);
		return $columns;
	}

	public static function columns_list($id = true)
	{
		$columns = self::get_columns();
		$hidden  = array();
		foreach ( $columns as $key => $display_name ) 
		{
			$thid  = ( $id ) ? " id='$key'" : '';
			$class = ( 'cb' === $key ) ? " class='check-column'" : " class='manage-column column-$key'";
			$style = ( in_array($key, $hidden) ) ? " style='display:none;'" : '';

			echo "<th scope='col'$thid$class$style>$display_name</th>";
		} 
	}

////  Row  ////

	public static function get_row( $id, $url_parms, $xtra = false) 
	{
		global $mp_mail;

		self::require_class('Mails');
		$mp_mail = $mail = MP_Mails::get( $id );
		$the_mail_status = $mail->status;

// url's
		$args = array();
		$args['id'] 	= $id;
		$args['action'] 	= 'iview';
		$args['KeepThis'] = 'true'; $args['TB_iframe']= 'true'; $args['width'] = '600'; $args['height']	= '400';
		$view_url		= clean_url(self::url(MP_Action_url, $args));

// table row 
//	to
		self::require_class('Users');
		$draft_dest = MP_Users::get_mailinglists();

		switch (true)
		{
			case ($xtra) :
				$email_display = "<blink style='color:red;font-weight:bold;'>" . $xtra . '</blink>';
			break;
			case (self::is_email($mail->toemail)) :
				$mail_url = self::url(MailPress_mails, $url_parms);
				$mail_url = remove_query_arg('s', $mail_url);
				$mail_url = clean_url( $mail_url . '&s=' . $mail->toemail );

				$email_display  = "\t\t\t<strong>\n";
				$email_display .= "\t\t\t\t<a class='row-title' href='$mail_url'  title='" . sprintf( __('Search "%1$s"', MP_TXTDOM), $mail->toemail) . "'>\n";
				if ( get_option('show_avatars') ) $email_display .= get_avatar( $mail->toemail, 32 );
				$email_display .= ( strlen($mail->toemail) > 40 ) ? substr($mail->toemail, 0, 39) . '...' : $mail->toemail;
				$email_display .= "\t\t\t\t</a>\n";
				$email_display .= "\t\t\t</strong>\n";
			break;
			case (isset($draft_dest[$mail->toemail])) :
				$email_display = "<strong>" . $draft_dest[$mail->toemail] . "</strong>";
			break;
			case (is_serialized($mail->toemail)) :
				$email_display = "<div class='num post-com-count-wrapper'><a class='post-com-count'><span class='comment-count'>" . count(unserialize($mail->toemail)) . "</span></a></div>"; 
			break;
			default  :
				$email_display = "<span style='color:red;font-weight:bold;'>" . __('(unknown)', MP_TXTDOM) . '</span>';
				unset($actions['send']);
			break;
		}
		$email_display = apply_filters('MailPress_to_mails_column', $email_display, $mail);
//	author
		$author = ( 0 == $mail->sent_user_id) ? $mail->created_user_id : $mail->sent_user_id;
		if ($author != 0 && is_numeric($author)) 
		{
			unset($url_parms['author']);
			$wp_user 		= get_userdata($author);
		}
//	subject
		self::require_class('Mailmeta');
		$metas = MP_Mailmeta::get( $id, '_MailPress_replacements');
		$subject_display = $mail->subject;
		if ($metas) foreach($metas as $k => $v) $subject_display = str_replace($k, $v, $subject_display);
//	attachements
		$attach = false;
		$metas = MP_Mailmeta::has( $id, '_MailPress_attached_file');
		if ($metas)
		{
			foreach($metas as $meta)
			{
				$meta_value = unserialize( $meta['meta_value'] );
				if (is_file($meta_value['file_fullpath']))
				{
					$attach = true;
					break;
				}
			}
		}
?>
	<tr id="mail-<?php echo $id; ?>">
<?php
		$columns = self::get_columns();

		foreach ( $columns as $column_name => $column_display_name ) 
		{
			$class = "class='$column_name column-$column_name'";
			$style = '';
			if ('unsent' == $mail->status) 		$style .= 'font-style:italic;';
			$style = ' style="' . $style . '"';

			$attributes = "$class$style";

			switch ($column_name) 
			{
				case 'title':
					$attributes = 'class="post-title column-title"' . $style;
?>
		<td  <?php echo $attributes ?>>
<?php
			if ($attach) :
?>
			<img class='attach' alt="<?php _e('Attachements', MP_TXTDOM); ?>" title="<?php _e('Attachements', MP_TXTDOM); ?>"  src='<?php echo get_option('siteurl') . '/' . MP_PATH; ?>/mp-admin/images/clip.gif' />
<?php
			endif;
			do_action('MailPress_get_icon_mails', $id);
?>
			<strong>
				<a class='row-title thickbox' href='<?php echo $view_url; ?>' title='<?php printf( __('View "%1$s"', MP_TXTDOM) , ( '' == $subject_display) ? __('(no subject)', MP_TXTDOM) : htmlspecialchars($subject_display, ENT_QUOTES) ); ?>'>
					<?php echo ( '' == $subject_display) ? __('(no subject)', MP_TXTDOM) : (( strlen($subject_display) > 40 ) ? $subject_display = substr($subject_display, 0, 39) . '...' : $subject_display); ?>
				</a>
			</strong>
		</td>
<?php
				break;
				case 'date':

					$t_time = self::get_mail_date(__('Y/m/d g:i:s A'));
					$m_time = self::get_mail_date_raw();
					$time   = self::get_mail_date('U');

					$time_diff = time() - $time; 

					if ( $time_diff > 0 && $time_diff < 24*60*60 )	$h_time = sprintf( __('%s ago'), human_time_diff( $time ) );
					elseif ( $time_diff == 0 )				$h_time = __('now', MP_TXTDOM);
					else								$h_time = mysql2date(__('Y/m/d'), $m_time);
					
?>
		<td  <?php echo $attributes ?>>
			<abbr title="<?php echo $t_time; ?>"><?php echo $h_time; ?></abbr>
		</td>
<?php
				break;
				case 'author':
?>
		<td  <?php echo $attributes ?>>
<?php					if ($author != 0 && is_numeric($author)) { ?>
			<?php echo $wp_user->display_name; ?>
<?php 				} else _e("(unknown)", MP_TXTDOM); ?>
		</td>
<?php
				break;
				case 'to':
?>
		<td  <?php echo $attributes ?>>
<?php echo $email_display; ?>
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
			<?php	do_action('MailPress_get_row_mails', $column_name, $mail, array()); ?>
		</td>
<?php
				break;
			}
		}
?>
	  </tr>
<?php
	}

	public static function mail_date($d = '') {
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

////  Body  ////

	public static function body()
	{
		include (MP_TMP . 'mp-admin/includes/tracking.php');
	}
}
?>