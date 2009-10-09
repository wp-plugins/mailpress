<?php 
MailPress::require_class('Admin_page_list');

class MP_AdminPage extends MP_Admin_page_list
{
	const screen 	= MailPress_page_autoresponders;
	const capability 	= 'MailPress_manage_autoresponders';
	const help_url	= 'http://www.mailpress.org/wiki/index.php?title=Add_ons:Autoresponder';

	const taxonomy 	= MailPress_autoresponder::taxonomy;

	const add_form_id = 'add';
	const list_id 	= 'the-list';
	const tr_prefix_id = 'atrspndr';

////  Redirect  ////

	public static function redirect() 
	{
		if ( isset($_POST['action']) )    $action = $_POST['action'];
		elseif ( isset($_GET['action']) ) $action = $_GET['action'];  
		if ( isset($_GET['deleteit']) )   $action = 'bulk-delete';
		if (!isset($action)) return;

		$url_parms = self::get_url_parms(array('s', 'apage', 'id'));

		self::require_class('Autoresponders');

		switch($action) 
		{
			case 'add':
				$e = MP_Autoresponders::insert($_POST);
				$url_parms['message'] = ( $e && !is_wp_error( $e ) ) ? 1 : 4;
				unset($url_parms['s']);
				self::mp_redirect( self::url(MailPress_autoresponders, $url_parms) );
			break;

			case 'delete':
				MP_Autoresponders::delete($url_parms['id']);
				unset($url_parms['id']);

				$url_parms['message'] = 2;
				self::mp_redirect( self::url(MailPress_autoresponders, $url_parms) );
			break;

			case 'bulk-delete':
				foreach ( (array) $_GET['delete'] as $id ) 
				{
					MP_Autoresponders::delete($id);
				}

				$url_parms['message'] = ( count($_GET['delete']) > 1) ? 6 : 2;
				self::mp_redirect( self::url(MailPress_autoresponders, $url_parms) );
			break;

			case 'edited':
				unset($_GET['action']);
				if (!isset($_POST['cancel'])) 
				{
					$e = MP_Autoresponders::insert($_POST);
					$url_parms['message'] = ( !is_wp_error($e) ) ? 3 : 5 ;
				}
				unset($url_parms['id']);
				self::mp_redirect( self::url(MailPress_autoresponders, $url_parms) );
			break;

			default:
				if ( !empty($_GET['_wp_http_referer']) )
					self::mp_redirect(remove_query_arg(array('_wp_http_referer', '_wpnonce'), stripslashes($_SERVER['REQUEST_URI'])));
			break;
		}
	}

////  Title  ////

	public static function title() { if (isset($_GET['id'])) { global $title; $title = __('Edit Autoresponder', 'MailPress'); } }

////  Styles  ////

	public static function print_styles($styles = array()) 
	{
		$styles[] = 'thickbox';
		parent::print_styles($styles);
	}

//// Scripts ////

	public static function print_scripts() 
	{
		wp_register_script( 'mp-ajax-response',	'/' . MP_PATH . 'mp-includes/js/mp_ajax_response.js', array('jquery'), false, 1);
		wp_localize_script( 'mp-ajax-response', 	'wpAjax', array(
			'noPerm' => __('An unidentified error has occurred.'), 
			'broken' => __('An unidentified error has occurred.'), 
			'l10n_print_after' => 'try{convertEntities(wpAjax);}catch(e){};' 
		));

		wp_register_script( 'mp-lists', 		'/' . MP_PATH . 'mp-includes/js/mp_lists.js', array('mp-ajax-response'), false, 1);
		wp_localize_script( 'mp-lists', 		'wpListL10n', array( 
			'url' => MP_Action_url
		));

		wp_register_script( 'mp-thickbox', 		'/' . MP_PATH . 'mp-includes/js/mp_thickbox.js', array('thickbox'), false, 1);

		wp_register_script( self::screen, 		'/' . MP_PATH . 'mp-includes/js/mp_taxonomy.js', array('mp-lists', 'mp-thickbox'), false, 1);
		wp_localize_script( self::screen, 		'MP_AdminPageL10n', array(	
			'pending' => __('%i% pending'), 
			'screen' => self::screen,
			'list_id' => self::list_id,
			'add_form_id' => self::add_form_id,
			'tr_prefix_id' => self::tr_prefix_id,
			'l10n_print_after' => 'try{convertEntities(MP_AdminPageL10n);}catch(e){};' 
		));

		$scripts[] = self::screen;
		parent::print_scripts($scripts);
	}

//// Columns ////

