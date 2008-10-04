<?php
class MP_Log
{
	const noMP_Log	= 123456789;

	function MP_Log($name,$path,$plug = '',$force=false,$option_name='MailPress_general')
	{
		$this->data = "\n";

		$this->errors 	= array (	1 	=> 'E_ERROR', 
							2 	=> 'E_WARNING', 
							4 	=> 'E_PARSE', 
							8 	=> 'E_NOTICE', 
							16 	=> 'E_CORE_ERROR', 
							32 	=> 'E_CORE_WARNING', 
							64 	=> 'E_COMPILE_ERROR', 
							128 	=> 'E_COMPILE_WARNING', 
							256 	=> 'E_USER_ERROR', 
							512 	=> 'E_USER_WARNING * ', 
							1024 	=> 'E_USER_NOTICE', 
							2048 	=> 'E_STRICT', 
							4096 	=> 'E_RECOVERABLE_ERROR', 
							8191 	=> 'E_ALL' ); 

		$this->name 	= $name;
		$this->path 	= $path . 'tmp';
		$this->plug 	= $plug;
		$this->option_name= $option_name;

		$this->ftmplt	= 'MP_Log' . '_' . $this->plug . '_' . $this->name . '_';
		$this->file 	= $this->path . '/' . $this->ftmplt . date('Ymd') . '.txt';

		$this->log_options	 = get_option($this->option_name);
		$this->level 	= (isset($this->log_options['level']))    ? (int) $this->log_options['level'] 	: MP_Log::noMP_Log ;
		$this->levels	= array (	1 	=> 1,
							2 	=> 2,
							4 	=> 4,
							8 	=> 8,
							16 	=> 16,
							32 	=> 32,
							64 	=> 64,
							128 	=> 128,
							256 	=> 256,
							512 	=> 512,
							1024 	=> 1024,
							2048 	=> 2048,
							4096 	=> 4096,
							8191 	=> 8191 );
		if ($force) 
		{
			foreach ($this->levels as $k => $v) $this->levels[$k] = 0;
			$this->level = 0;
		}
		if (!is_dir($this->path)) $this->level = MP_Log::noMP_Log ;
		if (MP_Log::noMP_Log == $this->level) return;
		if ( 0  != $this->level) set_error_handler(array(&$this,logError),$this->level);

		if ($force) 	
$this->log (" **** Start logging **** $this->plug *** $this->name *** log forced");
		else
$this->log (" **** Start logging **** $this->plug *** $this->name *** level : $this->level");

// purge log
		$now = date('Ymd');
		$this->lastpurge 	= (isset($this->log_options['lastpurge'])) ? $this->log_options['lastpurge'] 		: $now;
		$this->lognbr 	= (isset($this->log_options['lognbr']))    ? (int) $this->log_options['lognbr'] 	: 1;
		
		if ($now != $this->lastpurge) 
		{
			$this->dopurge ($now);
			$this->log_options['lastpurge'] = $now;
			if (!add_option ($this->option_name, $this->log_options ))
				update_option ($this->option_name, $this->log_options );
		}

		ob_start();
	}

	function log($x,$level=0)
	{
		if (MP_Log::noMP_Log    == $this->level) return;
		if ($level <= $this->level) $this->data .= date('Y-m-d H:i:s u') . " -- " . $x . "\n";
	}

	function logError($error_level, $error_message, $error_file, $error_line, $error_context)
	{ 
$this->log ("PHP [" . $this->errors[$error_level] . "] $error_level : $error_message in $error_file at line $error_line $y",$error_level);
	}

	function end($y=true)
	{
			if (MP_Log::noMP_Log == $this->level) return;
			if (0   != $this->level) restore_error_handler();

			$log = ob_get_contents();
		ob_end_clean();
		if (!empty($log)) $this->log($log);
		$y = ($y) ? "TRUE" : "FALSE";
$this->log("LOG ended with status  : " . $y );
$this->log (" **** End logging   **** $this->plug *** $this->name *** level : $this->level");
		$this->fh = fopen($this->file , 'a+');
		fputs($this->fh, $this->data); 
		fclose($this->fh); 
// mem'ries ...
		$xs = array( 	'this->data', 'this->errors', 'this->name', 'this->path', 'this->plug', 'this->ftmplt', 'this->file,', 'this->level', 'this->levels', 'this->lastpurge', 'this->lognbr');
		foreach ($xs as $x) if (isset($$x)) unset($$x);
	}

	function dopurge ($now)
	{
		$xs = array();
		if ($l = opendir($this->path)) 
		{
      		while (($file = readdir($l)) !== false) 
			{
           			switch (true)
				{
					case ($file  == '.') :
					break;
					case ($file  == '..') :
					break;
					case (strstr($file,$this->ftmplt)) :
						$xs[] = $file;
					break;
				}
      		}
      	 	closedir($l);
    		}
		if (count($xs) > $this->lognbr)
		{
			$y = count($xs) - $this->lognbr;
			sort($xs);
		 	foreach ($xs as $x)
			{
				unlink($this->path . '/' . $x);
$this->log (" **** Purged log file **** " . $this->path . '/' . $x);
				if (0 == $y) break; 
				$y--;
			}
		}
	}
}
?>