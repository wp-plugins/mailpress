<?php
abstract class MP_newsletter_processor_now_ extends MP_newsletter_processor_
{
	function process($newsletter, $trace)
	{
		$this->newsletter = $newsletter;
		$this->trace 	= $trace;

		$this->post_id  = $this->newsletter['params']['post_id'];
		$this->meta_key = $this->newsletter['params']['meta_key'];
		$this->post_type= (isset($this->newsletter['params']['post_type'])) ? $this->newsletter['params']['post_type'] : 'post';

	// detect if post already processed
		if ($this->already_processed()) 
		{
			MP_Newsletter_processors::message_report($this->newsletter, "{$this->post_type} {$this->post_id} already processed", $this->trace);
			return false;
		}

	// detect if anything else is required
		if (!$this->what_else()) return false;

		$this->newsletter['query_posts'] = isset($this->newsletter[$this->args]['query_posts']) ? $this->newsletter[$this->args]['query_posts'] : array();

		MP_Newsletter_processors::send($this->newsletter, $this->trace);
	}

	function already_processed()
	{
		if (get_post_meta($this->post_id, $this->meta_key))
			return true;

		add_post_meta($this->post_id, $this->meta_key, true, true);
		return false;
	}

	function what_else()
	{
		return true;
	}
}