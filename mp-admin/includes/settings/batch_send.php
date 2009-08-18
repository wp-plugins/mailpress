<?php
if ($_POST['formname'] != 'batch_send.form') return;

global $mp_general, $mp_tab;

$mp_general['tab'] = $mp_tab = 'MailPress_batch_send';

$batch_send	= $_POST['batch_send'];

if (!add_option ('MailPress_batch_send', $batch_send, 'MailPress - batch_send config' )) update_option ('MailPress_batch_send', $batch_send);
if (!add_option ('MailPress_general', $mp_general, 'MailPress - general settings' )) update_option ('MailPress_general', $mp_general);

MP_AdminPage::message(__("'Batch' settings saved", 'MailPress'));
?>