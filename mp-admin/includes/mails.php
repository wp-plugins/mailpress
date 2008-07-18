<?php
unset($fade);
$url_parms = MP_Admin::get_url_parms();
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
$num_mails 		= $wpdb->get_var("SELECT count(*) FROM $wpdb->mp_mails WHERE status = 'draft' ;");

$stati = array('draft' => sprintf(__('Draft (%s)','MailPress'), "<span class='mail-count'>$num_mails</span>"), 'sent' => __('Sent','MailPress'));
$class		= ( empty($url_parms['status']) ) ? ' class="current"' : '';
$status_links[] 	= "	<li><a href=\"" . MailPress_mails . "&amp;mode=" . $url_parms['mode'] . "\"$class>".__('Show All mails','MailPress')."</a>";
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

if (isset($fade)) MP_Admin::message($fade); 

?>
<div class='wrap'>
	<form id='posts-filter' action='' method='get'>
		<h2>
			<?php _e('Manage Mails','MailPress'); echo $h2_author; ?>
		</h2>
		<ul class='subsubsub'>

<?php echo $subsubsub_urls; ?>

		</ul>
		<input type='hidden' name='page' value='<?php echo MP_FOLDER; ?>/mp-admin/mails.php' />
		<p id='post-search'>
			<input type='text' id='mail-search-input' name='s' value='<?php echo $url_parms['s']; ?>' />
			<input type='submit' value='<?php _e( 'Search mails','MailPress' ); ?>' class='button' />
		</p>
		<input type='hidden' name='mode' value='<?php echo $url_parms['mode']; ?>' />
		<input type='hidden' name='status' value='<?php echo $url_parms['status']; ?>' />
	</form>
	<ul class='view-switch'>
		<li <?php if ( 'detail' == $url_parms['mode'] ) echo "class='current'" ?>><a href='<?php echo $detail_url; ?>'><?php _e('Detail View','MailPress') ?></a></li>
		<li <?php if ( 'list' == $url_parms['mode'] )   echo "class='current'" ?>><a href='<?php echo $list_url  ; ?>'><?php _e('List View','MailPress') ?></a></li>
	</ul>
	<form id='posts-form' action='' method='post'>
		<div class='tablenav'>
<?php if ( $page_links ) echo "			<div class='tablenav-pages'>$page_links</div>"; ?>

			<div class='alignleft'>
<?php if ( 'sent' != $url_parms['status'] ): ?>
				<input type='submit' value='<?php _e('Send','MailPress'); ?>' 	name='sendit'	  class='button-secondary' />
				<input type='submit' value='<?php _e('Delete','MailPress'); ?>' 	name='deleteit'     class='button-secondary' />
<?php endif; ?>
			</div>
			<br class='clear' />
		</div>
		<br class='clear' />
<?php
if ($mails) {
?>
		<table class='widefat'>
			<thead>
				<tr>
    					<th scope='col' style='text-align: left'><input type='checkbox' onclick="checkAll(document.getElementById('posts-form'));" /></th>
					<th scope='col'><?php _e('Subject','MailPress') ?></th>
					<th scope='col'><?php _e('by','MailPress') ?></th>
					<th scope='col'><?php _e('to','MailPress') ?></th>
					<th scope='col'><?php _e('Theme','MailPress') ?></th>
					<th scope='col'><?php _e('Actions','MailPress') ?></th>
				  </tr>
			</thead>
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
				<tr><td></td></tr>
			</tbody>
		</table>
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
	<div class='tablenav'>
<?php if ( $page_links ) echo "		<div class='tablenav-pages'>$page_links</div>"; ?>
		<br class='clear' />
	</div>
</div>