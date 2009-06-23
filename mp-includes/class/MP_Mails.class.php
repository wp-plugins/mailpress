<?php

class MP_Mails extends MP_abstract
{

	public static function get($mail, $output = OBJECT) 
	{
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
					if ($_mail) wp_cache_add($_mail->id, $_mail, 'mp_mail');
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

		do_action('MailPress_delete_mail', $id);

		self::require_class('Mailmeta');
		MP_Mailmeta::delete( $id );
		$query = "DELETE FROM $wpdb->mp_mails WHERE id = $id ; ";
		return $wpdb->query( $query );
	}


/// DRAFT ///


	public static function get_id($from='inconnu')
	{
		global $wpdb;
		$x 	= md5(uniqid(rand(), 1));
		$now	= date('Y-m-d H:i:s');
		$wp_user = MailPress::get_wp_user_id();

		$query = "INSERT INTO $wpdb->mp_mails (status, theme, themedir, template, fromemail, fromname, toemail, toname, subject, html, plaintext, created, created_user_id, sent, sent_user_id ) values ('', '$x', '', '', '', '', '', '', '$from', '', '', '$now', $wp_user, '0000-00-00 00:00:00', 0);";
		$wpdb->query( $query );
		return $wpdb->get_var( "SELECT id FROM $wpdb->mp_mails WHERE theme = '$x' ;" );
	}

