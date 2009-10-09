<?php
MailPress::require_class('Import_importer_abstract');
class MP_import_xmlsample extends MP_Import_importer_abstract
{
	function __construct() 
	{
		$this->importer 		= 'xmlsample';
		$this->description	= __('Import your <strong>xmlsample</strong> file.', 'MailPress');
		$this->header 		= __('Import XML sample', 'MailPress');
		$this->callback 		= array (&$this, 'dispatch');
		parent::__construct();
	}

	function dispatch($step = 0) 
	{
		if (isset($_GET['step']) && !empty($_GET['step'])) $step = (int) $_GET['step'];

		$this->header();
		switch ($step) 
		{
			case 0 :
				$this->greet();
			break;
			case 1 :
				$this->start_trace($step);
				if ( $this->handle_upload() )
				{
	               		$this->message_report(" ANALYSIS   !");
						$sniff = $this->sniff($step);

					if ($sniff)
					{
						$this->message_report(" IMPORTING  !");
							$import = $this->import();
						$this->end_trace($import);
						if ($import)
							$this->success('<p>' . sprintf(__("<b>File imported</b> : <i>%s</i>", 'MailPress'), $this->file) . '</p><p><strong>' . sprintf(__("<b>Number of records</b> : <i>%s</i>", 'MailPress'), $import) . '</strong></p>');
						else 
							$this->error('<p><strong>' . $this->file . '</strong></p>');
					}
					else
					{
						$this->end_trace($sniff);
						$this->error('<p><strong>' . $this->file . '</strong></p>');
						return false;
					}
				}
				else
				{
					$this->message_report("** ERROR ** ! Could not upload the file");
					$this->error('');
					$this->end_trace(false);
				}
			break;
		}
		$this->footer();
	}

// step 0

// step 1

	function sniff($step) 
	{
		$this->message_report(" sniff $step    ! >>> " . $this->file);

		$this->xml = '';
		$fp = $this->fopen($this->file, 'r');
		if ($fp) 
		{
			while ( !$this->feof($fp) ) 
			{
				$this->xml .= $this->fgets($fp);
			}
			$this->fclose($fp);
		}
		else 
		{
			$this->message_report("** ERROR ** ! Please upload a valid file");
			return false;
		}

		try 
		{
			set_error_handler(array(&$this, 'HandleXmlError'));
			$this->dom = New DOMDocument();
			$this->dom->loadXML($this->xml);
			restore_error_handler();
		}
		catch (DOMException $e) 
		{
			$this->message_report("* XML ERROR*! There was a problem with this file : ");
			$this->message_report("* XML ERROR*! " . $this->file );
			$this->message_report("* XML ERROR*! " . $e->getMessage() );
			return false;
		}

		$this->root = $this->parse_node($this->dom, 'MailPress');

		if (!$this->root || ($this->root->nodeName != 'MailPress'))
		{
			$this->message_report("* XML ERROR*! Wrong file : " . $this->file );
			return false;
		}
		return true;
	}

	function HandleXmlError($errno, $errstr, $errfile, $errline)
	{
		if ($errno==E_WARNING && (substr_count($errstr, "DOMDocument::loadXML()")>0))
			throw new DOMException($errstr);
		else
			return false;
	}

	function parse_node($node, $tagname) 
	{
		$xs = $node->getElementsByTagname($tagname); 
		foreach ($xs as $x) {};
		return (isset($x)) ? $x : false;
	}

	function import() 
	{
		$mailinglist_ID = false;

		if (class_exists('MailPress_mailinglist'))
		{
			$x = $this->parse_node($this->root, 'mailinglist');
			if ($x)
			{
				$mailinglist = trim($x->nodeValue);

				$mailinglist_ID = $this->sync_mailinglist($mailinglist);

				if (!$mailinglist_ID)
				{
					$this->message_report(' mailinglist! ' . sprintf(__('Unable to read or create a mailing list : %s', 'MailPress'), $mailinglist));
					return false;
				}
			}
		}

		$i = 0;
		$userf = $this->parse_node($this->root, 'users');
		$users = $userf->getElementsByTagname('user');
		foreach ($users as $user)
		{
			$i++;

			$email = trim($user->getAttribute('email'));
			$name  = trim($user->getAttribute('name'));

			$mp_user_id = $this->sync_mp_user($email, $name);

			if ($mp_user_id)
			{
				if ($mailinglist_ID)
				{
					$this->sync_mp_user_mailinglist($mp_user_id, $mailinglist_ID, $email, $mailinglist);
				}

				$dataf = $this->parse_node($user, 'datas');
				$datas = $dataf->getElementsByTagname('data');

				foreach ($datas as $data)
				{
					$attr = $data->getAttribute('id');
					$val  = trim($data->nodeValue);

					$this->sync_mp_usermeta($mp_user_id, $attr, $val);
				}
			}
		}
		return $i;
	}
}
$MP_import_xmlsample = new MP_import_xmlsample();
?>