<?php
//define('DOING_AJAX', true);
define('WP_ADMIN', true);
include('../../../../wp-load.php');
include('../../../../wp-admin/includes/admin.php');
do_action('admin_init');
include('class/MP_Actions.class.php');
$MP_Actions = new MP_Actions();
?>