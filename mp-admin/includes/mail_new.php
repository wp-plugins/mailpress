<?php
if (!current_user_can('MailPress_edit_mails')) wp_die(__('You do not have sufficient permissions to access this page.'));

global $mp_screen, $current_user ;

if (isset($_GET['id']))
{
	$draft 	= MP_Mail::get($_GET['id']);
	$rev_ids 	= MP_Mailmeta::get($draft->id,'_MailPress_mail_revisions');
}

$mp_general	= get_option('MailPress_general');
$draft_dest = MP_User::get_mailing_lists();
$url_parms 	= MP_Admin::get_url_parms();
$list_url 	= MP_Admin::url(MailPress_mails,false,$url_parms);
$hidden  	= "\t\t\t<input type='hidden' id='mail_id' 		name='id' 		value='0' />\n";

$autosave	= true;

$h2 = sprintf(__('Add New Mail','MailPress'), MailPress_mails);

if ($draft)
{
	$last_user 	= get_userdata($draft->created_user_id);

	$hidden  	= "\t\t\t<input type='hidden' id='mail_id' 		name='id' 		value='$draft->id' />\n";

	$h2 		= sprintf( __('Edit Draft # %1$s','MailPress'), $draft->id);

	$lastedited	= sprintf(__('Last edited by %1$s on %2$s at %3$s','MailPress'), wp_specialchars( $last_user->display_name ), mysql2date(get_option('date_format'), $draft->created), mysql2date(get_option('time_format'), $draft->created));

	$delete_url = clean_url(MailPress_mail  ."&amp;action=delete&amp;id=$draft->id");

/* revisions */

	if ($rev_ids)
	{
		foreach ($rev_ids as $rev_user => $rev_id)
		{
			if ($current_user->ID == $rev_user)
			{
				$revision = MP_Mail::get($rev_id);
				break;
			}
			else
			{
				$x = MP_Mail::get($rev_id);
				if ($x)
				{
					if ($x->created > $revision->created)
					{
						$revision = $x;
						$revision->not_this_user = true;
					}
				}
			}
		}
	}

	if ($revision)
	{
		if ($revision->created > $draft->created)
		{
			foreach ($autosave_data as $k => $v)
			{
				if ( wp_text_diff( $revision->$k, $draft->$k ) ) 
				{
					$notice = sprintf( __( 'There is an autosave of this mail that is more recent than the version below.  <a href="%s">View the autosave</a>.', 'MailPress' ), clean_url(MailPress_revision . "&id=$draft->id&revision=$revision->id") );
					$autosave = false;
					break;
				}
			}
		}
	}
	else $revision->id = '0';

	if ($revision->not_this_user) $revision->id = '0';

	$hidden  	.= "\t\t\t<input type='hidden' id='mail_revision' 	name='revision' 	value='$revision->id' />\n";

/* end of revisions */

/* lock */

	if ($last = MP_Mail::check_mail_lock($draft->id))
	{
		$lock_user 	= get_userdata($last);
		$lock_user_name = $lock_user ? $lock_user->display_name : __('Somebody');
		$lock = sprintf( __( 'Warning: %s is currently editing this mail' ), wp_specialchars( $lock_user_name ) );
	}
	else
	{
		MP_Mail::set_mail_lock($draft->id);
	}
		
/* end of lock */

}
elseif (isset($_GET['toemail'])) $draft->toemail = $_GET['toemail'];


// messages
$class= 'fromto';
if (isset($_GET['saved'])) 	{$message  .=	__('Mail saved', 'MailPress') 			. '<br />'; $err   = true; }
if (isset($_GET['notsent'])) 	{$message  .=	__('Mail NOT sent', 'MailPress') 			. '<br />'; $err   = false; }
if (isset($_GET['sent'])) 	{$message  .=	sprintf( __ngettext( __('%s mail sent', 'MailPress'), __('%s mails sent', 'MailPress'), $_GET['sent']), $_GET['sent'])	. '<br />'; $err   = true; }
if (isset($_GET['nomail'])) 	{$message  .=	__('Please, enter a valid email',  'MailPress')	. '<br />'; $err   = false; 	$class = "TO";}
if (isset($_GET['nodest'])) 	{$message  .=	__('Mail NOT sent, no recipient', 'MailPress') 	. '<br />'; $err   = false; 	$class = "TO";}
if (isset($_GET['revision'])) {$message  .= sprintf( __('Mail restored to revision from %s','MailPress'), mp_mail_revision_title( (int) $_GET['revision'], false, $_GET['time']) ); $err   = true; }
if ( $lock )			{$message   =	$lock; 									$err   = false; }
if ( $notice )			{$message   =	$notice; 									$err   = false; }

// from
$draft->fromemail = $mp_general['fromemail'];
$draft->fromname  = apply_filters('Mailpress_input_text',$mp_general['fromname']); 

