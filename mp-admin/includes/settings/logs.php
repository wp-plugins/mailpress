<?php
$mp_general['tab']	= $mp_tab =  4;
	
foreach ($_POST['logs'] as $k => $v) $logs[$k] = $v; // so we don't delete settings if addon deactivated !
	
if (!add_option ('MailPress_logs', $logs ))  update_option ('MailPress_logs', $logs );
if (!add_option ('MailPress_general', $mp_general )) update_option ('MailPress_general', $mp_general);

self::message(__('Logs settings saved', 'MailPress'));
?>