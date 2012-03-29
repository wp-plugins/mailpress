<?php
class MP_oEmbed extends WP_oEmbed
{
	function data2html( $data, $url )
	{
		$html = '';

		if (!class_exists('MP_oEmbed_providers', false)) new MP_oEmbed_providers();

		$filter = 'MailPress_oembed_providers_data2html_' . str_replace(' ', '_', $data->provider_name);
		if (has_filter($filter)) $html = apply_filters($filter, $html, $data, $url);
		if (!empty($html)) return $html;

		foreach(array('thumbnail_width', 'thumbnail_height', 'thumbnail_url', 'title') as $var)
		{
			if (isset($data->{$var}) && !empty($data->{$var})) continue;

			$html .= "<a target='_blank' href=\"" . esc_url($url) . "\"";
			if (isset($data->title)) 		$html .= " title=\"" . esc_html($data->title) . "\"";
			$html .= ">";
			$html .= (isset($data->title)) ? $data->title : $url;
			$html .= "</a>";
		}
		if (!empty($html)) return $html;

		if (!isset($data->url)) $data->url = $url;

		$html  = "<a target='_blank' href=\"" . esc_url($data->url) . "\"";
		$html .= " title=\"" . esc_html($data->title) . "\"";
		$html .= ">";

		$html .= "<img";
		$html .= " width='{$data->thumbnail_width}px'";
		$html .= " height='{$data->thumbnail_height}px'";
		$html .= " src='{$data->thumbnail_url}'";
		$html .= " title=\"" . esc_html($data->title) . "\" alt=\"" . esc_html($data->title) . "\"";
		$html .= " />";

		$html .= "</a>";

		return $html;
	}
}