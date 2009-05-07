<?php
require 'class/swift/Swift.php';

if (!class_exists('MP_Mail'))       include ('class/MP_Mail.class.php');
if (!class_exists('MP_Mailmeta'))   include ('class/MP_Mailmeta.class.php');
if (!class_exists('MP_User'))       include ('class/MP_User.class.php');
if (!class_exists('MP_Usermeta'))   include ('class/MP_Usermeta.class.php');
if (!class_exists('MP_Themes')) 	include ('class/MP_Themes.class.php');
if (!class_exists('MP_Log')) 		include ('class/MP_Log.class.php');
if (!class_exists('MP_Admin'))      include ('class/MP_Admin.class.php');
if (!class_exists('MP_Newsletter')) include ('class/MP_Newsletter.class.php');
if (!class_exists('MP_Widget'))     include ('class/MP_Widget.class.php');
?>