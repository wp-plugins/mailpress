<?php
$mail_to_lists = array (''  => '&nbsp;', '1' => __('active blog','MailPress'), '2' => __('active comments','MailPress'), '3' => __('active blog + comments','MailPress'), '4' => __('active + not active','MailPress') );

$h2 		= __('Write Mail','MailPress');
$viewbutton = __('Preview this mail','MailPress');

$url_parms 	= MP_Admin::get_url_parms();
$list_url 	= MP_Admin::url(MailPress_mails,false,$url_parms);
$delete_url = $hidden  = '';

if (isset($_GET['id'])) 
{
	$draft 	= MP_Mail::get_mail($_GET['id']);
	$hidden  	= "\t<input type='hidden' name='id' value='$draft->id'/>\n";
	$delete_url = clean_url(MailPress_mail  ."&amp;action=delete&amp;id=$draft->id");
	$h2 		= sprintf( __('Edit Draft # %1$s','MailPress'), $draft->id);
	$viewbutton = __('View this mail','MailPress');
}
elseif (isset($_GET['toemail'])) $draft->toemail = $_GET['toemail'];

$mp_general		= get_option('MailPress_general');
$draft->fromemail = $mp_general['fromemail'];
$draft->fromname  = $mp_general['fromname'];

if (is_numeric($draft->toemail)) 
{
	$draft->to_list = $draft->toemail;
	$draft->toemail = $draft->toname = '';
}

$last_user 	= get_userdata($draft->created_user_id);

$message = $style = '';
if (isset($_GET['saved'])) 	{$message  .=	__('Mail saved', 'MailPress') 			. '<br />'; $err   = true; }
if (isset($_GET['notsent'])) 	{$message  .=	__('Mail NOT sent', 'MailPress') 			. '<br />'; $err   = false; }
if (isset($_GET['nomail'])) 	{$message  .=	__('Please, enter a valid email',  'MailPress')	. '<br />'; $err   = false; 	$style = " style ='border-color:#f00;'";}
if (isset($_GET['nodest'])) 	{$message  .=	__('Mail NOT sent, no recipient', 'MailPress') 	. '<br />'; $err   = false; 	$style = " style ='border-color:#f00;'";}
if (!empty($message)) MP_Admin::message($message,$err); 

?>
<div class='wrap'>
	<form id='mail_newform' name='mail_newform' action='<?php echo clean_url(MailPress_mail); ?>' method='post' onsubmit='return form_ctrl();'>
		<input type='hidden' name='action' 		value='draft' />
		<?php echo $hidden; ?>
		<input type="hidden" name='user_ID' 	value="<?php echo MailPress::get_wp_user_id(); ?>" id='user-id' />
		<input type="hidden" name='referredby' 	value='<?php echo $_SERVER['HTTP_REFERER']; ?>' />
		<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>

		<h2>
			<?php echo $h2; ?>
		</h2>
		<div id='poststuff'>
			<div id='submitcomment' class='submitbox' style='margin-top:13px;'>
				<div id="previewview" style='padding:8px;'>
						<input class='submit' id='view' name='view' value='<?php echo $viewbutton; ?>' type="submit"/>
				</div>
				<div class="inside">
					<br class="clear" />
					<br class="clear" />
				</div>
				<p class="submit">
					<input name='save' value='<?php _e('Save','MailPress'); ?>' type="submit" style="font-weight: bold;"/>
					<input name='send' value='<?php _e('Send','MailPress'); ?>' type="submit"/>
<?php if ('' != $delete_url) { ?>
					<a class='submitdelete' href='<?php echo $delete_url ?>' onclick="if (confirm('<?php echo(js_escape(sprintf( __("You are about to delete this draft '%s'\n  'Cancel' to stop, 'OK' to delete."), $draft->id ))); ?>')) return true; return false;">
						<?php _e('Delete&nbsp;mail','MailPress'); ?>
					</a>
					<br class="clear" />
<?php

		printf(__('Last edited by %1$s on %2$s at %3$s','MailPress'), wp_specialchars( $last_user->display_name ), mysql2date(get_option('date_format'), $draft->created), mysql2date(get_option('time_format'), $draft->created));
 	} 
?>
					<br class="clear" />
				</p>
				<div class="side-info">
					<h5><?php _e('Related','MailPress'); ?></h5>
					<ul>
						<li><a href="<?php echo MailPress_mails; ?>"><?php _e('Manage All Mails','MailPress'); ?></a></li>
					</ul>
				</div>
			</div>
			<div id='post-body'>
				<table class='form-table'>
					<tr>
						<th>
							<?php _e('From','MailPress'); ?> 
						</th>
						<td> 
							<?php _e('Email : ','MailPress'); ?> 
							<input type='text' size='30' value='<?php echo $draft->fromemail; ?>' disabled='disabled' />
							&nbsp;&nbsp;&nbsp;
							<?php _e('Name : ','MailPress'); ?> 
							<input type='text' size='30' value='<?php echo $draft->fromname; ?>' disabled='disabled' />
						</td>
					</tr>
					<tr>
						<th>
							<?php _e('To','MailPress'); ?> 
						</th>
						<td> 
							<?php _e('Email : ','MailPress'); ?> 
							<input type='text' size='30' name='toemail' value='<?php echo $draft->toemail; ?>' <?php echo $style; ?>/>
							&nbsp;&nbsp;&nbsp;
							<?php _e('Name : ','MailPress'); ?> 
							<input type='text' size='30' name='toname'  value='<?php echo $draft->toname; ?>' />
							<br/>
							<?php _e('OR all','MailPress'); ?>
							&nbsp;&nbsp;
							<select name='to_list' <?php echo $style; ?>>
<?php MP_Admin::select_option($mail_to_lists,$draft->to_list) ?>
							</select>
						</td>
					</tr>
					<tr>
						<th>
							<?php _e('Subject','MailPress'); ?>
						</th>
						<td> 
							<input type='text' size='80' id='subject' name='subject' value="<?php echo htmlspecialchars(stripslashes($draft->subject)); ?>" />
						</td>
					</tr>
					<tr>
						<th>
							<?php _e('Html','MailPress') ?>
						</th>
						<td> 
							<textarea rows='15' cols='80' name='html'><?php echo htmlspecialchars(stripslashes($draft->html)); ?></textarea>
						</td>
					</tr>
				</table>
				<br/>
				<div id="plaintext" class="postbox <?php echo postbox_classes('plaintext', 'mailpress'); ?>">
					<h3><?php _e('Plain Text','MailPress') ?></h3>
					<div class="inside">
						<textarea rows='10' cols='90' name='plaintext'><?php echo htmlspecialchars(stripslashes($draft->plaintext), ENT_QUOTES); ?></textarea>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>