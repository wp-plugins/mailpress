<?php
if (class_exists('MailPress'))
{
/*
Plugin Name: MailPress_mailinglist
Plugin URI: http://www.mailpress.org
Description: This is just an addon for MailPress to manage mailing lists
Author: Andre Renaut
Version: 4.0
Author URI: http://www.mailpress.org
*/

class MailPress_mailinglist extends MP_abstract
{
	const taxonomy = 'MailPress_mailing_list';

	function __construct()
	{
// for taxonomy
		register_taxonomy(self::taxonomy, 'MailPress_user', array('hierarchical' => true , 'update_count_callback' => array('MailPress_mailinglist', 'update_count_callback')));

// for sending mails
		add_filter('MailPress_mailinglists', 	array('MailPress_mailinglist', 'mailinglists'), 8, 1);
		add_filter('MailPress_query_mailinglist', array('MailPress_mailinglist', 'query_mailinglist'), 8, 1);
// for shortcode
		add_filter('MailPress_form_defaults', 	array('MailPress_mailinglist', 'form_defaults'), 8, 1);
		add_filter('MailPress_form_options', 	array('MailPress_mailinglist', 'form_options'), 8, 1);
		add_filter('MailPress_form_submit', 	array('MailPress_mailinglist', 'form_submit'), 8, 2);
		add_action('MailPress_form', 		  	array('MailPress_mailinglist', 'form'), 1, 2); 
// register form && registering user
		add_action('MailPress_register_form', 	array('MailPress_mailinglist', 'register_form'), 1); 
		add_action('user_register', 			array('MailPress_mailinglist', 'register'), 10, 1);
// for mp_user in mailinglists
		add_action('MailPress_insert_user', 	array('MailPress_mailinglist', 'set_user_mailinglists'), 1, 1);
		add_action('MailPress_delete_user', 	array('MailPress_mailinglist', 'delete_user'), 1, 1);

// for wp admin
		if (is_admin())
		{
		// for plugin
			add_action('activate_' . MP_FOLDER . '/add_ons/MailPress_mailinglist.php', 	array('MailPress_mailinglist', 'install'));
		// for admin plugin pages
			define ('MailPress_page_mailinglists', 	'mailpress_mailinglists');
		// for admin plugin urls
			$file = 'admin.php';
			define ('MailPress_mailinglists', $file . '?page=' . MailPress_page_mailinglists);
		// for link on plugin page
			add_filter('plugin_action_links', 			array('MailPress_mailinglist', 'plugin_action_links'), 10, 2 );
		// for role & capabilities
			add_filter('MailPress_capabilities', 		array('MailPress_mailinglist', 'capabilities'), 1, 1);
		// for settings general
			add_action('MailPress_settings_general', 		array('MailPress_mailinglist', 'settings_general'));
		// for settings subscriptions
			add_action('MailPress_settings_subscriptions', 	array('MailPress_mailinglist', 'settings_subscriptions'));

		// for load admin page
			add_action('MailPress_load_admin_page', 		array('MailPress_mailinglist', 'load_admin_page'), 10, 1);
			// for ajax
			add_action('mp_action_add_mlnglst', 		array('MailPress_mailinglist', 'mp_action_add_mlnglst'));
			add_action('mp_action_delete_mlnglst', 		array('MailPress_mailinglist', 'mp_action_delete_mlnglst'));

		// for mp_users list
			add_action('MailPress_restrict_users', 		array('MailPress_mailinglist', 'restrict_users'), 1, 1);
			add_filter('MailPress_columns_users', 		array('MailPress_mailinglist', 'columns_users'), 1, 1);
			add_action('MailPress_get_row_users', 		array('MailPress_mailinglist', 'get_row_users'), 1, 3);

		// for meta box in user page
			add_action('MailPress_redirect', 			array('MailPress_mailinglist', 'redirect'), 8, 1);
			add_filter('MailPress_styles', 			array('MailPress_mailinglist', 'styles'), 8, 2);
			add_filter('MailPress_scripts', 			array('MailPress_mailinglist', 'scripts'), 8, 2);
			add_action('MailPress_add_meta_boxes_user', 	array('MailPress_mailinglist', 'meta_boxes_user'), 8, 2);
			// for ajax
			add_action('mp_action_add_mailinglist', 		array('MailPress_mailinglist', 'mp_action_add_mailinglist'));
		}
	}

//// Taxonomy ////

	public static function update_count_callback( $mailinglists )
	{
		global $wpdb;

		foreach ( $mailinglists as $mailinglist ) 
		{
			$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_taxonomy a, $wpdb->term_relationships b, $wpdb->mp_users c WHERE a.taxonomy = '" . self::taxonomy . "' AND  a.term_taxonomy_id = b.term_taxonomy_id AND a.term_taxonomy_id = %d AND c.id = b.object_id ", $mailinglist ) );
			$wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $mailinglist ) );
		}
	}

