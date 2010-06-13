// tracking

var mp_tracking = {

	init : function() {
		// close postboxes that should be closed
		jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');

		// postboxes
		postboxes.add_postbox_toggles(MP_AdminPageL10n.screen);

		// ip info
		if (GBrowserIsCompatible()) mp_tracking.gmap();
	},

	gmap : function() {
		if(typeof(u006) == "undefined") return;

		var map = new mp_gmap2(u006_user_settings);
		var icon = new GIcon(G_DEFAULT_ICON, mp_gmapL10n.url+'map_icon'+mp_gmapL10n.color+'.png');

		var markers = [];

		for (var i in u006)
		{
			mp_info = u006[i];

			lat = parseFloat(mp_info['lat']);
			lng = parseFloat(mp_info['lng']);
			tooltip = mp_info['ip'];

			var marker = new GMarker(new GLatLng(lat, lng), {icon:icon, title:tooltip, draggable:false});
			markers.push(marker);
		}
		if (markers.length > 100)
		{
			var markerCluster = new MarkerClusterer(map.map, markers);
			return;
		}
		for (var i in markers)
		{
			map.map.addOverlay(markers[i]);
		}
	}
}
jQuery(document).ready( function() { mp_tracking.init(); });