<?php
if (!current_user_can('MailPress_edit_mails')) wp_die(__('You do not have sufficient permissions to access this page.'));

$autosave_data['toemail'] 	= __('To','MailPress'); 
$autosave_data['toname'] 	= __('Name','MailPress'); 
$autosave_data['subject'] 	= __('Subject','MailPress'); 
$autosave_data['html'] 		= __('Html');
$autosave_data['plaintext']	= __('Plain Text','MailPress');

function mp_mail_revision_title( $revision, $link = true, $time = false) 
{
	if ( !$revision = MP_Mail::get( $revision ) ) return $revision;

	$datef = _c( 'j F, Y @ G:i|revision date format','MailPress');
	$autosavef = __( '%s [Autosave]' ,'MailPress');
	$currentf  = __( '%s [Current Revision]' ,'MailPress');

	$gmt_offset = (int) get_option('gmt_offset');
	$sign = '+';
	if ($gmt_offset < 0) 				{$sign = '-'; $gmt_offset = $gmt_offset * -1;}
	if ($gmt_offset < 10) 				$gmt_offset = '0' . $gmt_offset;
	$gmt_offset = 					str_replace('.','',$gmt_offset);
	while (strlen($gmt_offset) < 4) 		$gmt_offset = $gmt_offset . '0';
	$gmt_offset = $sign . $gmt_offset ;

	$time = ($time) ? $time : $revision->created;

	$date = date_i18n( $datef, strtotime( $time . ' ' . $gmt_offset ) );
	if ($link) $date = "<a href='" . clean_url($link) . "'>$date</a>";
	
	if ('' == $revision->status) 	$date = sprintf( $autosavef, $date );
	else					$date = sprintf( $currentf, $date );

	return $date;
}

function mp_list_mail_revisions( $mail_id = 0, $args = null ) { // TODO? split into two functions (list, form-table) ?
	if ( !$mail = MP_Mail::get( $mail_id ) )
		return;

	$defaults = array( 'parent' => false, 'right' => false, 'left' => false, 'format' => 'list', 'type' => 'all' );
	extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

	switch ( $type ) 
	{
		case 'autosave' :
			if ( !$rev_ids = MP_Mailmeta::get($mail->id,'_MailPress_mail_revisions')) return;
			break;
		case 'revision' : // just revisions - remove autosave later
		case 'all' :
		default :
			if ( !$rev_ids = MP_Mailmeta::get($mail->id,'_MailPress_mail_revisions')) return;
			break;
	}

	$titlef = _c( '%1$s by %2$s|mail revision 1:datetime, 2:name','MailPress');

	$rev_ids[0] = $mail->id;
	ksort($rev_ids);

	$rows = '';
	$class = false;

	foreach ( $rev_ids as $k => $rev_id ) 
	{
		if (!$revision = MP_Mail::get( $rev_id ) ) continue;

		$link = ('' == $revision->status) ? MailPress_revision . '&id=' . $mail->id . '&revision=' . $rev_id : MailPress_write . '&id=' . $mail->id;
		$date = mp_mail_revision_title( $rev_id, $link );
		$name = ( $k != 0) ? get_author_name($k) : get_author_name($mail->created_user_id);

		if ( 'form-table' == $format ) {
			if ( $left )
				$left_checked = $left == $rev_id ? ' checked="checked"' : '';
			else
				$left_checked = $right_checked ? ' checked="checked"' : ''; // [sic] (the next one)
			$right_checked = $right == $rev_id ? ' checked="checked"' : '';

			$class = $class ? '' : " class='alternate'";

			if ( $k != 0)
				$actions = '<a href="' . wp_nonce_url( add_query_arg( array( 'page' => MailPress_page_revision, 'action' => 'restore', 'id' => $mail->id, 'revision' => $rev_id ) ), "restore-post_$mail->id|$rev_id" ) . '">' . __( 'Restore','MailPress' ) . '</a>';
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

		global $mp_general;
?>
<form action='<?php echo (isset($mp_general['menu'])) ? 'admin.php' : 'post-new.php'; ?>' method="get">
	<div class="tablenav">
		<div class="alignleft actions">
			<input type="submit" class="button-secondary" value="<?php _e( 'Compare Revisions','MailPress' ); ?>" />
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
				<th scope="col"><?php _e( 'Date Created','MailPress' ); ?></th>
				<th scope="col"><?php _e( 'Author' ,'MailPress'); ?></th>
				<th scope="col" class="action-links"><?php _e( 'Actions','MailPress' ); ?></th>
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

if ( MailPress_page_revision == MP_Admin::get_page()) 	include ('includes/mail_revision.php');
else										include ('includes/mail_new.php');
?>