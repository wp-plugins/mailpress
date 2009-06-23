<?php
//do_action('mp_process_batch_send');
global $wpdb;

$url_parms = self::get_url_parms();
$h2 = __('Edit Mails', 'MailPress');

if (isset($url_parms['author'])) 
{
	$author_user = get_userdata( $url_parms['author'] );
	$h2 = $h2 . ' ' . sprintf(__('by %s'), wp_specialchars( $author_user->display_name ));
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
		$message = '';
		if ( $deleted > 0 ) 
		{
			$message .= sprintf( __ngettext( __('%s mail deleted', 'MailPress'), __('%s mails deleted', 'MailPress'), $deleted ), $deleted );
			$message .=  '<br />';
		}
		if ( $sent > 0 ) 
		{
			$message .= sprintf( __ngettext( __('%s mail sent', 'MailPress'), __('%s mails sent', 'MailPress'), $sent ), $sent );
			$message .=  '<br />';
		}
		if ( $notsent > 0 ) 
		{
			$message .= sprintf( __ngettext( __('%s mail NOT sent', 'MailPress'), __('%s mails NOT sent', 'MailPress'), $notsent ), $notsent );
			$message .=  '<br />';
   			$err   = true;
		}
		if ( $saved > 0 ) 
		{
			$message .=	__('Mail saved', 'MailPress');
			$message .=  '<br />';
		}
	}
}

//
// MANAGING SUBSUBSUB URL
//
$status_links 	= array();
$status_links_url = (isset($url_parms['mode'])) ? MailPress_mails . "&amp;mode=" . $url_parms['mode']  : MailPress_mails ;
$where =  (!current_user_can('MailPress_edit_others_mails')) ? "AND created_user_id = " . $user_ID : '';
$num_mails 		= $wpdb->get_var("SELECT count(*) FROM $wpdb->mp_mails WHERE status = 'draft' $where;");

$stati = array(	'sent' => __('Sent', 'MailPress'), 
			'draft' => sprintf(__('Draft (%s)', 'MailPress'), "<span class='mail-count'>$num_mails</span>")
		);
$class		= ( empty($url_parms['status']) ) ? ' class="current"' : '';
$status_links[] 	= "<a href='" . $status_links_url . "'$class>" . __('All Mails', 'MailPress') . "</a>";
foreach ( $stati as $status => $label ) 
{
	$class = '';
	if ( (isset($url_parms['status'])) && ( $status == $url_parms['status'] )) $class = ' class="current"';
	$status_links[] = "<a href='" . $status_links_url . "&amp;status=$status'$class>" . $label . '</a>';
}
$subsubsub_urls = '<li>' . implode(' | </li><li>', $status_links) . '</li>';
unset($status_links);

//
// MANAGING DETAIL/LIST URL
//
if (isset($url_parms['mode'])) $wmode = $url_parms['mode'];
$url_parms['mode'] = 'detail';
$detail_url = clean_url(self::url( MailPress_mails, $url_parms ));
$url_parms['mode'] = 'list';
$list_url  	= clean_url(self::url( MailPress_mails, $url_parms ));
if (isset($wmode)) $url_parms['mode'] = $wmode; 

//
// MANAGING PAGINATION
//
$url_parms['apage'] = isset($url_parms['apage']) ? $url_parms['apage'] : 1;
do
{
	$start = ( $url_parms['apage'] - 1 ) * 20;
	list($_mails, $total) = self::get_list($start, 25, $url_parms); // Grab a few extra
	$url_parms['apage']--;		
} while ( $total <= $start );
$url_parms['apage']++;

$mails 		= array_slice($_mails, 0, 20);
$extra_mails 	= array_slice($_mails, 20);

$page_links = paginate_links	(array(	'base' => add_query_arg( 'apage', '%#%' ), 
							'format' => '', 
							'total' => ceil($total / 20), 
							'current' => $url_parms['apage']
						)
					);
if ($url_parms['apage'] == 1) unset($url_parms['apage']);
?>
<div class='wrap'>
	<div id="icon-mailpress-mails" class="icon32"><br /></div>
	<div id='mp_message'></div>
	<h2><?php echo $h2; ?></h2>
