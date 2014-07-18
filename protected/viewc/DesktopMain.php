<?php
if($_SESSION['user']['id'] == 83) {
//    foreach($_SESSION as $k => $v) {
//        foreach($v as $ke => $va) {
//            echo $k . ', ' . $ke . ', ' . $va . '<br />';
//        }
//    }
//    echo '<br /><br /><br />';
//    foreach($this->data['user'] as $k => $v) {
//        echo $k . ', ' . $v . '<br />';
//    }
}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>EDOS-DP</title>
		<script src='global/js/OpenLayers.js'></script>
		<script src='global/js/jquery-1.7.2.min.js'></script>
		<script src='global/js/bootstrap.min.js'></script>
		<link rel="stylesheet" href="global/css/jquery/themes/base/jquery.ui.all.css">
		<link rel="stylesheet" href="global/css/bootstrap.min.css">
		<script src='global/js/jquery-ui-1.8.20.custom.min.js'></script>
		<script src='global/js/jquery.ui.combogrid-1.6.2.js'></script>
		
		<script src='global/js/jquery.jqGrid.min.js'></script>
		<script src="global/js/i18n/grid.locale-en.js" type="text/javascript"></script>
		<script src="http://maps.google.com/maps/api/js?sensor=false"></script>
        <script charset="UTF-8" type="text/javascript" src="https://ecn.dev.virtualearth.net/mapcontrol/mapcontrol.ashx?v=7.0&s=1"></script>
		<script type="text/javascript" src="https://www.google.com/jsapi"></script>
		<script src='global/js/desktop-app.js'></script>
		<script src="global/js/bootstrap-datepicker.js"></script>
		<script src="global/js/bootstrap-colorpicker.js"></script>
		<link href="global/css/datepicker.css" rel="stylesheet">
		<link href="global/css/colorpicker.css" rel="stylesheet">
		<link rel="stylesheet" href="global/css/desktop-app.css">
		<link rel="stylesheet" href="global/css/ui.jqgrid.css">
		<script type="text/javascript">
			var layers 				= <?php echo json_encode($this->data['layers'], JSON_NUMERIC_CHECK); ?>;
			var renderers           = <?php echo json_encode($this->data['renderers'], JSON_NUMERIC_CHECK); ?>;
			var disasters           = <?php echo json_encode($this->data['disasters'], JSON_NUMERIC_CHECK); ?>;
			var disaster_categories           = <?php echo json_encode($this->data['disaster_categories'], JSON_NUMERIC_CHECK); ?>;
			var user = {};
			user.name 				= <?php echo isset($this->data['user']['username']) ? "'".$this->data['user']['username']."'" : 'unknown'; ?>;
			user.home_zoom			= <?php echo isset($this->data['user']['home_zoom']) ? $this->data['user']['home_zoom'] : 4; ?>;
			user.home_latitude		= <?php echo isset($this->data['user']['home_latitude']) ?$this->data['user']['home_latitude'] : 40.00 ; ?>;
			user.home_longitude 	= <?php echo isset($this->data['user']['home_longitude']) ? $this->data['user']['home_longitude'] : -100.00; ?>;
			user.cluster_zoom_level = <?php echo isset($this->data['user']['cluster_zoom_level']) ? $this->data['user']['cluster_zoom_level'] : 15; ?>;
			user.default_basemap	= <?php echo isset($this->data['user']['default_basemap']) ? "'".$this->data['user']['default_basemap']."'" : 'null'; ?>;
			user.default_layer		= <?php echo isset($this->data['user']['default_layer']) ?  "'".$this->data['user']['default_layer']."'": 'null'; ?>;
            user.client             = <?php echo isset($this->data['user']['client']) ?  "'".$this->data['user']['client']."'": 'null'; ?>;
            var userServer          = "<?php echo $_SERVER['SERVER_NAME']; ?>";

            var map;
            var polygonId;
            var polygonName;
            var polygonColor;
            var polygonBounds;
            var polygonGeometry;
            var polyStyle;
            var polyVector;

			var enabledLayer = {};
			enabledLayer.group = 'null';
			enabledLayer.layer = 'null';
			var enabledBaseLayer = 'null';
			var enabledTool = 'null';

			var grid_base_url = user.client + ':UMTS';

            function closePolySaveOrig() {
                polyStyle = null;
                polyStyle = new OpenLayers.StyleMap({
                        "default": new OpenLayers.Style({
                        //strokeDashstyle: �dash�,
                        strokeColor: polygonColor,
                        strokeWidth: 1,
                        strokeOpacity: 0.8,
                        fillColor: polygonColor,
                        fillOpacity: 0.3
                    })
                });
                polyVector.styleMap = polyStyle;
                polyVector.redraw();
                $('#polygonInputModal').modal('hide');
            }
            function closePolySave() {
                var polyStyleNew = new OpenLayers.StyleMap({
                    "default": new OpenLayers.Style({
                        //strokeDashstyle: �dash�,
                        strokeColor: polygonColor,
                        strokeWidth: 1,
                        strokeOpacity: 0.8,
                        fillColor: polygonColor,
                        fillOpacity: 0.3
                    })
                });
                var polyNew = OpenLayers.Geometry.fromWKT(polygonGeometry.toString());
                var layerNew = new OpenLayers.Layer.Vector("Saved Polygon Vector", { styleMap: polyStyleNew } );
                var featureNew = new OpenLayers.Feature.Vector(polyNew);
                layerNew.addFeatures(featureNew);
                polygonBounds = layerNew.getDataExtent();
                map.addLayer(layerNew);
                layerNew.refresh();
                try {
                    map.removeLayer(polyVector);
                } catch(e) {
                    //console.log(e);
                }
                $('#polygonInputModal').modal('hide');
            }
            function displayPolyArray(rawInfoData) {
                if(rawInfoData.length < 12) {
                    alert('Your file is not a convertible map file.');
                    $('#uploadFileModal').modal('hide');
                    return;
                }
                var left = 20037508.34, right = -20037508.34, top = -20037508.34, bottom = 20037508.34;
                polyObjects = [];
                var mapInfoData = rawInfoData.split('|');
                mapInfoData.forEach(function(poly) {
                    if(poly.length > 1) {
                        polyObjects = [];
                        var coords = poly.split(';');
                        coords.forEach(function(coord) {
                            if(coord.length > 1) {
                                var loc = coord.split(',');
                                if(loc.length > 1) {
                                    point = new OpenLayers.Geometry.Point(loc[0], loc[1]);
                                    point.transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject());
                                    polyObjects.push(point);
                                    if(point.x < left) {
                                        left = point.x;
                                    }
                                    if(point.x > right) {
                                        right = point.x;
                                    }
                                    if(point.y > top) {
                                         top = point.y;
                                    }
                                    if(point.y < bottom) {
                                         bottom = point.y;
                                    }
                                }
                            }
                        });
                        polyObjects.push(polyObjects[0]);
                        var linear_ring = new OpenLayers.Geometry.LinearRing(polyObjects);
                        var layerNew = new OpenLayers.Layer.Vector("Saved Polygon Vectors");
                        var featureNew = new OpenLayers.Feature.Vector(linear_ring);
                        layerNew.addFeatures(featureNew);
                        map.addLayer(layerNew);
                        layerNew.refresh();
                    }
                });
                var border = 5000;
                var bounds = new OpenLayers.Bounds((parseFloat(left) - parseFloat(border)), (parseFloat(bottom) - parseFloat(border)), (parseFloat(right) + parseFloat(border)), (parseFloat(top) + parseFloat(border)));
                map.zoomToExtent(bounds);
                $('#uploadFileModal').modal('hide');
            }
            function getPolyArray(polygons) {
                $('#polygonOutputModal').modal('hide');
                var arrayStr = '';
                var bounding = new OpenLayers.Bounds();
                for(var i = 0; i < polygons.length; i++) {
                    var polygon = polygons[i].split(";");
                    var polyStyleNew = new OpenLayers.StyleMap({
                        "default": new OpenLayers.Style({
                            //strokeDashstyle: �dash�,
                            strokeColor: polygon[2],
                            strokeWidth: 1,
                            strokeOpacity: 0.8,
                            fillColor: polygon[2],
                            fillOpacity: 0.3
                        })
                    });
                    var polyNew = OpenLayers.Geometry.fromWKT(polygon[4]);
                    var layerNew = new OpenLayers.Layer.Vector("Saved Polygon Vector", { styleMap: polyStyleNew } );
                    var featureNew = new OpenLayers.Feature.Vector(polyNew);
                    layerNew.addFeatures(featureNew);
                    map.addLayer(layerNew);
                    layerNew.refresh();
                    var bounds = new OpenLayers.Bounds.fromString(polygon[3]);
                    bounding.extend(bounds);
                }
                map.zoomToExtent(bounding);
            }
            function getPoly(key) {
                $('#polygonOutputModal').modal('hide');
                var polyStyleNew = new OpenLayers.StyleMap( {
                    "default": new OpenLayers.Style( {
                        strokeColor: polygonColor,
                        strokeWidth: 1,
                        strokeOpacity: 0.8,
                        fillColor: polygonColor,
                        fillOpacity: 0.3
                    })
                });
                var polyNew = OpenLayers.Geometry.fromWKT(polygonGeometry);
                var layerNew = new OpenLayers.Layer.Vector("Saved Polygon Vector", { styleMap: polyStyleNew } );
                var featureNew = new OpenLayers.Feature.Vector(polyNew);
                layerNew.addFeatures(featureNew);
                map.addLayer(layerNew);
                layerNew.refresh();
            }
		</script>
	</head>
	<body onload="init()">
        <div id="map"><div id="mapDiv"></div></div>

		<!-- Feature Modal -->
		<div id="dialog" title="Basic dialog"></div>

		<!-- Profile Modal -->
		<div class="modal" id="profile-modal" style="display: none">
			<div class="modal-header">
				<button class="close" data-dismiss="modal">x</button>
				<h3>User Profile</h3>
			</div>
			<div class="modal-body">
			<form class="form-horizontal">
				<fieldset>
					<div class="control-group">
						<label class="control-label" for="client">Client</label>
						<div class="controls">
							<select id="client" class="span2">
								<?php
									foreach($this->data['clients'] as $client){
										if($this->data['user']['client'] == $client->name)
											echo '<option selected="selected">' . $client->name . '</option>';
										else
											echo '<option>' . $client->name . '</option>';
									}
								?>
							</select>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="default-basemap">Default Base Map</label>
						<div class="controls">
							<select id="default-basemap" class="span2">
								<option>Google Streets</option>
								<option>Google Terrain</option>
								<option>Google Satellite</option>
								<option>Bing Road</option>
                                <option>Bing Aerial</option>
                                <option>Bing Birds Eye</option>
								<option>Open Street Map</option>
							</select>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="default-layer">Default Layer</label>
						<div class="controls">
							<select id="default-layer" class="span4">
								<option></option>
							<?php
							foreach($this->data['layers'] as $gkey=>$group){
								foreach($group as $lkey=>$layer){
									echo "<option>$gkey - $lkey</option>";
								}
							}
							?>
							</select>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="cluster-zoom">Clustering Zoom Level</label>
						<div class="controls">
							<select id="cluster-zoom" class="span2">
								<option>15</option>
								<option>14</option>
								<option>13</option>
								<option>12</option>
								<option>11</option>
								<option>10</option>
							</select>
							<p class="help-block">Disable site clusters at zoom level. Lower is zoomed out more. </p>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">Save View As Home</label>
						<div class="controls">
							<a id="get-viewport" class="btn btn-primary" href="#">Get Viewport</a>
							<p id="get-viewport-msg" class="help-inline"></p>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">Change Password</label>
						<div class="controls">
							<label class="checkbox">
								<input id="change-password" type="checkbox">
								Change on next Login
							</label>
						</div>
					</div>
				</fieldset>
				</form>
			</div>
			<div class="modal-footer">
				<a href="#" id="profile-modal-close" class="btn">Close</a>
				<a href="#" id="profile-modal-save" class="btn btn-primary">Save changes</a>
			</div>
		</div>

		<!-- Contact Modal -->
		<div class="modal" id="contactModal" style="display: none">
			<div class="modal-header">
				<button class="close" data-dismiss="modal">x</button>
				<h3>Contact</h3>
			</div>
			<div class="modal-body">
				<b>Question, Comments, Suggestions?</b>
				<ul>
					<li>alex.hurd@ericsson.com</li>
					<li>sandip.sandhu@ericsson.com</li>
				</ul>
			</div>
		</div>

        <!-- StreetView Modal -->
        <div class="modal" id="streetviewModal" style="display: none">
            <div class="modal-header">
                <button class="close" data-dismiss="modal">x</button>
                <h3>Street View</h3>
            </div>
            <div class="modal-body" id="streetview" style="position:relative; height:500px;">

            </div>
        </div>

        <!-- Polygon Input Modal -->
        <div class="modal" id="polygonInputModal" style="display: none">
            <div class="modal-header">
                <button class="close" data-dismiss="modal">x</button>
                <h3>Save Polygon</h3>
            </div>
            <div class="modal-body" id="polyinputdiv" style="position:relative; height:500px;">
            <iframe id="polyiframedetail" frameBorder="0" width="530" height="390"></iframe>
            </div>
        </div>

        <!-- Polygon Output Modal -->
        <div class="modal" id="polygonOutputModal" style="display: none">
            <div class="modal-header">
                <button class="close" data-dismiss="modal">x</button>
                <h3>Retrieve Polygon</h3>
            </div>
            <div class="modal-body" id="polyoninputdiv" style="position:relative; height:500px;">
            <iframe id="polyiframeoutput" frameBorder="0" width="530" height="390"></iframe>
            </div>
        </div>

        <!-- Terrain Profile Modal -->
        <div class="modal" id="terrain-profile-Modal" style="display: none">
            <div class="modal-header">
                <button class="close" data-dismiss="modal">x</button>
                <h3>Terrain Profile</h3>
            </div>
            <div class="modal-body" id="chart_terrain_profile">
            </div>
        </div>

        <!-- Upload File Modal -->
        <div class="modal" id="uploadFileModal" style="display: none">
            <div class="modal-header">
                <button class="close" data-dismiss="modal">x</button>
                <h3>Upload File</h3>
            </div>
            <iframe id="uploadinput" frameBorder="0" width="530" height="390"></iframe>
            </div>
        </div>
		
		<!-- Disaster Feed Tracker Modal  (Not in use) -->
        <div class="modal" id="drFTModal" style="display: none">
            <div class="modal-header">
                <button class="close" data-dismiss="modal">x</button>
                <h3>Disaster Feed Tracker</h3>
            </div>
			<div class="modal-body" id="drftinputdiv">
				<div class="drfeed_div_class">
					<button class="dft_button">Add</button><button class="dft_button">Edit</button><button class="dft_button">Remove</button>
				</div>
				<div class="drfeed_div_class">
					<table id="drfeed_list"></table>
					<div id="drfeed_pager"></div>
				</div>
            </div>
        </div>

		<!-- Field Tech Input Modal-->
		<div class="modal" id="ftinputModal" style="display: none">
			<div class="modal-header">
                <button class="close" data-dismiss="modal">x</button>
                <h3>Field Tech Input</h3>
            </div>
			<div class="modal-body" id="ftinputdiv">
			<div>
			<label>Title :</label><input type="text" id="ftInputTitle"></input>
			<label>Linked Cascad :</label><input id="ftInputCascad"></input>
			</div>
			<label>Notes :</label>
			<textarea style="width:500px;height:100px" id="ftInputNotes">
			</textarea>
			<button id="submit_ftinput">Submit</button> <button id="cancel_ftinput" style="margin-left:10px">Cancel</button>
			</div>
		</div>
		<div id="dialog-message" title="Done" style="display: none">
		  <p>Successfully saved input.</p>
		</div>
		<div id="dialog-message-deleted" title="Done" style="display: none">
		  <p>Successfully deleted input.</p>
		</div>
		<!-- Field Tech Edit Input Modal-->
		<div class="modal" id="ftcinputModal" style="display: none">
			<div class="modal-header">
                <button class="close" data-dismiss="modal">x</button>
                <h3>Field Tech Input</h3>
            </div>
			<div class="modal-body" id="ftcinputdiv">
			<div>
			<label>Title :</label><input type="text" id="ftcInputTitle"></input>
			<div id="radio_ftinput">
				<input type="radio" id="inactive_ftinput" name="radio" /><label for="inactive_ftinput">In-Active</label>
				<input type="radio" id="active_ftinput" name="radio" checked="checked" /><label for="active_ftinput">Active</label>
			</div>
			<label>Linked Cascad :</label><input id="ftcInputCascad"></input>
			</div>
			<label>Notes :</label>
			<textarea style="width:500px;height:100px" id="ftcInputNotes">
			</textarea>
			<button id="submit_edit_ftinput">Submit</button> <button id="delete_ftinput" style="margin-left:10px">Delete</button>
			</div>
		</div>
		
		
		<!-- Navigation Bar -->
		<div class="navbar" id="navigationbar" name="navigationbar">
			<div class="navbar-inner">
				<div class="container">
					<div class="nav-collapse">
						<ul class="nav">
							<li id="menu-home">
								<a href="#">Home</a>
							</li>
							<li class="divider-vertical"></li>
							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown">Profile<b class="caret"></b></a>
								<ul class="dropdown-menu">
									<li id="profile-settings">
										<a href="#">Settings</a>
									</li>
									<li id="profile-logout">
										<a href="#">Logout</a>
									</li>
								</ul>
							</li>
							<li class="divider-vertical"></li>
							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown">Links<b class="caret"></b></a>
								<ul class="dropdown-menu">
								<?php
									foreach($this->data['links'] as $link){
										echo '<li>';
										echo '<a href="' . $link->url .'?username='.$this->data['user']['username']. '" target="_blank">' . $link->name . '</a>';
										echo '</li>';
									}
								?>
								</ul>
							</li>
							<li class="divider-vertical"></li>
							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown">Labels<b class="caret"></b></a>
								<ul id="mapLabelsDropDown" name="mapLabelsDropDown" type="map-Labels" class="dropdown-menu">
									<li>
										<a href="#">Site ID</a>
									</li>
									<li>
										<a href="#">Switch ID / Cell ID</a>
									</li>
									<li>
										<a href="#">PN</a>
									</li>
								</ul>
							</li>
							<li class="divider-vertical"></li>
							<li class="dropdown">
								<a href="#" id="mapRendererCtrl" class="dropdown-toggle" data-toggle="dropdown">Render<b class="caret"></b></a>
								<ul id="mapRendererDropDown" name="mapRendererDropDown" type="map-Renderer" class="dropdown-menu">
								   <?php
									foreach($this->data['renderers'] as $renderer){
										echo '<li>';
										
										if(strtoupper($renderer->type) == 'TEXT')
										{
											echo '<a href="#" class="renderSearchText_label" name="'.$renderer->key.'">'.$renderer->name.'</a>'.'<input class="renderSearchText" name="'.$renderer->key.'" type="text">';	
										}
										else
										{
											echo '<a href="#" name="'.$renderer->key.'">'.$renderer->name.'</a>';
										}
										echo '</li>';
									}
									?>
									
								</ul>
							</li>
							<li class="divider-vertical"></li>
							<input type="text" class="nav" id="search" placeholder="Search">
							<li class="divider-vertical"></li>
							<li>
								<a id="latitude_mouse"></a>
							</li>
							<li>
								<a id="longitude_mouse"></a>
							</li>

						</ul>
						<ul class="nav pull-right">

							<li>
                                                                <?php
                                                                        foreach($this->data['links'] as $link){
										if($link->name=='Dashboards')
                                                                                	echo '<a href="' . $link->url .'?username='.$this->data['user']['username'] . '" target="_blank">' . $link->name . '</a>';

                                                                        }
                                                                ?>


                                                        </li>
							<li class="divider-vertical"></li>
							<li>
                                                                <?php
                                                                        foreach($this->data['links'] as $link){
                                                                                if($link->name=='Pre Post')
                                                                                        echo '<a href="' . $link->url .'?username='.$this->data['user']['username'] . '" target="_blank">' . $link->name . '</a>';

                                                                        }
                                                                ?>

                                                        </li>
							<li>
                                                                <?php
                                                                        foreach($this->data['links'] as $link){
                                                                                if($link->name=='3 Way PP')
                                                                                        echo '<a href="' . $link->url .'?username='.$this->data['user']['username'] . '" target="_blank">' . $link->name . '</a>';

                                                                        }
                                                                ?>

                                                        </li>
							<li>
                                                                <?php
                                                                        foreach($this->data['links'] as $link){
                                                                                if($link->name=='Reports')
                                                                                        echo '<a href="' . $link->url .'?username='.$this->data['user']['username'] . '" target="_blank">' . $link->name . '</a>';

                                                                        }
                                                                ?>

                                                        </li>





							<li class="divider-vertical"></li>
							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown">Tools<b class="caret"></b></a>
								<ul class="dropdown-menu" type="tools">
									<li>
										<a href="#">Ruler</a>
									</li>
                                    <li>
                                        <a href="#">Polygon</a>
                                    </li>
                                    <li>
                                        <a href="#">Saved Polygons</a>
                                    </li>
									<li>
										<a href="#">Elevation</a>
									</li>
									<li>
										<a href="#">Terrain Profile</a>
									</li>
                                    <li>
                                        <a href="#">Street View</a>
                                    </li>
                                   <li>
                                        <a href="#">Import MapInfo File(.mif)</a>
                                    </li>
								</ul>
							</li>
							<li class="divider-vertical"></li>
							<li class="dropdown" id="mapDropDownLI" name="mapDropDownLI">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown">Maps<b class="caret"></b></a>
								<ul class="dropdown-menu" id="mapDropDownUL" name="mapDropDownUL" type="base-layers">
									<li>
										<a href="#">Google Streets</a></li>
									<li>
										<a href="#">Google Terrain</a></li>
									<li>
										<a href="#">Google Satellite</a>
									</li>
									<li>
										<a href="#">Bing Road</a>
									</li>
                                    <li>
                                        <a href="#">Bing Aerial</a>
                                    </li>
                                    <li>
                                        <a href="#">Bing Birds Eye</a>
                                    </li>
									<li>
										<a href="#">Open Street Map</a>
									</li>
								</ul>
							</li>
							<li class="divider-vertical"></li>
							<li id="menu-contact">
								<a href="#">Contact</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<!-- Left Column -->
		<div id="colleft">
			<ul id="layer-list" class="nav nav-list">
			<?php
				foreach($this->data['layers'] as $gkey=>$group){
                    echo "<li class=\"nav-header\">&nbsp;</li>";
                    //echo "<li class=\"nav-header\">$gkey</li>";
					foreach($group as $lkey=>$layer){
					    $sg = $layer[0]["search_group"];
						$pg_key = $layer[0]["pg_key"];
					    // foreach($layer[0] as $vkey=>$vvalue)
						// {
						 // if($vkey == "search_group")
							// {
							// }
						// }
						echo "<li class=\"layerText\" group=\"$gkey\" search_group=\"$sg\" pg_key=\"$pg_key\" layer=\"$lkey\"><a href=\"#\">$lkey</a></li>";
						//echo $layer[0]["search_group"];
					}
				}
			?>
			</ul>
		</div>

		<div id="colright">
		
		</div>
        <div id="coltools"></div>
        <iframe id="importfile" style="display:none;"></iframe>
        <iframe id="coltoolstreet"></iframe>
        <iframe id="polyiframe" style="display:none;"></iframe>
		<div id="bottom" >
			<div class="subnav subnav-fixed">
				<ul class="nav nav-pills">
					<li id="site-attributes-li"><a href="#">Site</a></li>
					<li id="kpi-menu-li"><a href="#">KPI</a></li>
					<li id="kpid-menu-li"><a href="#">KPI D</a></li>
                    <li id="kpic-menu-li"><a href="#">KPI Cell</a></li>
                    <li id="kpicd-menu-li"><a href="#">KPI Cell D</a></li>
					<li id="parameter-menu-li"><a href="#">Parameters</a></li>
					<li id="alarm-menu-li"><a href="#">Alarms</a></li>
					<li id="neighbors-menu-li"><a href="#">Neighbors</a></li>
					<li id="nlissues-menu-li"><a href="#">NL Issues</a></li>
					
