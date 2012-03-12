<?php
abstract class MP_useragent_
{
	function __construct() 
	{
		$this->img_path = site_url() . '/' . MP_PATH . "mp-admin/images/{$this->id}";
		$this->xml = new SimpleXMLElement(file_get_contents(MP_ABSPATH . "mp-admin/xml/{$this->id}s.xml"));
		$this->xml = $this->xml->{$this->id};

		add_filter('MailPress_useragent_' . $this->id . '_get',		array(&$this, 'get'), 	8, 1);
		add_filter('MailPress_useragent_' . $this->id . '_get_info',	array(&$this, 'get_info'),	8, 1);
	}

	function get($useragent)
	{
		$ug = new stdClass();

		foreach ($this->xml as $i)
		{
			if (!@preg_match($i->pattern, $useragent, $matches)) continue;

			if (!isset($i->versions)) break;

			foreach($i->versions as $attrs) $vp = (int) $attrs['pattern'];

			switch (true) 
			{
				case (isset($i->versions->id)) :
					foreach($i->versions->id as $ver)
					{
						if (@preg_match($ver->pattern, $matches[$vp]))
						{
							$version = (string) $ver->name;
							if (isset($ver->icon)) $i->icon = (string) $ver->icon;
							break;
						}
					}
				break;
				case (!empty($i->versions)) :
					@preg_match($i->versions, $useragent, $matches);
					if (isset($matches[$vp])) $version = $matches[$vp];
				break;
				default :
					$version = (string) $matches[$vp];
				break;
			}
			break;
		}

		if (isset($i->name)) { $ug->name = (string) $i->name; }
		if (isset($version)) { $ug->version = (string) $version; }
		if (isset($i->link)) { $ug->link = (string) $i->link; }
		if (isset($i->icon)) { $ug->icon = (string) $i->icon; $ug->icon_path = "{$this->img_path}/{$ug->icon}"; }

		$ug->full_name = ($ug->version) ? "{$ug->name} {$ug->version}" : $ug->name;

		return $ug;
	}

	function get_info($useragent)
	{
		$ug = $this->get($useragent);

		$txt = '';
		if (isset($ug->icon_path)) $txt .= "<img src='" . $ug->icon_path . "' alt='' />";
		if (isset($ug->link))
			$txt .= "&nbsp<a href='" . $ug->link . "' title='" . $ug->full_name . "' >" . $ug->name . '</a>';
		else
			$txt .= '&#160;' . $ug->full_name;
		return trim($txt);
	}
}