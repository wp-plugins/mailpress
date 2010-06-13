<?php
abstract class MP_Newsletters_scheduler_abstract
{
	function __construct($description)
	{
		$this->description = $description;

		$this->time = current_time('timestamp');

		$this->year  = (int) gmdate('Y', $this->time);
		$this->month = (int) gmdate('m', $this->time);
		$this->day   = (int) gmdate('j', $this->time);
		$this->hour  = (int) gmdate('H', $this->time);
		$this->minute= (int) gmdate('i', $this->time);

		$this->wday  = (int) gmdate('w', $this->time);

		add_filter('MailPress_newsletter_schedulers_register', 			array(&$this, 'register'), 8, 1);
		add_filter('MailPress_newsletter_scheduler_' . $this->id . '_schedule',	array(&$this, 'schedule'), 8, 1);
	}

	function register($schedulers) { $schedulers[$this->id] = $this->description; return $schedulers; }

	function schedule($newsletter) { return false; }

	function get_day($newsletter, $y, $m) 
	{
		$d = (isset($newsletter['scheduler']['args']['day'])) ? (int) $newsletter['scheduler']['args']['day'] : 1;

		$max_days = array(31,((($y%4==0)&&((!($y%100==0))||($y%400==0)))?29:28),31,30,31,30,31,31,30,31,30,31);
		$max_day  = $max_days[$m - 1];

		return (!is_numeric($d)) ? 1 : (($d <= 0 || $d > $max_day) ? $max_day : $d);
	}

	function get_wday($newsletter) 
	{
		$w = (isset($newsletter['scheduler']['args']['wday'])) ? (int) $newsletter['scheduler']['args']['wday'] : 1;
		if ( $w == 7 ) $w = 0;
		return (!is_numeric($w) || $w < 0 || $w > 6) ? 1 : $w;
	}

	function get_hour($newsletter) 
	{
		$h = (isset($newsletter['scheduler']['args']['hour'])) ? (int) $newsletter['scheduler']['args']['hour'] : 0;
		return (!is_numeric($h) || $h < 0 || $h > 23) ? 0 : $h;
	}

	function get_minute($newsletter) 
	{
		$i = (isset($newsletter['scheduler']['args']['minute'])) ? (int) $newsletter['scheduler']['args']['minute'] : 0;
		return (!is_numeric($i) || $i < 0 || $i > 59) ? 0 : $i;
	}

	function mktime( $h, $i, $s, $m, $d, $y )
	{
		return gmmktime( $h, $i, $s, $m, $d, $y ) - get_option('gmt_offset') * 3600;
	}

	function schedule_single_event($newsletter, $timestamp, $event = 'mp_process_newsletter')
	{
		wp_schedule_single_event( $timestamp, $event, array('args' => array('newsletter' => $newsletter )) );

		return MP_Newsletters_schedulers::schedule_report($newsletter, $timestamp, $this->id );
	}
}

require_once('MP_Newsletters_scheduler_post_abstract.class.php');