<?php
global $mp_subscriptions;
if (isset($mp_subscriptions['subcomment'])) 
	self::require_class('Comment');

self::require_class('Users');
$email	= MailPress::get_wp_user_email();
$mp_user	= MP_Users::get(MP_Users::get_id_by_email($email));
$active 	= ('active' == $mp_user->status) ? true : false;

if (isset($_POST['formname']) && ('sync_wordpress_user_subscriptions' == $_POST['formname']))
{
	if ($mp_user->name != $_POST['mp_user_name'])
	{
		MP_Users::update_name($mp_user->id, $_POST['mp_user_name']);
		$mp_user->name = $_POST['mp_user_name'];
	}
	if (isset($mp_subscriptions['subcomment']))
		MP_Comment::update_checklist($mp_user->id);

	if ($active)
	{
		MP_Newsletter::update_checklist($mp_user->id);
		if (class_exists('MailPress_mailinglist')) MailPress_mailinglist::update_checklist($mp_user->id);
	}
	$message = __('Subscriptions saved','MailPress');
}

$checklist_comments = $checklist_mailinglists = $checklist_newsletters = false;

if (isset($mp_subscriptions['subcomment']))
	$checklist_comments = MP_Comment::get_checklist($mp_user->id);

if ($active)
{
	$checklist_newsletters = MP_Newsletter::get_checklist($mp_user->id);
	if (class_exists('MailPress_mailinglist')) $checklist_mailinglists = MailPress_mailinglist::get_checklist($mp_user->id);
}

//
// MANAGING TITLE
//
	$h2    =  __('Manage Subscription','MailPress');
?>
<div class='wrap'>
	<form id='posts-filter' action='' method='post'>
		<div id="icon-mailpress-users" class="icon32"><br /></div>
	<h2><?php echo $h2; ?></h2>
<?php if (isset($message)) self::message($message); ?>
		<input type='hidden' name='page' value='<?php echo MailPress_page_subscriptions; ?>' />
		<input type='hidden' name='formname' value='sync_wordpress_user_subscriptions' />

		<table class="form-table">
			<tr>
				<th scope='row'><?php _e('Email', 'MailPress'); ?></th>
				<td>
					<input type='text' disabled='disabled' value='<?php echo $mp_user->email; ?>' size='30' />
				</td>
			</tr>
			<tr>
				<th scope='row'><?php _e('Name', 'MailPress'); ?></th>
				<td>
					<input name='mp_user_name' type='text' value="<?php echo self::input_text($mp_user->name); ?>" size='30' />
				</td>
			</tr>
<?php if ($checklist_comments) : $ok = true; ?>
			<tr>
				<th scope="row"><?php _e('Comments'); ?></th>
				<td>
					<?php echo $checklist_comments; ?>
				</td>
			</tr>
<?php endif; ?> 	
<?php if ($checklist_newsletters) : $ok = true; ?>
			<tr>
				<th scope="row"><?php _e('Newsletters','MailPress'); ?></th>
				<td>
					<?php echo $checklist_newsletters; ?>
				</td>
			</tr>
<?php endif; ?> 	
<?php if ($checklist_mailinglists) : $ok = true; ?>
			<tr>
				<th scope="row"><?php _e('Mailing lists','MailPress'); ?></th>
				<td>
					<?php echo $checklist_mailinglists; ?>
				</td>
			</tr>
<?php endif; ?>
		</table>
<?php if (isset($ok)) : ?> 
		<p class='submit'>
			<input class='button-primary' type='submit' name='Submit' value='<?php  _e('Save','MailPress'); ?>' />
		</p>
<?php else : ?> 
		<p>
<?php 
		if ($active) 	_e('Nothing to subscribe for ...','MailPress');
		else			_e('Your email has been deactivated, ask the administrator ...','MailPress');
?>
		</p>
<?php endif; ?> 
	</form>
</div>