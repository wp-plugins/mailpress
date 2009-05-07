<?php
if (!current_user_can('MailPress_edit_users')) wp_die(__('You do not have sufficient permissions to access this page.'));

function users_columns($id=true) {
	global $mp_screen ;
	$users_columns = MP_User::manage_list_columns();
	$hidden = (array) get_user_option( "manage" . $mp_screen . "columnshidden" );
	foreach ( $users_columns as $user_column_key => $column_display_name ) {
		if ( 'cb' === $user_column_key )
			$class = ' class="check-column"';
		else
			$class = " class=\"manage-column column-$user_column_key\"";

		$style = '';
		if ( in_array($user_column_key, $hidden) )
			$style = ' style="display:none;"';
?>
					<th scope="col" <?php if ($id) echo "id=\"$user_column_key\""; echo $class; echo $style?>><?php echo $column_display_name; ?></th>
<?php }
}

unset($fade);
$url_parms = MP_Admin::get_url_parms(array('mode','status','s','apage','author','mailinglist','startwith'));
$h2 = '';
if (isset($url_parms['author']) && !empty($url_parms['author'])) 
{
	$author_user = get_userdata( $url_parms['author'] );
	$h2 .= ' ' . sprintf(__('by %s'), wp_specialchars( $author_user->display_name ));
}
if (isset($url_parms['mailinglist']) && !empty($url_parms['mailinglist'])) 
{
	$mailinglist = get_mailinglist( $url_parms['mailinglist'] );
	$h2 .= ' ' . sprintf(__('in &#8220;%s&#8221;'), wp_specialchars( $mailinglist->name ));
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

do_action('MailPress_users_addon_update');

//
// MANAGING SUBSUBSUB URL
//
$status_links 	= array();
$num_users = MP_User::count();
$stati = array('active' => __('Active','MailPress'),'waiting' => sprintf(__('Waiting (%s)','MailPress'), "<span class='user-count'>$num_users->waiting</span>"));
$class		= ( empty($url_parms['status']) ) ? ' class="current"' : '';
$status_links[] 	= "	<li><a href=\"" . MailPress_users . "&amp;mode=" . $url_parms['mode'] . "\"$class>".__('All Subscribers','MailPress')."</a>";
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

//
// ALPHABETICAL LIST
//
$wstartwith = $url_parms['startwith'];
$alphabet = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
$alphabet = MP_User::alphabet();

$alphanav = '';
foreach ($alphabet as $letter)
{
	$url_parms['startwith'] = $letter;
	$current_letter = ($letter == $wstartwith) ? true : false;
	$alpha_url = clean_url(MP_Admin::url( MailPress_users, false ,$url_parms ));

	if ($current_letter)	$alphanav .= "<span class='page-numbers current'>$letter</span>";
	else				$alphanav .= "<a class='page-numbers' href='$alpha_url' title='" . sprintf(__('List Mailpress users from %s','MailPress'), $letter ) . "'>$letter</a>";
}
$url_parms['startwith'] = $wstartwith;
unset($wstartwith);
if ($alphanav) $alphanav = "<div class='tablenav' style='float:left;margin:-5px 0 2px 0;'><div class='tablenav-pages'>" . $alphanav . "</div></div>";

?>
<?php if (isset($fade)) MP_Admin::message($fade); ?>
<div class='wrap'>
	<div id="icon-mailpress-users" class="icon32"><br /></div>
	<h2>
		<?php _e('Manage MailPress users','MailPress'); echo $h2; ?>
	</h2>
	<ul class='subsubsub'>

<?php echo $subsubsub_urls; ?>

	</ul>
	<form id='search-form' action='' method='get'>
		<p id='post-search' class='search-box'>
			<input type='text' id='user-search-input' name='s' value='<?php echo $url_parms['s']; ?>' class="search-input" />
			<input type='submit' value='<?php _e( 'Search subscribers','MailPress' ); ?>' class='button' />
		</p>
		<input type='hidden' name='page' value='<?php echo MailPress_page_users; ?>' />
		<input type='hidden' name='mode' value='<?php echo $url_parms['mode']; ?>' />
		<input type='hidden' name='status' value='<?php echo $url_parms['status']; ?>' />
	<?php if (isset($url_parms['mailinglist'])) echo "	<input type='hidden' name='mailinglist' value='" . $url_parms['mailinglist'] . "' />"; ?>
	</form>



	<form id='posts-filter' action='' method='get'>
		<input type='hidden' name='page' value='<?php echo MailPress_page_users; ?>' />
		<input type='hidden' name='mode' value='<?php echo $url_parms['mode']; ?>' />
		<input type='hidden' name='status' value='<?php echo $url_parms['status']; ?>' />
<?php if (isset($url_parms['mailinglist'])) echo "		<input type='hidden' name='mailinglist' value='" . $url_parms['mailinglist'] . "' />"; ?>

		<div class='tablenav'>
			<div class='alignleft actions'>
<?php if ( 'active' != $url_parms['status'] ): ?>
				<input type='submit' value='<?php _e('Activate','MailPress'); ?>' 	name='activateit'	  class='button-secondary action' />
<?php endif; ?>
<?php if ( 'waiting' != $url_parms['status'] ): ?>
				<input type='submit' value='<?php _e('Deactivate','MailPress'); ?>' 	name='deactivateit' class='button-secondary action' />
<?php endif; ?>
<?php if (current_user_can('MailPress_delete_users')) : ?>
				<input type='submit' value='<?php _e('Delete','MailPress'); ?>' 	name='deleteit'     class='button-secondary delete action' />
<?php endif; ?>

<?php do_action('MailPress_restrict_manage_users',$url_parms); ?>

			</div>
<?php if ( $page_links ) echo "			<div class='tablenav-pages'>$page_links</div>"; ?>
			<div class='view-switch'>
				<a href="<?php echo $list_url;   ?>"><img id="view-switch-list"    height="20" width="20" <?php if ( 'list'   == $url_parms['mode'] ) echo "class='current'" ?> alt="<?php _e('List View','MailPress')   ?>" title="<?php _e('List View','MailPress')   ?>" src="../wp-includes/images/blank.gif" /></a>
				<a href="<?php echo $detail_url; ?>"><img id="view-switch-excerpt" height="20" width="20" <?php if ( 'detail' == $url_parms['mode'] ) echo "class='current'" ?> alt="<?php _e('Detail View','MailPress') ?>" title="<?php _e('Detail View','MailPress') ?>" src="../wp-includes/images/blank.gif" /></a>
			</div>
		</div>
		<div class="clear"></div>
		<?php echo $alphanav; ?>
<?php 
if ($users) {
?>
		<table class='widefat'>
			<thead>
				<tr>
<?php users_columns(); ?>
				  </tr>
			</thead>
			<tfoot>
				<tr>
<?php users_columns(false); ?>
				  </tr>
			</tfoot>
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
			</tbody>
		</table>
		<div class='tablenav'>
<?php if ( $page_links ) echo "		<div class='tablenav-pages'>$page_links</div>"; ?>
			<div class='alignleft actions'>
<?php if ( 'active' != $url_parms['status'] ): ?>
				<input type='submit' value='<?php _e('Activate','MailPress'); ?>' 	name='activateit'	  class='button-secondary' />
<?php endif; ?>
<?php if ( 'waiting' != $url_parms['status'] ): ?>
				<input type='submit' value='<?php _e('Deactivate','MailPress'); ?>' 	name='deactivateit' class='button-secondary' />
<?php endif; ?>
				<input type='submit' value='<?php _e('Delete','MailPress'); ?>' 	name='deleteit'     class='button-secondary delete' />
			</div>
		</div>
		<div class="clear"></div>
		<?php //echo $alphanav; ?>
	</form>
	<div class="clear"></div>

	<form id='get-extra-users' method='post' action='' class='add:the-extra-user-list:' style='display: none;'>
<?php MP_Admin::post_url_parms((array) $url_parms); ?>
<?php wp_nonce_field( 'add-user', '_ajax_nonce', false ); ?>
	</form>

	<div id='ajax-response'></div>
<?php
} else {
?>
	</form>
		<div class="clear"></div>
		<p>
			<?php _e('No results found.','MailPress') ?>
		</p>
<?php
}
?>
<?php do_action('MailPress_users_addon',$url_parms); ?>
</div>