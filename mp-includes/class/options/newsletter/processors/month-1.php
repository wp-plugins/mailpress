<?php
class MP_Newsletter_processor_previous_month extends MP_Newsletters_processor_abstract
{
	public $id = 'month-1';

	function __construct($description)
	{
		parent::__construct($description);
		$this->threshold = date('Ym', $this->time);
	}

	function query_posts($query_posts = array()) 
	{ 
		$query_posts['m'] = date('Ym', mktime(0, 0, 0, date('m', $this->time), 0, date('Y', $this->time)) );
		return $query_posts; 
	}
}
new MP_Newsletter_processor_previous_month(__('Previous month', MP_TXTDOM));