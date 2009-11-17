<?php
if ($_POST['formname'] != 'connection_sendmail.form') return;

global $mp_general, $mp_tab;

$mp_general['tab']	= $mp_tab =  1;

$connection_sendmail	= $_POST['connection_sendmail'];

if (!add_option ('MailPress_connection_sendmail', $connection_sendmail, 'MailPress - connection_sendmail config' )) update_option ('MailPress_connection_sendmail', $connection_sendmail);
if (!add_option ('MailPress_general', $mp_general, 'MailPress - general settings' )) update_option ('MailPress_general', $mp_general);

MP_AdminPage::message(__("'SENDMAIL' settings saved", MP_TXTDOM));
?>