//// Sending Mails ////

	public static function mailinglists( $draft_dest = array() ) 
	{
		self::require_class('Mailinglists');
		$args = array('hide_empty' => 0, 'hierarchical' => true, 'show_count' => 0, 'orderby' => 'name', 'name' => 'default_mailinglist' );
		foreach (MP_Mailinglists::array_tree($args) as $k => $v) $draft_dest[$k] = $v;
		return $draft_dest;
	}

	public static function query_mailinglist( $draft_toemail ) 
	{
		self::require_class('Mailinglists');

		$x = str_replace('MailPress_mailinglist~', '', $draft_toemail, $count);
		if (0 == $count) return false;
		if (empty($x)) return false;

		$y = MP_Mailinglists::get_children($x, ', ', '');
		$x = ('' == $y) ? ' = ' . $x : ' IN (' . $x . $y . ') ';

		if (empty($x)) return false;

		global $wpdb;
		$query = "SELECT DISTINCT c.id, c.email, c.name, c.status, c.confkey FROM $wpdb->term_taxonomy a, $wpdb->term_relationships b, $wpdb->mp_users c WHERE a.taxonomy = '" . self::taxonomy . "' AND  a.term_taxonomy_id = b.term_taxonomy_id AND a.term_id $x AND c.id = b.object_id AND c.status = 'active' ";

		return $query;
	}

//// Shortcode ////

	public static function form_defaults($x) { $x['mailinglist'] = false; return $x; }

	public static function form_options($x)  { return $x; }

	public static function form_submit($shortcode_message, $email) 
	{ 
		if (!isset($_POST['mailinglist'])) return $shortcode_message;

		self::require_class('Users');
		self::require_class('Mailinglists');

		$mp_user_id = MP_Users::get_id_by_email($email);
		$mailinglist_ID = $_POST['mailinglist'];
		$mp_user_mls = MP_Mailinglists::get_object_terms($mp_user_id);

		if (in_array($mailinglist_ID, $mp_user_mls))
			return $shortcode_message . __('<br />already in Mailing list!', 'MailPress');

		array_push($mp_user_mls, $mailinglist_ID);
		MP_Mailinglists::set_object_terms( $mp_user_id, $mp_user_mls);
		return $shortcode_message . __('<br />Mailing list added', 'MailPress');
	}

	public static function form($email, $options)  
	{
		if (!$options['mailinglist']) return;

		echo "<input type='hidden' name='mailinglist' value='" . $options['mailinglist'] . "' />\n";
	}

//// Register ////

	public static function register_form()
	{
		$checklist_mailinglists = self::get_checklist();
		if ($checklist_mailinglists)
		{
?>
	<br />
	<p>
		<label>
			<?php _e('Mailing lists', 'MailPress'); ?>
			<br />
			<span style='color:#777;font-weight:normal;'>
				<?php echo $checklist_mailinglists; ?>
			</span>
		</label>
	</p>
<?php 
		}
	}

	public static function register($wp_user_id)
	{
		self::require_class('Users');

		$user 	= get_userdata($wp_user_id);
		$email 	= $user->user_email;
		$mp_user_id	= MP_Users::get_id_by_email($email);

		self::update_checklist($mp_user_id);
	}

