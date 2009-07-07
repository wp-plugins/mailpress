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

  		$sql = "CREATE TABLE $wpdb->mp_tracks (
									id 				bigint(20) 				UNSIGNED NOT NULL AUTO_INCREMENT, 
									user_id 			bigint(20) 				NOT NULL default '0',
									mail_id 			bigint(20) 				NOT NULL default '0',
									tmstp				timestamp 				NOT NULL default '0000-00-00 00:00:00',
									mmeta_id 			bigint(20) 				NOT NULL default '0',
									context	 		varchar(20) 			NOT NULL default 'html',
									ip	 			varchar(100) 			NOT NULL default '',
									agent				varchar(255) 			NOT NULL default '',
									track	 			longtext,
									referrer 			longtext,
									UNIQUE KEY id (id),
									KEY user_id   (user_id),
									KEY mail_id   (mail_id),
									KEY mmeta_id  (mmeta_id)
								    ) $charset_collate;";
    
			require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
	  		dbDelta($sql);
?>