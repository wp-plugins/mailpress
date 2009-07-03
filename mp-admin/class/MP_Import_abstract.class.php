<?php 
abstract class MP_Import_abstract
{
	const parent_plugins_dir = 'wp-content';
	const bt = 133;

	function __construct() 
	{
		mp_register_importer($this->importer, $this->importer, $this->importer_text, array (&$this, 'dispatch'));
		$this->trace_header = false;
	}

	function header() 
	{
		echo "<div class='wrap'><div id='icon-mailpress-tools' class='icon32'><br /></div><h2>" . $this->importer_header . '</h2>';
	}

	function footer() 
	{
		echo '</div>';
	}

// step 0

	function greet() 
	{
?>
<div>
	<p>
<?php		_e('Howdy! Upload your file and we&#8217;ll import the emails and much more ... into this blog.', 'MailPress'); ?>
		<br />
<?php		_e('Choose a file to upload, then click Upload file and import.', 'MailPress'); ?>
	</p>
<?php wp_import_upload_form( MailPress_import . '&amp;mp_import=' . $this->importer . '&amp;step=1'); ?>
</div>
<?php
	}

// step 1

	function handle_upload() 
	{
		$file = wp_import_handle_upload();
		if ( isset($file['error']) )
		{
			$this->trace->end(true);
			$this->error('<p><strong>' . $file['error'] . '</strong></p>');
			return false;
		}
		
		$this->file = $file['file'];
		$this->id = (int) $file['id'];
		return true;
	}

// for files

	function fopen($filename, $mode='r') 
	{
		if ( $this->has_gzip() ) return gzopen($filename, $mode);
		return fopen($filename, $mode);
	}

	function feof($fp) 
	{
		if ( $this->has_gzip() ) return gzeof($fp);
		return feof($fp);
	}

	function fgets($fp, $len=8192) 
	{
		if ( $this->has_gzip() ) return gzgets($fp, $len);
		return fgets($fp, $len);
	}

	function fclose($fp) 
	{
		if ( $this->has_gzip() ) return gzclose($fp);
		return fclose($fp);
	}

	function has_gzip() 
	{
		return is_callable('gzopen');
	}

// for db tables

	public static function tableExists($table) 
	{
		global $wpdb;
		return strcasecmp($wpdb->get_var("show tables like '$table'"), $table) == 0;
	}

// for logs

	function start_trace($step)
	{
		MP_AdminPage::require_class('Log');
		$this->trace = new MP_Log($this->importer, ABSPATH . MP_PATH, MP_FOLDER, false, 'import');
		$this->header_report($step);
	}

	function end_trace($rc)
	{
   		$this->footer_report();
		$this->trace->end($rc);
	}

	function header_report($step)
	{
		if ((isset($this->trace_header)) && $this->trace_header) return;
		$this->trace_header = true;
		$this->message_report(str_repeat( '-', self::bt));
		$this->message_report(str_repeat( ' ', 5) . "Batch Report   importer : " . $this->importer . "     step : $step" );
		$this->message_report(str_repeat( '-', self::bt));
	}

	function message_report($bm)
	{
		if (!$this->trace_header) $this->header_report('?');

		$bl = strlen($bm);
		$bl = self::bt - $bl;
		$bl = ($bl < 0) ? 0 : $bl;
		$bm = '!' . $bm . str_repeat( ' ', $bl ) . '!';
		if ($this->trace) 
			$this->trace->log($bm);
		else
			echo '<pre>' . $bm . "</pre>\n";
	}

	function footer_report()
	{
		if ((isset($this->trace_footer)) && $this->trace_footer) return;
		$this->trace_footer = true;
		$this->message_report(str_repeat( '-', self::bt));
	}

	function link_trace()
	{
		if ( isset($this->trace->file) && file_exists($this->trace->file) )
		{ 
			$file = $this->trace->file;
			$y = substr($this->trace->file, strpos($this->trace->file, self::parent_plugins_dir));
			return "<p><a href='../$y' target='_blank'>" . __('See the log', 'MailPress') . '</a></p>';
		}
		return '';
	}

// for success & errors

