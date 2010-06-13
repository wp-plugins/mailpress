<?php
class MP_Newsletter_processor_previous_week extends MP_Newsletters_processor_abstract
{
	public $id = 'week-1';

	function process($newsletter, $trace)
	{
		$threshold = get_option($newsletter['processor']['threshold']);
		unset($threshold['threshold']);

	// new day of week
		$new_day_of_week = (isset($newsletter['processor']['wstart']) && is_numeric($newsletter['processor']['wstart'])) ? $newsletter['processor']['wstart'] : get_option('start_of_week');
		if ($new_day_of_week === false) 				$new_day_of_week = 1;
		if ($new_day_of_week < 0 || $new_day_of_week > 6) 	$new_day_of_week = 1;
		$new_day_of_week = (int) $new_day_of_week;
	// new end of week
		$new_end_of_week = $this->time;
		while (date('w', $new_end_of_week) != $new_day_of_week) $new_end_of_week -= 24*60*60;
		$new_end_of_week -= 24*60*60;

	// old day of week
		$old_day_of_week = ($threshold) ? ((isset($threshold['day_of_week'])) ? $threshold['day_of_week'] : 1) : $new_day_of_week;
	// old end of week
		$stored = (isset($threshold['end_of_week'])) ? $threshold['end_of_week'] : false;
		$comput = $new_end_of_week - 7*24*60*60;
		$old_end_of_week = ($stored > $comput) ? $stored : $comput;

		if ( date('Ymd', $old_end_of_week) >= date('Ymd', $new_end_of_week) )
		{
			MP_Newsletters_processors::message_report($newsletter, 'newsletter already processed : (' . date('Ymd', $old_end_of_week) . ' >= ' . date('Ymd', $new_end_of_week) . ') ', $trace, true);
			return false;
		}

		$threshold['day_of_week'] = $new_day_of_week;
		$threshold['end_of_week'] = $new_end_of_week;

		if (!update_option($newsletter['processor']['threshold'], $threshold))
			add_option($newsletter['processor']['threshold'],   $threshold);

		$report_newsletter = $newsletter;

		if (1 == $new_day_of_week)
		{
			$newsletter['processor']['query_posts']['year'] = date('o', $new_end_of_week);
			$newsletter['processor']['query_posts']['w']    = date('W', $new_end_of_week);

			if (1 != $old_day_of_week)
			{
				add_filter('posts_where', array(&$this, 'posts_where'), 8, 1);
				$this->end_of_previous_week = $old_end_of_week;

				global $wpdb;
				MP_Newsletters_processors::message_report($report_newsletter, "filter(posts_where) : \" AND $wpdb->posts.post_date >  '" . date('Y-m-d 23:59:59', $this->end_of_previous_week ) . "' \" ", $trace);
				$report_newsletter = false;
			}
		}
		else
		{
			add_filter('posts_where', array(&$this, 'posts_where'), 8, 1);
			$this->end_of_previous_week 	= $old_end_of_week;
			$this->end_of_week 		= $new_end_of_week;

			global $wpdb;
			MP_Newsletters_processors::message_report($report_newsletter, "filter(posts_where) : \" AND $wpdb->posts.post_date >  '" . date('Y-m-d 23:59:59', $this->end_of_previous_week ) . "' \" ", $trace);
			$report_newsletter = false;
			MP_Newsletters_processors::message_report($report_newsletter, "filter(posts_where) : \" AND $wpdb->posts.post_date <= '" . date('Y-m-d 23:59:59', $this->end_of_week ) . "' \" ", $trace);
		}

		$newsletter['query_posts'] = isset($newsletter['processor']['query_posts']) ? $newsletter['processor']['query_posts'] : array();

		MP_Newsletters_processors::send($newsletter, $trace);

		remove_filter('posts_where', array(&$this, 'posts_where'));
	}

	function posts_where($where)
	{
		global $wpdb;

		if (isset($this->end_of_previous_week)) 	$where .= " AND $wpdb->posts.post_date >  '" . date('Y-m-d 23:59:59', $this->end_of_previous_week ) . "' ";
		if (isset($this->end_of_week)) 		$where .= " AND $wpdb->posts.post_date <= '" . date('Y-m-d 23:59:59', $this->end_of_week ) . "' ";

		return $where;
	}
}
new MP_Newsletter_processor_previous_week(__('Previous week', MP_TXTDOM));