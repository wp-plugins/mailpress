<?php
if (!current_user_can('MailPress_edit_users')) wp_die(__('You do not have sufficient permissions to access this page.'));

function mp_usermeta_meta_box($mp_user)
{
?>
<div id="user-import">
<?php
	$header = true;
	$metas = MP_User::get_usermeta($mp_user->id);

	if ($metas)
	{
		if (!is_array($metas)) $metas = array($metas);

		foreach ($metas as $meta)
		{
			if ($meta->meta_key[0] == '_') continue;

			if ($header)
			{
				$header = false;
?>
	<table class='form-table'>
		<thead>
			<tr>
				<td style='border-bottom:none;padding:0px;width:20px;'>
				</td>
				<td style='border-bottom:none;padding:0px;'>
<b><?php _e('Key') ?></b>
				</td>
				<td style='border-bottom:none;padding:0px;'>
<b><?php _e('Value') ?></b>
				</td>
			</tr>
		</thead>
		<tbody>
<?php
			}
?>
			<tr>
				<td style='border-bottom:none;padding:0px;width:20px;'></td>
				<td style='border-bottom:none;line-height:0.8em;padding:0px;'>
					<input style='padding:3px;margin:0 10px 0 0;width:250px;' type='text' disabled='disabled' value="<?php echo $meta->meta_key; ?>"/>
				</td>
				<td style='border-bottom:none;line-height:0.8em;padding:0px;'>
					<input style='padding:3px;margin:0 10px 0 0;width:250px;' type='text' disabled='disabled' value="<?php echo $meta->meta_value; ?>"/>
				</td>
			</tr>
<?php
		}
	}
	
	if ($header) 	_e('No meta data','MailPress_import');
	else
	{
?>
			<tr>
				<td style='border-bottom:none;padding:0px;width:20px;'>&nbsp;</td>
				<td style='border-bottom:none;padding:0px;width:20px;'></td>
				<td style='border-bottom:none;padding:0px;width:20px;'></td>
			</tr>
		</tbody>
	</table>
<?php
	}
?>
</div>
<?php
}

$url_parms 	= MP_Admin::get_url_parms();

if (isset($_POST['id']))
{
	$mp_user 	= MP_User::get_user( $_POST['id'] );	
	$active = ('active' == MP_User::get_user_status($mp_user->id)) ? true : false;

	MailPress::update_mp_user_comments($mp_user->id);
	if ($active)
	{
		MP_Newsletter::update_mp_user_newsletters($mp_user->id);
	}
	$fade = __('MailPress user saved','MailPress');
}
else
{
	$mp_user 	= MP_User::get_user( $_GET['id'] );	
	$active = ('active' == MP_User::get_user_status($mp_user->id)) ? true : false;
}

$h2 = sprintf( __('Edit MailPress User # %1$s','MailPress'), $mp_user->id);

$delete_url = clean_url(MP_Admin::url(MailPress_user  ."&amp;action=delete&amp;id=$mp_user->id",false,$url_parms));
$write_url  = clean_url(MailPress_write . '&toemail=' . $mp_user->email);

$last_date  = ($mp_user->created > $mp_user->laststatus) ? $mp_user->created : $mp_user->laststatus ;
$last_user 	= ($mp_user->created > $mp_user->laststatus) ? $mp_user->created_user_id : $mp_user->laststatus_user_id ;
$last_user 	= get_userdata($last_user );

$check_comments = MailPress::checklist_mp_user_comments($mp_user->id);
if ($active)
{
	$check_newsletters = MP_Newsletter::checklist_mp_user_newsletters($mp_user->id,array('admin' => true));
}

$rowspan = 1;
if ($check_comments) 	$rowspan++;
if ($check_newsletters)	$rowspan++;
$rowspan = ($rowspan > 1) ? " rowspan='$rowspan'" : '';

$h21 		= (has_action('MailPress_user_advanced')) ? __('Advanced Options','MailPress') : false ; 