//// Mp_user in mailinglists ////

	public static function set_user_mailinglists( $mp_user_id, $user_mailinglists = array() )
	{
		self::require_class('Mailinglists');
		MP_Mailinglists::set_object_terms( $mp_user_id, $user_mailinglists );
	}

	public static function delete_user( $mp_user_id )
	{
		self::require_class('Mailinglists');
		MP_Mailinglists::delete_object( $mp_user_id );

	}

//// Subscriptions ////

	public static function get_checklist($mp_user_id = false, $args = '') 
	{
		global $mp_subscriptions;
		if (!isset($mp_subscriptions['display_mailinglists'])) return false;
		if ($mp_subscriptions['display_mailinglists'] == array()) return false;

		$checklist = '';
		$defaults = array (	'name' 	=> 'keep_mailinglists', 
						'echo' 	=> 1, 
						'selected' 	=> false, 
						'type'	=> 'checkbox', 
						'show_option_all' => false,
						'htmlstart'	=> '', 
						'htmlmiddle'=> '&nbsp;&nbsp;', 
						'htmlend'	=> "<br />\n"
					);
		$r = wp_parse_args( $args, $defaults );
		extract( $r );

		if ($mp_user_id)
		{
			self::require_class('Mailinglists');
			$mp_user_mls = MP_Mailinglists::get_object_terms($mp_user_id);
		}

		$default_mailing_list = get_option('MailPress_default_mailinglist');

		$mls = array();
		$mailinglists = apply_filters('MailPress_mailinglists', array());
		foreach ($mailinglists as $k => $v) 
		{
			$x = str_replace('MailPress_mailinglist~', '', $k, $count);
			if (0 == $count) 	continue;	
			if (empty($x)) 	continue;
			if ($x == $default_mailing_list) 	continue;
			$mls[$x] = $v;
		}

		foreach ($mls as $k => $v)
		{
			switch ($type)
			{
				case 'checkbox' :
					$checked = '';
					$typ2 = $type; 

					if (!$mp_user_id)
					{
						if (!isset($mp_subscriptions['display_mailinglists'][$k])) continue;
					}
					else
					{
						if (!in_array($k, $mp_user_mls))
						{
							if (!isset($mp_subscriptions['display_mailinglists'][$k])) continue;
						}
						else
						{
							$typ2    = (!isset($mp_subscriptions['display_mailinglists'][$k])) ? 'hidden' : $typ2;
							if ('checkbox' == $typ2) $checked =  " checked='checked'";
						}
					}

					$htmlstart2  = ('checkbox' == $typ2) ? $htmlstart  : '';
					$htmlmiddle2 = ('checkbox' == $typ2) ? $htmlmiddle . str_replace('&nbsp;', '', $v) : "<!-- " . str_replace('&nbsp;', '', $v) . "-->";
					$htmlend2    = ('checkbox' == $typ2) ? $htmlend    : "\n";

					$checklist .= $htmlstart2 . "<input type='$typ2' name='" . $name . "[$k]'$checked />" . $htmlmiddle2 . $htmlend2;
				break;
				case 'select' :
					if (!isset($mp_subscriptions['display_mailinglists'][$k])) continue;

					if (empty($checklist)) $checklist = "\n" . $htmlstart . "\n<select name='" . $name . "'>\n";
					if ($show_option_all)
					{
						$checklist .= "<option value=''>" . $show_option_all . "</option>\n";
						$show_option_all = false;
					}
					$sel = ($k == $selected) ? " selected='selected'" : '';
					$checklist .= "<option value=\"$k\"$sel>" . str_replace('&nbsp;', '', $v) . "</option>\n";
				break;
			}
		}
		if ('select' == $type) $checklist .= "</select>\n" . $htmlend . "\n";
		return $checklist;
	}

	public static function update_checklist($mp_user_id, $name = 'keep_mailinglists') 
	{
		global $mp_subscriptions;
		if (!isset($mp_subscriptions['display_mailinglists'])) return true;
		if ($mp_subscriptions['display_mailinglists'] == array()) return true;

		$mp_user_mls = array();

		if (isset($_POST[$name]))
		{
			foreach ($_POST[$name] as $mailinglist_ID => $v)
			{
				array_push($mp_user_mls, $mailinglist_ID);
			}
		}
		self::require_class('Mailinglists');
		MP_Mailinglists::set_object_terms( $mp_user_id, $mp_user_mls);
	}

