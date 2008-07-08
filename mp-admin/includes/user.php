<?php
//
///////////////////////////////////////////////////////////////////
///////////////////    MailPress  Functions    ////////////////////
///////////////////////////////////////////////////////////////////
//
$list_url = MP_Admin::url(MailPress_users,false,MP_Admin::get_url_parms());

if ( isset($_POST['action']) ) $action = $_POST['action'];
elseif ( isset($_GET['action']) ) $action = $_GET['action'];  

switch($action) 
{
	case 'delete':
		$id = $_GET['id'];
		MP_User::set_user_status( $id, 'delete' );
	break;
	case 'activate':
		$id = $_GET['id'];
		MP_User::set_user_status( $id, 'active' );
	break;
	case 'deactivate':
		$id = $_GET['id'];
		MP_User::set_user_status( $id, 'waiting' );
	break;
} 
wp_redirect($list_url);
?>