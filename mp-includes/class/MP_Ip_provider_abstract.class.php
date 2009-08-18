<?php
abstract class MP_Ip_provider_abstract
{
	function __construct()
	{
		add_filter('MailPress_ip_provider_register', 		array(&$this, 'register'), 8, 1);
		add_filter('MailPress_ip_content_' . $this->type, 	array(&$this, 'content'), 8, 3);
		add_filter('MailPress_ip_data_' . $this->provider, 	array(&$this, 'data'), 8, 6);
	}

	function register($providers)
	{
		$providers[$this->provider] = array('url' => $this->url, 'credit' => $this->credit, 'type' => $this->type);
		return $providers;
	}

	function cache_custom($ip, $geo = false, $country = false, $subcountry = false, $html = false)
	{
		$stores = array('geo', 'country', 'subcountry', 'html');

		$content['provider']['id'] 	 = $this->provider;
		$content['provider']['credit'] = $this->credit;

		foreach ($stores as $store) 
		{
			if (empty($$store)) 	continue;
			if (!$$store)		continue;
			$content[$store] = $$store;
		}

		file_put_contents(MP_TMP . 'tmp/' . $ip . '.spc', serialize($content));

		return $content;
	}

	function xml2array($input, $recurse = false)
	{
    		$data = ((!$recurse) && is_string($input)) ? simplexml_load_string($input): $input;
    		if ($data instanceof SimpleXMLElement) $data = (array) $data;
    		if (is_array($data)) foreach ($data as &$item) $item = $this->xml2array($item, true);
    		return $data;
	}
}
?>