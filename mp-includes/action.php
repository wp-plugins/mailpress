<?php
define('DOING_AJAX', true);
include('../../../../wp-config.php');
include('../../../../wp-admin/includes/admin.php');

if 		( isset($_POST['action']) ) 	$action = $_POST['action'];
elseif 	( isset($_GET['action']) ) 	$action = $_GET['action'];  
else 		die(-1);

switch ($action)
{

	case 'closed-postboxes' :

		$closed = isset( $_POST['closed'] )? $_POST['closed'] : '';
		$closed = explode( ',', $_POST['closed'] );
		$hidden = isset( $_POST['hidden'] )? $_POST['hidden'] : '';
		$hidden = explode( ',', $_POST['hidden'] );
		$page = isset( $_POST['page'] )? $_POST['page'] : '';
		if ( !preg_match( '/^[a-z-_]+$/', $page ) ) {
			die(-1);
		}
		$current_user = wp_get_current_user();
		if ( is_array($closed) )
			update_usermeta($current_user->ID, 'closedpostboxes_'.$page, $closed);
		if ( is_array($hidden) )
			update_usermeta($current_user->ID, 'meta-box-hidden_'.$page, $hidden);

	break;

	case 'meta-box-order':
		update_user_option( $GLOBALS['current_user']->ID, "meta-box-order_$_POST[page]", $_POST['order'] );
		die('1');
	break;

	case 'hidden-columns' :
	
		$hidden = isset( $_POST['hidden'] )? $_POST['hidden'] : '';
		$hidden = explode( ',', $_POST['hidden'] );
		$page = isset( $_POST['page'] )? $_POST['page'] : '';
		if ( !preg_match( '/^[a-z-_]+$/', $page ) ) {
			die(-1);
		}
		$current_user = wp_get_current_user();
		if ( is_array($hidden) )
			update_usermeta($current_user->ID, "_MailPress_manage-$page-columns-hidden", $hidden);

	default:
		do_action( 'mp_action_' . $action );
	break;
}
die();
?>