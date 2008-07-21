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
if ( isset( $_GET['activated'] ) || isset( $_GET['deactivated'] ) || isset( $_GET['deleted'] )  ) 
{
	$activated 		= isset( $_GET['activated'] ) 	? (int) $_GET['activated'] 	: 0;
	$deactivated 	= isset( $_GET['deactivated'] ) 	? (int) $_GET['deactivated'] 	: 0;
	$deleted   		= isset( $_GET['deleted'] )   	? (int) $_GET['deleted']   	: 0;


	if ($activated > 0  || $deactivated > 0 || $deleted > 0 ) 
	{
		if ( $activated > 0 ) 
		{
			$fade .= sprintf( __ngettext( __('%s subscriber activated', 'MailPress'), __('%s subscribers activated', 'MailPress'), $activated ), $activated );
			$fade .=  '<br />';
		}
		if ( $deactivated > 0 ) 
		{
			$fade .= sprintf( __ngettext( __('%s subscriber deactivated', 'MailPress'), __('%s subscribers deactivated', 'MailPress'), $deactivated ), $deactivated );
			$fade .=  '<br />';
		}
		if ( $deleted > 0 ) 
		{
			$fade .= sprintf( __ngettext( __('%s subscriber deleted', 'MailPress'), __('%s subscribers deleted', 'MailPress'), $deleted ), $deleted );
			$fade .=  '<br />';
		}
	}
}

//
// MANAGING SUBSUBSUB URL
//
$status_links 	= array();
$num_users = MP_User::count_users();
$stati = array('waiting' => sprintf(__('Waiting (%s)','MailPress'), "<span class='user-count'>$num_users->waiting</span>"), 'active' => __('Active','MailPress'));
$class		= ( empty($url_parms['status']) ) ? ' class="current"' : '';
$status_links[] 	= "	<li><a href=\"" . MailPress_users . "&amp;mode=" . $url_parms['mode'] . "\"$class>".__('Show All subscribers','MailPress')."</a>";
foreach ( $stati as $status => $label ) {
	$class = '';

	if ( $status == $url_parms['status'] ) $class = ' class="current"';

	$status_links[] = "	<li><a href=\"" . MailPress_users . "&amp;status=$status&amp;mode=" . $url_parms['mode'] . "\"$class>" . $label . '</a>';
}
$subsubsub_urls = implode(' | </li>', $status_links) . '</li>';
unset($status_links);

//
// MANAGING DETAIL/LIST URL
//
$wmode = $url_parms['mode'];
$url_parms['mode'] = 'detail';
$detail_url = clean_url(MP_Admin::url( MailPress_users, false ,$url_parms ));
$url_parms['mode'] = 'list';
$list_url  	= clean_url(MP_Admin::url( MailPress_users, false ,$url_parms ));
$url_parms['mode'] = $wmode;
unset($wmode);

//
// MANAGING PAGINATION
//
$url_parms['apage']		= isset($_GET['apage'])		? $_GET['apage'] : 1;

do
{
	$start = ( $url_parms['apage'] - 1 ) * 20;
	list($_users, $total) = MP_User::get_list( $url_parms, $start, 25 ); // Grab a few extra
	$url_parms['apage']--;		
} while ( $total <= $start );
$url_parms['apage']++;

$users 		= array_slice($_users, 0, 20);
$extra_users 	= array_slice($_users, 20);

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
			<?php _e('Manage MailPress users','MailPress'); echo $h2_author; ?>
		</h2>
		<ul class='subsubsub'>

<?php echo $subsubsub_urls; ?>

		</ul>
		<input type='hidden' name='page' value='<?php echo MP_FOLDER; ?>/mp-admin/users.php' />
		<p id='post-search'>
			<input type='text' id='user-search-input' name='s' value='<?php echo $url_parms['s']; ?>' />
			<input type='submit' value='<?php _e( 'Search subscribers','MailPress' ); ?>' class='button' />
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
<?php if ( 'active' != $url_parms['status'] ): ?>
				<input type='submit' value='<?php _e('Activate','MailPress'); ?>' 	name='activateit'	  class='button-secondary' />
<?php endif; ?>
<?php if ( 'waiting' != $url_parms['status'] ): ?>
				<input type='submit' value='<?php _e('Deactivate','MailPress'); ?>' 	name='deactivateit' class='button-secondary' />
<?php endif; ?>
				<input type='submit' value='<?php _e('Delete','MailPress'); ?>' 	name='deleteit'     class='button-secondary' />
			</div>
			<br class='clear' />
		</div>
		<br class='clear' />
<?php
if ($users) {
?>
		<table class='widefat'>
			<thead>
				<tr>
    					<th scope='col' class='check-column'><input type='checkbox'/></th>
					<th scope='col'><?php _e('E-mail','MailPress') ?></th>
					<th scope='col'><?php _e('by','MailPress') ?></th>
					<th scope='col'><?php _e('since','MailPress') ?></th>
					<th scope='col'><?php _e('Actions','MailPress') ?></th>
				  </tr>
			</thead>
			<tbody id='the-user-list' class='list:user'>
<?php
	foreach ($users as $user)
		MP_User::get_row( $user->id, $url_parms );
?>
			</tbody>
			<tbody id='the-extra-user-list' class='list:user' style='display: none;'>
<?php
	foreach ($extra_users as $user)
		MP_User::get_row( $user->id, $url_parms );
?>
				<tr><td></td></tr>
			</tbody>
		</table>
	</form>

	<form id='get-extra-users' method='post' action='' class='add:the-extra-user-list:' style='display: none;'>
<?php MP_Admin::post_url_parms((array) $url_parms); ?>
<?php wp_nonce_field( 'add-user', '_ajax_nonce', false ); ?>
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