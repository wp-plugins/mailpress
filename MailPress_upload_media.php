<?php
if ( (class_exists('MailPress')) && (is_admin()) )
{
/*
Plugin Name: MailPress_upload_media
Plugin URI: http://www.mailpress.org
Description: This is just an addon for MailPress to allow upload media button on MailPress Write
Author: Andre Renaut
Version: 4.0.1
Author URI: http://www.mailpress.org
*/

class MailPress_upload_media
{
	function __construct()
	{
		add_filter('MailPress_scripts', 		array('MailPress_upload_media', 'scripts'), 8, 2);
		add_filter('MailPress_upload_media', 	array('MailPress_upload_media', 'upload_media'), 8, 1);
	}

	public static function scripts($scripts, $screen) 
	{
		if ($screen != MailPress_page_write) return $scripts;

		wp_register_script( 'mp-media-upload', 	'/' . MP_PATH . 'mp-admin/js/write_upload_media.js', array('media-upload'), false, 1);
		$scripts[] = 'mp-media-upload';

		return $scripts;
	}

	public static function upload_media($x)
	{
		return true;
	}
}

$MailPress_upload_media = new MailPress_upload_media();
}
?>