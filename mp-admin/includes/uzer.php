<?php
if (!current_user_can('MailPress_edit_users')) wp_die(__('You do not have sufficient permissions to access this page.'));

global $mp_screen, $mp_user ;

if (isset($_POST['id']))
{
	$mp_user 	= MP_User::get( $_POST['id'] );	
	$active = ('active' == MP_User::get_status($mp_user->id)) ? true : false;

	MailPress::update_mp_user_comments($mp_user->id);
	if ($active)
	{
		MP_Newsletter::update_mp_user_newsletters($mp_user->id);
	}

	do_action('MailPress_update_user_meta_box');

	$fade = __('MailPress user saved','MailPress');
}
else
{
	$mp_user 	= MP_User::get( $_GET['id'] );	
	$active = ('active' == MP_User::get_status($mp_user->id)) ? true : false;
}

$h2 = sprintf( __('Edit MailPress User # %1$s','MailPress'), $mp_user->id);

//$delete_url = clean_url(MP_Admin::url(MailPress_user  ."&amp;action=delete&amp;id=$mp_user->id",false,$url_parms));
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

?>
<?php if (isset($fade)) MP_Admin::message($fade); ?>
<div class='wrap'>
	<div id="icon-mailpress-users" class="icon32"><br /></div>
	<h2><?php echo $h2; ?></h2>
	<form id='mp_user' name='mp_user_form' action='' method='post'>

		<input type="hidden" name='id' 		value="<?php echo $mp_user->id ?>" id='mp_user_id' />
		<input type="hidden" name='referredby' 	value='<?php echo clean_url($_SERVER['HTTP_REFERER']); ?>' />
		<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
		<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>

		<div id='poststuff' class='metabox-holder has-right-sidebar'>
			<div id="side-info-column" class="inner-sidebar">
<?php $side_meta_boxes = do_meta_boxes($mp_screen, 'side', $mp_user); ?>
			</div>

			<div id="post-body" class="<?php echo $side_meta_boxes ? 'has-sidebar' : ''; ?>">
				<div id="post-body-content" class="has-sidebar-content">
					<table class='form-table'>
						<tbody>
							<tr valign='top'>
								<th scope='row' class='h1em'>
									<?php _e('Email','MailPress'); ?>
								</th>
								<td class='h1em'>
									<input type='text' disabled='disabled' value='<?php echo $mp_user->email; ?>' size='30' />
								</td>
								<td class='mp_avatar' <?php echo $rowspan; ?>>
<?php if (get_option('show_avatars')) echo (get_avatar( $mp_user->email, 100 )); ?>
<br /><br />
<?php echo MP_User::get_flag_IP(); ?>
								</td>
							</tr>
<?php if ($check_comments) : ?>
			<tr>
				<th scope="row">
					<?php _e('Comments'); ?>
				</th>
				<td class='checklist'>
					<?php echo $check_comments; $ok = true; ?>
				</td>
			</tr>
<?php endif; ?> 
<?php if ($check_newsletters) : ?>
			<tr>
				<th scope="row">
					<?php _e('Newsletters','MailPress'); ?>
				</th>
				<td class='checklist'>
					<?php echo $check_newsletters ; $ok = true; ?>
				</td>
			</tr>
<?php endif; ?> 	
						</tbody>
					</table>
					<br />

<?php

do_meta_boxes($mp_screen,'normal',$mp_user);
if ($h21) echo "\n<h2> $h21 </h2>\n";
do_meta_boxes($mp_screen,'advanced',$mp_user);
?>
				</div>
			</div>
		</div>
	</form>
</div>