<?php
				if(trim($this->data['user']['client']) == 'ATT') {
					echo '<li id="geow-menu-li"><a href="#">Geo-W</a></li>';
				}
?>
<?php
				if(trim($this->data['user']['client']) != 'T-Mobile' && trim($this->data['user']['client']) != 'Mobilicity') {
					echo '<li id="csl-nlt-menu-li"><a href="#">CSL / NLT</a></li>';
				}
?>
					<li id="rules-menu-li"><a href="#">Rules</a></li>
					<li id="drive-test-menu-li"><a href="#">Drive Test</a></li>
<?php
				if(trim($this->data['user']['client']) != 'T-Mobile' && trim($this->data['user']['client']) != 'Mobilicity') {
					echo '<li id="disaster-feed-menu-li"><a href="#">Disaster Feed</a></li>';
					echo '<li id="field-tech-menu-li"><a href="#">Field Tech</a></li>';
				}
?>
					<!--<li id="data-attributes"><a href="#">Alerts</a></li>
					<li id="data-attributes"><a href="#">Alarms</a></li>
					<li id="data-attributes"><a href="#">Field Ops Tickets</a></li>
					<li id="data-attributes"><a href="#">Neighbors</a></li>
					<li id="data-attributes"><a href="#">Activities</a></li>
					<li id="data-attributes"><a href="#">Comments</a></li>
					<li id="data-attributes"><a href="#">Charts</a></li>
					<li id="data-attributes"><a href="#">Correlate</a></li>
					<li id="data-attributes"><a href="#">Rules</a></li> -->
					<li id="data-export"class=" pull-right" ><a href="#">Export</a></li>
				</ul>
			</div>
			
			<div id="site-attributes">
				<table id="list"><tr><td/></tr></table>
				<div id="pager"></div>
			</div>

			<div id="kpid-menu">
				<div>
					<div  style="float: left;">
						<form class="form-horizontal well">

							<div class="control-group">
								<label class="control-label" for="datepicker-control">Select Date 1</label>
								<div class="controls">
									<div class="input-append date" id="kpi-datepicker-control1" data-date-format="mm/dd/yyyy">
										<input id="kpi-datepicker1" class="span2" size="16" type="text" value="<?php echo date('m/d/Y', strtotime("-1 week")); ?>">
										<span class="add-on"><i class="icon-th"></i></span>
									</div>
									<span class="help-inline">Time Window 1</span>
									<select id="tw1-combo" class="span2">
										<option>Day</option>
										<option>Week</option>
										<option>Month</option>
									</select>
									<span class="help-inline">Thematic Distribution</span>
									<select id="range-combo" class="span2">
										<option value="equal_interval">Equal-Interval</option>
										<option value="quantile">Equal Distribution</option>
									</select>
									<button id="kpid-range-submit" class="btn btn-info" type="button">Update Ranges</button>
								</div>
							</div>
							<div class="control-group">
								<label class="control-label" for="datepicker-control">Select Date 2</label>
								<div class="controls">
									<div class="input-append date" id="kpi-datepicker-control2" data-date-format="mm/dd/yyyy">
										<input id="kpi-datepicker2" class="span2" size="16" type="text" value="<?php echo date('m/d/Y', strtotime("-2 day")); ?>">
										<span class="add-on"><i class="icon-th"></i></span>
									</div>
									<span class="help-inline">Time Window 2</span>
									<select id="tw2-combo" class="span2">
										<option>Day</option>
										<option>Week</option>
										<option>Month</option>
									</select>
									<span class="help-inline">Technology</span>

									 <select id="tech-combod" class="span2">
                                                                                    <?php
                                                                                        switch($this->data['user']['client']) {
                                                                                            case 'T-Mobile':
                                                                                            /*
                                                                                                foreach($this->data['layers'] as $gkey => $group){
                                                                                                    if($gkey == 'T-Mobile') {
                                                                                                        foreach($group as $lkey => $layer){
                                                                                                            if($lkey == 'UMTS') {
                                                                                                                echo '<option selected="selected">' . $lkey . '</option>';
                                                                                                            } else {
                                                                                                                echo "<option>$lkey</option>";
                                                                                                            }
                                                                                                        }
                                                                                                    }
                                                                                                }
                                                                                            */
                                                                                                            echo '<option>GSM</option>';
                                                                                                            echo '<option>LTE</option>';
                                                                                                            echo '<option selected="selected">UMTS</option>';
                                                                                                            break;
																							case 'Sprint-DR':
																											echo '<option>EVDO</option>';
                                                                                                            echo '<option>LTE</option>';
                                                                                                            echo '<option selected="selected">CDMA</option>';
                                                                                                            break;
                                                                                            default:
                                                                                                            echo '<option>cdma</option>';
                                                                                                            echo '<option>evdo</option>';
                                                                                                            break;
                                                                                        }
                                                                                ?>
                                                                        </select>


									<button id="kpid-submit" class="btn btn-primary" type="button">Update Map</button>
								</div>
							</div>
							<!-- KPI 1-->
							<div class="control-group">
								<label class="control-label">KPI Band 1</label>
								<div class="controls">
									 <select id="kpi1" class="span2" style="width:230px;">
                                                                                <?php switch($this->data['user']['client']) {
                                                                                         case 'T-Mobile':

                                                                                                echo '<option value="Voice_AFR">Voice AFR(%)</option>';
                                                                                                echo '<option selected="selected" value="Voice_DR">Voice DCR(%)</option>';
                                                                                                echo '<option value="PS_AFR">PS AFR(%)</option>';
                                                                                                echo '<option value="PS_RAB_DR">PS DCR(%)</option>';
                                                                                                echo '<option value="HSDPA_PS_AFR">HSDPA AFR(%)</option>';
                                                                                                echo '<option value="HSDPA_PS_DCR">HSDPA DCR(%)</option>';

                                                                                                break;
                                                                                         default:
                                                                                                echo '<option>Blocks</option>';
                                                                                                echo '<option selected="selected">Block_perc</option>';
                                                                                                echo '<option>Drops</option>';
                                                                                                echo '<option>Drop_perc</option>';
                                                                                                echo '<option>ATT</option>';
                                                                                                echo '<option>MOU</option>';
                                                                                                break;
                                                                                }
                                                                                ?>
                                                                        </select>


									<input id="t11" type="text" class="span1" value="0"/>
									<div class="input-append color" data-color="rgb(1, 255, 1)" data-color-format="rgb" id = "cp11">
									  <span class="add-on"><i style="background-color: rgb(1, 255, 1)"></i></span>
									</div>
									<input id="t12" type="text" class="span1" value="1"/>

									<input id="t13" type="text" class="span1 hide" value="1"/>
									<div class="input-append color" data-color="rgb(1, 1, 255)" data-color-format="rgb" id = "cp12">
									  <span class="add-on"><i style="background-color: rgb(1, 1, 255)"></i></span>
									</div>
									<input id="t14" type="text" class="span1" value="2"/>

									<input id="t15" type="text" class="span1 hide" value="2"/>
									<div class="input-append color" data-color="rgb(255, 255, 1)" data-color-format="rgb" id = "cp13">
									  <span class="add-on"><i style="background-color: rgb(255, 255, 1)"></i></span>
									</div>
									<input id="t16" type="text" class="span1" value="3"/>

									<input id="t17" type="text" class="span1 hide" value="3"/>
									<div class="input-append color" data-color="rgb(255, 123, 1)" data-color-format="rgb" id = "cp14">
									  <span class="add-on"><i style="background-color: rgb(255, 123, 1)"></i></span>
									</div>
									<input id="t18" type="text" class="span1" value="4"/>

									<input id="t19" type="text" class="span1 hide" value="3"/>
									<div class="input-append color" data-color="rgb(255, 1, 1)" data-color-format="rgb" id = "cp15">
									  <span class="add-on"><i style="background-color: rgb(255, 1, 1)"></i></span>
									</div>
									<input id="t110" type="text" class="span1" value="4"/>
								</div>
							</div>
							<!-- KPI 2-->
							<div class="control-group">
								<label class="control-label">KPI Band 2</label>
								<div class="controls">

									 <select id="kpi2" class="span2" style="width:230px;">
                                                                                <?php switch($this->data['user']['client']) {
                                                                                         case 'T-Mobile':

                                                                                                echo '<option selected="selected" value="Voice_AFR">Voice AFR(%)</option>';
                                                                                                echo '<option value="Voice_DR">Voice DCR(%)</option>';
                                                                                                echo '<option value="PS_AFR">PS AFR(%)</option>';
                                                                                                echo '<option value="PS_RAB_DR">PS DCR(%)</option>';
                                                                                                echo '<option value="HSDPA_PS_AFR">HSDPA AFR(%)</option>';
                                                                                                echo '<option value="HSDPA_PS_DCR">HSDPA DCR(%)</option>';

                                                                                                break;
                                                                                         default:
                                                                                                echo '<option>Blocks</option>';
                                                                                                echo '<option>Block_perc</option>';
                                                                                                echo '<option>Drops</option>';
                                                                                                echo '<option selected="selected">Drop_perc</option>';
                                                                                                echo '<option>ATT</option>';
                                                                                                echo '<option>MOU</option>';
                                                                                                break;
                                                                                }
                                                                                ?>
                                                                        </select>


									<input id="t21" type="text" class="span1" value="0"/>
									<div class="input-append color" data-color="rgb(1, 255, 1)" data-color-format="rgb" id = "cp21">
									  <span class="add-on"><i style="background-color: rgb(1, 255, 1)"></i></span>
									</div>
									<input id="t22" type="text" class="span1" value="1"/>

									<input id="t23" type="text" class="span1 hide" value="1"/>
									<div class="input-append color" data-color="rgb(1, 1, 255)" data-color-format="rgb" id = "cp22">
									  <span class="add-on"><i style="background-color: rgb(1, 1, 255)"></i></span>
									</div>
									<input id="t24" type="text" class="span1" value="2"/>

									<input id="t25" type="text" class="span1 hide" value="2"/>
									<div class="input-append color" data-color="rgb(255, 255, 1)" data-color-format="rgb" id = "cp23">
									  <span class="add-on"><i style="background-color: rgb(255, 255, 1)"></i></span>
									</div>
									<input id="t26" type="text" class="span1" value="3"/>

									<input id="t27" type="text" class="span1 hide" value="3"/>
									<div class="input-append color" data-color="rgb(255, 123, 1)" data-color-format="rgb" id = "cp24">
									  <span class="add-on"><i style="background-color: rgb(255, 123, 1)"></i></span>
									</div>
									<input id="t28" type="text" class="span1" value="4"/>

									<input id="t29" type="text" class="span1 hide" value="3"/>
									<div class="input-append color" data-color="rgb(255, 1, 1)" data-color-format="rgb" id = "cp25">
									  <span class="add-on"><i style="background-color: rgb(255, 1, 1)"></i></span>
									</div>
									<input id="t210" type="text" class="span1" value="4"/>

								</div>
							</div>
							<!-- KPI 3-->
							<div class="control-group">
								<label class="control-label">KPI Band 3</label>
								<div class="controls">

									 <select id="kpi3" class="span2" style="width:230px;">
                                                                                <?php switch($this->data['user']['client']) {
                                                                                         case 'T-Mobile':

                                                                                                echo '<option value="Voice_AFR">Voice AFR(%)</option>';
                                                                                                echo '<option value="Voice_DR">Voice DCR(%)</option>';
                                                                                                echo '<option value="PS_AFR">PS AFR(%)</option>';
                                                                                                echo '<option value="PS_RAB_DR">PS DCR(%)</option>';
                                                                                                echo '<option selected="selected" value="HSDPA_PS_AFR">HSDPA AFR(%)</option>';
                                                                                                echo '<option value="HSDPA_PS_DCR">HSDPA DCR(%)</option>';

                                                                                                break;
                                                                                         default:
                                                                                                echo '<option>Blocks</option>';
                                                                                                echo '<option >Block_perc</option>';
                                                                                                echo '<option>Drops</option>';
                                                                                                echo '<option>Drop_perc</option>';
                                                                                                echo '<option>ATT</option>';
                                                                                                echo '<option selected="selected">MOU</option>';
                                                                                                break;
                                                                                }
                                                                                ?>
                                                                        </select>


									<input id="t31" type="text" class="span1" value="0"/>
									<div class="input-append color" data-color="rgb(1, 255, 1)" data-color-format="rgb" id = "cp31">
									  <span class="add-on"><i style="background-color: rgb(1, 255, 1)"></i></span>
									</div>
									<input id="t32" type="text" class="span1" value="1"/>

									<input id="t33" type="text" class="span1 hide" value="1"/>
									<div class="input-append color" data-color="rgb(1, 1, 255)" data-color-format="rgb" id = "cp32">
									  <span class="add-on"><i style="background-color: rgb(1, 1, 255)"></i></span>
									</div>
									<input id="t34" type="text" class="span1" value="2"/>

									<input id="t35" type="text" class="span1 hide" value="2"/>
									<div class="input-append color" data-color="rgb(255, 255, 1)" data-color-format="rgb" id = "cp33">
									  <span class="add-on"><i style="background-color: rgb(255, 255, 1)"></i></span>
									</div>
									<input id="t36" type="text" class="span1" value="3"/>

									<input id="t37" type="text" class="span1 hide" value="3"/>
									<div class="input-append color" data-color="rgb(255, 123, 1)" data-color-format="rgb" id = "cp34">
									  <span class="add-on"><i style="background-color: rgb(255, 123, 1)"></i></span>
									</div>
									<input id="t38" type="text" class="span1" value="4"/>

									<input id="t39" type="text" class="span1 hide" value="3"/>
									<div class="input-append color" data-color="rgb(255, 1, 1)" data-color-format="rgb" id = "cp35">
									  <span class="add-on"><i style="background-color: rgb(255, 1, 1)"></i></span>
									</div>
									<input id="t310" type="text" class="span1" value="4"/>
								</div>
							</div>

						</form>
					</div>
					<div id="div2" style="float: right;">
						<table id="kpi-list"><tr><td/></tr></table>
						<div id="kpi-pager"></div>
					</div>
				</div>
			</div>
            <div id="kpicd-menu">
                <div>
                    <div  style="float: left;">
                        <form class="form-horizontal well">

                            <div class="control-group">
                                <label class="control-label" for="datepicker-control">Select Date 1</label>
                                <div class="controls">
                                    <div class="input-append date" id="kpicd-datepicker-control1" data-date-format="mm/dd/yyyy">
                                        <input id="kpicd-datepicker1" class="span2" size="16" type="text" value="<?php echo date('m/d/Y', strtotime("-2 week")); ?>">
                                        <span class="add-on"><i class="icon-th"></i></span>
                                    </div>
                                    <span class="help-inline">Time Window 1</span>
                                    <select id="twcd1-combo" class="span2">
                                        <option>Day</option>
                                        <option>Week</option>
                                        <option>Month</option>
                                    </select>
                                    <span class="help-inline">Thematic Distribution</span>
                                    <select id="rangecd-combo" class="span2">
                                        <option value="equal_interval">Equal-Interval</option>
                                        <option value="quantile">Equal Distribution</option>
                                    </select>
                                    <button id="kpicd-range-submit" class="btn btn-info" type="button">Update Ranges</button>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="datepicker-control">Select Date 2</label>
                                <div class="controls">
                                    <div class="input-append date" id="kpicd-datepicker-control2" data-date-format="mm/dd/yyyy">
                                        <input id="kpicd-datepicker2" class="span2" size="16" type="text" value="<?php echo date('m/d/Y', strtotime("-2 day")); ?>">
                                        <span class="add-on"><i class="icon-th"></i></span>
                                    </div>
                                    <span class="help-inline">Time Window 2</span>
                                    <select id="twcd2-combo" class="span2">
                                        <option>Day</option>
                                        <option>Week</option>
                                        <option>Month</option>
                                    </select>
                                    <span class="help-inline">Technology</span>

                                     <select id="techcd-combod" class="span2">
                                                                                    <?php
                                                                                        switch($this->data['user']['client']) {
                                                                                            case 'T-Mobile':
                                                                                            /*
                                                                                                foreach($this->data['layers'] as $gkey => $group){
                                                                                                    if($gkey == 'T-Mobile') {
                                                                                                        foreach($group as $lkey => $layer){
                                                                                                            if($lkey == 'UMTS Cell') {
                                                                                                                echo '<option selected="selected">' . $lkey . '</option>';
                                                                                                            } else {
                                                                                                                echo "<option>$lkey</option>";
                                                                                                            }
                                                                                                        }
                                                                                                    }
                                                                                                }
                                                                                            */
                                                                                                            //echo '<option value="GSM_Cell">GSM Cell</option>';
                                                                                                            echo '<option selected="selected" value="UMTS_Cell">UMTS Cell</option>';
                                                                                                            break;
																							case 'Sprint-DR':
																											echo '<option>EVDO</option>';
																											echo '<option>LTE</option>';
																											echo '<option selected="selected">CDMA</option>';
																											break;
                                                                                            default:
                                                                                                            echo '<option>cdma</option>';
                                                                                                            echo '<option>evdo</option>';
                                                                                                            break;
                                                                                        }
                                                                                ?>
                                                                        </select>


                                    <button id="kpicd-submit" class="btn btn-primary" type="button">Update Map</button>
                                </div>
                            </div>
                            <!-- KPI 1-->
                            <div class="control-group">
                                <label class="control-label">KPI Band 1</label>
                                <div class="controls">
                                     <select id="kpicd1" class="span2">
                                                                                <?php switch($this->data['user']['client']) {
                                                                                         case 'T-Mobile':

                                                                                                echo '<option value="Voice_AFR">Voice AFR(%)</option>';
                                                                                                echo '<option selected="selected" value="Voice_DR">Voice DCR(%)</option>';
                                                                                                echo '<option value="PS_AFR">PS AFR(%)</option>';
                                                                                                echo '<option value="PS_RAB_DR">PS DCR(%)</option>';
                                                                                                echo '<option value="HSDPA_PS_AFR">HSDPA AFR(%)</option>';
                                                                                                echo '<option value="HSDPA_PS_DCR">HSDPA DCR(%)</option>';

                                                                                                break;
                                                                                         default:
                                                                                                echo '<option>Blocks</option>';
                                                                                                echo '<option selected="selected">Block_perc</option>';
                                                                                                echo '<option>Drops</option>';
                                                                                                echo '<option>Drop_perc</option>';
                                                                                                echo '<option>ATT</option>';
                                                                                                echo '<option>MOU</option>';
                                                                                                break;
                                                                                }
                                                                                ?>
                                                                        </select>


                                    <input id="tcd11" type="text" class="span1" value="0"/>
                                    <div class="input-append color" data-color="rgb(1, 255, 1)" data-color-format="rgb" id = "cpcd11">
                                      <span class="add-on"><i style="background-color: rgb(1, 255, 1)"></i></span>
                                    </div>
                                    <input id="tcd12" type="text" class="span1" value="1"/>

                                    <input id="tcd13" type="text" class="span1 hide" value="1"/>
                                    <div class="input-append color" data-color="rgb(1, 1, 255)" data-color-format="rgb" id = "cpcd12">
                                      <span class="add-on"><i style="background-color: rgb(1, 1, 255)"></i></span>
                                    </div>
                                    <input id="tcd14" type="text" class="span1" value="2"/>

                                    <input id="tcd15" type="text" class="span1 hide" value="2"/>
                                    <div class="input-append color" data-color="rgb(255, 255, 1)" data-color-format="rgb" id = "cpcd13">
                                      <span class="add-on"><i style="background-color: rgb(255, 255, 1)"></i></span>
                                    </div>
                                    <input id="tcd16" type="text" class="span1" value="3"/>

                                    <input id="tcd17" type="text" class="span1 hide" value="3"/>
                                    <div class="input-append color" data-color="rgb(255, 123, 1)" data-color-format="rgb" id = "cpcd14">
                                      <span class="add-on"><i style="background-color: rgb(255, 123, 1)"></i></span>
                                    </div>
                                    <input id="tcd18" type="text" class="span1" value="4"/>

                                    <input id="tcd19" type="text" class="span1 hide" value="3"/>
                                    <div class="input-append color" data-color="rgb(255, 1, 1)" data-color-format="rgb" id = "cpcd15">
                                      <span class="add-on"><i style="background-color: rgb(255, 1, 1)"></i></span>
                                    </div>
                                    <input id="tcd110" type="text" class="span1" value="4"/>
                                </div>
                            </div>

                        </form>
                    </div>
                    <div id="kpicd-div2" style="float: right;">
                        <table id="kpicd-list"><tr><td/></tr></table>
                        <div id="kpicd-pager"></div>
                    </div>
                </div>
            </div>

