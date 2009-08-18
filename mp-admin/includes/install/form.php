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

  		$sql = "CREATE TABLE $wpdb->mp_forms (
									id 				bigint(20) 				NOT NULL auto_increment,
									label	 			varchar(255) 			NOT NULL default '',
									description			varchar(255) 			NOT NULL default '',
									template			varchar(50) 			NOT NULL default '',
									settings			longtext,

									UNIQUE KEY id (id)
								    ) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/upgrade-functions.php');

  		dbDelta($sql);
  		$sql = "CREATE TABLE $wpdb->mp_fields (
									id 				bigint(20) 				NOT NULL auto_increment,
									form_id 			bigint(20) 				NOT NULL,
									ordre		 		bigint(20) 				UNSIGNED NOT NULL default 0,
									type	 			varchar(50) 			NOT NULL default '',
									template			varchar(50) 			NOT NULL default '',
									label	 			varchar(255) 			NOT NULL default '',
									description			varchar(255) 			NOT NULL default '',
									settings			longtext,

									UNIQUE KEY id (id)
								    ) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
  		dbDelta($sql);
?>