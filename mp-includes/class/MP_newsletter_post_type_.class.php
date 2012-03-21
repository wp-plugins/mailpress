<?php
abstract class MP_newsletter_post_type_
{
	function __construct() 
	{
		add_action('MailPress_register_newsletter',	array(&$this, 'register'));

		if (is_admin())
		{
		// settings
			add_filter('MailPress_subscriptions_newsletter_th',		array(&$this, 'subscriptions_newsletter_th'), 10, 2 );
		// install
			register_activation_hook(  plugin_basename($this->file),	array(&$this, 'install'));
			register_deactivation_hook(plugin_basename($this->file),	array(&$this, 'uninstall'));
		// for link on plugin page
			add_filter('plugin_action_links',					array(&$this, 'plugin_action_links'), 10, 2 );
		}
	}

	function register() 
	{
		MP_Newsletter::register_files($this->args);
	}

	function subscriptions_newsletter_th($th, $newsletter)
	{
		if (	isset($newsletter['params']['post_type']) 	&& $this->post_type == $newsletter['params']['post_type'])
			return '** ' . $newsletter['mail']['the_post_type'] . ' **';
		return $th;
	}

	function install() 
	{
		$event = "Install newsletter_{$this->post_type}";
		if (isset($this->taxonomy)) $event .=  "_{$this->taxonomy}";

		$now4cron = current_time('timestamp', 'gmt');
		wp_schedule_single_event($now4cron - 1, 'mp_schedule_newsletters', array('args' => array('event' => $event )));
	}

	function uninstall() 
	{
		MailPress_newsletter::unschedule_hook('mp_process_newsletter');

		$event = "Uninstall newsletter_{$this->post_type}";
		if (isset($this->taxonomy)) $event .=  "_{$this->taxonomy}";

		$now4cron = current_time('timestamp', 'gmt');
		wp_schedule_single_event($now4cron + 1, 'mp_schedule_newsletters', array('args' => array('event' => $event )));
	}

	function plugin_action_links($links, $file)
	{
		return MailPress::plugin_links($links, $file, plugin_basename($this->file), 'subscriptions');
	}
}