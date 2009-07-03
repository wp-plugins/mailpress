<?php
class MP_Ip
{
	const cache_days 	= 120;			// keep it 120 days
	const provider 	= 'mailpress';
	const no_state	= 'ZZ';

	public static function get_providers()
	{
		$providers[self::provider] = array('url' => '%1$s', 'type' => 'xml', 'md5' => false);
		return apply_filters('MailPress_ip_add_provider', $providers);
	}

	public static function get_all($ip)
	{
		return self::get_ip_info($ip);
	}

	public static function get_latlng($ip)
	{
		$content = self::get_ip_info($ip);
		if (!$content) return false;
		if (!isset($content['geo']['lat'])) return false;
		return $content['geo'];
	}

	public static function get_country($ip)
	{
		$content = self::get_ip_info($ip);
		if ( (!$content) || (!isset($content['country'])) ||(strlen($content['country']) > 2) ) return self::no_state;
		return $content['country'];
	}

	public static function get_subcountry($ip)
	{
		$content = self::get_ip_info($ip);
		if ( (!$content) || (!isset($content['subcountry'])) || empty($content['subcountry']) ) return self::no_state;
		return $content['subcountry'];
	}

	public static function get_USstate($ip)
	{
		$response = wp_remote_get("http://api.hostip.info/get_html.php?ip=$ip");
		$x = (is_wp_error($response)) ? false : $response['body'];
		if (!$x || empty($x) || (2 < strlen($x))) return self::no_state;

		$USstates = array('AL', 'AK', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DE', 'FL', 'GA', 'HI', 'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'ME', 'MD', 'MA', 'MI', 'MN', 'MS', 'MO', 'MT', 'NE', 'NV', 'NH', 'NJ', 'NM', 'NY', 'NC', 'ND', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC', 'SD', 'TN', 'TX', 'UT', 'VT', 'VA', 'WA', 'WV', 'WI', 'WY');
		$USstate = substr($x, strlen($x)-2, 2);
		return (in_array(substr($USstate, 0, 2), $USstates)) ? substr($USstate, 0, 2) : self::no_state;
	}

	public static function get_ip_info($ip)
	{
		$providers = self::get_providers();

		foreach($providers as $provider => $pdata)
		{
			list($file, $ip_url, $cache) = self::get_context($pdata, $ip);
			if ($file == $cache) break;
		}

		switch ($file)
		{
			case $cache :
				$content = @file_get_contents($file);
				$valid   = (!empty($content) && $content);
			break;
			default :
				unset($providers[self::provider]);
				do
				{
					$provider	= array_rand($providers);
					$pdata	= $providers[$provider];
					list($file, $ip_url, $cache) = self::get_context($pdata, $ip);

					$response = wp_remote_get($ip_url);
					$content = (is_wp_error($response)) ? false : $response['body'];
					$valid = (!empty($content) && $content);

					if ($valid)
					{
						switch($pdata['type'])
						{
							case 'xml' :
								if (!simplexml_load_string($content)) 	$valid = false;
							break;
							case 'array' :
								if (!is_serialized($content)) 		$valid = false;
							break;
						}
						if ($valid) $valid = apply_filters('MailPress_ip_content_' . $pdata['type'], $valid, $content, $provider);	
					}
					if (!$valid) unset($providers[$provider]);
					if (empty($providers)) break;
				} while (!$valid);

				if ($valid) file_put_contents($cache, $content);
			break;
		}

		if (!$valid) return false;

		switch($provider)
		{
			case self::provider :
				return self::custom($content);
			break;
			default :
				return apply_filters('MailPress_ip_data_' . $provider, false, $content, $ip, $ip_url, $cache, $file);
			break;
		}
	}

	public static function get_context($pdata, $ip)
	{
		$file  = $ip_url = sprintf( $pdata['url'], $ip );
		$cache = MP_TMP . 'tmp/' . ((!isset($pdata['md5'])) ?  md5($ip_url) : $ip_url) . '.spc';


		if (file_exists($cache))
		{
			$cache_days = ( (isset($pdata['cache_days'])) ? $pdata['cache_days'] : self::cache_days )*24*60*60;
			if (filemtime($cache) >= (time() - $cache_days))	$file = $cache;
			else									unlink($cache);
		}
		return array($file, $ip_url, $cache);
	}

	public static function custom($content)
	{
		return (is_serialized($content)) ? unserialize($content) : false;
	}

	public static function cache_custom($provider, $ip, $geo = false, $country = false, $subcountry = false, $html = false)
	{
		$stores = array('geo', 'country', 'subcountry', 'html');
		$providers  = self::get_providers();
		if (!isset($providers[$provider])) return false;

		$content['provider']['id'] 	 = $provider;
		$content['provider']['credit'] = $providers[$provider]['credit'];

		foreach ($stores as $store) 
		{
			if (empty($$store)) 	continue;
			if (!$$store)		continue;
			$content[$store] = $$store;
		}

		file_put_contents(MP_TMP . 'tmp/' . $ip . '.spc', serialize($content));

		return $content;
	}

	public static function xml2array($input, $recurse = false)
	{
    		$data = ((!$recurse) && is_string($input)) ? simplexml_load_string($input): $input;
    		if ($data instanceof SimpleXMLElement) $data = (array) $data;
    		if (is_array($data)) foreach ($data as &$item) $item = self::xml2array($item, true);
    		return $data;
	}
}

class MP_Ip_infosniper
{
	const provider 	= 'infosniper';
	const type 		= 'xml';

