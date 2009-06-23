// tracking
var mp_tracking = {
	point : null, 
	zoomlevel : 6, 
	maptype : G_NORMAL_MAP, 

	gmap : function(div) {
		if(typeof(m006) == "undefined") return;

		var map = new GMap2(document.getElementById(div));

		map.addControl(new OwnZoomIn());
		map.addControl(new OwnZoomOut());
		map.addControl(new OwnChangeMapType());
		map.addControl(new OwnCenter());
		OwnWheelZoom(map, div); 

		var mp_info = m006[0];
		var lat = parseFloat(mp_info['lat']);
		var lng = parseFloat(mp_info['lng']);
		var tooltip = mp_info['ip'];
		mp_tracking.point  = new GLatLng(lat, lng);

		map.setCenter( mp_tracking.point, mp_tracking.zoomlevel, mp_tracking.maptype);

		for (var i in m006)
		{
			mp_info = m006[i];

			lat = parseFloat(mp_info['lat']);
			lng = parseFloat(mp_info['lng']);
			tooltip = mp_info['ip'];
			icon	= new GIcon(G_DEFAULT_ICON, mp_gmapL10n.url+'icon'+mp_gmapL10n.color+'.png');

			var marker = new GMarker(new GLatLng(lat, lng), {icon:icon, title:tooltip, draggable:false});
			map.addOverlay(marker);
		}
	}, 

	init : function(div) {
		// close postboxes that should be closed
		jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');

		// postboxes
		postboxes.add_postbox_toggles(MP_AdminPageL10n.screen);

		// ip info
		if (GBrowserIsCompatible()) mp_tracking.gmap(div);
	}
}
jQuery(document).ready( function() { mp_tracking.init('ip_info_div'); });