<?php if (isset($message)) self::message($message); ?>

	<ul class='subsubsub'><?php echo $subsubsub_urls; ?></ul>
	<form id='search-form' action='' method='get'>
		<p id='post-search' class='search-box'>
			<input type='text' id='mail-search-input' name='s' value="<?php if (isset($url_parms['s'])) echo $url_parms['s']; ?>" class="search-input" />
			<input type='submit' value='<?php _e( 'Search mails', 'MailPress' ); ?>' class='button' />
		</p>
		<input type='hidden' name='page' value='<?php echo MailPress_page_mails; ?>' />
		<input type='hidden' name='mode' value='<?php echo $url_parms['mode']; ?>' />
		<?php if (isset($url_parms['status'])) : ?><input type='hidden' name='status' value='<?php echo $url_parms['status']; ?>' /><?php endif; ?>
	</form>
	<form id='posts-filter' action='' method='get'>
		<input type='hidden' name='page' value='<?php echo MailPress_page_mails; ?>' />
		<input type='hidden' name='mode' value='<?php echo $url_parms['mode']; ?>' />
		<?php if (isset($url_parms['status'])) : ?><input type='hidden' name='status' value='<?php echo $url_parms['status']; ?>' /><?php endif; ?>

		<div class='tablenav'>
			<div class='alignleft actions'>
<?php if ((isset($url_parms['status'])) && ( 'sent' != $url_parms['status'] )) : ?>
				<?php if (current_user_can('MailPress_send_mails'))   : ?><input type='submit' value='<?php _e('Send', 'MailPress'); ?>' 	name='sendit'	  class='button-secondary action' /><?php endif; ?>
<?php endif; ?>
				<?php if (current_user_can('MailPress_delete_mails')) : ?><input type='submit' value='<?php _e('Delete', 'MailPress'); ?>' 	name='deleteit'     class='button-secondary action' /><?php endif; ?>
			</div>
<?php if ( $page_links ) echo "\n<div class='tablenav-pages'>$page_links</div>\n"; ?>
			<div class='view-switch'>
				<a href="<?php echo $list_url;   ?>"><img id="view-switch-list"    height="20" width="20" <?php if ( 'list'   == $url_parms['mode'] ) echo "class='current'" ?> alt="<?php _e('List View', 'MailPress')   ?>" title="<?php _e('List View', 'MailPress')   ?>" src="../wp-includes/images/blank.gif" /></a>
				<a href="<?php echo $detail_url; ?>"><img id="view-switch-excerpt" height="20" width="20" <?php if ( 'detail' == $url_parms['mode'] ) echo "class='current'" ?> alt="<?php _e('Detail View', 'MailPress') ?>" title="<?php _e('Detail View', 'MailPress') ?>" src="../wp-includes/images/blank.gif" /></a>
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
<?php self::columns_list(); ?>
				</tr>
			</thead>
			<tfoot>
				<tr>
<?php self::columns_list(false); ?>
				</tr>
			</tfoot>
			<tbody id='the-mail-list' class='list:mail'>
<?php foreach ($mails as $mail) 		self::get_row( $mail->id, $url_parms ); ?>
			</tbody>
<?php if ($extra_mails) : ?>
			<tbody id='the-extra-mail-list' class='list:mail' style='display: none;'>
<?php foreach ($extra_mails as $mail) 	self::get_row( $mail->id, $url_parms ); ?>
			</tbody>
<?php endif; ?>
		</table>
		<div class='tablenav'>
<?php if ( $page_links ) echo "\n<div class='tablenav-pages'>$page_links</div>\n"; ?>
			<div class='alignleft actions'>
<?php if ((isset($url_parms['status'])) && ( 'sent' != $url_parms['status'] )) : ?>
				<?php if (current_user_can('MailPress_send_mails'))   : ?><input type='submit' value='<?php _e('Send', 'MailPress'); ?>' 	name='sendit'	  class='button-secondary action' /><?php endif; ?>
<?php endif; ?>
				<?php if (current_user_can('MailPress_delete_mails')) : ?><input type='submit' value='<?php _e('Delete', 'MailPress'); ?>' 	name='deleteit'     class='button-secondary action' /><?php endif; ?>
			</div>
		</div>
		<div class="clear"></div>
	</form>
	<div class="clear"></div>

	<form id='get-extra-mails' method='post' action='' class='add:the-extra-mail-list:' style='display: none;'>
<?php self::post_url_parms((array) $url_parms); ?>
<?php wp_nonce_field( 'add-mail', '_ajax_nonce', false ); ?>
	</form>
	<div id='ajax-response'></div>
<?php
} else {
?>
	</form>
		<p>
			<?php _e('No results found.', 'MailPress') ?>
		</p>
<?php
}
?>
</div>