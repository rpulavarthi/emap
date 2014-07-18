<?php

class WMSPieController extends WMSController {

	//override
	function GetMap(){
		
		$this->buildGetMapRequestParams();
		
		// Get Layer config
		include(Doo::conf()->SITE_PATH . Doo::conf()->PROTECTED_FOLDER . 'config/layer.config.php');
		//only support single layer currently
		list($group, $layer) = explode( ':' , $this->requestParams['LAYERS'][0]);
		
		if(!isset($layer_config[$group][$layer])){
			echo 'unable to find layer in config';
			exit;
		}
		
		$this->layerConfig = $layer_config[$group][$layer];
		
		//load stylesa
		$this->loadSLD($this->requestParams['STYLES'], $this->layerConfig['styles']['pie']);
		
		//render pies
		$this->pies();
	}
	
	private function pies(){
	
		//get params
		$srid = $this->requestParams['SRID'];
		$minx = $this->requestParams['BBOX']['minx'];
		$miny = $this->requestParams['BBOX']['miny'];
		$maxx = $this->requestParams['BBOX']['maxx']; 
		$maxy = $this->requestParams['BBOX']['maxy'];
		$width = $this->requestParams['WIDTH'];
		$height = $this->requestParams['HEIGHT'];
		$layer = $this->requestParams['LAYERS'][0];
		

		//derive zoom level from  BBOX and image size
		$ratio = ($this->requestParams['BBOX']['maxx'] - $this->requestParams['BBOX']['minx']) / $this->requestParams['WIDTH'];
		$sql = "SELECT zoom, abs($ratio-scale) as delta, scale FROM resolutions ORDER BY delta ASC LIMIT 1";
		$zoomData = Doo::db()->fetchRow($sql);
		$zoom = $zoomData["zoom"];

		//cluster!
		session_start();
		$cluster_zoom_level = isset($_SESSION['user']['cluster_zoom_level']) ? $_SESSION['user']['cluster_zoom_level'] : 15;
		if($zoom <= $cluster_zoom_level){
		
			//get non cluster sites in current bounding box	
			$sql = "SELECT key FROM clusters 
					WHERE
					zoom = $zoom
					AND layer = '$layer'
					AND size = 1
					AND geom && SetSRID('BOX( $minx $miny, $maxx $maxy)'::box2d, $srid)";
		}else{
			$sql = "SELECT key FROM cells 
					WHERE
					layer = '$layer'
					AND geom && SetSRID('BOX( $minx $miny, $maxx $maxy)'::box2d, $srid)";
		
		}
			
		//get pie list		
		$sql = str_replace('$srid', $srid, str_replace('$sql', $sql, $this->layerConfig['sectorSQL']));	
		$pieData = Doo::db()->fetchAll($sql);

		//init image
		include (Doo::conf()->SITE_PATH . Doo::conf()->PROTECTED_FOLDER . 'class/imageSmoothArc.php');
		$im = imagecreatetruecolor($width, $height);
		imagealphablending($im, true);
		$black = imagecolorallocate($im,0,0,0);
		$green = imagecolorallocate($im,0,128,0);
		$backgrnd = imagecolorallocate($im , 255,255,255);
			
		//fill backgrnd
		imagefill($im,0,0,$backgrnd);
		
		foreach($pieData as $r){
			$key 		= $r['key'];
			$lng 		= $r['longitude'];
			$lat 		= $r['latitude'];
			$azimuth 	= $r['azimuth'];
			$hbw 		= $r['hbw'];

			$start = $azimuth - 90 - $hbw /2;
			$end = $azimuth - 90 + $hbw / 2;
		
			//omni
			if ($hbw == 360) { $start = 0; $end = 360;}
			
			
			//pie center
			list($x, $y) = $this->coords2pixels(array(array($lng,$lat)));			
			
			//radius
			$z = $this->layerConfig['WMS']['pieRadius'];
			

			//style
			$symbolizer = SLD::getStyleSymbolizer($this->sld, $r, 'Polygon');
			
			if($symbolizer == null)
				continue;
		
			$color = $this->rgb2array($symbolizer->fill);
			if(method_exists($layer,'renderPie')){
				//custom render
			} else{
			//	imageSmoothArc ( $im, $x, $y, $z*2.5, $z*2.5,  array($color[0], $color[0], $color[2], 0),deg2rad($start) , deg2rad($end));
				imageSmoothArc ( $im, $x, $y, $z*1.8, $z*1.8,  array($color[1], $color[0], $color[2], 0),deg2rad($start) , deg2rad($end));
				imageSmoothArc ( $im, $x, $y, $z*1.3, $z*1.3,  array($color[0], $color[1], $color[2], 0),deg2rad($start) , deg2rad($end));
			}
			
			//label
			if($zoom > $cluster_zoom_level){
				$font = Doo::conf()->SITE_PATH . Doo::conf()->PROTECTED_FOLDER . 'class/fonts/cour.ttf';
				imagettftext($im, 10, 0, $x - 30, $y + 30, $black, $font, $key);
			}
		}
		

		//set backgrnd transparent
		imagecolortransparent($im, $backgrnd);
		
		//dump to browser
		header('Content-Type: image/png');
		imagepng($im);
		imagedestroy($im);
		
	}
}