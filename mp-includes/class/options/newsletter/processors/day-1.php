<?php
class MP_Newsletter_processor_previous_day extends MP_Newsletters_processor_abstract
{
	public $id = 'day-1';

	function __construct($description)
	{
		parent::__construct($description);
		$this->threshold = date('Ymd', $this->time);
	}

	function query_posts($query_posts = array()) 
	{ 
		$query_posts['m'] = date('Ymd', $this->time - 24*60*60);
		return $query_posts; 
	}
}
new MP_Newsletter_processor_previous_day(__('Previous day', MP_TXTDOM));