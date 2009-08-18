<?php
if (class_exists('MailPress'))
{
/*
Plugin Name: MailPress_sync_wordpress_user
Plugin URI: http://www.mailpress.org
Description: This is just an add-on for MailPress to synchronise with WordPress users
Author: Andre Renaut
Version: 4.0
Author URI: http://www.mailpress.org
*/

class MailPress_sync_wordpress_user
{
	function __construct()
	{
// WP user management
// for register form
		$settings = get_option('MailPress_sync_wordpress_user');
		if (isset($settings['register_form']))			add_action('register_form', array('MailPress_sync_wordpress_user', 'register_form'));

		add_action('user_register', 					array('MailPress_sync_wordpress_user', 'register'), 1, 1);
		add_action('profile_update', 					array('MailPress_sync_wordpress_user', 'update'), 1, 1);
		add_action('delete_user', 					array('MailPress_sync_wordpress_user', 'delete'), 1, 1);
// MP user management
		add_action('MailPress_insert_user', 			array('MailPress_sync_wordpress_user', 'mp_insert_user'), 1, 1);
		add_action('MailPress_delete_user', 			array('MailPress_sync_wordpress_user', 'mp_delete_user'), 1, 1);

// for wp admin
		if (is_admin())
		{
		// for admin plugin pages
			define ('MailPress_page_subscriptions', 	'mailpress_subscriptions');
		// for install
			add_action('activate_' . basename(dirname(__FILE__)) . '/MailPress_sync_wordpress_user.php', 	array('MailPress_sync_wordpress_user', 'install'));
		// for link on plugin page
			add_filter('plugin_action_links', 			array('MailPress_sync_wordpress_user', 'plugin_action_links'), 10, 2 );
		// for role & capabilities
			add_filter('MailPress_capabilities', 		array('MailPress_sync_wordpress_user', 'capabilities'), 1, 1);
			if (!class_exists('MailPress_roles_and_capabilities')) add_action('MailPress_roles_and_capabilities', 	array('MailPress_sync_wordpress_user', 'roles_and_capabilities'));
		// for settings
		// for settings subscriptions
			add_action('MailPress_settings_general_forms', 	array('MailPress_sync_wordpress_user', 'settings_general_forms'));

		// for profile update
			add_action('personal_options_update', 		array('MailPress_sync_wordpress_user', 'profile_update'), 8, 1);
			add_action('edit_user_profile_update', 		array('MailPress_sync_wordpress_user', 'profile_update'), 8, 1);

		// for load admin page
			add_action('MailPress_load_admin_page', 		array('MailPress_sync_wordpress_user', 'load_admin_page'), 10, 1);

		// for meta box in user page
			add_filter('MailPress_styles', 			array('MailPress_sync_wordpress_user', 'styles'), 8, 2);
			add_action('MailPress_add_meta_boxes_user', 	array('MailPress_sync_wordpress_user', 'meta_boxes_user'), 1, 2); 
		}
	}

//// All about sync ////
	public static function profile_update($wid)
	{
		$id = get_usermeta( $wid, '_MailPress_sync_wordpress_user' );
		if ($id)
		{
			MailPress::require_class('Users');
			$mp_user = MP_Users::get($id);
			if (stripslashes($_POST['display_name']) != $mp_user->name)
			{
				MP_Users::update_name($id, $_POST['display_name']);
			}
			if ($_POST['email'] != $mp_user->email)
			{
				$wp_user = get_userdata($wid);
				if (MailPress::is_email($_POST['email'])) self::sync($wp_user);
			}
		}
		else
		{
			$wp_user = get_userdata($wid);
			self::sync($wp_user);
		}
	}

	public static function sync($wp_user)
	{

 // Already a MailPress user ?

		$id = get_usermeta( $wp_user->ID, '_MailPress_sync_wordpress_user');
		MailPress::require_class('Users');
		if ($id)
		{
			if (MP_Users::get_email($id) == $wp_user->user_email) return true;
		}

// Mail already in MailPress table ?

		$id =  MP_Users::get_id_by_email($wp_user->user_email);
		if ($id) 
		{
			update_usermeta( $wp_user->ID, '_MailPress_sync_wordpress_user' , $id );
			MP_Users::set_status($id, 'active');
			return true;										  
		}

// so insert !

		return self::insert($wp_user);
	}

	public static function sync_comments($oldid, $newid)
	{
		global $wpdb;
		$query = "UPDATE $wpdb->postmeta SET meta_value = '$newid' WHERE meta_key = '_MailPress_subscribe_to_comments_' AND meta_value = '$oldid';";
		return $wpdb->query( $query );
	}

	public static function insert($wp_user, $type = 'activate')
	{
		if ( !MailPress::is_email($wp_user->user_email) )					return false; // not an email

		MailPress::require_class('Users');
		if ('activate' == $type) 
		{
		 	$key = md5(uniqid(rand(), 1));	
			if (!MP_Users::insert($wp_user->user_email, isset($wp_user->display_name) ? $wp_user->user_login : $wp_user->nice_name, $key, 'active'))	return false; // user not inserted
		}
		else
		{
			$return = MP_Users::add($wp_user->user_email, $wp_user->display_name);
			if (!$return['result']) 							return false; // user not inserted
		}
		$id = MP_Users::get_id_by_email($wp_user->user_email);
		update_usermeta( $wp_user->ID, '_MailPress_sync_wordpress_user' , $id );
		return true;
	}

// generic functions

	public static function get_wp_users()
	{
		global $wpdb;
		$query = "SELECT ID, user_email FROM $wpdb->users";
		return $wpdb->get_results( $query );
	}

	public static function get_wp_user($wp_user_id)
	{
		global $wpdb;
		return $wpdb->get_row($wpdb->prepare("SELECT ID, user_email FROM $wpdb->users WHERE ID = %d ", $wp_user_id));
	}

	public static function get_wp_users_by_mp_user_id($mp_user_id)
	{
		global $wpdb;
		$query = $wpdb->prepare("SELECT DISTINCT ID, user_email FROM $wpdb->users a, $wpdb->usermeta b WHERE a.ID = b.user_id AND b.meta_key = '_MailPress_sync_wordpress_user' AND b.meta_value = '%d'", $mp_user_id);
		return $wpdb->get_results( $query );
	}

	public static function get_wp_users_by_email($email)
	{
		global $wpdb;
		$email = trim($email);
		if (!MailPress::is_email($email)) return false;

		$query = "SELECT ID FROM $wpdb->users WHERE user_email = '$email'";
		return $wpdb->get_results( $query );
	}

	public static function count_emails($email)
	{
		global $wpdb;
		return $wpdb->get_var("SELECT count(*) FROM $wpdb->users WHERE user_email = '$email'");
	}

//// WP user management ////

	public static function register_form()
	{
		$checklist = MP_Newsletter::get_checklist();
		if ($checklist)
		{
?>
<p>
	<label>
		<?php _e('Newsletters', 'MailPress'); ?>
		<br />
		<span style='color:#777;font-weight:normal;'>
			<?php echo $checklist; ?>
		</span>
	</label>
</p>
<?php 
		}
		do_action('MailPress_register_form');
?>
	<br /><br />
<?php
	}

	public static function register($wp_user_id)
	{
		$wp_user = self::get_wp_user($wp_user_id);
		if ($wp_user) self::sync($wp_user);

		$settings = get_option('MailPress_sync_wordpress_user');
		if (isset($settings['register_form']))
		{
			$user 	= get_userdata($wp_user_id);
			$email 	= $user->user_email;
			MailPress::require_class('Users');
			$mp_user_id	= MP_Users::get_id_by_email($email);

			MP_Newsletter::update_checklist($mp_user_id);
		}
	}

	public static function update($wp_user_id)
	{
		$wp_user = self::get_wp_user($wp_user_id);
		if ($wp_user)
		{
			$oldid = get_usermeta( $wp_user->ID, '_MailPress_sync_wordpress_user');
			if ($oldid)
			{
				MailPress::require_class('Users');
				$oldemail = MP_Users::get_email($oldid);
				if ($oldemail == $wp_user->user_email) return true;
				else
				{
					self::sync($wp_user);
					$newid =  MP_Users::get_id_by_email($wp_user->user_email);

					if (MP_Users::has_subscribed_to_comments($oldid))	self::sync_comments($oldid, $newid);
					$count = self::count_emails($oldemail);
					if (0 == $count)							MP_Users::delete($oldid);
				}
			}
			else
			{
				self::register($wp_user_id);
			}
		}
	}

	public static function delete($wp_user_id)
	{
		$wp_user = self::get_wp_user($wp_user_id);
		if ($wp_user) 
		{
			$id = get_usermeta( $wp_user->ID, '_MailPress_sync_wordpress_user');
			if ($id)
			{
				MailPress::require_class('Users');
				$email = MP_Users::get_email($id);
				if ($email)
				{
					$count = self::count_emails($email);
					if ((1 == $count) && !MP_Users::has_subscribed_to_comments($id)) MP_Users::delete($id);
				}
			}
		}
		return true;
	}


//// MP user management ////

	public static function mp_insert_user($mp_user_id)
	{
		MailPress::require_class('Users');
		$mp_email	= MP_Users::get_email($mp_user_id);
		$wp_users  	= self::get_wp_users_by_email($mp_email);
		if (is_array($wp_users)) foreach ($wp_users as $wp_user) update_usermeta( $wp_user->ID, '_MailPress_sync_wordpress_user' , $mp_user_id);
	}

	public static function mp_delete_user($mp_user_id)
	{
		global $wpdb;
		$query = "DELETE FROM $wpdb->usermeta WHERE meta_key = '_MailPress_sync_wordpress_user' AND meta_value = '$mp_user_id';";
		$results = $wpdb->query( $query );
	}

////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////
////  ADMIN  ////

// for install
	public static function install()
	{
		$users = self::get_wp_users();
		if ($users) foreach($users as $user) self::sync($user);
	}

// for link on plugin page
	public static function plugin_action_links($links, $file)
	{
		return MailPress::plugin_links($links, $file, plugin_basename(__FILE__), '0');
	}

// for role & capabilities
	public static function capabilities($capabilities) 
	{
		$pu = ( current_user_can('edit_users') ) ? 'users.php' : 'profile.php';

		$capabilities['MailPress_manage_subscriptions'] = array(	'name'	=> __('Your Subscriptions', 'MailPress'), 
												'group'	=> 'admin', 
												'menu'	=> 33, 
	
												'parent'	=> $pu, 
												'page_title'=> __('MailPress - Subscriptions', 'MailPress'), 
												'menu_title'=> __('Your Subscriptions', 'MailPress'), 
												'page'	=> MailPress_page_subscriptions, 
												'func'	=> array('MP_AdminPage', 'body')
											);
		return $capabilities;
	}

	public static function roles_and_capabilities()
	{
		global $wp_roles;
		foreach($wp_roles->role_names as $role => $name)
		{
			if ('administrator' == $role) continue;
			$r = get_role($role);
			$r->add_cap('MailPress_manage_subscriptions');
		}
	}

// for settings subscriptions
	public static function settings_general_forms()
	{
		$sync_wordpress_user = get_option('MailPress_sync_wordpress_user');
?>
<tr valign='top'>
	<th scope='row'><?php _e('Allow subscriptions from', 'MailPress'); ?></th>
	<td>
		<label for='sync_wordpress_user_register_form'>
			<input type='hidden' name='sync_wordpress_user_on' value='on' />
			<input type='checkbox' name='sync_wordpress_user[register_form]' id='sync_wordpress_user_register_form'<?php if (isset($sync_wordpress_user['register_form'])) checked($sync_wordpress_user['register_form'], 'on'); ?> />&nbsp;&nbsp;<?php _e('Registration Form', 'MailPress'); ?><br />
		</label>
	</td>
</tr>
<?php
	}

// for load admin page
	public static function load_admin_page($page)
	{
		if ($page != MailPress_page_subscriptions) return;
		include (MP_TMP . 'mp-admin/subscriptions.php');
	}

// for meta box in user page
	public static function styles($styles, $screen) 
	{
		if ($screen != 'mailpress_user') return $styles;

		wp_register_style ( 'MailPress_sync_wordpress_user', '/' . MP_PATH . 'mp-admin/css/user_sync_wordpress_user.css' );

		$styles[] = 'MailPress_sync_wordpress_user';

		return $styles;
	}

	public static function meta_boxes_user($mp_user_id, $mp_screen)
	{
		add_meta_box('mp_user_syncwordpress', __('WP User sync', 'MailPress') , array('MailPress_sync_wordpress_user', 'meta_box'), MP_AdminPage::screen, 'normal', 'core');
	}

	public static function meta_box($mp_user)
	{
		$wp_users = self::get_wp_users_by_mp_user_id( $mp_user->id );
		if ($wp_users)
		{
?>
<div id="user-syncwordpress">
	<table class='form-table'>
<?php
			$header = true;
			foreach ($wp_users as $wp_user)
			{
				$wp_user = get_userdata($wp_user->ID);
				if (empty($wp_user->first_name) && empty($wp_user->last_name) && empty($wp_user->nickname)) continue;
?>
		<tr>
			<td style='border-bottom:none;padding:5px;' class='side-info-hide'>
				<label>
					<?php printf(__('WP User # %1$s', 'MailPress'), $wp_user->ID); ?>
				</label>
			</td>
			<td style='border-bottom:none;line-height:0.8em;padding:5px;'>
				<table>
<?php
			if ($header)
			{
				$header = false;
?>
					<tr>
						<td style='border-bottom:none;line-height:0.8em;padding:0px;' class='side-info-hide'>
<b><?php _e('First name') ?></b>
						</td>
						<td style='border-bottom:none;line-height:0.8em;padding:0px;' class='side-info-hide'>
<b><?php _e('Last name') ?></b>
						</td>
						<td style='border-bottom:none;line-height:0.8em;padding:0px;'>
<b><?php _e('Nickname') ?></b>
						</td>
					</tr>
<?php
			}
?>
					<tr>
						<td style='border-bottom:none;line-height:0.8em;padding:0px;' class='side-info-hide'>
							<input style='padding:3px;margin:0 10px 0 0;width:170px;' type='text' disabled='disabled' value="<?php if (isset($wp_user->first_name)) echo $wp_user->first_name; ?>" />
						</td>
						<td style='border-bottom:none;line-height:0.8em;padding:0px;' class='side-info-hide'>
							<input style='padding:3px;margin:0 10px 0 0;width:170px;' type='text' disabled='disabled' value="<?php if (isset($wp_user->ast_name)) echo $wp_user->last_name; ?>" />
						</td>
						<td style='border-bottom:none;line-height:0.8em;padding:0px;'>
							<input style='padding:3px;margin:0 10px 0 0;width:170px;' type='text' disabled='disabled' value="<?php if (isset($wp_user->nickname)) echo $wp_user->nickname; ?>" />
						</td>
					</tr>
				</table>
			</td>
		</tr>
<?php
		}
?>
	</table>
</div>
<?php
		}
		else 
			printf(__('%1$s is not a WordPress user', 'MailPress'), $mp_user->email);
	}
}

$MailPress_sync_wordpress_user = new MailPress_sync_wordpress_user();
}
?>