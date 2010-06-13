<?php
if ($_POST['formname'] != 'bounce_handling.form') return;

global $mp_general, $mp_tab;

$mp_general['tab'] = $mp_tab = 'MailPress_bounce_handling';
$bounce_handling	= $_POST['bounce_handling'];

$old_bounce_handling = get_option('MailPress_bounce_handling');

if (!add_option ('MailPress_bounce_handling', $bounce_handling )) update_option ('MailPress_bounce_handling', $bounce_handling);
if (!add_option ('MailPress_general', $mp_general )) update_option ('MailPress_general', $mp_general);

if (!isset($old_bounce_handling['batch_mode'])) $old_bounce_handling['batch_mode'] = '';
if ($old_bounce_handling['batch_mode'] != $bounce_handling['batch_mode'])
{
	if ('wpcron' != $bounce_handling['batch_mode']) wp_clear_scheduled_hook('mp_process_bounce_handling');
	else 								MailPress_bounce_handling::schedule();
}

MP_AdminPage::message(__("'Bounces' settings saved", MP_TXTDOM));
?>