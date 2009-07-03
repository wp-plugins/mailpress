<?php
if (class_exists('MailPress'))
{
/*
Plugin Name: MailPress_newsletter_categories
Plugin URI: http://www.mailpress.org
Description: This is just an addon for MailPress to manage newsletters per categories
Author: Andre Renaut
Version: 4.0
Author URI: http://www.mailpress.org
*/

class MailPress_newsletter_categories
{
	function __construct() 
	{
// for plugin
		add_action('MailPress_register_newsletter',	array('MailPress_newsletter_categories', 'register'));
// for wp admin
		if (is_admin())
		{
		// for link on plugin page
			add_filter('plugin_action_links', 		array('MailPress_newsletter_categories', 'plugin_action_links'), 10, 2 );
		}
	}

// for plugin
	public static function register() 
	{
		if ( function_exists( 'create_initial_taxonomies' ) ) create_initial_taxonomies();

		add_action('publish_post', 	array(&$this, 'have_post'), 8, 1);

		$daily_value 	 	= date('Ymd');
		$d  				= date('Ymd', mktime(0, 0, 0, date('m'), date('d') - 1, date('Y')));
		$daily_query_posts 	= $d;

		$weekly_value 	 	= MP_Newsletter::get_yearweekofday(date('Y-m-d'));
		$w  				= MP_Newsletter::get_yearweekofday(date('Y-m-d', mktime(10, 0, 0, date('m'), date('d') - 7)));
		$weekly_query_posts_w 	= substr($w, 4, 2);
		$weekly_query_posts_y 	= substr($w, 0, 4);

		$monthly_value 		= date('Ym');
		$y  				= date('Y'); $m = date('m') - 1; if (0 == $m) { $m = 12; $y--;} if (10 > $m) $m = '0' . $m;
		$monthly_query_posts	= $y . $m ;

		$args = array('hierarchical' => false, 'depth'=>false, 'echo'=>false, 'get'=>'all');
		$categories = get_categories($args);
		foreach ($categories as $category)
		{
			if ($category->category_parent) continue;

			$id   = $category->cat_ID;
			$name = $category->cat_name;

			mp_register_newsletter (	"post_category_$id", 
								sprintf( __('[%1$s] New post in %2$s', 'MailPress'), get_bloginfo('name'), $name), 
								false, 
								'singlecat', 
								sprintf(__('Per post "%1$s"', 'MailPress'), $name), 
								sprintf(__('For each new post in %1$s', 'MailPress'), $name), 
								false, 
								true, 
								array('category' => $id, 'catname'=>$name)
						     );

			mp_register_newsletter (	"daily_category_$id", 
								sprintf( __('[%1$s] Daily newsletter in %2$s', 'MailPress'), get_bloginfo('name'), $name), 
								false, 
								'dailycat', 
								sprintf(__('Daily "%1$s"', 'MailPress'), $name), 
								sprintf(__('Daily newsletter for %1$s', 'MailPress'), $name), 
								array ( 	'callback'	 => array('MP_Newsletter', 'have')		, 
										'name'	 => 'MailPress_daily_category_' . $id	, 
										'value'	 => $daily_value 					, 
										'query_posts'=> array(
														'm'	=> $daily_query_posts , 
														'cat'	=> $id
													)
									), 
								true, 
								array('category' => $id, 'catname'=>$name)
						     );

			mp_register_newsletter (	"weekly_category_$id", 
								sprintf( __('[%1$s] Weekly newsletter for %2$s', 'MailPress'), get_bloginfo('name'), $name), 
								false, 
								'weeklycat', 
								sprintf(__('Weekly "%1$s"', 'MailPress'), $name), 
								sprintf(__('Weekly newsletter for %1$s', 'MailPress'), $name), 
								array ( 	'callback'	 => array('MP_Newsletter', 'have') 		, 
										'name'	 => 'MailPress_weekly_category_' . $id	, 
										'value'	 => $weekly_value 				, 
										'query_posts'=> array(
														'w'	=> $weekly_query_posts_w , 
														'year'=> $weekly_query_posts_y , 
														'cat'	=> $id
													)
									), 
								true, 
								array('category' => $id, 'catname'=>$name)
						     );

			mp_register_newsletter (	"monthly_category_$id", 
								sprintf( __('[%1$s] Monthly newsletter for %2$s', 'MailPress'), get_bloginfo('name'), $name), 
								false, 
								'monthlycat', 
								sprintf(__('Monthly "%1$s"', 'MailPress'), $name), 
								sprintf(__('Monthly newsletter for %1$s', 'MailPress'), $name), 
								array ( 	'callback'	 => array('MP_Newsletter', 'have')		, 
										'name'	 => 'MailPress_monthly_category_' . $id	, 
										'value'	 => $monthly_value				, 
										'query_posts'=> array(
														'm'	=> $monthly_query_posts , 
														'cat'	=> $id
													)
									), 
								true, 
								array('category' => $id, 'catname'=>$name)
						     );
		}
	}

// for newsletters
	public static function have_post($post_id) 
	{
		if (get_post_meta($post_id, '_MailPress_prior_to_install')) return true;

		global $mp_registered_newsletters, $mp_general;

		$args = array('hierarchical' => false, 'depth'=>false, 'echo'=>false, 'get'=>'all');
		$categories = get_categories($args);
		$cat_to_parent = self::cat_to_parent($categories);
		$post_to_cat   = wp_get_post_categories($post_id);
		$post_to_cat = (!empty($post_to_cat)) ? array_flip($post_to_cat) : array();

		foreach ($categories as $category)
		{
			if ($category->category_parent) continue;
			if (!self::post_in_category($cat_to_parent, $post_to_cat, $category->cat_ID)) continue;

			$id 			= $category->cat_ID;
			$newsletter_id 	= 'post_category_' . $id;
			$post_meta 		= '_MailPress_published_category_' . $id;

			if (isset($mp_general['newsletters'][$newsletter_id]))
			{
				if (!get_post_meta($post_id, $post_meta, true))	
				{
					add_post_meta($post_id, $post_meta, 'yes', true);
					$newsletter 			= $mp_registered_newsletters[$newsletter_id];
					$newsletter['query_posts'] 	= array(	'p'	=> $post_id , 
												'cat'	=> $id	);

					$post = &get_post($post_id);
					$newsletter['the_title'] =  apply_filters('the_title', $post->post_title );

					MP_Newsletter::prepare_to_send($newsletter, false);
				}
			}
		}
	}

	public static function cat_to_parent($categories) 
	{
		$cat_to_parent = array();
		foreach ($categories as $category)
		{
			if (!$category->category_parent) 	continue;
			$cat_to_parent[$category->cat_ID] = $category->category_parent;
		}
		
		do
		{
			$herit = false;
			foreach ($cat_to_parent as $k => $v)
			{
				if (isset($cat_to_parent[$v]))
				{
					$cat_to_parent[$k] = $cat_to_parent[$v];
					$herit = true;
				}
			}
		} while ($herit);
		return $cat_to_parent;
	}

	public static function post_in_category($cat_to_parent, $post_to_cat, $cat_ID) {
		if (isset($post_to_cat[$cat_ID])) return true;
		foreach ($cat_to_parent as $k => $v)
			if ($v == $cat_ID) if (isset($post_to_cat[$k])) return true;
		return false;
	}

////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////

// for link on plugin page
	public static function plugin_action_links($links, $file)
	{
		return MailPress::plugin_links($links, $file, plugin_basename(__FILE__), '3');
	}
}

$MailPress_newsletter_categories = new MailPress_newsletter_categories();
}
?>