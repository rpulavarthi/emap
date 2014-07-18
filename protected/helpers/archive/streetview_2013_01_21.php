<?php
/*
 * streetview.php *
 * Mark Patterson - Dec 11, 2012
 * (mark@oldensoft.com)
*/
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Map Street View Layer</title>
    <link rel="stylesheet" href="streetview.css">
    <script src="https://maps.google.com/maps/api/js?sensor=false"></script>
    <script>
      function initialize() {
        var theloc = new google.maps.LatLng(<?php echo $_GET['lat'] . ',' . $_GET['lon']; ?>);
        var mapOptions = {
          center: theloc,
          zoom: 14,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        var panoramaOptions = {
          position: theloc,
          pov: {
            heading: 34,
            pitch: 10,
            zoom: 1
          }
        };
        var panorama = new  google.maps.StreetViewPanorama(document.getElementById('iframestreet'),panoramaOptions);
      }
    </script>
  </head>
  <body onload="initialize()">
    <div id="iframestreet"></div>
  </body>
</html>
