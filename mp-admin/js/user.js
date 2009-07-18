// user

var mp_user = {
	point : null, 
	zoomlevel : 6, 
	maptype : G_NORMAL_MAP, 

	init : function(div) {
		// close postboxes that should be closed
		jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');

		// postboxes
		postboxes.add_postbox_toggles(MP_AdminPageL10n.screen);

		// custom fields
		jQuery('#the-list').wpList({ 	
			addAfter: function( xml, s ) {
				jQuery('table#list-table').show();
			}, 
			addBefore: function( s ) {
				s.data += '&mp_user_id=' + jQuery('#mp_user_id').val(); 
				return s;
			}
		});

		// mailinglist tabs
		var mailinglistTabs =jQuery('#user-tabs').tabs();

		// ip info
		if (GBrowserIsCompatible()) mp_user.gmap(div);
	},

	gmap : function(div) {
		if(typeof(meta_box_IP_info) == "undefined") return;

		mp_user.point  = new GLatLng(parseFloat(meta_box_IP_info.lat), parseFloat(meta_box_IP_info.lng)); 
		var size = new GSize(267, 250);
		var map = new GMap2(document.getElementById(meta_box_IP_info.div), {size:size});

		map.addControl(new OwnZoomIn());
		map.addControl(new OwnZoomOut());
		map.addControl(new OwnChangeMapType());
		map.addControl(new OwnCenter());
		OwnWheelZoom(map, div); 

		map.setCenter(mp_user.point, mp_user.zoomlevel, mp_user.maptype);
		map._thisCenter = mp_user.point;

		tooltip = 'lat : '+meta_box_IP_info.lat+' lng : '+meta_box_IP_info.lng;

		icon	= new GIcon(G_DEFAULT_ICON, mp_gmapL10n.url+'icon'+mp_gmapL10n.color+'.png');
		var marker = new GMarker(mp_user.point, {icon:icon, title:tooltip, draggable:false});
		
		map.addOverlay(marker);
	}
}
jQuery(document).ready( function() { mp_user.init('IP_info_gmap'); });