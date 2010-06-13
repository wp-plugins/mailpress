<?php
$url_parms = MP_AdminPage::get_url_parms();

$h2 = __('Edit Mails', MP_TXTDOM);
$subtitle = '';

if (isset($url_parms['author'])) 
{
	$author_user = get_userdata( $url_parms['author'] );
	$subtitle .= ' ' . sprintf(__('by %s'), wp_specialchars( $author_user->display_name ));
}

//
// MANAGING PAGINATION + SUBSUBSUB URL
//

if( !isset($_per_page) || $_per_page <= 0 ) $_per_page = 20;
$url_parms['apage'] = isset($url_parms['apage']) ? $url_parms['apage'] : 1;
do
{
	$start = ( $url_parms['apage'] - 1 ) * $_per_page;

	list($_mails, $total, $subsubsub_urls) = MP_AdminPage::get_list($start, $_per_page + 5, $url_parms); // Grab a few extra

	$url_parms['apage']--;		
} while ( $total <= $start );
$url_parms['apage']++;

$page_links = paginate_links	(array(	'base' => add_query_arg( 'apage', '%#%' ), 
							'format' => '', 
							'total' => ceil($total / $_per_page), 
							'current' => $url_parms['apage']
						)
					);
if ($url_parms['apage'] == 1) unset($url_parms['apage']);

$mails 		= array_slice($_mails, 0, $_per_page);
$extra_mails 	= array_slice($_mails, $_per_page);

//
// MANAGING MESSAGE / CHECKBOX RESULTS
//

$results = array(	'deleted'	=> array('s' => __('%s mail deleted', MP_TXTDOM), 'p' => __('%s mails deleted', MP_TXTDOM)),
			'sent'	=> array('s' => __('%s mail sent', MP_TXTDOM),    'p' => __('%s mails sent', MP_TXTDOM)),
			'notsent'	=> array('s' => __('%s mail NOT sent', MP_TXTDOM),'p' => __('%s mails NOT sent', MP_TXTDOM)),
			'saved'	=> array('s' => __('Mail saved', MP_TXTDOM),      'p' => __('Mail saved', MP_TXTDOM)),
);

foreach ($results as $k => $v)
{
	if (isset($_GET[$k]) && $_GET[$k])
	{
		if (!isset($message)) $message = '';
		$message .= sprintf( __ngettext( $v['s'], $v['p'], $_GET[$k] ), $_GET[$k] );
		$message .=  '<br />';
	}
}

//
// MANAGING DETAIL/LIST URL
//

if (isset($url_parms['mode'])) $wmode = $url_parms['mode'];
$url_parms['mode'] = 'detail';
$detail_url = clean_url(MP_AdminPage::url( MailPress_mails, $url_parms ));
$url_parms['mode'] = 'list';
$list_url  	= clean_url(MP_AdminPage::url( MailPress_mails, $url_parms ));
if (isset($wmode)) $url_parms['mode'] = $wmode; 

?>
<div class='wrap'>
	<div id="icon-mailpress-mails" class="icon32"><br /></div>
	<div id='mp_message'></div>
	<h2>
		<?php echo esc_html( $h2 ); ?> 
		<a href='<?php echo MailPress_write; ?>' class="button add-new-h2"><?php echo esc_html(__('Add New')); ?></a> 
<?php if ( isset($url_parms['s']) ) printf( '<span class="subtitle">' . __('Search results for &#8220;%s&#8221;') . '</span>', esc_attr( $url_parms['s'] ) ); ?>
<?php if ( !empty($subtitle) )      echo    "<span class='subtitle'>$subtitle</span>"; ?>
	</h2>
