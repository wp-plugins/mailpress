<?php
MailPress::require_class('Admin_page_list');

class MP_AdminPage extends MP_Admin_page_list
{
	const screen 	= MailPress_page_mailinglists;
	const capability 	= 'MailPress_manage_mailinglists';
	const help_url	= 'http://www.mailpress.org/wiki/index.php?title=Add_ons:Mailinglist';

	const taxonomy 	= MailPress_mailinglist::taxonomy;

	const add_form_id = 'add';
	const list_id 	= 'the-list';
	const tr_prefix_id = 'mlnglst';

////  Redirect  ////

	public static function redirect() 
	{
		if ( isset($_POST['action']) )    $action = $_POST['action'];
		elseif ( isset($_GET['action']) ) $action = $_GET['action'];  
		if ( isset($_GET['deleteit']) )   $action = 'bulk-delete';
		if (!isset($action)) return;

		$url_parms = self::get_url_parms(array('s', 'apage', 'id'));

		self::require_class('Mailinglists');

		switch($action) 
		{
			case 'add':
				$e = MP_Mailinglists::insert($_POST);
				$url_parms['message'] = ( $e && !is_wp_error( $e ) ) ? 1 : 4;
				unset($url_parms['s']);
				self::mp_redirect( self::url(MailPress_mailinglists, $url_parms) );
			break;

			case 'delete':
				$id = $_GET['id'];
				if ( $id == get_option('MailPress_default_mailinglist') )
					wp_die(sprintf(__("Can&#8217;t delete the <strong>%s</strong> mailing list: this is the default one",'MailPress'), MP_Mailinglists::get_name($id)));

				MP_Mailinglists::delete($id);
				unset($url_parms['id']);

				$url_parms['message'] = 2;
				self::mp_redirect( self::url(MailPress_mailinglists, $url_parms) );
			break;

			case 'bulk-delete':
				foreach ( (array) $_GET['delete'] as $id) 
				{
					if ( $id == get_option('MailPress_default_mailinglist') )
						wp_die(sprintf(__("Can&#8217;t delete the <strong>%s</strong> mailing list: this is the default one",'MailPress'), MP_Mailinglists::get_name($id)));

					MP_Mailinglists::delete($id);
				}

				$url_parms['message'] = ( count($_GET['delete']) > 1) ? 6 : 2;
				self::mp_redirect( self::url(MailPress_mailinglists, $url_parms) );
			break;

			case 'edited':
				unset($_GET['action']);
				if (!isset($_POST['cancel'])) 
				{
					$e = MP_Mailinglists::insert($_POST);
					$url_parms['message'] = ( !is_wp_error($e) ) ? 3 : 5 ;
				}
				unset($url_parms['id']);
				self::mp_redirect( self::url(MailPress_mailinglists, $url_parms) );
			break;

			default:
				if ( !empty($_GET['_wp_http_referer']) ) 
					self::mp_redirect(remove_query_arg(array('_wp_http_referer', '_wpnonce'), stripslashes($_SERVER['REQUEST_URI'])));
			break;
		}
	}

////  Title  ////

	public static function title() { if (isset($_GET['id'])) { global $title; $title = __('Edit Mailinglist','MailPress'); } }

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

