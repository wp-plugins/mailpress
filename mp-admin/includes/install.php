<?php
//
//
//	New install
//
		global $wpdb;

		add_option ('MailPress_daily',  		array('threshold'=>date('Ymd')));
		add_option ('MailPress_weekly', 		array('threshold'=>MP_Newsletter::get_yearweekofday(date('Y-m-d'))));
		add_option ('MailPress_monthly', 		array('threshold'=>date('Ym')));

		add_option ('MailPress_template', 		'default');
		add_option ('MailPress_stylesheet', 	'default');
		add_option ('MailPress_current_theme', 	'MailPress Default');

		$charset_collate = '';
		if ( $wpdb->supports_collation() ) 
		{
			if ( ! empty($wpdb->charset) ) $charset_collate  = "DEFAULT CHARACTER SET $wpdb->charset";
			if ( ! empty($wpdb->collate) ) $charset_collate .= " COLLATE $wpdb->collate";
		}

	  	if (!MP_Admin::tableExists($wpdb->mp_users)) 
		{

	  		$sql = "CREATE TABLE $wpdb->mp_users (
										id 				bigint(20) 				UNSIGNED NOT NULL AUTO_INCREMENT, 
										email 			varchar(100) 			NOT NULL, 
										status 			enum('waiting', 'active')	NOT NULL, 
										confkey 			varchar(100)			NOT NULL, 
										created			timestamp 				NOT NULL default '0000-00-00 00:00:00',
										created_IP 			varchar(100) 			NOT NULL default '',
										created_agent		varchar(255) 			NOT NULL default '',
										created_user_id 		bigint(20) 				UNSIGNED NOT NULL default 0,
										created_country		char(2)				NOT NULL default 'ZZ',
										created_US_state		char(2)				NOT NULL default 'ZZ',
										laststatus 			timestamp 				NOT NULL default '0000-00-00 00:00:00',
										laststatus_IP		varchar(100) 			NOT NULL default '',
										laststatus_agent 		varchar(255) 			NOT NULL default '',
										laststatus_user_id	bigint(20) 				UNSIGNED NOT NULL default 0,
										UNIQUE KEY id (id)
									    ) $charset_collate;";

			require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
	  		dbDelta($sql);
	  	}
	  	if (!MP_Admin::tableExists($wpdb->mp_stats)) 
		{
	  		$sql = "CREATE TABLE $wpdb->mp_stats (
									  	sdate 			date 					NOT NULL,
										stype 			char(1) 				NOT NULL,
										slib 				varchar(45) 			NOT NULL,
										scount 			bigint 				NOT NULL,
										PRIMARY KEY(sdate, stype, slib)
									    ) $charset_collate;";

			require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
	  		dbDelta($sql);
	  	}
	  	if (!MP_Admin::tableExists($wpdb->mp_mails)) 
		{
	  		$sql = "CREATE TABLE $wpdb->mp_mails (
										id 				bigint(20) 				UNSIGNED NOT NULL AUTO_INCREMENT, 
										status 			enum('draft', 'sent','')	NOT NULL, 
										theme				varchar(255) 			NOT NULL default '',
										themedir			varchar(255) 			NOT NULL default '',
										template			varchar(255) 			NOT NULL default '',
										fromemail	 		varchar(255) 			NOT NULL default '',
										fromname	 		varchar(255) 			NOT NULL default '',
										toname	 		varchar(255) 			NOT NULL default '',
										charset	 		varchar(255) 			NOT NULL default '',
										subject			varchar(255) 			NOT NULL default '',
										created			timestamp 				NOT NULL default '0000-00-00 00:00:00',
										created_user_id 		bigint(20) 				UNSIGNED NOT NULL default 0,
										sent				timestamp 				NOT NULL default '0000-00-00 00:00:00',
										sent_user_id 		bigint(20) 				UNSIGNED NOT NULL default 0,
										toemail	 		longtext				NOT NULL,
									  	plaintext			longtext 				NOT NULL,
									  	html 				longtext 				NOT NULL,
										UNIQUE KEY id (id)
									    ) $charset_collate;";

			require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
	  		dbDelta($sql);
		}

	  	if (!MP_Admin::tableExists($wpdb->mp_usermeta)) 
		{
	  		$sql = "CREATE TABLE $wpdb->mp_usermeta (
										umeta_id 			bigint(20) 				NOT NULL auto_increment,
										user_id 			bigint(20) 				NOT NULL default '0',
										meta_key 			varchar(255) 			default NULL,
										meta_value 			longtext,
										PRIMARY KEY  (umeta_id),
										KEY user_id  (user_id),
										KEY meta_key (meta_key)
									     ) $charset_collate;";

			require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
	  		dbDelta($sql);
	  	}

	  	if (!MP_Admin::tableExists($wpdb->mp_mailmeta)) 
		{
	  		$sql = "CREATE TABLE $wpdb->mp_mailmeta (
										mmeta_id 			bigint(20) 				NOT NULL auto_increment,
										mail_id 			bigint(20) 				NOT NULL default '0',
										meta_key 			varchar(255) 			default NULL,
										meta_value 			longtext,
										PRIMARY KEY  (mmeta_id),
										KEY user_id  (mail_id),
										KEY meta_key (meta_key)
									     ) $charset_collate;";

			require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
	  		dbDelta($sql);
	  	}
//
//	From older versions installed
//
		global $mp_general;

		$mp_general = get_option('MailPress_general');

		if (!isset($mp_general['newsletters']))
		{
			$x = array('new_post','daily','weekly','monthly');
			$newsletters = array();

			foreach ($x as $n)
			{
				if (isset($mp_general[$n]))
				{
					$newsletters[$n] = true;
					unset($mp_general[$n]);
				}
			} 

			$mp_general['newsletters'] = $newsletters;
			update_option ('MailPress_general', $mp_general);
		}
		$x = false;
		$x = get_option ('MailPress_daily');
		if ($x && !is_array($x)) update_option('MailPress_daily', array('threshold'=>$x));
		$x = false;
		$x = get_option ('MailPress_weekly');
		if ($x && !is_array($x)) update_option('MailPress_weekly', array('threshold'=>$x));
		$x = false;
		$x = get_option ('MailPress_monthly');
		if ($x && !is_array($x)) update_option('MailPress_monthly', array('threshold'=>$x));

//
//	To avoid mailing existing published post
//
		$post_meta = '_MailPress_prior_to_install';
		$query = "SELECT ID FROM $wpdb->posts WHERE post_status = 'publish' ;";
		$ids = $wpdb->get_results( $query );
		if ($ids) foreach ($ids as $id) if (!get_post_meta($id->ID, $post_meta, true)) add_post_meta($id->ID, $post_meta, 'yes', true);

//
//	Some Clean Up
//
		$sql = " DELETE FROM $wpdb->mp_mails WHERE status = '' AND theme <> ''; ";
		$wpdb->query( $sql );
		$sql = " UPDATE FROM $wpdb->mp_mailmeta SET meta_key = '_MailPress_attached_file' WHERE meta_key = '_mp_attached_file';";
		$wpdb->query( $sql );
?>