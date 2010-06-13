<?php
/*
Template Name: comments
*/

$post = get_post($this->args->p->id);

$_the_title 	= 'Comment # {{c[id]}} in "{{the_title}}"';

$_the_actions 	= __('Reply') . ' [' . $post->guid . "#comment-" . $this->args->c->id . ']';

include('_mail.php');