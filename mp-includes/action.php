<?php
include('../../../../wp-config.php');
include('../../../../wp-admin/includes/admin.php');

if 	    ( ( isset($_GET['tg']) ) && !( isset($_POST['action']) || isset($_GET['action'])) ) $action = 'tracking';
elseif	( isset($_POST['action']) ) 	$action = $_POST['action'];
elseif 	( isset($_GET['action'])  ) 	$action = $_GET['action'];  
else 		die(-1);

switch ($action)
{
	case 'tracking' :
		do_action('mp_action_' . $action ); // will activate if any !
		MP_Mail::end_of_tracking();
	break;

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
	break;

	case 'iframe_upload' :
	
		$id 		= $_GET['id'];
		$draft_id 	= $_GET['draft_id'];
		$file 	= $_GET['file'];
		$url 		= get_option( 'siteurl' ) . '/' . MP_PATH . 'mp-includes/action.php';
		$bytes 	= apply_filters( 'import_upload_size_limit', wp_max_upload_size() );
		$size 	= wp_convert_bytes_to_hr( $bytes );
		include('upload-iframe.html.php');
	break;

	case 'attach_download' :
		$mmeta_id 	= $_GET['id'];

		$meta = MP_Mailmeta::get_by_id($mmeta_id);
        $meta_value = unserialize( $meta->meta_value );

		if (!$meta_value)    						die(__('Cannot Open Attachement 1!','MailPress'));
		if (!is_file($meta_value['file_fullpath'])) 	die(__('Cannot Open Attachement 2! ' . $meta_value['file_fullpath'],'MailPress'));

		$file = $meta_value['name'];
		if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) $file = preg_replace('/\./', '%2e', $file, substr_count($file, '.') - 1);

		if(!$fdl=@fopen($meta_value['file_fullpath'],'r')) 	die(__('Cannot Open Attachement 3!','MailPress'));

		header("Cache-Control: ");# leave blank to avoid IE errors
		header("Pragma: ");# leave blank to avoid IE errors
		header("Content-type: " . $meta_value['mime_type']);
		header("Content-Disposition: attachment; filename=\"".$file."\"");
		header("Content-length:".(string)(filesize($meta_value['file_fullpath'])));
		sleep(1);
		fpassthru($fdl);
	break;

	default:
		do_action('mp_action_' . $action );
	break;
}
die();
?>