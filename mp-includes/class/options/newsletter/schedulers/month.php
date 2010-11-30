<?php
class MP_Newsletter_scheduler_month extends MP_Newsletters_scheduler_abstract
{
	public $id = 'month';

	function schedule($newsletter) 
	{
		$y = $this->year;
		$m = $this->month;

		$d = $this->get_day($newsletter, $y, $m);
		if ( $this->day > $d )
		{
			$m++;
			if ($m > 12) { $y++; $m = 1; }
			$d = $this->get_day($newsletter, $y, $m);
		}

		$h = $this->get_hour($newsletter);
		$i = $this->get_minute($newsletter);

		return $this->schedule_single_event( $newsletter, $this->mktime( $h, $i, 1, $m, $d, $y ) );
	}
}
new MP_Newsletter_scheduler_month(__('Every month', MP_TXTDOM));