	public static function update_draft($id, $status='draft')
	{
		global $wpdb;

		wp_cache_delete($id, 'mp_mail');

// process attachements
		if (isset($_POST['type_of_upload']))
		{
			$attach = '';
			if (isset($_POST['Files'])) foreach ($_POST['Files'] as $k => $v) if (is_numeric($k)) $attach .= (empty($attach)) ? "$k" : ", $k";
			$query = (empty($attach)) ? "SELECT mmeta_id FROM $wpdb->mp_mailmeta WHERE mail_id = $id AND meta_key = '_MailPress_attached_file';" : "SELECT mmeta_id FROM $wpdb->mp_mailmeta WHERE mail_id = $id AND meta_key = '_MailPress_attached_file' AND mmeta_id NOT IN ($attach);";
			$file_exits = $wpdb->get_results($query);
			self::require_class('Mailmeta');
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
		$query = "UPDATE $wpdb->mp_mails SET status = '$status', theme = '" . trim($_POST['Theme']) . "', toemail = '" . trim($_POST['toemail']) . "', toname = '" . trim($_POST['toname']) . "', subject = '" . trim($_POST['subject']) . "', html = '" . trim($_POST['html']) . "', plaintext = '" . trim($_POST['plaintext']) . "', created = '$now', created_user_id = " . MailPress::get_wp_user_id() . " WHERE id = $id ;";
		return $wpdb->query( $query );
	}

	public static function send_draft($id = NULL, $ajax = false, $_toemail = false, $_toname = false) {

		if (NULL == $id) return false;
		$draft = self::get($id);
		if ('draft' != $draft->status) return false;

// so we duplicate the draft into a new mail
		$mail = (object) null;

		$mail->id = self::get_id(__CLASS__ . ' ' . __METHOD__);
		$mail->main_id	= $id;

		if (!empty($draft->theme)) $mail->Theme = $draft->theme;

		if ($_toemail)
		{
			$mail->toemail	= $_toemail;
			$mail->toname	= ($_toname) ? $_toname : '';
		}
		else
		{
			$query = self::get_query_mailinglist($draft->toemail);
			if ($query)
			{
				$mail->recipients_query = $query;
			}
			else
			{
				if 	(!self::is_email($draft->toemail)) return 'y';
				$mail->toemail	= $draft->toemail;
				$mail->toname	= stripslashes($draft->toname);
			}
		}

		$mail->subject	= stripslashes($draft->subject);

		$mail->html		= stripslashes($draft->html);

		$mail->plaintext	= stripslashes($draft->plaintext);

		$mail->draft 	= true;

		$count = MailPress::mail($mail);

		if (0 === $count)		return 'x'; // no recipient
		if (!$count) return 0;			// something wrong !

		if ($ajax) 	return array($mail->id);
		return $count;
	}

//// Recipients queries ////

	public static function get_query_mailinglist($draft_toemail)
	{
		global $wpdb;

		$query = false;
		switch ($draft_toemail)
		{
			case '1' :
				$query  = "SELECT id, email, name, status, confkey FROM $wpdb->mp_users WHERE status = 'active';";
			break;
			case '2' :
				$query  = "SELECT DISTINCT id, email, name, status, confkey FROM $wpdb->mp_users a, $wpdb->postmeta b WHERE a.id = b.meta_value and b.meta_key = '_MailPress_subscribe_to_comments_';";
			break;
			case '3' :
				$query  = "SELECT id, email, name, status, confkey FROM $wpdb->mp_users WHERE status = 'active'";
				$query .= " UNION ";
				$query .= "SELECT DISTINCT id, email, name, status, confkey FROM $wpdb->mp_users a, $wpdb->postmeta b WHERE a.id = b.meta_value and a.status = 'waiting' and b.meta_key = '_MailPress_subscribe_to_comments_';";
			break;
			case '4' :
				$query  = "SELECT id, email, name, status, confkey FROM $wpdb->mp_users ;";
			break;
			default :
				if (has_filter('MailPress_query_mailinglist')) $query = apply_filters('MailPress_query_mailinglist', $draft_toemail);
			break;
		}
		return $query;
	}

/// DISPLAYING E-MAILS & NAMES ///

	public static function display_toemail($toemail, $toname, $tolist='')
	{
		$return = '';
		self::require_class('Users');
		$draft_dest = MP_Users::get_mailinglists();

		if 		(!empty($tolist)  && isset($draft_dest[$tolist]))	return "<b>" . $draft_dest[$tolist] . "</b>"; 
		elseif 	(!empty($toemail) && isset($draft_dest[$toemail]))	return "<b>" . $draft_dest[$toemail] . "</b>"; 
		elseif 	(self::is_email($toemail))
		{
				return self::display_name_email($toname, $toemail);
		}
		else
		{
			$y = unserialize($toemail);
			unset($y['MP_Mail']);
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
		return self::display_name($name, false) . " &lt;$email&gt;";
	}

	public static function display_name($name, $for_mail=true)
	{
		$default = '_';
		if ( self::is_email($name) )	$name = trim(str_replace('.', ' ', substr($name, 0, strpos($name, '@'))));
		if ( $for_mail ) 
		{ if ( empty($name) ) 	$name = $default; }
		else
		{ if ($default == $name)$name = '';}
		return $name;									
	}


//// Write ////

	public static function autosave_data()
	{
		$autosave_data['toemail'] 	= __('To', 'MailPress'); 
		$autosave_data['toname'] 	= __('Name', 'MailPress'); 
		$autosave_data['theme']		= __('Theme', 'MailPress');
		$autosave_data['subject'] 	= __('Subject', 'MailPress'); 
		$autosave_data['html'] 		= __('Html');
		$autosave_data['plaintext']	= __('Plain Text', 'MailPress');
		return $autosave_data;
	}

	public static function check_mail_lock( $id ) 
	{
		global $current_user;

		if ( !$mail = self::get( $id ) ) return false;

		self::require_class('Mailmeta');
		$lock = MP_Mailmeta::get( $id, '_edit_lock' );
		$last = MP_Mailmeta::get( $id, '_edit_last' );
		$time_window = AUTOSAVE_INTERVAL * 2 ;

		if ( $lock && $lock > time() - $time_window && $last != $current_user->ID )	return $last;
		return false;
	}

	public static function set_mail_lock( $id ) 
	{
		global $current_user;
		if ( !$mail = self::get( $id ) )			return false;
		if ( !$current_user || !$current_user->ID )	return false;

		$now = time();

		self::require_class('Mailmeta');
		MP_Mailmeta::update( $mail->id, '_edit_lock', $now );
		MP_Mailmeta::update( $mail->id, '_edit_last', $current_user->ID );
	}

////  Revisions ////

	public static function mail_revision_title( $revision, $link = true, $time = false) 
	{
		if ( !$revision = self::get( $revision ) ) return $revision;

		$datef = _c( 'j F, Y @ G:i|revision date format', 'MailPress');
		$autosavef = __( '%s [Autosave]' , 'MailPress');
		$currentf  = __( '%s [Current Revision]' , 'MailPress');

		$gmt_offset = (int) get_option('gmt_offset');
		$sign = '+';
		if ($gmt_offset < 0) 				{$sign = '-'; $gmt_offset = $gmt_offset * -1;}
		if ($gmt_offset < 10) 				$gmt_offset = '0' . $gmt_offset;
		$gmt_offset = 					str_replace('.', '', $gmt_offset);
		while (strlen($gmt_offset) < 4) 		$gmt_offset = $gmt_offset . '0';
		$gmt_offset = $sign . $gmt_offset ;

		$time = ($time) ? $time : $revision->created;

		$date = date_i18n( $datef, strtotime( $time . ' ' . $gmt_offset ) );
		if ($link) $date = "<a href='" . clean_url($link) . "'>$date</a>";
	
		if ('' == $revision->status) 	$date = sprintf( $autosavef, $date );
		else					$date = sprintf( $currentf, $date );

		return $date;
	}

	public static function list_mail_revisions( $mail_id = 0, $args = null ) 
	{
		if ( !$mail = self::get( $mail_id ) ) return;

		$defaults = array( 'parent' => false, 'right' => false, 'left' => false, 'format' => 'list', 'type' => 'all' );
		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

		switch ( $type ) 
		{
			case 'autosave' :
				self::require_class('Mailmeta');
				if ( !$rev_ids = MP_Mailmeta::get($mail->id, '_MailPress_mail_revisions')) return;
				break;
			case 'revision' : // just revisions - remove autosave later
			case 'all' :
			default :
				self::require_class('Mailmeta');
				if ( !$rev_ids = MP_Mailmeta::get($mail->id, '_MailPress_mail_revisions')) return;
				break;
		}

		$titlef = _c( '%1$s by %2$s|mail revision 1:datetime, 2:name', 'MailPress');

		$rev_ids[0] = $mail->id;
		ksort($rev_ids);

		$rows = '';
		$class = false;

		foreach ( $rev_ids as $k => $rev_id ) 
		{
			if (!$revision = self::get( $rev_id ) ) continue;

			$link = ('' == $revision->status) ? MailPress_revision . '&id=' . $mail->id . '&revision=' . $rev_id : MailPress_write . '&id=' . $mail->id;
			$date = self::mail_revision_title( $rev_id, $link );
			$name = ( $k != 0) ? get_author_name($k) : get_author_name($mail->created_user_id);

			if ( 'form-table' == $format ) {
				if ( $left )
					$left_checked = $left == $rev_id ? ' checked="checked"' : '';
				else
					$left_checked = $right_checked ? ' checked="checked"' : ''; // [sic] (the next one)
				$right_checked = $right == $rev_id ? ' checked="checked"' : '';

				$class = $class ? '' : " class='alternate'";

				if ( $k != 0)
					$actions = '<a href="' . wp_nonce_url( add_query_arg( array( 'page' => MailPress_page_revision, 'action' => 'restore', 'id' => $mail->id, 'revision' => $rev_id ) ), "restore-post_$mail->id|$rev_id" ) . '">' . __( 'Restore', 'MailPress' ) . '</a>';
				else
					$actions = '';

				$rows .= "<tr$class>\n";
				$rows .= "\t<th style='white-space: nowrap' scope='row'><input type='radio' name='left' value='$rev_id'$left_checked /><input type='radio' name='right' value='$rev_id'$right_checked /></th>\n";
				$rows .= "\t<td>$date</td>\n";
				$rows .= "\t<td>$name</td>\n";
				$rows .= "\t<td class='action-links'>$actions</td>\n";
				$rows .= "</tr>\n";
			} else {
				if ($k != 0)
				{
					$title = sprintf( $titlef, $date, $name );
					$rows .= "\t<li>$title</li>\n";
				}
			}
		}
	
		if ( 'form-table' == $format ) : 

?>
<form action='admin.php' method="get">
	<div class="tablenav">
		<div class="alignleft actions">
			<input type="submit" class="button-secondary" value="<?php _e( 'Compare Revisions', 'MailPress' ); ?>" />
			<input type="hidden" name="page"   value="<?php echo MailPress_page_mails; ?>" />
			<input type="hidden" name="file"   value="revision" />
			<input type="hidden" name="action" value="diff" />
			<input type="hidden" name="id"     value="<?php echo $mail->id; ?>" />
		</div>
	</div>
	<br class="clear" />
	<table class="widefat post-revisions">
		<col />
		<col style="width: 33%" />
		<col style="width: 33%" />
		<col style="width: 33%" />
		<thead>
			<tr>
				<th scope="col"></th>
				<th scope="col"><?php _e( 'Date Created', 'MailPress' ); ?></th>
				<th scope="col"><?php _e( 'Author' , 'MailPress'); ?></th>
				<th scope="col" class="action-links"><?php _e( 'Actions', 'MailPress' ); ?></th>
			</tr>
		</thead>
		<tbody>
<?php echo $rows; ?>
		</tbody>
	</table>
</form>
<?php
		else :
			echo "<ul class='post-revisions'>\n";
			echo $rows;
			echo "</ul>";
		endif;
	}

//// attachements

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

		$args = 
		$href = clean_url(add_query_arg( array('action' => 'attach_download', 'id' => $id), MP_Action_url ));
		return array('id'=>$id, 'url'=>$href, 'file'=>$fx);
	}

	public static function get_attachement_link($meta, $mail_status)
	{
		$meta_value = unserialize( $meta['meta_value'] );
		$href = clean_url(add_query_arg( array('action' => 'attach_download', 'id' => $meta['mmeta_id']), MP_Action_url ));

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

	public static function insert_attachment($object, $file = false, $draft_id) 
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
			self::require_class('Mailmeta');
			return MP_Mailmeta::add( $draft_id, '_MailPress_attached_file', $object );
		}
		return $draft_id;
	}
}
?>