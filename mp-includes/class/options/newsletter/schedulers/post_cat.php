<?php
class MP_Newsletter_scheduler_post_cat extends MP_newsletter_scheduler_post_
{
	public $id = 'post_cat';
	public $taxonomy  = 'category';
}
new MP_Newsletter_scheduler_post_cat(__('Per post/category', MP_TXTDOM));