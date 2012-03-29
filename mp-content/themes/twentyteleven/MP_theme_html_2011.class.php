<?php
class MP_theme_html_2011
{
	const HEADER_IMAGE_WIDTH = 760;
	const HEADER_IMAGE_HEIGHT = 219;

	public static $_comments_1st = false;

	function __construct()
	{
		add_action('MailPress_build_mail_content_start',	array(__CLASS__, 'build_mail_content_start'));
		add_action('MailPress_build_mail_content_end',		array(__CLASS__, 'build_mail_content_end'));
	}

	public static function build_mail_content_start($type)
	{
		if ('html' != $type) return;

		add_filter( 'wp_nav_menu', 				array(__CLASS__, 'wp_nav_menu'), 8, 2 );
		add_filter( 'wp_page_menu', 				array(__CLASS__, 'wp_nav_menu'), 8, 2 );
		add_filter( 'comments_popup_link_attributes', 	array(__CLASS__, 'comments_popup_link_attributes'), 8, 1 );
		add_filter( 'the_category', 				array(__CLASS__, 'the_category'), 8, 3 );
		add_filter( 'term_links-post_tag', 			array(__CLASS__, 'term_links_post_tag'), 8, 1 );
	}

	public static function build_mail_content_end($type)
	{
		if ('html' != $type) return;

		remove_filter( 'wp_nav_menu', 				array(__CLASS__, 'wp_nav_menu'));
		remove_filter( 'wp_page_menu', 				array(__CLASS__, 'wp_nav_menu'));
		remove_filter( 'comments_popup_link_attributes',array(__CLASS__, 'comments_popup_link_attributes') );
		remove_filter( 'the_category', 				array(__CLASS__, 'the_category') );
		remove_filter( 'term_links-post_tag', 		array(__CLASS__, 'term_links_post_tag') );
	}



	public static function header_image($default, $post_id = false)
	{
		if (!is_numeric($post_id)) $post_id = false;
		switch (true)
		{
			case ( $post_id && function_exists('has_post_thumbnail') && function_exists('get_post_thumbnail_id') && function_exists('wp_get_attachment_image_src') && $post_id && has_post_thumbnail( $post_id ) && ($image = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'post-thumbnail')) && ($image[1] >= self::HEADER_IMAGE_WIDTH) ) :
				echo $image[0];
			break;
			case ( function_exists('get_header_image') && get_header_image() ) :
				echo get_header_image();
			break;
			default:
				echo $default;
			break;
		}
	}

	public static function wp_nav_menu($menu, $args)
	{
		$searched = array('<ul>',
					'<li class="current_page_item">',
					'<li class="page_item page-item-',
					'<a',
		);
		$replace  = array('<ul style="font-size:13px;list-style:none outside none;margin:0 7.6%;padding-left:0;">',
					'<li style="float:left;font-weight: bold;">',
					'<li style="float:left;" class="page_item page-item-',
					'<a style="color:#EEE;display:block;line-height:3.333em;padding:0 1.2125em;text-decoration:none;" ',
		);
		return str_ireplace($searched, $replace, $menu);
	}

	public static function comments_popup_link_attributes($attrs = '')
	{
		self::$_comments_1st = !self::$_comments_1st;
		if (self::$_comments_1st)
		{
			$url = site_url() . "/wp-content/themes/twentyeleven/images/comment-bubble.png";
			return $attrs . ' style="background: url(\'' . $url . '\') no-repeat scroll 0 0 #EEEEEE;color:#666666;font-size:13px;font-weight:normal;height:36px;line-height:35px;overflow:hidden;padding:0;position:absolute;right:0;text-align:center;text-decoration:none;top:1.5em;width:43px;" ';
		}
		return $attrs . ' style="border:0 none;font-family:inherit;font-size:100%;font-style:inherit;font-weight:inherit;margin:0;padding:0;vertical-align:baseline;color:#1982D1;text-decoration:none;font-weight:bold;" ';
	}

	public static function the_category($thelist, $separator, $parents)
	{
		return str_replace(array('a href=', 'rel="category"'), array('a class="hover_underline" style="border:0 none;font-family:inherit;font-size:100%;font-style:inherit;font-weight:inherit;margin:0;padding:0;vertical-align:baseline;color:#1982D1;text-decoration:none;font-weight:bold;" href=', ''), $thelist );
	}

	public static function term_links_post_tag($term_links)
	{
		foreach($term_links as $k => $v)
			$term_links[$k] = str_replace('a href=', 'a class="hover_underline" style="border:0 none;font-family:inherit;font-size:100%;font-style:inherit;font-weight:inherit;margin:0;padding:0;vertical-align:baseline;color:#1982D1;text-decoration:none;font-weight:bold;" href=', $v );
		return $term_links;
	}
}
new MP_theme_html_2011();