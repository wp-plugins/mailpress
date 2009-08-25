<?php

global $mp_general;
if (!isset($mp_general['gmapkey']) || empty($mp_general['gmapkey'])) return;

MailPress::require_class('Tracking_module_abstract');
class MP_Tracking_module_u006 extends MP_Tracking_module_abstract
{
	var $module = 'u006';
	const prefix = 'tracking_u006';

	function __construct()
	{
		$this->type  = basename(dirname(__FILE__));
		$this->title = __('Geoip','MailPress');
		add_filter('MailPress_scripts', array(&$this, 'scripts'), 8, 2);
		parent::__construct();
	}
	
	function scripts($scripts)
	{
		wp_register_script( 'user-tracking-006', 		'/' . MP_PATH . 'mp-admin/js/user_tracking_006.js', false, false, 1);
		$scripts[] = 'user-tracking-006';
		return $scripts;
	}

	function meta_box($mp_user)
	{
	// u006_user_settings
		$u['u006_user_settings'] = get_user_option('_MailPress_' . self::prefix);
		if (!$u['u006_user_settings']) $u['u006_user_settings'] = array('center_lat' => 48.8352, 'center_lng' => 2.4718, 'zoomlevel' => 3, 'maptype' => 'NORMAL');
		$u['u006_user_settings']['prefix'] = self::prefix;

	// u006
		global $wpdb;
		$m = array();

		$query = "SELECT DISTINCT ip FROM $wpdb->mp_tracks WHERE user_id = " . $mp_user->id ;
		$tracks = $wpdb->get_results($query);

		if ($tracks)
		{
			MP_AdminPage::require_class('Ip');
			foreach($tracks as $track)
			{
				$x = MP_Ip::get_latlng($track->ip);
				if ($x)
				{
					$x['ip'] = $track->ip;
					$m['u006'][] = $x;
				}
			}
		}
?>
<script type='text/javascript'>
/* <![CDATA[ */
<?php
		$eol = "";
		foreach ( $u as $var => $val ) {
			echo "var $var = " . MP_AdminPage::print_scripts_l10n_val($val);
			$eol = ",\n\t\t";
		}
		echo ";\n";

		$eol = "";
		foreach ( $m as $var => $val ) {
			echo "var $var = " . MP_AdminPage::print_scripts_l10n_val($val);
			$eol = ",\n\t\t";
		}
		echo ";\n";
?>
/* ]]> */
</script>
		<div id='<?php echo self::prefix; ?>_map' style='height:300px;width:auto;padding:0;margin:0;'></div>
<?php 	
		foreach($u['u006_user_settings'] as $k => $v) 
		{
                if ('prefix' == $k) continue;
?>
		<input type='hidden' id='<?php echo self::prefix . '_' . $k; ?>' value="<?php echo $v; ?>" />
<?php
		}
	}
}
$MP_Tracking_module_u006 = new MP_Tracking_module_u006();
?>