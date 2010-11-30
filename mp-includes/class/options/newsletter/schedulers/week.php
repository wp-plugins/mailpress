<?php
class MP_Newsletter_scheduler_week extends MP_Newsletters_scheduler_abstract
{
	public $id = 'week';

	function schedule($newsletter) 
	{
		$y = $this->year;
		$m = $this->month;

		$wdiff  = $this->get_wday($newsletter) - $this->wday;
		if ( $wdiff < 0 ) $wdiff += 7;
		$d = $this->day + $wdiff; 

		$h = $this->get_hour($newsletter);
		$i = $this->get_minute($newsletter);

		return $this->schedule_single_event( $newsletter, $this->mktime( $h, $i, 1, $m, $d, $y ) );
	}
}
new MP_Newsletter_scheduler_week(__('Every week', MP_TXTDOM));