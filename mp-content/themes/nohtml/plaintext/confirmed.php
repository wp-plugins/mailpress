<?php
/*
Template Name: confirmed
*/

$_the_title 	= 'Congratulations !';

$_the_content 	= sprintf('You are now a subscriber of %1$s [%2$s] ', get_option('blogname'), get_option('siteurl'));

include('_mail.php');