<?php
global $wpdb;

$url_parms = self::get_url_parms(array('mode','status','s','apage','author','mailinglist','startwith'));
$h2 = __('Edit Users', MP_TXTDOM);
$h2_author = '';
if (isset($url_parms['author'])) 
{
	$author_user = get_userdata( $url_parms['author'] );
	$h2_author = ' ' . sprintf(__('by %s'), wp_specialchars( $author_user->display_name ));
}
if (isset($url_parms['mailinglist']) && !empty($url_parms['mailinglist'])) 
{
	self::require_class('Mailinglists');
	$mailinglist = MP_Mailinglists::get( $url_parms['mailinglist'] );
	$h2 .= ' ' . sprintf(__('in &#8220;%s&#8221;'), wp_specialchars( $mailinglist->name ));
}

//
// MANAGING CHECKBOX RESULTS
//
if ( isset( $_GET['activated'] ) || isset( $_GET['deactivated'] ) || isset( $_GET['deleted'] ) || isset( $_GET['unbounced'] ) )
{
	$activated 		= isset( $_GET['activated'] ) 	? (int) $_GET['activated'] 	: 0;
	$deactivated 	= isset( $_GET['deactivated'] ) 	? (int) $_GET['deactivated'] 	: 0;
	$deleted   		= isset( $_GET['deleted'] )   	? (int) $_GET['deleted']   	: 0;
	$unbounced   	= isset( $_GET['unbounced'] )   	? (int) $_GET['unbounced']   	: 0;

	if ($activated > 0  || $deactivated > 0 || $deleted > 0 || $unbounced > 0 ) 
	{
		$message = '';
		if ( $activated > 0 ) 
		{
			$message .= sprintf( __ngettext( __('%s subscriber activated', MP_TXTDOM), __('%s subscribers activated', MP_TXTDOM), $activated ), $activated );
			$message .=  '<br />';
		}
		if ( $deactivated > 0 ) 
		{
			$message .= sprintf( __ngettext( __('%s subscriber deactivated', MP_TXTDOM), __('%s subscribers deactivated', MP_TXTDOM), $deactivated ), $deactivated );
			$message .=  '<br />';
		}
		if ( $deleted > 0 ) 
		{
			$message .= sprintf( __ngettext( __('%s subscriber deleted', MP_TXTDOM), __('%s subscribers deleted', MP_TXTDOM), $deleted ), $deleted );
			$message .=  '<br />';
		}
		if ( $unbounced > 0 ) 
		{
			$message .= sprintf( __ngettext( __('%s subscriber unbounced', MP_TXTDOM), __('%s subscribers unbounced', MP_TXTDOM), $unbounced ), $unbounced );
			$message .=  '<br />';
		}
	}
}

do_action('MailPress_users_addon_update');

//
// MANAGING SUBSUBSUB URL
//
$status_links 	= array();
$status_links_url = (isset($url_parms['mode'])) ? MailPress_users . "&amp;mode=" . $url_parms['mode']  : MailPress_users ;
$num_users = self::count();

$stati = array(	'active' 		=> __('Active', MP_TXTDOM),
			'waiting' 		=> sprintf(__('Waiting (%s)', MP_TXTDOM), "<span class='user-count-waiting'>" . $num_users->waiting . "</span>"),
			'bounced' 		=> sprintf(__('Bounced (%s)', MP_TXTDOM), "<span class='user-count-bounced'>" . $num_users->bounced . "</span>"),
			'unsubscribed' 	=> sprintf(__('Unsubscribed (%s)', MP_TXTDOM), "<span class='user-count-unsubscribed'>" . $num_users->unsubscribed . "</span>"),
		);

if (!class_exists('MailPress_bounce_handling')) 	unset($stati['bounced']);
elseif (!$num_users->bounced)					unset($stati['bounced']);

if (!$num_users->unsubscribed)				unset($stati['unsubscribed']);

$class		= ( empty($url_parms['status']) ) ? ' class="current"' : '';
$status_links[] 	= "<a href='" . $status_links_url . "'$class>" . __('All Subscribers', MP_TXTDOM) . "</a>";
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
$detail_url = clean_url(self::url( MailPress_users, $url_parms ));
$url_parms['mode'] = 'list';
$list_url  	= clean_url(self::url( MailPress_users, $url_parms ));
if (isset($wmode)) $url_parms['mode'] = $wmode; 

