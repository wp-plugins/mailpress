<?php
MailPress::require_class('Import_importer_abstract');
class MP_import_csv extends MP_Import_importer_abstract
{
	function __construct() 
	{
		$this->importer 		= 'csv';
		$this->description 	= __('Import your <strong>csv</strong> file.', 'MailPress');
		$this->header 		= __('Import Csv', 'MailPress');
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
					$this->end_trace(true);
					if ($sniff)
						$this->fileform();
					else
						$this->error('<p><strong>' . __('Unable to determine email location', 'MailPress') . '</strong></p>');
				}
				else
				{
					$this->message_report("** ERROR ** ! Could not upload the file");
					$this->end_trace(false);
				}
			break;
			case 2:
				$this->start_trace($step);
					$import = $this->import( $_GET['id'] );
				$this->end_trace(true);
				if ($import)
					$this->success('<p>' . sprintf(__("<b>File imported</b> : <i>%s</i>", 'MailPress'), $this->file) . '</p><p><strong>' . sprintf(__("<b>Number of records</b> : <i>%s</i>", 'MailPress'), $import) . '</strong></p>');
				else 
					$this->error('<p><strong>' . $this->file . '</strong></p>');
			break;
		}
		$this->footer();
	}

// step 0

// step 1

	function sniff($step, $first=true)
	{
		$this->message_report(" sniff $step    ! >>> " . $this->file);

		require_once 'parsecsv/parsecsv.lib.php';
		$this->csv = new parseCSV();
		$this->csv->auto($this->file);
		$this->hasheader = true;

		return ($first) ? $this->find_email() : true;
	}

	function find_email()
	{
		$i = 0;
		$email = array();
		foreach ($this->csv->data as $row)
		{
			foreach ($row as $k => $v)	if (MP_AdminPage::is_email($v)) if (isset($email[$k])) $email[$k]++; else $email[$k] = 1;

			$i++;
			if ($i > 9) break;
		}

		if (0 == count($email))
		{
			$this->message_report(' **WARNING* ! Unable to determine email location');
			return false;
		}

		asort($email);
		$x = array_flip($email);
		$this->emailcol = end($x);
		
		$this->message_report(' email      ! ' . sprintf('Email probably in column %s', $this->emailcol));

		return true;
	}

	function fileform() 
	{
		if (class_exists('MailPress_mailinglist'))
		{
			$draft_dest = $x = array ('' => '');
			$draft_dest = apply_filters('MailPress_mailinglists', $draft_dest);
		}
?>
	<form action="<?php echo MailPress_import; ?>&amp;mp_import=csv&amp;step=2&amp;id=<?php echo $this->id; ?>" method="post">
<?php if (class_exists('MailPress_mailinglist')) : ?>
		<h3><?php _e('Mailing list', 'MailPress'); ?></h3>
		<p><?php _e('Optional, you can import the MailPress users in a specific mailing list ...', 'MailPress'); ?></p>
		<select name='mailinglist' id='mailinglist'>
<?php MP_AdminPage::select_option($draft_dest, 'MailPress_mailinglist~' . get_option('MailPress_default_mailinglist')) ?>
		</select>
<?php endif; ?>
		<h3><?php _e('File scan', 'MailPress'); ?></h3>
		<p><?php printf(__("On the first records (see hereunder), the file scan found that the email is in column '<strong>%s</strong>'.", 'MailPress'), $this->emailcol); ?>
		<?php _e('However, you can select another column.<br /> Invalid emails will not be inserted.', 'MailPress'); ?></p>
		<table class='widefat'>
			<thead>
				<tr>
					<td style='width:auto;'><?php _e('Choose email column', 'MailPress'); ?></td>
<?php
		foreach ($this->csv->data as $row)
		{
			foreach ($row as $k => $v)
			{
?>
					<td><input type='radio' name='is_email' value="<?php echo $k; ?>" <?php if ($k == $this->emailcol) echo "checked='checked'"; ?> /><span><?php echo $k; ?></span></td>
<?php
			}
			break;
		}
?>
				</tr>
				<tr>
					<td><?php _e('Choose name column', 'MailPress'); ?></td>
<?php
		foreach ($this->csv->data as $row)
		{
			foreach ($row as $k => $v)
			{
?>
					<td><input type='radio' name='is_name' value="<?php echo $k; ?>" /><span><?php echo $k; ?></span></td>
<?php
			}
			break;
		}
?>
				</tr>
			</thead>
			<tbody>
<?php
		$i = 0;
		foreach ($this->csv->data as $row)
		{
?>
				<tr>
					<td></td>
<?php
			foreach ($row as $k => $v)
			{
?>
					<td><span <?php if ($k == $this->emailcol) if (!MP_AdminPage::is_email($v)) echo "style='background-color:#fdd;'"; else echo "style='background-color:#dfd;'";?>><?php echo $v; ?></span></td>
<?php
			}
?>
				</tr>
<?php
			$i++;
			if ($i > 9) break;
		}
?>
			</tbody>
		</table>
		<p class='submit'>
			<input class='button-primary' type='submit' value="<?php echo attribute_escape( __('Submit') ); ?>" />
		</p>
	</form>
<?php
	}

// step 2

	function import($id) 
	{
		$this->id = (int) $id;
		$this->file = get_attached_file($this->id);

		$this->message_report(" IMPORTING  !");

		$this->sniff(2, false);

		if ( !file_exists( $this->file) ) {	$this->message_report("File not found" . $this->file); return false;}

		$this->emailcol = $_POST['is_email'];
		$this->namecol  = $_POST['is_name'];
		
		if (!empty($_POST['mailinglist']))
		{
			$mailinglist_ID = str_replace('MailPress_mailing_list~', '', $_POST['mailinglist'], $mailinglist_ok);

			MP_AdminPage::require_class('Mailinglists');
			$mailinglist_name = MP_Mailinglists::get_name($mailinglist_ID);
		}

		$i = 0;
		foreach ($this->csv->data as $row)
		{
			$i++;

			$curremail = trim(strtolower($row[$this->emailcol]));
			$currname  = trim(strtolower($row[$this->namecol]));
			$mp_user_id = $this->sync_mp_user($curremail, $currname);

			if ($mp_user_id)
			{
				if (isset($mailinglist_ok) && $mailinglist_ok)
				{
					$this->sync_mp_user_mailinglist($mp_user_id, $mailinglist_ID, $curremail, $mailinglist_name);
				}

				foreach ($row as $k => $v)
				{
					if ($k == $this->emailcol) continue;
					if ($k == $this->namecol) continue;

					$this->sync_mp_usermeta($mp_user_id, $k, $v);
				}
			}
		}
		return $i;
	}
}
$MP_import_csv = new MP_import_csv();
?>