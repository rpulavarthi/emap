OpenLayers.ImgPath = "global/img/ol/";
OpenLayers.Strategy.BBOX.prototype.triggerRead = 
function() {
    if (this.response) {
        this.layer.protocol.abort(this.response);
        this.layer.events.triggerEvent("loadend");
    }
    this.layer.events.triggerEvent("loadstart");
    this.response = this.layer.protocol.read({
        filter: this.createFilter(),
        callback: this.merge,
        scope: this,
        params: {zoom: this.layer.map.zoom}
    });
};

var isElevationToolActive = false;
var isPolygonToolActive = false;
var isStreetViewToolActive = false;
var isTerrainProfileToolActive = false;
var isImportToolActive = false;
var isBirdsEyeToolActive = false;
var viewparams;
var enabledMapLabel = "Site ID";
var enabledMapRenderer = "none";
var enabledMapRendererText = "";
var controls_fieldtech;
var active_control_fieldtech = "none_fieldtech";
var vectors_fieldtech;
// Load the Visualization API and the piechart package.
google.load("visualization", "1", {packages: ["columnchart"]});

function init(){
	//Set Cluster Zoom Level to mapping server
	// $.ajax({
        //                type: "POST",
        //                url: host + "geo/user/setclusterzoomlevel",
        //                data: user
        //        });

	//terrain Profile
	//var chart_terrain_profile = new google.visualization.ColumnChart(document.getElementById('chart_terrain_profile'));  
	var chart_terrain_profile = new google.visualization.ColumnChart(document.getElementById('coltools'));

	//map options
	var options = {
			controls: [ new OpenLayers.Control.Navigation(), 
			new OpenLayers.Control.PanZoomBar({position: new OpenLayers.Pixel( 130, 36)}),
                ],
			projection: new OpenLayers.Projection("EPSG:900913"),
			displayProjection: new OpenLayers.Projection("EPSG:4326"),
			units: "m",
			maxResolution: 156543.0339,
			theme: "global/css/ol/theme/default/style.css",
			eventListeners: { "zoomend": zoomEnd },
			maxExtent: new OpenLayers.Bounds(-20037508.34, -20037508.34,
											 20037508.34, 20037508.34)
	};
	
	//init map
	map = new OpenLayers.Map('map', options);
	
	//load basemaps
	var baseLayers = [];
	
	baseLayers.push( new OpenLayers.Layer.Google(
        "Google Streets", // the default
        {numZoomLevels: 20}
    ));
	
	baseLayers.push( new OpenLayers.Layer.Google(
        "Google Terrain",
        {type: google.maps.MapTypeId.TERRAIN}
    ));
	
	baseLayers.push( new OpenLayers.Layer.Google(
        "Google Satellite",
        {type: google.maps.MapTypeId.HYBRID, numZoomLevels: 20}
    ));
  
	baseLayers.push( new OpenLayers.Layer.Bing({
		key: 'AqTGBsziZHIJYYxgivLBf0hVdrAk9mWO5cQcb8Yux8sW5M8c8opEC2lZqKR1ZZXf',
		type: "Road", name: 'Bing Road',
		metadataParams: {mapVersion: "v1"}
	}));
	baseLayers.push(new OpenLayers.Layer.Bing({
		key: 'AqTGBsziZHIJYYxgivLBf0hVdrAk9mWO5cQcb8Yux8sW5M8c8opEC2lZqKR1ZZXf',
		type: "AerialWithLabels",
		name: "Bing Aerial"
	}));
	var bingmap = baseLayers.push(new OpenLayers.Layer.Bing({
		type: "Birdseye",
		name: "Bing Birds Eye"
	}));

	baseLayers.push(new OpenLayers.Layer.OSM("Open Street Map"));
	
	//Add basemaps to map
	map.addLayers(baseLayers);
	 
	//Set default base map
	for (l in baseLayers){
		if(baseLayers[l].name == user.default_basemap){
			map.setBaseLayer(baseLayers[l]);
			enabledBaseLayer = user.default_basemap;
			$('ul[type="base-layers"] > li').each(function(index){
				if($.trim($(this).text()) == user.default_basemap)
					$(this).toggleClass("active");
			});
			break;
		}	
	}
	
	if(user.client == "Sprint-DR")
	{
		$("#colright").html("<iframe style=\"background:white;width:100%;height:100%\" src='http://138.85.245.145/geo/legend'></iframe>");
	}
	
	$('ul[type="map-Labels"] > li').each(function(index){
				if($.trim($(this).text()) == enabledMapLabel)
					$(this).toggleClass("active");
			});
	
	$('ul[type="map-Renderer"] > li').each(function(index){
				if($.trim($(this).children("a").attr("name")) == enabledMapRenderer)
					$(this).toggleClass("active");
			});
     
     var renderer = OpenLayers.Util.getParameters(window.location.href).renderer;
                renderer = (renderer) ? [renderer] : OpenLayers.Layer.Vector.prototype.renderers;
	 vectors_fieldtech = new OpenLayers.Layer.Vector("Field Tech - Vector Layer", {
                    renderers: renderer
                });
	vectors_fieldtech.events.on({
                    "afterfeaturemodified": report_fieldtech,
                    "sketchcomplete": report_fieldtech
                });
     map.addLayer(vectors_fieldtech);
	 controls_fieldtech = {
                    point_fieldtech: new OpenLayers.Control.DrawFeature(vectors_fieldtech,
                                OpenLayers.Handler.Point),
                    line_fieldtech: new OpenLayers.Control.DrawFeature(vectors_fieldtech,
                                OpenLayers.Handler.Path),
                    polygon_fieldtech: new OpenLayers.Control.DrawFeature(vectors_fieldtech,
                                OpenLayers.Handler.Polygon),
					modify_fieldtech: new OpenLayers.Control.ModifyFeature(vectors_fieldtech)
                };

                for(var key in controls_fieldtech) {
                    map.addControl(controls_fieldtech[key]);
                }
	controls_fieldtech.modify_fieldtech.mode |= OpenLayers.Control.ModifyFeature.RESHAPE;
	controls_fieldtech.modify_fieldtech.mode |= OpenLayers.Control.ModifyFeature.ROTATE;
	controls_fieldtech.modify_fieldtech.mode |= OpenLayers.Control.ModifyFeature.RESIZE;
	controls_fieldtech.modify_fieldtech.mode |= OpenLayers.Control.ModifyFeature.DRAG;
	//Set default layer if set
	//if(user.default_layer != '' && user.default_layer != null){
	//	var default_layer_array = user.default_layer.split(" - ");
	//	toggleLayer(default_layer_array[0], default_layer_array[1]);
	//	enabledLayer.group = default_layer_array[0]
	//	enabledLayer.layer = default_layer_array[1];
	//	$('li[layer="' +  enabledLayer.group +'"][group="' + enabledLayer.layer +'"]').toggleClass("active");
	//}
	
	// Create an ElevationService and StreetView
	var elevator = new google.maps.ElevationService();

    var panoramaOptions = { pov: { heading: 34, pitch: 10, zoom: 1 } };
	var streetview = new google.maps.StreetViewPanorama(document.getElementById('coltoolstreet'), panoramaOptions);
	streetview.setVisible(false);
	
	//Add markers layer
	var markers = new OpenLayers.Layer.Markers( "Markers" );
    map.addLayer(markers);
	
	//Measure Ruler 
	// style the sketch fancy
	var sketchSymbolizers = {
		"Point": {
			pointRadius: 4,
			graphicName: "square",
			fillColor: "white",
			fillOpacity: 1,
			strokeWidth: 1,
			strokeOpacity: 1,
			strokeColor: "#333333"
		},
		"Line": {
			strokeWidth: 3,
			strokeOpacity: 1,
			strokeColor: "#666666",
			strokeDashstyle: "dash"
		},
		"Polygon": {
			strokeWidth: 2,
			strokeOpacity: 1,
			strokeColor: "#666666",
			fillColor: "white",
			fillOpacity: 0.3
		}
	};
	var style = new OpenLayers.Style();
	style.addRules([
		new OpenLayers.Rule({symbolizer: sketchSymbolizers})
	]);
	var styleMap = new OpenLayers.StyleMap({"default": style});
	  // allow testing of specific renderers via "?renderer=Canvas", etc
	var renderer = OpenLayers.Util.getParameters(window.location.href).renderer;
	renderer = (renderer) ? [renderer] : OpenLayers.Layer.Vector.prototype.renderers;

	var measure = new OpenLayers.Control.Measure(
		OpenLayers.Handler.Path, {
			persist: true,
			handlerOptions: {
				layerOptions: {
					renderers: renderer,
					styleMap: styleMap
				}
			}
		}
    );
	measure.geodesic = true;		
	measure.events.on({
		"measure": handleMeasurements
	});
	map.addControl(measure);
	
	polyStyle = new OpenLayers.StyleMap({
		    "default": new OpenLayers.Style({
	    	strokeColor: polygonColor,
	    	strokeWidth: 1,
	    	strokeOpacity: 0.8,
	    	fillColor: polygonColor,
	    	fillOpacity: 0.3
    	})
	});

	polyVector = new OpenLayers.Layer.Vector("Polygon vector", { styleMap: polyStyle } );
	polyVector.styleMap = polyStyle;
	map.addLayer(polyVector);
	map.addControl(new OpenLayers.Control.MousePosition());

	var poly = new OpenLayers.Control.DrawFeature(
		polyVector,
		OpenLayers.Handler.Polygon, {
			persist: true,
			handlerOptions: {
//				freehand: true,
//				holeModifier: "altKey"
			},
		}
	);
	poly.events.on( {
		"featureadded": handlePoly
	} );
	map.addControl(poly);

	 $("#list").jqGrid({
                //url:'geo/grid/Sprint:V2R2_CDMA?lat=33',
                datatype: 'json',
                mtype: 'GET',
                colModel :[
                  {name:'Site', index:'key', width:150},
                  {name:'Switch Cell', index:'switch_cell', width:150},
                  {name:'latitude', index:'latitude', hidden:true},
                  {name:'longitude', index:'longitude', hidden:true},
        //        {name:'PN', index:'key', width:80, align:'right'},
        //        {name:'Carrier Count', index:'carrier_count', width:80, align:'right'},
                  {name:'Address', index:'total', align:'right', width:900}
                ],
                pager: '#pager',
                height:200,
                rowNum:10,
                sortname: 'key',
                sortorder: 'desc',
                viewrecords: true,
                gridview: true,
                onSelectRow: function (rowId, status, event) {
                    if (!event || event.which === 1) {
                    	//zoom to lat lng 
                    	var mapZoomLevel = 18;
                    	map.setCenter(new OpenLayers.LonLat($(this).jqGrid('getCell', rowId, 'longitude'), $(this).jqGrid('getCell', rowId, 'latitude')).transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject()), mapZoomLevel);
                		map.zoom = mapZoomLevel;
                    	if(isBirdsEyeToolActive) {
                			var mapEventCenter = map.getCenter();
                			mapEventCenter.transform(map.projection, new OpenLayers.Projection("EPSG:4326"));
                			var mapEventLat = mapEventCenter.lat;
                			var mapEventLon = mapEventCenter.lon;
                			bingmap.setView( { center: new Microsoft.Maps.Location(mapEventLat, mapEventLon) } );
                			bingmap.setView( { zoom: mapZoomLevel } );
                		}
                    }
                }
        });
        $("#list").jqGrid('navGrid','#pager',{edit:false,add:false,del:false});
        
	//zoom to user's home
	map.setCenter(new OpenLayers.LonLat(user.home_longitude, user.home_latitude).transform(new OpenLayers.Projection("EPSG:4326"), map.projection), user.home_zoom);
	 //Set default layer if set
        //if(user.default_layer != '' && user.default_layer != null){
          //      var default_layer_array = user.default_layer.split(" - ");
            //    toggleLayer(default_layer_array[0], default_layer_array[1]);
              //  enabledLayer.group = default_layer_array[0]
                //enabledLayer.layer = default_layer_array[1];
           //     $('li[layer="' +  enabledLayer.group +'"][group="' + enabledLayer.layer +'"]').toggleClass("active");
       // }
 
	function zoomEnd() {
		var mapEventCenter = map.getCenter();
		mapEventCenter.transform(map.projection, new OpenLayers.Projection("EPSG:4326"));
		var mapEventLat = mapEventCenter.lat;
		var mapEventLon = mapEventCenter.lon;
		if(isBirdsEyeToolActive) {
			bingmap.setView( { zoom: map.getZoom() } );
		}
	}
	
	//zoom end event
	map.events.register("zoomend", map, function (e) {
		var mapEventCenter = map.getCenter();
		mapEventCenter.transform(map.projection, new OpenLayers.Projection("EPSG:4326"));
		var mapEventLat = mapEventCenter.lat;
		var mapEventLon = mapEventCenter.lon;
	});
	
	//move end event
	map.events.register("moveend", null, function() {
		//update search boundary
		
		var bb = map.getExtent().transform( map.projection, new OpenLayers.Projection("EPSG:4326"));
		var p = bb.getCenterLonLat();
		
		$( "#search" ).combogrid( "option", "bottom", bb.bottom);
		$( "#search" ).combogrid( "option", "top", bb.top);
		$( "#search" ).combogrid( "option", "left", bb.left);
		$( "#search" ).combogrid( "option", "right", bb.right);
		$( "#search" ).combogrid( "option", "centerLat", p.lat );
		$( "#search" ).combogrid( "option", "centerLng", p.lon );
		//console.log(map.getZoom());
		updateSiteGrid();
		
		//update attribute data
		//var url = jQuery('#list').jqGrid('getGridParam','url');
		//jQuery("#list").jqGrid('setGridParam',{url:url.substring(0, url.indexOf("?")) + "?minx=" + bb.bottom + "&miny=" + bb.left + "&maxx=" + bb.top + "&maxy=" + bb.right}).trigger("reloadGrid");
		//jQuery("#list").jqGrid('setGridParam',{url:grid_base_url + "?minx=" + bb.bottom + "&miny=" + bb.left + "&maxx=" + bb.top + "&maxy=" + bb.right}).trigger("reloadGrid");
	});
	
	//update mouse coords to screen
	map.events.register("mousemove", map, function (e) {
		var position = map.getLonLatFromViewPortPx(e.xy).transform( map.projection, new OpenLayers.Projection("EPSG:4326"));
		//round to 1/1000
		$('#latitude_mouse').text('lat: ' + Math.round(position.lat * 10000) / 10000);
		$('#longitude_mouse').text('lng: ' + Math.round(position.lon * 10000) / 10000);
		if(isBirdsEyeToolActive) {
			var mapEventCenter = map.getCenter();
			mapEventCenter.transform(map.projection, new OpenLayers.Projection("EPSG:4326"));
			var mapEventLat = mapEventCenter.lat;
			var mapEventLon = mapEventCenter.lon;
			var intvalue = Math.floor( map.getZoom() );
			bingmap.setView( { center: new Microsoft.Maps.Location(mapEventLat, mapEventLon) } );
			bingmap.setView( { zoom: intvalue } );
			//console.log('OL zoom level: ' + map.getZoom() + ', MS zoom level: ' + bingmap.getZoom() + ', intval: ' + intvalue);
		}
	});
	
	map.events.register("click", map , function(e) {
	
		var lonlat = map.getLonLatFromPixel(e.xy);
		//check if tools are enabled

		//elevation
		if(isElevationToolActive) {
			//convert to  wgs84 lat / lng
			var wgs84_latlng = lonlat.clone();
			wgs84_latlng.transform( map.projection, new OpenLayers.Projection("EPSG:4326"))
			var clickedLocation = new google.maps.LatLng(wgs84_latlng.lat, wgs84_latlng.lon);
			var locations = [];
			locations.push(clickedLocation);
			var positionalRequest = {
				'locations': locations
			}

			//send request
			elevator.getElevationForLocations(positionalRequest, function(results, status) {
				if (status == google.maps.ElevationStatus.OK) {
					if (results[0]) {
						var popup = new OpenLayers.Popup("chicken",
							lonlat,
							new OpenLayers.Size(150,20),
							"<div style='font-size:.8em'>Elevation: " + Math.round(results[0].elevation * 3.28084 * 100)/100 +" feet</div>",
							true
						);
						//add popup to map
						map.addPopup(popup);
					}
				}
			});
		}
		
		//show street view
		if(isStreetViewToolActive){
			var wgs84_latlng = lonlat.clone();
			wgs84_latlng.transform( map.projection, new OpenLayers.Projection("EPSG:4326"))		
			var theloc = new google.maps.LatLng(wgs84_latlng.lat,wgs84_latlng.lon);
		
			//check if pano exist for clicked lat/lng
			var streetViewCheck = new google.maps.StreetViewService();  
			streetViewCheck.getPanoramaByLocation(theloc, 50, function(data, status) {
			 console.log(status);
				if (status == google.maps.StreetViewStatus.ZERO_RESULTS)
					streetview.setVisible(false);
				else {
					//$('#streetviewModal').modal('show');
					streetview.setPosition(theloc);
					if(userServer == '127.0.0.1') {
						iframeSrcStr = "http://127.0.0.1/emap/protected/helpers/streetview.php?lat=" + wgs84_latlng.lat + "&lon=" + wgs84_latlng.lon;
					} else {
						iframeSrcStr = "http://evas.ericsson.net/emap/protected/helpers/streetview.php?lat=" + wgs84_latlng.lat + "&lon=" + wgs84_latlng.lon;
					}
					document.getElementById("coltoolstreet").src = iframeSrcStr;
					//alert(document.getElementById("coltoolstreet").src);
					$("#coltoolstreet").show();
					$("#coltoolstreet").animate({ right: "0px" }, 400);
					streetview.setVisible(true);
				}
			});
		}
		
	});
	
	//Add Left Panel
	var leftPanelCollapsed = true;
	var panelLeft = new OpenLayers.Control.Panel({ displayClass: "showHideLeftPanel", id: "leftPanelTabs" });
    map.addControl(panelLeft);
    panelLeft.addControls(new OpenLayers.Control.Button({ displayClass: "toggleLayers", trigger: ExpandCollapseLeftPanel }));	


	//Hide then expand layers
	$("#colleft").hide();
	ExpandCollapseLeftPanel();
	ExpandCollapseLeftPanel();
	
	 $("#coltools").animate({ right: "-600px" }, 400)
	 $("#coltools").hide();
	 $("#coltoolstreet").animate({ right: "-600px" }, 400)
	 $("#coltoolstreet").hide();
	 	
	//Add Right Panel
	var rightPanelCollapsed = true;
	var panelRight = new OpenLayers.Control.Panel({ displayClass: "showHideRightPanel", id: "rightPanelTabs" });
    map.addControl(panelRight);
    panelRight.addControls(new OpenLayers.Control.Button({ displayClass: "toggleLegend", trigger: ExpandCollapseRightPanel }));	
	$("#colright").hide();
	ExpandCollapseRightPanel();
	ExpandCollapseRightPanel();
	
	
	//Click handlers
	$(".layerText").click(updateLayers);
	
	//Contact Menu
	$("#menu-contact").click(function(){ 
		$('#contactModal').modal('show');
	});

	//Home menue
	$("#menu-home").click(function(){ 
		map.setCenter(new OpenLayers.LonLat(user.home_longitude, user.home_latitude).transform(new OpenLayers.Projection("EPSG:4326"), map.projection), user.home_zoom);
	});
	
	//Profile Menu
	$("#profile-settings").click(function(){ 
		//set user's curr ops
		$("#default-basemap").val(user.default_basemap);
		$("#default-layer").val(user.default_layer);
		$("#cluster-zoom").val(user.cluster_zoom_level);

		//show
		$('#profile-modal').modal('show');
	});
	
	$("#get-viewport").click(function(){ 
		var center = map.getCenter();
		center.transform(map.projection, new OpenLayers.Projection("EPSG:4326"));
		console.log(center);
		user.home_latitude = center.lat;
		user.home_longitude = center.lon;
		user.home_zoom = map.getZoom();
		
		$("#get-viewport-msg").text("Updated! Don't forget to click Save Changes!");
	});
	
	
	//Profile modal submit
	$("#profile-modal-save").click(function(){ 
		//save variables
		user.cluster_zoom_level = $("#cluster-zoom").val();
		user.default_basemap = $("#default-basemap").val();
		user.default_layer = $("#default-layer").val();
		user.change_password_on_login = $("#change-password").attr('checked') ==  undefined ? 0:1;
		user.client = $("#client").val();
			
		//send ajax
		$.ajax({
			type: "POST",
			url: "users",
			data: user,
			success: function(response){
				if(response.success){
					$("#profile-modal").modal('hide');
				}else{
					alert('Error Saving Data!');
				}
			}
		});
	});
	
	//Profile modal close
	$("#profile-modal-close").click(function(){ 
		$("#profile-modal").modal('hide');
	});
	
	//Log out 
	$("#profile-logout").click(function(){ 
		$.ajax({
			type: "POST",
			url: "auth/logout",
			success: function(response){
				//redirect
				window.location  = "/emap";
			}
		});
	});

	


	//Attributes
    bottomPanelCollapsed = true;
	$("#site-attributes-li").click(function(){ 
		$('#site-attributes').show();
		$('#kpi-menu').hide();
		$('#parameter-menu').hide();
		$('#alarm-menu').hide();
		$('#rules-menu').hide();
		$('#neighbors-menu').hide();
		$('#nlissues-menu').hide();
		$('#kpic-menu').hide();
		$('#kpid-menu').hide();
		$('#kpicd-menu').hide();
		$('#drive-test-menu').hide();
		$('#csl-nlt-menu').hide();
		$('#geow-menu').hide();
		if(!bottomPanelCollapsed) {
			cleanBackgroundColor();
			$("#site-attributes-li").css('backgroundColor', '#fff');
		}
	});

	$("#site-attributes-li").dblclick(function(){
		cleanBackgroundColor();
		if(bottomPanelCollapsed) {
			$("#site-attributes-li").css('backgroundColor', '#fff');
		}
		toggleBottomPanel();
	});

	$("#csl-nlt-menu-li").click(function(){ 
		$('#site-attributes').hide();
		$('#kpi-menu').hide();
		$('#parameter-menu').hide();
		$('#alarm-menu').hide();
		$('#rules-menu').hide();
		$('#neighbors-menu').hide();
		$('#nlissues-menu').hide();
		$('#kpic-menu').hide();
		$('#kpid-menu').hide();
		$('#kpicd-menu').hide();
		$('#drive-test-menu').hide();
		$('#csl-nlt-menu').show();
		$('#geow-menu').hide();
//		viewparams = undefined ;
		if(!bottomPanelCollapsed) {
			cleanBackgroundColor();
			$("#csl-nlt-menu-li").css('backgroundColor', '#fff');
		}
	 	var bb = map.getExtent().transform( map.projection, new OpenLayers.Projection("EPSG:4326"));
				
 	    jQuery("#csl-nlt-list").jqGrid('setGridParam',{url:"http://138.85.245.136/geo/grid/Sprint:EVDO_NA?type=csl-nlt-list&minx=" + bb.bottom + "&miny=" + bb.left + "&maxx=" + bb.top + "&maxy=" + bb.right}).trigger("reloadGrid");		
	});

	$("#csl-nlt-menu-li").dblclick(function(){
		cleanBackgroundColor();
		if(bottomPanelCollapsed) {
			$("#csl-nlt-menu-li").css('backgroundColor', '#fff');
		}
		
		toggleBottomPanel();
	 	var bb = map.getExtent().transform( map.projection, new OpenLayers.Projection("EPSG:4326"));

	 	jQuery("#csl-nlt-list").jqGrid('setGridParam',{url:"http://138.85.245.136/geo/grid/Sprint:EVDO_NA" + "?type=csl-nlt-list&minx=" + bb.bottom + "&miny=" + bb.left + "&maxx=" + bb.top + "&maxy=" + bb.right}).trigger("reloadGrid");		
	});

	$('#csl-failure-color-1').colorpicker();
	$('#csl-failure-color-5').colorpicker();
	$('#csl-failure-color-6').colorpicker();
	$('#csl-failure-color-7').colorpicker();
	$('#csl-failure-color-8').colorpicker();
	$('#csl-failure-color-9').colorpicker();
	
    $("#csl-nlt-submit").click(function(){
    	var pattern = /ecio/i; 
    	if($("select#csl-nlt-tech-combo").val() == "CSL") {
    		if(pattern.test($("select#csl-parameter").val())) {
        		window.open("http://138.85.245.145/Reporting/cell_chart/cell_chart.swf"
        				+"?BSC="+csl_nlt_list_bsc_selected
        				+"&CELL="+csl_nlt_list_cell_selected
        				+"&SECTOR="+csl_nlt_list_sector_selected
        				+"&BAND="+csl_nlt_list_band_selected
        				+"&TIME="+$("input#csl-nlt-datepicker").val()
        				+"&LAT="+csl_nlt_list_lat_selected
        				+"&LNG="+csl_nlt_list_lng_selected);
    		} else {
        		window.open("http://138.85.245.145/Reporting/cell_chart/cell_chart.swf"
        				+"?BSC="+csl_nlt_list_bsc_selected
        				+"&CELL="+csl_nlt_list_cell_selected
        				+"&SECTOR="+csl_nlt_list_sector_selected
        				+"&BAND="+csl_nlt_list_band_selected
        				+"&TIME="+$("input#csl-nlt-datepicker").val()
        				+"&LAT="+csl_nlt_list_lat_selected
        				+"&LNG="+csl_nlt_list_lng_selected);
    		}
    	} else {
    		window.open("http://138.85.245.150/nlta/?id="+csl_nlt_list_ebid_selected+"&bsc="+csl_nlt_list_bsc_selected); 
    	}
   });

	
	$('#cp11').colorpicker(); $('#cp12').colorpicker(); $('#cp13').colorpicker();$('#cp14').colorpicker();$('#cp15').colorpicker();
	$('#cp21').colorpicker(); $('#cp22').colorpicker(); $('#cp23').colorpicker();$('#cp24').colorpicker();$('#cp25').colorpicker();
	$('#cp31').colorpicker(); $('#cp32').colorpicker(); $('#cp33').colorpicker();$('#cp34').colorpicker();$('#cp35').colorpicker();
	$('#tdcp0').colorpicker();$('#tdcp1').colorpicker(); $('#tdcp2').colorpicker(); $('#tdcp3').colorpicker();$('#tdcp4').colorpicker();$('#tdcp5').colorpicker();$('#tdcp6').colorpicker();

	$("#kpi-datepicker-control1").datepicker();
	$("#kpi-datepicker-control2").datepicker();

	$('#cpcd11').colorpicker(); $('#cpcd12').colorpicker(); $('#cpcd13').colorpicker();$('#cpcd14').colorpicker();$('#cpcd15').colorpicker();

	$("#kpicd-datepicker-control1").datepicker();
	$("#kpicd-datepicker-control2").datepicker();

	//range kpicd1
	$('#tcd12').change(function() {
		$('#tcd13').val($(this).val());
	});
	$('#tcd13').change(function() {
		$('#tcd12').val($(this).val());
	});
	$('#tcd14').change(function() {
		$('#tcd15').val($(this).val());
	});
	$('#tcd15').change(function() {
		$('#tcd14').val($(this).val());
	});
	$('#tcd16').change(function() {
		$('#tcd17').val($(this).val());
	});
	$('#tcd17').change(function() {
		$('#tcd16').val($(this).val());
	});
	$('#tcd18').change(function() {
		$('#tcd19').val($(this).val());
	});
	$('#tcd19').change(function() {
		$('#tcd18').val($(this).val());
	});
	
	//range kpi1
	$('#t12').change(function() {
		$('#t13').val($(this).val());
	});
	$('#t13').change(function() {
		$('#t12').val($(this).val());
	});
	$('#t14').change(function() {
		$('#t15').val($(this).val());
	});
	$('#t15').change(function() {
		$('#t14').val($(this).val());
	});
	$('#t16').change(function() {
		$('#t17').val($(this).val());
	});
	$('#t17').change(function() {
		$('#t16').val($(this).val());
	});
	$('#t18').change(function() {
		$('#t19').val($(this).val());
	});
	$('#t19').change(function() {
		$('#t18').val($(this).val());
	});
	
	//range kpi2
	$('#t22').change(function() {
		$('#t23').val($(this).val());
	});
	$('#t23').change(function() {
		$('#t22').val($(this).val());
	});
	$('#t24').change(function() {
		$('#t25').val($(this).val());
	});
	$('#t25').change(function() {
		$('#t24').val($(this).val());
	});
	$('#t26').change(function() {
		$('#t27').val($(this).val());
	});
	$('#t27').change(function() {
		$('#t26').val($(this).val());
	});
	$('#t28').change(function() {
		$('#t29').val($(this).val());
	});
	$('#t29').change(function() {
		$('#t28').val($(this).val());
	});
	
	//range kpi3
	$('#t32').change(function() {
		$('#t33').val($(this).val());
	});
	$('#t33').change(function() {
		$('#t32').val($(this).val());
	});
	$('#t34').change(function() {
		$('#t35').val($(this).val());
	});
	$('#t35').change(function() {
		$('#t34').val($(this).val());
	});
	$('#t36').change(function() {
		$('#t37').val($(this).val());
	});
	$('#t37').change(function() {
		$('#t36').val($(this).val());
	});
	$('#t38').change(function() {
		$('#t39').val($(this).val());
	});
	$('#t39').change(function() {
		$('#t38').val($(this).val());
	});
	
	$("#kpid-range-submit").click(function(){ 

		//get vars
		var kpi = {};
		kpi.date1 = $("#kpi-datepicker1").val();
		kpi.tw1 = $("#tw1-combo").val();
		kpi.date2 = $("#kpi-datepicker2").val();
		kpi.tw2 = $("#tw2-combo").val();
	
		kpi.tech = $("#tech-combod").val();
		
		kpi.kpi1 = $("#kpi1").val();
		kpi.kpi2 = $("#kpi2").val();
		kpi.kpi3 = $("#kpi3").val();
		
		vp = "type:kpidrange;kpi1:" + kpi.kpi1 + ";kpi2:" + kpi.kpi2 + ";kpi3:" + kpi.kpi3 + ";date1:" + kpi.date1 + ";tw1:" + kpi.tw1 + ";date2:" + kpi.date2+ ";tw2:" + kpi.tw2 + ";tech:" + kpi.tech ;
		var thisMarket =  '';
		$('#layer-list > li').each(function(index) {
			if ($(this).hasClass('active') && $(this).hasClass('layerText')) {
				thisMarket = $(this).attr("group");
			}
        });
		var technology = $("#tech-combod").val();
//		var range_url = 'http://localhost/geo/wms/pie?LAYERS=Sprint%3AV2R2_CDMA&STYLES=pie_carrier_count&VIEWPARAMS=' + vp + '&SERVICE=WMS&VERSION=1.1.1&REQUEST=GetMap&FORMAT=image%2Fjpeg&SRS=EPSG%3A900913&BBOX=-13079153.992852,3851716.6877832,-13005774.445708,3874647.7962656&WIDTH=1920&HEIGHT=600';
		var mapBounds = map.getExtent();
		var mapBoundsStr = mapBounds.toBBOX(7);
		var range_url = 'http://138.85.245.136/geo/wms/pie/' + map.getZoom() + '?LAYERS=' + thisMarket + '%3A' + technology + '&STYLES=pie_carrier_count&VIEWPARAMS=' + vp + '&SERVICE=WMS&VERSION=1.1.1&REQUEST=GetMap&FORMAT=image%2Fjpeg&SRS=EPSG%3A900913&BBOX=' + mapBoundsStr + '&WIDTH=1920&HEIGHT=600';
//		console.log(map.getZoom());
//		console.log(mapBoundsStr);
		//send ajax
		$.ajax({
			type: "GET",
			url: range_url,
			success: function(response){
				var json = eval("(" + response + ")");		
				var range_type = $('#range-combo').val();   
				$.each(json, function(key, value){
					if(key == kpi.kpi1){
						$.each(value, function(key1, value){
							if(key1 == range_type){
								if(value[0] < 1 && value[0] > -1) { $("#t11").val(value[0].toFixed(6)); } else { $("#t11").val(value[0].toFixed(4)); }
								if(value[1] < 1 && value[1] > -1) { $("#t12").val(value[1].toFixed(6)); } else { $("#t12").val(value[1].toFixed(4)); }
								if(value[1] < 1 && value[1] > -1) { $("#t13").val(value[1].toFixed(6)); } else { $("#t13").val(value[1].toFixed(4)); }
								if(value[2] < 1 && value[2] > -1) { $("#t14").val(value[2].toFixed(6)); } else { $("#t14").val(value[2].toFixed(4)); }
								if(value[2] < 1 && value[2] > -1) { $("#t15").val(value[2].toFixed(6)); } else { $("#t15").val(value[2].toFixed(4)); }
								if(value[3] < 1 && value[3] > -1) { $("#t16").val(value[3].toFixed(6)); } else { $("#t16").val(value[3].toFixed(4)); }
								if(value[3] < 1 && value[3] > -1) { $("#t17").val(value[3].toFixed(6)); } else { $("#t17").val(value[3].toFixed(4)); }
								if(value[4] < 1 && value[4] > -1) { $("#t18").val(value[4].toFixed(6)); } else { $("#t18").val(value[4].toFixed(4)); }
								if(value[4] < 1 && value[4] > -1) { $("#t19").val(value[4].toFixed(6)); } else { $("#t19").val(value[4].toFixed(4)); }
								if(value[5] < 1 && value[5] > -1) { $("#t110").val(value[5].toFixed(6)); } else { $("#t110").val(value[5].toFixed(4)); }
							}
						});				
						if(key == 'Data_Volume') {
							$.each(value, function(key1, value){
								if(key1 == range_type){
									if(value[0] < 1 && value[0] > -1) { $("#t11").val(value[5].toFixed(6)); } else { $("#t11").val(value[5].toFixed(4)); }
									if(value[1] < 1 && value[1] > -1) { $("#t12").val(value[4].toFixed(6)); } else { $("#t12").val(value[4].toFixed(4)); }
									if(value[1] < 1 && value[1] > -1) { $("#t13").val(value[4].toFixed(6)); } else { $("#t13").val(value[4].toFixed(4)); }
									if(value[2] < 1 && value[2] > -1) { $("#t14").val(value[3].toFixed(6)); } else { $("#t14").val(value[3].toFixed(4)); }
									if(value[2] < 1 && value[2] > -1) { $("#t15").val(value[3].toFixed(6)); } else { $("#t15").val(value[3].toFixed(4)); }
									if(value[3] < 1 && value[3] > -1) { $("#t16").val(value[2].toFixed(6)); } else { $("#t16").val(value[2].toFixed(4)); }
									if(value[3] < 1 && value[3] > -1) { $("#t17").val(value[2].toFixed(6)); } else { $("#t17").val(value[2].toFixed(4)); }
									if(value[4] < 1 && value[4] > -1) { $("#t18").val(value[1].toFixed(6)); } else { $("#t18").val(value[1].toFixed(4)); }
									if(value[4] < 1 && value[4] > -1) { $("#t19").val(value[1].toFixed(6)); } else { $("#t19").val(value[1].toFixed(4)); }
									if(value[5] < 1 && value[5] > -1) { $("#t110").val(value[0].toFixed(6)); } else { $("#t110").val(value[0].toFixed(4)); }
								}
							});				
						}
					}
					
					if(key == kpi.kpi2){
						$.each(value, function(key2, value){
							if(key2 == range_type){
								if(value[0] < 1 && value[0] > -1) { $("#t21").val(value[0].toFixed(6)); } else { $("#t21").val(value[0].toFixed(4)); }
								if(value[1] < 1 && value[1] > -1) { $("#t22").val(value[1].toFixed(6)); } else { $("#t22").val(value[1].toFixed(4)); }
								if(value[1] < 1 && value[1] > -1) { $("#t23").val(value[1].toFixed(6)); } else { $("#t23").val(value[1].toFixed(4)); }
								if(value[2] < 1 && value[2] > -1) { $("#t24").val(value[2].toFixed(6)); } else { $("#t24").val(value[2].toFixed(4)); }
								if(value[2] < 1 && value[2] > -1) { $("#t25").val(value[2].toFixed(6)); } else { $("#t25").val(value[2].toFixed(4)); }
								if(value[3] < 1 && value[3] > -1) { $("#t26").val(value[3].toFixed(6)); } else { $("#t26").val(value[3].toFixed(4)); }
								if(value[3] < 1 && value[3] > -1) { $("#t27").val(value[3].toFixed(6)); } else { $("#t27").val(value[3].toFixed(4)); }
								if(value[4] < 1 && value[4] > -1) { $("#t28").val(value[4].toFixed(6)); } else { $("#t28").val(value[4].toFixed(4)); }
								if(value[4] < 1 && value[4] > -1) { $("#t29").val(value[4].toFixed(6)); } else { $("#t29").val(value[4].toFixed(4)); }
								if(value[5] < 1 && value[5] > -1) { $("#t210").val(value[5].toFixed(6)); } else { $("#t210").val(value[5].toFixed(4)); }
							}
						});
						if(key == 'Data_Volume') {
							$.each(value, function(key2, value){
								if(key2 == range_type){
									if(value[0] < 1 && value[0] > -1) { $("#t21").val(value[5].toFixed(6)); } else { $("#t21").val(value[5].toFixed(4)); }
									if(value[1] < 1 && value[1] > -1) { $("#t22").val(value[4].toFixed(6)); } else { $("#t22").val(value[4].toFixed(4)); }
									if(value[1] < 1 && value[1] > -1) { $("#t23").val(value[4].toFixed(6)); } else { $("#t23").val(value[4].toFixed(4)); }
									if(value[2] < 1 && value[2] > -1) { $("#t24").val(value[3].toFixed(6)); } else { $("#t24").val(value[3].toFixed(4)); }
									if(value[2] < 1 && value[2] > -1) { $("#t25").val(value[3].toFixed(6)); } else { $("#t25").val(value[3].toFixed(4)); }
									if(value[3] < 1 && value[3] > -1) { $("#t26").val(value[2].toFixed(6)); } else { $("#t26").val(value[2].toFixed(4)); }
									if(value[3] < 1 && value[3] > -1) { $("#t27").val(value[2].toFixed(6)); } else { $("#t27").val(value[2].toFixed(4)); }
									if(value[4] < 1 && value[4] > -1) { $("#t28").val(value[1].toFixed(6)); } else { $("#t28").val(value[1].toFixed(4)); }
									if(value[4] < 1 && value[4] > -1) { $("#t29").val(value[1].toFixed(6)); } else { $("#t29").val(value[1].toFixed(4)); }
									if(value[5] < 1 && value[5] > -1) { $("#t210").val(value[0].toFixed(6)); } else { $("#t210").val(value[0].toFixed(4)); }
								}
							});
						}
					}
					
					if(key == kpi.kpi3){
						$.each(value, function(key3, value){
							if(key3 == range_type){
								if(value[0] < 1 && value[0] > -1) { $("#t31").val(value[0].toFixed(6)); } else { $("#t31").val(value[0].toFixed(4)); }
								if(value[1] < 1 && value[1] > -1) { $("#t32").val(value[1].toFixed(6)); } else { $("#t32").val(value[1].toFixed(4)); }
								if(value[1] < 1 && value[1] > -1) { $("#t33").val(value[1].toFixed(6)); } else { $("#t33").val(value[1].toFixed(4)); }
								if(value[2] < 1 && value[2] > -1) { $("#t34").val(value[2].toFixed(6)); } else { $("#t34").val(value[2].toFixed(4)); }
								if(value[2] < 1 && value[2] > -1) { $("#t35").val(value[2].toFixed(6)); } else { $("#t35").val(value[2].toFixed(4)); }
								if(value[3] < 1 && value[3] > -1) { $("#t36").val(value[3].toFixed(6)); } else { $("#t36").val(value[3].toFixed(4)); }
								if(value[3] < 1 && value[3] > -1) { $("#t37").val(value[3].toFixed(6)); } else { $("#t37").val(value[3].toFixed(4)); }
								if(value[4] < 1 && value[4] > -1) { $("#t38").val(value[4].toFixed(6)); } else { $("#t38").val(value[4].toFixed(4)); }
								if(value[4] < 1 && value[4] > -1) { $("#t39").val(value[4].toFixed(6)); } else { $("#t39").val(value[4].toFixed(4)); }
								if(value[5] < 1 && value[5] > -1) { $("#t310").val(value[5].toFixed(6)); } else { $("#t310").val(value[5].toFixed(4)); }
							}
						});
						if(key == 'Data_Volume') {
							$.each(value, function(key3, value){
								if(key3 == range_type){
									if(value[0] < 1 && value[0] > -1) { $("#t31").val(value[5].toFixed(6)); } else { $("#t31").val(value[5].toFixed(4)); }
									if(value[1] < 1 && value[1] > -1) { $("#t32").val(value[4].toFixed(6)); } else { $("#t32").val(value[4].toFixed(4)); }
									if(value[1] < 1 && value[1] > -1) { $("#t33").val(value[4].toFixed(6)); } else { $("#t33").val(value[4].toFixed(4)); }
									if(value[2] < 1 && value[2] > -1) { $("#t34").val(value[3].toFixed(6)); } else { $("#t34").val(value[3].toFixed(4)); }
									if(value[2] < 1 && value[2] > -1) { $("#t35").val(value[3].toFixed(6)); } else { $("#t35").val(value[3].toFixed(4)); }
									if(value[3] < 1 && value[3] > -1) { $("#t36").val(value[2].toFixed(6)); } else { $("#t36").val(value[2].toFixed(4)); }
									if(value[3] < 1 && value[3] > -1) { $("#t37").val(value[2].toFixed(6)); } else { $("#t37").val(value[2].toFixed(4)); }
									if(value[4] < 1 && value[4] > -1) { $("#t38").val(value[1].toFixed(6)); } else { $("#t38").val(value[1].toFixed(4)); }
									if(value[4] < 1 && value[4] > -1) { $("#t39").val(value[1].toFixed(6)); } else { $("#t39").val(value[1].toFixed(4)); }
									if(value[5] < 1 && value[5] > -1) { $("#t310").val(value[0].toFixed(6)); } else { $("#t310").val(value[0].toFixed(4)); }
								}
							});
						}
					}
				});
			}
		});
		
		generateKpidLegend(); //  (mrt)
	});
	
	$("#kpid-submit").click(function(){ 
		
		$('#layer-list > li').each(function(index) {
			if ($(this).hasClass('active') && $(this).hasClass('layerText')) {
				toggleLayer($(this).attr("group"),$(this).attr("layer"), false);
			}
		});
		//get vars
		var kpi = {};
		kpi.date1 = $("#kpi-datepicker1").val();
		kpi.tw1 = $("#tw1-combo").val();
		kpi.date2 = $("#kpi-datepicker2").val();
		kpi.tw2 = $("#tw2-combo").val();
	
		kpi.tech = $("#tech-combod").val();
		
		//kpi1
		kpi.kpi1 = $("#kpi1").val();
		
		kpi.color11 = colorToHex( $("#cp11 > span > i" ).css("background-color"));
		kpi.t11 = $("#t11").attr('value');
		kpi.t12 = $("#t12").attr('value');
		
		kpi.color13 = colorToHex( $("#cp12 > span > i" ).css("background-color"));
		kpi.t13 = $("#t13").attr('value');
		kpi.t14 = $("#t14").attr('value');
		
		kpi.color15 = colorToHex( $("#cp13 > span > i" ).css("background-color"));
		kpi.t15 = $("#t15").attr('value');
		kpi.t16 = $("#t16").attr('value');
		
		kpi.color17 = colorToHex( $("#cp14 > span > i" ).css("background-color"));
		kpi.t17 = $("#t17").attr('value');
		kpi.t18 = $("#t18").attr('value');
		
		kpi.color19 = colorToHex( $("#cp15 > span > i" ).css("background-color"));
		kpi.t19 = $("#t19").attr('value');
		kpi.t110 = $("#t110").attr('value');
		
		kpi.tcount1 = 5;
	
		//kpi2
		kpi.kpi2 = $("#kpi2").val();
		
		kpi.color21 =  colorToHex( $("#cp21 > span > i" ).css("background-color"));
		kpi.t21 = $("#t21").attr('value');
		kpi.t22 = $("#t22").attr('value');
		
		kpi.color23 = colorToHex( $("#cp22 > span > i" ).css("background-color"));
		kpi.t23 = $("#t23").attr('value');
		kpi.t24 = $("#t24").attr('value');
		
		kpi.color25 = colorToHex( $("#cp23 > span > i" ).css("background-color"));
		kpi.t25 = $("#t25").attr('value');
		kpi.t26 = $("#t26").attr('value');
		
		kpi.color27 = colorToHex( $("#cp24 > span > i" ).css("background-color"));
		kpi.t27 = $("#t27").attr('value');
		kpi.t28 = $("#t28").attr('value');
		
		kpi.color29 = colorToHex( $("#cp25 > span > i" ).css("background-color"));
		kpi.t29 = $("#t29").attr('value');
		kpi.t210 = $("#t210").attr('value');
		
		kpi.tcount2 = 5;
	
		//kpi3
		kpi.kpi3 = $("#kpi3").val();
		
		kpi.color31 = colorToHex( $("#cp31 > span > i" ).css("background-color"));
		kpi.t31 = $("#t31").attr('value');
		kpi.t32 = $("#t32").attr('value');
		
		kpi.color33 = colorToHex( $("#cp32 > span > i" ).css("background-color"));
		kpi.t33 = $("#t33").attr('value');
		kpi.t34 = $("#t34").attr('value');
		
		kpi.color35 = colorToHex( $("#cp33 > span > i" ).css("background-color"));
		kpi.t35 = $("#t35").attr('value');
		kpi.t36 = $("#t36").attr('value');
		
		kpi.color37 = colorToHex( $("#cp34 > span > i" ).css("background-color"));
		kpi.t37 = $("#t37").attr('value');
		kpi.t38 = $("#t38").attr('value');
		
		kpi.color39 = colorToHex( $("#cp35 > span > i" ).css("background-color"));
		kpi.t39 = $("#t39").attr('value');
		kpi.t310 = $("#t310").attr('value');
		
		kpi.tcount3 = 5;
	
		viewparams = "type:kpid;date1:" + kpi.date1 + ";tw1:" + kpi.tw1 + ";date2:" + kpi.date2+ ";tw2:" + kpi.tw2 + ";tech:" + kpi.tech +
		";kpi1:" + kpi.kpi1 + ";color11:" + kpi.color11 + ";t11:" + kpi.t11 + ";t12:" + kpi.t12 + ";color13:" + kpi.color13 + ";t13:" + kpi.t13 + ";t14:" + kpi.t14 + ";color15:" + kpi.color15 + ";t15:" + kpi.t15 + ";t16:" + kpi.t16 + ";color17:" + kpi.color17 + ";t17:" + kpi.t17 + ";t18:" + kpi.t18 + ";tcount1:" + kpi.tcount1 + ";color19:" + kpi.color19 + ";t19:" + kpi.t19 + ";kpi110:" + kpi.t110 +
		";kpi2:" + kpi.kpi2 + ";color21:" + kpi.color21 + ";t21:" + kpi.t21 + ";t22:" + kpi.t22 + ";color23:" + kpi.color23 + ";t23:" + kpi.t23 + ";t24:" + kpi.t24 + ";color25:" + kpi.color25 + ";t25:" + kpi.t25 + ";t26:" + kpi.t26 + ";color27:" + kpi.color27 + ";t27:" + kpi.t27 + ";t28:" + kpi.t28 + ";tcount2:" + kpi.tcount2 + ";color29:" + kpi.color29 + ";t29:" + kpi.t29 + ";kpi210:" + kpi.t210 +
		";kpi3:" + kpi.kpi3 + ";color31:" + kpi.color31 + ";t31:" + kpi.t31 + ";t32:" + kpi.t32 + ";color33:" + kpi.color33 + ";t33:" + kpi.t33 + ";t34:" + kpi.t34 + ";color35:" + kpi.color35 + ";t35:" + kpi.t35 + ";t36:" + kpi.t36 + ";color37:" + kpi.color37 + ";t37:" + kpi.t37 + ";t38:" + kpi.t38 + ";tcount3:" + kpi.tcount3 + ";color39:" + kpi.color39 + ";t39:" + kpi.t39 + ";kpi310:" + kpi.t310;

	 	$('#layer-list > li').each(function(index) {
			 if($("#tech-combod").val() == $(this).attr("layer")) {
				 $(this).addClass('active');
			 } else {
				 $(this).removeClass('active');
			 }
			 
			if ($(this).hasClass('active') && $(this).hasClass('layerText')) {
				toggleLayer($(this).attr("group"),$(this).attr("layer"), true);
			}
        });
		
		generateKpidLegend();  //  (mrt)
	});
	/* S---------------------------------------------------------------------------- */	
	$("#kpicd-range-submit").click(function(){ 

		//get vars
		var kpicd = {};
		kpicd.date1 = $("#kpicd-datepicker1").val();
		kpicd.tw1 = $("#twcd1-combo").val();
		kpicd.date2 = $("#kpicd-datepicker2").val();
		kpicd.tw2 = $("#twcd2-combo").val();
	
		kpicd.tech = $("#techcd-combod").val();
		kpicd.kpi1 = $("#kpicd1").val();
		
		vp = "type:kpicdrange;kpi1:" + kpicd.kpi1 + ";date1:" + kpicd.date1 + ";tw1:" + kpicd.tw1 + ";date2:" + kpicd.date2+ ";tw2:" + kpicd.tw2 + ";tech:" + kpicd.tech;
		var thisMarket =  '';
		$('#layer-list > li').each(function(index) {
			if ($(this).hasClass('active') && $(this).hasClass('layerText')) {
				thisMarket = $(this).attr("group");
			}
        });
		var technology = $("#techcd-combod").val().replace('_', '');
		//		var range_url = 'http://localhost/geo/wms/pie?LAYERS=Sprint%3AV2R2_CDMA&STYLES=pie_carrier_count&VIEWPARAMS=' + vp + '&SERVICE=WMS&VERSION=1.1.1&REQUEST=GetMap&FORMAT=image%2Fjpeg&SRS=EPSG%3A900913&BBOX=-13079153.992852,3851716.6877832,-13005774.445708,3874647.7962656&WIDTH=1920&HEIGHT=600';
		var mapBounds = map.getExtent();
		var mapBoundsStr = mapBounds.toBBOX(7);
		var range_url = 'http://138.85.245.136/geo/wms/pie/' + map.getZoom() + '?LAYERS=' + thisMarket + '%3A' + technology + '&STYLES=pie_carrier_count&VIEWPARAMS=' + vp + '&SERVICE=WMS&VERSION=1.1.1&REQUEST=GetMap&FORMAT=image%2Fjpeg&SRS=EPSG%3A900913&BBOX=' + mapBoundsStr + '&WIDTH=1920&HEIGHT=600';

		//send ajax
		$.ajax({
			type: "GET",
			url: range_url,
			success: function(response){
				var json = eval("(" + response + ")");		
				var range_type = $('#rangecd-combo').val();   
				$.each(json, function(key, value){
					if(key == kpicd.kpi1){
						$.each(value, function(key1, value){
							if(key1 == range_type){
								if(value[0] < 1 && value[0] > -1) { $("#tcd11").val(value[0].toFixed(6)); } else { $("#tcd11").val(value[0].toFixed(4)); }
								if(value[1] < 1 && value[1] > -1) { $("#tcd12").val(value[1].toFixed(6)); } else { $("#tcd12").val(value[1].toFixed(4)); }
								if(value[1] < 1 && value[1] > -1) { $("#tcd13").val(value[1].toFixed(6)); } else { $("#tcd13").val(value[1].toFixed(4)); }
								if(value[2] < 1 && value[2] > -1) { $("#tcd14").val(value[2].toFixed(6)); } else { $("#tcd14").val(value[2].toFixed(4)); }
								if(value[2] < 1 && value[2] > -1) { $("#tcd15").val(value[2].toFixed(6)); } else { $("#tcd15").val(value[2].toFixed(4)); }
								if(value[3] < 1 && value[3] > -1) { $("#tcd16").val(value[3].toFixed(6)); } else { $("#tcd16").val(value[3].toFixed(4)); }
								if(value[3] < 1 && value[3] > -1) { $("#tcd17").val(value[3].toFixed(6)); } else { $("#tcd17").val(value[3].toFixed(4)); }
								if(value[4] < 1 && value[4] > -1) { $("#tcd18").val(value[4].toFixed(6)); } else { $("#tcd18").val(value[4].toFixed(4)); }
								if(value[4] < 1 && value[4] > -1) { $("#tcd19").val(value[4].toFixed(6)); } else { $("#tcd19").val(value[4].toFixed(4)); }
								if(value[5] < 1 && value[5] > -1) { $("#tcd110").val(value[5].toFixed(6)); } else { $("#tcd110").val(value[5].toFixed(4)); }
							}
						});				
						if(key == 'Data_Volume') {
							$.each(value, function(key1, value){
								if(key1 == range_type){
									if(value[0] < 1 && value[0] > -1) { $("#tcd11").val(value[5].toFixed(6)); } else { $("#tcd11").val(value[5].toFixed(4)); }
									if(value[1] < 1 && value[1] > -1) { $("#tcd12").val(value[4].toFixed(6)); } else { $("#tcd12").val(value[4].toFixed(4)); }
									if(value[1] < 1 && value[1] > -1) { $("#tcd13").val(value[4].toFixed(6)); } else { $("#tcd13").val(value[4].toFixed(4)); }
									if(value[2] < 1 && value[2] > -1) { $("#tcd14").val(value[3].toFixed(6)); } else { $("#tcd14").val(value[3].toFixed(4)); }
									if(value[2] < 1 && value[2] > -1) { $("#tcd15").val(value[3].toFixed(6)); } else { $("#tcd15").val(value[3].toFixed(4)); }
									if(value[3] < 1 && value[3] > -1) { $("#tcd16").val(value[2].toFixed(6)); } else { $("#tcd16").val(value[2].toFixed(4)); }
									if(value[3] < 1 && value[3] > -1) { $("#tcd17").val(value[2].toFixed(6)); } else { $("#tcd17").val(value[2].toFixed(4)); }
									if(value[4] < 1 && value[4] > -1) { $("#tcd18").val(value[1].toFixed(6)); } else { $("#tcd18").val(value[1].toFixed(4)); }
									if(value[4] < 1 && value[4] > -1) { $("#tcd19").val(value[1].toFixed(6)); } else { $("#tcd19").val(value[1].toFixed(4)); }
									if(value[5] < 1 && value[5] > -1) { $("#tcd110").val(value[0].toFixed(6)); } else { $("#tcd110").val(value[0].toFixed(4)); }
								}
							});	
						}
					}
				});
			}
		});
		
		generateKpiCellDLegend();		//  (mrt)

	});
	
	$("#kpicd-submit").click(function(){ 
		
		$('#layer-list > li').each(function(index) {
			if ($(this).hasClass('active') && $(this).hasClass('layerText')) {
				toggleLayer($(this).attr("group"),$(this).attr("layer"), false);
			}
		});
		//get vars
		var kpicd = {};
		kpicd.date1 = $("#kpicd-datepicker1").val();
		kpicd.tw1 = $("#twcd1-combo").val();
		kpicd.date2 = $("#kpicd-datepicker2").val();
		kpicd.tw2 = $("#twcd2-combo").val();
	
		kpicd.tech = $("#techcd-combod").val();
		
		//kpi1
		kpicd.kpi1 = $("#kpicd1").val();
		
		kpicd.color11 = colorToHex( $("#cpcd11 > span > i" ).css("background-color"));
		kpicd.t11 = $("#tcd11").attr('value');
		kpicd.t12 = $("#tcd12").attr('value');
		
		kpicd.color13 = colorToHex( $("#cpcd12 > span > i" ).css("background-color"));
		kpicd.t13 = $("#tcd13").attr('value');
		kpicd.t14 = $("#tcd14").attr('value');
		
		kpicd.color15 = colorToHex( $("#cpcd13 > span > i" ).css("background-color"));
		kpicd.t15 = $("#tcd15").attr('value');
		kpicd.t16 = $("#tcd16").attr('value');
		
		kpicd.color17 = colorToHex( $("#cpcd14 > span > i" ).css("background-color"));
		kpicd.t17 = $("#tcd17").attr('value');
		kpicd.t18 = $("#tcd18").attr('value');
		
		kpicd.color19 = colorToHex( $("#cpcd15 > span > i" ).css("background-color"));
		kpicd.t19 = $("#tcd19").attr('value');
		kpicd.t110 = $("#tcd110").attr('value');
		
		kpicd.tcount1 = 5;
		
		viewparams = "type:kpicd;date1:" + kpicd.date1 + ";tw1:" + kpicd.tw1 + ";date2:" + kpicd.date2+ ";tw2:" + kpicd.tw2 + ";tech:" + kpicd.tech +
		";kpicd1:" + kpicd.kpi1 + ";color11:" + kpicd.color11 + ";t11:" + kpicd.t11 + ";t12:" + kpicd.t12 + ";color13:" + kpicd.color13 + ";t13:" + kpicd.t13 + ";t14:" + kpicd.t14 + ";color15:" + kpicd.color15 + ";t15:" + kpicd.t15 + ";t16:" + kpicd.t16 + ";color17:" + kpicd.color17 + ";t17:" + kpicd.t17 + ";t18:" + kpicd.t18 + ";tcount1:" + kpicd.tcount1 + ";color19:" + kpicd.color19 + ";t19:" + kpicd.t19 + ";kpi110:" + kpicd.t110;

		$('#layer-list > li').each(function(index) {
			if($(this).attr("layer") != undefined) {
				if($("#techcd-combod").val() == $(this).attr("layer").replace(' ', '_')) {
					 $(this).addClass('active');
				 } else {
					 $(this).removeClass('active');
				 }
			}
			if ($(this).hasClass('active') && $(this).hasClass('layerText')) {
				toggleLayer($(this).attr("group"),$(this).attr("layer"), true);
			}
        });
		
		generateKpiCellDLegend();  //  (mrt)
	});
	
	/* E---------------------------------------------------------------------------- */	
	function rgbToHex(R,G,B) {return toHex(R)+toHex(G)+toHex(B)}
	function toHex(n) {
		n = parseInt(n,10);
		if (isNaN(n)) return "00";
		n = Math.max(0,Math.min(n,255));
		return "0123456789ABCDEF".charAt((n-n%16)/16) + "0123456789ABCDEF".charAt(n%16);
	}

	function colorToHex(color) {
		if (color.substr(0, 1) === '#') {
			return color;
		}
		var digits = /(.*?)rgb\((\d+), (\d+), (\d+)\)/.exec(color);
		
		var red = parseInt(digits[2]);
		var green = parseInt(digits[3]);
		var blue = parseInt(digits[4]);
		
		return "#" + rgbToHex(red, green, blue);
	};
	
	$("#kpid-menu-li").click(function(){ 
		$('#site-attributes').hide();
		$('#kpid-menu').show();
		$('#kpi-menu').hide();
   		$('#kpicd-menu').hide();
		$('#parameter-menu').hide();
		$('#alarm-menu').hide();
		$('#rules-menu').hide();
		$('#neighbors-menu').hide();
        $('#nlissues-menu').hide();
		$('#kpic-menu').hide();
		$('#drive-test-menu').hide();
		$('#csl-nlt-menu').hide();
		$('#geow-menu').hide();
		if(!bottomPanelCollapsed) {
			cleanBackgroundColor();
			$("#kpid-menu-li").css('backgroundColor', '#fff');
		}
		
		generateKpidLegend();  // (mrt)
	});
	$("#kpid-menu-li").dblclick(function(){
		cleanBackgroundColor();
		if(bottomPanelCollapsed) {
			$("#kpid-menu-li").css('backgroundColor', '#fff');
		}
		toggleBottomPanel();
		
		generateKpidLegend(); //  (mrt)
	});


