<?php

$url_parms = self::get_url_parms();
if (empty($url_parms['s'])) unset($url_parms['s']);
$h2 = __('View Logs', MP_TXTDOM);

//
// MANAGING CHECKBOX RESULTS
//
if ( isset( $_GET['deleted'] )  ) 
{
	$deleted   		= isset( $_GET['deleted'] )   	? (int) $_GET['deleted']   	: 0;

	if ( $deleted > 0 ) 
	{
		$message = sprintf( __ngettext( __('%s file deleted', MP_TXTDOM), __('%s files deleted', MP_TXTDOM), $deleted ), $deleted );
	}
}

//
// MANAGING SUBSUBSUB URL
//
$status_links 	= array();
$status_links[] 	= "	<li><a href=\"" . MailPress_view_logs . "\" class='current'>".__('Show All Logs', MP_TXTDOM)."</a>";
$subsubsub_urls = implode(' | </li>', $status_links) . '</li>';
unset($status_links);

//
// MANAGING PAGINATION
//
$url_parms['apage'] = $page	= isset($_GET['apage'])	? $_GET['apage'] : 1;
do
{
	$start = ( $url_parms['apage'] - 1 ) * 20;
	list($_logs, $total) = self::get_list($start, 25, $url_parms); // Grab a few extra
	$url_parms['apage']--;
} while ( $total <= $start );
$url_parms['apage']++;

$files 		= array_slice($_logs, 0, 20);
$extra_files 	= array_slice($_logs, 20);

$page_links = paginate_links	(array(	'base' => add_query_arg( 'apage', '%#%' ),
							'format' => '',
							'total' => ceil($total / 20),
							'current' => $url_parms['apage']
						)
					);
if ($url_parms['apage'] == 1) unset($url_parms['apage']);

?>
<div class='wrap'>
	<div id="icon-mailpress-tools" class="icon32"><br /></div>
	<h2><?php echo $h2; ?></h2>
<?php if (isset($message)) self::message($message); ?>
	<ul class='subsubsub'><?php echo $subsubsub_urls; ?></ul>
	<form id='search-form' action='' method='get'>
		<p id='post-search' class='search-box'>
			<input type='text' id='file-search-input' name='s' value='<?php if (isset($url_parms['s'])) echo $url_parms['s']; ?>' class="search-input" />
			<input type='submit' value="<?php _e( 'Search Logs', MP_TXTDOM); ?>" class='button' />
		</p>
		<input type='hidden' name='page' value='<?php echo MailPress_page_view_logs; ?>' />
	</form>
	<form id='posts-filter' action='' method='get'>
		<input type='hidden' name='page' value='<?php echo MailPress_page_view_logs; ?>' />
		<?php self::post_url_parms((array) $url_parms); ?>

		<div class='tablenav'>
			<div class='alignleft actions'>
				<input type='submit' value="<?php _e('Delete', MP_TXTDOM); ?>" name='deleteit' class='button-secondary delete' />
			</div>
<?php if ( $page_links ) echo "\n<div class='tablenav-pages'>$page_links</div>\n"; ?>
			<div class="clear"></div>
		</div>
		<div class="clear"></div>
<?php
	if ($files)
	{
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
			<tbody id='the-file-list' class='list:file'>
<?php	foreach ($files as $file) self::get_row( $file, $url_parms ); ?>
			</tbody>
			<tbody id='the-extra-file-list' class='list:file' style='display:none;'>
<?php	foreach ($extra_files as $file) self::get_row( $file, $url_parms ); ?>
			</tbody>
		</table>
		<div class='tablenav'>
<?php 	if ( $page_links ) echo "			<div class='tablenav-pages'>$page_links</div>"; ?>
			<div class='alignleft actions'>
				<input type='submit' value="<?php _e('Delete', MP_TXTDOM); ?>" name='deleteit' class='button-secondary delete' />
			</div>
			<br class='clear' />
		</div>
	</form>

	<form id='get-extra-files' method='post' action='' class='add:the-extra-file-list:' style='display: none;'>
<?php  self::post_url_parms((array) $url_parms); ?>
<?php wp_nonce_field( 'add-file', '_ajax_nonce', false ); ?>
	</form>
	<div id='ajax-response'></div>
<?php
} else {
?>
	</form>
		<p>
			<?php (is_dir('../' . self::get_path())) ? _e('No logs available', MP_TXTDOM) : printf( __('Wrong path : %s', MP_TXTDOM), '../' . self::get_path() ); ?>
		</p>
<?php
}
?>
</div>