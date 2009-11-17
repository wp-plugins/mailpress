<?php
if ($_POST['formname'] != 'connection_phpmail.form') return;

global $mp_general, $mp_tab;

$mp_general['tab']	= $mp_tab =  1;

$connection_phpmail	= $_POST['connection_phpmail'];

if (!add_option ('MailPress_connection_phpmail', $connection_phpmail, 'MailPress - connection_phpmail config' )) update_option ('MailPress_connection_phpmail', $connection_phpmail);
if (!add_option ('MailPress_general', $mp_general, 'MailPress - general settings' )) update_option ('MailPress_general', $mp_general);

MP_AdminPage::message(__("'PHP MAIL' settings saved", MP_TXTDOM));
?>