	public static function get_columns() 
	{
		$columns = array(	'cb' 		=> "<input type='checkbox' />", 
					'name' 	=> __('Name', 'MailPress'), 
					'active'	=> __('Active', 'MailPress'), 
					'desc'	=> __('Description', 'MailPress'), 
					'event' 	=> __('Event', 'MailPress')
				);
		return $columns;
	}

//// List ////

	public static function get_list($page = 1, $pagesize = 20) 
	{
		$count = 0;
		$url_parms = self::get_url_parms(array('s', 'apage'));

		$args = (!empty($_GET['s'])) ? array('search' => $_GET['s']) : array();

		self::require_class('Autoresponders');
		$autoresponders = MP_Autoresponders::get_all($args);

		if (empty($autoresponders)) return false;

		echo self::_get_list($page, $pagesize, $count, $url_parms, $autoresponders);
	}

	public static function _get_list($page, $pagesize, &$count, $url_parms, $autoresponders)
	{
		$start = ($page - 1) * $pagesize;
		$end   = $start + $pagesize;

		$out = '';
		foreach( $autoresponders as $key => $autoresponder ) 
		{
			if ( $count >= $end ) break;

			if ( $count >= $start )
				$out .= "\t" . self::get_row( $autoresponder, $url_parms );

			unset( $autoresponders[$key] );

			$count++;
		}

		return $out;
	}

////  Row  ////

	public static function get_row( $autoresponder, $url_parms ) 
	{
		self::require_class('Autoresponders');
		include(MP_TMP . 'mp-admin/includes/options/autoresponders.php');

		static $row_class = '';

		$autoresponder = MP_Autoresponders::get( $autoresponder );

		$name = $autoresponder->name ;

// url's
		$url_parms['action'] 	= 'edit';
		$url_parms['id'] 	= $autoresponder->term_id;

		$edit_url = clean_url(self::url( MailPress_autoresponders, $url_parms ));
		$url_parms['action'] 	= 'delete';
		$delete_url = clean_url(self::url( MailPress_autoresponders, $url_parms, 'delete-autoresponder_' . $autoresponder->term_id ));
// actions
		$actions = array();
		$actions['edit'] = '<a href="' . $edit_url . '">' . __('Edit') . '</a>';
		$actions['delete'] = "<a class='delete:" . self::list_id . ":" . self::tr_prefix_id . "-" . $autoresponder->term_id . " submitdelete' href='$delete_url'>" . __('Delete') . "</a>";

		$row_class = 'alternate' == $row_class ? '' : 'alternate';

		$out = '';
		$out .= "<tr id='" . self::tr_prefix_id . "-$autoresponder->term_id' class='iedit $row_class'>";

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
					$out .= '<th scope="row" class="check-column"> <input type="checkbox" name="delete[]" value="' . $autoresponder->term_id . '" /></th>';
				break;
				case 'name':
					$out .= '<td ' . $attributes . '><strong><a class="row-title" href="' . $edit_url . '" title="' . attribute_escape(sprintf(__('Edit "%s"'), $name)) . '">' . $name . '</a></strong><br />';
					$out .= self::get_actions($actions);
					$out .= '</td>';
				break;
				case 'active':
					$x = (isset($autoresponder->description['active'])) ? __('Yes', 'MailPress') : __('No', 'MailPress');
					$out .= "<td $attributes>" . $x . "</td>";
				break;
				case 'desc':
					$out .= "<td $attributes>" . stripslashes($autoresponder->description['desc']) . "</td>";
				break;
				case 'event':
					$out .= "<td $attributes>" . $mp_autoresponder_registered_events[$autoresponder->description['event']]['desc'] . "</td>";
				break;
			}
		}
		$out .= '</tr>';

		return $out;
	}

//// Body ////

	public static function body()
	{
		include (MP_TMP . 'mp-admin/includes/autoresponders.php');
	}
}
?>