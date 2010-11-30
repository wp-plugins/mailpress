<?php
/*
Template Name: new_subscriber
Subject: [<?php bloginfo('name');?>] <?php printf( __('En attente de : %s', MP_TXTDOM), '{{toemail}}'); ?>
*/

$_the_title = "Validation email";

$_the_content = "Veuillez <a " . $this->classes('button', false) . "href='{{subscribe}}'>confirmer</a> votre adresse mail.";
$_the_content .= '<br />';

unset($this->args->unsubscribe);
include('_mail.php');