//
// MANAGING PAGINATION
//
$url_parms['apage'] = isset($url_parms['apage']) ? $url_parms['apage'] : 1;
do
{
	$start = ( $url_parms['apage'] - 1 ) * 20;
	list($_users, $total) = self::get_list($start, 25, $url_parms); // Grab a few extra
	$url_parms['apage']--;		
} while ( $total <= $start );
$url_parms['apage']++;

$users 		= array_slice($_users, 0, 20);
$extra_users 	= array_slice($_users, 20);

$page_links = paginate_links	(array(	'base' => add_query_arg( 'apage', '%#%' ),
							'format' => '',
							'total' => ceil($total / 20),
							'current' => $url_parms['apage']
						)
					);
if ($url_parms['apage'] == 1) unset($url_parms['apage']);

//
// ALPHABETICAL LIST
//
$wstartwith = (isset($url_parms['startwith'])) ? $url_parms['startwith'] : false;
$alphabet = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
$alphabet = self::alphabet();

$alphanav = '';
foreach ($alphabet as $letter)
{
	$url_parms['startwith'] = $letter;
	$current_letter = ($letter == $wstartwith) ? true : false;
	$alpha_url = clean_url(self::url( MailPress_users, $url_parms ));

	if ($current_letter)	$alphanav .= "<span class='page-numbers current'>$letter</span>";
	else				$alphanav .= "<a class='page-numbers' href='$alpha_url' title='" . sprintf(__('List Mailpress users from %s', MP_TXTDOM), $letter ) . "'>$letter</a>";
}
$url_parms['startwith'] = $wstartwith;
unset($wstartwith);
if ($alphanav) $alphanav = "<div class='tablenav' style='float:left;margin:-5px 0 2px 0;'><div class='tablenav-pages'>" . $alphanav . "</div></div>";
?>
<div class='wrap'>
	<div id="icon-mailpress-users" class="icon32"><br /></div>
	<h2><?php echo $h2; ?></h2>
<?php if (isset($message)) self::message($message); ?>
	<ul class='subsubsub'><?php echo $subsubsub_urls; ?></ul>
	<form id='search-form' action='' method='get'>
		<p id='post-search' class='search-box'>
			<input type='text' id='user-search-input' name='s' value="<?php if (isset($url_parms['s'])) echo $url_parms['s']; ?>" class="search-input" />
			<input type='submit' value='<?php _e( 'Search subscribers', MP_TXTDOM ); ?>' class='button' />
		</p>
		<input type='hidden' name='page' value='<?php echo MailPress_page_users; ?>' />
		<input type='hidden' name='mode' value='<?php echo $url_parms['mode']; ?>' />
		<?php if (isset($url_parms['status'])) : ?><input type='hidden' name='status' value='<?php echo $url_parms['status']; ?>' /><?php endif; ?>
	<?php if (isset($url_parms['mailinglist'])) echo "	<input type='hidden' name='mailinglist' value='" . $url_parms['mailinglist'] . "' />"; ?>
	</form>
	<form id='posts-filter' action='' method='get'>
		<input type='hidden' name='page' value='<?php echo MailPress_page_users; ?>' />
		<input type='hidden' name='mode' value='<?php echo $url_parms['mode']; ?>' />
		<?php if ((isset($url_parms['status'])) && ( 'sent' != $url_parms['status'] )) : ?><input type='hidden' name='status' value='<?php echo $url_parms['status']; ?>' /><?php endif; ?>
