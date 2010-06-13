<?php

global $mp_general;
if (!isset($mp_general['gmapkey']) || empty($mp_general['gmapkey'])) return;

class MP_Tracking_module_m006 extends MP_Tracking_module_abstract
{
	const prefix = 'tracking_m006';

	var $module = 'm006';
	var $context = 'side';
	var $file = __FILE__;

	function __construct($title)
	{
		add_filter('MailPress_scripts', array(&$this, 'scripts'), 8, 2);
		parent::__construct($title);
	}
	
	function scripts($scripts)
	{
        if (!isset($_GET['id'])) return;
		global $mp_general;
	// google map
		wp_register_script( 'google-map',	'http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=false&amp;key=' . $mp_general['gmapkey'], false, false, 1);

		$color 	= ('fresh' == get_user_option('admin_color')) ? '' : '_b';
		$pathimg 	= MP_ABSPATH . 'mp-admin/images/map_control' . $color . '.png';
		$color 	= (is_file($pathimg)) ? $color : '';

	// mp-gmap2
		wp_register_script( 'mp-gmap2',	'/' . MP_PATH . 'mp-includes/js/mp_gmap2.js', array('google-map', 'schedule'), false, 1);
		wp_localize_script( 'mp-gmap2', 	'mp_gmapL10n', array(
			'id'		=> $_GET['id'],
			'type'	=> 'mp_mail',
			'url'		=> get_option( 'siteurl' ) . '/' . MP_PATH . 'mp-admin/images/',
			'ajaxurl'	=> MP_Action_url,
			'color'	=> $color,
			'zoomwide'	=> js_escape(__('zoom -', MP_TXTDOM)),
			'zoomtight'	=> js_escape(__('zoom +', MP_TXTDOM)),
			'center'	=> js_escape(__('center', MP_TXTDOM)),
			'changemap'	=> js_escape(__('change map', MP_TXTDOM))
		));
		$scripts[] = 'mp-gmap2';

	// markerclusterer
		wp_register_script( 'mp-markerclusterer',	'/' . MP_PATH . 'mp-includes/js/markerclusterer/markerclusterer_packed.js', false, false, 1);
		$scripts[] = 'mp-markerclusterer';

		return $scripts;
	}

	function meta_box($mail)
	{
	// m006_user_settings
		MailPress::require_class('Mailmeta');
		$u['m006_user_settings'] = MP_Mailmeta::get($mail->id, '_MailPress_' . self::prefix);
		if (!$u['m006_user_settings']) $u['m006_user_settings'] = get_user_option('_MailPress_' . self::prefix);
		if (!$u['m006_user_settings']) $u['m006_user_settings'] = array('center_lat' => 48.8352, 'center_lng' => 2.4718, 'zoomlevel' => 3, 'maptype' => 'NORMAL');
		$u['m006_user_settings']['prefix'] = self::prefix;

	// m006
		global $wpdb;
		$m = array();

		$tracks = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT ip FROM $wpdb->mp_tracks WHERE mail_id = %d ", $mail->id) );

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
		<div id='<?php echo self::prefix; ?>_map' style='overflow:hidden;height:500px;width:auto;padding:0;margin:0;'></div>
<?php 	
		foreach($u['m006_user_settings'] as $k => $v) 
		{
                if ('prefix' == $k) continue;
?>
		<input type='hidden' id='<?php echo self::prefix . '_' . $k; ?>' value="<?php echo $v; ?>" />
<?php
		}
	}
}
new MP_Tracking_module_m006( __('Geoip', MP_TXTDOM));