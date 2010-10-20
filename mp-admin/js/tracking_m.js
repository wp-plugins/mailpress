// tracking

var mp_tracking = {

	init : function() {
		// close postboxes that should be closed
		jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');

		// postboxes
		postboxes.add_postbox_toggles(MP_AdminPageL10n.screen);

		// ip info
		mp_tracking.gmap();
	},

	gmap : function() {
		if(typeof(m006) == "undefined") return;

		var map  = new mp_gmap3(m006_user_settings);
		var markers = [];

		for (var i in m006)
		{
			var mkOptions = {
				position:new google.maps.LatLng(parseFloat(m006[i]['lat']), parseFloat(m006[i]['lng'])),
				title:m006[i]['ip']
			};
			var marker = new google.maps.Marker(mkOptions);
			markers.push(marker);
		}
		var markerCluster = new MarkerClusterer(map.map, markers);
	}
}
jQuery(document).ready( function() { mp_tracking.init(); });