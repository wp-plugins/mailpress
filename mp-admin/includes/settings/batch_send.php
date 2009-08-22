<?php
if ($_POST['formname'] != 'batch_send.form') return;

global $mp_general, $mp_tab;

$mp_general['tab'] = $mp_tab = 'MailPress_batch_send';

$batch_send	= $_POST['batch_send'];

$old_batch_send = get_option('MailPress_batch_send');

if (!add_option ('MailPress_batch_send', $batch_send )) update_option ('MailPress_batch_send', $batch_send);
if (!add_option ('MailPress_general', $mp_general    )) update_option ('MailPress_general', $mp_general);

if (!isset($old_batch_send['batch_mode'])) $old_batch_send['batch_mode'] = '';
if ($old_batch_send['batch_mode'] != $batch_send['batch_mode'])
{
	if ('wpcron' != $batch_send['batch_mode']) { wp_clear_scheduled_hook('mp_action_batchsend'); wp_clear_scheduled_hook('mp_process_batch_send'); }
	else							 MailPress_batch_send::schedule();
}

MP_AdminPage::message(__("'Batch' settings saved", 'MailPress'));
?>