$metas = MP_User::get_usermeta($mp_user->id);
if ($metas) 
{
	if (!is_array($metas)) $metas = array($metas);
	foreach ($metas as $meta)
	{
		if ($meta->meta_key[0] == '_') continue;
		add_meta_box('mp_usermetadiv', __('Custom Fields','MailPress_import') , 'mp_usermeta_meta_box', 'MailPress_user', 'advanced', 'low');
		break;
	}
}

if (isset($fade)) MP_Admin::message($fade); 
?>
<form id='mp_user' name='mp_user_form' action='' method='post'>
	<div class='wrap'>
		<input type="hidden" name='id' 		value="<?php echo $mp_user->id ?>" id='mp_user_id' />
		<input type="hidden" name='referredby' 	value='<?php echo clean_url($_SERVER['HTTP_REFERER']); ?>' />
		<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>

		<h2>
			<?php echo $h2; ?>
		</h2>
		<div id='poststuff'>
			<div id='submitcomment' class='submitbox' style='margin-top:13px;'>
				<div id="previewview">
					&nbsp;<br/>
				</div>
				<div class="inside">
					<br class="clear" />
					<br class="clear" />
				</div>
				<p class="submit">
					<input name='save' value='<?php _e('Save','MailPress'); ?>' type="submit" style="font-weight: bold;"/>
<?php if (current_user_can('MailPress_delete_users')) : ?>
					<a class='submitdelete' href='<?php echo $delete_url ?>' onclick="if (confirm('<?php echo(js_escape(sprintf( __("You are about to delete this MailPress user '%s'\n  'Cancel' to stop, 'OK' to delete.",'MailPress'), $mp_user->id ))); ?>')) return true; return false;">
						<?php _e('Delete&nbsp;MailPress&nbsp;user','MailPress'); ?>
					</a>
<?php endif; ?>
					<br class="clear" />
					<!-- <?php printf(__('Last edited by %1$s on %2$s at %3$s','MailPress'), wp_specialchars( $last_user->display_name ), mysql2date(get_option('date_format'), $last_date), mysql2date(get_option('time_format'), $last_date)); ?> -->
					<br class="clear" />
				</p>
				<div class="side-info">
					<h5><?php _e('Related','MailPress'); ?></h5>
					<ul>
						<?php if (current_user_can('MailPress_edit_mails')) : ?><li><a href="<?php echo $write_url; ?>"><?php _e('Write to this user','MailPress'); ?></a></li><?php endif; ?>
						<li><a href="<?php echo MailPress_users; ?>"><?php _e('Manage All users','MailPress'); ?></a></li>
<?php do_action('MailPress_user_relatedlinks'); ?>
					</ul>
				</div>
			</div>
			<div id='post-body'>
				<div>
					<table class='form-table'>
						<tbody>
							<tr valign='top'>
								<th scope='row'>
									<?php _e('Email','MailPress'); ?>
								</th>
								<td>
									<input type='text' disabled='disabled' value='<?php echo $mp_user->email; ?>' size='30'/>
								</td>
								<td class='avatar' <?php echo $rowspan; ?>>
<?php if (get_option('show_avatars')) echo (get_avatar( $mp_user->email, 100 )); ?>
<br/><br/>
<?php MP_User::flag_IP(); ?>
								</td>
							</tr>
<?php if ($check_comments) : ?>
			<tr>
				<th scope="row">
					<?php _e('Comments'); ?>
				</th>
				<td>
					<?php echo $check_comments; $ok = true; ?>
				</td>
			</tr>
<?php endif; ?> 
<?php if ($check_newsletters) : ?>
			<tr>
				<th scope="row">
					<?php _e('Newsletters','MailPress'); ?>
				</th>
				<td>
					<?php echo $check_newsletters ; $ok = true; ?>
				</td>
			</tr>
<?php endif; ?> 	
						</tbody>
					</table>
					<br />
				</div>
<?php
do_action('MailPress_user_form',$mp_user->id);
do_meta_boxes('MailPress_user','normal',$mp_user);
if ($h21) echo "\n<h2> $h21 </h2>\n";
do_action('MailPress_user_advanced',$mp_user->id);
do_meta_boxes('MailPress_user','advanced',$mp_user);
?>
			</div>
		</div>
	</div>
</form>