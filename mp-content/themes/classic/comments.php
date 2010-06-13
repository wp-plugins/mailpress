<?php
/*
Template Name: comments
*/

$post = get_post($this->args->p->id);

$_the_title = 'Comment # {{c[id]}} in "{{the_title}}"';

$_the_actions = "<a " . $this->classes('button', false) . " href='" . $post->guid . "#comment-" . $this->args->c->id . "'>" . __('Reply') . "</a>";

include('_mail.php');