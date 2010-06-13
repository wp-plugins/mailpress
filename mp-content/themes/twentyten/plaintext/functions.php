<?php
if (!class_exists('MP_theme_2010'))
{
	class MP_theme_2010
	{
		const HEADER_IMAGE_WIDTH = 700;
		const HEADER_IMAGE_HEIGHT = 147;

		public static function posts_orderby($orderby = '')
		{
			global $MP_post_ids;
			$orderby = ' FIELD(ID, ' . implode(',', $MP_post_ids) . ')';
			return $orderby;
		}

		public static function comments_popup_link_attributes($attrs = '')
		{
			return $attrs . ' style="color: rgb(136, 136, 136); background: none repeat scroll 0pt 0pt transparent; border: 0pt none; margin: 0pt; padding: 0pt; vertical-align: baseline;" ';
		}

		public static function the_category($thelist, $separator, $parents)
		{
			return str_replace('a href=', 'a style="color: rgb(136, 136, 136); background: none repeat scroll 0pt 0pt transparent; border: 0pt none; margin: 0pt; padding: 0pt; vertical-align: baseline;" href=', $thelist );
		}

		public static function term_links_post_tag($term_links)
		{
			foreach($term_links as $k => $v)
				$term_links[$k] = str_replace('a href=', 'a style="color: rgb(136, 136, 136); background: none repeat scroll 0pt 0pt transparent; border: 0pt none; margin: 0pt; padding: 0pt; vertical-align: baseline;" href=', $v );
			return $term_links;
		}
	}
}