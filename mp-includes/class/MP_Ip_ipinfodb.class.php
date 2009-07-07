<?php
class MP_Ip_ipinfodb
{
	const provider 	= 'ipinfodb';
	const type 		= 'xml';

	function __construct()
	{
		add_filter('MailPress_ip_add_provider', 			array(&$this, 'add_provider'), 8, 1);
		add_filter('MailPress_ip_content_' . self::type, 	array(&$this, 'content'), 8, 3);
		add_filter('MailPress_ip_data_' . self::provider, 	array(&$this, 'data'), 8, 6);
	}

	function add_provider($providers)
	{
		$providers[self::provider] = array('url' => 'http://ipinfodb.com/ip_query.php?ip=%1$s', 'credit' => 'http://ipinfodb.com/', 'type' => self::type);
		return $providers;
	}

	function content($valid, $content, $provider)
	{
		if ($provider != self::provider) return $valid;
		if (!strpos($content, '<Status>OK</Status>')) return false;
		return true;
	}

	function data($valid, $content, $ip, $ip_url, $cache, $file)
	{
		$skip = array('Status', 'RegionCode', 'Gmtoffset', 'Dstoffset');
		$html = '';

		$xml = MP_Ip::xml2array( $content );
		foreach ($xml as $k => $v)
		{
			if ($v == 'n/a') continue;

			if (in_array($k, $skip)) continue;

			if (in_array($k, array('CountryCode', 'Latitude', 'Longitude'))) {$$k = $v; continue;}

			$html .= "<p style='margin:3px;'><b>$k</b> : $v</p>";
		}
		$geo = (isset($Latitude) && isset($Longitude)) ? 	array('lat' => $Latitude, 'lng' => $Longitude) : array();
		$country = (isset($CountryCode)) ? 				$CountryCode : '';
		$subcountry = ('US' == strtoupper($country)) ? MP_Ip::get_USstate($ip) : MP_Ip::no_state;
		return MP_Ip::cache_custom(self::provider, $ip, $geo, strtoupper(substr($country, 0, 2)), strtoupper($subcountry), $html);
	}
}
?>