<?php
abstract class MP_Options
{
	function __construct()
	{
   		// Load all options so that they can do what they have to do.
		$root = ABSPATH . MP_PATH . 'mp-admin/includes/options/' . $this->path;
		$dir  = @opendir($root);
		if ($dir) while ( ($file = readdir($dir)) !== false ) if ($file[0] != '.') $this->load($root, $file);
		@closedir($dir);
	}

	function load($root, $file)
	{
   		if ($file{0} == '.') return;
		if (isset($this->deep))
		{
			if (is_dir("$root/$file"))
			{
				$root .= "/$file";
				$dir  = @opendir($root);
				if ($dir) while (($file = readdir($dir)) !== false) $this->load_file("$root/$file");
				@closedir($dir);
				return;
			}
		}
		elseif ( isset($this->includes) && !isset($this->includes[substr($file, 0, -4)]) ) return;

		$this->load_file("$root/$file");
	}

	function load_file($file)
	{
		if (substr($file, -4) != '.php') return;
//if (MailPress::debug) { global $mp_debug_log; if (isset($mp_debug_log)) $mp_debug_log->log(" คค loading " . basename($file) . " ..." ); }
		require_once($file);
//if (MailPress::debug) { global $mp_debug_log; if (isset($mp_debug_log)) $mp_debug_log->log(" คคค loaded " . basename($file) ); }
	}
}
?>