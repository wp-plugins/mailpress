<?php
if (class_exists('MailPress_newsletter') && !class_exists('MailPress_newsletter_categories') )
{
/*
Plugin Name: MailPress_newsletter_categories
Plugin URI: http://www.mailpress.org/wiki/index.php?title=Add_ons:Newsletter_categories
Description: Newsletters : for posts per category  (<span style='color:red;'>required !</span> <span style='color:#D54E21;'>Newsletter</span> add-on)
Version: 5.3
*/

class MailPress_newsletter_categories
{
	function __construct() 
	{
// for plugin
		add_action('MailPress_register_newsletter',	array(__CLASS__, 'register'));
// for wp admin
		if (is_admin())
		{
		// install
			register_activation_hook(  plugin_basename(__FILE__), array(__CLASS__, 'install'));
			register_deactivation_hook(plugin_basename(__FILE__), array(__CLASS__, 'uninstall'));
		// for link on plugin page
			add_filter('plugin_action_links', 		array(__CLASS__, 'plugin_action_links'), 10, 2 );
		// settings
			add_filter('MailPress_subscriptions_newsletter_th',	 array(__CLASS__, 'subscriptions_newsletter_th'), 10, 2 );
		}
	}

// for plugin
	public static function register() 
	{
		$taxonomy_s = 'categories';
		$args = array(	'root' 		=> MP_CONTENT_DIR . "advanced/newsletters/post/{$taxonomy_s}",
					'root_filter' 	=> 'MailPress_advanced_newsletters_root',
					'folder'		=> $taxonomy_s,
					'files'		=> array($taxonomy_s),
					'taxonomy'		=> 'cat',
					'get_terms_args'=> array('hierarchical' => false, 'depth'=>false, 'echo'=>false, 'get'=>'all'),
		);

		extract( $args );

		$categories = get_categories($get_terms_args);

		if (isset($root_filter)) $root  = apply_filters($root_filter, $root);

   		$dir  = @opendir($root);
		if ($dir) while ( ($file = readdir($dir)) !== false ) if (preg_match("/category-[0-9]*\.xml/", $file)) $files[] = substr($file, 0, -4);
		if ($dir) @closedir($dir);
		if (empty($files)) return;

		$xml = '';
		foreach($files as $file)
		{
			$fullpath = "$root/$file.xml";
			if (!is_file($fullpath)) continue;

	            if ($folder == $file)
	            {
				foreach ($categories as $category)
				{
					if ($category->category_parent) continue;
					ob_start();
						include($fullpath);
						$xml .= trim(ob_get_contents());
					ob_end_clean();
				}
	            }
	            else
	            {
				ob_start();
					include($fullpath);
					$xml .= trim(ob_get_contents());
				ob_end_clean();
      	      }
		}
		if (empty($xml)) return;

		MP_Newsletter::xml_register($xml);
	}

////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////

// install
	public static function install() 
	{
		$now4cron = current_time('timestamp', 'gmt');
		wp_schedule_single_event($now4cron - 1, 'mp_schedule_newsletters', array('args' => array('event' => 'Install newsletter_categories' )));
	}

	public static function uninstall() 
	{
		MailPress_newsletter::unschedule_hook('mp_process_newsletter');

		$now4cron = current_time('timestamp', 'gmt');
		wp_schedule_single_event($now4cron + 1, 'mp_schedule_newsletters', array('args' => array('event' => 'Uninstall newsletter_categories' )));
	}

// for link on plugin page
	public static function plugin_action_links($links, $file)
	{
		return MailPress::plugin_links($links, $file, plugin_basename(__FILE__), 'subscriptions');
	}

// settings
	public static function subscriptions_newsletter_th($th, $newsletter)
	{
		if (isset($newsletter['mail']['the_category'])) return __('Post') . '/' . $newsletter['mail']['the_category'];
		return $th;
	}
}
new MailPress_newsletter_categories();
}