<!--
S============================================================
-->
			<div id="csl-nlt-menu">
				<div>
					<div style="float: left;">
						<form class="form-horizontal well">
							<fieldset>
								<div class="control-group">
									<label class="control-label" for="datepicker-control">Select Date (to do)</label>
									<div class="controls">
										<div class="input-append date" id="csl-nlt-datepicker-control" data-date-format="mm/dd/yyyy">
										
										<!-- production time... <input id="csl-nlt-datepicker" class="span2" size="16" type="text" value="<?php echo date('Y-m-d H:i', strtotime("-2 day")); ?>"> -->
										
										<!-- (mrt) test time... -->
										<input id="csl-nlt-datepicker" class="span2" size="16" type="text" value="2012-12-12 12:00:00">
										
										<span class="add-on"><i class="icon-th"></i></span>
										&nbsp;&nbsp;Type
										<select id="csl-nlt-tech-combo" class="span2">
										<?php switch($this->data['user']['client']) {
											//case 'T-Mobile':
											//		echo '<option>GSM</option>';
											//		echo '<option selected="selected">UMTS</option>';
											//		break;
											default:
													echo '<option selected="selected">CSL</option>';
													echo '<option>NLT</option>';
													break;
										}
										?>
										</select>
										
										&nbsp;&nbsp;<!-- Parameter -->
										<!-- <select id="csl-parameter" class="span2">
											<option value="ecio">Ec/Io (db)</option>
											<option value="failure">Failures</option>
										</select> -->
										
										</div>
									</div>
								</div>
									
