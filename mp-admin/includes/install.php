<?php
		global $wpdb;

		add_option ('MailPress_daily',  		date('Ymd'));
		add_option ('MailPress_weekly', 		MailPress::get_yearweekofday(date('Y-m-d')));
		add_option ('MailPress_monthly', 		date('Ym'));

		add_option ('MailPress_template', 		'default');
		add_option ('MailPress_stylesheet', 	'default');
		add_option ('MailPress_current_theme', 	'MailPress Default');

		$charset_collate = '';
		if ( $wpdb->supports_collation() ) 
		{
			if ( ! empty($wpdb->charset) ) $charset_collate  = "DEFAULT CHARACTER SET $wpdb->charset";
			if ( ! empty($wpdb->collate) ) $charset_collate .= " COLLATE $wpdb->collate";
		}

	  	if (!MailPress::tableExists($wpdb->mp_subscribers)) 
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
	  	if (!MailPress::tableExists($wpdb->mp_stats)) 
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
	  	if (!MailPress::tableExists($wpdb->mp_mails)) 
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
?>