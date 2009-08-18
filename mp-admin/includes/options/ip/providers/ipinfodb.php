<?php
MailPress::require_class('Ip_provider_abstract');

class MP_Ip_ipinfodb extends MP_Ip_provider_abstract
{
	var $provider 	= 'ipinfodb';
	var $url		= 'http://ipinfodb.com/ip_query.php?ip=%1$s';
	var $credit		= 'http://ipinfodb.com/';
	var $type 		= 'xml';

	function content($valid, $content, $provider)
	{
		if ($provider != $this->provider) return $valid;
		if (!strpos($content, '<Status>OK</Status>')) return false;
		return true;
	}

	function data($valid, $content, $ip, $ip_url, $cache, $file)
	{
		$skip = array('Status', 'RegionCode', 'Gmtoffset', 'Dstoffset');
		$html = '';

		$xml = $this->xml2array( $content );
		foreach ($xml as $k => $v)
		{
			if ($v == 'n/a') continue;

			if (in_array($k, $skip)) continue;

			if (in_array($k, array('CountryCode', 'Latitude', 'Longitude'))) {$$k = $v; continue;}

			$html .= "<p style='margin:3px;'><b>$k</b> : $v</p>";
		}
		$geo = (isset($Latitude) && isset($Longitude)) ? 	array('lat' => $Latitude, 'lng' => $Longitude) : array();
		$country = (isset($CountryCode)) ? $CountryCode : '';
		$subcountry = ('US' == strtoupper($country)) ? MP_Ip::get_USstate($ip) : MP_Ip::no_state;
		return $this->cache_custom($ip, $geo, strtoupper(substr($country, 0, 2)), strtoupper($subcountry), $html);
	}
}
$MP_Ip_ipinfodb = new MP_Ip_ipinfodb();
?>