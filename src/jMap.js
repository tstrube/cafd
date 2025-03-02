///////////////////////
/// M A I N   M A P ///
///////////////////////

// Init Map
var href = window.location.href;
var zoomlevel = href.search('search.php');

var view = new ol.View({
		   center: ol.proj.transform([((zoomlevel > -1) ? 87 : 81), ((zoomlevel > -1) ? 33 : 35.5)], 'EPSG:4326', 'EPSG:3857'),
	   resolution: ((zoomlevel > -1) ? 10000 : 5000),
	minResolution: 0,
	maxResolution: 50000
});
var map = new ol.Map({
	controls: ol.control.defaults({
		attributionOptions: ({ collapsible: false })
	}).extend([
		new ol.control.ScaleLine()
	]),
    target: 'map',
	layers: [
		new ol.layer.Tile({
			source: new ol.source.BingMaps({
			   key: 'Aj6DIWfGOpAqw6zYpiqoOQ4UWwc-wDWOxOi1_HBnBsShQyGLCY49lxDebO6UXZiu',
        imagerySet: 'Road',
		   maxZoom: 19
      })
		})
	],
	view: view
});

// Init Spinner
var opts = {
  lines: 13 // The number of lines to draw
, length: 28 // The length of each line
, width: 14 // The line thickness
, radius: 42 // The radius of the inner circle
, scale: 1 // Scales overall size of the spinner
, corners: 1 // Corner roundness (0..1)
, color: '#000' // #rgb or #rrggbb or array of colors
, opacity: 0.25 // Opacity of the lines
, rotate: 0 // The rotation offset
, direction: 1 // 1: clockwise, -1: counterclockwise
, speed: 1 // Rounds per second
, trail: 60 // Afterglow percentage
, fps: 20 // Frames per second when using setTimeout() as a fallback for CSS
, zIndex: 2e9 // The z-index (defaults to 2000000000)
, className: 'spinner' // The CSS class to assign to the spinner
, top: '50%' // Top position relative to parent
, left: '50%' // Left position relative to parent
, shadow: false // Whether to render a shadow
, hwaccel: false // Whether to use hardware acceleration
, position: 'relative' // Element positioning
};
var spinner = new Spinner(opts);


/////////////////////////////
/// L O A D   F A U L T S ///
/////////////////////////////

var styleFaultCache = [];
var center = [];
var center_zoom;

// Load faults
var faultsLayer = new ol.layer.Vector({
	source: new ol.source.Vector({
		projection: 'EPSG:3857',
			   url: 'openlayers/ol_faults.php?id=' + getQueryVariable('id') + '&search=' + getQueryVariable('search'),
			format: new ol.format.GeoJSON()
	}),
	style: (function(feature, resolution) {
		var zoom_res = view.getResolution();

		if (zoom_res >= 2500)
			zoom_res = 2;
		else if (zoom_res < 500)
			zoom_res = 9;
		else if (zoom_res < 1000)
			zoom_res = 7;
		else if (zoom_res < 2500)
			zoom_res = 5;

		var highlight = 'h' + feature.get('highlight') + 'z' + zoom_res;

		if (feature.get('center')) {
			center = feature.get('center');
			center_zoom = feature.get('zoom');
		}

		if (feature.get('highlight') == 1)
			var rgba = 'rgba(0, 0, 0, 1.0)';
		else
			var rgba = 'rgba(255, 0, 0, 0.6)';

		if (!styleFaultCache[highlight])
			styleFaultCache[highlight] = [new ol.style.Style({
				stroke: new ol.style.Stroke({
					color: rgba,
					width: Math.max(2, Math.min(10, zoom_res))
				})
			})];

		return styleFaultCache[highlight];
	})
});
map.addLayer(faultsLayer);

// center current fault
faultsLayer.getSource().on('change', function(e) {
	setTimeout(function() {
		if (center.length > 0) {
			var ani_pan = new ol.animation.pan({
				duration: 1000,
				source: view.getCenter()
			});

			var ani_zoom = ol.animation.zoom({
				resolution: view.getResolution()
			});

			map.beforeRender(ani_pan, ani_zoom);

			view.setCenter(ol.proj.transform(center, 'EPSG:4326', 'EPSG:3857'));
       		view.setResolution(view.getResolution() * center_zoom);
		}
	}, 100);
});

