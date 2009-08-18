<?php 
MailPress::require_class('Admin_page_list');

class MP_AdminPage extends MP_Admin_page_list
{
	const screen 	= 'MailPress_page_form_fields';
	const capability 	= 'MailPress_manage_forms';

	const add_form_id = 'add';
	const list_id 	= 'the-list';
	const tr_prefix_id = 'field';

////  Redirect  ////

	public static function redirect() 
	{
		if ( isset($_POST['action']) )    $action = $_POST['action'];
		elseif ( isset($_GET['action']) ) $action = $_GET['action'];  
		if ( isset($_GET['deleteit']) )   $action = 'bulk-delete';
		if (!isset($action)) return;

		$url_parms = self::get_url_parms(array('s', 'apage', 'id', 'form_id'));

		self::require_class('Forms_fields');

		switch($action) 
		{
			case 'add':
				$ret = MP_Forms_fields::insert($_POST);

				$url_parms['message'] = ( $ret && !is_wp_error( $ret ) ) ? 1 : 4;
				unset($url_parms['s']);
				self::mp_redirect( self::url(MailPress_fields, $url_parms) );
			break;

			case 'delete':
				MP_Forms_fields::delete($url_parms['id'], $url_parms['form_id']);
				unset($url_parms['id']);

				$url_parms['message'] = 2;
				self::mp_redirect( self::url(MailPress_fields, $url_parms) );
			break;

			case 'bulk-delete':
				foreach ( (array) $_GET['delete'] as $id ) 
				{
					MP_Forms_fields::delete($id, $url_parms['form_id']);
				}
				unset($url_parms['id']);

				$url_parms['message'] = ( count($_GET['delete']) > 1) ? 6 : 2;
				self::mp_redirect( self::url(MailPress_fields, $url_parms) );
			break;

			case 'edited':
				unset($_GET['action']);
				if (!isset($_POST['cancel'])) 
				{
					$e = MP_Forms_fields::insert($_POST);
					$url_parms['message'] = ( $e ) ? 3 : 5 ;
					$url_parms['action']  = 'edit';
				}
				else unset($url_parms['id']);

				self::mp_redirect( self::url(MailPress_fields, $url_parms) );
			break;

			default:
				if ( !empty($_GET['_wp_http_referer']) )
					self::mp_redirect(remove_query_arg(array('_wp_http_referer', '_wpnonce'), stripslashes($_SERVER['REQUEST_URI'])));
			break;
		}
	}

////  Title  ////

	public static function title() { if (isset($_GET['form_id'])) { global $title; $title = __('MailPress Forms Edit Fields', 'MailPress'); } }

////  Styles  ////

	public static function print_styles($styles = array()) 
	{
// for specific css
		$pathcss		= MP_TMP . 'mp-admin/includes/options/form/field_types/field_types_' . get_user_option('admin_color') . '.css';
		$css_url		= '/' . MP_PATH . 'mp-admin/includes/options/form/field_types/field_types_' . get_user_option('admin_color') . '.css';
		$css_url_default 	= '/' . MP_PATH . 'mp-admin/includes/options/form/field_types/field_types_fresh.css';
		$css_url		= (is_file($pathcss)) ? $css_url : $css_url_default;
		wp_register_style ( 'mp_field_types', 	$css_url);

		wp_register_style ( self::screen,		'/' . MP_PATH . 'mp-admin/css/form_fields.css', array('mp_field_types', 'thickbox') );
		$styles[] = self::screen;
		parent::print_styles($styles);
	}

//// Scripts ////

	public static function print_scripts() 
	{
		wp_register_script( 'mp-ajax-response',	'/' . MP_PATH . 'mp-includes/js/mp_ajax_response.js', array('jquery'), false, 1);
		wp_localize_script( 'mp-ajax-response', 	'wpAjax', array(
			'noPerm' => __('Email was not sent AND/OR Update database failed', 'MailPress'), 
			'broken' => __('An unidentified error has occurred.'), 
			'l10n_print_after' => 'try{convertEntities(wpAjax);}catch(e){};' 
		));

		wp_register_script( 'mp-lists', 		'/' . MP_PATH . 'mp-includes/js/mp_lists.js', array('mp-ajax-response'), false, 1);
		wp_localize_script( 'mp-lists', 		'wpListL10n', array( 
			'url' => MP_Action_url
		));

		wp_register_script( 'mp-taxonomy', 		'/' . MP_PATH . 'mp-includes/js/mp_taxonomy.js', array('mp-lists'), false, 1);
		wp_localize_script( 'mp-taxonomy', 		'MP_AdminPageL10n', array(	
			'pending' => __('%i% pending'), 
			'screen' => self::screen,
			'list_id' => self::list_id,
			'add_form_id' => self::add_form_id,
			'tr_prefix_id' => self::tr_prefix_id,
			'l10n_print_after' => 'try{convertEntities(MP_AdminPageL10n);}catch(e){};' 
		));

		wp_register_script( 'mp-thickbox', 		'/' . MP_PATH . 'mp-includes/js/mp_thickbox.js', array('thickbox'), false, 1);

		wp_register_script( self::screen, 		'/' . MP_PATH . 'mp-admin/js/form_fields.js', array('mp-taxonomy', 'mp-thickbox', 'jquery-ui-tabs'), false, 1);

		$scripts[] = self::screen;
		parent::print_scripts($scripts);
	}

//// Columns ////

