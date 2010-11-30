<?php
/*
Template Name: new_subscriber
*/

$_the_title 	= sprintf('Abonnement à %1$s', get_option('blogname'));

$_the_content 	= sprintf('Confirmer votre abonnement en cliquant sur le lien suivant : %1$s ', '{{subscribe}}');

unset($this->args->unsubscribe);
include('_mail.php');