/////////////////////////////
/// L O A D   Q U A K E S ///
/////////////////////////////

var styleCacheQuake = [];
var vectorSourceQuake;

function generateQuakeLayer(c, d, m) {
	vectorSourceQuake = new ol.source.Vector({
		projection: 'EPSG:3857',
		url: 'openlayers/ol_quakes.php?c=' + c + '&d=' + d + '&m=' + m,
		format: new ol.format.GeoJSON()
	});

	return new ol.layer.Vector({
		source: vectorSourceQuake,
		style: (function(feature, resolution) {
			var radius = feature.get('magnitude') < 5 ? 5 : 10;

			if (feature.get('data_source') == 'aftershock') {
				var rgb = '125, 255, 0';
				var id = radius + 1;
			} else if (feature.get('depth') > 70) {
				var rgb = '0, 190, 255';
				var id = radius + 2;
			} else {
				var rgb = '255, 150, 0';
				var id = radius + 3;
			}

			if (!styleCacheQuake[id])
				styleCacheQuake[id] = [new ol.style.Style({
					image: new ol.style.Circle({
						radius: radius,
						fill: new ol.style.Fill({
							color: 'rgba(' + rgb + ', 0.2)'
						}),
						stroke: new ol.style.Stroke({
							color: 'rgba(' + rgb + ', 1)',
							width: 1
						})
					})
				})];

			return styleCacheQuake[id];
		})
	});
}

/////////////////////////////
/// L O A D   S L I D E S ///
/////////////////////////////

var styleCacheSlide = [];
var vectorSourceSlide;

function generateSlideLayer(c, a) {
	 vectorSourceSlide = new ol.source.Vector({
		projection: 'EPSG:3857',
		url: 'openlayers/ol_slides.php?c=' + c + '&a=' + a,
		format: new ol.format.GeoJSON()
	});

	return new ol.layer.Vector({
		source: vectorSourceSlide,
		style: (function(feature, resolution) {
			var radius = feature.get('display_big') == 0 ? 5 : 10;

			if (feature.get('display_big') == 0) {
				var rgb = '200, 0, 100';
				var id = radius + 'c' + feature.get('count');
			} else {
				var rgb = '200, 0, 100';
				var id = radius + 'c' + feature.get('count');
			}

			if (!styleCacheSlide[id])
				styleCacheSlide[id] = [new ol.style.Style({
					image: new ol.style.Circle({
						radius: radius,
						points: 3,
						fill: new ol.style.Fill({
							color: 'rgba(' + rgb + ', ' + Math.min(feature.get('count') / 10, 1) + ')'
						}),
						stroke: new ol.style.Stroke({
							color: 'rgba(' + rgb + ', ' + Math.min(feature.get('count') / 10, 1) + ')',
							width: 1
						})
					})
				})];

			return styleCacheSlide[id];
		})
	});
}

/////////////////////////////////////////////////////////
/// L O A D   Q U A T E R N A R Y   S L I P   R A T E ///
/////////////////////////////////////////////////////////

var styleCacheQuatSlip = [];
var vectorSourceQuatSlip;

function generateQuatSlipLayer(c, a) {
	 vectorSourceQuatSlip = new ol.source.Vector({
		projection: 'EPSG:3857',
		url: 'openlayers/ol_quatslip.php',
		format: new ol.format.GeoJSON()
	});

	id = 0;

	return new ol.layer.Vector({
		source: vectorSourceQuatSlip,
		style: (function(feature, resolution) {
			if (!styleCacheQuatSlip[id])
				styleCacheQuatSlip[id] = [new ol.style.Style({
					image: new ol.style.Circle({
						radius: 10,
						fill: new ol.style.Fill({
							color: 'rgba(0, 0, 0, 0.5)'
						}),
						stroke: new ol.style.Stroke({
							color: 'rgba(0, 0, 0, 1)',
							width: 1
						})
					})
				})];

				return styleCacheQuatSlip[id];
			})
		});
	}

///////////////////////////////////////////
/// L O A D   Q U A K E - I N D U C E D ///
///////////////////////////////////////////

