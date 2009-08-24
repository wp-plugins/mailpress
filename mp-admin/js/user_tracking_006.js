// tracking

var mp_tracking_006 = {
	init : function() {
		// ip info
		if (GBrowserIsCompatible()) mp_tracking_006.gmap();
	},

	gmap : function() {
		if(typeof(u006) == "undefined") return;

		var map = new mp_gmap2(u006_user_settings);
		var icon = new GIcon(G_DEFAULT_ICON, mp_gmapL10n.url+'map_icon'+mp_gmapL10n.color+'.png');

		for (var i in u006)
		{
			mp_info = u006[i];

			lat = parseFloat(mp_info['lat']);
			lng = parseFloat(mp_info['lng']);
			tooltip = mp_info['ip'];

			var marker = new GMarker(new GLatLng(lat,lng), {icon:icon,title:tooltip,draggable:false});
			map.map.addOverlay(marker);
		}
	}
}
jQuery(document).ready( function() { mp_tracking_006.init(); });