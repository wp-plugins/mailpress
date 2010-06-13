<?php
class MP_Newsletter_scheduler_post_cat extends MP_Newsletters_scheduler_post_abstract
{
	public $id = 'post_cat';

	function get_meta_key($newsletter)
	{
		return '_MailPress_published_category_' . $newsletter['params']['cat_id'];
	}
}
new MP_Newsletter_scheduler_post_cat(__('Per post/category', MP_TXTDOM));