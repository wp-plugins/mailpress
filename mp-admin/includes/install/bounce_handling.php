<?php
//
//
//	New install
//
		global $wpdb;

		$charset_collate = '';
		if ( $wpdb->supports_collation() ) 
		{
			if ( ! empty($wpdb->charset) ) $charset_collate  = "DEFAULT CHARACTER SET $wpdb->charset";
			if ( ! empty($wpdb->collate) ) $charset_collate .= " COLLATE $wpdb->collate";
		}

  		$sql = "CREATE TABLE $wpdb->mp_users (
									id  				bigint(20) 				UNSIGNED NOT NULL AUTO_INCREMENT, 
									email  			varchar(100) 			NOT NULL, 
									name 	 			varchar(100) 			NOT NULL, 
									status  			enum('waiting', 'active', 'bounced', 'unsubscribed')	NOT NULL, 
									confkey  			varchar(100)			NOT NULL, 
									created 			timestamp 				NOT NULL default '0000-00-00 00:00:00',
									created_IP  		varchar(100) 			NOT NULL default '',
									created_agent 		varchar(255) 			NOT NULL default '',
									created_user_id  		bigint(20) 				UNSIGNED NOT NULL default 0,
									created_country 		char(2)				NOT NULL default 'ZZ',
									created_US_state 		char(2)				NOT NULL default 'ZZ',
									laststatus 			timestamp 				NOT NULL default '0000-00-00 00:00:00',
									laststatus_IP  		varchar(100) 			NOT NULL default '',
									laststatus_agent 		varchar(255) 			NOT NULL default '',
									laststatus_user_id  	bigint(20) 				UNSIGNED NOT NULL default 0,
									UNIQUE KEY id (id)
								    ) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  		dbDelta($sql);
?>