<!-- 								<div class="control-group"> -->
<!-- 									<label class="control-label">Number of Levels</label> -->
<!-- 									<div class="controls"> -->
<!-- 										<select id="csl-level-count" class="span2"> -->
<!-- 											<option>1</option> -->
<!-- 											<option>2</option> -->
<!-- 											<option>3</option> -->
<!-- 											<option>4</option> -->
<!-- 											<option SELECTED>5</option> -->
<!-- 										</select> -->
<!-- 									</div> -->
<!-- 								</div> -->
								
								<div class="control-group">
									<!-- <label class="control-label">CSL EC/IO</label> -->
									<div class="controls">
									
									<!-- 
										1 == Blocked -> 0xff0808
										5 == RF_Failure -> 0xff0000
										6 == Network_Failure -> 0xff8800
										7 == Unknown_Setup_Failure -> 0xff0088
										8 == Network_Drop -> 0xff44cc
										9 == RF_Drop -> 0xffcc44
									 -->
									
<!-- 										<select id="csl-failure-1" class="span2"> -->
											<?php 
// 											switch($this->data['user']['client']) {
// 														case 'T-Mobile':
// 															echo '<option value="1" selected="selected">Blocked</option>';
// 															echo '<option value="5">RF Failure</option>';
// 															echo '<option value="6">Network Failure</option>';
// 															echo '<option value="7">Setup Failure</option>';
// 															echo '<option value="8">Network Drop</option>';
// 															echo '<option value="9">RF Drop</option>';
// 															break;
															
// 														default:
// 															echo '<option value="1" selected="selected">Blocked</option>';
// 															echo '<option value="5">RF Failure</option>';
// 															echo '<option value="6">Network Failure</option>';
// 															echo '<option value="7">Setup Failure</option>';
// 															echo '<option value="8">Network Drop</option>';
// 															echo '<option value="9">RF Drop</option>';
// 															break;
// 											} 
											?>
<!-- 										</select> -->
<!-- 										<div class="input-append color" data-color="rgb(255, 8, 8)" data-color-format="rgb" id = "csl-failure-color-1"> -->
<!--										  <span class="add-on"><i style="background-color: rgb(255, 8, 8)"></i></span> -->
<!-- 										</div> -->
										&nbsp;