//kpi

	$("#tech-combod").change(function(){
		 $("#kpi1").empty();
         $("#kpi2").empty();
         $("#kpi3").empty();
	switch($("#tech-combod").val()) {
		case 'cdma':
		case 'evdo':
			 $("#kpi1").append('<option>Blocks</option>');
			 $("#kpi1").append('<option>Dropst</option>');
			 $("#kpi1").append('<option>Drop_perc</option>');
			 $("#kpi1").append('<option selected="selected">Block_perc</option>')
			 $("#kpi1").append('<option>ATT</option>');
                         $("#kpi1").append('<option>MOU</option>');
			 $("#kpi2").append('<option>Blocks</option>');
                         $("#kpi2").append('<option>Drops</option>');
                         $("#kpi2").append('<option selected="selected">Drop_perc</option>');
                         $("#kpi2").append('<option>Block_perc</option>')
                         $("#kpi2").append('<option>ATT</option>');
                         $("#kpi2").append('<option>MOU</option>');
			 $("#kpi3").append('<option>Blocks</option>');
                         $("#kpi3").append('<option>Drops</option>');
                         $("#kpi3").append('<option>Drop_perc</option>');
                         $("#kpi3").append('<option>Block_perc</option>')
                         $("#kpi3").append('<option>ATT</option>');
                         $("#kpi3").append('<option selected="selected">MOU</option>');
			 break;

		case 'UMTS':
			 $("#kpi1").append('<option value="Voice_DR">Voice DCR(%)</option>');
			 $("#kpi1").append('<option selected="selected" value="Voice_AFR">Voice AFR(%)</option>')
                       $("#kpi1").append('<option value="PS_AFR">PS AFR(%)</option>');
                       $("#kpi1").append('<option value="PS_RAB_DR">PS DCR(%)</option>');
                       $("#kpi1").append('<option value="HSDPA_PS_AFR">HSDPA AFR(%)</option>');
                       $("#kpi1").append('<option value="HSDPA_PS_DCR">HSDPA DCR(%)</option>');
			 $("#kpi2").append('<option selected="selected" value="Voice_DR">Voice DCR(%)</option>');
                        $("#kpi2").append('<option value="Voice_AFR">Voice AFR(%)</option>')
                        $("#kpi2").append('<option value="PS_AFR">PS AFR(%)</option>');
                        $("#kpi2").append('<option value="PS_RAB_DR">PS DCR(%)</option>');
                        $("#kpi2").append('<option value="HSDPA_PS_AFR">HSDPA AFR(%)</option>');
                        $("#kpi2").append('<option value="HSDPA_PS_DCR">HSDPA DCR(%)</option>');
			 $("#kpi3").append('<option value="Voice_DR">Voice DCR(%)</option>');
                        $("#kpi3").append('<option value="Voice_AFR">Voice AFR(%)</option>')
                        $("#kpi3").append('<option value="PS_AFR">PS AFR(%)</option>');
                        $("#kpi3").append('<option value="PS_RAB_DR">PS DCR(%)</option>');
                        $("#kpi3").append('<option selected="selected" value="HSDPA_PS_AFR">HSDPA AFR(%)</option>');
                        $("#kpi3").append('<option value="HSDPA_PS_DCR">HSDPA DCR(%)</option>');

                        break;
		case 'LTE':
					$("#kpi1").append('<option selected="selected" value="L_SESSION_SETUP_SUCCESS_RATE">Session Set Up Success Rate(%)</option>');
						$("#kpi1").append('<option value="L_CSFB_TO_WCDMA_ACTIVITY_RATE">CSFB Rate(%)</option>');
						$("#kpi1").append('<option value="L_IRAT_HANDOVER_RATE">IRAT Rate(%)</option>');
						$("#kpi1").append('<option value="L_ERAB_DROP_RATE">ERAB Drop Rate(%)</option>');

						$("#kpi2").append('<option value="L_SESSION_SETUP_SUCCESS_RATE">Session Set Up Success Rate(%)</option>');
					$("#kpi2").append('<option selected="selected" value="L_CSFB_TO_WCDMA_ACTIVITY_RATE">CSFB Rate(%)</option>');
						$("#kpi2").append('<option value="L_IRAT_HANDOVER_RATE">IRAT Rate(%)</option>');
						$("#kpi2").append('<option value="L_ERAB_DROP_RATE">ERAB Drop Rate(%)</option>');

						$("#kpi3").append('<option value="L_SESSION_SETUP_SUCCESS_RATE">Session Set Up Success Rate(%)</option>');
						$("#kpi3").append('<option value="L_CSFB_TO_WCDMA_ACTIVITY_RATE">CSFB Rate(%)</option>');
					$("#kpi3").append('<option selected="selected" value="L_IRAT_HANDOVER_RATE">IRAT Rate(%)</option>');
						$("#kpi3").append('<option value="L_ERAB_DROP_RATE">ERAB Drop Rate(%)</option>');

                        break;
		case 'GSM':
                         $("#kpi1").append('<option value="Voice_DCR">Voice DCR</option>');
                         $("#kpi1").append('<option selected="selected" value="NAF"> NAF(%)</option>')
                         $("#kpi1").append('<option value="Handover_SR">Handover SR</option>');
                         $("#kpi1").append('<option value="Voice_Traffic">Voice Traffic</option>');
                         $("#kpi1").append('<option value="Data_Volume">Data Volume</option>');
                         $("#kpi1").append('<option value="Throughput">Throughput</option>');
                         $("#kpi2").append('<option selected="selected" value="Voice_DCR">Voice DCR</option>');
                         $("#kpi2").append('<option value="NAF">NAF(%)</option>')
                         $("#kpi2").append('<option value="Handover_SR">Handover SR</option>');
                         $("#kpi2").append('<option value="Voice_Traffic">Voice Traffic</option>');
                         $("#kpi2").append('<option value="Data_Volume">Data Volume</option>');
                         $("#kpi2").append('<option value="Throughput">Throughput</option>');
                         $("#kpi3").append('<option value="Voice_DR">Voice DR</option>');
                         $("#kpi3").append('<option value="NAF">NAF(%)</option>')
                         $("#kpi3").append('<option value="Handover_SR">Handover SR</option>');
                         $("#kpi3").append('<option value="Voice_Traffic">Voice Traffic</option>');
                         $("#kpi3").append('<option selected="selected" value="Data_Volume">Data Volume</option>');
                         $("#kpi3").append('<option value="Throughput">Throughput</option>');

                         break;

			

	}
				
	
	});


	// NL Issues Selected



	$("#nlissues-datepicker-control").datepicker();


        $("#nlissues-menu-li").click(function(){
                $('#site-attributes').hide();
                $('#kpic-menu').hide();
                $('#kpi-menu').hide();
           		$('#kpicd-menu').hide();
                $('#parameter-menu').hide();
                $('#alarm-menu').hide();
                $('#rules-menu').hide();
                $('#neighbors-menu').hide();
                $('#nlissues-menu').show();
                $('#kpid-menu').hide();
                $('#drive-test-menu').hide();
				$('#disaster-feed-menu').hide();
				$('#csl-nlt-menu').hide();
				$('#field-tech-menu').hide();
				$('#geow-menu').hide();
        		viewparams = undefined ;
                $('#layer-list > li').each(function(index) {
                        if ($(this).hasClass('active') && $(this).hasClass('layerText')) {
                                toggleLayer($(this).attr("group"),$(this).attr("layer"));
                                toggleLayer($(this).attr("group"),$(this).attr("layer"));
                        }
                });
        		if(!bottomPanelCollapsed) {
        			cleanBackgroundColor();
        			$("#nlissues-menu-li").css('backgroundColor', '#fff');
        		}
        });
        $("#nlissues-menu-li").dblclick(function(){
        	cleanBackgroundColor();
    		if(bottomPanelCollapsed) {
    			$("#nlissues-menu-li").css('backgroundColor', '#fff');
    		}
        	toggleBottomPanel();
        });

         $("#nlissues-submit").click(function(){
                //get vars
                var kpi = {};
                kpi.date = $("#nlissues-datepicker").val();
                //kpi.tw = $("#nlissues-tw-combo").val();
                kpi.tech = $("#nlissues-tech-combo").val();
                kpi.type = $("#nlissues-type").val();
                //kpi.threshold = $("#neighbors-threshold").val();
                //kpi.metric =  $("#neighbors-metric").val();
                //kpi.kpi1 = $("#kpic1").val();
                //kpi.op1 = $("#kpic-op1").val();
                //kpi.val1 = $("#kpic-val1").attr('value');



                viewparams = "type:kpic;date:" + kpi.date + ";tech:" + kpi.tech  + ";type:" + kpi.type;

                $('#layer-list > li').each(function(index) {
                        if ($(this).hasClass('active') && $(this).hasClass('layerText'))
                                toggleLayer($(this).attr("group"),$(this).attr("layer"));
                        enabledLayer.group=$(this).attr("group");
                });

                console.log(enabledLayer.group);
                 toggleLayer(enabledLayer.group,kpi.tech);
                //alert('todo: toggle layer');
        });


	// Neighbors Menu Selected



	$("#neighbors-datepicker-control").datepicker();


        $("#neighbors-menu-li").click(function(){
                $('#site-attributes').hide();
                $('#kpic-menu').hide();
                $('#kpi-menu').hide();
           		$('#kpicd-menu').hide();
                $('#parameter-menu').hide();
                $('#alarm-menu').hide();
                $('#rules-menu').hide();
                $('#neighbors-menu').show();
                $('#nlissues-menu').hide();
                $('#kpid-menu').hide();
                $('#drive-test-menu').hide();
				$('#disaster-feed-menu').hide();
				$('#field-tech-menu').hide();
				$('#csl-nlt-menu').hide();
				$('#geow-menu').hide();
        		viewparams = undefined ;
                $('#layer-list > li').each(function(index) {
                        if ($(this).hasClass('active') && $(this).hasClass('layerText')) {
                                toggleLayer($(this).attr("group"),$(this).attr("layer"));
                                toggleLayer($(this).attr("group"),$(this).attr("layer"));
                        }
                });
        		if(!bottomPanelCollapsed) {
        			cleanBackgroundColor();
        			$("#neighbors-menu-li").css('backgroundColor', '#fff');
        		}
        });
        $("#neighbors-menu-li").dblclick(function(){
        	cleanBackgroundColor();
    		if(bottomPanelCollapsed) {
    			$("#neighbors-menu-li").css('backgroundColor', '#fff');
    		}
            toggleBottomPanel();
        });

         $("#neighbors-submit").click(function(){
                //get vars
                var kpi = {};
                kpi.date = $("#neighbors-datepicker").val();
                kpi.tech = $("#neighbors-tech-combo").val();
                kpi.techtype = $("#neighbors-type :selected").val();

                var bb = map.getExtent().transform( map.projection, new OpenLayers.Projection("EPSG:4326"));
                viewparams = "type:neighbors;date:" + kpi.date + ";tech:" + kpi.tech  + ";techtype:" + kpi.techtype;
            	var thisGroup = '';
            	var thisLayer = '';
                $('#layer-list > li').each(function(index) {
                        if ($(this).hasClass('active') && $(this).hasClass('layerText')) {
                        	thisGroup = $(this).attr("group");
                        	thisLayer = $(this).attr("layer");
                            toggleLayer($(this).attr("group"),$(this).attr("layer"));
                        }
                        enabledLayer.group=$(this).attr("group");
                });

                toggleLayer(enabledLayer.group,kpi.tech);
         	    jQuery("#neighbors-list").jqGrid('setGridParam',{url:grid_base_url + "?type=neighbors-list&date=" + kpi.date + "&tech=" + kpi.tech + "&techtype=" + kpi.techtype + "&minx=" + bb.bottom + "&miny=" + bb.left + "&maxx=" + bb.top + "&maxy=" + bb.right}).trigger("reloadGrid");		
     	   });


	// Rules Menu Selected 

	$("#rules-datepicker-control").datepicker();


        $("#rules-menu-li").click(function(){
                $('#site-attributes').hide();
                $('#kpic-menu').hide();
                $('#kpi-menu').hide();
           		$('#kpicd-menu').hide();
                $('#parameter-menu').hide();
                $('#alarm-menu').hide();
                $('#rules-menu').show();
                $('#neighbors-menu').hide();
                $('#nlissues-menu').hide();
                $('#kpid-menu').hide();
                $('#drive-test-menu').hide();
				$('#disaster-feed-menu').hide();
				$('#csl-nlt-menu').hide();
				$('#field-tech-menu').hide();
				$('#geow-menu').hide();
        		viewparams = undefined ;
                $('#layer-list > li').each(function(index) {
                        if ($(this).hasClass('active') && $(this).hasClass('layerText')) {
                                toggleLayer($(this).attr("group"),$(this).attr("layer"));
                                toggleLayer($(this).attr("group"),$(this).attr("layer"));
                        }
                });
        		if(!bottomPanelCollapsed) {
        			cleanBackgroundColor();
        			$("#rules-menu-li").css('backgroundColor', '#fff');
        		}
        });
        $("#rules-menu-li").dblclick(function(){
        	cleanBackgroundColor();
    		if(bottomPanelCollapsed) {
    			$("#rules-menu-li").css('backgroundColor', '#fff');
    		}
            toggleBottomPanel();
        });

         $("#rules-submit").click(function(){
                //get vars
                var kpi = {};
                kpi.date = $("#rules-datepicker").val();
                kpi.tw = $("#rules-tw-combo").val();
                kpi.tech = $("#rules-tech-combo").val();
                kpi.type = $("#rules-type").val();
                kpi.threshold = $("#rules-threshold").val();
                kpi.metric =  $("#rules-metric").val();
                //kpi.kpi1 = $("#kpic1").val();
                //kpi.op1 = $("#kpic-op1").val();
                //kpi.val1 = $("#kpic-val1").attr('value');

                viewparams = "type:kpic;date:" + kpi.date + ";tech:" + kpi.tech + ";tw:" + kpi.tw + ";type:" + kpi.type + ";threshold:" + kpi.threshold  + ";metric:" + kpi.metric;

                $('#layer-list > li').each(function(index) {
                        if ($(this).hasClass('active') && $(this).hasClass('layerText'))
                                toggleLayer($(this).attr("group"),$(this).attr("layer"));
                        enabledLayer.group=$(this).attr("group");
                });

                console.log(enabledLayer.group);
                 toggleLayer(enabledLayer.group,kpi.tech);
                //alert('todo: toggle layer');
        });

         //start of drive test codes
      	$("#drive-test-datepicker-control").datepicker();
        $("#drive-test-menu-li").click(function(){
                      
             	     $('#site-attributes').hide();
                     $('#kpic-menu').hide();
                     $('#kpi-menu').hide();
                     $('#parameter-menu').hide();
                     $('#alarm-menu').hide();
                     $('#rules-menu').hide();
                     $('#drive-test-menu').show();
                     $('#neighbors-menu').hide();
                     $('#nlissues-menu').hide();
                     $('#kpid-menu').hide();
                     $('#kpicd-menu').hide();
             		 $('#disaster-feed-menu').hide();
					 $('#field-tech-menu').hide();
					 $('#csl-nlt-menu').hide();
					 $('#geow-menu').hide();
					 
                      viewparams = undefined ;
                      $('#layer-list > li').each(function(index) {
                              if ($(this).hasClass('active') && $(this).hasClass('layerText')) {
                                      toggleLayer($(this).attr("group"),$(this).attr("layer"));
                                      toggleLayer($(this).attr("group"),$(this).attr("layer"));
                              }
                      });
              		if(!bottomPanelCollapsed) {
              			cleanBackgroundColor();
              			$("#drive-test-menu-li").css('backgroundColor', '#fff');
              		}
					
					generateDriveTestLegend();  // (mrt)
              });
		
              $("#drive-test-menu-li").dblclick(function(){
              	cleanBackgroundColor();
          		if(bottomPanelCollapsed) {
          			$("#drive-test-menu-li").css('backgroundColor', '#fff');
          		}
                  toggleBottomPanel();
				  
				  generateDriveTestLegend(); //  (mrt)
              });
			  
			  $("#disaster-feed-menu-li").click(function(){
             	     $('#site-attributes').hide();
                     $('#kpic-menu').hide();
                     $('#kpi-menu').hide();
                     $('#parameter-menu').hide();
                     $('#alarm-menu').hide();
                     $('#rules-menu').hide();
                     $('#drive-test-menu').hide();
                     $('#neighbors-menu').hide();
                     $('#nlissues-menu').hide();
                     $('#kpid-menu').hide();
                     $('#kpicd-menu').hide();
					 $('#disaster-feed-menu').show();
					 $('#field-tech-menu').hide();
					 $('#csl-nlt-menu').hide();
					 $('#geow-menu').hide();
              		if(!bottomPanelCollapsed) {
              			cleanBackgroundColor();
              			$("#disaster-feed-menu-li").css('backgroundColor', '#fff');
              		}
              });
			  
			  $("#disaster-feed-menu-li").dblclick(function(){
              	cleanBackgroundColor();
          		if(bottomPanelCollapsed) {
          			$("#disaster-feed-menu-li").css('backgroundColor', '#fff');
          		}
                  toggleBottomPanel();
              });
              

               $("#enod-submit").click(function(){
 					//get vars
 					var enod = {};

 					if ($("#enod-year").val()==""){
 						alert ("Please select a year");
 						$("#enod-year").focus();
 						return false;
 					}
 					if ($("#enod-dataset").val()==""){
 						alert ("Please select a quarter");
 						$("#enod-dataset").focus();
 						return false;
 					}
 					enod.year = $("#enod-year").val();
 					enod.dataset = $("#enod-dataset").val();
 					enod.datatype = $("#enod-datatype").val();
 					enod.pn = $("#enod-pn").val();

 					enod.kpi_num  = $("#enod-kpi-count").val();
 					
 					enod.color1 = $("#tcpv0").val();
 					enod.color2 = $("#tcpv1").val();
 					enod.color3 = $("#tcpv2").val();
 					enod.color4 = $("#tcpv3").val();
 					enod.color5 = $("#tcpv4").val();
 					enod.color6 = $("#tcpv5").val();
 					//enod.color7 = $("#tcpv6").val();

 					enod.range1 = $("#td1").val();
 					enod.range2 = $("#td2").val();
 					enod.range3 = $("#td3").val();
 					enod.range4 = $("#td4").val();
 					enod.range5 = $("#td5").val();
 					enod.layers = "";
 					//enod.zoom	= map.getZoom();
 					//enod.range6 = $("#td6").val();

 					var eLayers = map.getLayersByName("enod_layer");
 			    	for (var i=0;i<eLayers.length;i++){
 			        	  var eLayer = map.getLayersByName("enod_layer")[i];
 			    	   	  eLayer.destroy();//.setVisibility(false);    		   
 			    	}
 			    	   
 					url = "http://138.85.245.150/enod/geo/wms/dt";
 					map.addLayer(buildWMS("enod_layer", url, enod));
 					$('#driveTestCheckDiv').show();
 					$('#driveTestCheck').bind('change',drive_test_visibility);
 					/*
 					var markers = new OpenLayers.Layer.Markers( "Markers" );
 					map.addLayer(markers);
 					
 					var size = new OpenLayers.Size(21,25);
 					var offset = new OpenLayers.Pixel(-(size.w/2), -size.h);	        
 					var icon = new OpenLayers.Icon('../emap/global/img/bullet_green.png',size,offset);
                     
                     // this is working
             		jQuery.getJSON(url, function(enod_data) {
                     	  
                     	json = enod_data; 
                     	markers.clearMarkers();        	
                         for (var i = 0, length = json.length; i < length; i++) {
           					data = json[i];
           					alert(data);
           					lonLat = new OpenLayers.LonLat(data.Longitude, data.Latitude);
           				    markers.addMarker(new OpenLayers.Marker(lonLat,icon.clone())); 
           		          }
                     	   
                     });*/              	

				generateDriveTestLegend();  // (mrt)
              });
       $('#enod-pn').tooltip();

       function drive_test_visibility(){
     	   var eLayer = map.getLayersByName("enod_layer")[0];
       	   if($('#driveTestCheck').is(':checked')){
	   	   	      eLayer.setVisibility(false);
	    	   }else{
	    	   	   eLayer.setVisibility(true);
	    	   }
       }
       $('#driveTestCheck').change(function(){
    	   var eLayers = map.getLayersByName("enod_layer");
    	   for (var i=0;i<eLayers.length;i++){
        	   var eLayer = map.getLayersByName("enod_layer")[i];
    	   	   if($('#driveTestCheck').is(':checked')){
    	    		   eLayer.setVisibility(false);
    	    	   }else{
    	    		   eLayer.setVisibility(true);
    	    	   }    		   
    	   }
       });
       $('#enod-year').change(function(){
  		  var year =  $('#enod-year').find(":selected").text();
  		  var client = $("#client").val();
  		  var url = "./getEnodDataSource/?year="+year+"&client="+client;
  		  
		  jQuery.getJSON(url, function(enod_data_source) {     	  
            	//json = enod_data_source; 
            	var $el = $("#enod-dataset");
            	$el.empty();
            	$.each(enod_data_source,function(key,value) {
  					//alert(value);
  					$el.append($("<option></option>").attr("value", value['data_source']).text(value['data_source']));
  		          });
            	changeDS();
            	   
            	});
  	  });
       $('#enod-dataset').change(function(){
 		  var ds =  $('#enod-dataset').find(":selected").val();
 		  
 		  var url = "./getEnodDataSourceParams/?data_source="+ds;
 		  jQuery.getJSON(url, function(enod_data_source_params) {     	  
           	var $el = $("#enod-datatype");
           	$el.empty();
           	var query_params = enod_data_source_params['query_params_values'].split(',');
           	$.each(query_params,function(key,value) {
 					$el.append($("<option></option>").attr("value", value).text(value));
 		          });
           	$el.bind('change',changeDS);
           	   
           	});
 	  });
       function changeDS(){
     	  var ds =  $('#enod-dataset').find(":selected").val();
 		  var url = "./getEnodDataSourceParams/?data_source="+ds;
 		  jQuery.getJSON(url, function(enod_data_source_params) {     	  
           	var $el = $("#enod-datatype");
           	$el.empty();
           	var query_params = enod_data_source_params['query_params_values'].split(',');
           	$.each(query_params,function(key,value) {
 					$el.append($("<option></option>").attr("value", value).text(value));
 		          });
           	   
           	});
       }
 	  $('#enod-kpi-count').change(function(){
		  var num =  $('#enod-kpi-count').find(":selected").text();
	 	 for (var i=1;i<=7;i++){
	 		 if (i <= num){
	 			 $('#td'+i).show();
	 			 $('#tdcp'+i).show();
	 		 }else{
	 			 $('#td'+i).hide();
	 			 $('#tdcp'+i).hide();
	 		 }
	 	 }
	  });
       $('#enod-datatype').change(function(){
       	switch($('#enod-datatype').val()) {
   			case "Dominant_PN_Ec":
   		    	$('#td1').val("-107.6"); 
   		    	$('#td2').val("-99");
   		    	$('#td3').val("-91.9");
   		    	$('#td4').val("-81.9");
   		    	$('#td5').val("-76.9");
   		    	break;
   			case "Dominant_PN_EcIo":
   		    	$('#td1').val("-17");
   		    	$('#td2').val("-14");
   		    	$('#td3').val("-12");
   		    	$('#td4').val("-9");
   		    	$('#td5').val("-7");
   		    	break;
   			case "Rx_Power_Agg":
   		    	$('#td1').val("-99.4");
   		    	$('#td2').val("-90.8");
   		    	$('#td3').val("-83.7");
   		    	$('#td4').val("-73.7"); 
   		    	$('#td5').val("-68.7");
   		    	break;	
   			case "Rx_Power":
   		    	$('#td1').val("-99.4");
   		    	$('#td2').val("-90.8");
   		    	$('#td3').val("-83.7");
   		    	$('#td4').val("-73.7"); 
   		    	$('#td5').val("-68.7");
   		    	break;		
   			case "Mobile_Tx_Pwr":
   		    	$('#td1').val("-23");
   		    	$('#td2').val("-10");
   		    	$('#td3').val("0");
   		    	$('#td4').val("10");
   		    	$('#td5').val("15");
   		    	break;	
   			case "Tx_Pwr":
   		    	$('#td1').val("-23");
   		    	$('#td2').val("-10");
   		    	$('#td3').val("0");
   		    	$('#td4').val("10");
   		    	$('#td5').val("15");
   		    	break;
   			case "FFER":
   		    	$('#td1').val("1");
   		    	$('#td2').val("3");
   		    	$('#td3').val("5");
   		    	$('#td4').val("10");
   		    	$('#td5').val("25");
   		    	break;	
   		    }
       	return false;

       });
       //end of drive test codes

	// Alarm Menu Selected

	$("#alarm-datepicker-control").datepicker();


        $("#alarm-menu-li").click(function(){
                $('#site-attributes').hide();
                $('#kpic-menu').hide();
           		$('#kpicd-menu').hide();
                $('#kpi-menu').hide();
                $('#parameter-menu').hide();
                $('#alarm-menu').show();
				$('#rules-menu').hide();
				$('#neighbors-menu').hide();
                $('#nlissues-menu').hide();
                $('#kpid-menu').hide();
                $('#drive-test-menu').hide();
				$('#disaster-feed-menu').hide();
				$('#field-tech-menu').hide();
				$('#csl-nlt-menu').hide();
				$('#geow-menu').hide();
				viewparams = undefined ;
                $('#layer-list > li').each(function(index) {
                        if ($(this).hasClass('active') && $(this).hasClass('layerText')) {
                                toggleLayer($(this).attr("group"),$(this).attr("layer"));
                                toggleLayer($(this).attr("group"),$(this).attr("layer"));
                        }
                });
        		if(!bottomPanelCollapsed) {
        			cleanBackgroundColor();
        			$("#alarm-menu-li").css('backgroundColor', '#fff');
        		}
        });
        $("#alarm-menu-li").dblclick(function(){
        	cleanBackgroundColor();
    		if(bottomPanelCollapsed) {
    			$("#alarm-menu-li").css('backgroundColor', '#fff');
    		}
            toggleBottomPanel();
        });

         $("#alarm-submit").click(function(){
                //get vars
                var kpi = {};
                kpi.date = $("#alarm-datepicker").val();
                kpi.tw = $("#alarm-tw-combo").val();
                kpi.tech = $("#alarm-tech-combo").val();
                kpi.type = $("#alarm-type").val();
                kpi.threshold = $("#alarm-threshold").val();
                kpi.cleared =  $("#alarm-cleared").is(':checked');
                //kpi.kpi1 = $("#kpic1").val();
                //kpi.op1 = $("#kpic-op1").val();
                //kpi.val1 = $("#kpic-val1").attr('value');

                viewparams = "type:kpial;date:" + kpi.date + ";tech:" + kpi.tech + ";tw:" + kpi.tw + ";severitytype:" + kpi.type + ";threshold:" + kpi.threshold  + ";cleared:" + kpi.cleared;

                $('#layer-list > li').each(function(index) {
                        if ($(this).hasClass('active') && $(this).hasClass('layerText'))
                                toggleLayer($(this).attr("group"),$(this).attr("layer"));
                        enabledLayer.group=$(this).attr("group");
                });

                jQuery("#alarm-list").jqGrid('setGridParam',{url:grid_base_url + "?type=alarm-list&date=" + kpi.date + "&tw=" + kpi.tw + "&severitytype=" + kpi.type + "&cleared=" + kpi.cleared + "&minx=" + bb.bottom + "&miny=" + bb.left + "&maxx=" + bb.top + "&maxy=" + bb.right}).trigger("reloadGrid");		
                toggleLayer(enabledLayer.group, kpi.tech);
                //alert('todo: toggle layer');
        });

	// Parameter Menu selected 
	$("#parameter-datepicker-control").datepicker();

        $("#parameter-menu-li").click(function(){
	        $('#site-attributes').hide();
	        $('#kpic-menu').hide();
	        $('#kpi-menu').hide();
	   		$('#kpicd-menu').hide();
	        $('#parameter-menu').show();
			$('#alarm-menu').hide();
			$('#rules-menu').hide();
			$('#neighbors-menu').hide();
	        $('#nlissues-menu').hide();
	        $('#kpid-menu').hide();
	        $('#drive-test-menu').hide();
			$('#disaster-feed-menu').hide();
		    $('#field-tech-menu').hide();
			$('#csl-nlt-menu').hide();
			$('#geow-menu').hide();
			viewparams = undefined ;
	        $('#layer-list > li').each(function(index) {
	                if ($(this).hasClass('active') && $(this).hasClass('layerText')) {
	                        toggleLayer($(this).attr("group"),$(this).attr("layer"));
	                        toggleLayer($(this).attr("group"),$(this).attr("layer"));
	                }
	        });
			if(!bottomPanelCollapsed) {
				cleanBackgroundColor();
				$("#parameter-menu-li").css('backgroundColor', '#fff');
			}
        });
        $("#parameter-menu-li").dblclick(function(){
        	cleanBackgroundColor();
    		if(bottomPanelCollapsed) {
    			$("#parameter-menu-li").css('backgroundColor', '#fff');
    		}
            toggleBottomPanel();
        });

         $("#parameter-submit").click(function(){
                //get vars
                var kpi = {};
                kpi.date = $("#parameter-datepicker").val();
                kpi.tw = $("#parameter-tw-combo").val();
                kpi.tech = $("#parameter-tech-combo").val();
                kpi.paramtype = $("#parameter-type").val();
                kpi.checktype = $("#parameter-check-type").val();
                //kpi.kpi1 = $("#kpic1").val();
                //kpi.op1 = $("#kpic-op1").val();
                //kpi.val1 = $("#kpic-val1").attr('value');

                viewparams = "type:kpie;date:" + kpi.date + ";tech:" + kpi.tech + ";tw:" + kpi.tw + ";paramtype:" + kpi.paramtype + ";checktype:" + kpi.checktype;

                $('#layer-list > li').each(function(index) {
            		if ($(this).hasClass('active') && $(this).hasClass('layerText')) {
                        toggleLayer($(this).attr("group"),$(this).attr("layer"));
            		}
                    enabledLayer.group=$(this).attr("group");
                });

        	    $('#layer-list > li').each(function(index) {
					if($(this).attr("search_group") != undefined) {
							if(kpi.tech == $(this).attr("search_group").replace(' ', '_'))
							{
								toggleLayer(enabledLayer.group, $(this).attr("layer"));
							}
					}
        		});
        	    jQuery("#parameter-list").jqGrid('setGridParam',{url:grid_base_url + "?type=parameter-list&date=" + kpi.date + "&tw=" + kpi.tw + "&minx=" + bb.bottom + "&miny=" + bb.left + "&maxx=" + bb.top + "&maxy=" + bb.right}).trigger("reloadGrid");		
                //toggleLayer(enabledLayer.group, kpi.tech);
                //alert('todo: toggle layer');
        });

     	// GEOW Menu selected 
     		 $("#geow-datepicker-control").datepicker();

             $("#geow-menu-li").click(function(){
     	        $('#site-attributes').hide();
     	        $('#kpic-menu').hide();
     	        $('#kpi-menu').hide();
     	   		$('#kpicd-menu').hide();
     	        $('#parameter-menu').hide();
     			$('#alarm-menu').hide();
     			$('#rules-menu').hide();
     			$('#neighbors-menu').hide();
     	        $('#nlissues-menu').hide();
     	        $('#kpid-menu').hide();
     	        $('#drive-test-menu').hide();
     			$('#disaster-feed-menu').hide();
     		    $('#field-tech-menu').hide();
     			$('#csl-nlt-menu').hide();
     			$('#geow-menu').show();
     			viewparams = undefined ;
     	        $('#layer-list > li').each(function(index) {
     	                if ($(this).hasClass('active') && $(this).hasClass('layerText')) {
     	                        toggleLayer($(this).attr("group"),$(this).attr("layer"));
     	                        toggleLayer($(this).attr("group"),$(this).attr("layer"));
     	                }
     	        });
     			if(!bottomPanelCollapsed) {
     				cleanBackgroundColor();
     				$("#geow-menu-li").css('backgroundColor', '#fff');
     			}
             });
             $("#geow-menu-li").dblclick(function(){
             	cleanBackgroundColor();
         		if(bottomPanelCollapsed) {
         			$("#geow-menu-li").css('backgroundColor', '#fff');
         		}
                 toggleBottomPanel();
             });

              $("#geow-submit").click(function(){
                     //get vars
                     var kpi = {};
                     kpi.date = $("#geow-datepicker").val();
                     kpi.tw = $("#geow-tw-combo").val();
                     kpi.tech = $("#geow-tech-combo").val();
                     kpi.paramtype = $("#geow-type").val();
                     kpi.checktype = $("#geow-check-type").val();
                     //kpi.kpi1 = $("#kpic1").val();
                     //kpi.op1 = $("#kpic-op1").val();
                     //kpi.val1 = $("#kpic-val1").attr('value');

                     viewparams = "type:kpie;date:" + kpi.date + ";tech:" + kpi.tech + ";tw:" + kpi.tw + ";paramtype:" + kpi.paramtype + ";checktype:" + kpi.checktype;

                     $('#layer-list > li').each(function(index) {
                 		if ($(this).hasClass('active') && $(this).hasClass('layerText')) {
                             toggleLayer($(this).attr("group"),$(this).attr("layer"));
                 		}
                         enabledLayer.group=$(this).attr("group");
                     });

             	    $('#layer-list > li').each(function(index) {
     					if($(this).attr("search_group") != undefined) {
     							if(kpi.tech == $(this).attr("search_group").replace(' ', '_'))
     							{
     								toggleLayer(enabledLayer.group, $(this).attr("layer"));
     							}
     					}
             		});
             	    jQuery("#geow-list").jqGrid('setGridParam',{url:grid_base_url + "?type=geow-list&date=" + kpi.date + "&tw=" + kpi.tw + "&minx=" + bb.bottom + "&miny=" + bb.left + "&maxx=" + bb.top + "&maxy=" + bb.right}).trigger("reloadGrid");		
                     //toggleLayer(enabledLayer.group, kpi.tech);
                     //alert('todo: toggle layer');
             });

	//   KPI Cell Menu selected
	$("#kpic-datepicker-control").datepicker();

	$("#kpic-menu-li").click(function(){
				$('#site-attributes').hide();
		        $('#kpic-menu').show();
		        $('#kpi-menu').hide();
		   		$('#kpicd-menu').hide();
				$('#parameter-menu').hide();
				$('#alarm-menu').hide();
				$('#rules-menu').hide();
				$('#neighbors-menu').hide();
				$('#drive-test-menu').hide();
				$('#nlissues-menu').hide();
				$('#kpid-menu').hide();
				$('#drive-test-menu').hide();
				$('#disaster-feed-menu').hide();
		        $('#field-tech-menu').hide();
				$('#csl-nlt-menu').hide();
				$('#geow-menu').hide();
				viewparams = undefined ;
				$('#layer-list > li').each(function(index) {
                       if ($(this).hasClass('active') && $(this).hasClass('layerText')) {
                               toggleLayer($(this).attr("group"),$(this).attr("layer"));
                               toggleLayer($(this).attr("group"),$(this).attr("layer"));
                       }
               });
       		if(!bottomPanelCollapsed) {
       			cleanBackgroundColor();
       			$("#kpic-menu-li").css('backgroundColor', '#fff');
       		}
		   
		   generateKpiCellLegend(); //  (mrt)
       });
       $("#kpic-menu-li").dblclick(function(){
   		cleanBackgroundColor();
   		if(bottomPanelCollapsed) {
   			$("#kpic-menu-li").css('backgroundColor', '#fff');
   		}
           toggleBottomPanel();
		   
		   generateKpiCellLegend(); //  (mrt)
       });

   	$("#kpicd-menu-li").click(function() {
		$('#site-attributes').hide();
		$('#kpi-menu').hide();
		$('#kpic-menu').hide();
		$('#kpicd-menu').show();
		$('#parameter-menu').hide();
		$('#alarm-menu').hide();
		$('#rules-menu').hide();
		$('#neighbors-menu').hide();
		$('#nlissues-menu').hide();
		$('#kpid-menu').hide();
		$('#drive-test-menu').hide();
		$('#disaster-feed-menu').hide();
		$('#field-tech-menu').hide();
		$('#csl-nlt-menu').hide();
		$('#geow-menu').hide();
		viewparams = undefined ;
		$('#layer-list > li').each(function(index) {
			if ($(this).hasClass('active') && $(this).hasClass('layerText')) {
				toggleLayer($(this).attr("group"),$(this).attr("layer"));
				toggleLayer($(this).attr("group"),$(this).attr("layer"));
			}
		});
		if(!bottomPanelCollapsed) {
			cleanBackgroundColor();
			$("#kpicd-menu-li").css('backgroundColor', '#fff');
		}
		
		generateKpiCellDLegend();		//  (mrt)

   });
   $("#kpicd-menu-li").dblclick(function(){
	   cleanBackgroundColor();
	   if(bottomPanelCollapsed) {
		   $("#kpicd-menu-li").css('backgroundColor', '#fff');
	   }
       toggleBottomPanel();
		
		generateKpiCellDLegend();		//  (mrt)

   });


	$("#kpic-tech-combo").change(function(){
	 $("#kpic1").empty();

	switch($("#kpic-tech-combo").val()) {
		case 'cdma':
		case 'evdo':
			 $("#kpic1").append('<option>Blocks</option>');
			 $("#kpic1").append('<option>Dropst</option>');
			 $("#kpic1").append('<option>Drop_perc</option>');
			 $("#kpic1").append('<option selected="selected">Block_perc</option>')
			 $("#kpic1").append('<option>ATT</option>');
			 $("#kpic1").append('<option>MOU</option>');
			 
			 break;
		 case 'UMTS Cell':
			 $("#kpic1").append('<option value="Voice_DR">Voice DCR(%)</option>');
			 $("#kpic1").append('<option selected="selected" value="Voice_AFR">Voice AFR(%)</option>')
			 $("#kpic1").append('<option value="PS_AFR">PS AFR(%)</option>');
			 $("#kpic1").append('<option value="PS_RAB_DR">PS DCR(%)</option>');
			 $("#kpic1").append('<option value="HSDPA_PS_AFR">HSDPA AFR(%)</option>');
			 $("#kpic1").append('<option value="HSDPA_PS_DCR">HSDPA DCR(%)</option>');
			 
			 break;
						 
		case 'GSM Cell':
			 $("#kpic1").append('<option value="Voice_DCR">Voice DCR(%)</option>');
			 $("#kpic1").append('<option selected="selected" value="NAF"> NAF(%)</option>')
			 $("#kpic1").append('<option value="Handover_SR">Handover SR(%)</option>');
			 $("#kpic1").append('<option value="Voice_Traffic">Voice Traffic</option>');
			 $("#kpic1").append('<option value="Data_Volume">Data Volume</option>');
			 $("#kpic1").append('<option value="Throughput">Throughput</option>');
			 
			 break;
	}
	});

	
	 $("#kpic-submit").click(function(){
        //get vars
        var kpi = {};
        kpi.date = $("#kpic-datepicker").val();
        kpi.tw = $("#kpic-tw-combo").val();
        kpi.tech = $("#kpic-tech-combo").val();
        kpi.kpi1 = $("#kpic1").val();
        kpi.op1 = $("#kpic-op1").val();
        kpi.val1 = $("#kpic-val1").attr('value');

        viewparams = "type:kpic;date:" + kpi.date + ";tech:" + kpi.tech + ";tw:" + kpi.tw + ";kpi1:" + kpi.kpi1 + ";op1:" + kpi.op1 + ";val1:" + kpi.val1;

        $('#layer-list > li').each(function(index) {
                if ($(this).hasClass('active') && $(this).hasClass('layerText'))
                        toggleLayer($(this).attr("group"),$(this).attr("layer"));
                enabledLayer.group=$(this).attr("group");
        });
        toggleLayer(enabledLayer.group,kpi.tech);
     	generateKpiCellLegend();	// (mrt)
        });

	// KPI Selected
	$("#kpi-datepicker-control").datepicker();

	$("#kpi-menu-li").click(function(){ 
		$('#site-attributes').hide();
		$('#kpi-menu').show();
		$('#parameter-menu').hide();
   		$('#kpicd-menu').hide();
		$('#alarm-menu').hide();
		$('#rules-menu').hide();
		$('#neighbors-menu').hide();
        $('#nlissues-menu').hide();
		$('#kpic-menu').hide();
		$('#kpid-menu').hide();
		$('#drive-test-menu').hide();
		$('#disaster-feed-menu').hide();
		$('#field-tech-menu').hide();
		$('#csl-nlt-menu').hide();
		$('#geow-menu').hide();
		viewparams = undefined ;
		$('#layer-list > li').each(function(index) {
            if ($(this).hasClass('active') && $(this).hasClass('layerText')) {
                    toggleLayer($(this).attr("group"),$(this).attr("layer"));
                    toggleLayer($(this).attr("group"),$(this).attr("layer"));
            }
        });
		if(!bottomPanelCollapsed) {
			cleanBackgroundColor();
			$("#kpi-menu-li").css('backgroundColor', '#fff');
		}
		
		generateKpiLegend();  //(mrt)
	});
	
	$("#kpi-menu-li").dblclick(function(){
		cleanBackgroundColor();
		if(bottomPanelCollapsed) {
			$("#kpi-menu-li").css('backgroundColor', '#fff');
		} 
		toggleBottomPanel();

		generateKpiLegend(); // (mrt)
	});

	$("#field-tech-menu-li").click(function(){ 
		$('#site-attributes').hide();
		$('#kpi-menu').hide();
		$('#parameter-menu').hide();
   		$('#kpicd-menu').hide();
		$('#alarm-menu').hide();
		$('#rules-menu').hide();
		$('#neighbors-menu').hide();
        $('#nlissues-menu').hide();
		$('#kpic-menu').hide();
		$('#kpid-menu').hide();
		$('#drive-test-menu').hide();
		$('#disaster-feed-menu').hide();
		$('#field-tech-menu').show();
		$('#csl-nlt-menu').hide();
		$('#geow-menu').hide();
	
		if(!bottomPanelCollapsed) {
			cleanBackgroundColor();
			$("#field-tech-menu-li").css('backgroundColor', '#fff');
		}
		
		jQuery("#list_field_tech").jqGrid({
			datatype: "local",
			height:250,
			colNames:['ID', 'Owner', 'Active', 'Modify date', 'Notes'],
			colModel: [
			{name:'id',index:'id',width:50, sorttype:"int"},
			{name:'owner', index:'owner asc', width: 100},
			{name:'active',index:'active',width:50, sorttype:"int"},
			{name:'modifydate', index:'modifydate', sorttype:"date", width:100},
			{name:'notes', index:'note', width:200, sortable:false}
			],
			multiselect: false,
			rowNum:10,
			rowList:[10,20,30],
			pager:'#pager_field_tech'
			});
		$('#radio_fieldtech_div_controls').buttonset();	
		
	});
	
	function toggleControl_fieldtech() {
        for(key in controls_fieldtech) {
            var control = controls_fieldtech[key];
            if(active_control_fieldtech == key) {
                control.activate();
            } else {
                control.deactivate();
            }
        }
    }
	
	var curredit_geomtype = "";
	var currtitle_ftinput = "";
	var currnotes_ftinput = "";
	var currpoints_ftinput = "";
	var currcascad_ftinput = "";
	
    function report_fieldtech(event)
	{
	    console.log(event.feature);
		curredit_geomtype = "";
		currtitle_ftinput = "";
		currnotes_ftinput = "";
		currpoints_ftinput = "";
		
		switch(event.feature.geometry.CLASS_NAME)
		{
			case "OpenLayers.Geometry.Point":
			currpoints_ftinput = event.feature.geometry.x + " " + event.feature.geometry.y;
			curredit_geomtype = "point"
			break;
			case "OpenLayers.Geometry.LineString":
			curredit_geomtype = "line";
			$.each(event.feature.geometry.components, function(key, value){
			     console.log(value);
				if(currpoints_ftinput != "")
				{
					currpoints_ftinput = currpoints_ftinput + ","+ value.x+ " "+value.y;
				}
				else
				{
					currpoints_ftinput = value.x+ " "+value.y;
				}
			});
			break;
			case "OpenLayers.Geometry.Polygon":
			$.each(event.feature.geometry.components[0].components, function(key, value){
			     console.log(value);
				if(currpoints_ftinput != "")
				{
					currpoints_ftinput = currpoints_ftinput + ","+ value.x+ " "+value.y;
				}
				else
				{
					currpoints_ftinput = value.x+ " "+value.y;
				}
			});
			curredit_geomtype = "polygon";
			break;
		}
		
		if(event.type == "sketchcomplete")
		{
		    var _lng = currpoints_ftinput.split(",")[0].split(" ")[0];
	        var _lat = currpoints_ftinput.split(',')[0].split(' ')[1];
			var _url = "http://138.85.245.145/geo/search?centerLat="+_lat+"&centerLng="+_lng;
			$( "#ftInputCascad" ).autocomplete(
			{
			    source: _url,
				minLength: 1,
				select: function( event, ui ) {
				$( "#ftInputCascad" ).val(ui.key);
				}
			});
			$('#ftinputModal').modal('show');
		}else if(event.type == "afterfeaturemodified")
		{
			$('#ftcinputModal').modal('show');
		}
	}
	
	$("#submit_ftinput").click(function(){
	    currtitle_ftinput = $("#ftInputTitle").val();
		currnotes_ftinput = $("#ftInputNotes").val();
		currcascad_ftinput = $("#ftInputCascad").val();
var _url = "http://evas.ericsson.net/emap/create/ftinput/"+user.client+"/"+user.name+"?title="+currtitle_ftinput+"&notes="+currnotes_ftinput+"&geomtype="+curredit_geomtype+"&points="+currpoints_ftinput+"&cascad="+currcascad_ftinput;
		$.ajax({
				  url: _url,
				  context: document.body
				}).done(function() {
						  $( "#dialog-message" ).dialog({
							  modal: true,
							  buttons: {
								Ok: function() {
								  $( this ).dialog( "close" );
								}
							  }
							});
				});
		console.log("SD - Test1");
		clearFieldTechVectorLayer();
		refreshFieldTechVectorLayer();
		$('#ftinputModal').modal('hide');
		
	})
	
	function clearFieldTechVectorLayer()
	{
		var _vectorLayer = map.getLayersByName("Field Tech - Vector Layer")[0];
		_vectorLayer.destroyFeatures();
	}
	
	function refreshFieldTechVectorLayer()
	{
	   // var _url = "http://evas.ericsson.net/emap/get/ftinput/"+user.client;
	   // $.ajax({url:_url}).done(function(data){
	   // console.log(data);
	   // });
	}
	
    $("#pointToggle_fieldtech").click(function(){
	active_control_fieldtech = "point_fieldtech";
	toggleControl_fieldtech();
	});	

	$("#lineToggle_fieldtech").click(function(){
		active_control_fieldtech = "line_fieldtech";
		toggleControl_fieldtech();
	});	

	$("#polygonToggle_fieldtech").click(function(){
		active_control_fieldtech = "polygon_fieldtech";
		toggleControl_fieldtech();
	});		
	
	$("#noneToggle_fieldtech").click(function(){
		active_control_fieldtech = "none_fieldtech";
		toggleControl_fieldtech();
	});	

	$("#modifyToggle_fieldtech").click(function(){
		active_control_fieldtech = "modify_fieldtech";
		toggleControl_fieldtech();
	});	
		
    $("#field-tech-menu-li").dblclick(function(){
		cleanBackgroundColor();
		if(bottomPanelCollapsed) {
			$("#field-tech-menu-li").css('backgroundColor', '#fff');
		} 
		toggleBottomPanel();
	});

	$("#tech-combo").change(function(){
	 $("#kpia1").empty();
	 $("#kpia2").empty();
	 $("#kpia3").empty();
	switch($("#tech-combo").val()) {
		case 'cdma':
		case 'evdo':
			 $("#kpia1").append('<option>Blocks</option>');
			 $("#kpia1").append('<option>Dropst</option>');
			 $("#kpia1").append('<option>Drop_perc</option>');
			 $("#kpia1").append('<option selected="selected">Block_perc</option>')
			 $("#kpia1").append('<option>ATT</option>');
			 $("#kpia1").append('<option>MOU</option>');
			 $("#kpia2").append('<option>Blocks</option>');
			 $("#kpia2").append('<option>Drops</option>');
			 $("#kpia2").append('<option selected="selected">Drop_perc</option>');
			 $("#kpia2").append('<option>Block_perc</option>')
			 $("#kpia2").append('<option>ATT</option>');
			 $("#kpia2").append('<option>MOU</option>');
			 $("#kpia3").append('<option>Blocks</option>');
			 $("#kpia3").append('<option>Drops</option>');
			 $("#kpia3").append('<option>Drop_perc</option>');
			 $("#kpia3").append('<option>Block_perc</option>')
			 $("#kpia3").append('<option>ATT</option>');
			 $("#kpia3").append('<option selected="selected">MOU</option>');
			 break;
		 case 'UMTS':
			 $("#kpia1").append('<option value="Voice_DR">Voice DCR(%)</option>');
			 $("#kpia1").append('<option selected="selected" value="Voice_AFR">Voice AFR(%)</option>')
			 $("#kpia1").append('<option value="PS_AFR">PS AFR(%)</option>');
			 $("#kpia1").append('<option value="PS_RAB_DR">PS DCR(%)</option>');
			 $("#kpia1").append('<option value="HSDPA_PS_AFR">HSDPA AFR(%)</option>');
			 $("#kpia1").append('<option value="HSDPA_PS_DCR">HSDPA DCR(%)</option>');
			 $("#kpia2").append('<option selected="selected" value="Voice_DR">Voice DCR(%)</option>');
			 $("#kpia2").append('<option value="Voice_AFR">Voice AFR(%)</option>')
			 $("#kpia2").append('<option value="PS_AFR">PS AFR(%)</option>');
			 $("#kpia2").append('<option value="PS_RAB_DR">PS DCR(%)</option>');
			 $("#kpia2").append('<option value="HSDPA_PS_AFR">HSDPA AFR(%)</option>');
			 $("#kpia2").append('<option value="HSDPA_PS_DCR">HSDPA DCR(%)</option>');
			 $("#kpia3").append('<option value="Voice_DR">Voice DCR(%)</option>');
			 $("#kpia3").append('<option value="Voice_AFR">Voice AFR(%)</option>')
			 $("#kpia3").append('<option value="PS_AFR">PS AFR(%)</option>');
			 $("#kpia3").append('<option value="PS_RAB_DR">PS DCR(%)</option>');
			 $("#kpia3").append('<option selected="selected" value="HSDPA_PS_AFR">HSDPA AFR(%)</option>');
			 $("#kpia3").append('<option value="HSDPA_PS_DCR">HSDPA DCR(%)</option>');
			 break;
						 
		case 'LTE':
			 $("#kpia1").append('<option value="L_SESSION_SETUP_SUCCESS_RATE">Session Set Up Success Rate(%)</option>');
			 $("#kpia1").append('<option selected="selected" value="L_CSFB_TO_WCDMA_ACTIVITY_RATE">CSFB Rate(%)</option>')
			 $("#kpia1").append('<option value="L_IRAT_HANDOVER_RATE ">IRAT Rate(%)</option>');
			 $("#kpia1").append('<option value="L_ERAB_DROP_RATE ">ERAB Drop Rate(%)</option>');
			 $("#kpia2").append('<option selected="selected" value="L_SESSION_SETUP_SUCCESS_RATE">Session Set Up Success Rate(%)</option>');
			 $("#kpia2").append('<option value="L_CSFB_TO_WCDMA_ACTIVITY_RATE">CSFB Rate(%)</option>')
			 $("#kpia2").append('<option value="L_IRAT_HANDOVER_RATE">IRAT Rate(%)</option>');
			 $("#kpia2").append('<option value="L_ERAB_DROP_RATE">ERAB Drop Rate(%)</option>');
			 $("#kpia3").append('<option value="L_SESSION_SETUP_SUCCESS_RATE">Session Set Up Success Rate(%)</option>');
			 $("#kpia3").append('<option value="L_CSFB_TO_WCDMA_ACTIVITY_RATE">CSFB Rate(%)</option>')
			 $("#kpia3").append('<option value="L_IRAT_HANDOVER_RATE">IRAT Rate(%)</option>');
			 $("#kpia3").append('<option selected="selected" value="L_ERAB_DROP_RATE">ERAB Drop Rate(%)</option>');
			 break;
			 
		case 'GSM':
			 $("#kpia1").append('<option value="Voice_DCR">Voice DCR(%)</option>');
			 $("#kpia1").append('<option selected="selected" value="NAF"> NAF(%)</option>')
			 $("#kpia1").append('<option value="Handover_SR">Handover SR(%)</option>');
			 $("#kpia1").append('<option value="Voice_Traffic">Voice Traffic</option>');
			 $("#kpia1").append('<option value="Data_Volume">Data Volume</option>');
			 $("#kpia1").append('<option value="Throughput">Throughput</option>');
			 $("#kpia2").append('<option selected="selected" value="Voice_DCR">Voice DCR</option>');
			 $("#kpia2").append('<option value="NAF">NAF(%)</option>')
			 $("#kpia2").append('<option value="Handover_SR">Handove SR(%)</option>');
			 $("#kpia2").append('<option value="Voice_Traffic">Voice Traffic</option>');
			 $("#kpia2").append('<option value="Data_Volume">Data Volume</option>');
			 $("#kpia2").append('<option value=Throughput">Throughput</option>');
			 $("#kpia3").append('<option value="Voice_DCR">Voice DCR(%)</option>');
			 $("#kpia3").append('<option value="NAF">NAF(%)</option>')
			 $("#kpia3").append('<option value="Handover_SR">Handover SR(%)</option>');
			 $("#kpia3").append('<option value="Voice_Traffic">Voice Traffic</option>');
			 $("#kpia3").append('<option selected="selected" value="Data_Volume">Data Volume</option>');
			 $("#kpia3").append('<option value="Throughput">Throughput</option>');
			 break;
	}
	});

	$("#kpi-submit").click(function(){ 
		//get vars
		var kpi = {};
		kpi.date = $("#kpi-datepicker").val();
		kpi.tw = $("#tw-combo").val();
		kpi.tech = $("#tech-combo").val();
		kpi.kpi1 = $("#kpia1").val();
		kpi.op1 = $("#op1").val();
		kpi.val1 = $("#val1").attr('value');
		
		kpi.kpi2 = $("#kpia2").val();
		kpi.op2 = $("#op2").val();
		kpi.val2 = $("#val2").attr('value');
		
		kpi.kpi3 = $("#kpia3").val();
		kpi.op3 = $("#op3").val();
		kpi.val3 = $("#val3").attr('value');
		
		viewparams = "type:kpia;date:" + kpi.date + ";tech:" + kpi.tech + ";tw:" + kpi.tw + ";kpi1:" + kpi.kpi1 + ";op1:" + kpi.op1 + ";val1:" + kpi.val1 +  ";kpi2:" + kpi.kpi2 + ";op2:" + kpi.op2 + ";val2:" + kpi.val2 +  ";kpi3:" + kpi.kpi3 + ";op3:" + kpi.op3 + ";val3:" + kpi.val3;

		$('#layer-list > li').each(function(index) {
		    if ($(this).hasClass('active') && $(this).hasClass('layerText')) 	
				toggleLayer($(this).attr("group"),$(this).attr("layer"));	
				enabledLayer.group=$(this).attr("group");
		});
		
	    $('#layer-list > li').each(function(index) {
			if(kpi.tech == $(this).attr("search_group"))
			{
				toggleLayer(enabledLayer.group, $(this).attr("layer"));
			}
		});
		//toggleLayer(enabledLayer.group,kpi.tech);
		//alert('todo: toggle layer');
		
		generateKpiLegend();  // (mrt)
	});
	
	function cleanBackgroundColor() {   		

		$("#site-attributes-li").css('backgroundColor', '#000');
		$("#kpid-menu-li").css('backgroundColor', '#000');
		$("#nlissues-menu-li").css('backgroundColor', '#000');
		$("#neighbors-menu-li").css('backgroundColor', '#000');
		$("#rules-menu-li").css('backgroundColor', '#000');
		$("#alarm-menu-li").css('backgroundColor', '#000');
		$("#parameter-menu-li").css('backgroundColor', '#000');
		$("#kpic-menu-li").css('backgroundColor', '#000');
		$("#kpicd-menu-li").css('backgroundColor', '#000');
		$("#kpi-menu-li").css('backgroundColor', '#000');
		$("#drive-test-menu-li").css('backgroundColor', '#000');
		$("#disaster-feed-menu-li").css('backgroundColor', '#000');
		$("#field-tech-menu-li").css('backgroundColor', '#000');
		$('#csl-nlt-menu-li').css('backgroundColor', '#000');
		$('#geow-menu-li').css('backgroundColor', '#000');
	}
	
	function toggleBottomPanel (){
		if(bottomPanelCollapsed){
			$("#bottom").animate({ bottom: "250px" }, 300);
			$(".subnav-fixed").animate({ bottom: "250px" }, 400);
		} else{
			cleanBackgroundColor();
			$("#bottom").animate({ bottom: "0px" }, 400);
			$(".subnav-fixed").animate({ bottom: "0px" }, 400);
		}
		bottomPanelCollapsed = !bottomPanelCollapsed;
	}
	
	
	$("#kpi-list").jqGrid({
		datatype: 'json',
		mtype: 'GET',
		colModel :[ 
			{name:'Site ID', index:'key', width:150}, 
			{name:'Sector', index:'sector', width:50}, 
			{name:'Drop%', index:'drop_perc', width:50},
			{name:'Block%', index:'block_perc', width:50},
			{name:'Drops', index:'drop', width:50},
			{name:'Blocks', index:'block', width:50},
			{name:'Attempts', index:'attempts', width:70},
			{name:'MOU', index:'mou', width:50}
		],
		pager: '#kpi-pager',
		height:200,
		rowNum:10,
		sortname: 'key',
		sortorder: 'desc',
		viewrecords: true,
		gridview: true
	
	});

	 $("#kpic-list").jqGrid({
	       datatype: 'json',
	       mtype: 'GET',
	       colModel :[
	               {name:'Cell Id', index:'key', width:150},
	               {name:'Drop%', index:'drop_perc', width:50},
	               {name:'Block%', index:'block_perc', width:50},
	               {name:'Drops', index:'drop', width:50},
	               {name:'Blocks', index:'block', width:50},
	               {name:'Attempts', index:'attempts', width:70},
	               {name:'MOU', index:'mou', width:50},
				{name:'Alarms', index:'sector', width:50},
				{name:'Changes', index:'sector', width:60},
				{name:'Compliance', index:'sector', width:70},
				{name:'Rules', index:'sector', width:50}
	       ],
	       pager: '#kpic-pager',
	       height:200,
	       rowNum:10,
	       sortname: 'key',
	       sortorder: 'desc',
	       viewrecords: true,
	       gridview: true
       });


	 $("#kpicd-list").jqGrid({
	        datatype: 'json',
	        mtype: 'GET',
	        colModel :[
	                {name:'Cell Id', index:'key', width:150},
	                {name:'Drop%', index:'drop_perc', width:50},
	                {name:'Block%', index:'block_perc', width:50},
	                {name:'Drops', index:'drop', width:50},
	                {name:'Blocks', index:'block', width:50},
	                {name:'Attempts', index:'attempts', width:70},
	                {name:'MOU', index:'mou', width:50},
				{name:'Alarms', index:'sector', width:50},
				{name:'Changes', index:'sector', width:60},
				{name:'Compliance', index:'sector', width:70},
				{name:'Rules', index:'sector', width:50}
	        ],
	        pager: '#kpicd-pager',
	        height:200,
	        rowNum:10,
	        sortname: 'key',
	        sortorder: 'desc',
	        viewrecords: true,
	        gridview: true

        });

		$("#parameter-list").jqGrid({
			//url:'138.85.245.136/geo/grid/T-MobileA:UMTSCell',
            datatype: 'json',
            mtype: 'GET',
            colModel :[
                {name:'Cell Id', index:'key', width:150},
                //{name:'All', index:'UtrancellId', width:50},
                {name:'Eul', index:'Eul', width:50},
                {name:'FACh', index:'Fach', width:50},
                {name:'HSDSCh', index:'Hsdsch', width:60},
                {name:'NodeBFunction', index:'NodeBFunction', width:90},
                {name:'RBSLocalCell', index:'RbsLocalCell', width:80},
                {name:'UTRANCell', index:'total', width:120}
            ],
            pager: '#parameter-pager',
            height:200,
            rowNum:10,
            sortname: 'key',
            sortorder: 'desc',
            viewrecords: true,
            gridview: true

		});
		$("#parameter-list").jqGrid('navGrid','#parameter-pager',{edit:false,add:false,del:false});

		$("#geow-list").jqGrid({
			//url:'138.85.245.136/geo/grid/T-MobileA:UMTSCell',
            datatype: 'json',
            mtype: 'GET',
            colModel :[
                {name:'Cell Id', index:'key', width:150},
                //{name:'All', index:'UtrancellId', width:50},
                {name:'one', index:'one', width:50},
                {name:'two', index:'two', width:50},
                {name:'three', index:'three', width:60},
                {name:'four', index:'four', width:90},
                {name:'five', index:'five', width:80},
                {name:'six', index:'six', width:120}
            ],
            pager: '#geow-pager',
            height:200,
            rowNum:10,
            sortname: 'key',
            sortorder: 'desc',
            viewrecords: true,
            gridview: true

		});
		$("#geow-list").jqGrid('navGrid','#geow-pager',{edit:false,add:false,del:false});

		$("#alarm-list").jqGrid({
            datatype: 'json',
            mtype: 'GET',
            colModel :[
                    {name:'Alarm Id', index:'key', width:80},
                    {name:'Cell Id', index:'cell', width:80},
                    {name:'Date/Time', index:'alarm_time', width:125},
                    {name:'All', index:'all', width:50},
                    {name:'Critical', index:'critical', width:50},
                    {name:'Major', index:'major', width:50},
                    {name:'Minor', index:'minor', width:50},
                    {name:'Indeterminate', index:'indeterminate', width:90},
                    {name:'Warning', index:'warning', width:50},
                    {name:'Cleared', index:'cleared', width:50}
            ],
            pager: '#alarm-pager',
            height:200,
            rowNum:10,
            sortname: 'key',
            sortorder: 'desc',
            viewrecords: true,
            gridview: true

        });
    $("#alarm-list").jqGrid('navGrid','#alarm-pager',{edit:false,add:false,del:false});

	$("#rules-list").jqGrid({
        datatype: 'json',
        mtype: 'GET',
        colModel :[
                {name:'Cell Id', index:'key', width:150},
                {name:'All', index:'drop_perc', width:70},
                {name:'Voice Drop', index:'block_perc', width:70},
                {name:'Voice AF', index:'drop', width:70},
                {name:'PS Drop', index:'block', width:70},
                {name:'PS AF', index:'attempts', width:70},
                {name:'HSDPA Drop', index:'mou', width:80},
				{name:'HSDPA AF', index:'mou', width:70},
				{name:'Capacity', index:'mou', width:70}
            ],
            pager: '#rules-pager',
            height:200,
            rowNum:10,
            sortname: 'key',
            sortorder: 'desc',
            viewrecords: true,
            gridview: true

    });

	$("#drive-test-list").jqGrid({
        datatype: 'json',
        mtype: 'GET',
        colModel :[
            {name:'Source', index:'source', width:150},
            {name:'HEPE', index:'HEPE', width:70},
            {name:'GPSRSSI', index:'GPSRSSI', width:70},
            {name:'Band', index:'Band', width:70}
        ],
		pager: '#drive-test-pager',
        height:200,
        rowNum:10,
        sortname: 'source',
        sortorder: 'desc',
        viewrecords: true,
        gridview: true

	});

	var csl_nlt_list_bsc_selected = "";
	var csl_nlt_list_cell_selected = "";
	var csl_nlt_list_sector_selected = "";
	var csl_nlt_list_band_selected = "";
	var csl_nlt_list_ebid_selected = "";
	var csl_nlt_list_lat_selected = "";
	var csl_nlt_list_lng_selected = "";
	$('#csl-nlt-list').jqGrid({
         datatype: 'json',
         mtype: 'GET',
         colModel :[
             {name:'EBID', index:'key', width:70},
             {name:'BSC', index:'bsc', width:150},
             {name:'Cell', index:'cell', width:50},
             {name:'Sector', index:'sector', width:70},
             {name:'Band', index:'band', width:60},
             {name:'Lat', index:'lat', width:150},
             {name:'Lng', index:'lng', width:150}
         ],
         pager: '#csl-nlt-pager',
         height:200,
         rowNum:10,
         sortname: 'key',
         sortorder: 'desc',
         viewrecords: true,
         gridview: true,  
         onSelectRow: function(rowId, status, event) {
        	 csl_nlt_list_ebid_selected   = $(this).jqGrid('getCell', rowId, 0);
        	 csl_nlt_list_bsc_selected    = $(this).jqGrid('getCell', rowId, 1);
        	 csl_nlt_list_cell_selected   = $(this).jqGrid('getCell', rowId, 2);
        	 csl_nlt_list_sector_selected = $(this).jqGrid('getCell', rowId, 3);
        	 csl_nlt_list_band_selected   = $(this).jqGrid('getCell', rowId, 4);
        	 csl_nlt_list_lat_selected    = $(this).jqGrid('getCell', rowId, 5);
        	 csl_nlt_list_lng_selected    = $(this).jqGrid('getCell', rowId, 6);
         }

     });

	 $("#nlissues-list").jqGrid({
         datatype: 'json',
         mtype: 'GET',
         colModel :[
             {name:'Cell Id', index:'key', width:150},
             {name:'All', index:'drop_perc', width:70},
             {name:'Changes', index:'block_perc', width:70},
             {name:'Missing', index:'drop', width:70},
             {name:'Undeclared', index:'block', width:70},
             {name:'Tier2 Conflict', index:'attempts', width:70},
             {name:'Tier3 Conflict', index:'mou', width:80}
         ],
         pager: '#nlissues-pager',
         height:200,
         rowNum:10,
         sortname: 'key',
         sortorder: 'desc',
         viewrecords: true,
         gridview: true

 });


	$("#neighbors-list").jqGrid({
            datatype: 'json',
            mtype: 'GET',
            colModel :[
                {name:'Cell', index:'key', width:130},
                {name:'N_Cell_Id', index:'cell_id', width:350},
                {name:'Completed', index:'completed', width:70, align:'center'},
                {name:'Attempts', index:'attempts', width:70, align:'center'},
                {name:'CHP', index:'chp', width:70, align:'center'},
                {name:'latitude', index:'latitude', width:1},
                {name:'longitude', index:'longitude', width:1},
                {name:'group', index:'group', width:1},
                {name:'layer', index:'layer', width:1}
            ],
            pager: '#neighbors-pager',
            height:200,
            rowNum:10,
            sortname: 'key',
            sortorder: 'desc',
            viewrecords: true,
            gridview: true,
            onSelectRow: function (rowId, status, event) {
                if (!event || event.which === 1) {
					//zoom to lat lng 
                	var mapZoomLevel = 15;
                	map.setCenter(new OpenLayers.LonLat($(this).jqGrid('getCell', rowId, 'longitude'), $(this).jqGrid('getCell', rowId, 'latitude')).transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject()), mapZoomLevel);
            		map.zoom = mapZoomLevel;
    				var kpi = {};
                    kpi.cell = $(this).jqGrid('getCell', rowId, 'Cell').substring(0, 11);
                    kpi.HO_Type = $(this).jqGrid('getCell', rowId, 'Cell').substring(12, 18);
                    kpi.date = $("#neighbors-datepicker").val();
                    kpi.tech = $("#neighbors-tech-combo").val();
                    kpi.techtype = $("#neighbors-type :selected").val();
                    if($("#neighbors-displayall").attr('checked')) {
                		kpi.displayall = 'true';
                    } else {
                    	kpi.displayall = 'false';
                    }
                   
                 	var bb = map.getExtent().transform( map.projection, new OpenLayers.Projection("EPSG:4326"));
                    viewparams = "type:neighbors;act:colorize;cell:" + kpi.cell + ";date:" + kpi.date + ";tech:" + kpi.tech  + ";techtype:" + kpi.techtype  + ";HO_Type:" + kpi.HO_Type  + ";displayall:" + kpi.displayall;
                	var thisGroup = '';
                	var thisLayer = '';
                    $('#layer-list > li').each(function(index) {
                         if ($(this).hasClass('active') && $(this).hasClass('layerText')) {
                        	thisGroup = $(this).attr("group");
                        	thisLayer = $(this).attr("layer");
                            toggleLayer($(this).attr("group"),$(this).attr("layer"));
                        }
                        enabledLayer.group=$(this).attr("group");
                    });
console.log('mrp 2768 ' + enabledLayer.group + ', ' + kpi.tech)
                    reloadNeighborsMap(enabledLayer.group, kpi.tech);
                }
            }
        });
		function reloadNeighborsMap(group, tech) {
console.log('mrp 2773 ' + group + ', ' + tech)
			map.addLayer(toggleLayer(group, tech));
		}
		$("#neighbors-list").jqGrid('navGrid','#neighbors-pager',{edit:false,add:false,del:false});
		jQuery("#neighbors-list").hideCol(['latitude']);
		jQuery("#neighbors-list").hideCol(['longitude']);
		jQuery("#neighbors-list").hideCol(['group']);
		jQuery("#neighbors-list").hideCol(['layer']);

		
	$("#data-export").click(function(){ 
		//TODO
	});
	
	var bb = map.getExtent().transform( map.projection, new OpenLayers.Projection("EPSG:4326"));
	var p = bb.getCenterLonLat();
	$( "#search" ).combogrid({
		url: 'geo/search/default',
		minLength: 4,
		debug:true,
		width: "300px",
		autoFocus: true,
		centerLat: p.lat,
		centerLng: p.lon,
		bottom : bb.bottom,
		top: bb.top,
		left: bb.left,
		right: bb.right,
		colModel: [{'columnName':'key','label':'Results', 'align': 'center'}],
		select: function( event, ui ) {
			 $( "#search" ).val( ui.item.key );
			
			 $('#layer-list > li').each(function(index) {
			
		     if ((!$(this).hasClass('active')) && $(this).hasClass('layerText')) 
			 {	
			   if($(this).attr("pg_key") == ui.item.layer)
			   {
				toggleLayer($(this).attr("group"),$(this).attr("layer"));
			   }
			 }
			 });
			//move marker layer to top
			var numLayers = map.getNumLayers();
			//map.setLayerIndex(markers, numLayers );
			map.raiseLayer(markers, 10);
			//add marker
			markers.clearMarkers();
			var size = new OpenLayers.Size(40,37);
            var offset = new OpenLayers.Pixel(-(size.w/2)+10, -size.h);
            var icon = new OpenLayers.Icon('https://chart.googleapis.com/chart?chst=d_map_pin_letter_withshadow&chld=|FF0000|000000', size, offset);
            markers.addMarker(new OpenLayers.Marker(new OpenLayers.LonLat(ui.item.lng,ui.item.lat).transform(new OpenLayers.Projection("EPSG:4326"), map.projection),icon));
			
			//pan map
			map.setCenter(new OpenLayers.LonLat(ui.item.lng, ui.item.lat).transform(new OpenLayers.Projection("EPSG:4326"), map.projection), 15)
			// Turn on the appropriate layer
			return false;
		}
	});

	//search pop-over
	var searchPrefixContent = "<ul><li>A:Street Address</li><li>S:Site Name</li></ul>";
	//$('#search').popover({content: searchPrefixContent, title:'Search Prefixes', placement:'bottom'})
	
	//Toggle Tools 
	$('ul[type="tools"] > li').click(function(){
		var me = $(this);
		var tool = $.trim($(this).text());
		
		//toggle active
		if(enabledTool != tool){

			//disable
			$('ul[type="tools"] > li').each(function(index){
	
				if($.trim($(this).text()) == enabledTool){
					$(this).removeClass("active");		
				}
				var me = $(this);
				var tool = $.trim($(this).text());
				toggleTool(tool, me);
			});
			
			//enable
			$(this).toggleClass("active");
			toggleTool(tool, me);
			
			//save
			if(tool != "Saved Polygons") {
				enabledTool = tool;
			}
		} else {
			//disable
			$(this).removeClass("active");
			toggleTool(tool, $(this));
			enabledTool = 'null';
		}
	});
	
	function toggleTool(tool, me){
		switch(tool){
		case 'Ruler':
			if(me.hasClass('active'))
				measure.activate();
			else
				measure.deactivate();
			break;
			
		case 'Polygon':
			if(me.hasClass('active')) {
				poly.activate();
			} else {
				poly.deactivate();
				isPolygonToolActive = false;
			}
			break;
			
		case 'Saved Polygons':
			if(me.hasClass('active')) {
				getPoly();
				me.removeClass("active");
			}
			break;
			
		case 'Elevation':
			if(me.hasClass('active'))
				isElevationToolActive = true;
			else
				isElevationToolActive = false;
			break;
			
		case 'Terrain Profile':
			if(me.hasClass('active')){
				measure.activate();
				isTerrainProfileToolActive = true;
			}else{
				measure.deactivate();
				isTerrainProfileToolActive = false;
				 $("#coltools").animate({ right: "-600px" }, 400);
				 $("#coltools").hide();
			}
			break;
		
		case 'Street View':
			if(me.hasClass('active')) {
				isStreetViewToolActive = true;
			} else {
				isStreetViewToolActive = false;
				 $("#coltoolstreet").animate({ right: "-600px" }, 400);
				 $("#coltoolstreet").hide();
			}
			break;

		case 'Import MapInfo File(.mif)':
			if(me.hasClass('active')) {
				isImportToolActive = true;
				getFile();
			} else {
				isImportToolActive = false;
				$("#importfile").animate({ right: "-600px" }, 400);
				$("#importfile").hide();
			}
			break;
		// case 'DR FT':
			// $('#drFTModal').modal('show');
			// jQuery("#drfeed_list").jqGrid({
			// datatype: "local",
			// height:250,
			// colNames:['ID', 'Owner', 'Create date', 'Notes'],
			// colModel: [
			// {name:'id',index:'id',width:50, sorttype:"int"},
			// {name:'owner', index:'owner asc', width: 100},
			// {name:'createdate', index:'createdate', sorttype:"date", width:100},
			// {name:'notes', index:'note', width:200, sortable:false}
			// ],
			// multiselect: false,
			// rowNum:10,
			// rowList:[10,20,30],
			// pager:'#drfeed_pager'
			// });
		// break;
		}
	}

	function getFile() {
		$('#uploadFileModal').modal('show');
		// show get file dialog
		if(userServer == '127.0.0.1') {
			iframeSrcStr = "http://127.0.0.1/emap/protected/helpers/getUpload.php";
		} else {
			iframeSrcStr = "http://evas.ericsson.net/emap/protected/helpers/getUpload.php";
		}
		document.getElementById("uploadinput").src = iframeSrcStr;
	}
	
	function getPoly() {
		$('#polygonOutputModal').modal('show');
		// show get poly dialog
		if(userServer == '127.0.0.1') {
			iframeSrcStr = "http://127.0.0.1/emap/protected/helpers/polyGet.php";
		} else {
			iframeSrcStr = "http://evas.ericsson.net/emap/protected/helpers/polyGet.php";
		}
		document.getElementById("polyiframeoutput").src = iframeSrcStr;
	}
	
	function handlePoly(event) {
		polygonId = event.feature.geometry.id;
		polygonBounds = event.feature.geometry.getBounds();
		polygonGeometry = event.feature.geometry;
		$('#polygonInputModal').modal('show');
		if(userServer == '127.0.0.1') {
			iframeSrcStr = "http://127.0.0.1/emap/protected/helpers/polyForm.php";
		} else {
			iframeSrcStr = "http://evas.ericsson.net/emap/protected/helpers/polyForm.php";
		}
		document.getElementById("polyiframedetail").src = iframeSrcStr;
       if(isPolygonToolActive) { 
			map.addPopup(popup);
		}
		poly.deactivate();
		poly.activate();
    }
	
	//Ruler measurement callback
    function handleMeasurements(event) {
        var geometry = event.geometry;
        var units = event.units;
        var order = event.order;
        var measure_result = event.measure;
		
		//Terrarin Profile
		if(isTerrainProfileToolActive){

			//build Elevator Service Request
			var latlngs = [];
			for(i = 0 ; i < geometry.components.length; i++){
				var point = geometry.components[i];
				point.transform( map.projection, new OpenLayers.Projection("EPSG:4326"));
				latlngs.push( new google.maps.LatLng(point.y,point.x));
			}
 
			//send request
			elevator.getElevationAlongPath({ 
				path: latlngs, 
				samples: 256, 
				}, plotElevation);
		
		} else {
			//Ruler
			var miles = 0;
			switch(units){
				case 'm':
					miles = measure_result * 0.000621371;
				break;
				case 'km':
					miles = measure_result * 0.621371;
				break;
			}

			//last click coords
			var popup = new OpenLayers.Popup("chicken",
				OpenLayers.LonLat.fromString(geometry.components[geometry.components.length-1].toShortString()),
				new OpenLayers.Size(150,20),
				"<div style='font-size:.8em'>Distance: " + Math.round(miles*100)/100 +" miles</div>",
				true
			);
				   
			map.addPopup(popup);
		}
		//cleanup
		measure.deactivate();
		measure.activate();
	}
	
	//Chart terrain profile
	function plotElevation(results) {
		elevations = results;
		
		var path = [];
		for (var i = 0; i < results.length; i++) {
		  path.push(elevations[i].location);
		}
		
		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Sample');
		data.addColumn('number', 'Elevation');
		for (var i = 0; i < results.length; i++) {
			//convert to feet
			data.addRow(['', elevations[i].elevation * 3.28084]);
		}

		//document.getElementById('chart_terrain_profile').style.display = 'block';
		
		chart_terrain_profile.draw(data, {
		  width: 595,
		  height: 290,
		  legend: 'none' ,
		  titleY: 'Elevation (ft)',
		  focusBorderColor: '#00ff00'
		});
		

		//$("#terrain-profile-Modal").modal('show');
		 $("#coltools").show();
         $("#coltools").animate({ right: "0px" }, 400);

	}
	
	//Toggle Disaster Feeds
	$('#disaster-feed-list > li').click(function()
	{
		$(this).toggleClass("active");
		console.log("SD - "+$(this).attr("url"));
		togglefeed($(this).attr("url"), $(this).attr("icon"), $(this).attr("name"));
	}
	);
	
	function togglefeed(feed_url,icon,name)
	{ 
	    if(map.getLayersByName(name).length == 0)
		{
		var feed_style = new OpenLayers.Style({externalGraphic : icon, pointRadius : 10});
	    var feed_layer = new OpenLayers.Layer.Vector(name, {
	        projection: new OpenLayers.Projection("EPSG:4326"),
	        strategies: [new OpenLayers.Strategy.Fixed()],
			styleMap: new OpenLayers.StyleMap(feed_style),
	        protocol: new OpenLayers.Protocol.HTTP({
	            url: feed_url,
	            format: new OpenLayers.Format.GeoRSS()
        	})
		});
        map.addLayer(feed_layer);
		}
		else
		{
		map.removeLayer(map.getLayersByName(name)[0], true);
		}
	}
	
	//Toggle Labels
	$('ul[type="map-Labels"] > li').click(function(){
		var mapLabelName = $.trim($(this).text());
		
		if(enabledMapLabel != mapLabelName){
			$(this).toggleClass("active");
			$('ul[type="map-Labels"] > li').each(function(index){
				if($.trim($(this).text()) == enabledMapLabel)
					$(this).toggleClass("active");
			});
		}
		
		try {
					enabledMapLabel = mapLabelName;
				} catch(e) {
					console.log(e);
				}
				
		$('#layer-list > li').each(function(index) {
		
		    if ($(this).hasClass('active') && $(this).hasClass('layerText')) 
			{
				console.log("Test");
				toggleLayer($(this).attr("group"),$(this).attr("layer"));	
				toggleLayer($(this).attr("group"),$(this).attr("layer"));
			}
		});
		
		
		});
	
	//Toggle Renderer
	$('ul[type="map-Renderer"] > li').click(function(){
		if($(this).children("input").length > 0)
		{
			enabledMapRendererText = $.trim($(this).children("input").val());
		}
		else
		{
			enabledMapRendererText = '';
		}
	 	var mapRendererName = $.trim($(this).children("a").attr("name"));
		if(enabledMapRenderer != mapRendererName){
			$(this).toggleClass("active");
			$('ul[type="map-Renderer"] > li').each(function(index){
			    if($.trim($(this).children("a").attr("name")) == enabledMapRenderer)
					{  
						$(this).toggleClass("active");
					}
			});
		}
		try {
					enabledMapRenderer = mapRendererName;
				} catch(e) {
					console.log(e);
				}
		console.log("SD input value :"+enabledMapRendererText);		
		$('#layer-list > li').each(function(index) {
		
		    if ($(this).hasClass('active') && $(this).hasClass('layerText')) 
			{
				toggleLayer($(this).attr("group"),$(this).attr("layer"));	
				toggleLayer($(this).attr("group"),$(this).attr("layer"));
			}
		});
		
		});
		
	//Toggle Base Layers

	$('ul[type="base-layers"] > li').click(function(){
		var baseLayerName = $.trim($(this).text());
		//toggle active
		if(enabledBaseLayer != baseLayerName){
			//enable
			$(this).toggleClass("active");
			//disable
			$('ul[type="base-layers"] > li').each(function(index){
				if($.trim($(this).text()) == enabledBaseLayer)
					$(this).toggleClass("active");
			});
		}
		var l;
		for (l in baseLayers){
			if(baseLayers[l].name == baseLayerName) {
				if(baseLayerName == 'Bing Birds Eye') {
					isBirdsEyeToolActive = !isBirdsEyeToolActive;
					//$('#navigationbar').animate( { "top": "25px" }, 500 );
					
					var msCenter = map.getCenter();  
					msCenter.transform(map.projection, new OpenLayers.Projection("EPSG:4326"));
					var msLat = msCenter.lat;
					var msLon = msCenter.lon;
					var msLocation = new Microsoft.Maps.Location(msLat, msLon);

					//Bing Map Options: disableZooming: true, showDashboard: false, showMapTypeSelector: false, showScalebar: false
					bingmap = new Microsoft.Maps.Map(document.getElementById("mapDiv"), { credentials: "AqTGBsziZHIJYYxgivLBf0hVdrAk9mWO5cQcb8Yux8sW5M8c8opEC2lZqKR1ZZXf", zoom: 17, showMapTypeSelector: false, showDashboard: false, center: msLocation, mapTypeId:Microsoft.Maps.MapTypeId.birdseye });
				} else {
					isBirdsEyeToolActive = false;
				}
				$('#mapDropDownUL li').each(function() {
					$(this).removeClass("active");
				});
				$('#mapDropDownUL li').each(function() {
					if(baseLayerName == $.trim($(this).text())) {
						$(this).toggleClass("active");
					}
				});
/*				
				var mrp_str = '';
				var allitems = document.getElementsByTagName("*");
				for (var i = allitems.length; i--;) {
					if(parseInt(allitems[i].style.zIndex) > 0) {
						if(allitems[i].id.length != 0) { //allitems[i].id.indexOf("OpenLayers.") != -1 || allitems[i].id.indexOf("Tabs") != -1) {
							mrp_str += allitems[i].id +  " - " + allitems[i].id.indexOf("OpenLayers.Layer") + ", " + allitems[i].style.zIndex + "\r";
						}
					}
				}
				alert(mrp_str);
*/				
				map.setBaseLayer(baseLayers[l]);
				try {
					enabledBaseLayer = baseLayerName;
				} catch(e) {
					console.log(e);
				}
				if(!leftPanelCollapsed) {
					ExpandCollapseLeftPanel();
				}
				leftPanelCollapsed = !leftPanelCollapsed;
				ExpandCollapseLeftPanel();
				return;
			}
		}
	});
	
	// $(#disaster-feed-list li).click(function(idx)
	// {
		// $(this).toggleClass("active");
	// });
	
	function updateLayers(){
	  
		//$(this).toggleClass("active");
		var group = $(this).attr("group");
		var layer = $(this).attr("layer");
		
		/*
		// only disable current
		if(group == enabledLayer.group && layer == enabledLayer.layer){
			toggleLayer(enabledLayer.group, enabledLayer.layer);
			$('li[layer="' +  enabledLayer.layer +'"][group="' + enabledLayer.group +'"]').removeClass("active");

			//no layer enabled
			enabledLayer.group = 'null';
			enabledLayer.layer = 'null';
			return;
		}
		
		//disable current
		toggleLayer(enabledLayer.group, enabledLayer.layer);
		$('li[layer="' +  enabledLayer.layer +'"][group="' + enabledLayer.group +'"]').removeClass("active");
		*/
		//enable new
		toggleLayer(group, layer, false);
		//$('li[layer="' + layer +'"][group="' + group +'"]').addClass("active");
		
		
		//save enabled
	//	enabledLayer.group = group;
		//enabledLayer.layer = layer;
	}
	//console.log(layers);
	//Toggle Operational Layers
	function toggleLayer(group, layer, toggle){
	 	if(group == 'null' || layer == 'null')
			return;

		//toggle selection
		if(!toggle) {
			$('li[layer="' +  layer +'"][group="' + group +'"]').toggleClass("active");
		}
		for (var i in layers[group][layer]){
//set search url
			$( "#search" ).combogrid( "option", "url", layers[group][layer][i]["search_url"]);
			//set grid url
			grid_base_url = layers[group][layer][i]["grid_url"];
			updateSiteGrid();	
			switch(layers[group][layer][i]["type"]){
			
				case "WMS":
					if(layers[group][layer][i]["layer"] == undefined){
						layers[group][layer][i]["layer"] = buildWMS(layer, layers[group][layer][i]["url"], layers[group][layer][i]["options"]);
						map.addLayer(layers[group][layer][i]["layer"]);
					} else {
						
						map.removeLayer(layers[group][layer][i]["layer"]);
						delete layers[group][layer][i]["layer"];
					}
					break;
				case "KML":
					if(layers[group][layer][i]["layer"] == undefined){
					 
						layers[group][layer][i]["layer"] = buildKML(layer, layers[group][layer][i]["url"]);
						map.addLayer(layers[group][layer][i]["layer"]);
						//add cell tech legend
					} else {
						map.removeLayer(layers[group][layer][i]["layer"]);
						delete layers[group][layer][i]["layer"];
						//remove cell tech legend
						var legend_url = layers[group][layer][i]["legend_url"];
					}
					break;
			}
		}
	}
	
	function ExpandCollapseRightPanel(){
	if (rightPanelCollapsed==true) {
			$("#colright").show();
			$("#colright").animate({ right: "0px" }, 420);
		
			$(".showHideRightPanel").animate({ right: "200px" }, 420);
		}
		else {
			$("#colright").animate({ right: "-200px" }, 420);
		
			$(".showHideRightPanel").animate({ right: "0px" }, 420, function() { $("#colright").hide(); });
		}
		rightPanelCollapsed = !rightPanelCollapsed;
	}
	
	
	function ExpandCollapseLeftPanel() {
		if (leftPanelCollapsed==true) {
			$("#colleft").show();
			$("#colleft").animate({ left: "0px" }, 400);
			$(".olControlPanZoomBar").animate({ left: "140px" }, 400);
			$(".showHideLeftPanel").animate({ left: "200px" }, 400);
		}
		else {
			$("#colleft").animate({ left: "-200px" }, 400);
			$(".olControlPanZoomBar").animate({ left: "-70px" }, 400);
			$(".showHideLeftPanel").animate({ left: "0px" }, 400, function() { $("#colleft").hide(); });
		}
		leftPanelCollapsed = !leftPanelCollapsed;
	}

	function onFeatureSelect(event) {
	  var feature = event.feature;
		var selectedFeature = feature;
		console.log("Description - "+feature.attributes.description);
		if(feature.attributes.name != undefined){
			$('#dialog').append(feature.attributes.description);
			$('#tabs').tabs();
			$('#dialog').dialog({
				title: '<div style="width:465px;"><div style="float:left; text-align:left;">' + feature.attributes.name + '</div><div style="float:right; text-align:right;">' + feature.attributes.technology + '</div></div>',				width: 520,
				modal: true,
				resizable: false,
				close: function(event, ui) { 
					$('#dialog').empty();
				}
			});
		}
	}
	
	function onFeatureUnselect(event) {
		var feature = event.feature;
		if(feature.popup) {
			map.removePopup(feature.popup);
			feature.popup.destroy();
			delete feature.popup;
		}
	}
	
	function buildKML( layer_name, url){
		url = url + '/' + user.cluster_zoom_level;
		var kml = new OpenLayers.Layer.Vector(layer_name, {
		projection: map.displayProjection,
		strategies: [new OpenLayers.Strategy.BBOX({resFactor:1, ratio:1.5})],
		protocol: new OpenLayers.Protocol.HTTP({
			url: url,
			format: new OpenLayers.Format.KML({
				extractStyles: true,
				extractAttributes: true
			})
			})
		});
			
		var select = new OpenLayers.Control.SelectFeature(kml);
		kml.events.on({
			"featureselected": onFeatureSelect,
			"featureunselected": onFeatureUnselect
		});
		
		map.addControl(select);
		select.activate();
		
		return kml;
	
	}
	
	function buildWMS(layer_name, url, options){
		url = url + '/' + user.cluster_zoom_level;	
		var _mapLabel = "";
		switch(enabledMapLabel) {
			case "Site ID":
				_mapLabel = "siteid";
				break;
			case "Switch ID / Cell ID":
				_mapLabel = "switchid";
				break;
			case "PN":
				_mapLabel = "pn";
				break;
			default:
				_mapLabel = "siteid";
			break;
		}
		
		var _viewparams = "";
		if(typeof viewparams == 'undefined') {
			_viewparams = "label:"+_mapLabel;
			if(enabledMapRenderer != "none")
			{
				_viewparams = _viewparams + ";type:"+enabledMapRenderer+";text:"+enabledMapRendererText;
			}
		} else {
			_viewparams = viewparams+";label:"+_mapLabel;
		}
		
		options.viewparams = _viewparams;
//console.log("SD - "+_viewparams);
//console.log('mrp 3321 ' + layer_name + ', ' + url + ', ' + options.viewparams);		
		var wms = new OpenLayers.Layer.WMS( 
			layer_name,
			url, 
			options, 
			{
				isBaseLayer : false,
				opacity: 1,
				singleTile: true,
				visibility : true
		});
		return wms;
	}
	
	if(user.default_layer != '' && user.default_layer != null){
                var default_layer_array = user.default_layer.split(" - ");
                toggleLayer(default_layer_array[0], default_layer_array[1]);
                enabledLayer.group = default_layer_array[0];
                enabledLayer.layer = default_layer_array[1];
                $('li[layer="' +  enabledLayer.group +'"][group="' + enabledLayer.layer +'"]').toggleClass("active");
        }
	else {
		 $( "#search" ).combogrid( "option", "url", layers["T-Mobile"]["GSM"][0]["search_url"]);
	}




	function updateSiteGrid() {
		//if(map.getExtent()!=null) {
		 	var bb = map.getExtent().transform( map.projection, new OpenLayers.Projection("EPSG:4326"));
		 	//var url = jQuery('#list').jqGrid('getGridParam','url');
			if($("#site-attributes").css("display") == 'block') {
				jQuery("#list").jqGrid('setGridParam',{url:grid_base_url + "?minx=" + bb.bottom + "&miny=" + bb.left + "&maxx=" + bb.top + "&maxy=" + bb.right}).trigger("reloadGrid");
			}
		//}
		
		//map.zoomToExtent(bb);
			
		generateBasicLegend(grid_base_url);  // (mrt)
		
	}


};


