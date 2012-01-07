<?php
if (class_exists('MailPress') && !class_exists('MailPress_user_role_mailinglist') )
{
/*
Plugin Name: MailPress_user_role_mailinglist
Description: This is just an add-on for MailPress to add mailing lists based on wp user roles for new mail admin screen
Version: 5.2.1
*/

class MailPress_user_role_mailinglist
{
	function __construct()
	{
// for sending mails
		add_filter('MailPress_mailinglists_optgroup', 	array(__CLASS__, 'mailinglists_optgroup'), 8, 2);
		add_filter('MailPress_mailinglists', 			array(__CLASS__, 'mailinglists'), 8, 1);
		add_filter('MailPress_query_mailinglist', 		array(__CLASS__, 'query_mailinglist'), 8, 2);
	}

//// Sending Mails ////

	public static function mailinglists_optgroup( $label, $optgroup ) 
	{
		if (__CLASS__ == $optgroup) return __('WP User Roles', MP_TXTDOM);
		return $label;
	}

	public static function mailinglists( $draft_dest = array() ) 
	{
		global $wpdb, $wp_roles;

		$query = "SELECT COUNT(*) FROM $wpdb->usermeta WHERE meta_key = '" . $wpdb->get_blog_prefix(get_current_blog_id()) . "capabilities' AND meta_value LIKE '%%%s%%'";

		foreach ( $wp_roles->get_names() as $role => $name )
			if ( $wpdb->get_var( sprintf( $query, like_escape( $role ) ) ) )
				$draft_dest[__CLASS__ . '~' . $role] = sprintf( __('To all "%1$s"', MP_TXTDOM), translate_user_role( $name ) );

		return $draft_dest;
	}

	public static function query_mailinglist( $query, $draft_toemail ) 
	{
		if ($query) return $query;

		$role = str_replace(__CLASS__ . '~', '', $draft_toemail, $count);
		if (0 == $count) return $query;
		if (empty($role))  return $query;

		$users = array();
		$results = get_users( array( 'role' => $role, 'fields' => array('user_email') ) );
                foreach ($results as $result) $users[] = $result->user_email;
		if (empty($users)) return $query;

		global $wpdb;
		return "SELECT DISTINCT c.id, c.email, c.name, c.status, c.confkey FROM $wpdb->mp_users c WHERE c.email IN ('" . join("', '", $users) . "') AND c.status = 'active' ";
	}
}
new MailPress_user_role_mailinglist();
}