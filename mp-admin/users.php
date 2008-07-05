<?php 
if ( isset($_GET['file']) && ($_GET['file'] == 'user')) 	include('includes/user.php');
else										include('includes/users.php');
?>