var styleCacheQuakeInduced = [];
var vectorSourceQuakeInduced;

function generateQuakeInducedLayer(c, a) {
	 vectorSourceQuakeInduced = new ol.source.Vector({
		projection: 'EPSG:3857',
		url: 'openlayers/ol_quakeInduced.php',
		format: new ol.format.GeoJSON()
	});

	id = 0;

	return new ol.layer.Vector({
		source: vectorSourceQuakeInduced,
		style: (function(feature, resolution) {
			if (!styleCacheQuakeInduced[id])
				styleCacheQuakeInduced[id] = [new ol.style.Style({
					image: new ol.style.Circle({
						radius: 10,
						fill: new ol.style.Fill({
							color: 'rgba(0, 0, 0, 0.5)'
						}),
						stroke: new ol.style.Stroke({
							color: 'rgba(0, 0, 0, 1)',
							width: 1
						})
					})
				})];

			return styleCacheQuakeInduced[id];
		})
	});
}

//////////////////////////////////////
/// L O A D   A L L    S L I D E S ///
//////////////////////////////////////

var styleCacheAllSlides = [];
var vectorSourceAllSlides;

function generateAllSlideLayers(c, a) {
	 vectorSourceAllSlides = new ol.source.Vector({
		projection: 'EPSG:3857',
		url: 'openlayers/ol_allslides.php',
		format: new ol.format.GeoJSON()
	});

	id = 0;

	return new ol.layer.Vector({
		source: vectorSourceAllSlides,
		style: (function(feature, resolution) {
			if (!styleCacheAllSlides[id])
				styleCacheAllSlides[id] = [new ol.style.Style({
					image: new ol.style.Circle({
						radius: 10,
						fill: new ol.style.Fill({
							color: 'rgba(0, 0, 0, 0.5)'
						}),
						stroke: new ol.style.Stroke({
							color: 'rgba(0, 0, 0, 1)',
							width: 1
						})
					})
				})];

			return styleCacheAllSlides[id];
		})
	});
}

/////////////////////
/// B U T T O N S ///
/////////////////////

$('button').on('click', function() {
    $('.' + this.className.split(" ")[0]).removeClass('selected');
    $(this).addClass('selected');
});

var quakeLayers 			= [];
var slideLayers 			= [];
var quakeInducedLayers 		= [];
var quaternaryLayers		= [];
var allslideLayers			= [];

var quake_mag 				= 0 // OLD : $("input[type='radio'][name='quake_mag']").val();
var quake_depth 			= 0 // OLD : $("input[type='radio'][name='quake_depth']:checked").val();

var slide_area 				= 0 // OLD : $("input[type='radio'][name='slide_area']:checked").val();

function selectionChanged() {
	var enableFaults 		= $("select#layer option:selected").val() == "faults";

	var enableQuake 		= $("select#layer option:selected").val() == "quake";
	var quake_catalogue 	= $("select#quake_catalogue option:selected").val();

	var enableSlide 		= $("select#layer option:selected").val() == "slide";
	var slide_catalogue 	= $("select#slide_catalogue option:selected").val();

	var enableQuakeInduced 	= $("select#layer option:selected").val() == "induced";

	var enableQuaternarySlip= $("select#layer option:selected").val() == "quatslip";

	var enableAllSlides 	= $("select#layer option:selected").val() == "allslides";

	// Show fault legend (faults are always visible)
	if (enableFaults) {
		$(".fault_buttons").css("display", "block");
	} else {
		$(".fault_buttons").css("display", "none");
	}

	// Show earthquakes
	if (enableQuake) {
		$(".quake_buttons").css("display", "block");
		showQuake(quake_catalogue, quake_mag, quake_depth);
	} else {
		$(".quake_buttons").css("display", "none");
		quakeLayers.forEach(function (val) {
			val.setVisible(false);
		});
	}

	// Show landslides
	if (enableSlide) {
		$(".slide_buttons").css("display", "block");
		showSlide(slide_catalogue, slide_area);
	} else {
		$(".slide_buttons").css("display", "none");
		slideLayers.forEach(function (val) {
			val.setVisible(false);
		});
	}

	// Show quake induced
	if (enableQuakeInduced) {
		$(".quakeInduced_buttons").css("display", "block");
		showQuakeInduced();
	} else {
		$(".quakeInduced_buttons").css("display", "none");
		quakeInducedLayers.forEach(function (val) {
			val.setVisible(false);
		});
	}

	// Show Quaternary Slip Rate
	if (enableQuaternarySlip) {
		$(".quatslip_buttons").css("display", "block");
		showQuaternary();
	} else {
		$(".quatslip_buttons").css("display", "none");
		quaternaryLayers.forEach(function (val) {
			val.setVisible(false);
		});
	}

	// Show all slides
	if (enableAllSlides) {
		$(".allslides_buttons").css("display", "block");
		showAllSlides();
	} else {
		$(".allslides_buttons").css("display", "none");
		allslideLayers.forEach(function (val) {
			val.setVisible(false);
		});
	}
}

