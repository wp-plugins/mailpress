<?php
if ($_POST['formname'] != 'roles_and_capabilities.form') return;

global $mp_general, $mp_tab;

$mp_general['tab']	= $mp_tab =  'MailPress_roles_and_capabilities';

global $wp_roles;
foreach($wp_roles->role_names as $role => $name)
{
	if ('administrator' == $role) continue;
	$rcs	= $_POST['cap'][$role];
	if (!add_option ('MailPress_r&c_' . $role, $rcs, 'MailPress - roles and capabilities config' )) update_option ('MailPress_r&c_' . $role, $rcs);
}
if (!add_option ('MailPress_general', $mp_general, 'MailPress - general settings' )) update_option ('MailPress_general', $mp_general);

MP_AdminPage::message(__("'Roles and capabilities' settings saved", 'MailPress'));
?>