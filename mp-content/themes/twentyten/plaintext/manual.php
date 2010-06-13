<?php
/*
Template Name: manual
*/

$this->get_header();
include('_loom.php');

MailPress::require_class('Posts');
global $MP_post_ids;
$MP_post_ids = MP_Posts::get_object_terms($this->args->main_id);
if ($MP_post_ids && !empty($MP_post_ids))
{
	// to set query_posts arguments
	$args = array( 'posts_per_page' => 15, 'post__in' => $MP_post_ids, 'caller_get_posts' => 1 );

	// to order posts in query_posts
	add_filter('posts_orderby', array('MP_theme_2010', 'posts_orderby'), 8, 1);

	// query_posts
	query_posts($args);

	remove_filter('posts_orderby', array('MP_theme_2010', 'posts_orderby'));

	// to tweak $this->the_content in manually newsletter with query_posts
	$this->args->newsletter = true;

	include('_loop.php');

	$this->args->newsletter = false;
}

$this->get_footer();