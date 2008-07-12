<?php 
if ( isset($_GET['file']) && ($_GET['file'] == 'mail')) 	include('includes/mail.php');
else										include('includes/mails.php');
?>