////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////

// for install
	public static function install()
	{
		global $wpdb;
		if (!get_option('MailPress_default_mailinglist'))
		{
	// Default mailing list
			$name = $wpdb->escape(__('Uncategorized', 'MailPress'));
			$slug = sanitize_title(sanitize_term_field('slug', _c('Uncategorized', 'MailPress'), 0, self::taxonomy, 'db'));
			$wpdb->query("INSERT INTO $wpdb->terms (name, slug, term_group) VALUES ('$name', '$slug', '0')");
			$term_id = $wpdb->get_var("SELECT term_id FROM $wpdb->terms WHERE slug = '$slug' ");
			$wpdb->query("INSERT INTO $wpdb->term_taxonomy (term_id, taxonomy, description, parent, count) VALUES ($term_id, '" . self::taxonomy . "', '', '0', '0')");
			add_option('MailPress_default_mailinglist', $term_id );
		}

	// Synchronize
		$default_mailinglist	= get_option('MailPress_default_mailinglist');
		$query = "SELECT DISTINCT a.id FROM $wpdb->mp_users a WHERE NOT EXISTS (SELECT DISTINCT b.id FROM $wpdb->term_taxonomy c, $wpdb->term_relationships d, $wpdb->mp_users b WHERE c.taxonomy = '" . self::taxonomy . "' AND  c.term_taxonomy_id = d.term_taxonomy_id AND b.id = d.object_id AND b.id = a.id)";
		$unmatches = $wpdb->get_results($query);
		if ($unmatches) foreach ($unmatches as $unmatch)
		{
			self::require_class('Mailinglists');
			MP_Mailinglists::set_object_terms($unmatch->id, array($default_mailinglist));
		}
	}

// for link on plugin page
	public static function plugin_action_links($links, $file)
	{
		if ($file != plugin_basename(__FILE__)) return $links;

		$settings_link = '<a href="' . MailPress_settings . '#fragment-MailPress_mailinglist">' . __('Settings') . '</a>';
		array_unshift ($links, $settings_link);
		return $links;
	}

// for role & capabilities
	public static function capabilities($capabilities) 
	{
		$capabilities['MailPress_manage_mailinglists'] = array(	'name'  => __('Mailing lists', 'MailPress'), 
											'group' => 'users', 
											'menu'  => 35, 

											'parent'		=> false, 
											'page_title'	=> __('MailPress Mailing lists', 'MailPress'), 
											'menu_title'   	=> __('Mailing lists', 'MailPress'), 
											'page'  		=> MailPress_page_mailinglists, 
											'func'  		=> array('MP_AdminPage', 'body')
										);
		return $capabilities;
	}

// for settings
// for settings general
	public static function settings_general()
	{
		self::require_class('Mailinglists');
		$default_mailinglist	= get_option('MailPress_default_mailinglist');
?>
<tr><th></th><td></td></tr>
<tr valign='top' class="mp_sep">
	<th style='padding:0;'><strong><?php _e('Mailing lists', 'MailPress'); ?></strong></th>
	<td class='field'>
		<input type='hidden' name='default_mailinglist_on' value='on' />
<?php
		$dropdown_options = array('hide_empty' => 0, 'hierarchical' => true, 'show_count' => 0, 'orderby' => 'name', 'selected' => $default_mailinglist, 'name' => 'default_mailinglist' );
		MP_Mailinglists::dropdown($dropdown_options);
?>&nbsp;<?php _e('Default Mailing list', 'MailPress'); ?>
	</td>
</tr>
<?php
	}

