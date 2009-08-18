<?php 
MailPress::require_class('Admin_page_list');

class MP_AdminPage extends MP_Admin_page_list
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
		self::require_class('Import_importers');
		$importers = MP_Import_importers::get_all();

		return ( empty($importers) ) ? false : $importers;
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
		$actions['import'] = "<a href='$import_url' title='" . wptexturize(strip_tags($data[1])) . "'>" . ((strpos($id, 'export') !== false) ?  __('Export', 'MailPress') : __('Import', 'MailPress') ) . '</a>';

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