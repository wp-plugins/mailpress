<?php 
MailPress::require_class('Admin_list');

class MP_AdminPage extends MP_Admin_list
{
	const screen 	= MailPress_page_import;
	const capability 	= 'MailPress_import';

////  Columns  ////

	public static function get_columns() 
	{
		$columns = array(	'name' 	=> __('Name', 'MailPress'), 
					'desc'	=> __('Description', 'MailPress'));
		return $columns;
	}

//// List ////

	public static function get_list() 
	{
		// Load all importers so that they can register.
		$root = ABSPATH . MP_PATH . 'mp-admin/includes/options/import/importers';
		$dir  = @opendir($root);
		if ($dir) 
			while (($file = readdir($dir)) !== false) 
				if (($file{0} != '.') && (substr($file, -4) == '.php')) require_once("$root/$file");
		@closedir($dir);

		$importers = self::get_all();

		return ( empty($importers) ) ? false : $importers;
	}

	public static function get_all() 
	{
		global $mp_importers;
		if ( is_array($mp_importers) ) uasort($mp_importers, create_function('$a, $b', 'return strcmp($a[0], $b[0]);'));
		return $mp_importers;
	}

////  Row  ////

	public static function get_row( $id, $data ) 
	{

		static $row_class = '';

// url's
		$url_parms = array();
		$url_parms['mp_import'] 	= $id;
		$import_url = clean_url(self::url( MailPress_import, $url_parms ));
// actions
		$actions = array();
		$actions['import'] = "<a href='$import_url' title='" . wptexturize(strip_tags($data[1])) . "'>" . __('Import', 'MailPress') . '</a>';

		$row_class = 'alternate active' == $row_class ? '' : 'alternate active';

		$out = '';
		$out .= "<tr class='$row_class'>";

		$columns = self::get_columns();
		$hidden  = self::get_hidden_columns();

		foreach ( $columns as $column_name => $column_display_name ) 
		{
			$class = "class='$column_name column-$column_name'";

			$style = '';
			if ( in_array($column_name, $hidden) )
				$style = ' style="display:none;"';

			$attributes = "$class$style";

			switch ($column_name) 
			{
				case 'name':
					$out .= "<td $attributes><strong><a class='row-title' href='$import_url' title='" . attribute_escape(sprintf(__('Import "%s"', 'MailPress'), $data[1])) . "'>{$data[0]}</a></strong>";
					$out .= self::get_actions($actions);
					$out .= '</td>';
				break;
				case 'desc' :
					$out .= "<td $attributes>" . $data[1] . "</td>";
				break;
			}
		}
		$out .= '</tr>';

		return $out;
	}

//// Body ////

	public static function body()
	{
		include (MP_TMP . 'mp-admin/includes/import.php');
	}
}
?>