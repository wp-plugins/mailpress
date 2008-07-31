<?php 
if ( isset($_GET['file']) && ($_GET['file'] == 'uzer')) 	include('includes/uzer.php');
else										include('includes/uzers.php');
?>