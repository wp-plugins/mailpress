<?php
/*
Template Name: moderate
*/

$_the_title  = sprintf( __('Nouveau commentaire dans votre article "%2$s" [#%1$s]'), $this->args->c->post_ID , '{{the_title}}' );

$_the_content  = sprintf( __('Auteur : %1$s (IP: %2$s , %3$s)'), $this->args->c->author, $this->args->c->author_IP, $this->args->c->domain);
$_the_content .= "\n"; 
$_the_content .= sprintf( __('E-mail : %s'), $this->args->c->email );
$_the_content .= "\n"; 
$_the_content .= sprintf( __('URL    : %s'), $this->args->c->url);
$_the_content .= "\n"; 
$_the_content .= sprintf( __('Whois  : http://ws.arin.net/cgi-bin/whois.pl?queryinput=%s'), $this->args->c->author_IP );
$_the_content .= "\n\n"; 
$_the_content .= __('Commentaire : ');
$_the_content .= "\n"; 
$_the_content .= $this->args->c->content;
$_the_content .= "\n"; 

$moderator = (isset($mail->p->title)) ? true : false;

$_the_actions  = ($moderator)  ?  __('Tous') . ' [' . get_permalink($this->args->c->post_ID ) . '#comments]' . "\n"
					 :  __('Approuver')  . ' [' . get_option('siteurl') . "/wp-admin/comment.php?action=mac&c=" . $this->args->c->id . ']' . "\n";
$_the_actions  .= 		    __('Supprimer')  . ' [' . get_option('siteurl') . "/wp-admin/comment.php?action=cdc&c=" . $this->args->c->id . ']' . "\n";
$_the_actions  .= 		    __('Spam')       . ' [' . get_option('siteurl') . "/wp-admin/comment.php?action=cdc&dt=spam&c=" . $this->args->c->id . ']' . "\n";

include('_mail.php');