<!-- 										<select id="csl-failure-8" class="span2"> -->
											<?php 
//											switch($this->data['user']['client']) {
// 														case 'T-Mobile':
// 															echo '<option value="1">Blocked</option>';
// 															echo '<option value="5">RF Failure</option>';
// 															echo '<option value="6">Network Failure</option>';
// 															echo '<option value="7">Setup Failure</option>';
// 															echo '<option value="8" selected="selected">Network Drop</option>';
// 															echo '<option value="9">RF Drop</option>';
// 															break;
															
// 														default:
// 															echo '<option value="1" selected="selected">Blocked</option>';
// 															echo '<option value="5">RF Failure</option>';
// 															echo '<option value="6">Network Failure</option>';
// 															echo '<option value="7">Setup Failure</option>';
// 															echo '<option value="8" selected="selected">Network Drop</option>';
// 															echo '<option value="9">RF Drop</option>';
// 															break;
// 											} 
											?>
<!-- 										</select> -->
<!-- 										<div class="input-append color" data-color="rgb(255, 68, 204)" data-color-format="rgb" id = "csl-failure-color-8"> -->
										  <span class="add-on"><i style="background-color: rgb(255, 68, 204)"></i></span>
<!-- 										</div> -->
										&nbsp;
<!-- 										<select id="csl-failure-9" class="span2"> -->
											<?php 
// 											switch($this->data['user']['client']) {
// 														case 'T-Mobile':
// 															echo '<option value="1">Blocked</option>';
// 															echo '<option value="5">RF Failure</option>';
// 															echo '<option value="6">Network Failure</option>';
// 															echo '<option value="7">Setup Failure</option>';
// 															echo '<option value="8">Network Drop</option>';
// 															echo '<option value="9" selected="selected">RF Drop</option>';
// 															break;
															
// 														default:
// 															echo '<option value="1">Blocked</option>';
// 															echo '<option value="5">RF Failure</option>';
// 															echo '<option value="6">Network Failure</option>';
// 															echo '<option value="7">Setup Failure</option>';
// 															echo '<option value="8">Network Drop</option>';
// 															echo '<option value="9" selected="selected">RF Drop</option>';
// 															break;
// 											} 
// 											?>
<!-- 										</select> -->
<!-- 										<div class="input-append color" data-color="rgb(255, 204, 68)" data-color-format="rgb" id = "csl-failure-color-9"> -->
										  <span class="add-on"><i style="background-color: rgb(255, 204, 68)"></i></span>
<!-- 										</div> -->
									</div>
								</div>
								
								<div class="control-group">
									<!-- <label class="control-label">CSL Failures</label> -->
									<div class="controls">
										
<!-- 										<select id="csl-failure-7" class="span2"> -->
											<?php 
// 											switch($this->data['user']['client']) {
// 														case 'T-Mobile':
// 															echo '<option value="1">Blocked</option>';
// 															echo '<option value="5">RF Failure</option>';
// 															echo '<option value="6">Network Failure</option>';
// 															echo '<option value="7" selected="selected">Setup Failure</option>';
// 															echo '<option value="8">Network Drop</option>';
// 															echo '<option value="9">RF Drop</option>';
// 															break;
															
// 														default:
// 															echo '<option value="1" selected="selected">Blocked</option>';
// 															echo '<option value="5">RF Failure</option>';
// 															echo '<option value="6">Network Failure</option>';
// 															echo '<option value="7" selected="selected">Setup Failure</option>';
// 															echo '<option value="8">Network Drop</option>';
// 															echo '<option value="9">RF Drop</option>';
// 															break;
// 											} 
											?>
										</select>
<!-- 										<div class="input-append color" data-color="rgb(255, 0, 136)" data-color-format="rgb" id = "csl-failure-color-7"> -->
<!--										  <span class="add-on"><i style="background-color: rgb(255, 0, 136)"></i></span> -->
<!-- 										</div> -->
										&nbsp;
<!-- 										<select id="csl-failure-6" class="span2"> -->
											<?php 
// 											switch($this->data['user']['client']) {
// 														case 'T-Mobile':
// 															echo '<option value="1">Blocked</option>';
// 															echo '<option value="5">RF Failure</option>';
// 															echo '<option value="6" selected="selected">Network Failure</option>';
// 															echo '<option value="7">Setup Failure</option>';
// 															echo '<option value="8">Network Drop</option>';
// 															echo '<option value="9">RF Drop</option>';
// 															break;
															
// 														default:
// 															echo '<option value="1">Blocked</option>';
// 															echo '<option value="5">RF Failure</option>';
// 															echo '<option value="6" selected="selected">Network Failure</option>';
// 															echo '<option value="7">Setup Failure</option>';
// 															echo '<option value="8">Network Drop</option>';
// 															echo '<option value="9">RF Drop</option>';
// 															break;
// 											} 
// 											?>
<!-- 										</select> -->
<!-- 										<div class="input-append color" data-color="rgb(255, 136, 0)" data-color-format="rgb" id = "csl-failure-color-6"> -->
										  <span class="add-on"><i style="background-color: rgb(255, 136, 0)"></i></span>
<!-- 										</div> -->
										&nbsp;
<!-- 										<select id="csl-failure-5" class="span2"> -->
											<?php 
// 											switch($this->data['user']['client']) {
// 														case 'T-Mobile':
// 															echo '<option value="1">Blocked</option>';
// 															echo '<option value="5" selected="selected">RF Failure</option>';
// 															echo '<option value="6">Network Failure</option>';
// 															echo '<option value="7">Setup Failure</option>';
// 															echo '<option value="8">Network Drop</option>';
// 															echo '<option value="9">RF Drop</option>';
// 															break;
															
// 														default:
// 															echo '<option value="1">Blocked</option>';
// 															echo '<option value="5" selected="selected">RF Failure</option>';
// 															echo '<option value="6">Network Failure</option>';
// 															echo '<option value="7">Setup Failure</option>';
// 															echo '<option value="8">Network Drop</option>';
// 															echo '<option value="9">RF Drop</option>';
// 															break;
// 											} 
// 											?>
<!-- 										</select> -->
<!-- 										<div class="input-append color" data-color="rgb(255, 0, 0)" data-color-format="rgb" id = "csl-failure-color-5"> -->
										  <span class="add-on"><i style="background-color: rgb(255, 0, 0)"></i></span>
<!-- 										</div> -->
										
									
<!-- 										<div class="input-append color" data-color="rgb(255,0,0)" data-color-format="rgb" id = "csl-div-color-0"> -->
<!-- 											<input type="hidden" value="rgb(255,0,0)" id="csl-color-0"> -->
											<span class="add-on"><i style="background-color: rgb(255,0,0)"></i></span>
<!-- 										</div> -->

<!-- 										<input id="csl-input-1" type="text" class="span1" value="-17"/> -->
										
<!-- 										<div class="input-append color" data-color="rgb(255, 180,60)" data-color-format="rgb" id = "csl-div-color-1"> -->
<!-- 											<input type="hidden" value="rgb(255, 180,60)" id="csl-color-1"> -->
											<span class="add-on"><i style="background-color: rgb(255, 180,60)"></i></span>
<!-- 										</div> -->

<!-- 										<input id="csl-input-2" type="text" class="span1" value="-14"/> -->
										
<!-- 										<div class="input-append color" data-color="rgb(255, 255, 0)" data-color-format="rgb" id = "csl-div-color-2"> -->
<!-- 											<input type="hidden" value="rgb(255, 255, 0)" id="csl-color-2"> -->
											<span class="add-on"><i style="background-color: rgb(255, 255, 0)"></i></span>
<!-- 										</div> -->

<!-- 										<input id="csl-input-3" type="text" class="span1" value="-12"/> -->

<!-- 										<div class="input-append color" data-color="rgb(0,128,0)" data-color-format="rgb" id = "csl-div-color-3"> -->
<!-- 											<input type="hidden" value="rgb(0,128,0)" id="csl-color-3"> -->
											<span class="add-on"><i style="background-color: rgb(0,128,0)"></i></span>
<!-- 										</div> -->

<!-- 										<input id="csl-input-4" type="text" class="span1" value="-9"/> -->

<!-- 										<div class="input-append color" data-color="rgb(0,220,220)" data-color-format="rgb" id = "csl-div-color-4"> -->
<!-- 											<input type="hidden" value="rgb(0,220,220)" id="csl-color-4"> -->
											<span class="add-on"><i style="background-color: rgb(0,220,220)"></i></span>
<!-- 										</div> -->

<!-- 										<input id="csl-input-5" type="text" class="span1" value="-7"/> -->

<!-- 										<div class="input-append color" data-color="rgb(0,0,255)" data-color-format="rgb" id = "csl-div-color-5"> -->
<!-- 											<input type="hidden" value="rgb(0,0,255)" id="csl-color-5"> -->
											<span class="add-on"><i style="background-color: rgb(0,0,255)"></i></span>
<!-- 										</div> -->
	
									</div>
									<br />
								</div>
								
								<div class="control-group">
									<div class="controls">
										<button id="csl-nlt-submit" class="btn btn-primary" type="button">Submit</button>
									</div>
									<br /><br /><br />
								</div>
							</fieldset>
						</form>
					 </div>
					<div id="csl-nlt-div2" style="float: right;">
						<table id="csl-nlt-list"><tr><td/></tr></table>
						<div id="csl-nlt-pager"></div>
					</div>
				</div>
			</div>