		wp_register_script( self::screen, 		'/' . MP_PATH . 'mp-includes/js/mp_taxonomy.js', array('mp-lists'), false, 1);
		wp_localize_script( self::screen, 		'MP_AdminPageL10n', array(
			'pending' => __('%i% pending'), 
			'screen'  => self::screen,
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
		$columns = array(	'cb'		=> '<input type="checkbox" />',
					'name' 	=> __('Name','MailPress'),
					'desc'	=> __('Description','MailPress'),
					'num' 	=> __('MailPress users','MailPress'));
		return $columns;
	}

//// List ////

	public static function get_list($page = 1, $pagesize = 20) 
	{
		$count = 0;
		$url_parms = self::get_url_parms(array('s', 'apage'));

		$args = (!empty($_GET['s'])) ? array('search' => $_GET['s']) : array();
		$args['hide_empty'] = 0;

		self::require_class('Mailinglists');
		$mailinglists = MP_Mailinglists::get_all($args);

		if (empty($mailinglists)) return false;

		$children = _get_term_hierarchy(self::taxonomy);

		echo self::_get_list($page, $pagesize, $count, $url_parms, $mailinglists, $children);
	}

	public static function _get_list($page, $pagesize, &$count, $url_parms, $mailinglists, &$children, $parent = 0, $level = 0)
	{
		$start = ($page - 1) * $pagesize;
		$end   = $start + $pagesize;

		$out = '';
		foreach ( $mailinglists as $key => $mailinglist ) 
		{
			if ( $count >= $end ) break;

			if ( $mailinglist->parent != $parent && empty($_GET['s']) ) continue;

			if ( $count == $start && $mailinglist->parent > 0 ) 
			{
				$my_parents = array();
				$my_parent = $mailinglist->parent;
				do 
				{
					$my_parent = MP_Mailinglists::get($my_parent);
					$my_parents[] = $my_parent;
					$my_parent = $my_parent->parent;
				}
				while ($my_parent);
				$num_parents = $plevel = count($my_parents);
				while( $my_parent = array_pop($my_parents) ) 
				{
					$out .= "\t" . self::get_row( $my_parent, $url_parms, $plevel - $num_parents );
					$num_parents--;
				}
				$out .= "\t" . self::get_row( $mailinglist, $url_parms, $plevel );
				unset( $mailinglists[$key] );
				$count++;

				continue;
			}

			if ( $count >= $start )
				$out .= "\t" . self::get_row( $mailinglist, $url_parms, $level );

			unset( $mailinglists[$key] );

			$count++;

			if ( isset($children[$mailinglist->term_id]) )
				$out .= self::_get_list($page, $pagesize, $count, $url_parms, $mailinglists, $children, $mailinglist->term_id, $level + 1 );
		}

		return $out;
	}

////  Row  ////

	public static function get_row( $mailinglist, $url_parms, $level, $name_override = false ) 
	{
		self::require_class('Mailinglists');

		static $row_class = '';

		$mailinglist = MP_Mailinglists::get( $mailinglist );

		$default_mailinglist_id = get_option( 'MailPress_default_mailinglist' );
		$pad = str_repeat( '&#8212; ', $level );
		$name = ( $name_override ) ? $name_override : $pad . ' ' . $mailinglist->name ;

// url's
		$url_parms['action'] = 'edit';
		$url_parms['id'] = $mailinglist->term_id;

		$edit_url = clean_url(self::url( MailPress_mailinglists, $url_parms ));
		$url_parms['action']	= 'delete';
		$delete_url = clean_url(self::url( MailPress_mailinglists, $url_parms,  'delete-mailinglist_' . $mailinglist->term_id ));
// actions
		$actions = array();
		$actions['edit'] = '<a href="' . $edit_url . '">' . __('Edit') . '</a>';
		$actions['delete'] = "<a class='delete:" . self::list_id . ":" . self::tr_prefix_id . "-" . $mailinglist->term_id . " submitdelete' href='$delete_url'>" . __('Delete') . "</a>";

		if ( $default_mailinglist_id == $mailinglist->term_id ) unset($actions['delete']);

		$row_class = 'alternate' == $row_class ? '' : 'alternate';

		$out = '';
		$out .= "<tr id='" . self::tr_prefix_id . "-$mailinglist->term_id' class='iedit $row_class'>";

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
					$out .= "<th scope='row' class='check-column'>";
					if ( $default_mailinglist_id != $mailinglist->term_id ) {
						$out .= "<input type='checkbox' name='delete[]' value='$mailinglist->term_id' />";
					} else {
						$out .= "&nbsp;";
					}
					$out .= '</th>';
				break;
				case 'name':
					$out .= '<td ' . $attributes . '><strong><a class="row-title" href="' . $edit_url . '" title="' . attribute_escape(sprintf(__('Edit "%s"'), $name)) . '">' . $name . '</a></strong><br />';
					$out .= self::get_actions($actions);
					$out .= '</td>';
	 			break;
	 			case 'desc':
	 				$out .= "<td $attributes>$mailinglist->description</td>";
	 			break;
				case 'num':
					$mailinglist->count = number_format_i18n( $mailinglist->count );

					if (current_user_can('MailPress_edit_users')) 
						$mp_users_count = ( $mailinglist->count > 0 ) ? "<a href='" . MailPress_users . "&amp;mailinglist=$mailinglist->term_id'>$mailinglist->count</a>" : $mailinglist->count;
					else
						$mp_users_count =  $mailinglist->count;

	 				$attributes = 'class="num column-num"' . $style;
					$out .= "<td $attributes>$mp_users_count</td>\n";
	 			break;
			}
		}
		$out .= '</tr>';

		return $out;
	}

//// Body ////

	public static function body()
	{
		include (MP_TMP . 'mp-admin/includes/mailinglists.php');
	}
}
?>