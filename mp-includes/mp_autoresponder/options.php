<?php
$mp_autoresponders_by_id = $mp_autoresponders_by_event = array();

$mp_autoresponder_registered_events[1] = 	array (	'desc'	=> __('Subscription activated', 'MailPress'), 
									'event'	=> 'MailPress_activate_user', 
									'callback'	=> array('MailPress_autoresponder', 'start_user_autoresponder')
							);
$mp_autoresponder_registered_events[2] = 	array (	'desc'	=> __('New commenter', 'MailPress'), 
									'event'	=> 'MailPress_new commenter', 
									'callback'	=> array('MailPress_autoresponder', 'start_user_autoresponder')
							);


foreach ($mp_autoresponder_registered_events as $k => $v)
{
	$mp_autoresponders_by_id [$k] =  $v['desc'];
	$mp_autoresponders_by_event [$v['event']][$k] =  $v['desc'];
}
?>