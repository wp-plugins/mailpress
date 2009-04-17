<?php
if (!current_user_can('MailPress_edit_mails')) wp_die(__('You do not have sufficient permissions to access this page.'));

global $wpdb;

function mails_columns($id=true)
{
	global $mp_screen ;
	$mails_columns = MP_Mail::manage_list_columns();
	$hidden = (array) get_user_option( "manage" . $mp_screen . "columnshidden" );
	foreach ( $mails_columns as $mail_column_key => $column_display_name ) {
		if ( 'cb' === $mail_column_key )
			$class = ' class="check-column"';
		else
			$class = " class=\"manage-column column-$mail_column_key\"";

		$style = '';
		if ( in_array($mail_column_key, $hidden) )
			$style = ' style="display:none;"';
?>
					<th scope="col" <?php if ($id) echo "id=\"$mail_column_key\""; echo $class; echo $style?>><?php echo $column_display_name; ?></th>
<?php	} 
}

unset($fade);
$url_parms = MP_Admin::get_url_parms();
switch (true)
{
	case (isset($url_parms['status']) && 'draft' == $url_parms['status']) :
		$h2 = sprintf(__('Drafts (<a href="%s">Add New</a>)','MailPress'), clean_url(MailPress_write));
	break;
	case (isset($url_parms['status']) && 'sent' == $url_parms['status']) :
		$h2 = sprintf(__('Mails sent (<a href="%s">Add New</a>)','MailPress'), clean_url(MailPress_write));
	break;
	default :
		$h2 = sprintf(__('Mails (<a href="%s">Add New</a>)','MailPress'), clean_url(MailPress_write));
	break;
}
$h2 = __('Edit Mails','MailPress');
$h2_author = '';
if (isset($url_parms['author'])) 
{
	$author_user = get_userdata( $url_parms['author'] );
	$h2_author = ' ' . sprintf(__('by %s'), wp_specialchars( $author_user->display_name ));
}

//
// MANAGING CHECKBOX RESULTS
//
if ( isset($_GET['deleted']) || isset($_GET['sent']) || isset($_GET['notsent']) || isset($_GET['saved']) ) 
{
	$deleted   	= isset( $_GET['deleted'] )  	? (int) $_GET['deleted']   	: 0;
	$sent		= isset( $_GET['sent'] ) 	? (int) $_GET['sent'] 		: 0;
	$notsent	= isset( $_GET['notsent'] ) 	? (int) $_GET['notsent'] 	: 0;
	$saved   	= isset( $_GET['saved'] )  	? (int) $_GET['saved']   	: 0;

	if ($deleted > 0  || $sent > 0  || $notsent > 0 || $saved > 0 ) 
	{
		if ( $deleted > 0 ) 
		{
			$fade .= sprintf( __ngettext( __('%s mail deleted', 'MailPress'), __('%s mails deleted', 'MailPress'), $deleted ), $deleted );
			$fade .=  '<br />';
		}
		if ( $sent > 0 ) 
		{
			$fade .= sprintf( __ngettext( __('%s mail sent', 'MailPress'), __('%s mails sent', 'MailPress'), $sent ), $sent );
			$fade .=  '<br />';
		}
		if ( $notsent > 0 ) 
		{
			$fade .= sprintf( __ngettext( __('%s mail NOT sent', 'MailPress'), __('%s mails NOT sent', 'MailPress'), $notsent ), $notsent );
			$fade .=  '<br />';
		}
		if ( $saved > 0 ) 
		{
			$fade .=	__('Mail saved', 'MailPress');
			$fade .=  '<br />';
			$err   = true;
		}
	}
}

//
// MANAGING SUBSUBSUB URL
//
$status_links 	= array();
$where =  (!current_user_can('MailPress_edit_others_mails')) ? "AND created_user_id = " . $user_ID : '';
$num_mails 		= $wpdb->get_var("SELECT count(*) FROM $wpdb->mp_mails WHERE status = 'draft' $where;");

$stati = array('sent' => __('Sent','MailPress'),'draft' => sprintf(__('Draft (%s)','MailPress'), "<span class='mail-count'>$num_mails</span>"));
$class		= ( empty($url_parms['status']) ) ? ' class="current"' : '';
$status_links[] 	= "	<li><a href=\"" . MailPress_mails . "&amp;mode=" . $url_parms['mode'] . "\"$class>".__('All Mails','MailPress')."</a>";
foreach ( $stati as $status => $label ) {
	$class = '';

	if ( $status == $url_parms['status'] ) $class = ' class="current"';

	$status_links[] = "	<li><a href=\"" . MailPress_mails . "&amp;status=$status&amp;mode=" . $url_parms['mode'] . "\"$class>" . $label . '</a>';
}
$subsubsub_urls = implode(' | </li>', $status_links) . '</li>';
unset($status_links);

//
// MANAGING DETAIL/LIST URL
//
$wmode = $url_parms['mode'];
$url_parms['mode'] = 'detail';
$detail_url = clean_url(MP_Admin::url( MailPress_mails, false ,$url_parms ));
$url_parms['mode'] = 'list';
$list_url  	= clean_url(MP_Admin::url( MailPress_mails, false ,$url_parms ));
$url_parms['mode'] = $wmode;
unset($wmode);

//
// MANAGING PAGINATION
//
$url_parms['apage']		= isset($_GET['apage'])		? $_GET['apage'] : 1;

