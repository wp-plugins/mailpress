<?php

define ('MP_MAIL_REVISIONS', true);

function mp_mail_revision_display_toemail($toemail)
{
	$draft_dest = MP_User::get_mailing_lists();

	if 		(!empty($toemail) && isset($draft_dest[$toemail]))	return $draft_dest[$toemail];
	elseif 	(MailPress::is_email($toemail))				return $toemail;
	else											return '';
}


$x = array('revision', 'id', 'left', 'right', 'action');
foreach($x as $xx) global $$xx;
wp_reset_vars($x);
$revision_id = absint($revision);
$id 		 = absint($id);
$diff        = absint($diff);
$left        = absint($left);
$right       = absint($right);

switch ( $action )
{
	case 'diff' :
		$left_revision  	= MP_Mail::get( $left );
		$right_revision 	= MP_Mail::get( $right);
		$mail    		= MP_Mail::get($id);

		$edit_url  = MailPress_edit . '&id=' . $id;
		$edit_url  = clean_url($edit_url);
		$mail_title = '<a href="' . $edit_url . '">' . $mail->subject . '</a>';
		$h2 = sprintf( __( 'Compare Revisions of &#8220;%1$s&#8221;','MailPress' ), $mail_title );

		$left  = $left_revision->id;
		$left_link = ('' == $left_revision->status) ? MailPress_revision . '&id=' . $mail->id . '&revision=' . $left : MailPress_edit . '&id=' . $left;

		$right = $right_revision->id;
		$right_link = ('' == $revision->status) ? MailPress_revision . '&id=' . $mail->id . '&revision=' . $right : MailPress_edit . '&id=' . $right;

		$date = mp_mail_revision_title( $rev_id, $link );
	break;
	default :
		$revision 	= MP_Mail::get( $revision_id );
		$mail 	= MP_Mail::get( $id );

		$edit_url  = clean_url(MailPress_edit . '&id=' . $id);
		$mail_title = '<a href="' . $edit_url . '">' . $mail->subject . '</a>';

		$revision_title = mp_mail_revision_title( $revision_id , false );
		$h2 = sprintf( __( 'Mail Revision for &#8220;%1$s&#8221; created on %2$s','MailPress' ), $mail_title, $revision_title );

		$left  = $revision->id;
		$right = $mail->id;
	break;
}
?>
<div class="wrap">
	<div id="icon-mailpress-mailnew" class="icon32"><br /></div>
	<h2 class="long-header">
		<?php echo $h2; ?>
	</h2>
	<table class="form-table ie-fixed">
		<col class="th" />
<?php if ( 'diff' == $action ) : ?>
		<tr id="revision">
			<th scope="row"></th>
			<th scope="col" class="th-full">
				<span class="alignleft"><?php  printf( __('Older: %s','MailPress'), mp_mail_revision_title( $left_revision,$left_link ) ); ?></span>
				<span class="alignright"><?php printf( __('Newer: %s','MailPress'), mp_mail_revision_title( $right_revision,$right_link ) ); ?></span>
			</th>
		</tr>
<?php endif;
$identical = true;
foreach ( $autosave_data as $field => $field_title ) :
	if ( 'diff' == $action ) 
	{
		add_filter('_mp_mail_revision_field_toemail','mp_mail_revision_display_toemail',8,1);

		$left_content  = apply_filters("_mp_mail_revision_field_$field", $left_revision->$field,  $field );
		$right_content = apply_filters("_mp_mail_revision_field_$field", $right_revision->$field, $field );
		if ( !$content = wp_text_diff( $left_content, $right_content ) )
			continue; // There is no difference between left and right
		$identical = false;
	} 
	else 
	{
		add_filter("_mp_mail_revision_field_$field", 'htmlspecialchars' );
		$content = ('toemail' == $field) ? mp_mail_revision_display_toemail($revision->$field) : apply_filters("_mp_mail_revision_field_$field", $revision->$field, $field );
	}
	?>

	<tr id="revision-field-<?php echo $field; ?>">
		<th scope="row"><?php echo wp_specialchars( $field_title ); ?></th>
		<td><div class="pre"><?php echo $content; ?></div></td>
	</tr>

	<?php

endforeach;

if ( 'diff' == $action && $identical ) :

	?>

	<tr><td colspan="2"><div class="updated"><p><?php _e( 'These revisions are identical.' ,'MailPress'); ?></p></div></td></tr>

	<?php

endif;

?>
	</table>
	<br class="clear" />
	<h2><?php echo $title; ?></h2>
<?php
$args = array( 'format' => 'form-table', 'parent' => true, 'right' => $right, 'left' => $left, 'type' => 'autosave' );
mp_list_mail_revisions( $mail, $args );
?>
</div>