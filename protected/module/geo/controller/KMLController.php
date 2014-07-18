<?php

class KMLController extends DooController {

	public function beforeRun($resource, $action){
		Doo::db()->reconnect('dev_postgis');
	}
	function index() {
	
		echo 'You are visiting '.$_SERVER['REQUEST_URI'];
	}
	
	function siteCluster(){
		include(Doo::conf()->SITE_PATH . Doo::conf()->PROTECTED_FOLDER . 'config/layer.config.php');
		
		//load layer
		list($group, $layer) = explode( ':' , $this->params['layer']);
		
		if(!isset($layer_config[$group][$layer])){
			echo 'unable to find layer in config';
			exit;
		}
		$this->layerConfig = $layer_config[$group][$layer];
		
		//load style
		$style = $this->params['style'];
		$this->loadSLD($style, $this->layerConfig['styles']['cell']);

		$bbox = explode(',',$_GET['bbox']);

		$minx = $bbox[0];
		$miny = $bbox[1];
		$maxx = $bbox[2];
		$maxy = $bbox[3];
		
		$zoom = $_GET['zoom'];
		
		$clustersql = '';
		$sitesql = '';
		$clusterdata = array();
		$sitedata = array();
		
		//cluster!
		//check user settings otherwize use default (15)
		session_start();
		$cluster_zoom_level = isset($_SESSION['user']['cluster_zoom_level']) ? $_SESSION['user']['cluster_zoom_level'] : 15;
		if($zoom <= $cluster_zoom_level){
		
			$clustersql = "SELECT latitude, longitude, size FROM clusters WHERE
					zoom = $zoom
					AND size > 1
					AND layer = '".$this->params['layer']. "'
					AND latitude between $miny and $maxy
					AND longitude between $minx and $maxx";
					
			$sitesql = "SELECT key  FROM clusters WHERE
					zoom = $zoom
					AND size = 1
					AND layer = '".$this->params['layer']. "'
					AND latitude between $miny and $maxy
					AND longitude between $minx and $maxx";
		} else {
			//no cluster pull from cells table
			$sitesql = "SELECT key FROM cells WHERE
					layer = '".$this->params['layer']. "'
					AND latitude between $miny and $maxy
					AND longitude between $minx and $maxx";
		}

		if($clustersql != '')
			$clusterdata = Doo::db()->fetchAll($clustersql);

		if($sitesql != ''){
			$srid = 4326;
			//get pie list		
			$sitesql = str_replace('$srid', $srid, str_replace('$sql', $sitesql, $this->layerConfig['sectorSQL']));		
			$sitedata = Doo::db()->fetchAll($sitesql);
		}	
	
		//KML header	
		$kml = array('<?xml version="1.0" encoding="UTF-8"?>');
		$kml[] = '<kml xmlns="http://earth.google.com/kml/2.2">';
		$kml[] = '	<Document >';
	
		foreach($clusterdata as $r){
			$lat = $r['latitude'];
			$lon = $r['longitude'];
			$size = $r['size'];
			
			//add cluster size SLD
			$color = 'FF0000';
			
			if($size > 1 && $size <= 5)
				$color = '00FF00';
			
			if($size > 5 && $size <= 15)
				$color = '33FF00';
				
			if($size > 15 && $size <= 30)
				$color = 'CCFF33';	
			
			if($size > 30 && $size <= 60)
				$color = 'FFA500';	
			
			if($size > 60 && $size <= 120)
				$color = 'FF4500';	
			
			if($size > 120)
				$color = 'FF0000';	
				

			$kml[] = '		<Placemark>';
			$kml[] = '		<Style >';
			$kml[] = '			<IconStyle>';
			$kml[] = '				<Icon>';
			$kml[] = '					<href><![CDATA[http://chart.apis.google.com/chart?cht=it&chs=32x32&chco='.$color.',000000ff,ffffff01&chl=' . $size . '&chx=' . ($color == 'CCFF33' ? '000000': 'FFFFFF') . ',0&chf=bg,s,00000000&ext=.png]]></href>';
			$kml[] = '					<scale>.75</scale>';
			$kml[] = '				</Icon>';
			$kml[] = '			</IconStyle>';
			$kml[] = '		</Style>';
			$kml[] = '			<Point>';
			$kml[] = '				<coordinates>' .$lon.', ' . $lat . ',0</coordinates>';
			$kml[] = '			</Point>';
			$kml[] = '		</Placemark>';
		}

		$attributes = array();
		foreach($sitedata as $key=>$r){

			if( $key > count($sitedata)-2 || ($sitedata[$key]['key'] != $sitedata[$key+1]['key'] )){
			
				$attributes[] = $r;
				$lat = $r['latitude'];
				$lon = $r['longitude'];
				$key = $r['key'];
			
				$kml[] = '		<Placemark>';
				$kml[] = ' 			<name>'. $key . '</name>';
				$kml[] = ' 			<description>'; 
				$kml[] = ' 				<![CDATA[' . $layer::buildCellSectorDescription( $attributes) . ']]>';
				$kml[] = ' 			</description>';
				$kml[] = '		<Style >';
				
				
				$sym = SLD::getStyleSymbolizer($this->sld, $r, 'Point');

				if(isset($sym)){
					$kml[] = '			<IconStyle>';
					$kml[] = '				<Icon>';
					$kml[] = '					<href><![CDATA[http://'.$_SERVER["SERVER_ADDR"] .'/emap/geo/kml/site/icon/' . str_replace('#','',$sym->fill) .'/' . $sym->size . ']]></href>';			
					$kml[] = '					<scale>.40</scale>';	
					$kml[] = '				</Icon>';
					$kml[] = '			</IconStyle>';
				}
				$kml[] = '		</Style>';
				$kml[] = '			<Point>';
				$kml[] = '				<coordinates>' .$lon.', ' . $lat . ',0</coordinates>';
				$kml[] = '			</Point>';
				$kml[] = '		</Placemark>';
			
				//reset
				$attributes = array();
			
			}else{
			  $attributes[] = $r;
			}
			
		}
		
		//Close Tags
		$kml[] = '	</Document>';
		$kml[] = '</kml>';
		
		//Output
		//Prevent Cache
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		header("Content-Type: application/vnd.google-earth.kml+xml");
		header("Access-Control-Allow-Origin: *");
		echo join("\n", $kml);

	}
	
