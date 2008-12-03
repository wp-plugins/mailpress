<?php 

if (!current_user_can('MailPress_edit_mails')) wp_die(__('You do not have sufficient permissions to access this page.'));

if ( isset($_GET['file']) && ($_GET['file'] == 'mail')) 	include('includes/mail.php');
else										include('includes/mails.php');
?>