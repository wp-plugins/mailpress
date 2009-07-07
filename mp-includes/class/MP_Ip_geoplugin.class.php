<?php
class MP_Ip_geoplugin
{
	const provider 	= 'geoplugin';
	const type 		= 'array';

	function __construct()
	{
		add_filter('MailPress_ip_add_provider', 			array(&$this, 'add_provider'), 8, 1);
		add_filter('MailPress_ip_content_' . self::type, 	array(&$this, 'content'), 8, 3);
		add_filter('MailPress_ip_data_' . self::provider, 	array(&$this, 'data'), 8, 6);
	}

	function add_provider($providers)
	{
		$providers[self::provider] = array('url' => 'http://www.geoplugin.net/php.gp?ip=%1$s', 'credit' => 'http://www.geoplugin.net/', 'type' => self::type);
		return $providers;
	}

	function content($valid, $content, $provider)
	{
		if ($provider != self::provider) return $valid;
		if (strpos($content, 'IP Address not found') === true) return false;
		return true;
	}

	function data($valid, $content, $ip, $ip_url, $cache, $file)
	{
		$skip = array('geoplugin_areaCode', 'geoplugin_dmaCode', 'geoplugin_continentCode', 'geoplugin_currencyCode', 'geoplugin_currencySymbol', 'geoplugin_currencyConverter');
		$html = '';
		$content = unserialize($content);
		foreach ($content as $k => $v)
		{
			if ($v == 'n/a') continue;

			if (in_array($k, $skip)) continue;

			if (in_array($k, array('geoplugin_region', 'geoplugin_countryCode', 'geoplugin_latitude', 'geoplugin_longitude'))) {$$k = $v; continue;}

			$html .= "<p style='margin:3px;'><b>" . str_replace('geoplugin_', '', $k) . "</b> : $v</p>";
		}
		$geo = (isset($geoplugin_latitude) && isset($geoplugin_longitude)) ? 	array('lat' => $geoplugin_latitude, 'lng' => $geoplugin_longitude) : array();
		$country = (isset($geoplugin_countryCode)) ? $geoplugin_countryCode : '';
		$subcountry = (isset($geoplugin_region))   ? $geoplugin_region : '';
		return MP_Ip::cache_custom(self::provider, $ip, $geo, strtoupper(substr($country, 0, 2)), $subcountry, $html);
	}
}
?>