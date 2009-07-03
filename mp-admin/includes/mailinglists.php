<?php
self::require_class('Mailinglists');

global $action;

$url_parms = self::get_url_parms();
$h2 = __('Mailing lists','MailPress'); 

wp_reset_vars(array('action'));
if ('edit' == $action) 
{
	$h3 = __('Update the mailing list','MailPress');
	$action = 'edited';
	$disabled = " disabled='disabled'";
	$cancel = "<input type='submit' class='button' name='cancel' value=\"" . __('Cancel','MailPress') . "\" />\n";
	
	$id = (int) $_GET['id'];
	$mailinglist = MP_Mailinglists::get( $id, OBJECT, 'edit' );
		
	$hidden = "<input type='hidden' name='id'   value=\"" . $id . "\" />\n";
	$hidden .="<input name='name' type='hidden' value=\"" . attribute_escape($mailinglist->name) . "\"/>\n";
}
else 
{
	$customfield = array();
	$h3 = __('Add a mailing list','MailPress');
	$action = self::add_form_id;
	$hidden = '';
	$disabled = '';
	$cancel = '';
}

//
// MANAGING MESSAGE
//

$messages[1] = __('Mailinglist added.','MailPress');
$messages[2] = __('Mailinglist deleted.','MailPress');
$messages[3] = __('Mailinglist updated.','MailPress');
$messages[4] = __('Mailinglist not added.','MailPress');
$messages[5] = __('Mailinglist not updated.','MailPress');
$messages[6] = __('Mailinglists deleted.','MailPress');

if (isset($_GET['message']))
{
	$message = $messages[$_GET['message']];
	$_SERVER['REQUEST_URI'] = remove_query_arg(array('message'), $_SERVER['REQUEST_URI']);
}

//
// MANAGING PAGINATION
//

$url_parms['apage'] = isset($url_parms['apage']) ? $url_parms['apage'] : 1;
$total = ( isset($url_parms['s']) && !empty($url_parms['s']) ) ? count(MP_Mailinglists::get_all(array('hide_empty' => 0, 'search' => $url_parms['s']))) : wp_count_terms(self::taxonomy);
if( !isset($mailinglistsperpage) || $mailinglistsperpage <= 0 ) $mailinglistsperpage = 20;

$page_links = paginate_links	(array(	'base' => add_query_arg( 'apage', '%#%' ),
							'format' => '',
							'total' => ceil( $total / $mailinglistsperpage),
							'current' => $url_parms['apage']
						)
					);
if ($url_parms['apage'] == 1) unset($url_parms['apage']);
?>
<div class="wrap nosubsub">
	<div id="icon-mailpress-users" class="icon32"><br /></div>
	<h2><?php echo $h2; ?></h2>
<?php if (isset($message)) self::message($message); ?>
	<form class='search-form topmargin' action='' method='get'>
		<p class='search-box'>
			<input type='hidden' name='page' value='<?php echo MailPress_page_mailinglists; ?>' />
			<input type='text' id='post-search-input' name='s' value='<?php if (isset($url_parms['s'])) echo $url_parms['s']; ?>' class="search-input"  />
			<input type='submit' value="<?php _e( 'Search Mailinglists','MailPress' ); ?>" class='button' />
		</p>
	</form>
	<br class='clear' />
	<div id="col-container">
		<div id="col-right">
			<div class="col-wrap">	
				<form id='posts-filter' action='' method='get'>
<?php self::post_url_parms($url_parms); ?>
					<div class='tablenav'>
<?php 	if ( $page_links ) echo "						<div class='tablenav-pages'>$page_links</div>"; ?>
						<div class='alignleft actions'>
							<input type='submit' value="<?php _e('Delete','MailPress'); ?>" name='deleteit' class='button-secondary delete action' />
							<input type='hidden' name='page' value='<?php echo MailPress_page_mailinglists; ?>' />
						</div>
						<br class='clear' />
					</div>
					<div class="clear"></div>
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
						<tbody id='<?php echo self::list_id; ?>' class='list:<?php echo self::tr_prefix_id; ?>'>
<?php self::get_list((isset($url_parms['apage'])) ? $url_parms['apage'] : 1, $mailinglistsperpage ); ?>
						</tbody>
					</table>
					<div class='tablenav'>
