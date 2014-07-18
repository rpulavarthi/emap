<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
        <title>OpenLayers with jQuery Mobile</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
        <meta name="apple-mobile-web-app-capable" content="yes">
		<script src="http://maps.google.com/maps/api/js?sensor=true"></script>
        <link rel="stylesheet" href="global/css/jquery-mobile/themes/default/jquery.mobile-1.1.0.min.css">
		<script src='global/js/jquery-1.7.2.min.js'></script>
        <script src="global/js/jquery.mobile-1.1.0.min.js"></script>
        <link rel="stylesheet" href="global/css/mobile.css" type="text/css">
        <link rel="stylesheet" href="global/css/mobile-jq.css" type="text/css">
        <script src="global/js/OpenLayers.js?mobile"></script>
        <script src="global/js/mobile-base.js"></script>
		 <script src="global/js/mobile-jq.js"></script>
       
    </head>
    <body>
        <h1 id="title">OpenLayers with jQuery Mobile</h1>
        <div id="tags">
          mobile, jquery
        </div>
        <p id="shortdesc">
          Using jQuery Mobile to display an OpenLayers map.
        </p>

        <div data-role="page" id="mappage">
          <div data-role="content">
            <div id="map"></div>
          </div>

          <div data-role="footer">
            <a href="#searchpage" data-icon="search" data-role="button">Search</a>
            <a href="#" id="locate" data-icon="locate" data-role="button">Locate</a>
            <a href="#layerspage" data-icon="layers" data-role="button">Layers</a>
          </div>
          <div id="navigation" data-role="controlgroup" data-type="vertical">
            <a href="#" data-role="button" data-icon="plus" id="plus"
               data-iconpos="notext"></a>
            <a href="#" data-role="button" data-icon="minus" id="minus"
               data-iconpos="notext"></a>
          </div>
        </div>

        <div data-role="page" id="searchpage">
          <div data-role="header">
            <h1>Search</h1>
          </div>
          <div data-role="fieldcontain">
            <input type="search" name="query" id="query"
                   value="" placeholder="Search for places"
                   autocomplete="off"/>
          </div>
          <ul data-role="listview" data-inset="true" id="search_results"></ul> 
        </div>

        <div data-role="page" id="layerspage">
          <div data-role="header">
            <h1>Layers</h1>
          </div>
          <div data-role="content">
            <ul data-role="listview" data-inset="true" data-theme="d" data-dividertheme="c" id="layerslist">
            </ul>
          </div>
        </div>

        <div id="popup" data-role="dialog">
            <div data-position="inline" data-theme="d" data-role="header">
                <h1>Details</h1>
            </div>
            <div data-theme="c" data-role="content">
                <ul id="details-list" data-role="listview">
                </ul>
            </div>
        </div>
    </body>
</html>
