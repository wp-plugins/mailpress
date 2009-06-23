<?php
class MP_Ip
{
	const ttl = 120;	// keep it 120 days

	public static function get_country($ip)
	{
		$content = self::get_ip_info($ip);
		if (!$content) return 'ZZ';
		if (!isset($content['country'])) return 'ZZ';
		if (strlen($content['country']) > 2) return 'ZZ';
		return $content['country'];
	}

	public static function get_subcountry($ip)
	{
		$content = self::get_ip_info($ip);
		if (!$content) return 'ZZ';
		if (!isset($content['subcountry'])) return 'ZZ';
		return $content['subcountry'];
	}

	public static function get_USstate($ip)
	{
		$USstate = 'ZZ';

		if ('127.0.0.1' == $ip) return $USstate;

		$x = self::get_all($ip);
		if ($x)
		{
			if (isset($x['subcountry']) && isset($x['country']) && ('US' == $x['country']))
			{
				$USstates = array('AL', 'AK', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DE', 'FL', 'GA', 'HI', 'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'ME', 'MD', 'MA', 'MI', 'MN', 'MS', 'MO', 'MT', 'NE', 'NV', 'NH', 'NJ', 'NM', 'NY', 'NC', 'ND', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC', 'SD', 'TN', 'TX', 'UT', 'VT', 'VA', 'WA', 'WV', 'WI', 'WY');
				return (in_array($x['subcountry'], $USstates)) ? $x['subcountry'] : $USstate;
			}
		}
		return $USstate;
	}

	public static function get_latlng($ip)
	{
		$content = self::get_ip_info($ip);
		if (!$content) return false;
		if (!isset($content['geo']['lat'])) return false;
		return $content['geo'];
	}

	public static function get_all($ip)
	{
		$content = self::get_ip_info($ip);
		if (!$content) return false;
		return $content;
	}

	public static function get_providers($custom = true)
	{
		$providers = 	array (
						'custom' 		=> 	array('url' => '%1$s', 
											'type' => 'xml', 
											'md5' => false
										), 
						'infosnipper' 	=> 	array('url' => 'http://www.infosniper.net/xml.php?ip_address=%1$s',
											'credit' => 'http://www.infosniper.net/', 
											'type' => 'xml'
										),
						'ipinfodb' 		=>	array('url' => 'http://ipinfodb.com/ip_query.php?ip=%1$s', 
											'credit' => 'http://ipinfodb.com/', 
											'type' => 'xml'
										),
						'geoplugin' 	=> 	array('url' => 'http://www.geoplugin.net/php.gp?ip=%1$s', 
											'credit' => 'http://www.geoplugin.net/', 
											'type' => 'array'
										)
						);

		$providers = apply_filters('MailPress_ip_providers', $providers);

		if (!$custom) unset( $providers['custom']);
		return $providers;
	}

	public static function get_ip_info($ip)
	{
		$providers = self::get_providers();

		foreach($providers as $provider => $pdata)
		{
			list($ip_url, $cache, $file) = self::get_context($pdata, $ip);
			if ($file == $cache) break;
		}

		if ($file == $ip_url)
		{
			unlink($cache);
			$providers  = self::get_providers(false);
			
			do
			{
				$provider	= array_rand($providers);

				$pdata	= $providers[$provider];
				list($ip_url, $cache, $file) = self::get_context($pdata, $ip);

				$content = apply_filters('MailPress_providers_content', ($file != '*') ? @file_get_contents($file) : '', $provider, $ip, $cache, $file);

				$data_ok = true;
				if (!empty($content) && $content)
				{
					switch($pdata['type'])
					{
						case 'xml' :
							if (!simplexml_load_string($content)) $data_ok = false;
							// infosnipper
							if (strpos($content, 'Quota exceeded') === true) $data_ok = false;
							// ipinfodb
							if (!strpos($content, '<Status>OK</Status>')) $data_ok = false;
							$data_ok = apply_filters('MailPress_providers_content_xml', $data_ok, $provider, $content);
						break;
						case 'array' :
							if (!is_serialized($content)) $data_ok = false;
							$data_ok = apply_filters('MailPress_providers_content_array', true, $provider, $content);	
						break;
						default :
							$data_ok = apply_filters('MailPress_providers_content_default', false, $provider, $content);	
						break;
					}
				}
				else
					$data_ok = false;

				if (!$data_ok) unset($providers[$provider]);
				if (empty($providers)) return false;
			} while (!$data_ok);

			if (!$data_ok) return false;

			file_put_contents($cache, $content);	
		}
		else
			$content = @file_get_contents($file);

		switch($provider)
		{
			case 'custom' :
				return self::custom($content);
			break;
			case 'infosnipper' :
				return self::infosnipper($content, $ip);
			break;
			case 'ipinfodb' :
				return self::ipinfodb($content, $ip);
			break;
			case 'geoplugin' :
				return self::geoplugin($content, $ip);
			break;
			default :
				return apply_filters('MailPress_providers_data', false, $provider, $content, $ip, $ip_url, $cache, $file);
			break;
		}
	}

	public static function get_context($pdata, $ip)
	{
		$ip_url = sprintf( $pdata['url'], $ip );
		$cache  = (!isset($pdata['md5'])) ? MP_TMP . 'tmp/' . md5($ip_url) . '.spc' : MP_TMP . 'tmp/' . $ip_url . '.spc';
		$file   = (file_exists($cache) && (time() - (self::ttl*24*60*60) < filemtime($cache))) ? $cache : $ip_url;

		return array($ip_url, $cache, $file);
	}

	public static function custom($content)
	{
		return (is_serialized($content)) ? unserialize($content) : false;
	}

	public static function infosnipper($content, $ip)
	{
		$skip = array('countryflag', 'areacode', 'dmacode', 'queries');
		$html = '';

		$xml = self::XML2Array( $content );
		foreach ($xml['result'] as $k => $v)
		{
			if ($v == 'n/a') continue;

			if (in_array($k, $skip)) continue;

			if (in_array($k, array('countrycode', 'latitude', 'longitude'))) {$$k = $v; continue;}

			$html .= "<p style='margin:3px;'><b>$k</b> : $v</p>";
		}
		$geo = ( isset($latitude) && isset($longitude) ) ? array('lat' => $latitude, 'lng' => $longitude) : array();
		$country = (isset($countrycode)) ? 				$countrycode : '';
		$subcountry = '';
		return self::cache_custom('infosnipper', $ip, $geo, strtoupper($country), $subcountry, $html);
	}

	public static function ipinfodb($content, $ip)
	{
		$skip = array('Status', 'RegionCode', 'Gmtoffset', 'Dstoffset');
		$html = '';

		$xml = self::XML2Array( $content );
		foreach ($xml as $k => $v)
		{
			if ($v == 'n/a') continue;

			if (in_array($k, $skip)) continue;

			if (in_array($k, array('CountryCode', 'Latitude', 'Longitude'))) {$$k = $v; continue;}

			$html .= "<p style='margin:3px;'><b>$k</b> : $v</p>";
		}
		$geo = (isset($Latitude) && isset($Longitude)) ? 	array('lat' => $Latitude, 'lng' => $Longitude) : array();
		$country = (isset($CountryCode)) ? 				$CountryCode : '';
		$subcountry = '';
		return self::cache_custom('ipinfodb', $ip, $geo, strtoupper($country), $subcountry, $html);
	}

	public static function geoplugin($content, $ip)
	{
		$skip = array('geoplugin_areaCode', 'geoplugin_dmaCode', 'geoplugin_continentCode', 'geoplugin_currencyCode', 'geoplugin_currencySymbol', 'geoplugin_currencyConverter');
		$html = '';
		$content = unserialize($content);
		foreach ($content as $k => $v)
		{
			if ($v == 'n/a') continue;

			if (in_array($k, $skip)) continue;

			if (in_array($k, array('geoplugin_region', 'geoplugin_countryCode', 'geoplugin_latitude', 'geoplugin_longitude'))) {$$k = $v; continue;}

			$html .= "<p style='margin:3px;'><b>$k</b> : $v</p>";
		}
		$geo = (isset($geoplugin_latitude) && isset($geoplugin_longitude)) ? 	array('lat' => $geoplugin_latitude, 'lng' => $geoplugin_longitude) : array();
		$country = (isset($geoplugin_countryCode)) ? 				$geoplugin_countryCode : '';
		$subcountry = (isset($geoplugin_region)) ? 					$geoplugin_region : '';
		return self::cache_custom('geoplugin', $ip, $geo, strtoupper($country), strtoupper($subcountry), $html);
	}

	public static function cache_custom($provider, $ip, $geo, $country, $subcountry, $html)
	{
		$stores = array('geo', 'country', 'subcountry', 'html');
		$providers  = self::get_providers(false);

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

	public static function XML2Array( $xml , $recursive = false )
	{
	    if ( ! $recursive )
	    {
	        $array = simplexml_load_string ( $xml ) ;
	    }
	    else
	    {
	        $array = $xml ;
	    }
   
	    $newArray = array () ;
	    $array = ( array ) $array ;
	    foreach ( $array as $key => $value )
	    {
	        $value = ( array ) $value ;
	        if ( isset ( $value [ 0 ] ) )
	        {
	            $newArray [ $key ] = trim ( $value [ 0 ] ) ;
	        }
	        else
	        {
	            $newArray [ $key ] = self::XML2Array ( $value , true ) ;
	        }
	    }
	    return $newArray ;
	}
}
?>