// for settings subscriptions
	public static function settings_subscriptions()
	{
		global $mp_subscriptions;
?>
<tr><th></th><td colspan='4'></td></tr>
<tr valign='top'>
	<th style='padding:0;'><strong><?php _e('Mailing lists', 'MailPress'); ?></strong></th>
	<td style='padding:0;' colspan='4'></td>
</tr>
<tr valign='top' class="rc_role">
	<th scope='row'><?php _e('Allow subscriptions to', 'MailPress'); ?></th>
	<td colspan='4'>
		<table id='mailinglists' class='general'>
			<tr>
				<td style='width:50px;vertical-align:top;'>&nbsp;</td>
				<td>
<?php
		$default_mailing_list = get_option('MailPress_default_mailinglist');

		$mls = array();
		$mailinglists = apply_filters('MailPress_mailinglists', array());

		if (empty($mailinglists))
        {
				_e('You need to create at least one mailinglist.', 'MailPress');
        }
		else
        {
?>
					<input type='hidden'   name='mailinglist[on]' value='on' />
<?php
			foreach ($mailinglists as $k => $v) 
			{
				$x = str_replace('MailPress_mailinglist~', '', $k, $count);
				if (0 == $count) 	continue;	
				if (empty($x)) 	continue;
				if ($x == $default_mailing_list) 	continue;
				$mls[$x] = $v;
			}
	
			foreach ($mls as $k => $v)
			{
?>
					<label for='subscriptions_display_mailinglists_<?php echo $k; ?>'><input type='checkbox' id='subscriptions_display_mailinglists_<?php echo $k; ?>' name='subscriptions[display_mailinglists][<?php echo $k; ?>]'<?php if (isset($mp_subscriptions['display_mailinglists'][$k])) checked($mp_subscriptions['display_mailinglists'][$k], 'on'); ?> />&nbsp;&nbsp;<?php echo $v; ?></label><br />
<?php
			}
        }
?>
				</td>
			</tr>
		</table>
	</td>
</tr>
<?php
	}