<?php 	if ( $page_links ) echo "						<div class='tablenav-pages'>$page_links</div>\n"; ?>
						<div class='alignleft actions'>
							<input type='submit' value="<?php _e('Delete','MailPress'); ?>" name='deleteit' class='button-secondary delete' />
						</div>
						<br class='clear' />
					</div>
					<br class='clear' />
				</form>
				<div class="form-wrap">
					<p><?php printf(__('<strong>Note:</strong><br />Deleting a mailing list does not delete the MailPress users in that mailing list. Instead, MailPress users that were only assigned to the deleted mailing list are set to the mailing list <strong>%s</strong>.','MailPress'), MP_Mailinglists::get_name(get_option('MailPress_default_mailinglist'))) ?></p>
				</div>
			</div>
		</div><!-- /col-right -->

		<div id="col-left">
			<div class="col-wrap">
				<div class="form-wrap">
					<h3><?php echo $h3; ?></h3>
					<div id="ajax-response"></div>
					<form name='<?php echo $action; ?>'  id='<?php echo $action; ?>'  method='post' action='' class='<?php echo $action; ?>:<?php echo self::list_id; ?>: validate'>
						<input type='hidden' name='action'   value='<?php echo $action; ?>' />
						<input type='hidden' name='formname' value='mailinglist_form' />
						<?php echo $hidden; ?>
						<?php wp_nonce_field('update-' . self::tr_prefix_id); ?>
						<div class="form-field form-required" style='margin:0;padding:0;'>
							<label for='mailinglist_name'><?php _e('Name','MailPress'); ?></label>
							<input name='name' id='mailinglist_name' type='text'<?php echo $disabled; ?> value="<?php if (isset($mailinglist->name)) echo attribute_escape($mailinglist->name); ?>" size='40' aria-required='true' />
							<p><?php _e('The name is used to identify the mailinglist almost everywhere.','MailPress'); ?></p>
						</div>
						<div class="form-field" style='margin:0;padding:0;'>
							<label for='mailinglist_slug'><?php _e('Slug','MailPress') ?></label>
							<input name='slug' id='mailinglist_slug' type='text' value="<?php if (isset($mailinglist->slug)) echo attribute_escape($mailinglist->slug); ?>" size='40' />
							<p><?php _e('The &#8220;slug&#8221; is a unique id for the mailing list (not so friendly !). In case of conflict, new mailing list is not created or when updating, slug might be regenerated. It is usually all lowercase and contains only letters, numbers, and hyphens. It is never displayed.','MailPress'); ?></p>
						</div>
						<div class="form-field" style='margin:0;padding:0;'>
							<label for='mailinglist_parent'><?php _e('Mailing list Parent','MailPress') ?></label>
							<?php MP_Mailinglists::dropdown(array('hide_empty' => 0, 'name' => 'parent', 'orderby' => 'name', 'htmlid' => 'mailinglist_parent', 'selected' => (isset($mailinglist->parent)) ? $mailinglist->parent : '', 'hierarchical' => true, 'show_option_none' => __('None','MailPress'))); ?>
							<p><?php _e("Mailing list can have a hierarchy. You might have a Rock'n roll mailing list, and under that have children mailing lists for Elvis and The Beatles. Totally optional !",'MailPress'); ?></p>
						</div>
						<div class="form-field" style='margin:0;padding:0;'>
							<label for='mailinglist_description'><?php _e('Description','MailPress') ?></label>
							<textarea name='description' id='mailinglist_description' rows='5' cols='50' style='width: 97%;'><?php if (isset($mailinglist->description)) echo htmlentities(stripslashes($mailinglist->description),ENT_QUOTES); ?></textarea>
							<p><?php _e('The description is not prominent by default.','MailPress'); ?></p>
						</div>
						<p class='submit'>
							<input type='submit' class='button' name='submit' id='mailinglist_submit' value="<?php echo $h3; ?>" />
							<?php echo $cancel; ?>
						</p>
					</form>
				</div>
			</div>
		</div><!-- /col-left -->
	</div><!-- /col-container -->
</div><!-- /wrap -->