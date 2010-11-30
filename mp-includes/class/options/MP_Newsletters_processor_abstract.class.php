<?php
abstract class MP_Newsletters_processor_abstract
{
	function __construct($description)
	{
		$this->description = $description;

		$this->time = current_time('timestamp');

		add_filter('MailPress_newsletter_processors_register', 			array(&$this, 'register'), 8, 1);

		add_action('MailPress_newsletter_processor_' . $this->id . '_process',	array(&$this, 'process'), 8, 2);
	}

	function register($processors) { $processors[$this->id] = $this->description; return $processors; }

	function process($newsletter, $trace)
	{
		$threshold = get_option($newsletter['processor']['threshold']);

		$old_threshold = ($threshold) ? $threshold['threshold'] : false;

		if ( $this->threshold <=  $old_threshold)
		{
			MP_Newsletters_processors::message_report($newsletter, "newsletter already processed : ($old_threshold >= {$this->threshold}) ", $trace, true);
			return false;
		}

		$threshold['threshold'] = $this->threshold;

		if (!update_option($newsletter['processor']['threshold'], $threshold))
			  add_option($newsletter['processor']['threshold'], $threshold);

		$newsletter['query_posts'] = $this->query_posts( (isset($newsletter['processor']['query_posts'])) ? $newsletter['processor']['query_posts'] : array() );

		MP_Newsletters_processors::send($newsletter, $trace);
	}
}