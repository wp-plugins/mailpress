<?php
/*
class MP_Ip_infosniper extends MP_Ip_provider_abstract
{
	var $provider 	= 'infosniper';
	var $url		= 'http://www.infosniper.net/xml.php?ip_address=%1$s';
	var $credit		= 'http://www.infosniper.net/';
	var $type 		= 'xml';

	function content($valid, $content)
	{
		if (strpos($content, 'Quota exceeded') === true) return false;
		return $valid;
	}

	function data($content, $ip)
	{
		$skip = array('countryflag', 'areacode', 'dmacode', 'queries');
		$html = '';

		$xml = $this->xml2array( $content );
		foreach ($xml['result'] as $k => $v)
		{
			if ($v == 'n/a') continue;

			if (in_array($k, $skip)) continue;

			if (in_array($k, array('countrycode', 'latitude', 'longitude'))) {$$k = $v; continue;}

			$html .= "<p style='margin:3px;'><b>$k</b> : $v</p>";
		}
		$geo = ( isset($latitude) && isset($longitude) ) ? array('lat' => $latitude, 'lng' => $longitude) : array();
		$country = (isset($countrycode)) ? $countrycode : '';
		$subcountry = ('US' == strtoupper($country)) ? MP_Ip::get_USstate($ip) : MP_Ip::no_state;
		return $this->cache_custom($ip, $geo, strtoupper(substr($country, 0, 2)), strtoupper($subcountry), $html);
	}
}
new MP_Ip_infosniper();
//*/