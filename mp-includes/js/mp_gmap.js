function OwnZoomIn() {}

OwnZoomIn.prototype = new GControl();
OwnZoomIn.prototype.initialize = function(map) 
{
	var container = document.createElement('div');

	var zoomInDiv = document.createElement('img');
	zoomInDiv.setAttribute('src', mp_gmapL10n.url+'zoommapplus'+mp_gmapL10n.color+'.png');
	zoomInDiv.setAttribute('alt', mp_gmapL10n.zoomtight);
	zoomInDiv.setAttribute('title', mp_gmapL10n.zoomtight);
  	container.appendChild(zoomInDiv);

  	GEvent.addDomListener(zoomInDiv, 'click', function() {map.zoomIn();});

  	map.getContainer().appendChild(container);
  	return container;
}
OwnZoomIn.prototype.getDefaultPosition = function() 
{
	return new GControlPosition(G_ANCHOR_TOP_LEFT, new GSize(3, 3));
}

function OwnZoomOut() {}

OwnZoomOut.prototype = new GControl();
OwnZoomOut.prototype.initialize = function(map) 
{
	var container = document.createElement('div');

	var zoomOutDiv = document.createElement('img');
	zoomOutDiv.setAttribute('src', mp_gmapL10n.url+'zoommapmoin'+mp_gmapL10n.color+'.png');
	zoomOutDiv.setAttribute('alt', mp_gmapL10n.zoomwide);
	zoomOutDiv.setAttribute('title', mp_gmapL10n.zoomwide);
 	container.appendChild(zoomOutDiv);

  	GEvent.addDomListener(zoomOutDiv, 'click', function() {map.zoomOut();});

  	map.getContainer().appendChild(container);
  	return container;
}
OwnZoomOut.prototype.getDefaultPosition = function() 
{
	return new GControlPosition(G_ANCHOR_TOP_LEFT, new GSize(3, 22));
}

function OwnCenter() {}

OwnCenter.prototype = new GControl();
OwnCenter.prototype.initialize = function(map) 
{
	var container = document.createElement('div');

	var centerDiv = document.createElement('img');
	centerDiv.setAttribute('src', mp_gmapL10n.url+'centermap'+mp_gmapL10n.color+'.png');
	centerDiv.setAttribute('alt', mp_gmapL10n.center);
	centerDiv.setAttribute('title', mp_gmapL10n.center);
 	container.appendChild(centerDiv);

  	GEvent.addDomListener(centerDiv, 'click', function() {map.setCenter(point);});

  	map.getContainer().appendChild(container);
  	return container;
}
OwnCenter.prototype.getDefaultPosition = function() 
{
	return new GControlPosition(G_ANCHOR_TOP_RIGHT, new GSize(3, 3));
}

function OwnChangeMapType() {}

OwnChangeMapType.prototype = new GControl();
OwnChangeMapType.prototype.initialize = function(map) 
{
	var container = document.createElement('div');

	var changeMap = document.createElement('img');
	changeMap.setAttribute('src', mp_gmapL10n.url+'controlmap'+mp_gmapL10n.color+'.png');
	changeMap.setAttribute('alt', mp_gmapL10n.changemap);
	changeMap.setAttribute('title', mp_gmapL10n.changemap);
  	container.appendChild(changeMap);

  	GEvent.addDomListener(changeMap, 'click', function() 
	{
		switch (true)
		{
			case (G_NORMAL_MAP == map.getCurrentMapType()):
				map.setMapType(G_SATELLITE_MAP);
			break;
			case (G_SATELLITE_MAP == map.getCurrentMapType()):
				map.setMapType(G_HYBRID_MAP);
			break;
			case (G_HYBRID_MAP == map.getCurrentMapType()):
				map.setMapType(G_NORMAL_MAP);
			break;
		}
	});

  	map.getContainer().appendChild(container);
  	return container;
}
OwnChangeMapType.prototype.getDefaultPosition = function() 
{
	return new GControlPosition(G_ANCHOR_TOP_RIGHT, new GSize(3, 22));
}

function OwnWheelZoom(map, div) 
{
      GEvent.addDomListener(document.getElementById(div), "DOMMouseScroll",function(e) 
	{
		if (typeof e.preventDefault  == 'function') e.preventDefault();
		if (typeof e.stopPropagation == 'function') e.stopPropagation();
		if (e.detail)
		{
			if (e.detail < 0)			{ map.zoomIn(); }
			else if (e.detail > 0)		{ map.zoomOut(); }
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
			if (e.wheelDelta > 0)		{ map.zoomIn(); }
			else if (e.wheelDelta < 0)	{ map.zoomOut(); }
		}
	}); // IE
}