	public static function get_columns() 
	{
		$columns = array(	'cb'		=> '<input type="checkbox" />',
					'name' 	=> __('Label', 'MailPress'),
				//	'description'=> __('Description', 'MailPress'),
					'type' 	=> __('Type', 'MailPress'),
					'order' 	=> __('Order', 'MailPress'),
					'required' 	=> __('Required', 'MailPress'),
					'template' 	=> __('Template', 'MailPress') 
				);
		return apply_filters('MailPress_form_columns_form_fields', $columns);
	}

	public static function add_incopy_column($columns)
	{
		$template 			= array_pop($columns);
		$columns['incopy']	= __('In&nbsp;copy', 'MailPress');
        	$columns['template'] 	= $template;
		return $columns;
    }

//// List ////

	public static function get_list($start, $num, $url_parms) 
	{
		global $wpdb;

		$order = "a.ordre";

		$where = ' (a.form_id = ' . $_GET['form_id'] . ') ';
		if (isset($url_parms['s']) && !empty($url_parms['s']))
		{
			$s = $wpdb->escape($url_parms['s']);
			if (!empty($where)) $where = $where . ' AND ';
			if ($s) $where .= " (a.label LIKE '%$s%' OR a.description LIKE '%$s%') "; 
		}

		if ($where) $where = ' WHERE ' . $where;

		$query = "SELECT DISTINCT SQL_CALC_FOUND_ROWS a.id, a.ordre FROM $wpdb->mp_fields a $where ORDER BY $order";

		return parent::get_list($start, $num, $query, 'mp_fields');
	}

////  Row  ////

	public static function get_row( $id, $url_parms, $checkbox = true ) 
	{
		static $row_class = '';

		self::require_class('Forms_fields');
		$field = MP_Forms_fields::get( $id );

		self::require_class('Forms_field_types');
		$field_types = MP_Forms_field_types::get_all();

// url's
		$url_parms['action'] 	= 'edit';
		$url_parms['id'] 		= $field->id;
		$url_parms['form_id'] 	= $field->form_id;

		$edit_url = clean_url(self::url( MailPress_fields, $url_parms ));
		$url_parms['action'] 	= 'duplicate';
		$duplicate_url = clean_url(self::url( MailPress_fields, $url_parms, 'duplicate-field_' . $field->id ));
		$url_parms['action'] 	= 'delete';
		$delete_url = clean_url(self::url( MailPress_fields, $url_parms, 'delete-form_' . $field->id ));
// actions
		$actions = array();
		$actions['edit'] = '<a href="' . $edit_url . '">' . __('Edit') . '</a>';
		$actions['duplicate'] = "<a class='dim:" . self::list_id . ":" . self::tr_prefix_id . "-" . $field->id . ":unapproved:e7e7d3:e7e7d3' href='$duplicate_url'>" . __('Duplicate', 'MailPress') . "</a>";
		$actions['delete'] = "<a class='delete:" . self::list_id . ":" . self::tr_prefix_id . "-" . $field->id . " submitdelete' href='$delete_url'>" . __('Delete') . "</a>";

		$row_class = 'alternate' == $row_class ? '' : 'alternate';

// protected
		$disabled = '';
		if (isset($field->settings['options']['protected']))
		{
			unset($actions['duplicate'], $actions['delete']);
			$disabled = " disabled='disabled'";
		}

		$out = '';
		$out .= "<tr id='" . self::tr_prefix_id . "-$field->id' class='iedit $row_class'>";

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
				case 'cb':
					$out .= (!$disabled) ? '<th scope="row" class="check-column"> <input type="checkbox" name="delete[]" value="' . $field->id . '" /></th>' : '<th scope="row" class="check-column"></th>';
				break;
				case 'name':
					$out .= '<td ' . $attributes . '><strong><a class="row-title" href="' . $edit_url . '" title="' . attribute_escape(sprintf(__('Edit "%s"'), stripslashes($field->label))) . '">' . stripslashes($field->label) . '</a></strong><br />';
					$out .= self::get_actions($actions);
					$out .= '</td>';
				break;
				case 'type':
	 				$out .= "<td $attributes>";
					$out .= "<div class='field_type_" . $field->type . "'  style='margin-top:10px;padding-left:28px;'>" . $field_types[$field->type]['desc'] . "</div>";
	 				$out .= "</td>\n";
	 			break;
				case 'incopy':
	 				$out .= "<td $attributes>";
					if (isset($field->settings['options']['incopy'])) $out .= __('yes', 'MailPress');
	 				$out .= "</td>\n";
	 			break;
				case 'required':
	 				$out .= "<td $attributes>";
					if (isset($field->settings['controls']['required'])) $out .= __('yes', 'MailPress');
	 				$out .= "</td>\n";
	 			break;
				case 'order':
	 				$out .= "<td $attributes>";
					$out .= $field->ordre;
	 				$out .= "</td>\n";
	 			break;
				default:
					$out .= '<td ' . $attributes . '>';
					$out .= $field->{$column_name};
					$out .= '</td>';
				break;
			}
		}
		$out .= "</tr>\n";

		return $out;
	}

//// Body ////

	public static function body()
	{
		include (MP_TMP . 'mp-admin/includes/form_fields.php');
	}
}
?>