<?php
if (!isset($_POST['formname'])) return;
if ($_POST['formname'] != 'tracking.form') return;

global $mp_general, $mp_tab;

$mp_general['tab']	= $mp_tab =  'MailPress_tracking';

$tracking	= $_POST['tracking'];

if (!add_option ('MailPress_tracking', $tracking, 'MailPress - tracking config' )) update_option ('MailPress_tracking', $tracking);
if (!add_option ('MailPress_general', $mp_general, 'MailPress - general settings' )) update_option ('MailPress_general', $mp_general);

MP_AdminPage::message(__("'Tracking' settings saved", 'MP_TXTDOM));
?>