<?php if (isset($url_parms['mailinglist'])) echo "		<input type='hidden' name='mailinglist' value='" . $url_parms['mailinglist'] . "' />"; ?>
<?php 
if ($users) {
?>
	
		<div class='tablenav'>
			<div class='alignleft actions'>
				<?php if ((isset($url_parms['status'])) && ( 'unsubscribed' == $url_parms['status'] )) : ?><input type='submit' value='<?php _e('Deactivate', MP_TXTDOM); ?>' name='deactivateit' class='button-secondary action' /><?php endif; ?>
				<?php if ((isset($url_parms['status'])) && ( 'bounced' == $url_parms['status'] )) : ?><input type='submit' value='<?php _e('Unbounce', MP_TXTDOM); ?>' 	name='unbounceit'   class='button-secondary action' /><?php endif; ?>
				<?php if ((isset($url_parms['status'])) && ( 'waiting' == $url_parms['status'] )) : ?><input type='submit' value='<?php _e('Activate', MP_TXTDOM); ?>' 	name='activateit'	  class='button-secondary action' /><?php endif; ?>
				<?php if ((isset($url_parms['status'])) && ( 'active'  == $url_parms['status'] )) : ?><input type='submit' value='<?php _e('Deactivate', MP_TXTDOM); ?>' 	name='deactivateit' class='button-secondary action' /><?php endif; ?>
				<?php if (current_user_can('MailPress_delete_users')) : ?><input type='submit' value='<?php _e('Delete', MP_TXTDOM); ?>' 	name='deleteit'     class='button-secondary delete action' /><?php endif; ?>
<?php do_action('MailPress_restrict_users',$url_parms); ?>
			</div>
<?php if ( $page_links ) echo "\n<div class='tablenav-pages'>$page_links</div>\n"; ?>
			<div class='view-switch'>
				<a href="<?php echo $list_url;   ?>"><img id="view-switch-list"    height="20" width="20" <?php if ( 'list'   == $url_parms['mode'] ) echo "class='current'" ?> alt="<?php _e('List View', MP_TXTDOM)   ?>" title="<?php _e('List View', MP_TXTDOM)   ?>" src="../wp-includes/images/blank.gif" /></a>
				<a href="<?php echo $detail_url; ?>"><img id="view-switch-excerpt" height="20" width="20" <?php if ( 'detail' == $url_parms['mode'] ) echo "class='current'" ?> alt="<?php _e('Detail View', MP_TXTDOM) ?>" title="<?php _e('Detail View', MP_TXTDOM) ?>" src="../wp-includes/images/blank.gif" /></a>
			</div>
		</div>
		<div class="clear"></div>
		<?php echo $alphanav; ?>

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
			<tbody id='the-user-list' class='list:user'>
<?php foreach ($users as $user) 		self::get_row( $user->id, $url_parms ); ?>
			</tbody>
<?php if ($extra_users) : ?>
			<tbody id='the-extra-user-list' class='list:user' style='display: none;'>
<?php
	foreach ($extra_users as $user)  	self::get_row( $user->id, $url_parms ); ?>
			</tbody>
<?php endif; ?>
		</table>
		<div class='tablenav'>
<?php if ( $page_links ) echo "\n<div class='tablenav-pages'>$page_links</div>\n"; ?>
			<div class='alignleft actions'>
				<?php if ((isset($url_parms['status'])) && ( 'unsubscribed' == $url_parms['status'] )) : ?><input type='submit' value='<?php _e('Deactivate', MP_TXTDOM); ?>' name='deactivateit' class='button-secondary action' /><?php endif; ?>
				<?php if ((isset($url_parms['status'])) && ( 'bounced' == $url_parms['status'] )) : ?><input type='submit' value='<?php _e('Unbounce', MP_TXTDOM); ?>' 	name='unbounceit'   class='button-secondary action' /><?php endif; ?>
				<?php if ((isset($url_parms['status'])) && ( 'waiting' == $url_parms['status'] )) : ?><input type='submit' value='<?php _e('Activate', MP_TXTDOM); ?>' 	name='activateit'	  class='button-secondary action' /><?php endif; ?>
				<?php if ((isset($url_parms['status'])) && ( 'active'  == $url_parms['status'] )) : ?><input type='submit' value='<?php _e('Deactivate', MP_TXTDOM); ?>' 	name='deactivateit' class='button-secondary action' /><?php endif; ?>
				<?php if (current_user_can('MailPress_delete_users')) : ?><input type='submit' value='<?php _e('Delete', MP_TXTDOM); ?>' 	name='deleteit'     class='button-secondary delete action' /><?php endif; ?>
			</div>
		</div>
		<div class="clear"></div>
	</form>
	<div class="clear"></div>

	<form id='get-extra-users' method='post' action='' class='add:the-extra-user-list:' style='display: none;'>
<?php self::post_url_parms((array) $url_parms); ?>
<?php wp_nonce_field( 'add-user', '_ajax_nonce', false ); ?>
	</form>
	<div id='ajax-response'></div>
<?php
} else {
?>
	</form>
	<div class="clear"></div>
	<p>
		<?php _e('No results found.', MP_TXTDOM) ?>
	</p>
<?php
}
?>
<?php do_action('MailPress_users_addon',$url_parms); ?>
</div>