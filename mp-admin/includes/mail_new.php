<?php

$mp_general	= get_option('MailPress_general');
$draft_dest = MP_User::get_mailing_lists();
$url_parms 	= MP_Admin::get_url_parms();
$list_url 	= MP_Admin::url(MailPress_mails,false,$url_parms);
$hidden  	= "\t<input type='hidden' id='mail_id' 		name='id' 		value='0'/>\n";

$autosave	= true;

$preview	= ' ';

$h2 		= __('Write Mail','MailPress');

if (isset($_GET['id'])) 
{
	$draft 	= MP_Mail::get_mail($_GET['id']);
	if ($draft)
	{
		$last_user 	= get_userdata($draft->created_user_id);

		$hidden  	= "\t<input type='hidden' id='mail_id' 		name='id' 		value='$draft->id'/>\n";

		$h2 		= sprintf( __('Edit Draft # %1$s','MailPress'), $draft->id);

		$preview_url= clean_url(get_option('siteurl') . '/' . MP_PATH . "mp-includes/action.php?action=iview&id=$draft->id&KeepThis=true&TB_iframe=true");
		$preview	= "<a target='_blank' href='$preview_url'>" . __('Preview this mail','MailPress') . "</a>\n";

		$lastedited	= sprintf(__('Last edited by %1$s on %2$s at %3$s','MailPress'), wp_specialchars( $last_user->display_name ), mysql2date(get_option('date_format'), $draft->created), mysql2date(get_option('time_format'), $draft->created));

		if (current_user_can('MailPress_delete_mails')) $delete_url = clean_url(MailPress_mail  ."&amp;action=delete&amp;id=$draft->id");

/* revisions */

		if ($rev_ids = MP_Mail::get_mail_meta($draft->id,'_MailPress_mail_revisions'))
		{
			foreach ($rev_ids as $rev_user => $rev_id)
			{
				if ($current_user->ID == $rev_user)
				{
					$revision = MP_Mail::get_mail($rev_id);
					break;
				}
				else
				{
					$x = MP_Mail::get_mail($rev_id);
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

		$hidden  	.= "\t<input type='hidden' id='mail_revision' 	name='revision' 	value='$revision->id'/>\n";

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
}
elseif (isset($_GET['toemail'])) $draft->toemail = $_GET['toemail'];


// messages 

if (isset($_GET['saved'])) 	{$message  .=	__('Mail saved', 'MailPress') 			. '<br />'; $err   = true; }
if (isset($_GET['notsent'])) 	{$message  .=	__('Mail NOT sent', 'MailPress') 			. '<br />'; $err   = false; }
if (isset($_GET['sent'])) 	{$message  .=	sprintf( __ngettext( __('%s mail sent', 'MailPress'), __('%s mails sent', 'MailPress'), $_GET['sent']), $_GET['sent'])	. '<br />'; $err   = true; }
if (isset($_GET['nomail'])) 	{$message  .=	__('Please, enter a valid email',  'MailPress')	. '<br />'; $err   = false; 	$style = " style ='border-color:#f00;'";}
if (isset($_GET['nodest'])) 	{$message  .=	__('Mail NOT sent, no recipient', 'MailPress') 	. '<br />'; $err   = false; 	$style = " style ='border-color:#f00;'";}
if (isset($_GET['revision'])) {$message  .= sprintf( __('Mail restored to revision from %s','MailPress'), mp_mail_revision_title( (int) $_GET['revision'], false, $_GET['time'] ) ); $err   = true; }
if ( $lock )			{$message   =	$lock; 									$err   = false; }
if ( $notice )			{$message   =	$notice; 									$err   = false; }
if ($message) 	MP_Admin::message($message,$err); 

// from
$draft->fromemail = $mp_general['fromemail'];
$draft->fromname  = htmlentities(stripslashes($mp_general['fromname']),ENT_QUOTES);

// to 
if (isset($_GET['toemail'])) $draft->toemail = $_GET['toemail'];
if (!MailPress::is_email($draft->toemail)) 
{
	$draft->to_list = $draft->toemail;
	$draft->toemail = $draft->toname = '';
}
else
{
	$draft->toname  = htmlentities(stripslashes($draft->toname),ENT_QUOTES);
}

?>
<form id='mail_newform' name='mail_newform' action='<?php echo clean_url(MailPress_mail); ?>' method='post' onsubmit='return form_ctrl();'>
	<input type='hidden' 				name='action'  		value='draft' />
	<input type='hidden' 				name='referredby' 	value='<?php echo clean_url($_SERVER['HTTP_REFERER']); ?>' />
<?php echo $hidden; ?>
	<input type='hidden' id='user-id' 		name='user_ID' 		value="<?php echo MailPress::get_wp_user_id(); ?>" />

	<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); echo ("\n"); ?>
<?php if ( $autosave ) : ?>
	<?php wp_nonce_field( 'autosave', 'autosavenonce', false ); echo ("\n"); ?>
	<?php wp_nonce_field( 'getpreviewlink', 'getpreviewlinknonce', false ); echo ("\n"); ?>
<?php endif; ?>

	<div class='wrap'>

		<h2><?php echo $h2; ?></h2>

		<div id='poststuff'>
			<div class='submitbox' id='submitpost'>
				<div id='previewview'><?php if ($preview) echo $preview; ?></div>
				<div class='inside'>
					<p>
						<br class='clear' />
						<br class='clear' />
					</p>
				</div>
				<p class='submit'>
					<input type='submit' name='save' id='save-post' class='button button-highlighted' value="<?php _e('Save','MailPress'); ?>"  />
					<?php if (current_user_can('MailPress_send_mails')) : ?><input type='submit' name='send' id='publish'   class='button' 			 value="<?php _e('Send','MailPress'); ?>" /><?php endif; ?>
<?php if ($delete_url) : ?>
					<a class='submitdelete' href='<?php echo $delete_url ?>' onclick="if (confirm('<?php echo(js_escape(sprintf( __("You are about to delete this draft '%s'\n  'Cancel' to stop, 'OK' to delete."), $draft->id ))); ?>')) return true; return false;">
						<?php _e('Delete&nbsp;draft','MailPress'); ?>
					</a>
<?php	endif; ?>
					<br class="clear" />
<?php if ($lastedited) : ?>
					<?php echo $lastedited; ?>
					<br class="clear" />
<?php	endif; ?>
					<span id="autosave"></span>
				</p>
<?php do_action('MailPress_extra_form_mail_new'); ?>
				<div class='side-info'>
					<h5><?php _e('Related','MailPress'); ?></h5>
					<ul>
						<li><a href="<?php echo MailPress_mails; ?>"><?php _e('Manage All Mails','MailPress'); ?></a></li>
<?php if (current_user_can('MailPress_edit_users')) : ?>
						<li><a href="<?php echo MailPress_users; ?>"><?php _e('Manage All users','MailPress'); ?></a></li>
<?php endif; ?>
<?php do_action('MailPress_mail-new_relatedlinks'); ?>
						<li><a href="<?php echo MailPress_mails; ?>&amp;status=draft"><?php _e('Manage All Drafts','MailPress'); ?></a></li>
					</ul>
				</div>
			</div>
			<div id='post-body'>
				<div id='fromtodiv'>
					<table class='form-table'>
						<tr>
							<th style='border:none;padding:5px 10px 5px 10px;color:#c0c0c0;vertical-align:middle;'>
								<?php _e('From','MailPress'); ?> 
							</th>
							<td style='border:none;padding:5px 10px 0px 10px;'>
								<?php _e('Email : ','MailPress'); ?> 
								<input type='text' size='30' value='<?php echo $draft->fromemail; ?>' disabled='disabled' style='margin:0 1px 0 1px;'/>
								&nbsp;&nbsp;&nbsp;
								<?php _e('Name : ','MailPress'); ?> 
								<input type='text' size='30' value="<?php echo $draft->fromname; ?>"  disabled='disabled' style='margin:0 1px 0 1px;'/>
							</td>
						</tr>
						<tr>
							<th style='padding:5px 10px 5px 10px;vertical-align:middle;'>
								<?php _e('To','MailPress'); ?> 
							</th>	
							<td style='padding:5px 10px 5px 10px;'>
								<?php _e('Email : ','MailPress'); ?> 
								<input type='text' size='30' name='toemail' id='toemail' value='<?php echo $draft->toemail; ?>' <?php echo $style; ?>/>
								&nbsp;&nbsp;&nbsp;
								<?php _e('Name : ','MailPress'); ?> 
								<input type='text' size='30' name='toname'  id='toname'  value='<?php echo $draft->toname; ?>' />
								<br/>
								<?php _e('OR all','MailPress'); ?>
								&nbsp;&nbsp;
								<select name='to_list' id='to_list' <?php echo $style; ?>>
<?php MP_Admin::select_option($draft_dest,$draft->to_list) ?>
								</select>
							</td>
						</tr>
					</table>
				</div>
				<div id='titlediv'>
					<h3>
						<label for='title'>
							<?php _e('Subject','MailPress'); ?>
						</label>
					</h3>
					<div id='titlewrap'>
						<input type='text' name='subject' id='title' size='30' tabindex='1' autocomplete='off' value="<?php echo htmlspecialchars(stripslashes($draft->subject)); ?>" />
					</div>
				</div>
<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">
	<h3>
		<label for="content">
			<?php _e('Html') ?>
		</label>
	</h3>

<?php the_editor($draft->html,'content','title',apply_filters('MailPress_upload_media',false),5); ?>

</div>

				<div id="plaintextbox" class="postbox <?php echo postbox_classes('plaintextbox', 'mailpress_mailnew'); ?>">
					<h3><?php _e('Plain Text','MailPress') ?></h3>
					<div class="inside">
						<textarea id='plaintext' name='plaintext' cols='40' rows='1'><?php echo htmlspecialchars(stripslashes($draft->plaintext), ENT_QUOTES); ?></textarea>
					</div>
				</div>
<?php if ($rev_ids) { ?>
				<div id="revisionbox" class="postbox <?php echo postbox_classes('revisionbox', 'mailpress_mailnew'); ?>">
					<h3><?php _e('Mail Revisions','MailPress') ?></h3>
					<div class="inside">
<?php mp_list_mail_revisions($draft->id); ?>
					</div>
				</div>
<?php } ?>
			</div>
		</div>
	</div>
</form>