<?php
if ( (class_exists('MailPress')) && (is_admin()) )
{
/*
Plugin Name: MailPress_bulk_import
Plugin URI: http://www.mailpress.org
Description: This is just an addon for MailPress to import users
Author: Daniel Caleb & Andre Renaut
Version: 4.0
Author URI: http://www.mailpress.org http://galerie-eigenheim.de
*/

class MailPress_bulk_import
{
	function __construct()
	{
		add_action('MailPress_users_addon', 		array('MailPress_bulk_import', 'form'), 1, 1);
		add_action('MailPress_users_addon_update', 	array('MailPress_bulk_import', 'process_form'));
	}

	public static function form($url_parms)
	{
?>
<!-- MailPress_bulk_import -->
<form id='bulk-add' action='' method='post' style="z-index:2000">
	<input type='text'   name='emails'   value='' id='emails' onclick="document.getElementById('bulk-add').style.width='90%';document.getElementById('emails').style.width='70%';document.getElementById('radios').style.display='block';" />
	<input type='submit' name='bulk_add' value='<?php _e('Bulk Add', 'MailPress' ); ?>' class='button' />
	<div id="radios" style="display:none">
<?php 
if (class_exists('MailPress_mailinglist'))
{
	MP_AdminPage::require_class('Mailinglists');
	$dropdown_options = array('hierarchical' => true, 'show_count' => 0, 'orderby' => 'name', 'htmlid' => 'bulk_import_mailinglist', 'name' => 'bulk_import_mailinglist', 'selected' => get_option('MailPress_default_mailinglist'));
	MP_Mailinglists::dropdown($dropdown_options);
}
?>
		<input type='radio' name='activate' value='activate' /><?php _e('Activate', 'MailPress'); ?>
		<input type='radio' name='activate' value='waiting' checked='checked' /> <?php _e('Require Authorization', 'MailPress'); ?>
		<span style="color:#f00;padding-left:50px;">
			<?php _e('email,name;email,name;...', 'MailPress'); ?>
		</span>
	</div>
	<input type='hidden' name='mode' value='<?php echo $url_parms['mode']; ?>' />
	<input type='hidden' name='status' value='<?php if (isset($url_parms['status'])) echo $url_parms['status']; ?>' />
</form>
<br />
<!-- MailPress_bulk_import -->
<?php
	}

	public static function process_form()
	{
		if (!(isset($_POST['bulk_add']))) return;
		if ((empty($_POST['emails']))) return;

		global $wpdb;

		$count_records = $count_emails = $count_users = 0;

		$count_records 	= count(explode(';', $_POST['emails']));

		$count_emails	= self::bulk_users($_POST['emails'], $_POST['activate']);

		$count_users 	= $wpdb->get_var("SELECT count(*)    FROM $wpdb->mp_users;");

		$m1 = sprintf( __ngettext( __('%s email', 'MailPress'), __('%s emails', 'MailPress'), $count_emails ), $count_emails );
		$m2 = sprintf( __ngettext( __('%s record', 'MailPress'), __('%s records', 'MailPress'), $count_records ), $count_records );
		$m3 = sprintf( __ngettext( __('%s user', 'MailPress'), __('%s users', 'MailPress'), $count_users ), $count_users );
		$m4 = sprintf(__('%1$s from %2$s added. Now there are a total of %3$s', 'MailPress'), $m1, $m2, $m3);
		MP_AdminPage::message($m4);
	}

	public static function bulk_users($mails, $type)
	{
		$count = 0;
		$email_array 	= explode(';', $mails);

		foreach ($email_array as $email_name) 
		{
			$x = explode(',', $email_name);
						
			$email = trim($x[0]);
			$name = (isset($x[1])) ? trim($x[1]) : '';

			MP_AdminPage::require_class('Users');
			if ( MP_AdminPage::is_email($email) && (!MP_Users::get_status_by_email($email)))
			{
				if ('activate' == $type) 
				{
				 	$key = md5(uniqid(rand(), 1));	
					MP_Users::insert($email, $name, $key, 'active');
					$count++;
				}
				else
				{
					MP_AdminPage::require_class('Mails');
					$return = MP_Users::add($email, $name);
					if ($return['result']) $count++;
				}

				if (class_exists('MailPress_mailinglist'))
				{
					MP_AdminPage::require_class('Mailinglists');
					$mp_user_id  = MP_Users::get_id_by_email($email);
					MP_Mailinglists::set_object_terms(MP_Users::get_id_by_email($email), (is_array($_POST['bulk_import_mailinglist'])) ? $_POST['bulk_import_mailinglist'] : array($_POST['bulk_import_mailinglist']) );
				}
			}
		}
		return $count;
	}
}
$MailPress_bulk_import = new MailPress_bulk_import();
}
?>