// for load admin page
	public static function load_admin_page($page)
	{
		if ($page != MailPress_page_mailinglists) return;
		include (MP_TMP . 'mp-admin/mailinglists.php');
	}
	//¤ for ajax	
	public static function mp_action_delete_mlnglst() 
	{
		self::require_class('Mailinglists');
		$id = isset($_POST['id'])? (int) $_POST['id'] : 0;
		die( MP_Mailinglists::delete($id) ? '1' : '0' );
	}

	public static function mp_action_add_mlnglst()
	{
		if (!current_user_can('MailPress_manage_mailinglists')) die('-1');

		if ( '' === trim($_POST['name']) ) 
		{
			$x = new WP_Ajax_Response( array(	'what' => 'mailinglist', 
									'id' => new WP_Error( 'mailinglist_name', __('You did not enter a valid mailing list name.', 'MailPress') )
								   ) );
			$x->send();
		}

		self::require_class('Mailinglists');
		if ( MP_Mailinglists::exists( trim( $_POST['name'] ) ) ) 
		{
			$x = new WP_Ajax_Response( array(	'what' => 'mailinglist', 
									'id' => new WP_Error( __CLASS__ . '::exists', __('The mailing list you are trying to create already exists.', 'MailPress'), array( 'form-field' => 'name' ) ), 
								  ) );
			$x->send();
		}
	
		$mailinglist = MP_Mailinglists::insert( $_POST, true );

		if ( is_wp_error($mailinglist) ) 
		{
			$x = new WP_Ajax_Response( array(	'what' => 'mailinglist', 
									'id' => $mailinglist
								  ) );
			$x->send();
		}

		if ( !$mailinglist || (!$mailinglist = MP_Mailinglists::get( $mailinglist )) ) 	die('0');

		$level 			= 0;
		$mailinglist_full_name 	= $mailinglist->name;
		$_mailinglist 		= $mailinglist;
		while ( $_mailinglist->parent ) 
		{
			$_mailinglist 		= MP_Mailinglists::get( $_mailinglist->parent );
			$mailinglist_full_name 	= $_mailinglist->name . ' &#8212; ' . $mailinglist_full_name;
			$level++;
		}
		$mailinglist_full_name = attribute_escape($mailinglist_full_name);

		include (MP_TMP . 'mp-admin/mailinglists.php');
		$x = new WP_Ajax_Response( array(	'what' => 'mailinglist', 
								'id' => $mailinglist->term_id, 
								'data' => MP_AdminPage::get_row( $mailinglist, array(), $level, $mailinglist_full_name ), 
								'supplemental' => array('name' => $mailinglist_full_name, 'show-link' => sprintf(__( 'Mailing list <a href="#%s">%s</a> added' , 'MailPress'), "mailinglist-$mailinglist->term_id", $mailinglist_full_name))
							  ) );
		$x->send();
		break;
	}

	public static function mp_action_add_mailinglist()
	{
		self::require_class('Mailinglists');
		$names = explode(',', $_POST['newmailinglist']);
		$parent = (int) $_POST['newmailinglist_parent'];
		if ($parent < 0) $parent = 0;

		$all_mailinglists_ids = isset($_POST['mp_user_mailinglist']) ? (array) $_POST['mp_user_mailinglist'] : array();

		$most_used_ids = (isset( $_POST['popular_ids'] )) ? explode( ',', $_POST['popular_ids'] ) : false;

		$x = new WP_Ajax_Response();
		foreach ( $names as $name )
		{
			$name = trim($name);
			$id = MP_Mailinglists::create( $name, $parent );
			$all_mailinglists_ids[] = $id;
			if ( $parent ) continue;										// Do these all at once in a second
			$mailinglist = MP_Mailinglists::get( $id );
			ob_start();
				self::all_mailinglists_checkboxes( 0, $id, $all_mailinglists_ids, $most_used_ids );
				$data = ob_get_contents();
			ob_end_clean();
			$x->add( array(	'what' => 'mailinglist', 
						'id'   => $id, 
						'data' => $data, 
						'position' => -1
					  ) );
		}
		if ( $parent ) 
		{ 									// Foncy - replace the parent and all its children
			ob_start();
				self::all_mailinglists_checkboxes( 0, $parent, $all_mailinglists_ids, $most_used_ids );
				$data = ob_get_contents();
			ob_end_clean();
			$x->add( array(	'what' => 'mailinglist', 
						'id'   => $parent, 
						'old_id' => $parent, 
						'data' => $data, 
						'position' => -1
					  ) );
		}
		$x->send();
	}

// for mp_users list
	public static function restrict_users($url_parms)
	{
		self::require_class('Mailinglists');
		$x = (isset($url_parms['mailinglist'])) ? $url_parms['mailinglist'] : '';
		$dropdown_options = array('show_option_all' => __('View all mailing lists', 'MailPress'), 'hide_empty' => 0, 'hierarchical' => true, 'show_count' => 0, 'orderby' => 'name', 'selected' => $x );
		MP_Mailinglists::dropdown($dropdown_options);
		echo "<input type='submit' id='mailinglistsub' value=\"" . __('Filter', 'MailPress') . "\" class='button-secondary' />";
	}

	public static function columns_users($x)
	{
		$date = array_pop($x);
		$x['mailinglists']=  __('Mailing lists','MailPress');
		$x['date']		= $date;
		return $x;
	}

	public static function get_row_users($column_name, $mp_user, $url_parms)
	{
		if ('mailinglists' != $column_name) return;

		self::require_class('Mailinglists');

		$args = array('orderby' => 'name', 'order' => 'ASC', 'fields' => 'all');
		$mp_user_mls = MP_Mailinglists::get_object_terms( $mp_user->id, $args);

		if ( !empty( $mp_user_mls ) ) 
		{
			$out = array();
			foreach ( $mp_user_mls as $m )
				$out[] = "<a href='" . MailPress_users . "&amp;mailinglist=$m->term_id'>" . wp_specialchars(sanitize_term_field('name', $m->name, $m->term_id, self::taxonomy, 'display')) . "</a>";
			echo join( ', ', $out );
		}
		else
		{
			_e('Uncategorized ', 'MailPress');
		}
	}

