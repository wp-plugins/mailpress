<?php
class MP_Ip_ipinfoio extends MP_ip_provider_
{
	var $id 	= 'ipinfoio';
	var $url	= 'http://ipinfo.io/%1$s/geo';
	var $credit	= 'http://ipinfo.io/';
	var $type 	= 'json';

	function content($valid, $content)
	{
		if (!strpos($content, '"loc":')) return false;
		return $valid;
	}

	function data($content, $ip)
	{
		$skip = array('ip', 'hostname', 'org', 'postal');
		$html = '';

		$json =  json_decode( $content, true );
		foreach ($json as $k => $v)
		{
			if ($v == 'n/a') continue;
			if (empty($v))   continue;

			if (in_array($k, $skip)) continue;

			if (in_array($k, array('country', 'loc'))) {$$k = $v; continue;}

			$html .= "<p style='margin:3px;'><b>$k</b> : $v</p>";
		}

		$geo = array();
		if (isset($loc)) $ll = explode(',',$loc);
		if (count($ll) == 2) $geo = array('lat' => $ll[0], 'lng' => $ll[1]);

		$subcountry = ('US' == strtoupper($country)) ? MP_Ip::get_USstate($ip) : MP_Ip::no_state;
		return $this->cache_custom($ip, $geo, strtoupper(substr($country, 0, 2)), strtoupper($subcountry), $html);
	}
}
new MP_Ip_ipinfoio();