<?php
class MP_Newsletter_scheduler_day extends MP_Newsletters_scheduler_abstract
{
	public $id = 'day';

	function schedule($newsletter) 
	{ 
		$y = $this->year;
		$m = $this->month;

		$d = $this->day;

		$h = $this->get_hour($newsletter);
		$i = $this->get_minute($newsletter);

		return $this->schedule_single_event( $newsletter, $this->mktime( $h, $i, 1, $m, $d, $y ) );
	}
}
new MP_Newsletter_scheduler_day(__('Every day', MP_TXTDOM));