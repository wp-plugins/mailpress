<?php
class MP_Newsletter_scheduler_post_cat extends MP_newsletter_scheduler_post_
{
	public $id        = 'post_cat';
	public $post_type = 'post';
	public $taxonomy  = 'category';
}
new MP_Newsletter_scheduler_post_cat(sprintf(__('Each %s', MP_TXTDOM), 'post/category'));