function clickQuakeMag(v) {
	quake_mag = v
	selectionChanged()
}

function clickQuakeDepth(v) {
	quake_depth = v
	selectionChanged()
}

function clickSlideArea(v) {
	slide_area = v
	selectionChanged()
}

function showQuake(c, m, d) {
	quakeLayers.forEach(function (val) {
		val.setVisible(false);
	});

    if (c == 'tipage')
  		var c_nr = '1';
    else if (c == 'usgs')
    	var c_nr = '2';
	else if (c == 'ferghana')
		var c_nr = '3';

	if (!quakeLayers[c_nr + d + m]) {
		spinner.spin(document.getElementById('map_size'));

		quakeLayers[c_nr + d + m] = generateQuakeLayer(c, d, m);
		map.addLayer(quakeLayers[c_nr + d + m]);

		var listenerKey = vectorSourceQuake.on('change', function(e) {
			if (vectorSourceQuake.getState() == 'ready') {
				spinner.stop();
				ol.Observable.unByKey(listenerKey);
			}
		});

	} else
		quakeLayers[c_nr + d + m].setVisible(true);
}

function showSlide(c, a) {
	slideLayers.forEach(function (val) {
		val.setVisible(false);
	});

    if (c == 'Xu_etal_2015')
  		var c_nr = '1';

	if (!slideLayers[c_nr + a]) {
		spinner.spin(document.getElementById('map_size'));

		slideLayers[c_nr + a] = generateSlideLayer(c, a);
		map.addLayer(slideLayers[c_nr + a]);

		var listenerKey = vectorSourceSlide.on('change', function(e) {
			if (vectorSourceSlide.getState() == 'ready') {
				spinner.stop();
				ol.Observable.unByKey(listenerKey);
			}
		});

	} else
		slideLayers[c_nr + a].setVisible(true);
}

function showQuakeInduced() {
	quakeInducedLayers.forEach(function (val) {
		val.setVisible(false);
	});

	if (!quakeInducedLayers[0]) {
		spinner.spin(document.getElementById('map_size'));

		quakeInducedLayers[0] = generateQuakeInducedLayer();
		map.addLayer(quakeInducedLayers[0]);

		var listenerKey = vectorSourceQuakeInduced.on('change', function(e) {
			if (vectorSourceQuakeInduced.getState() == 'ready') {
				spinner.stop();
				ol.Observable.unByKey(listenerKey);
			}
		});

	} else
		quakeInducedLayers[0].setVisible(true);
}

function showAllSlides() {
	allslideLayers.forEach(function (val) {
		val.setVisible(false);
	});

	if (!allslideLayers[0]) {
		spinner.spin(document.getElementById('map_size'));

		allslideLayers[0] = generateAllSlideLayers();
		map.addLayer(allslideLayers[0]);

		var listenerKey = vectorSourceAllSlides.on('change', function(e) {
			if (vectorSourceAllSlides.getState() == 'ready') {
				spinner.stop();
				ol.Observable.unByKey(listenerKey);
			}
		});

	} else
		allslideLayers[0].setVisible(true);
}

