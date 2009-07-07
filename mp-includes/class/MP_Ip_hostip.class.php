<?php
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
?>