<?php
/*
Template Name: moderate
*/

$_the_title = sprintf( __('Nouveau commentaire pour l\'article "%2$s" [#%1$s]'), $this->args->c->post_ID , '{{the_title}}' );

$_the_content  = sprintf( __('Auteur : %1$s (IP: %2$s , %3$s)'), $this->args->c->author, $this->args->c->author_IP, $this->args->c->domain);
$_the_content .= "<br />\n"; 
$_the_content .= sprintf( __('E-mail : %s'), $this->args->c->email );
$_the_content .= "<br />\n"; 
$_the_content .= sprintf( __('URL    : %s'), $this->args->c->url);
$_the_content .= "<br />\n"; 
$_the_content .= sprintf( __('Whois  : http://ws.arin.net/cgi-bin/whois.pl?queryinput=%s'), $this->args->c->author_IP );
$_the_content .= "<br /><br />\n"; 
$_the_content .= __('Commentaire: ');
$_the_content .= "<br />\n"; 
$_the_content .= $this->args->c->content;
$_the_content .= "<br />\n"; 


$moderator = (isset($mail->p->title)) ? true : false;

$_the_actions = ($moderator)  ? "<a " . $this->classes('button', false) . " href='" . get_permalink($this->args->c->post_ID ) . "#comments'>" . __('View all') . "</a>"
					: "<a " . $this->classes('button', false) . " href='" . get_option('siteurl') . "/wp-admin/comment.php?action=mac&c=" . $this->args->c->id . "'>" . __('Approve') . "</a>";
$_the_actions .= "&nbsp;<a " . $this->classes('button', false) . " href='" . get_option('siteurl') . "/wp-admin/comment.php?action=cdc&c=" . $this->args->c->id . "'>" . __('Delete') . "</a>";
$_the_actions .= "&nbsp;<a " . $this->classes('button', false) . " href='" . get_option('siteurl') . "/wp-admin/comment.php?action=cdc&dt=spam&c=" . $this->args->c->id . "'>" . __('Spam') . "</a>";


include('_mail.php');