function showQuaternary() {
	quaternaryLayers.forEach(function (val) {
		val.setVisible(false);
	});

	if (!quaternaryLayers[0]) {
		spinner.spin(document.getElementById('map_size'));

		quaternaryLayers[0] = generateQuatSlipLayer();
		map.addLayer(quaternaryLayers[0]);

		var listenerKey = vectorSourceQuatSlip.on('change', function(e) {
			if (vectorSourceQuatSlip.getState() == 'ready') {
				spinner.stop();
				ol.Observable.unByKey(listenerKey);
			}
		});

	} else
		quaternaryLayers[0].setVisible(true);
}

/////////////////
/// P O P U P ///
/////////////////

// Add tooltip functionality
var tooltip = document.getElementById('tooltip');
var overlay_tooltip = new ol.Overlay({ element: tooltip });
map.addOverlay(overlay_tooltip);

// Add infobox functionality
var infobox = document.getElementById('infobox');
var overlay_infobox = new ol.Overlay({ element: infobox });
map.addOverlay(overlay_infobox);

// Generate Info Box
var displayFeatureInfo = function(pixel, coords) {
	var feature = getFeature(pixel);

	if (feature && feature.get('q_slip_rate') !== undefined) {
		tooltip.style.display = 'block';
		overlay_tooltip.setPosition([coords[0] + view.getResolution() * 5, coords[1]]);
		tooltip.innerHTML = 'Fault name: ' + feature.get('fault_name') + '<br />' +
							'Quaternary slip rate (mm/yr): ' + feature.get('q_slip_rate') + '<br />' +
							'Method: ' + feature.get('method') + '<br />' +
							'References: ' + feature.get('reference');
		$("#map").css("cursor", "pointer");

	} else if (feature && feature.get('fault_id') !== undefined) {
		tooltip.style.display = 'block';
		overlay_tooltip.setPosition([coords[0] + view.getResolution() * 5, coords[1]]);
		tooltip.innerHTML = feature.get('name');
		$("#map").css("cursor", "pointer");

	} else if (feature && feature.get('slide_count') !== undefined) {
		tooltip.style.display = 'block';
		overlay_tooltip.setPosition([coords[0] + view.getResolution() * 5, coords[1]]);
		tooltip.innerHTML = feature.get('name');
		$("#map").css("cursor", "pointer");

	} else {
		tooltip.style.display = 'none';
		$("#map").css("cursor", "move");
	}
};

// Handle mouse clicks
var mouseClickHandler = function(pixel, coords) {
	var feature = getFeature(pixel);
	if (feature && feature.get('fault_id') > 0) {
		window.location = "view.php?id=" + feature.get('fault_id');

	} else if (feature && feature.get('slide_count') !== undefined) {
		infobox.style.display = 'block';
		overlay_infobox.setPosition([coords[0] + view.getResolution() * 5, coords[1]]);
		infobox.innerHTML = '<b>' + feature.get('name') + '</b><br />' +
								'Magnitude: ' + feature.get('magnitude') + '<br />' +
								'Date: ' + feature.get('time') + '<br />' +
								'<a href="' + feature.get('download') + '" target="_blank">Download data</a>';

	} else {
		infobox.style.display = 'none';
	}
};

// getFeature
var getFeature = function(pixel) {
	return map.forEachFeatureAtPixel(pixel, function(feature, layer) {
		return feature;
	});
};

// Track mouse movement
map.on('pointermove', function(evt) {
	if (evt.dragging)
		tooltip.style.display = 'none';
	else
		displayFeatureInfo(map.getEventPixel(evt.originalEvent), evt.coordinate);

  	// Display coordinates
	var latlon = ol.proj.transform(evt.coordinate, 'EPSG:3857', 'EPSG:4326');
	document.getElementById('latlon').innerHTML = '<span style="font-size: 10px;">Latitude: ' + latlon[1].toFixed(4) + '&nbsp;&nbsp;&nbsp;Longitude: ' + latlon[0].toFixed(4) + '</span>';
});

// Track mouse clicks
map.on('click', function(evt) {
	mouseClickHandler(map.getEventPixel(evt.originalEvent), evt.coordinate);
});

// Helper
function getQueryVariable(variable)
{
	   var query = window.location.search.substring(1);
	   var vars = query.split("&");
	   for (var i=0;i<vars.length;i++) {
	           var pair = vars[i].split("=");
	           if(pair[0] == variable){return pair[1];}
	   }
	   return(false);
}
