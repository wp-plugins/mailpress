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

  		$sql = "CREATE TABLE $wpdb->mp_mails (
									id 				bigint(20) 				UNSIGNED NOT NULL AUTO_INCREMENT, 
									status 			enum('draft', 'sent', 'unsent', 'sending', '')	NOT NULL, 
									theme 			varchar(255) 			NOT NULL default '',
									themedir 			varchar(255) 			NOT NULL default '',
									template 			varchar(255) 			NOT NULL default '',
									fromemail 	 		varchar(255) 			NOT NULL default '',
									fromname 	 		varchar(255) 			NOT NULL default '',
									toname 	 		varchar(255) 			NOT NULL default '',
									charset 	 		varchar(255) 			NOT NULL default '',
									parent 			bigint(20)				UNSIGNED NOT NULL default 0,
									child  			bigint(20)				NOT NULL default 0,
									subject 			varchar(255) 			NOT NULL default '',
									created 			timestamp 				NOT NULL default '0000-00-00 00:00:00',
									created_user_id 		bigint(20) 				UNSIGNED NOT NULL default 0,
									sent 				timestamp 				NOT NULL default '0000-00-00 00:00:00',
									sent_user_id  		bigint(20) 				UNSIGNED NOT NULL default 0,
									toemail 	 		longtext				NOT NULL,
								  	plaintext 			longtext 				NOT NULL,
								  	html 				longtext 				NOT NULL,
									UNIQUE KEY id (id)
								    ) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  		dbDelta($sql);

		$query = "UPDATE $wpdb->mp_mailmeta SET meta_key = '" . self::metakey . "' WHERE meta_key = 'batch_send';";
		$wpdb->query( $query );
?>