	function __construct()
	{
		add_filter('MailPress_ip_add_provider', 			array(&$this, 'add_provider'), 8, 1);
		add_filter('MailPress_ip_content_' . self::type, 	array(&$this, 'content'), 8, 3);
		add_filter('MailPress_ip_data_' . self::provider, 	array(&$this, 'data'), 8, 6);
	}

	function add_provider($providers)
	{
		$providers[self::provider] = array('url' => 'http://www.infosniper.net/xml.php?ip_address=%1$s', 'credit' => 'http://www.infosniper.net/', 'type' => self::type);
		return $providers;
	}

	function content($valid, $content, $provider)
	{
		if ($provider != self::provider) return $valid;
		if (strpos($content, 'Quota exceeded') === true) return false;
		return true;
	}

	function data($valid, $content, $ip, $ip_url, $cache, $file)
	{
		$skip = array('countryflag', 'areacode', 'dmacode', 'queries');
		$html = '';

		$xml = MP_Ip::xml2array( $content );
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
		return MP_Ip::cache_custom(self::provider, $ip, $geo, strtoupper(substr($country, 0, 2)), strtoupper($subcountry), $html);
	}
}

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

class MP_Ip_hostip
{
	const provider 	= 'hostip';
	const type 		= 'xml';

	function __construct()
	{
		add_filter('MailPress_ip_add_provider', 			array(&$this, 'add_provider'), 8, 1);
		add_filter('MailPress_ip_content_' . self::type, 	array(&$this, 'content'), 8, 3);
		add_filter('MailPress_ip_data_' . self::provider, 	array(&$this, 'data'), 8, 6);
	}

	function add_provider($providers)
	{
		$providers[self::provider] = array('url' => 'http://api.hostip.info/get_xml.php?ip=%1$s&position=true', 'credit' => 'http://www.hostip.info/', 'type' => self::type);
		return $providers;
	}

	function content($valid, $content, $provider)
	{
		if ($provider != self::provider) return $valid;
		if (strpos($content, '<countryAbbrev>XX</countryAbbrev>')) return false;
		if (strpos($content, '<!-- Co-ordinates are unavailable -->')) return false;
        return true;
	}

	function data($valid, $content, $ip, $ip_url, $cache, $file)
	{
		$html = '';
		try 
		{
			set_error_handler(array(&$this, 'HandleXmlError'));
			$dom = New DOMDocument();
			$dom->loadXML($content);
			restore_error_handler();
		}
		catch (DOMException $e) 
		{
			return false;
		}

		$x = self::parse_node($dom, 'Hostip');
		if ($x->nodeName == 'Hostip')
		{
			$h = self::parse_node($x, 'countryAbbrev');
			$country = $h->nodeValue;

			$h = self::parse_node($x, 'name');
			$html .= "<p style='margin:3px;'><b>City</b> : " . $h->nodeValue . "</p>";
			if ('US' == strtoupper($country)) $subcountry = substr($h->nodeValue, strlen($h->nodeValue)-2, 2);

			$h = self::parse_node($x, 'countryName');
			$html .= "<p style='margin:3px;'><b>Country</b> : " . $h->nodeValue . "</p>";

			$h = self::parse_node($x, 'coordinates');
			$lnglat = explode(',', $h->nodeValue);
			if (count($lnglat) < 2) return false;
			$geo['lat'] = $lnglat[1];
			$geo['lng'] = $lnglat[0];
		}
		return MP_Ip::cache_custom(self::provider, $ip, $geo, strtoupper(substr($country, 0, 2)), strtoupper($subcountry), $html);
	}

	function parse_node($node, $tagname) 
	{
		$xs = $node->getElementsByTagname($tagname); 
		foreach ($xs as $x) {};
		return $x;
	}

	function HandleXmlError($errno, $errstr, $errfile, $errline)
	{
		if ($errno==E_WARNING && (substr_count($errstr,"DOMDocument::loadXML()")>0))
		{
			throw new DOMException($errstr);
		}
		else
			return false;
	}
}

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

$MP_Ip_infosniper = new MP_Ip_infosniper();
$MP_Ip_ipinfodb 	= new MP_Ip_ipinfodb();
$MP_Ip_hostip 	= new MP_Ip_hostip();
$MP_Ip_geoplugin 	= new MP_Ip_geoplugin();
?>