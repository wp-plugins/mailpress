<?php
class MP_Newsletter_processor_now extends MP_Newsletters_processor_abstract
{
	public $id = 'now';

	function process($newsletter, $trace)
	{
		$this->post_id  = $newsletter['params']['post_id'];
		$this->meta_key = $newsletter['params']['meta_key'];

	// detect if post already processed
		if ($this->already_processed()) 
		{
			MP_Newsletters_processors::message_report($newsletter, "Post {$this->post_id} already processed", $trace);
			return false;
		}

	// detect if any category required

		$cats		= $this->get_cats($newsletter, 'cat',			'intval');
		$cats_in	= $this->get_cats($newsletter, 'category__in',     	'absint');
		$cats_out	= $this->get_cats($newsletter, 'category__not_in', 	'absint');

		if (!empty($cats)) foreach ( $cats as $cat )
		{
			$in = ($cat > 0);
			$cat = abs($cat);
			if ( $in ) {
				$cats_in[] = $cat;
				$cats_in   = array_merge($cats_in,  get_term_children($cat, 'category'));
			} else {
				$cats_out[]= $cat;
				$cats_out  = array_merge($cats_out, get_term_children($cat, 'category'));
			}
		}

		if (!empty($cats_in))
		{
			$post_categories = wp_get_post_categories($this->post_id);
			sort($post_categories);

			$cats_in = array_unique($cats_in);
			sort($cats_in);

			$intersect  = array_intersect($post_categories, $cats_in);
			if (empty($intersect))
			{
				MP_Newsletters_processors::message_report($newsletter, "newsletter categories (in) : " 	. join(',', $cats_in), $trace);
				MP_Newsletters_processors::message_report(false, "post categories : " . join(',', $post_categories), $trace);
				MP_Newsletters_processors::message_report(false, "Post {$this->post_id} not in required categories", $trace);
				return false;
			}
		}

		if (!empty($cats_out))
		{
			$post_categories = wp_get_post_categories($this->post_id);
			foreach($post_categories as $cat) $post_categories = array_merge($post_categories, get_term_children($cat, 'category'));
			$post_categories = array_unique($post_categories);
			sort($post_categories);

			$cats_out = array_unique($cats_out);
			sort($cats_out);

			$diff  = array_diff($post_categories, $cats_out);
			if (empty($diff))
			{
				MP_Newsletters_processors::message_report($newsletter, "newsletter categories (out) : " 	. join(',', $cats_out), $trace);
				MP_Newsletters_processors::message_report(false, "post categories : " . join(',', $post_categories), $trace);
				MP_Newsletters_processors::message_report(false, "Post {$this->post_id} in excluding categories", $trace);
				return false;
			}
		}

		$newsletter['query_posts'] = isset($newsletter['processor']['query_posts']) ? $newsletter['processor']['query_posts'] : array();

		MP_Newsletters_processors::send($newsletter, $trace);
	}

	function already_processed()
	{
		if (get_post_meta($this->post_id, $this->meta_key))
			return true;

		add_post_meta($this->post_id, $this->meta_key, true, true);
		return false;
	}

	function get_cats($newsletter, $arg, $array_map)
	{
		if (!isset($newsletter['processor']['query_posts'][$arg])) return array();
		if ( empty($newsletter['processor']['query_posts'][$arg])) return array();
		if (!is_array($newsletter['processor']['query_posts'][$arg])) $newsletter['processor']['query_posts'][$arg] = array($newsletter['processor']['query_posts'][$arg]);

		$cats = join(',', $newsletter['processor']['query_posts'][$arg]);

		return array_map($array_map, preg_split('/[,\s]+/', $cats));
	}
}
new MP_Newsletter_processor_now(__('Now', MP_TXTDOM));