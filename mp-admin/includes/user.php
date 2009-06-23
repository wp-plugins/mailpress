<?php
global $wpdb, $mp_user;

$url_parms = self::get_url_parms(array('mode', 'status', 's', 'apage', 'author', 'mailinglist', 'startwith'));

//
// MANAGING RESULTS
//

if (isset($_POST['id']))
{
	self::require_class('Users');
	$mp_user = MP_Users::get( $_POST['id'] );

	if ($mp_user->name != $_POST['mp_user_name'])
	{
		MP_Users::update_name($mp_user->id, $_POST['mp_user_name']);
		$mp_user->name = $_POST['mp_user_name'];
	}
	$active  = ('active' == $mp_user->status) ? true : false;

	self::require_class('Comment');
	MP_Comment::update_checklist($mp_user->id);
	if ($active) 
	{
		self::require_class('Newsletter');
		MP_Newsletter::update_checklist($mp_user->id);
	}

	switch (true)
	{
		case isset($_POST['addmeta']) :
			self::require_class('Usermeta');
			MP_Usermeta::add_meta($mp_user->id);
		break;
		case isset($_POST['updatemeta']) :
			foreach ($_POST['meta'] as $umeta_id => $meta)
			{
				self::require_class('Usermeta');
				$meta_key = $meta['key'];
				$meta_value = $meta['value'];
				MP_Usermeta::update_by_id($umeta_id , $meta_key, $meta_value);
			}
		break;
		case isset($_POST['deletemeta']) :
			foreach ($_POST['deletemeta'] as $umeta_id => $x)
			{
				self::require_class('Usermeta');
				MP_Usermeta::delete_by_id( $umeta_id );
			}
		break;
	}

// what else ?
	do_action('MailPress_update_meta_boxes_user');

// messages
	$message = __('MailPress user saved', 'MailPress');
}
else
{
	self::require_class('Users');
	$mp_user = MP_Users::get( $_GET['id'] );
	$active  = ('active' == $mp_user->status) ? true : false;
}

$h2 = sprintf( __('Edit MailPress User # %1$s', 'MailPress'), $mp_user->id);

//
// User subscription datas
//

self::require_class('Comment');
$check_comments = MP_Comment::get_checklist($mp_user->id);
if ($active)
{
	self::require_class('Newsletter');
	$check_newsletters = MP_Newsletter::get_checklist($mp_user->id, array('admin' => true));
}

$rowspan = 2;
if (isset($check_comments) && $check_comments) 	$rowspan++;
if (isset($check_newsletters) && $check_newsletters)	$rowspan++;
$rowspan = ($rowspan > 1) ? " rowspan='$rowspan'" : '';
?>
<div class='wrap'>
	<div id="icon-mailpress-users" class="icon32"><br /></div>
	<h2><?php echo $h2; ?></h2>
<?php if (isset($message)) self::message($message); ?>
	<form id='mp_user' name='mp_user_form' action='' method='post'>

		<input type="hidden" name='id' 		value="<?php echo $mp_user->id ?>" id='mp_user_id' />
		<input type="hidden" name='referredby' 	value='<?php if(isset($_SERVER['HTTP_REFERER'])) echo clean_url($_SERVER['HTTP_REFERER']); ?>' />
		<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
		<?php wp_nonce_field( 'meta-box-order',  'meta-box-order-nonce', false ); ?>

		<div id='poststuff' class='metabox-holder has-right-sidebar'>
			<div id="side-info-column" class="inner-sidebar">
<?php $side_meta_boxes = do_meta_boxes(self::screen, 'side', $mp_user); ?>
			</div>

			<div id="post-body" class="<?php echo $side_meta_boxes ? 'has-sidebar' : ''; ?>">
				<div id="post-body-content" class="has-sidebar-content">
					<table class='form-table'>
						<tbody>
							<tr valign='top'>
								<th scope='row' class='h1em'>
									<?php _e('Email', 'MailPress'); ?>
								</th>
								<td class='h1em'>
									<input type='text' disabled='disabled' value='<?php echo $mp_user->email; ?>' size='30' />
								</td>
								<td class='mp_avatar' <?php echo $rowspan; ?>>
<?php if (get_option('show_avatars')) echo (get_avatar( $mp_user->email, 100 )); ?>
<br /><br />
<?php 
self::require_class('Users');
echo MP_Users::get_flag_IP(); 
?>
								</td>
							</tr>
							<tr valign='top'>
								<th scope='row' class='h1em'>
									<?php _e('Name', 'MailPress'); ?>
								</th>
								<td class='h1em'>
									<input name='mp_user_name' type='text' value="<?php echo self::input_text($mp_user->name); ?>" size='30' />
								</td>
							</tr>
<?php if (isset($check_comments) && $check_comments) : ?>
			<tr>
				<th scope="row">
					<?php _e('Comments'); ?>
				</th>
				<td class='checklist'>
					<?php echo $check_comments; $ok = true; ?>
				</td>
			</tr>
<?php endif; ?> 
<?php if (isset($check_newsletters) && $check_newsletters) : ?>
			<tr>
				<th scope="row">
					<?php _e('Newsletters', 'MailPress'); ?>
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

do_meta_boxes(self::screen, 'normal', $mp_user);
?>
				</div>
			</div>
		</div>
	</form>
</div>