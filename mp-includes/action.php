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
		$page = isset( $_POST['page'] )? $_POST['page'] : '';
		if ( !preg_match( '/^[a-z-]+$/', $page ) ) {
			die(-1);
		}
		if (!is_array($closed)) break;
		$current_user = wp_get_current_user();
		update_usermeta($current_user->ID, 'closedpostboxes_mailpress_'.$page, $closed);
	break;
	default:
		do_action( 'mp_action_' . $action );
	break;
}
die();
?>