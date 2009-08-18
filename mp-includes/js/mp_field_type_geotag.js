function mp_field_type_geotag(settings)
{
	this.settings 	= settings;
	this.map 		= null;
	this.prefix 	= 'mp_' + this.settings.form + '_' + this.settings.field;
	this.div 		= this.prefix + '_map';

	this.lat 		= jQuery('#' + this.prefix + '_lat,#' + this.prefix + '_lat_d');
	this.lng 		= jQuery('#' + this.prefix + '_lng,#' + this.prefix + '_lng_d');

	this.center_lat 	= jQuery('#' + this.prefix + '_center_lat');
	this.center_lng 	= jQuery('#' + this.prefix + '_center_lng');
	this.zoomlevel  	= jQuery('#' + this.prefix + '_zoomlevel');
	this.maptype  	= jQuery('#' + this.prefix + '_maptype');
	this.rgeocode 	= jQuery('#' + this.prefix + '_geocode');

	this.init = function() {
		this.map = new GMap2(document.getElementById(this.div));

		this.point = new GLatLng(parseFloat(this.settings.center_lat), parseFloat(this.settings.center_lng));
		this.map.setCenter(this.point, parseFloat(this.settings.zoomlevel), this.map_type(this.settings.maptype));
		this.map_events(this.map, this.center_lat, this.center_lng);

		this.point = new GLatLng(parseFloat(this.settings.lat), parseFloat(this.settings.lng));
		this.marker = new GMarker(this.point, {draggable:true});
		this.map.addOverlay(this.marker);
		this.marker_events(this.marker, this.lat, this.lng);

		this.geocoder = new GClientGeocoder();
		this.geocoder_events(this.geocoder, this.map, this.marker, this.lat, this.lng, this.geocode, this.prefix);

		this.map.mp_marker    = this.marker;
		this.map.mp_zoomlevel = this.zoomlevel;
		this.map.mp_maptype   = this.maptype;
		this.map.mp_rgeocode  = this.rgeocode;

		if (this.settings.zoom 		== '1') {  var mp_zoomin = this.map.addControl(new OwnZoomIn()); this.map.addControl(new OwnZoomOut()); }
		if (this.settings.changemap 	== '1') this.map.addControl(new OwnChangeMapType());
		if (this.settings.center  	== '1') this.map.addControl(new OwnCenter());
		if (this.settings.rgeocode  	== '1') this.map.addControl(new OwnGeoc());
		this.wheelzoom(this.map, this.div);
	}

	this.map_events = function(map, center_lat, center_lng) {
		GEvent.addListener(map, 'moveend', function() {
			point = map.getCenter();
			center_lat.val(point.y.toFixed(6));
			center_lng.val(point.x.toFixed(6));
		});
	}

	this.marker_events = function(marker, lat, lng) {
		GEvent.addListener(marker, 'drag', function() {
			point = this.getLatLng();
			lat.val(point.y.toFixed(6));
			lng.val(point.x.toFixed(6));
		});
	}

	this.geocoder_events = function(geocoder, map, marker, lat, lng, geocode, prefix) {
		jQuery('#' + prefix + '_geocode_button').click( function() {
			loc = jQuery('#' + prefix + '_geocode').val();
			geocode(geocoder, loc, map, marker, lat, lng);
		});
	}

	this.geocode = function (geocoder, loc, map, marker, lat, lng) {
		geocoder.getLatLng(loc, function(point) {
			if (!point) return;
			map.setCenter(point);
			marker.setLatLng(point);
			lat.val(point.y.toFixed(6));
			lng.val(point.x.toFixed(6));
		});
	}

	this.map_type = function(maptype) {
		switch(maptype)
		{
			case 'SATELLITE' 	: return G_SATELLITE_MAP;	break;
			case 'HYBRID' 	: return G_HYBRID_MAP;		break;
			case 'PHYSICAL' 	: return G_PHYSICAL_MAP;	break;
			default	 	: return G_NORMAL_MAP;		break;
		}
	}

	this.wheelzoom = function(map, div) {
	      GEvent.addDomListener(document.getElementById(div), "DOMMouseScroll",function(e) 
		{
			if (typeof e.preventDefault  == 'function') e.preventDefault();
			if (typeof e.stopPropagation == 'function') e.stopPropagation();
			if (e.detail)
			{
				if (e.detail < 0)			{ map.zoomIn();  map.setCenter(map.mp_marker.getLatLng()); map.mp_zoomlevel.val(map.getZoom()); }
				else if (e.detail > 0)		{ map.zoomOut(); map.setCenter(map.mp_marker.getLatLng()); map.mp_zoomlevel.val(map.getZoom()); }
			}
		}); // Firefox
	     	GEvent.addDomListener(document.getElementById(div), "mousewheel",function(e) 
		{
			if (window.event) 
			{
				window.event.cancelBubble = true;
				window.event.returnValue  = false;
			}	
			if (e.wheelDelta)	
			{
				if (e.wheelDelta > 0)		{ map.zoomIn();  map.setCenter(map.mp_marker.getLatLng()); map.mp_zoomlevel.val(map.getZoom()); }
				else if (e.wheelDelta < 0)	{ map.zoomOut(); map.setCenter(map.mp_marker.getLatLng()); map.mp_zoomlevel.val(map.getZoom()); }
			}
		}); // IE
	}

	this.init();
}

