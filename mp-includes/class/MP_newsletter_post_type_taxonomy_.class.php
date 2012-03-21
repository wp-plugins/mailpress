<?php
abstract class MP_newsletter_post_type_taxonomy_ extends MP_newsletter_post_type_
{
	function register() 
	{
		MP_Newsletter::register_taxonomy($this->args);
	}

	function subscriptions_newsletter_th($th, $newsletter)
	{
		if (	isset($newsletter['params']['post_type']) 	&& $this->post_type == $newsletter['params']['post_type'] && 
			isset($newsletter['params']['taxonomy']) 	&& $this->taxonomy  == $newsletter['params']['taxonomy']) 
			return $newsletter['mail']['the_post_type'] . '/' .  $newsletter['mail']['the_taxonomy'] ;
		return $th;
	}
}