	function siteIcon(){
		//RGBA
		$color = $this->params['color'];
		$rgbaArray = $this->rgba2array($color);
		
		//size
		$size = $this->params['size'];
		$width = $size;
		$height =  $size;
	
		include (Doo::conf()->SITE_PATH . Doo::conf()->PROTECTED_FOLDER . 'class/imageSmoothArc.php');
		$im = imagecreatetruecolor($width, $height);
		imagealphablending($im, true);
		$black = imagecolorallocate($im,0,0,0);
		$green = imagecolorallocate($im,0,128,0);
		$backgrnd = imagecolorallocate($im , 255,255,255);
		//fill backgrnd
		imagefill($im,0,0,$backgrnd);

		//create circle
		imageSmoothArc ( $im, ($width/2)-2 , ($height/2)-1, $width-3, $height-3, $rgbaArray, deg2rad(0) , deg2rad(360));
		
		//set backgrnd transparent
		imagecolortransparent($im, $backgrnd);
		
		//dump to browser
		header('Content-Type: image/png');
		imagepng($im);
		imagedestroy($im);
	}
	
	protected function rgba2array($rgba) {
		//strip # from leading RGB if exist
		$rgba = str_replace('#','',$rgba);
		return array(
			base_convert(substr($rgba, 0, 2), 16, 10),
			base_convert(substr($rgba, 2, 2), 16, 10),
			base_convert(substr($rgba, 4, 2), 16, 10),
			base_convert(substr($rgba, 6, 2), 16, 10),
		);
	}
	
	
	protected function loadSLD($style, $styleList){
		// init
		$sld = '';

		if(is_array($style))
			$style = $style[0];
			
		// Check if style is defined
		if(!isset($style) || $style == ""){
			// Use default style
			$sld = $styleList[0];
		}else{
			// check if requsted style is available
			if(isset( $styleList) && !in_array($style, $styleList)){
				// Error!
				echo 'Invalid Style'; exit;
			}else{
				$sld = $style;
			}
		}
	
		// Load SLD (XML) to array
		$this->sld = Utils::xml2array(file_get_contents(Doo::conf()->SITE_PATH . Doo::conf()->PROTECTED_FOLDER . "config/style/$sld.sld"));
		
	}
}
?>