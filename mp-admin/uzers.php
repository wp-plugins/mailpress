<?php 
if (!current_user_can('MailPress_edit_users')) wp_die(__('You do not have sufficient permissions to access this page.'));

if ( isset($_GET['file']) && ($_GET['file'] == 'uzer')) 	include('includes/uzer.php');
else										include('includes/uzers.php');
?>