<?php if (isset($message)) MP_AdminPage::message($message); ?>

	<ul class='subsubsub'><?php echo $subsubsub_urls; ?></ul>

	<form id='search-form' action='' method='get'>
		<p id='post-search' class='search-box'>
			<input type='text' id='mail-search-input' name='s' value="<?php if (isset($url_parms['s'])) echo esc_attr( $url_parms['s'] ); ?>" class="search-input" />
			<input type='submit' value='<?php _e( 'Search', MP_TXTDOM ); ?>' class='button' />
		</p>
		<input type='hidden' name='page' value='<?php echo MailPress_page_mails; ?>' />
		<input type='hidden' name='mode' value='<?php echo $url_parms['mode']; ?>' />
		<?php if (isset($url_parms['status'])) : ?><input type='hidden' name='status' value='<?php echo $url_parms['status']; ?>' /><?php endif; ?>
	</form>

	<form id='posts-filter' action='' method='get'>
		<input type='hidden' name='page' value='<?php echo MailPress_page_mails; ?>' />
		<input type='hidden' name='mode' value='<?php echo $url_parms['mode']; ?>' />
		<?php if (isset($url_parms['status'])) : ?><input type='hidden' name='status' value='<?php echo $url_parms['status']; ?>' /><?php endif; ?>
<?php
if ($mails) {
?>
		<div class='tablenav'>
			<div class='alignleft actions'>
<?php if ((isset($url_parms['status'])) && ( 'sent' != $url_parms['status'] )) : ?>
				<?php if (current_user_can('MailPress_send_mails'))   : ?><input type='submit' value='<?php _e('Send', MP_TXTDOM); ?>' 	name='sendit'	  class='button-secondary action' /><?php endif; ?>
<?php endif; ?>
				<?php if (current_user_can('MailPress_delete_mails')) : ?><input type='submit' value='<?php _e('Delete', MP_TXTDOM); ?>' 	name='deleteit'     class='button-secondary action' /><?php endif; ?>
			</div>

<?php if ( $page_links ) echo "\n<div class='tablenav-pages'>$page_links</div>\n"; ?>

			<div class='view-switch'>
				<a href="<?php echo $list_url;   ?>"><img id="view-switch-list"    height="20" width="20" <?php if ( 'list'   == $url_parms['mode'] ) echo "class='current'" ?> alt="<?php _e('List View', MP_TXTDOM)   ?>" title="<?php _e('List View', MP_TXTDOM)   ?>" src="../wp-includes/images/blank.gif" /></a>
				<a href="<?php echo $detail_url; ?>"><img id="view-switch-excerpt" height="20" width="20" <?php if ( 'detail' == $url_parms['mode'] ) echo "class='current'" ?> alt="<?php _e('Detail View', MP_TXTDOM) ?>" title="<?php _e('Detail View', MP_TXTDOM) ?>" src="../wp-includes/images/blank.gif" /></a>
			</div>
			<div class="clear"></div>
		</div>
		<div class="clear"></div>

		<table class='widefat'>
			<thead>
				<tr>
<?php MP_AdminPage::columns_list(); ?>
				</tr>
			</thead>
			<tfoot>
				<tr>
<?php MP_AdminPage::columns_list(false); ?>
				</tr>
			</tfoot>
			<tbody id='the-mail-list' class='list:mail'>
<?php foreach ($mails as $mail) 		MP_AdminPage::get_row( $mail->id, $url_parms ); ?>
			</tbody>
<?php if ($extra_mails) : ?>
			<tbody id='the-extra-mail-list' class='list:mail' style='display: none;'>
<?php foreach ($extra_mails as $mail) 	MP_AdminPage::get_row( $mail->id, $url_parms ); ?>
			</tbody>
<?php endif; ?>
		</table>
		<div class='tablenav'>
<?php if ( $page_links ) echo "\n<div class='tablenav-pages'>$page_links</div>\n"; ?>
			<div class='alignleft actions'>
<?php if ((isset($url_parms['status'])) && ( 'sent' != $url_parms['status'] )) : ?>
				<?php if (current_user_can('MailPress_send_mails'))   : ?><input type='submit' value='<?php _e('Send', MP_TXTDOM); ?>' 	name='sendit'	  class='button-secondary action' /><?php endif; ?>
<?php endif; ?>
				<?php if (current_user_can('MailPress_delete_mails')) : ?><input type='submit' value='<?php _e('Delete', MP_TXTDOM); ?>' 	name='deleteit'     class='button-secondary action' /><?php endif; ?>
			</div>
		</div>
		<div class="clear"></div>
	</form>
	<div class="clear"></div>

	<form id='get-extra-mails' method='post' action='' class='add:the-extra-mail-list:' style='display: none;'>
<?php MP_AdminPage::post_url_parms((array) $url_parms); ?>
<?php wp_nonce_field( 'add-mail', '_ajax_nonce', false ); ?>
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
</div>