<!--
E============================================================
-->
			<div id="nlissues-menu">
				<div>
					<div  style="float: left;">
						<form class="form-horizontal well">
							<fieldset>
							<div class="control-group">
								<label class="control-label" for="datepicker-control">Select Date</label>
								<div class="controls">
									<div class="input-append date" id="nlissues-datepicker-control" data-date-format="mm/dd/yyyy">
									<input id="nlissues-datepicker" class="span2" size="16" type="text" value="<?php echo date('m/d/Y', strtotime("-2 day")); ?>">
									<span class="add-on"><i class="icon-th"></i></span>
									</div>
									<span class="help-inline">Tech</span>
									<select id="nlissues-tech-combo" class="span2">
									<?php switch($this->data['user']['client']) {
										case 'T-Mobile':
												echo '<option>GSM</option>';
												echo '<option selected="selected">UMTS</option>';
												break;
										default:
												echo '<option>CDMA</option>';
												echo '<option>EVDO</option>';
												break;
									}
									?>
									</select>

								</div>
							</div>


							<div class="control-group">
								<label class="control-label">Type</label>
								<div class="controls">
									<select id="nlissues-type" class="span2">
									<option>All</option>
									<option>Change</option>
									<option>Missing</option>
									<option>Undeclared</option>
									<option>Tier2 Conflict</option>
									<option>Tier3 Conflict</option>
									</select>
									<button id="nlissues-submit" class="btn btn-primary" type="button">Submit</button>
									<br> <br>  <br> <br>  <br> <br>  <br> <br>

								</div>
							</div>

							</fieldset>
						</form>
					 </div>
					<div id="nlissues-div2" style="float: right;">
						<table id="nlissues-list"><tr><td/></tr></table>
						<div id="nlissues-pager"></div>
					</div>
				</div>

			</div>


			<div id="neighbors-menu">
				<div>
					<div  style="float: left;">
						<form class="form-horizontal well">
							<fieldset>
							<div class="control-group">
								<label class="control-label" for="datepicker-control">Select Date</label>
								<div class="controls">
									<div class="input-append date" id="neighbors-datepicker-control" data-date-format="mm/dd/yyyy">
									<input id="neighbors-datepicker" class="span2" size="16" type="text" value="<?php echo date('m/d/Y', strtotime("-2 day")); ?>">
									<span class="add-on"><i class="icon-th"></i></span>
									</div>
									<span class="help-inline">Tech</span>
									<select id="neighbors-tech-combo" class="span2">
									<?php switch($this->data['user']['client']) {
										case 'T-Mobile':
												echo '<option>GSM</option>';
												echo '<option selected="selected">UMTS</option>';
												break;
										default:
												echo '<option>CDMA</option>';
												echo '<option>EVDO</option>';
												break;
									}
									?>
									</select>

								</div>
							</div>


							<div class="control-group">
								<label class="control-label">Type</label>
								<div class="controls">
									<select id="neighbors-type" class="span2">
									<option>All</option>
									<option>Regular</option>
									<option>IRAT</option>
									</select>
									&nbsp;&nbsp;Display All&nbsp;&nbsp;<input type="checkbox" id="neighbors-displayall"></input>
									&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									<button id="neighbors-submit" class="btn btn-primary" type="button">Submit</button>
								</div>
									<br />
                                    <div class="input-append color" data-color="rgb(1, 255, 1)" data-color-format="rgb" id = "neighborswatch2">
										<span class="add-on"><i style="background-color: rgb(0, 102, 0)">&nbsp;</i></span>
									</div> CHP > 20% & HOSR > 80%
									&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <div class="input-append color" data-color="rgb(1, 255, 1)" data-color-format="rgb" id = "neighborswatch2">
										<span class="add-on"><i style="background-color: rgb(0, 255, 0)">&nbsp;</i></span>
									</div> CHP <  20% & CHP > 5% & HOSR > 80%<br />
                                    <div class="input-append color" data-color="rgb(1, 255, 1)" data-color-format="rgb" id = "neighborswatch2">
										<span class="add-on"><i style="background-color: rgb(204, 255, 0)">&nbsp;</i></span>
									</div>  CHP < 5% & CHP > 0% & HOSR > 80%
									&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
 									&nbsp;&nbsp;&nbsp;
                                    <div class="input-append color" data-color="rgb(1, 255, 1)" data-color-format="rgb" id = "neighborswatch2">
										<span class="add-on"><i style="background-color: rgb(204, 51, 0)">&nbsp;</i></span>
									</div>  CHP < 5% & CHP > 0% & HOSR < 80%<br />
                                    <div class="input-append color" data-color="rgb(1, 255, 1)" data-color-format="rgb" id = "neighborswatch2">
										<span class="add-on"><i style="background-color: rgb(153, 51, 51)">&nbsp;</i></span>
									</div> CHP < 20% & CHP > 5% & HOSR < 80%&nbsp;&nbsp;
									&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <div class="input-append color" data-color="rgb(1, 255, 1)" data-color-format="rgb" id = "neighborswatch2">
										<span class="add-on"><i style="background-color: rgb(153, 0, 0)">&nbsp;</i></span>
									</div> CHP > 20% & HOSR < 80%<br />
                                    <div class="input-append color" data-color="rgb(1, 255, 1)" data-color-format="rgb" id = "neighborswatch2">
										<span class="add-on"><i style="background-color: rgb(238, 238, 238)">&nbsp;</i></span>
									</div> CHP = 0%<br />
							</div>
							</fieldset>
						</form>
					 </div>
					<div id="neighbors-div2" style="float: right;">
						<table id="neighbors-list"><tr><td/></tr></table>
						<div id="neighbors-pager"></div>
					</div>
				</div>
			</div>

			<div id="rules-menu">
				<div>
					<div  style="float: left;">
						<form class="form-horizontal well">
							<fieldset>
							<div class="control-group">
								<label class="control-label" for="datepicker-control">Select Date</label>
								<div class="controls">
									<div class="input-append date" id="rules-datepicker-control" data-date-format="mm/dd/yyyy">
									<input id="rules-datepicker" class="span2" size="16" type="text" value="<?php echo date('m/d/Y', strtotime("-2 day")); ?>">
									<span class="add-on"><i class="icon-th"></i></span>
									</div>
									<span class="help-inline">Tech</span>
									<select id="rules-tech-combo" class="span2">
									<?php switch($this->data['user']['client']) {
										case 'T-Mobile':
												echo '<option>GSM</option>';
												echo '<option selected="selected">UMTS</option>';
												break;
										default:
												echo '<option>CDMA</option>';
												echo '<option>EVDO</option>';
												break;
									}
									?>
									</select>

								</div>
							</div>
							<div class="control-group">
								<label class="control-label">Time Window</label>
								<div class="controls">
									<select id="rules-tw-combo" class="span2">
									<option>Day</option>
									<option>Week</option>
									<option>Month</option>
									</select>

								</div>
							</div>

							<div class="control-group">
								<label class="control-label">Type</label>
								<div class="controls">
									<select id="rules-type" class="span2">
									<option>Raw</option>
									<option>STA</option>
									<option>LTA</option>
									</select>

								</div>
							</div>



							<div class="control-group">
								<label class="control-label">Metric</label>
								<div class="controls">
									<select id="rules-metric" class="span2">
									<?php switch($this->data['user']['client']) {
										 case 'T-Mobile':
											echo '<option value="Voice_Drop">Voice_Drop</option>';
											echo '<option selected="selected" value="All">All</option>';
											echo '<option value="Voice_AFR">Voice AF</option>';
											echo '<option value="PS_Drop ">PS Drop</option>';
											echo '<option value="PS_AFR">PS AF</option>';
											echo '<option value="HSDPA_Drop">HSDPA Drop</option>';
											echo '<option value="HSDPA_AFR">HSDPA AF</option>';
											echo '<option value="Capacity">Capacity</option>';
											break;
										 default:
											echo '<option>Drops</option>';
											echo '<option selected="selected">Blocks</option>';
											echo '<option>Block_perc</option>';
											echo '<option>Drop_perc</option>';
											echo '<option>ATT</option>';
											echo '<option>MOU</option>';
											break;
									}
									?>
									</select>
								</div>
							</div>
							<div class="control-group">
								<label class="control-label">Threshold</label>
								<div class="controls">
									<select id="rules-threshold" class="span1">
										<option value="1">1</option>
										<option value="2">2</option>
										<option value="5">5</option>
										<option value="10">10</option>
										<option value="100">100</option>
									</select>

									<!--<select id="rules-op1" class="span1">
									<option value="lt"><</option>
									<option value="le"><=</option>
									<option value="gt">></option>
									<option value="ge" selected="selected">>=</option>
									<option value="eq">=</option>
									<option value="ne"><></option>
									</select>
									<input id="rules-val1" type="text" class="span1" value=".5"/> -->
									<button id="rules-submit" class="btn btn-primary" type="button">Submit</button>


								</div>
							</div>
							</fieldset>
						</form>
					 </div>
					<div id="rules-div2" style="float: right;">
						<table id="rules-list"><tr><td/></tr></table>
						<div id="rules-pager"></div>
					</div>
				</div>

			</div>


			<div id="alarm-menu">
				<div>
					<div  style="float: left;">
						<form class="form-horizontal well">
							<fieldset>
							<div class="control-group">
								<label class="control-label" for="datepicker-control">Select Date</label>
								<div class="controls">
									<div class="input-append date" id="alarm-datepicker-control" data-date-format="mm/dd/yyyy">
									<input id="alarm-datepicker" class="span2" size="16" type="text" value="<?php echo date('m/d/Y', strtotime("-2 day")); ?>">
									<span class="add-on"><i class="icon-th"></i></span>
									</div>
									<span class="help-inline">Tech</span>
									<select id="alarm-tech-combo" class="span2">
									<?php switch($this->data['user']['client']) {
										case 'T-Mobile':
												echo '<option>GSM</option>';
												echo '<option selected="selected">UMTS</option>';
												break;
										default:
												echo '<option>CDMA</option>';
												echo '<option>EVDO</option>';
												break;
									}
									?>
									</select>

								</div>
							</div>
							<div class="control-group">
								<label class="control-label">Time Window</label>
								<div class="controls">
									<select id="alarm-tw-combo" class="span2">
									<option>Day</option>
									<option>Week</option>
									<option>Month</option>
									</select>

								</div>
							</div>
							<div class="control-group">

								<input type="checkbox" id="alarm-cleared" class="span1" />
								<label class="control-label">Incl. Cleared Alarms</label>
							</div>
							<!-- Alarm Type --->

							<div class="control-group">
								<label class="control-label">Severity</label>
								<div class="controls">
									<select id="alarm-type" class="span2">
									<?php switch($this->data['user']['client']) {
										 case 'T-Mobile':
											echo '<option value="Critical">Critical</option>';
											echo '<option selected="selected" value="All">All</option>';
											echo '<option value="Major">Major</option>';
											echo '<option value="Minor">Minor</option>';
											echo '<option value="Indeterminate">Indeterminate</option>';
											echo '<option value="Warning">Warning</option>';
											echo '<option value="Cleared">Cleared</option>';


											break;
										 default:
											echo '<option>Blocks</option>';
											echo '<option selected="selected">Block_perc</option>';
											echo '<option>Drops</option>';
											echo '<option>Drop_perc</option>';
											echo '<option>ATT</option>';
											echo '<option>MOU</option>';
											break;
									}
									?>
									</select>

								 </div>
							 </div>
							 <div class="control-group">
                                                                <label class="control-label">Threshold</label>
                                                                <div class="controls">

									<select id="alarm-threshold" class="span1">
										<option value="1">1</option>
										<option value="2">2</option>
										<option value="5">5</option>
										<option value="10">10</option>
										<option value="100">100</option>
									</select>

									<!--<select id="alarm-op1" class="span1">
									<option value="lt"><</option>
									<option value="le"><=</option>
									<option value="gt">></option>
									<option value="ge" selected="selected">>=</option>
									<option value="eq">=</option>
									<option value="ne"><></option>
									</select>
									<input id="alarm-val1" type="text" class="span1" value=".5"/> -->
									<button id="alarm-submit" class="btn btn-primary" type="button">Submit</button>


								</div>
							</div>
							</fieldset>
						</form>
					 </div>
					<div id="alarm-div2" style="float: right;">
						<table id="alarm-list"><tr><td/></tr></table>
						<div id="alarm-pager"></div>
					</div>
				</div>

			</div>

			<div id="parameter-menu">
				<div>
					<div  style="float: left;">
						<form class="form-horizontal well">
							<fieldset>
							<div class="control-group">
								<label class="control-label" for="datepicker-control">Select Date</label>
								<div class="controls">
									<div class="input-append date" id="parameter-datepicker-control" data-date-format="mm/dd/yyyy">
									<input id="parameter-datepicker" class="span2" size="16" type="text" value="<?php echo date('m/d/Y', strtotime("-2 day")); ?>">
									<span class="add-on"><i class="icon-th"></i></span>
									</div>
									<span class="help-inline">Tech</span>
									<select id="parameter-tech-combo" class="span2">
									<?php 
										switch($this->data['user']['client']) {
											case 'T-Mobile':
													echo '<option value="GSM">GSM Cell</option>';
													echo '<option selected="selected" value="UMTS_Cell">UMTS Cell</option>';
													break;
											default:
													echo '<option>CDMA</option>';
													echo '<option>EVDO</option>';
													break;
										}
										?>
									</select>

								</div>
							</div>
							<div class="control-group">
								<label class="control-label">Time Window</label>
								<div class="controls">
									<select id="parameter-tw-combo" class="span2">
									<option>Day</option>
									<option>Week</option>
									<option>Month</option>
									</select>

								</div>
							</div>

							<!-- Parameter Type --->

							<div class="control-group">
								<label class="control-label">Type</label>
								<div class="controls">
									<select id="parameter-type" class="span2">
									<?php switch($this->data['user']['client']) {
										 case 'T-Mobile':

											echo '<option selected="selected" value="All">All</option>';
											echo '<option value="Eul">Eul</option>';
											echo '<option value="Fach">Fach</option>';
											echo '<option value="Hsdsch">Hsdsch</option>';
											echo '<option value="NodeBFunction">NodeBFunction</option>';
											echo '<option value="RbsLocalCell">RBSLocalCell</option>';
											echo '<option value="UtranCell">UTRANCell</option>';

											break;
										 default:
											echo '<option>Blocks</option>';
											echo '<option selected="selected">Block_perc</option>';
											echo '<option>Drops</option>';
											echo '<option>Drop_perc</option>';
											echo '<option>ATT</option>';
											echo '<option>MOU</option>';
											break;
									}
									?>
									</select>
								 </div>

							 </div>

							 <div class="control-group">
                                <label class="control-label">Check</label>
                                <div class="controls">
									<select id="parameter-check-type" class="span2">
										<!-- <option value="Compliance">Compliance</option> -->
										<option value="Change">Change</option>
									</select>

									<!--<select id="parameter-op1" class="span1">
									<option value="lt"><</option>
									<option value="le"><=</option>
									<option value="gt">></option>
									<option value="ge" selected="selected">>=</option>
									<option value="eq">=</option>
									<option value="ne"><></option>
									</select>
									<input id="parameter-val1" type="text" class="span1" value=".5"/> -->
									<button id="parameter-submit" class="btn btn-primary" type="button">Submit</button>
									<!-- <br>
                                     <div class="input-append color" data-color="rgb(1, 255, 1)" data-color-format="rgb">
                                      0 - 2&nbsp;<span class="add-on"><i style="background-color: rgb(1, 255, 1)"></i></span>
                                    </div>
                                    <div class="input-append color" data-color="rgb(1, 255, 200)" data-color-format="rgb">
                                      3 - 5&nbsp;<span class="add-on"><i style="background-color: rgb(1, 255, 200)"></i></span>
                                    </div>
                                    <div class="input-append color" data-color="rgb(1, 100, 255)" data-color-format="rgb">
                                      6 - 8&nbsp;<span class="add-on"><i style="background-color: rgb(1, 100, 255)"></i></span>
                                    </div>
                                    <div class="input-append color" data-color="rgb(255, 1, 100)" data-color-format="rgb">
                                      9+&nbsp;<span class="add-on"><i style="background-color: rgb(255, 1, 100)"></i></span>
                                    </div>
									<br> <br> -->

								</div>
							</div>
							</fieldset>
						</form>
					 </div>
					<div id="parameter-div2" style="float: right;">
						<table id="parameter-list"><tr><td/></tr></table>
						<div id="parameter-pager"></div>
					</div>
				</div>

			</div>

			<div id="geow-menu">
				<div>
					<div  style="float: left;">
						<form class="form-horizontal well">
							<fieldset>
							<div class="control-group">
								<label class="control-label" for="datepicker-control">Select Date</label>
								<div class="controls">
									<div class="input-append date" id="geow-datepicker-control" data-date-format="mm/dd/yyyy">
									<input id="geow-datepicker" class="span2" size="16" type="text" value="<?php echo date('m/d/Y', strtotime("29 November 2012")); ?>">
									<span class="add-on"><i class="icon-th"></i></span>
									</div>
								 	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								 	<button id="geow-submit" class="btn btn-primary" type="button">Submit</button>
								</div>
								<label class="control-label" for="datepicker-control">Start Hour</label>
								<div class="controls">
									<input id="geow-hour-start" style="width:20px;" class="span2" type="text" value="15">
									&nbsp;&nbsp;&nbsp;&nbsp;End Hour
									<input id="geow-hour-end" style="width:20px;" class="span2" type="text" value="15">
								</div>
								<br />
								<label class="control-label">Bin Size</label>
								<div class="controls">
									<select id="geow-bin-combo" class="span2">
									<option selected="selected" value="4">10 Meters</option>
									<option value="3">100 Meters</option>
									</select>
								 </div>
								<label class="control-label">Parameters</label>
								<div class="controls">
									<select id="geow-param-combo" class="span2">
									<option>CPICH EC NO</option>
									<option>RSCP</option>
									</select>
								</div>
								<label class="control-label">Type</label>
								<div class="controls">
									<select id="geow-type" class="span2">
									<option selected="selected" value="db">db Average</option>
									<option value="max">Maximum</option>
									</select>
								</div>
							</div>

							<div class="control-group">
								<label class="control-label">KPI Band</label>
								<div class="controls">
										<div class="input-append color" data-color="rgb(255,0,0)" data-color-format="rgb" id = "geowc0">
										<input type="hidden" value="rgb(255,0,0)" id="geowv0">
										<span class="add-on"><i style="background-color: rgb(255,0,0)"></i></span>
										</div>

										<input id="td1" type="text" class="span1" value="-50"/>
										<div class="input-append color" data-color="rgb(255, 180,60)" data-color-format="rgb" id = "geowc1">
										<input type="hidden" value="rgb(255, 180,60)" id="geowv1">
										<span class="add-on"><i style="background-color: rgb(255, 180,60)"></i></span>
										</div>

										<input id="td2" type="text" class="span1" value="-40"/>

										<div class="input-append color" data-color="rgb(255, 255, 0)" data-color-format="rgb" id = "geowc2">
										<input type="hidden" value="rgb(255, 255, 0)" id="geowv2">
										<span class="add-on"><i style="background-color: rgb(255, 255, 0)"></i></span>
										</div>

										<input id="td3" type="text" class="span1" value="-30"/>

										<div class="input-append color" data-color="rgb(0,128,0)" data-color-format="rgb" id = "geowc3">
										<input type="hidden" value="rgb(0,128,0)" id="geowv3">
										<span class="add-on"><i style="background-color: rgb(0,128,0)"></i></span>
										</div>

										<input id="td4" type="text" class="span1" value="-20"/>

										<div class="input-append color" data-color="rgb(0,220,220)" data-color-format="rgb" id = "geowc4">
										<input type="hidden" value="rgb(0,220,220)" id="geowv4">
										<span class="add-on"><i style="background-color: rgb(0,220,220)"></i></span>
										</div>

										<input id="td5" type="text" class="span1" value="-10"/>

										<div class="input-append color" data-color="rgb(0,0,255)" data-color-format="rgb" id = "geowc5">
										<input type="hidden" value="rgb(0,0,255)" id="geowv5">
										<span class="add-on"><i style="background-color: rgb(0,0,255)"></i></span>
										</div>
								</div>
							</div>
							<!-- geow Type --->


							 <!--<div class="control-group">
                                <div class="controls">
									<select id="geow-check-type" class="span2">
										<!-- <option value="Compliance">Compliance</option>
										<option value="Change">Change</option>
									</select>

									<select id="geow-op1" class="span1">
									<option value="lt"><</option>
									<option value="le"><=</option>
									<option value="gt">></option>
									<option value="ge" selected="selected">>=</option>
									<option value="eq">=</option>
									<option value="ne"><></option>
									</select> -->
									<!-- <br>
                                     <div class="input-append color" data-color="rgb(1, 255, 1)" data-color-format="rgb">
                                      0 - 2&nbsp;<span class="add-on"><i style="background-color: rgb(1, 255, 1)"></i></span>
                                    </div>
                                    <div class="input-append color" data-color="rgb(1, 255, 200)" data-color-format="rgb">
                                      3 - 5&nbsp;<span class="add-on"><i style="background-color: rgb(1, 255, 200)"></i></span>
                                    </div>
                                    <div class="input-append color" data-color="rgb(1, 100, 255)" data-color-format="rgb">
                                      6 - 8&nbsp;<span class="add-on"><i style="background-color: rgb(1, 100, 255)"></i></span>
                                    </div>
                                    <div class="input-append color" data-color="rgb(255, 1, 100)" data-color-format="rgb">
                                      9+&nbsp;<span class="add-on"><i style="background-color: rgb(255, 1, 100)"></i></span>
                                    </div>
									<br> <br>

								</div>
							</div> -->
							</fieldset>
						</form>
					 </div>
				</div>

			</div>

			<div id="kpic-menu">
				<div>
					<div  style="float: left;">
						<form class="form-horizontal well">
							<fieldset>
							<div class="control-group">
								<label class="control-label" for="datepicker-control">Select Date</label>
								<div class="controls">
									<div class="input-append date" id="kpic-datepicker-control" data-date-format="mm/dd/yyyy">
										<input id="kpic-datepicker" class="span2" size="16" type="text" value="<?php echo date('m/d/Y', strtotime("-2 day")); ?>">
										<span class="add-on"><i class="icon-th"></i></span>
									</div>
									<span class="help-inline">Layer</span>
									<select id="kpic-tech-combo" class="span2">
										<?php switch($this->data['user']['client']) {
											case 'T-Mobile':
													//echo '<option>GSM Cell</option>';
													echo '<option selected="selected">UMTS Cell</option>';
													break;
											case 'Sprint-DR':
													echo '<option>EVDO</option>';
													echo '<option>LTE</option>';
													echo '<option selected="selected">CDMA</option>';
													break;
											default:
													echo '<option>cdma</option>';
                                                                                                        echo '<option>evdo</option>';
                                                                                                        break;
										}
										?>
									</select>

								</div>
							</div>
							<div class="control-group">
								<label class="control-label">Time Window</label>
								<div class="controls">
									<select id="kpic-tw-combo" class="span2">
										<option>Day</option>
										<option>Week</option>
										<option>Month</option>
									</select>

								</div>
							</div>
							<!-- KPI 1-->
							<div class="control-group">
								<label class="control-label">KPI Band 1</label>
								<div class="controls">
									<select id="kpic1" class="span2">
										<?php switch($this->data['user']['client']) {
											 case 'T-Mobile':

												echo '<option value="Voice_AFR">Voice AFR(%)</option>';
                                                                                                echo '<option selected="selected" value="Voice_DR">Voice DCR(%)</option>';
                                                                                                echo '<option value="PS_AFR">PS AFR(%)</option>';
                                                                                                echo '<option value="PS_RAB_DR">PS DCR(%)</option>';
                                                                                                echo '<option value="HSDPA_PS_AFR">HSDPA AFR(%)</option>';
                                                                                                echo '<option value="HSDPA_PS_DCR">HSDPA DCR(%)</option>';

												break;
											 default:
												echo '<option>Blocks</option>';
												echo '<option selected="selected">Block_perc</option>';
												echo '<option>Drops</option>';
												echo '<option>Drop_perc</option>';
												echo '<option>ATT</option>';
												echo '<option>MOU</option>';
												break;
										}
										?>
									</select>
									<select id="kpic-op1" class="span1">
										<option value="lt"><</option>
										<option value="le"><=</option>
										<option value="gt">></option>
										<option value="ge" selected="selected">>=</option>
										<option value="eq">=</option>
										<option value="ne"><></option>
									</select>
									<input id="kpic-val1" type="text" class="span1" value=".5"/>
									<button id="kpic-submit" class="btn btn-primary" type="button">Submit</button>
									<br> <br>  <br> <br>  <br> <br>

								</div>
							</div>
							</fieldset>
						</form>
					 </div>
                                        <div id="kpic-div2" style="float: right;">
                                                <table id="kpic-list"><tr><td/></tr></table>
                                                <div id="kpic-pager"></div>
                                        </div>
                                </div>

                        </div>



			<div id="kpi-menu">
				<div>
					<div  style="float: left;">
						<form class="form-horizontal well">
							<fieldset>
							<div class="control-group">
								<label class="control-label" for="datepicker-control">Select Date</label>
								<div class="controls">
									<div class="input-append date" id="kpi-datepicker-control" data-date-format="mm/dd/yyyy">
										<input id="kpi-datepicker" class="span2" size="16" type="text" value="<?php echo date('m/d/Y', strtotime("-2 day")); ?>">
										<span class="add-on"><i class="icon-th"></i></span>
									</div>
									<span class="help-inline">Tech</span>
									<select id="tech-combo" class="span2">
										<?php switch($this->data['user']['client']) {
											case 'T-Mobile':
                                                    echo '<option>GSM</option>';
                                                    echo '<option>LTE</option>';
													echo '<option selected="selected">UMTS</option>';
													break;
											case 'Sprint-DR':
													echo '<option>EVDO</option>';
													echo '<option>LTE</option>';
													echo '<option selected="selected">CDMA</option>';
													break;
											default:
													echo '<option>cdma</option>';
                                                    echo '<option>evdo</option>';
                                                    break;
										}
										?>
									</select>

								</div>
							</div>
							<div class="control-group">
								<label class="control-label">Time Window</label>
								<div class="controls">
									<select id="tw-combo" class="span2">
										<option>Day</option>
										<option>Week</option>
										<option>Month</option>
									</select>

								</div>
							</div>
							<!-- KPI 1-->
							<div class="control-group">
								<label class="control-label">KPI Band 1</label>
								<div class="controls">
									<select id="kpia1" class="span2" style="width:230px;">
										<?php switch($this->data['user']['client']) {
											 case 'T-Mobile':

												echo '<option value="Voice_AFR">Voice AFR(%)</option>';
                                                echo '<option selected="selected" value="Voice_DR">Voice DCR(%)</option>';
                                                echo '<option value="PS_AFR">PS AFR(%)</option>';
                                                echo '<option value="PS_RAB_DR">PS DCR(%)</option>';
                                                echo '<option value="HSDPA_PS_AFR">HSDPA AFR(%)</option>';
                                                echo '<option value="HSDPA_PS_DCR">HSDPA DCR(%)</option>';
												break;
											case 'Sprint-DR':
											break;
											 default:
												echo '<option>Blocks</option>';
												echo '<option selected="selected">Block_perc</option>';
												echo '<option>Drops</option>';
												echo '<option>Drop_perc</option>';
												echo '<option>ATT</option>';
												echo '<option>MOU</option>';
												break;
										}
										?>
									</select>
									<select id="op1" class="span1">
										<option value="lt"><</option>
										<option value="le"><=</option>
										<option value="gt">></option>
										<option value="ge" selected="selected">>=</option>
										<option value="eq">=</option>
										<option value="ne"><></option>
									</select>
									<input id="val1" type="text" class="span1" value="0.5"/>
								</div>
							</div>
							<!-- KPI 2-->
							<div class="control-group">
								<label class="control-label">KPI Band 2</label>
								<div class="controls">
									 <select id="kpia2" class="span2" style="width:230px;">
                                        <?php 
                                        	switch($this->data['user']['client']) {
                                                 case 'T-Mobile':
                                                        echo '<option selected="selected" value="Voice_AFR">Voice AFR(%)</option>';
                                                        echo '<option value="Voice_DR">Voice DCR(%)</option>';
                                                        echo '<option value="PS_AFR">PS AFR(%)</option>';
                                                        echo '<option value="PS_RAB_DR">PS DCR(%)</option>';
                                                        echo '<option value="HSDPA_PS_AFR">HSDPA AFR(%)</option>';
                                                        echo '<option value="HSDPA_PS_DCR">HSDPA DCR(%)</option>';
                                                        break;
												case 'Sprint-DR':
												break;
                                                 default:
                                                        echo '<option>Blocks</option>';
                                                        echo '<option>Block_perc</option>';
                                                        echo '<option>Drops</option>';
                                                        echo '<option selected="selected">Drop_perc</option>';
                                                        echo '<option>ATT</option>';
                                                        echo '<option>MOU</option>';
                                                        break;
                                        	}
                                        ?>
                                        </select>

									<select id="op2" class="span1">
										<option value="lt"><</option>
										<option value="le"><=</option>
										<option value="gt">></option>
										<option value="ge" selected="selected">>=</option>
										<option value="eq">=</option>
										<option value="ne"><></option>
									</select>
									<input id="val2" type="text" class="span1" value="0.5"/>
								</div>
							</div>
							<!-- KPI 3-->
							<div class="control-group">
								<label class="control-label">KPI Band 3</label>
								<div class="controls">
									 <select id="kpia3" class="span2" style="width:230px;">
                                        <?php 
                                        	switch($this->data['user']['client']) {
                                                 case 'T-Mobile':
                                                        echo '<option value="Voice_AFR">Voice AFR(%)</option>';
                                                        echo '<option value="Voice_DR">Voice DCR(%)</option>';
                                                        echo '<option value="PS_AFR">PS AFR(%)</option>';
                                                        echo '<option value="PS_RAB_DR">PS DCR(%)</option>';
                                                        echo '<option selected="selected" value="HSDPA_PS_AFR">HSDPA AFR(%)</option>';
                                                        echo '<option value="HSDPA_PS_DCR">HSDPA DCR(%)</option>';
                                                        break;
												case 'Sprint-DR':
												break;
                                                 default:
                                                        echo '<option>Blocks</option>';
                                                        echo '<option >Block_perc</option>';
                                                        echo '<option>Drops</option>';
                                                        echo '<option>Drop_perc</option>';
                                                        echo '<option>ATT</option>';
                                                        echo '<option selected="selected">MOU</option>';
                                                        break;
                                        	}
                                        ?>
                                        </select>

									<select id="op3" class="span1">
										<option value="lt"><</option>
										<option value="le"><=</option>
										<option value="gt">></option>
										<option value="ge"selected="selected">>=</option>
										<option value="eq">=</option>
										<option value="ne"><></option>
									</select>
									<input id="val3" type="text" class="span1" value="0.5"/>
									<button id="kpi-submit" class="btn btn-primary" type="button">Submit</button>
								</div>
							</div>
							</fieldset>
						</form>
					</div>
					<div id="div2" style="float: right;">
						<table id="kpi-list"><tr><td/></tr></table>
						<div id="kpi-pager"></div>
					</div>
				</div>
			</div>
			<!-- start of drive test tab -->
			<div id="drive-test-menu">

				<?php

				$pn_params = "";

				$years ="";//<option value=''>Select Year</option>";
				$locations ="";//<option value=''>Select Datasource</option>";
				if($this->data['dtest_year']){
					foreach ($this->data['dtest_year'] as $dyear) {
						$years .= '<option value="'.$dyear['year'].'">'.$dyear['year'].'</option>';
						}
					}
				if($this->data['dtest_location']){
					foreach ($this->data['dtest_location'] as $dloc) {
						$locations .= '<option value="'.$dloc['data_source'].'">'.$dloc['data_source'].'</option>';
					}
				}
				$params = "";
				if($this->data['dtest_params']){
					foreach ($this->data['dtest_params'] as $dp) {
						$params =$dp;
					}
					$par_array = explode(',',$params['query_params_values']);
					foreach ($par_array as $p){
						$pn_params	.= "<option>".$p."</option>";
						}
				}

			?>
			<script language="javascript">