//
//	legend generation functions...
//
function generateBasicLegend(legend_layer) {
	try {
		//console.log("mrt, generateBasicLegend enter...");
		//console.log("mrt, generateBasicLegend legend_layer -->"+legend_layer+"<--");
		
		var legend_vp = "no data to display, yet";
		//console.log("mrt, generateBasicLegend legend_vp -->"+legend_vp+"<--");
		
		var legend_shortTitle = legend_layer.split(':');
		var legend_title = "Basic for "+legend_shortTitle[2];
		//console.log("mrt, generateBasicLegend legend_title -->"+legend_title+"<--");

		var legend_layer_selected = false;
		$('#layer-list > li > a').each(function(index) {
			//console.log("mrt, generateBasicLegend UMTS backgroundColor -->"+$(this).css("backgroundColor")+"<--");
			var legend_layer_color = $(this).css("backgroundColor");
			//console.log("mrt, generateBasicLegend UMTS legend_layer_color -->"+legend_layer_color+"<--");

			if(legend_layer_color.match(/rgb\(0/)) {
				legend_layer_selected = true; 
				legend_title = $(this).text();
				//console.log("mrt, generateBasicLegend new legend_title -->"+legend_title+"<--");
			}
		});
		
		// remove any legend images...		
		
		if(legend_layer_selected) {
			//console.log("mrt, generateBasicLegend legend_layer_selected -->true<--");
			
			$('img.legend_image').remove();
			var legend_range_url = 'http://138.85.245.136/geo/legend?TITLE='+encodeURIComponent(legend_title)+"&VIEWPARAMS="+encodeURIComponent(legend_vp);
			//console.log("mrt, generateBasicLegend legend_range_url -->"+legend_range_url+"<--");
			
			// insert new legend image..
			//$("#colright").append("<img class='legend_image' src='" + legend_range_url + "'/>");
		} else {
			//console.log("mrt, generateBasicLegend legend_layer_selected -->false<--");
		}
		
	} catch (legend_e) {
		//console.log("mrt, generateBasicLegend error: "+legend_e.message);
	}

	//console.log("mrt, generateBasicLegend exit...");
	
}

function generateKpiLegend() {
	try {
		//console.log("mrt, generateKpi_Legend enter...");
		
		var legend_title = "KPI";
		//console.log("mrt, generateKpi_Legend legend_vp -->"+legend_vp+"<--");
		
		var legend_vp = 'select#kpia1.span2' + ',' + $('select#kpia1.span2').val() + ';' + 
						'select#op1.span1'   + ',' + $('select#op1.span1').val()   + ';' + 
						'input#val1.span1'   + ',' + $('input#val1.span1').val()   + ';' + 
						
						'select#kpia2.span2' + ',' + $('select#kpia2.span2').val() + ';' + 
						'select#op2.span1'   + ',' + $('select#op2.span1').val()   + ';' + 
						'input#val2.span1'   + ',' + $('input#val2.span1').val()   + ';' + 
						
						'select#kpia3.span2' + ',' + $('select#kpia3.span2').val() + ';' + 
						'select#op3.span1'   + ',' + $('select#op3.span1').val()   + ';' + 
						'input#val3.span1'   + ',' + $('input#val3.span1').val(); 
		
		// remove any legend images...	
		$('img.legend_image').remove();
		
		// insert new legend image...   JUST USE A GENERAL LEDGER, UNTIL COLORS ARE TRANSLATED...
		var legend_range_url = 'http://138.85.245.136/geo/legend?TITLE='+encodeURIComponent(legend_title)+"&VIEWPARAMS="+encodeURIComponent(legend_vp);
		//console.log("mrt, generateKpi_Legend legend_range_url -->"+legend_range_url+"<--");
		$("#colright").append("<img class='legend_image' src='" + legend_range_url + "'/>");
		
	} catch (legend_e) {
		//console.log("mrt, generateKpi_Legend error: "+legend_e.message);
	}

	//console.log("mrt, generateKpi_Legend exit...");
	
}

function generateKpidLegend() {
	try {
		//console.log("mrt, generateKpidLegend enter...");
		
		//$('div#colright').height(328);
		
		//$('div.toggleLegendItemInactive').height(328);
		
		var legend_vp = 'select#kpi1' + ',' + $('select#kpi1').val() + ';' + 
						'input#t11'   + ',' + $('input#t11').val()   + ';' + 
						'div#cp11'       + ',' + $("#cp11 > span > i" ).css("background-color") + ';' + 
						'input#t12'   + ',' + $('input#t12').val()   + ';' + 
						'div#cp12'       + ',' + $("#cp12 > span > i" ).css("background-color") + ';' + 
						'input#t13'   + ',' + $('input#t13').val()   + ';' + 
						'div#cp13'       + ',' + $("#cp13 > span > i" ).css("background-color") + ';' + 
						'input#t14'   + ',' + $('input#t14').val()   + ';' + 
						'div#cp14'       + ',' + $("#cp14 > span > i" ).css("background-color") + ';' + 
						'input#t15'   + ',' + $('input#t15').val()   + ';' + 
		'div#cp15'    + ',' + $('#cp15 > span > i').css("background-color")  + ';' + 
						'input#t16'   + ',' + $('input#t16').val()   + ';' + 
						'select#kpi2' + ',' + $('select#kpi2').val() + ';' + 
						'input#t21'   + ',' + $('input#t21').val()   + ';' + 
						'div#cp21'       + ',' + $("#cp21 > span > i" ).css("background-color") + ';' + 
						'input#t22'   + ',' + $('input#t22').val()   + ';' + 
						'div#cp22'       + ',' + $("#cp22 > span > i" ).css("background-color") + ';' + 
						'input#t23'   + ',' + $('input#t23').val()   + ';' + 
						'div#cp23'       + ',' + $("#cp23 > span > i" ).css("background-color") + ';' + 
						'input#t24'   + ',' + $('input#t24').val()   + ';' + 
						'div#cp24'       + ',' + $("#cp24 > span > i" ).css("background-color") + ';' + 
						'input#t25'   + ',' + $('input#t25').val()   + ';' + 
						'div#cp25'       + ',' + $("#cp25 > span > i" ).css("background-color") + ';' + 
						'input#t26'   + ',' + $('input#t26').val()   + ';' + 
						'select#kpi3' + ',' + $('select#kpi3').val() + ';' + 
						'input#t31'   + ',' + $('input#t31').val()   + ';' + 
						'div#cp31'       + ',' + $("#cp31 > span > i" ).css("background-color") + ';' + 
						'input#t32'   + ',' + $('input#t32').val()   + ';' + 
						'div#cp32'       + ',' + $("#cp32 > span > i" ).css("background-color") + ';' + 
						'input#t33'   + ',' + $('input#t33').val()   + ';' + 
						'div#cp33'       + ',' + $("#cp33 > span > i" ).css("background-color") + ';' + 
						'input#t34'   + ',' + $('input#t34').val()   + ';' + 
						'div#cp34'       + ',' + $("#cp34 > span > i" ).css("background-color") + ';' + 
						'input#t35'   + ',' + $('input#t35').val()   + ';' + 
						'div#cp35'       + ',' + $("#cp35 > span > i" ).css("background-color") + ';' + 
						'input#t36'   + ',' + $('input#t36').val(); 
		//console.log("mrt, generateKpidLegend legend_vp -->"+legend_vp+"<--");
		
		var legend_title = "KPI D";
		
		var legend_range_url = 'http://138.85.245.136/geo/legend?TITLE='+encodeURIComponent(legend_title)+"&VIEWPARAMS="+encodeURIComponent(legend_vp);
		//console.log("mrt, generateKpidLegend legend_range_url -->"+legend_range_url+"<--");
		
		// remove any legend images...		
		$('img.legend_image').remove();
		
		// insert new legend image..
		$("#colright").append("<img class='legend_image' src='" + legend_range_url + "'/>");
		
	} catch (legend_e) {
		//console.log("mrt, generateKpidLegend error: "+legend_e.message);
	}

	//console.log("mrt, generateKpidLegend exit...");
	
}

function generateKpiCellLegend() {
	try {
		//console.log("mrt, generateKpiCellLegend enter...");
		
		//$('div#colright').height(328);
		
		//$('div.toggleLegendItemInactive').height(328);

		var legend_vp = 'select#kpic1.span2'    + ',' + $('select#kpic1.span2').val()    + ';' + 
						'select#kpic-op1.span1' + ',' + $('select#kpic-op1.span1').val() + ';' + 
						'input#kpic-val1.span1' + ',' + $('input#kpic-val1.span1').val(); 
		//console.log("mrt, generateKpiCellLegend legend_vp -->"+legend_vp+"<--");
		
		var legend_title = "KPI Cell";
		
		var legend_range_url = 'http://138.85.245.136/geo/legend?TITLE='+encodeURIComponent(legend_title)+"&VIEWPARAMS="+encodeURIComponent(legend_vp);
		//console.log("mrt, generateKpiCellLegend legend_range_url -->"+legend_range_url+"<--");
		
		// remove any legend images...
		$('img.legend_image').remove();
		
		// insert new legend image..
		$("#colright").append("<img class='legend_image' src='" + legend_range_url + "'/>");
		
	} catch (legend_e) {
		//console.log("mrt, generateKpiCellLegend error: "+legend_e.message);
	}

	//console.log("mrt, generateKpiCellLegend exit...");
	
}

function generateKpiCellDLegend() {
	try {
		//console.log("mrt, generateKpiCell_D_Legend enter...");
		
		//$('div#colright').height(328);
		
		//$('div.toggleLegendItemInactive').height(328);

		var legend_vp = 'select#kpicd1' + ',' + $('select#kpicd1').val() + ';' + 
						'input#tcd11'   + ',' + $('input#tcd11').val()     + ';' + 
						'#cpcd11'       + ',' + $("#cpcd11 > span > i").css("background-color") + ';' + 
						'input#tcd12'   + ',' + $('input#tcd12').val()     + ';' + 
						'#cpcd12'       + ',' + $("#cpcd12 > span > i").css("background-color") + ';' + 
						'input#tcd13'   + ',' + $('input#tcd14').val()     + ';' + 
						'#cpcd13'       + ',' + $("#cpcd13 > span > i").css("background-color") + ';' + 
						'input#tcd14'   + ',' + $('input#tcd16').val()     + ';' + 
						'#cpcd14'       + ',' + $("#cpcd14 > span > i").css("background-color") + ';' + 
						'input#tcd15'   + ',' + $('input#tcd18').val()     + ';' + 
						'#cpcd15'       + ',' + $("#cpcd15 > span > i").css("background-color") + ';' + 
						'input#tcd16'   + ',' + $('input#tcd110').val(); 
		//console.log("mrt, generateKpiCell_D_Legend vp -->"+legend_vp+"<--");
		
		var legend_title = "KPI Cell D";
		
		var legend_range_url = 'http://138.85.245.136/geo/legend?TITLE='+encodeURIComponent(legend_title)+"&VIEWPARAMS="+encodeURIComponent(legend_vp);
		//console.log("mrt, generateKpiCell_D_Legend legend_range_url -->"+legend_range_url+"<--");
		
		// remove any legend images...	
		$('img.legend_image').remove();
		
		// insert new legend image..
		$("#colright").append("<img class='legend_image' src='" + legend_range_url + "'/>");
		
	} catch (legend_e) {
		//console.log("mrt, generateKpiCell_D_Legend error: "+legend_e.message);
	}

	//console.log("mrt, generateKpiCell_D_Legend exit...");
	
}

// drive test legend...
function generateLTELegend() {
	try {
		//console.log("mrt, generateLTELegend enter...");
		
		//$('div#colright').height(328);
		
		//$('div.toggleLegendItemInactive').height(328);

		var legend_vp = 'select#kpicd1' + ',' + $('select#kpicd1').val() + ';' + 
						'input#tcd11'   + ',' + $('input#t11').val()     + ';' + 
						'#cpcd11'       + ',' + $('#cp11').val()         + ';' + 
						'input#tcd12'   + ',' + $('input#t12').val()     + ';' + 
						'#cpcd12'       + ',' + $('#cp12').val()         + ';' + 
						'input#tcd13'   + ',' + $('input#t13').val()     + ';' + 
						'#cpcd13'       + ',' + $('#cp13').val()         + ';' + 
						'input#tcd14'   + ',' + $('input#t14').val()     + ';' + 
						'#cpcd14'       + ',' + $('#cp14').val()         + ';' + 
						'input#t15'    + ',' + $('input#t15').val()      + ';' + 
						'#cp15'        + ',' + $('#cp15').val()          + ';' + 
						'input#t16'    + ',' + $('input#t16').val(); 
		//console.log("mrt, generateLTELegend legend_vp -->"+legend_vp+"<--");
		
		var legend_title = "LTE";
		
		var legend_range_url = 'http://138.85.245.136/geo/legend?TITLE='+encodeURIComponent(legend_title)+"&VIEWPARAMS="+encodeURIComponent(legend_vp);
		//console.log("mrt, generateLTELegend legend_range_url -->"+legend_range_url+"<--");
		
		// remove any legend images...	
		$('img.legend_image').remove();
		
		// insert new legend image..
		//$("#colright").append("<img class='legend_image' src='" + legend_range_url + "'/>");
		
	} catch (legend_e) {
		//console.log("mrt, generateLTELegend error: "+legend_e.message);
	}

	//console.log("mrt, generateLTELegend exit...");
	
}

function generateDriveTestLegend() {
	try {
		//console.log("mrt, generateDriveTestLegend enter...");
		
		//$('div#colright').height(328);
		
		//$('div.toggleLegendItemInactive').height(328);

		var vp = 'input#tcpv0'     + ',' + $('input#tcpv0').val()     + ';' + 
				 'input#td1.span1' + ',' + $('input#td1.span1').val() + ';' + 
				 'input#tcpv1'     + ',' + $('input#tcpv1').val()     + ';' + 
				 'input#td2.span1' + ',' + $('input#td2.span1').val() + ';' + 
				 'input#tcpv2'     + ',' + $('input#tcpv2').val()     + ';' + 
				 'input#td3.span1' + ',' + $('input#td3.span1').val() + ';' + 
				 'input#tcpv3'     + ',' + $('input#tcpv3').val()     + ';' + 
				 'input#td4.span1' + ',' + $('input#td4.span1').val() + ';' + 
				 'input#tcpv4'     + ',' + $('input#tcpv4').val()     + ';' + 
				 'input#td5.span1' + ',' + $('input#td5.span1').val() + ';' + 
				 'input#tcpv5'     + ',' + $('input#tcpv5').val();
		//console.log("mrt, generateDriveTestLegend vp -->"+vp+"<--");
		
		var title = "Drive Test";
		
		var range_url = 'http://138.85.245.136/geo/legend?TITLE='+encodeURIComponent(title)+"&VIEWPARAMS="+encodeURIComponent(vp);
		//console.log("mrt, generateDriveTestLegend range_url -->"+range_url+"<--");
		
		// remove any legend images...
		$('img.legend_image').remove();
		
		// insert new legend image..
		$("#colright").append("<img class='legend_image' src='" + range_url + "'/>");
		
	} catch (legend_e) {
		//console.log("mrt, generateDriveTestLegend error: "+legend_e.message);
	}

	//console.log("mrt, generateDriveTestLegend exit...");
	
}
















