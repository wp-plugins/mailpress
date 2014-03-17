<?php
class MP_Ip_ip_json extends MP_ip_provider_
{
	var $id 	= 'ip-json';
	var $url	= 'http://ip-json.rhcloud.com/xml/%1$s';
	var $credit= 'http://ip-json.rhcloud.com/';
	var $type 	= 'xml';

	function content($valid, $content)
	{
		return $valid;
	}

	function data($content, $ip)
	{
		$html = '';

		$xml = $this->xml2array( $content );
		if (!isset($xml['Response'])) return false;
		$xml = $xml['Response'];

		$latitude 		= $xml['latitude'];
		$html .= "<p style='margin:3px;'><b>latitude</b> : $latitude</p>";

		$longitude 	= $xml['longitude'];
		$html .= "<p style='margin:3px;'><b>longitude</b> : $longitude</p>";

		$country 		= ucwords($xml['country_name']);
		$html .= "<p style='margin:3px;'><b>country</b> : $country</p>";

		$country_code 	= strtoupper($xml['country_code']);
		if ('US' == $country_code) 
		{
			$state_code 	= (!empty($xml['region'])) ? strtoupper($xml['region']) : '';
			$html .= "<p style='margin:3px;'><b>state_code</b> : $state_code</p>";
		}

		$city 		= ucwords($xml['city']);
		$html .= "<p style='margin:3px;'><b>city</b> : $city</p>";

		$postal_code 	= $xml['postal_code'];
		$html .= "<p style='margin:3px;'><b>postal_code</b> : $postal_code</p>";

		$geo = (isset($latitude) && isset($longitude)) ? array('lat' => $latitude, 'lng' => $longitude) : array();
		return $this->cache_custom($ip, $geo, substr($country_code, 0, 2), $state_code, $html);
	}
}
new MP_Ip_ip_json();