// for meta box in user page
	public static function redirect($screen) 
	{
		if ('mailpress_user' != $screen) return;

		if (!isset($_POST['id'])) return;
		if (!isset($_POST['mp_user_mailinglist'])) $_POST['mp_user_mailinglist'] = array();

		self::require_class('Mailinglists');

		MP_Mailinglists::set_object_terms($_POST['id'], $_POST['mp_user_mailinglist']);
	}

	public static function styles($styles, $screen) 
	{
		if ('mailpress_user' != $screen) return $styles;

		wp_register_style ( 'mp-user-mailinglists', get_option('siteurl') . '/' . MP_PATH . 'mp-admin/css/user_mailinglists.css' );

		$styles[] = 'mp-user-mailinglists';

		return $styles;
	}

	public static function scripts($scripts, $screen) 
	{
		if ('mailpress_user' != $screen) return $scripts;

		wp_register_script( 'mp-user-mailinglists', '/' . MP_PATH . 'mp-admin/js/user_mailinglists.js', array('mp-lists'), false, 1);

		$scripts[] = 'mp-user-mailinglists';

		return $scripts;
	}

	public static function meta_boxes_user($mp_user_id, $screen)
	{
		if (current_user_can('MailPress_manage_mailinglists'))
			add_meta_box('mailinglistdiv', __('Mailing lists', 'MailPress'), array('MailPress_mailinglist', 'meta_box'), $screen, 'side', 'core');
	}

	public static function meta_box($mp_user)
	{ 
		self::require_class('Mailinglists', MP_TMP);
?>
<ul id="mailinglist-tabs">
	<li class="tabs">
		<a href="#mailinglists-all" tabindex="3">
			<?php _e( 'All Mailing lists', 'MailPress'  ); ?>
		</a>
	</li>
	<li class="hide-if-no-js">
		<a href="#mailinglists-pop" tabindex="3">
			<?php _e( 'Most Used' , 'MailPress' ); ?>
		</a>
	</li>
</ul>

<div id="mailinglists-pop" class="tabs-panel" style="display: none;">
	<ul id="mailinglistchecklist-pop" class="mailinglistchecklist form-no-clear" >
<?php $most_used_ids = self::most_used_checkboxes(self::taxonomy, $mp_user->id); ?>
	</ul>
</div>
<div id="mailinglists-all" class="tabs-panel">
	<ul id="mailinglistchecklist" class="list:mailinglist mailinglistchecklist form-no-clear">
		<?php self::all_mailinglists_checkboxes($mp_user->id, false, false, $most_used_ids) ?>
	</ul>
</div>
<div id="mailinglist-adder" class="wp-hidden-children">
	<h4>
		<a id="mailinglist-add-toggle" href="#mailinglist-add" class="hide-if-no-js" tabindex="3">
			<?php _e( '+ Add New mailing list' , 'MailPress'); ?>
		</a>
	</h4>
	<p id="mailinglist-add" class="wp-hidden-child">
		<label class="screen-reader-text" for="newcat">
			<?php _e( 'Add New mailinglist' ); ?>
		</label>
		<input type="text" name="newmailinglist" id="newmailinglist" class="form-required form-input-tip" value="<?php esc_attr_e( 'New mailing list', 'MailPress' ); ?>" tabindex="3" aria-required="true"/>
		<label class="screen-reader-text" for="newmailinglist_parent">
			<?php _e('Parent Mailing list', 'MailPress'); ?> :
		</label>
		<?php MP_Mailinglists::dropdown( array( 'hide_empty' => 0, 'name' => 'newmailinglist_parent', 'orderby' => 'name', 'hierarchical' => 1, 'show_option_none' => __('Parent Mailing list', 'MailPress'), 'tab_index' => 3 ) ); ?>
		<input type="button" id="mailinglist-add-submit" class="add:mailinglistchecklist:mailinglist-add button" value="<?php esc_attr_e( 'Add', 'MailPress'  ); ?>" tabindex="3" />
<?php	wp_nonce_field( 'add-mailinglist', '_ajax_nonce', false ); ?>
		<span id="mailinglist-ajax-response"></span>
	</p>
</div>
<?php
	}

	public static function most_used_checkboxes( $taxonomy, $mp_user_id, $default = 0, $number = 10, $echo = true ) 
	{
		$mailinglists = get_terms( $taxonomy, array( 'orderby' => 'count', 'order' => 'DESC', 'number' => $number, 'hierarchical' => false ) );

		$most_used_ids = array();
		foreach ( (array) $mailinglists as $mailinglist ) 
		{
			$most_used_ids[] = $mailinglist->term_id;
			if ( !$echo ) // hack for AJAX use
				continue;
			$id = "popular-mailinglist-$mailinglist->term_id";
?>
		<li id="<?php echo $id; ?>" class="popular-mailinglist">
			<label class="selectit" for="in-<?php echo $id; ?>">
			<input id="in-<?php echo $id; ?>" type="checkbox" value="<?php echo (int) $mailinglist->term_id; ?>" />
				<?php echo wp_specialchars( $mailinglist->name ); ?>
			</label>
		</li>
<?php
		}
		return $most_used_ids;
	}

	public static function all_mailinglists_checkboxes( $mp_user_id = 0, $descendants_and_self = 0, $selected_mailinglists = false, $popular_mailinglists = false ) 
	{
		self::require_class('Mailinglists_Walker_Checklist');
		$walker = new MP_Mailinglists_Walker_Checklist;

		self::require_class('Mailinglists');

		$descendants_and_self = (int) $descendants_and_self;
		$args = array();

		if ( is_array( $selected_mailinglists ) )
			$args['selected_mailinglists'] = $selected_mailinglists;
		elseif ( $mp_user_id )
			$args['selected_mailinglists'] = MP_Mailinglists::get_object_terms($mp_user_id);
		else
			$args['selected_mailinglists'] = array();

		if ( is_array( $popular_mailinglists ) )
			$args['popular_mailinglists'] = $popular_mailinglists;
		else
			$args['popular_mailinglists'] = MP_Mailinglists::get_all( array( 'fields' => 'ids', 'orderby' => 'count', 'order' => 'DESC', 'number' => 10, 'hierarchical' => false ) );

		if ( $descendants_and_self ) 
		{
			$mailinglists = MP_Mailinglists::get_all( array('child_of' => $descendants_and_self, 'hierarchical' => 0, 'hide_empty' => 0) );
			$self = MP_Mailinglists::get( $descendants_and_self );
			array_unshift( $mailinglists, $self );
		}
		else
		{
			$mailinglists = MP_Mailinglists::get_all( array('get' => 'all') );
		}

		$all_mailinglists_ids = array();
		$keys = array_keys( $mailinglists );

		foreach( $keys as $k )
		{
			if ( in_array($mailinglists[$k]->term_id, $args['selected_mailinglists']) )
			{
				$all_mailinglists_ids[] = $mailinglists[$k];
				unset($mailinglists[$k]);
			}
		}

		$args['input_name'] = 'mp_user_mailinglist[]';
	// Put checked mailinglists on top
		echo call_user_func_array(array(&$walker, 'walk'), array($all_mailinglists_ids, 0, $args));
	// Then the rest of them
		echo call_user_func_array(array(&$walker, 'walk'), array($mailinglists, 0, $args));
	}
}

$MailPress_mailinglist = new MailPress_mailinglist();
}
?>