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

		if (isset($data->thumbnail_width, $data->thumbnail_height, $data->thumbnail_url, $data->title))
		{
			$url = (isset($data->url)) ? $data->url : $url;

			$html  = "<a target='_blank' href=\"" . esc_url($url) . "\"";
			if (isset($data->title))			$html .= " title=\"" . esc_html($data->title) . "\"";
			$html .= ">";

			$html .= "<img";
			if (isset($data->thumbnail_width))	$html .= " width='{$data->thumbnail_width}px'";
			if (isset($data->thumbnail_height))	$html .= " height='{$data->thumbnail_height}px'";
			if (isset($data->thumbnail_url))	$html .= " src='{$data->thumbnail_url}'";
			if (isset($data->title))			$html .= " title=\"" . esc_html($data->title) . "\" alt=\"" . esc_html($data->title) . "\"";
			$html .= " />";

			$html .= "</a>";
		}
		if (!empty($html)) return $html;

		$html .= "<a target='_blank' href=\"" . esc_url($url) . "\"";
		if (isset($data->title))    	   $html .= " title=\"" . esc_html($data->title) . "\"";
		$html .= ">";
		$html .= (isset($data->title)) ? $data->title : $url;
		$html .= "</a>";
                                
		return $html;
	}
}