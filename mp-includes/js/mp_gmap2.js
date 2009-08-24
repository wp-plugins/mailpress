function mp_gmap2(settings)
{
	this.settings 	= settings;
	this.map 		= null;
	this.prefix 	= this.settings.prefix;
	this.div 		= this.prefix + '_map';

	this.center_lat 	= jQuery('#' + this.prefix + '_center_lat');
	this.center_lng 	= jQuery('#' + this.prefix + '_center_lng');
	this.zoomlevel  	= jQuery('#' + this.prefix + '_zoomlevel');
	this.maptype  	= jQuery('#' + this.prefix + '_maptype');

	this.init = function() {
		this.map = new GMap2(document.getElementById(this.div));

		this.point = new GLatLng(parseFloat(this.center_lat.val()), parseFloat(this.center_lng.val()));
		this.map.setCenter(this.point, parseFloat(this.zoomlevel.val()), this.map_type(this.maptype.val()));
		this.map_events(this.map, this.center_lat, this.center_lng);

		this.map.mp_zoomlevel = this.zoomlevel;
		this.map.mp_maptype   = this.maptype;
		this.map.mp_center    = this.point;

		this.map.addControl(new OwnZoomIn()); 
		this.map.addControl(new OwnZoomOut());
		this.map.addControl(new OwnChangeMapType());
		this.map.addControl(new OwnCenter());
		this.wheelzoom(this.map, this.div);

		var schedule_id = this.prefix + '_schedule';
		var _this = this;
		jQuery.schedule({	id: schedule_id,
					time: 60000, 
					func: function() { _this.update_settings(); }, 
					repeat: true, 
					protect: true
		});
	}

	this.map_events = function(map, center_lat, center_lng) {
		GEvent.addListener(map, 'moveend', function() {
			point = map.getCenter();
			center_lat.val(point.y.toFixed(6));
			center_lng.val(point.x.toFixed(6));
		});
	}

	this.update_settings = function() {
		var data = {};
		data['action'] = 'map_settings';
		data['prefix'] = this.prefix;
		data['settings[center_lat]'] = this.center_lat.val();
		data['settings[center_lng]'] = this.center_lng.val();
		data['settings[zoomlevel]']  = this.zoomlevel.val();
		data['settings[maptype]']    = this.maptype.val();

		jQuery.ajax({
			data: data,
			beforeSend: null,
			type: "POST",
			url: mp_gmapL10n.ajaxurl,
			success: null
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
				if (e.detail < 0)			{ map.zoomIn();  map.mp_zoomlevel.val(map.getZoom()); }
				else if (e.detail > 0)		{ map.zoomOut(); map.mp_zoomlevel.val(map.getZoom()); }
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
				if (e.wheelDelta > 0)		{ map.zoomIn();  map.mp_zoomlevel.val(map.getZoom()); }
				else if (e.wheelDelta < 0)	{ map.zoomOut(); map.mp_zoomlevel.val(map.getZoom()); }
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

  	GEvent.addDomListener(zoomInDiv,  'click', function() {map.zoomIn(); map.mp_zoomlevel.val(map.getZoom()); });

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

  	GEvent.addDomListener(zoomOutDiv, 'click', function() {map.zoomOut(); map.mp_zoomlevel.val(map.getZoom()); });

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

  	GEvent.addDomListener(centerDiv, 'click', function() {map.setCenter(map.mp_center);});

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