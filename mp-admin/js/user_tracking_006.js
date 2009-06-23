// tracking
var mp_tracking_006 = {
	point : null, 
	zoomlevel : 6, 
	maptype : G_NORMAL_MAP, 

	gmap : function(div) {
		if(typeof(u006) == "undefined") return;

		var map = new GMap2(document.getElementById(div));

		map.addControl(new OwnZoomIn());
		map.addControl(new OwnZoomOut());
		map.addControl(new OwnChangeMapType());
		map.addControl(new OwnCenter());
		OwnWheelZoom(map,div); 

		var mp_info = u006[0];
		var lat = parseFloat(mp_info['lat']);
		var lng = parseFloat(mp_info['lng']);
		var tooltip = mp_info['ip'];
		mp_tracking_006.point  = new GLatLng(lat,lng);

		map.setCenter(mp_tracking_006.point, mp_tracking_006.zoomlevel, mp_tracking_006.maptype);

		for (var i in u006)
		{
			mp_info = u006[i];

			lat = parseFloat(mp_info['lat']);
			lng = parseFloat(mp_info['lng']);
			tooltip = mp_info['ip'];
			icon	= new GIcon(G_DEFAULT_ICON,mp_gmapL10n.url+'icon'+mp_gmapL10n.color+'.png');

			var marker = new GMarker(new GLatLng(lat,lng), {icon:icon,title:tooltip,draggable:false});
			map.addOverlay(marker);
		}
	},

	init : function(div) {
		mp_tracking_006.gmap(div);
	}
}
jQuery(document).ready( function() { mp_tracking_006.init('ip_info_div'); });