do
{
	$start = ( $url_parms['apage'] - 1 ) * 20;
	list($_mails, $total) = MP_Mail::get_list( $url_parms, $start, 25 ); // Grab a few extra
	$url_parms['apage']--;		
} while ( $total <= $start );
$url_parms['apage']++;

$mails 		= array_slice($_mails, 0, 20);
$extra_mails 	= array_slice($_mails, 20);

$page_links = paginate_links	(array(
						'base' => add_query_arg( 'apage', '%#%' ),
						'format' => '',
						'total' => ceil($total / 20),
						'current' => $url_parms['apage']
						)
					);
if ($url_parms['apage'] == 1) unset($url_parms['apage']);

?>
<?php if (isset($fade)) MP_Admin::message($fade); ?>
<div class='wrap'>
	<div id="icon-mailpress-mails" class="icon32"><br /></div>
	<h2><?php echo $h2; ?></h2>
	<ul class='subsubsub'>

<?php echo $subsubsub_urls; ?>

	</ul>
	<form id='search-form' action='' method='get'>
		<p id='post-search' class='search-box'>
			<input type='text' id='mail-search-input' name='s' value='<?php echo $url_parms['s']; ?>' class="search-input" />
			<input type='submit' value='<?php _e( 'Search mails','MailPress' ); ?>' class='button' />
		</p>
		<input type='hidden' name='page' value='<?php echo MailPress_page_mails; ?>' />
		<input type='hidden' name='mode' value='<?php echo $url_parms['mode']; ?>' />
		<input type='hidden' name='status' value='<?php echo $url_parms['status']; ?>' />
	</form>
	<form id='posts-filter' action='' method='get'>
		<input type='hidden' name='page' value='<?php echo MailPress_page_mails; ?>' />
		<input type='hidden' name='mode' value='<?php echo $url_parms['mode']; ?>' />
		<input type='hidden' name='status' value='<?php echo $url_parms['status']; ?>' />

		<div class='tablenav'>
			<div class='alignleft actions'>
<?php if ( 'sent' != $url_parms['status'] ): ?>
				<?php if (current_user_can('MailPress_send_mails')) : ?><input type='submit' value='<?php _e('Send','MailPress'); ?>' 		name='sendit'	  class='button-secondary action' /><?php endif; ?>
				<?php if (current_user_can('MailPress_delete_mails')) : ?><input type='submit' value='<?php _e('Delete','MailPress'); ?>' 	name='deleteit'     class='button-secondary action' /><?php endif; ?>
<?php else : ?>
				<?php if (current_user_can('MailPress_delete_mails')) : ?><input type='submit' value='<?php _e('Delete','MailPress'); ?>' 	name='deleteit'     class='button-secondary action' /><?php endif; ?>
<?php endif; ?>
			</div>
<?php if ( $page_links ) echo "			<div class='tablenav-pages'>$page_links</div>"; ?>
			<div class='view-switch'>
				<a href="<?php echo $list_url;   ?>"><img id="view-switch-list"    height="20" width="20" <?php if ( 'list'   == $url_parms['mode'] ) echo "class='current'" ?> alt="<?php _e('List View','MailPress')   ?>" title="<?php _e('List View','MailPress')   ?>" src="../wp-includes/images/blank.gif" /></a>
				<a href="<?php echo $detail_url; ?>"><img id="view-switch-excerpt" height="20" width="20" <?php if ( 'detail' == $url_parms['mode'] ) echo "class='current'" ?> alt="<?php _e('Detail View','MailPress') ?>" title="<?php _e('Detail View','MailPress') ?>" src="../wp-includes/images/blank.gif" /></a>
			</div>
			<div class="clear"></div>
		</div>
		<div class="clear"></div>
<?php
if ($mails) {
?>
		<table class='widefat'>
			<thead>
				<tr>
<?php mails_columns(); ?>
				  </tr>
			</thead>
			<tfoot>
				<tr>
<?php mails_columns(false); ?>
				  </tr>
			</tfoot>
			<tbody id='the-mail-list' class='list:mail'>
<?php
	foreach ($mails as $mail)
		MP_Mail::get_row( $mail->id, $url_parms );
?>
			</tbody>

			<tbody id='the-extra-mail-list' class='list:mail' style='display: none;'>
<?php
	foreach ($extra_mails as $mail)
		MP_Mail::get_row( $mail->id, $url_parms );
?>
			</tbody>
		</table>
		<div class='tablenav'>
<?php if ( $page_links ) echo "		<div class='tablenav-pages'>$page_links</div>"; ?>
			<div class='alignleft actions'>
<?php if ( 'sent' != $url_parms['status'] ): ?>
				<input type='submit' value='<?php _e('Send','MailPress'); ?>' 	name='sendit'	  class='button-secondary' />
				<input type='submit' value='<?php _e('Delete','MailPress'); ?>' 	name='deleteit'     class='button-secondary' />
<?php else : ?>
				<input type='submit' value='<?php _e('Delete','MailPress'); ?>' 	name='deleteit'     class='button-secondary' />
<?php endif; ?>
			</div>
			<br class='clear' />
		</div>
	</form>
	<form id='get-extra-mails' method='post' action='' class='add:the-extra-mail-list:' style='display: none;'>
<?php MP_Admin::post_url_parms((array) $url_parms); ?>
<?php wp_nonce_field( 'add-mail', '_ajax_nonce', false ); ?>
	</form>

	<div id='ajax-response'></div>
<?php
} else {
?>
	</form>
		<p>
			<?php _e('No results found.','MailPress') ?>
		</p>
<?php
}
?>
</div>