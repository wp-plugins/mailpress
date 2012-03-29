<?php
class MP_theme_html_2012
{
	const HEADER_IMAGE_WIDTH = 760;
	const HEADER_IMAGE_HEIGHT = 198;

	function __construct()
	{
		add_action('MailPress_build_mail_content_start',array(__CLASS__, 'build_mail_content_start'));
		add_action('MailPress_build_mail_content_end',	array(__CLASS__, 'build_mail_content_end'));
	}

	public static function build_mail_content_start($type)
	{
		if ('html' != $type) return;

		add_filter( 'comments_popup_link_attributes', 	array(__CLASS__, 'comments_popup_link_attributes'), 8, 1 );
		add_filter( 'the_category', 				array(__CLASS__, 'the_category'), 8, 3 );
		add_filter( 'term_links-post_tag', 			array(__CLASS__, 'term_links_post_tag'), 8, 1 );
	}

	public static function build_mail_content_end($type)
	{
		if ('html' != $type) return;

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

	public static function comments_popup_link_attributes($attrs = '')
	{
		return $attrs . ' style="font-size:12px;color:#777;font-style:italic;" ';
	}

	public static function the_category($thelist, $separator, $parents)
	{
		return str_replace(array('a href=', 'rel="category"'), array('a class="hover_underline" style="font-size:12px;color:#777;font-style:italic;" href=', ''), $thelist );
	}

	public static function term_links_post_tag($term_links)
	{
		foreach($term_links as $k => $v)
			$term_links[$k] = str_replace('a href=', 'a class="hover_underline" style="font-size:12px;color:#777;font-style:italic;" href=', $v );
		return $term_links;
	}
}
new MP_theme_html_2012();