<?php 
MailPress::require_class('Admin_page_list');

class MP_AdminPage extends MP_Admin_page_list
{
	const screen 	= MailPress_page_mails;
	const capability 	= 'MailPress_edit_mails';
	const help_url	= 'http://www.mailpress.org/wiki/index.php?title=Help:Admin:Mails';

////  Redirect  ////

	public static function redirect() 
	{
		if (empty( $_GET['delete_mails'] )) return;		// MANAGING CHECKBOX REQUESTS

		$deleted = $sent = $notsent = 0;

		$url_parms 	= self::get_url_parms();

		foreach ($_GET['delete_mails'] as $id)
		{ 							
			switch (true)
			{
				case ( isset( $_GET['deleteit'] )) :
					self::require_class('Mails');
					MP_Mails::delete($id);
					$deleted++;
				break;
				case (isset( $_GET['sendit'] )) :
					self::require_class('Mails');
					$x = MP_Mails::send_draft($id);
					$url = (is_numeric($x))	? $sent += $x : $notsent++ ;
				break;
			}
		}
		if ($deleted) 	$url_parms['deleted'] 	= $deleted;
		if ($sent) 		$url_parms['sent'] 	= $sent;
		if ($notsent) 	$url_parms['notsent'] 	= $notsent;
		self::mp_redirect( self::url(MailPress_mails, $url_parms) );
	}

////  Styles  ////

	public static function print_styles($styles = array()) 
	{
		wp_register_style ( self::screen, 		'/' . MP_PATH . 'mp-admin/css/mails.css',       array('thickbox') );

		$styles[] = self::screen;
		parent::print_styles($styles);
	}

//// Scripts ////

	public static function print_scripts() 
	{
		if (has_filter('MailPress_autorefresh_js')) $scripts = apply_filters('MailPress_autorefresh_js', array());

		wp_register_script( 'mp-ajax-response', 	'/' . MP_PATH . 'mp-includes/js/mp_ajax_response.js', array('jquery'), false, 1);
		wp_localize_script( 'mp-ajax-response', 		'wpAjax', array( 
			'noPerm' => __('Email was not sent AND/OR Update database failed', MP_TXTDOM), 
			'broken' => __('An unidentified error has occurred.'), 
			'l10n_print_after' => 'try{convertEntities(wpAjax);}catch(e){};' 
		));

		wp_register_script( 'mp-lists', 		'/' . MP_PATH . 'mp-includes/js/mp_lists.js', array('mp-ajax-response'), false, 1);
		wp_localize_script( 'mp-lists', 			'wpListL10n', array( 
			'url' => MP_Action_url
		));

		wp_register_script( 'mp-thickbox', 		'/' . MP_PATH . 'mp-includes/js/mp_thickbox.js', array('thickbox'), false, 1);

		wp_register_script( self::screen, 		'/' . MP_PATH . 'mp-admin/js/mails.js', array('mp-thickbox', 'mp-lists'), false, 1);
		wp_localize_script( self::screen, 	'MP_AdminPageL10n', array(	
			'pending' => __('%i% pending'), 
			'screen' => self::screen, 
			'l10n_print_after' => 'try{convertEntities(MP_AdminPageL10n);}catch(e){};' 
		));

		$scripts[] = self::screen;
		parent::print_scripts($scripts);
	}

//// Columns ////

	public static function get_columns() 
	{
		$disabled = (!current_user_can('MailPress_delete_mails') && !current_user_can('MailPress_send_mails')) ? " disabled='disabled'" : '';
		$columns = array(	'cb' 		=> "<input type='checkbox'$disabled />", 
					'title' 	=> __('Subject', MP_TXTDOM), 
					'author' 	=> __('Author'), 
					'theme' 	=> __('Theme', MP_TXTDOM), 
					'to' 		=> __('To', MP_TXTDOM), 
					'date'	=> __('Date') );
		$columns = apply_filters('MailPress_columns_mails', $columns);
		return $columns;
	}

//// List ////

