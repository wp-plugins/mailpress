<?php

$path = substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'wp-content'));

include($path . 'wp-config.php');
include($path . 'wp-admin/includes/admin.php');

do_action('mp_process_batch_send');

die();
?>