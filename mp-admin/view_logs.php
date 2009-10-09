<?php 
MailPress::require_class('Admin_page_list');

class MP_AdminPage extends MP_Admin_page_list
{
	const screen 	= MailPress_page_view_logs;
	const capability	= 'MailPress_view_logs';
	const help_url	= 'http://www.mailpress.org/wiki/index.php?title=Add_ons:View_logs';

////  Redirect  ////

	public static function redirect() 
	{
		if (!isset( $_GET['delete'] )) return;		// MANAGING CHECKBOX REQUESTS
		$ftmplt	= self::get_file_template();
		$path 	= '../' . self::get_path();

		$deleted 	= 0;

		$url_parms 	= self::get_url_parms();

		foreach ($_GET['delete'] as $file)
		{							
			switch (true)
			{
				case ( isset( $_GET['deleteit'] )):
					@unlink($path . '/' . $file);
					$deleted++;
				break;
			}
		}
		if ($deleted) $url_parms['deleted'] = $deleted;
		self::mp_redirect( self::url(MailPress_view_logs, $url_parms) );
	}

	// for path
	public static function get_path() 
	{
		return MP_PATH . 'tmp';
	}

	// for file template
	public static function get_file_template()
	{
		global $wpdb;
		return (isset($wpdb->blogid)) ? 'MP_Log_' . $wpdb->blogid . '_' : 'MP_Log_' ;
	}

////  Columns  ////

	public static function get_columns() 
	{
		$columns = array(	'cb'		=> "<input type='checkbox' />", 
					'name'	=> __('Name', 'MailPress'));
		return $columns;
	}

////  List  ////

	public static function get_list($start, $num, $url_parms)
	{
		$ftmplt	= self::get_file_template();
		$path 	= '../' . self::get_path();

		$logs = array();
		if (is_dir($path) && ($l = opendir($path))) 
		{
			while (($file = readdir($l)) !== false) 
			{
		      	switch (true)
				{
					case ($file[0]  == '.') :
					break;
					case (isset($url_parms['s'])) :
						if ((strstr($file, $ftmplt)) && (strstr($file, $url_parms['s'])))
							$logs[] = $file;
					break;
					case (strstr($file, $ftmplt)) :
						$logs[] = $file;
					break;
				}
			}
			closedir($l);
		}
		$files = array();
		foreach ($logs as $log)	$files[] = array($log, filemtime($path . '/' . $log));

	// sort logs by date
		uasort($files, create_function('$a, $b', 'return strcmp($a[1], $b[1]);'));

		$logs = array();
		foreach ($files as $file) $logs[] = $file[0];

		$total = count($logs);
		$rows  = array_slice ($logs, $start, 25); // Grab a few extra

		return array($rows, $total);
	}

////  Row  ////

	public static function get_row($file, $url_parms)
	{
		static $row_class = '';


		$f 		= substr($file, strpos($file, str_replace( ABSPATH, '', WP_CONTENT_DIR )));
		$view_url 	= clean_url(MailPress_view_log . "&id=$f");
		$browse_url = '../' . self::get_path() . '/' . $f;
		$actions['view']   = "<a href='$view_url' title='" . sprintf( __('View "%1$s"', 'MailPress') , $file ) . "'>"	. __('View', 'MailPress') . '</a>';
		$actions['browse'] = "<a href='$browse_url' target='_blank' title='" . sprintf( __('Browse "%1$s"', 'MailPress') , $file ) . "'>"	. __('Browse', 'MailPress') . '</a>';

		$row_class = (" class='alternate'" == $row_class) ? '' : " class='alternate'";
		$attributes = "class='post-title column-title'";
?>
	<tr<?php echo $row_class; ?>>
		<th class="check-column" scope="row">
			<input type="checkbox" value="<?php echo $file; ?>" name="delete[]" />
		</th>
		<td  <?php echo $attributes ?>>
			<span style='display:block;'>
				<strong style='display:inline;'>
					<a class='row-title'href='<?php echo $view_url; ?>' title='<?php printf( __('View "%1$s"', 'MailPress') , $file ); ?>'>
						<?php echo $file; ?>
					</a>
				</strong>
			</span>
<?php echo self::get_actions($actions); ?>
		</td>
	</tr>
<?php
	}

//// Body ////

	public static function body()
	{
		include (MP_TMP . 'mp-admin/includes/view_logs.php');
	}
}
?>