	public static function get_list($start, $num, $url_parms) 
	{
		global $wpdb, $user_ID;

		$where = " status <> '' ";
		if (isset($url_parms['s']) && !empty($url_parms['s']))
		{
			$s = trim($wpdb->escape($url_parms['s']));
			if (!empty($where)) $where = $where . ' AND ';
			if ($s) $where .= " ((theme LIKE '%$s%') OR (themedir LIKE '%$s%') OR (template LIKE '%$s%') OR (toemail LIKE '%$s%') OR (toname LIKE '%$s%') OR (subject LIKE '%$s%') OR (html LIKE '%$s%') OR (plaintext LIKE '%$s%') OR (created like '%$s%') OR (sent like '%$s%')) "; 
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

		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM $wpdb->mp_mails  $where ORDER BY created DESC";

		return parent::get_list($start, $num, $query, 'mp_mail');
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

		$edit_url    	= clean_url(self::url( MailPress_edit, array_merge($args, $url_parms) ));

		$args['action'] 	= 'send';
		$send_url 		= clean_url(self::url( MailPress_mail, array_merge($args, $url_parms), "send-mail_$id" ));

		$args['action'] 	= 'delete';
		$delete_url 	= clean_url(self::url( MailPress_mail, array_merge($args, $url_parms), "delete-mail_$id" ));

		unset($args['action']);
		if (apply_filters('MailPress_is_tracking', false)) $tracking_url 	= clean_url(self::url( MailPress_tracking, array_merge($args, $url_parms)));

		$args['action'] 	= 'iview';
		if ('draft' == $mail->status) if (!empty($mail->theme)) $args['theme'] 	= $mail->theme;
		$args['KeepThis'] = 'true'; $args['TB_iframe']= 'true'; $args['width'] = '600'; $args['height']	= '400';
		$view_url		= clean_url(self::url(MP_Action_url, $args));

// actions
		$actions = array();
		$actions['edit']	= "<a href='$edit_url'   	title='" .  __('Edit') . "'>" . __('Edit') . '</a>';
		$actions['send'] 	= "<a href='$send_url' 		class='dim:the-mail-list:mail-$id:unapproved:e7e7d3:e7e7d3:?mode=" . $url_parms['mode'] . "' title='" . __('Send this mail', MP_TXTDOM ) . "'>" . __( 'Send', MP_TXTDOM ) . '</a>';
		if (apply_filters('MailPress_is_tracking', false)) $actions['tracking'] = "<a href='$tracking_url' title='" . __('See tracking results', MP_TXTDOM ) . "'>" 									. __('Tracking', MP_TXTDOM) . '</a>';
		$actions['delete']= "<a href='$delete_url' 	class='delete:the-mail-list:mail-$id submitdelete' title='" . __('Delete this mail', MP_TXTDOM ) . "'>" 	. __('Delete', MP_TXTDOM) . '</a>';
		$actions['view'] 	= "<a href='$view_url' 		class='thickbox'  title='" . __('View', MP_TXTDOM ) . "'>"								. __('View', MP_TXTDOM) . '</a>';

		if (!current_user_can('MailPress_send_mails')) 		unset($actions['send']);
		if (!current_user_can('MailPress_delete_mails')) 	unset($actions['delete']);
		if (!current_user_can('MailPress_tracking_mails')) 	unset($actions['tracking']);

		if ('draft' == $mail->status) 				unset($actions['tracking']);
		else								    { unset($actions['send']); unset($actions['edit']); }

		if ('unsent' == $mail->status) 				unset($actions['delete']);

// table row 
//	class
		$row_class = '';
		if ('draft' == $the_mail_status)  $row_class = 'draft';
		if ('unsent' == $the_mail_status) $row_class = 'unsent';
// 	checkbox
		$disabled = (!current_user_can('MailPress_delete_mails') && !current_user_can('MailPress_send_mails')) ? " disabled='disabled'" : '';
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

				$email_display = '';
				$mail_url2 	    = "<a class='row-title' href='$mail_url'  title='" . sprintf( __('Search "%1$s"', MP_TXTDOM), $mail->toemail) . "'>";
				if ( ('detail' == $url_parms['mode']) && (get_option('show_avatars') ) )
				{
					$email_display .= "<div style='float:left;'>";
					$email_display .= $mail_url2;
					$email_display .= get_avatar( $mail->toemail, 32 );
					$email_display .= '</a>';
					$email_display .= '</div>';
				}
				$email_display .= '<div>';
				$email_display .= $mail_url2;
				$email_display .= '<strong>';
				$email_display .= ( strlen($mail->toemail) > 40 ) ? substr($mail->toemail, 0, 39) . '...' : $mail->toemail;
				$email_display .= '</strong>';
				$email_display .= '</a>';
				if (!empty($mail->toname)) $email_display .= '<br />' . $mail->toname;
				$email_display .= '</div>';



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
			$author_url 	= clean_url(self::url( MailPress_mails . "&author=" . $author, $url_parms ));
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
	<tr id="mail-<?php echo $id; ?>" class='<?php echo $row_class; ?>'>
<?php
		$columns = self::get_columns();
		$hidden  = self::get_hidden_columns();

		foreach ( $columns as $column_name => $column_display_name ) 
		{
			$class = "class='$column_name column-$column_name'";

			$style = '';
			if ('unsent' == $mail->status) 		$style .= 'font-style:italic;';
			if ( in_array($column_name, $hidden) ) 	$style .= 'display:none;';
			$style = ' style="' . $style . '"';

			$attributes = "$class$style";

			switch ($column_name) 
			{

				case 'cb':
					if ('unsent' == $mail->status)
					{
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
			<img class='attach' alt="<?php _e('Attachements', MP_TXTDOM); ?>" title="<?php _e('Attachements', MP_TXTDOM); ?>"  src='<?php echo get_option('siteurl') . '/' . MP_PATH; ?>/mp-admin/images/clip.gif' />
<?php
			endif;
			do_action('MailPress_get_icon_mails', $id);
?>
			<strong>
				<a class='row-title<?php echo ('draft' == $mail->status) ? '' : ' thickbox'; ?>' href='<?php echo ('draft' == $mail->status) ? $edit_url : $view_url; ?>' title='<?php printf( ('draft' == $mail->status) ?  __('Edit "%1$s"', MP_TXTDOM) : __('View "%1$s"', MP_TXTDOM) , ( '' == $subject_display) ? __('(no subject)', MP_TXTDOM) : htmlspecialchars($subject_display, ENT_QUOTES) ); ?>'>
					<?php echo ( '' == $subject_display) ? __('(no subject)', MP_TXTDOM) : (( strlen($subject_display) > 40 ) ? $subject_display = substr($subject_display, 0, 39) . '...' : $subject_display); ?>
				</a>
<?php if ('draft' == $mail->status) echo ' - ' . __('Draft'); ?>
			</strong>
<?php			echo self::get_actions($actions); ?>
		</td>
<?php
				break;
				case 'author':
?>
		<td  <?php echo $attributes ?>>
<?php					if ($author != 0 && is_numeric($author)) { ?>
			<a href='<?php echo $author_url; ?>' title='<?php printf( __('Mails by "%1$s"', MP_TXTDOM), $wp_user->display_name); ?>'><?php echo $wp_user->display_name; ?></a>
<?php 				} else _e("(unknown)", MP_TXTDOM); ?>
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
				case 'to':
?>
		<td  <?php echo $attributes ?>>
<?php echo $email_display; ?>
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
				default:
?>
		<td  <?php echo $attributes ?>>
			<?php	do_action('MailPress_get_row_mails', $column_name, $mail, $url_parms); ?>
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

//// Body ////

	public static function body()
	{
		include (MP_TMP . 'mp-admin/includes/mails.php');
	}
}
?>