<?php 
abstract class MP_abstract
{

////	email	////

	public static function is_email($email)
	{
		if(eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $email)) return true;
		return false;
	}

//// functions ////

	public static function mp_redirect($location)
	{
		wp_redirect($location);
		die();
	}

	public static function require_class($classname, $fullpath = MP_TMP)
	{
		if (class_exists('MP_' . $classname)) return;
		require_once($fullpath . "mp-includes/class/MP_$classname.class.php");
	}

	public static function url($url, $url_parms = array(), $wpnonce = false)
	{
		foreach ($url_parms as $key => $value)
			if ($value != '')
				$url .= (strpos($url, '?') === false) ? "?$key=$value" : "&$key=$value";	
		if ($wpnonce) $url = wp_nonce_url( $url, $wpnonce );
		return $url;
	}

	public static function input_text($x, $perluette = '&') 
	{  
		return str_replace('"', $perluette . "QUOT;", stripslashes($x));
	}

	public static function select_option($list, $selected, $echo = true)
	{
		$x = '';
		foreach( $list as $key => $value )
			$x .= "<option " . selected( (string) $key, (string) $selected, false, false ) . " value='$key'>$value</option>\n";

		if (!$echo) return $x;
		echo $x;
	}

	public static function select_number($start, $max, $selected, $tick = 1, $echo = true)
	{
		$x = '';
		while ($start <= $max)
		{
			if (intval ($start/$tick) == $start/$tick ) 
				$x .= "<option " . selected( (string) $start, (string) $selected, false, false ) . " value='$start'>$start</option>\n";
			$start++;
		}
		if (!$echo) return $x;
		echo $x;
	}
}
?>