// to 
if (isset($_GET['toemail'])) $draft->toemail = $_GET['toemail'];
if (!MailPress::is_email($draft->toemail)) 
{
	$draft->to_list = $draft->toemail;
	$draft->toemail = $draft->toname = '';
}
else
{
	$draft->toname  = apply_filters('Mailpress_input_text',$draft->toname); 
}
?>
<?php if ($message) 	MP_Admin::message($message,$err); ?>
	<div class='wrap'>
		<div id="icon-mailpress-mailnew" class="icon32"><br /></div>
		<h2><?php echo $h2; ?></h2>
		<form id='mail_newform' name='mail_newform' action='<?php echo clean_url(MailPress_mail); ?>' method='post' onsubmit='return form_ctrl();'>

		<input type='hidden' 				name='action'  		value='draft' />
		<input type='hidden' 				name='referredby' 	value='<?php echo clean_url($_SERVER['HTTP_REFERER']); ?>' />
<?php echo $hidden; ?>
		<input type='hidden' id='user-id' 		name='user_ID' 		value="<?php echo MailPress::get_wp_user_id(); ?>" />

		<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); echo ("\n"); ?>
<?php if ( $autosave ) : ?>
		<?php wp_nonce_field( 'autosave', 'autosavenonce', false ); echo ("\n"); ?>
		<?php wp_nonce_field( 'getpreviewlink', 'getpreviewlinknonce', false ); echo ("\n"); ?>
		<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
<?php endif; ?>

		<div id='poststuff' class='metabox-holder has-right-sidebar'>
			<div id="side-info-column" class="inner-sidebar">
<?php $side_meta_boxes = do_meta_boxes($mp_screen, 'side', $draft); ?>
			</div>

			<div id="post-body" class="<?php echo $side_meta_boxes ? 'has-sidebar' : ''; ?>">
				<div id="post-body-content" class="has-sidebar-content">
					<div id='fromtodiv'>
						<table class='form-table'>
							<tr>
								<th class='nombp from'>
									<?php _e('From','MailPress'); ?> 
								</th>
								<td class='nombp' >
									<input class='w90 fromto' type='text' value='<?php echo $draft->fromemail; ?>' disabled='disabled' title="<?php _e('Email','MailPress'); ?>" />
								</td>
								<td class='nombp' >
									<input class='w90 fromto' type='text' value="<?php echo $draft->fromname; ?>"  disabled='disabled' title="<?php _e('Name','MailPress'); ?>" />
								</td>
							</tr>
							<tr>
								<th class='nombp' rowspan='2'>
									<?php _e('To','MailPress'); ?> 
								</th>	
								<td class='nombp' >
									<input  class='w90 <?php echo $class; ?>' type='text' name='toemail' id='toemail' value='<?php echo $draft->toemail; ?>' title="<?php _e('Email','MailPress'); ?>" />
								</td>
								<td class='nombp' >
									<input  class='w90 <?php echo $class; ?>' type='text' name='toname'  id='toname'  value='<?php echo $draft->toname; ?>'  title="<?php _e('Name','MailPress'); ?>" />
								</td>
							</tr>
							<tr>
								<td colspan='2'>
									<?php _e('OR all','MailPress'); ?>
									&nbsp;&nbsp;
									<select name='to_list' id='to_list'  class='<?php echo $class; ?>'>
<?php MP_Admin::select_option($draft_dest,$draft->to_list) ?>
									</select>
								</td>
							</tr>
						</table>
					</div>
					<div id='titlediv'>
						<h3 class='mp_hndle'>
							<label for='title'>
								<?php _e('Subject','MailPress'); ?>
							</label>
						</h3>
						<div id='titlewrap'>
							<input type='text' name='subject' id='title' size='30' tabindex='1' autocomplete='off' value="<?php echo htmlspecialchars(stripslashes($draft->subject)); ?>" />
						</div>
					</div>
					<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">
						<h3 class='mp_hndle'>
							<?php _e('Html') ?>
						</h3>

<?php the_editor($draft->html,'content','title',apply_filters('MailPress_upload_media',false),5); ?>
						<div id="post-status-info">
							<span id="wp-word-count" class="alignleft"></span>
							<span class="alignright">
								<span id="autosave">&nbsp;</span>
								<span id="last-edit">
<?php if ($lastedited) : ?>
									<?php echo $lastedited; ?>
<?php	endif; ?>
								</span>
							</span>
							<br class="clear" />
						</div>
					</div>
<?php do_meta_boxes($mp_screen, 'normal', $draft); ?>
				</div>
			</div>
		</div>
	</form>
</div>
<?php if (!MP_Mail::flash()) : ?>
	<div id='html-upload-iframes'></div>
<?php endif; ?>