//				var ds_params = array(<?php //print_r($params);?>);
			</script>
				<div>
					<div  style="float: left;">
						<form class="form-horizontal well" style="margin-bottom:50px;">
							<fieldset>
							<div class="control-group">
								<label class="control-label">Select Year</label>
								<div class="controls">
									<select id="enod-year" class="span2">
										<?php echo $years;?>
									</select>
									&nbsp;&nbsp;&nbsp;&nbsp;Select Datasource&nbsp;&nbsp;
									<select id="enod-dataset" class="span2">
										<?php echo $locations;?>
									</select>
                                    <div id="driveTestCheckDiv" name="driveTestCheckDiv" style="float: right; display:none;"><input id="driveTestCheck" name="driveTestCheck" type="checkbox" >&nbsp;Clear</div>
								</div>
							</div>
							<div class="control-group">
								<label class="control-label">Select Data Type</label>
								<div class="controls">
									<select id="enod-datatype" class="span2">
										<?php echo $pn_params;?>
									</select>
									<input id="enod-pn" class="span2" type="text" value="" placeholder="PN" data-placement="right" data-original-title="Separate multiple values by comma">
								</div>
							</div>
							<div class="control-group">
								<label class="control-label">Number of KPIs</label>
								<div class="controls">
									<select id="enod-kpi-count" class="span2">
										<option>1</option>
										<option>2</option>
										<option>3</option>
										<option>4</option>
										<option SELECTED>5</option>
									</select>
								</div>
							</div>
							<div class="control-group">
								<label class="control-label">KPI Band</label>
								<div class="controls">
									<div class="input-append color" data-color="rgb(255,0,0)" data-color-format="rgb" id = "tdcp0">
										<input type="hidden" value="rgb(255,0,0)" id="tcpv0">
										<span class="add-on"><i style="background-color: rgb(255,0,0)"></i></span>
										</div>

										<input id="td1" type="text" class="span1" value="-107.6"/>
										<div class="input-append color" data-color="rgb(255, 180,60)" data-color-format="rgb" id = "tdcp1">
										<input type="hidden" value="rgb(255, 180,60)" id="tcpv1">
										<span class="add-on"><i style="background-color: rgb(255, 180,60)"></i></span>
										</div>

										<input id="td2" type="text" class="span1" value="-99"/>

										<div class="input-append color" data-color="rgb(255, 255, 0)" data-color-format="rgb" id = "tdcp2">
										<input type="hidden" value="rgb(255, 255, 0)" id="tcpv2">
										<span class="add-on"><i style="background-color: rgb(255, 255, 0)"></i></span>
										</div>

										<input id="td3" type="text" class="span1" value="-91.9"/>

										<div class="input-append color" data-color="rgb(0,128,0)" data-color-format="rgb" id = "tdcp3">
										<input type="hidden" value="rgb(0,128,0)" id="tcpv3">
										<span class="add-on"><i style="background-color: rgb(0,128,0)"></i></span>
										</div>

										<input id="td4" type="text" class="span1" value="-81.9"/>

										<div class="input-append color" data-color="rgb(0,220,220)" data-color-format="rgb" id = "tdcp4">
										<input type="hidden" value="rgb(0,220,220)" id="tcpv4">
										<span class="add-on"><i style="background-color: rgb(0,220,220)"></i></span>
										</div>

										<input id="td5" type="text" class="span1" value="-76.9"/>

										<div class="input-append color" data-color="rgb(0,0,255)" data-color-format="rgb" id = "tdcp5">
										<input type="hidden" value="rgb(0,0,255)" id="tcpv5">
										<span class="add-on"><i style="background-color: rgb(0,0,255)"></i></span>
										</div>
									<!--  <input id="td6" type="text" class="span1" value="0"/>

									<div class="input-append color" data-color="rgb(1, 255, 1)" data-color-format="rgb" id = "tdcp6">
									  <input type="hidden" value="rgb(1, 255, 1)" id="tcpv6">
									  <span class="add-on"><i style="background-color: rgb(1, 255, 1)"></i></span>
									</div>
									-->

								</div>
								<br/>
								<button id="enod-submit" class="btn btn-primary pull-right" type="button">Update</button>
								<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
							</div>
							</div>
							</fieldset>
						</form>
					 </div>
					<div id="drive-test-div2" style="float: right;">
						<table id="drive-test-list"><tr><td/></tr></table>
						<div id="drive-test-pager"></div>
					</div>
				</div>

			<!-- end of drive test tab -->
			
			<div id="disaster-feed-menu">
			    <div class="item_bottom" id="disaster-feed-controls">
					<img class="icon_left" src="http://evas.ericsson.net/icons/rss_icon.png"/>
					<label class="item_left">Categories : </label>
					<select class="item_left_sub">
					  <option value="0">All</option>
					  <?php
						  foreach($this->data['disaster_categories'] as $disaster_category)
						  {
							  $disaster_category_id = $disaster_category->id;
							  $disaster_category_name = $disaster_category->name;
							  echo "<option value=\"$disaster_category_id\">$disaster_category_name</option>";
						  }
					  ?>
					</select>
				</div>
				<div class="item_bottom">
					<ul id="disaster-feed-list" class="nav nav-list">
					<?php
					    $i = 1;
						foreach($this->data['disasters'] as $disaster){
						        $disaster_name = $disaster->name;
								$feed_url = $disaster->url;
								$disaster_id = $disaster->id;
								$disaster_icon = $disaster->icon;
								$feed_type = $disaster->type;
								$geom_type = $disaster->geometry;
								echo "<li class=\"disasterFeed\" name=\"$disaster_name\" geom=\"$geom_type\" icon=\"$disaster_icon\" type=\"$feed_type\" url=\"$feed_url\"><a href=\"#\">$i. $disaster_name</a></li>";
								$i = $i + 1;
						}
					?>
					</ul>
				</div>
			</div>
           <div id="field-tech-menu">
					<div id="radio_fieldtech_div_controls" style="width:500px;float:left">
						<input type="radio" id="noneToggle_fieldtech" name="radio" checked="checked" /><label for="noneToggle_fieldtech">None</label>
						<input type="radio" id="pointToggle_fieldtech" name="radio" /><label for="pointToggle_fieldtech">Add Point</label>
						<input type="radio" id="lineToggle_fieldtech" name="radio" /><label for="lineToggle_fieldtech">Add Line</label>
						<input type="radio" id="polygonToggle_fieldtech" name="radio" /><label for="polygonToggle_fieldtech">Add Polygon</label>
						<input type="radio" id="modifyToggle_fieldtech" name="radio" /><label for="modifyToggle_fieldtech">Edit Input</label>
					</div>
				<div class="fieldtech_div_list" style="float:left;height:100px">
					<table id="list_field_tech"><tr><td/></tr></table>
					<div id="pager_field_tech"></div>
				</div>
		   </div>
			
			
		</div>
	</body>
</html>
