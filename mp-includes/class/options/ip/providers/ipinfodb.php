<?php
if ( defined('MP_Ip_ipinfodb_ApiKey') )
{
class MP_Ip_ipinfodb extends MP_ip_provider_
{
	var $id 	= 'ipinfodb';
	var $url	= 'http://api.ipinfodb.com/v3/ip-city/?ip=%1$s&key=%2$s&format=xml';
	var $credit	= 'http://ipinfodb.com/';
	var $type 	= 'xml';

	function content($valid, $content)
	{
		if (!strpos($content, '<statusCode>OK</statusCode>')) return false;
		if (strpos($content, '<latitude>0</latitude>') && strpos($content, '<longitude>0</longitude>')) return false;
		return $valid;
	}

	function url($arg)
	{
		$arg[] = MP_Ip_ipinfodb_ApiKey;
		return $arg;
	}

	function data($content, $ip)
	{
		$skip = array('statusCode', 'statusMessage', 'ipAddress', 'zipCode');
		$html = '';

		$xml = $this->xml2array( $content );
		foreach ($xml as $k => $v)
		{
			if ($v == 'n/a') continue;
			if (empty($v))   continue;

			if (in_array($k, $skip)) continue;

			if (in_array($k, array('countryCode', 'latitude', 'longitude'))) {$$k = $v; continue;}

			$html .= "<p style='margin:3px;'><b>$k</b> : $v</p>";
		}
		$geo = (isset($latitude) && isset($longitude)) ? array('lat' => $latitude, 'lng' => $longitude) : array();
		$country = (isset($countryCode)) ? $countryCode : '';
		$subcountry = ('US' == strtoupper($country)) ? MP_Ip::get_USstate($ip) : MP_Ip::no_state;
		return $this->cache_custom($ip, $geo, strtoupper(substr($country, 0, 2)), strtoupper($subcountry), $html);
	}
}
new MP_Ip_ipinfodb();
}