function OwnZoomIn() {}
OwnZoomIn.prototype = new GControl();
OwnZoomIn.prototype.initialize = function(map) 
{
	var container = document.createElement('div');

	var zoomInDiv = document.createElement('img');
	zoomInDiv.setAttribute('src', mp_gmapL10n.url+'map_zoom+'+mp_gmapL10n.color+'.png');
	zoomInDiv.setAttribute('alt', mp_gmapL10n.zoomtight);
	zoomInDiv.setAttribute('title', mp_gmapL10n.zoomtight);
  	container.appendChild(zoomInDiv);

  	GEvent.addDomListener(zoomInDiv,  'click', function() {map.zoomIn();  map.setCenter(map.mp_marker.getLatLng()); map.mp_zoomlevel.val(map.getZoom()); });

  	map.getContainer().appendChild(container);
  	return container;
}
OwnZoomIn.prototype.getDefaultPosition = function() { return new GControlPosition(G_ANCHOR_TOP_LEFT, new GSize(3, 3)); }

function OwnZoomOut() {}
OwnZoomOut.prototype = new GControl();
OwnZoomOut.prototype.initialize = function(map) 
{
	var container = document.createElement('div');

	var zoomOutDiv = document.createElement('img');
	zoomOutDiv.setAttribute('src', mp_gmapL10n.url+'map_zoom-'+mp_gmapL10n.color+'.png');
	zoomOutDiv.setAttribute('alt', mp_gmapL10n.zoomwide);
	zoomOutDiv.setAttribute('title', mp_gmapL10n.zoomwide);
 	container.appendChild(zoomOutDiv);

  	GEvent.addDomListener(zoomOutDiv, 'click', function() {map.zoomOut(); map.setCenter(map.mp_marker.getLatLng()); map.mp_zoomlevel.val(map.getZoom()); });

  	map.getContainer().appendChild(container);
  	return container;
}
OwnZoomOut.prototype.getDefaultPosition = function() { return new GControlPosition(G_ANCHOR_TOP_LEFT, new GSize(3, 22)); }

function OwnCenter() {}
OwnCenter.prototype = new GControl();
OwnCenter.prototype.initialize = function(map) 
{
	var container = document.createElement('div');

	var centerDiv = document.createElement('img');
	centerDiv.setAttribute('src', mp_gmapL10n.url+'map_center'+mp_gmapL10n.color+'.png');
	centerDiv.setAttribute('alt', mp_gmapL10n.center);
	centerDiv.setAttribute('title', mp_gmapL10n.center);
 	container.appendChild(centerDiv);

  	GEvent.addDomListener(centerDiv, 'click', function() {map.setCenter(map.mp_marker.getLatLng());});

  	map.getContainer().appendChild(container);
  	return container;
}
OwnCenter.prototype.getDefaultPosition = function() { return new GControlPosition(G_ANCHOR_TOP_RIGHT, new GSize(3, 3)); }

function OwnChangeMapType() {}
OwnChangeMapType.prototype = new GControl();
OwnChangeMapType.prototype.initialize = function(map) 
{
	var container = document.createElement('div');

	var changeMap = document.createElement('img');
	changeMap.setAttribute('src', mp_gmapL10n.url+'map_control'+mp_gmapL10n.color+'.png');
	changeMap.setAttribute('alt', mp_gmapL10n.changemap);
	changeMap.setAttribute('title', mp_gmapL10n.changemap);
  	container.appendChild(changeMap);

  	GEvent.addDomListener(changeMap, 'click', function() 
	{
		switch (true)
		{
			case (G_NORMAL_MAP == map.getCurrentMapType()):
				map.setMapType(G_SATELLITE_MAP);
				map.mp_maptype.val('SATELLITE');
			break;
			case (G_SATELLITE_MAP == map.getCurrentMapType()):
				map.setMapType(G_HYBRID_MAP);
				map.mp_maptype.val('HYBRID');
			break;
			case (G_HYBRID_MAP == map.getCurrentMapType()):
				map.setMapType(G_PHYSICAL_MAP);
				map.mp_maptype.val('PHYSICAL');
			break;
			case (G_PHYSICAL_MAP == map.getCurrentMapType()):
				map.setMapType(G_NORMAL_MAP);
				map.mp_maptype.val('NORMAL');
			break;
		}
	});

  	map.getContainer().appendChild(container);
  	return container;
}
OwnChangeMapType.prototype.getDefaultPosition = function() { return new GControlPosition(G_ANCHOR_TOP_RIGHT, new GSize(3, 22)); }

function OwnGeoc() {}
OwnGeoc.prototype = new GControl();
OwnGeoc.prototype.initialize = function(map) 
{
	var container = document.createElement('div');

	var rgeocodeDiv = document.createElement('img');
	rgeocodeDiv.setAttribute('src', mp_gmapL10n.url+'map_geocode'+mp_gmapL10n.color+'.png');
	rgeocodeDiv.setAttribute('alt', mp_gmapL10n.rgeocode);
	rgeocodeDiv.setAttribute('title', mp_gmapL10n.rgeocode);
 	container.appendChild(rgeocodeDiv);

  	GEvent.addDomListener(rgeocodeDiv, 'click', function() {
		var g = new GClientGeocoder();
		var a = map.mp_rgeocode;
		g.getLocations(map.mp_marker.getLatLng(), function(response) {
			if (!response || response.Status.code != 200) { alert('?'); return;}
			a.val(response.Placemark[0].address);
		});
	});

  	map.getContainer().appendChild(container);
  	return container;
}
OwnGeoc.prototype.getDefaultPosition = function() { return new GControlPosition(G_ANCHOR_BOTTOM_RIGHT, new GSize(3, 3)); }