	function success($text = '', $echo = true)
	{
		$x  = '<div><h3>'.__('Import successfull', 'MailPress').'</h3>';
		$x .= $text;
		$x .= $this->link_trace();
		$x .= '</div>';

		if ($echo) echo $x;
		return $x;
	}

	function error($text = '', $echo = true)
	{
		$x  = '<div><h3>'.__('Sorry, there has been an error.', 'MailPress').'</h3>';
		$x .= $text;
		$x .= $this->link_trace();
		$x .= '</div>';

		if ($echo) echo $x;
		return $x;
	}

////  IMPORT API  ////

	function sync_mp_user($email, $name, $status = 'active')
	{
		$xl = strlen($email);
		$xl = ((25 - $xl) < 0) ? 0 : 25 - $xl;
		$x = $email . str_repeat( ' ', $xl);

		if ( !MP_AdminPage::is_email($email))
		{
			$this->message_report("** ERROR ** ! $x not an email ($name)");
		 	return false;
		}
		
		MP_AdminPage::require_class('Users');
		if (MP_Users::get_status_by_email($email))
		{
			$this->message_report(" **WARNING* ! $x already exists ($name) (processed if extra work to do)");
		}
		else
		{
		 	$key = md5(uniqid(rand(), 1));	
			MP_Users::insert($email, $name, $key, $status, true);

			$this->message_report(" insert     ! $x inserted ($name)");
		}
		return MP_Users::get_id_by_email($email);
	}

	function sync_mp_usermeta($mp_user_id, $meta_key, $meta_value)
	{
		MP_AdminPage::require_class('Usermeta');
		MP_Usermeta::update( $mp_user_id, $meta_key, $meta_value ) ;

		$this->message_report(" meta       ! user [$mp_user_id]=> update of meta data key=>\"$meta_key\" data=>\"$meta_value\"");
	}

	function sync_mailinglist($mailinglist) 
	{
		if (!class_exists('MailPress_mailinglist')) return false;

		MP_AdminPage::require_class('Mailinglists');
		if ($id = MP_Mailinglists::get_id('MailPress_import_' . $mailinglist))
		{
			$this->message_report(" mailinglist! mailing list found : [$id] => $mailinglist");
			return $id;
		}

		if ($id = MP_Mailinglists::insert(array('name'=>'MailPress_import_' . $mailinglist)))
		{
			$this->message_report(" mailinglist! mailing list inserted : [$id] => $mailinglist");
			return $id;
		}

		$this->message_report("** ERROR ** ! Unable to read or create a mailing list : $mailinglist");
		return false;
	}

	function sync_mp_user_mailinglist($mp_user_id, $mailinglist_ID, $email='', $mailinglist='', $trace=false) 
	{
		if (!class_exists('MailPress_mailinglist')) return false;

		MP_AdminPage::require_class('Mailinglists');
		$user_mailinglists = MP_Mailinglists::get_object_terms($mp_user_id);
		if (in_array($mailinglist_ID, $user_mailinglists))
		{
			$xl = strlen($email);
			$xl = ((25 - $xl) < 0) ? 0 : 25 - $xl;
			$x = $email . str_repeat( ' ', $xl);
			$this->message_report(" mailinglist! $x [$mp_user_id] already in mailing list [$mailinglist_ID] => $mailinglist");
		}
		else
		{
			array_push($user_mailinglists, $mailinglist_ID);
			MP_Mailinglists::set_object_terms( $mp_user_id, $user_mailinglists );

			$xl = strlen($email);
			$xl = ((25 - $xl) < 0) ? 0 : 25 - $xl;
			$x = $email . str_repeat( ' ', $xl);
			$this->message_report(" mailinglist! $x [$mp_user_id] inserted in mailing list [$mailinglist_ID] => $mailinglist");
		}
	}
}
?>