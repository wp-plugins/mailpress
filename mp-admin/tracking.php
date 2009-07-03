<?php
require_once(MP_TMP . 'mp-admin/class/MP_Admin_abstract.class.php');

class MP_AdminPage extends MP_Admin_abstract
{
	const screen 	= 'mailpress_tracking';
	const capability 	= 'MailPress_tracking_mails';

////  Xmlns  ////

	public static function admin_xml_ns()
	{
		global $mp_general;
		if (!isset($mp_general['gmapkey']) || empty($mp_general['gmapkey'])) return;
		echo "xmlns:v=\"urn:schemas-microsoft-com:vml\"";
	}

////  Title  ////

	public static function title() { global $title; $title = __('Tracking', 'MailPress'); }

////  Styles  ////

	public static function print_styles($styles = array()) 
	{
		$styles[] = 'dashboard';

		wp_register_style ( 'mp_mail', get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/mails.css', array('thickbox') );
		$styles[] = 'mp_mail';

		wp_register_style ( self::screen, get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/tracking.css' );

		$styles[] = self::screen;
		parent::print_styles($styles);
	}

////  Scripts  ////

	public static function print_scripts() 
	{
		global $mp_general;

		if (isset($mp_general['gmapkey']) && !empty($mp_general['gmapkey']))
		{
			wp_register_script( 'google-map',	'http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=false&amp;key=' . $mp_general['gmapkey'], array(), false, 1);

			$color 	= ('fresh' == get_user_option('admin_color')) ? '' : 'b';
			$pathimg 	= MP_TMP . 'mp-admin/images/controlmap' . $color . '.png';
			$color 	= (is_file($pathimg)) ? $color : '';

			wp_register_script( 'mp-gmap',	'/' . MP_PATH . 'mp-includes/js/mp_gmap.js', array('google-map', 'postbox'), false, 1);
			wp_localize_script( 'mp-gmap', 	'mp_gmapL10n', array(	
				'url'		=> get_option( 'siteurl' ) . '/' . MP_PATH . 'mp-admin/images/', 
				'color'	=> $color, 
				'zoomwide'	=> js_escape(__('zoom -', 'MailPress')), 
				'zoomtight'	=> js_escape(__('zoom +', 'MailPress')), 
				'center'	=> js_escape(__('center', 'MailPress')), 
				'changemap'	=> js_escape(__('change map', 'MailPress'))
			));

			$deps[] = 'mp-gmap';
		}

		wp_register_script( 'mp-thickbox', 		'/' . MP_PATH . 'mp-includes/js/mp_thickbox.js', array('thickbox'), false, 1);
		$deps[] = 'mp-thickbox';

		wp_register_script( self::screen, 		'/' . MP_PATH . 'mp-admin/js/tracking.js', $deps, false, 1);
		wp_localize_script( self::screen, 		'MP_AdminPageL10n',  array(
			'screen' => self::screen
		));

		$scripts[] = self::screen;
		parent::print_scripts($scripts);
	}

////  Metaboxes  ////

	public static function screen_meta() 
	{
		self::require_class('Mails');
		$mail = MP_Mails::get($_GET['id']);
		$tracking = get_option('MailPress_tracking');
		if (!is_array($tracking)) return;
		include (MP_TMP . 'mp-admin/includes/options/tracking/reports.php');
		if ($tracking)
		{
			foreach($tracking as $k => $v)
			{
				if (!isset($tracking_reports['mail'][$k])) continue;
				include(MP_TMP . "mp-admin/includes/options/tracking/$k/$k.php");
				add_meta_box('tracking'.$k.'div', $tracking_reports['mail'][$k]['title'] , "meta_box_tracking_mp_$k", 	self::screen, 'normal', '');
			}
		}

		parent::screen_meta();
	}

//// Columns ////

	public static function get_columns() 
	{
		$columns = array(	'title' 	=> __('Subject', 'MailPress'), 
					'author' 	=> __('Author'), 
					'theme' 	=> __('Theme', 'MailPress'), 
					'to' 		=> __('To', 'MailPress'), 
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
				$email_display .= "\t\t\t\t<a class='row-title' href='$mail_url'  title='" . sprintf( __('Search "%1$s"', 'MailPress'), $mail->toemail) . "'>\n";
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
				$email_display = "<span style='color:red;font-weight:bold;'>" . __('(unknown)', 'MailPress') . '</span>';
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
		$subject_display = $mail->subject;
		if ( strlen($subject_display) > 40 )	$subject_display = substr($subject_display, 0, 39) . '...';
//	attachements
		$attach = false;
		self::require_class('Mailmeta');
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
			<img class='attach' alt="<?php _e('Attachements', 'MailPress'); ?>" title="<?php _e('Attachements', 'MailPress'); ?>"  src='<?php echo get_option('siteurl') . '/' . MP_PATH; ?>/mp-admin/images/clip.gif' />
<?php
			endif;
			do_action('MailPress_get_icon_mails', $id);
?>
			<strong>
				<a class='row-title thickbox' href='<?php echo $view_url; ?>' title='<?php printf( __('View "%1$s"', 'MailPress') , ( '' == $mail->subject) ? __('(no subject)', 'MailPress') : htmlspecialchars($mail->subject, ENT_QUOTES) ); ?>'>
					<?php echo ( '' == $subject_display) ? __('(no subject)', 'MailPress') : $subject_display; ?>
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
					elseif ( $time_diff == 0 )				$h_time = __('now', 'MailPress');
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
<?php 				} else _e("(unknown)", 'MailPress'); ?>
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