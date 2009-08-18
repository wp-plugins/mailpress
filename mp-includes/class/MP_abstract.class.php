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

	public static function print_scripts_l10n_val($val0, $before = "")
	{
		if (is_array($val0))
		{
			$eol = "\t\t";
			$text =  "{\n\t$before";
			foreach($val0 as $var => $val)
			{
				$text .=  "$eol$var: " . self::print_scripts_l10n_val($val, "\t" . $before );
				$eol = ", \n$before\t\t\t";
			}
			$text .= "\n\t\t$before}";
		}
		else
		{
			$quot = (stripos($val0, '"') === false) ? '"' : "'";
			$text = "$quot$val0$quot";
		}
		return $text;
	}

	public static function mp_redirect($location)
	{
		wp_redirect($location);
		self::mp_die();
	}

	public static function mp_die($r = true)
	{
		if (MailPress::debug) { global $mp_debug_log; if ($r !== false) $mp_debug_log->log(" ******* Dying : >> $r << "); $mp_debug_log->end(true); }
		die($r);
	}

	public static function require_class($classname, $fullpath = MP_TMP)
	{
		if (class_exists('MP_' . $classname)) return;
//if (MailPress::debug) { global $mp_debug_log; if (isset($mp_debug_log)) $mp_debug_log->log(" ¤¤ loading MP_$classname.class.php ..." ); }
		require_once($fullpath . "mp-includes/class/MP_$classname.class.php");
//if (MailPress::debug) { global $mp_debug_log; if (isset($mp_debug_log)) $mp_debug_log->log(" ¤¤¤ MP_$classname.class.php loaded " ); }
	}

	public static function url($url, $url_parms = array(), $wpnonce = false)
	{
		foreach ($url_parms as $key => $value)
			if ($value != '') $url .= (strpos($url, '?') === false) ? "?$key=$value" : "&$key=$value";	
		if ($wpnonce) $url = wp_nonce_url( $url, $wpnonce );
		return $url;
	}

	public static function input_text($x) 
	{  
		return str_replace('"', '&QUOT;', stripslashes($x));
	}

	public static function select_option($list, $selected, $echo = true)
	{
		$x = '';
		foreach( $list as $value => $label )
		{
			$_selected = (!is_array($selected)) ? $selected : ( (in_array($value, $selected)) ? $value : null );
			$x .= "<option " . self::selected( (string) $value, (string) $_selected, false, false ) . " value=\"$value\">$label</option>";
		}
		if (!$echo) return "\n$x\n";
		echo "\n$x\n";
	}

	public static function select_number($start, $max, $selected, $tick = 1, $echo = true)
	{
		$x = '';
		while ($start <= $max)
		{
			if (intval ($start/$tick) == $start/$tick ) 
				$x .= "<option " . self::selected( (string) $start, (string) $selected, false, false ) . " value='$start'>$start</option>";
			$start++;
		}
		if (!$echo) return "\n$x\n";
		echo "\n$x\n";
	}

	public static function selected( $selected, $current = true, $echo = true) 
	{
		return self::__checked_selected_helper( $selected, $current, $echo, 'selected' );
	}

	public static function __checked_selected_helper( $helper, $current, $echo, $type) 
	{
		$result = ( $helper == $current) ? " $type='$type'" : '';
		if ($echo) echo $result;
		return $result;
	}
}
?>