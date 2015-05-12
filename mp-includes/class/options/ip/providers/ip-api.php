<?php
class MP_Ip_ip_api extends MP_ip_provider_
{
	var $id 	= 'ip_api';
	var $url	= 'http://ip-api.com/xml/%1$s';
	var $credit	= 'http://ip-api.com/';
	var $type 	= 'xml';

	function content($valid, $content)
	{
		if (strpos($content, '<status>success</status>') !== false) return false;
		return $valid;
	}

	function data($content, $ip)
	{
		$skip = array('status', 'timezone', 'zip', 'isp', 'org', 'as', 'query');
		$html = '';

		$xml = $this->xml2array( $content );
		foreach ($xml as $k => $v)
		{
			if (empty($v))   continue;
			if ($v == 'n/a') continue;

			if (in_array($k, $skip)) continue;

			if (in_array($k, array('countryCode', 'region', 'regionName', 'lat', 'lon'))) {$$k = $v; continue;}

			$html .= "<p style='margin:3px;'><b>" . $k . "</b> : $v</p>";
		}
		$geo = (isset($lat) && isset($lon)) ? 	array('lat' => $lat, 'lng' => $lon) : array();
		$country = (isset($country)) ? $countryCode : '';
		$subcountry =  ('US' == strtoupper($country)) ? $region : '';
		return $this->cache_custom($ip, $geo, strtoupper(substr($countryCode, 0, 2)), strtoupper($subcountry), $html);
	}
}
new MP_Ip_ip_api();