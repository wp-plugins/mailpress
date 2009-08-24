<?php

global $mp_general;
if (!isset($mp_general['gmapkey']) || empty($mp_general['gmapkey'])) return;

MailPress::require_class('Tracking_module_abstract');
class MP_Tracking_module_m006 extends MP_Tracking_module_abstract
{
	var $module = 'm006';

	const prefix = 'tracking_m006';

	function __construct()
	{
		$this->type  = basename(dirname(__FILE__));
		$this->title = __('Geoip','MailPress');
		add_filter('MailPress_scripts', array(&$this, 'scripts'), 8, 2);
		parent::__construct();
	}
	
	function scripts($scripts)
	{
		global $mp_general;
	// google map
		wp_register_script( 'google-map',	'http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=false&amp;key=' . $mp_general['gmapkey'], false, false, 1);

		$color 	= ('fresh' == get_user_option('admin_color')) ? '' : '_b';
		$pathimg 	= MP_TMP . 'mp-admin/images/map_control' . $color . '.png';
		$color 	= (is_file($pathimg)) ? $color : '';

	// mp-gmap2
		wp_register_script( 'mp-gmap2',	'/' . MP_PATH . 'mp-includes/js/mp_gmap2.js', array('google-map', 'schedule'), false, 1);
		wp_localize_script( 'mp-gmap2', 	'mp_gmapL10n', array(
			'url'		=> get_option( 'siteurl' ) . '/' . MP_PATH . 'mp-admin/images/',
			'ajaxurl'	=> MP_Action_url,
			'color'	=> $color,
			'zoomwide'	=> js_escape(__('zoom -', 'MailPress')),
			'zoomtight'	=> js_escape(__('zoom +', 'MailPress')),
			'center'	=> js_escape(__('center', 'MailPress')),
			'changemap'	=> js_escape(__('change map', 'MailPress'))
		));
		$scripts[] = 'mp-gmap2';

	// markerclusterer
		wp_register_script( 'mp-markerclusterer',	'/' . MP_PATH . 'mp-includes/js/markerclusterer/markerclusterer.js', false, false, 1);
		$scripts[] = 'mp-markerclusterer';

		return $scripts;
	}

	function meta_box($mail)
	{
	// m006_user_settings
		$u['m006_user_settings'] = get_user_option('_MailPress_' . self::prefix);
		if (!$u['m006_user_settings']) $u['m006_user_settings'] = array('center_lat' => 48.8352, 'center_lng' => 2.4718, 'zoomlevel' => 3, 'maptype' => 'NORMAL');
		$u['m006_user_settings']['prefix'] = self::prefix;
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

	// m006
		global $wpdb;
		$m = array();
		$query = "SELECT DISTINCT ip FROM $wpdb->mp_tracks WHERE mail_id = " . $mail->id . " LIMIT 10;";
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
					$m['m006'][] = $x;
				}
			}
		} 

		$eol = "";
		foreach ( $m as $var => $val ) {
			echo "var $var = " . MP_AdminPage::print_scripts_l10n_val($val);
			$eol = ",\n\t\t";
		}
		echo ";\n";
?>
/* ]]> */
</script>
		<div id='<?php echo self::prefix; ?>_map' style='height:500px;width:auto;padding:0;margin:0;'></div>
<?php 	foreach($u['m006_user_settings'] as $k => $v) 
		{
                if ('prefix' == $k) continue;
?>
		<input type='hidden' id='<?php echo self::prefix . '_' . $k; ?>' value="<?php echo $v; ?>" />
<?php
		}
	}
}
$MP_Tracking_module_m006 = new MP_Tracking_module_m006();
?>