<?php
if ($_POST['formname'] != 'filter_img.form') return;

global $mp_general, $mp_tab;

$mp_general['tab'] = $mp_tab = 'MailPress_filter_img';

$filter_img	= $_POST['filter_img'];

if (!add_option ('MailPress_filter_img', $filter_img, 'MailPress - filter_img config' )) update_option ('MailPress_filter_img', $filter_img);
if (!add_option ('MailPress_general', $mp_general, 'MailPress - general settings' )) update_option ('MailPress_general', $mp_general);

MP_AdminPage::message(__("'Image filter' settings saved", MP_TXTDOM));
?>