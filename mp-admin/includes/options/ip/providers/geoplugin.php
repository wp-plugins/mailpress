<?php
MailPress::require_class('Ip_provider_abstract');

class MP_Ip_geoplugin extends MP_Ip_provider_abstract
{
	var $provider 	= 'geoplugin';
	var $url		= 'http://www.geoplugin.net/php.gp?ip=%1$s';
	var $credit		= 'http://www.geoplugin.net/';
	var $type 		= 'array';

	function content($valid, $content, $provider)
	{
		if ($provider != $this->provider) return $valid;
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
		return $this->cache_custom($ip, $geo, strtoupper(substr($country, 0, 2)), strtoupper($subcountry), $html);
	}
}
$MP_Ip_geoplugin = new MP_Ip_geoplugin();
?>