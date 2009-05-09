<?php 
if (!current_user_can('MailPress_edit_mails')) wp_die(__('You do not have sufficient permissions to access this page.'));

if ( isset($_GET['file']))
	switch ($_GET['file']) 
	{
		case 'mail' : 
			include('includes/mail.php');
		break;
		default :
			include('mail_new.php');
		break;
	}
else						include('includes/mails.php');
?>