<?php

class WMSController extends DooController {

	protected $requestParam;
	protected $sld;
	protected $layerConfig;
	
	public function beforeRun($resource, $action){
		Doo::db()->reconnect('dev_postgis');
	}
	
	function wms(){
	
	//var_dump($this); exit;
		//Start parser Timer
	//	$this->start = Utils::timer();
	//	$this->log = new Logging;
		
		//Request Name
		$request = $_GET['REQUEST'];
		$this->requestParams = array();
		
		//Route Request
		switch($request){
			case 'GetMap': $this->GetMap(); break;
			case 'GetFeatureInfo': $this->GetFeatureInfo(); break;
			case 'GetLegendGraphic': $this->GetLegendGraphic();break;
		}
		
	}

	
	protected function buildGetMapRequestParams(){
		//Request Version
		$this->requestParams['VERSION'] = $_GET['VERSION'];
		
		//only support single layer & style currently
		$this->requestParams['LAYERS'] = explode(',', $_GET['LAYERS']);
		$this->requestParams['STYLES'] = isset($_GET['STYLES']) ? explode(',', $_GET['STYLES']) : null;
		
		//Output format of Map ['image/png' , 'image/gif', 'image/jpeg']
		$this->requestParams['FORMAT'] = $_GET['FORMAT'];
		
		//Width in pixles of map picture
		$this->requestParams['WIDTH'] = (int)$_GET['WIDTH'];
		
		//Height in pixles of map picture
		$this->requestParams['HEIGHT'] = (int)$_GET['HEIGHT'];
	
		//Spatial Reference System
		$this->requestParams['SRS'] = $_GET['SRS'];
		
		//Spatial Reference Identifier
		preg_match('/\d.*/', $_GET['SRS'], $match);
		$this->requestParams['SRID'] = (int)$match[0];
		
		//Bounding box corners(lower left, upper right) in SRS units
		$this->requestParams['BBOX'] = explode(',',$_GET['BBOX']);
		$this->requestParams['BBOX']['minx'] = (float)$this->requestParams['BBOX'][0];
		$this->requestParams['BBOX']['miny'] = (float)$this->requestParams['BBOX'][1];
		$this->requestParams['BBOX']['maxx'] = (float)$this->requestParams['BBOX'][2];
		$this->requestParams['BBOX']['maxy'] = (float)$this->requestParams['BBOX'][3];
		
		//Background transparency of map TRUE|FALSE / optional (Default=FALSE) 
		$this->requestParams['TRANSPARENT'] = isset($_GET['TRANSPARENT']) ? $_GET['TRANSPARENT'] : 'FALSE';
		
		//ViewParams - use for parameterized sql and themiatic mapping / optional
		$this->requestParams['VIEWPARAMS'] = isset($_GET['VIEWPARAMS']) ? explode(';',$_GET['VIEWPARAMS']) : null;
	
	}

	protected function GetFeatureInfo(){
		//Request Version
		$this->requestParams['VERSION'] = $_GET['VERSION'];
		
		//Bounding box corners(lower left, upper right) in SRS units
		$this->requestParams['BBOX'] = explode(',',$_GET['BBOX']);
		$this->requestParams['BBOX']['minx'] = (float)$this->requestParams['BBOX'][0];
		$this->requestParams['BBOX']['miny'] = (float)$this->requestParams['BBOX'][1];
		$this->requestParams['BBOX']['maxx'] = (float)$this->requestParams['BBOX'][2];
		$this->requestParams['BBOX']['maxy'] = (float)$this->requestParams['BBOX'][3];
		$this->requestParams['QUERY_LAYERS'] = explode(',', $_GET['QUERY_LAYERS']);
		$this->requestParams['INFO_FORMAT'] = $_GET['INFO_FORMAT'];
		preg_match('/\d.*/', $_GET['SRS'], $match);
		$this->requestParams['SRID'] = (int)$match[0];
		$this->requestParams['FEATURE_COUNT'] = $_GET['FEATURE_COUNT'];
		$this->requestParams['WIDTH'] = (int)$_GET['WIDTH'];
		$this->requestParams['HEIGHT'] = (int)$_GET['HEIGHT'];
		$this->requestParams['X'] = $_GET['X'];
		$this->requestParams['Y'] = $_GET['Y'];
		
		// Get Layer config
		include './protected/config/layers.conf.php';
		//only support single layer currently
		list($group, $layer) = explode( ':' , $this->requestParams['QUERY_LAYERS'][0]);
		$this->layerConfig = $layer_config[$group][$layer];

		list($lat, $lng) = $this->pixels2coords($this->requestParams['X'], $this->requestParams['Y']);
		
		$master_collection = $this->queryInfoGIS($lat, $lng);
		
		// Query Attributes via MySQL (Optional)
		if(isset($this->layerConfig['MySQL'])){
			$attributes = $this->queryMySQLAttributes();
			//match attributes to master_collection and save
			$myGeom = $this->layerConfig['MySQL']['Geom'];
			foreach($attributes as $row){
				$master_collection[$row[$myGeom]]['data'] =  $row;
			}
		}
		
		$this->log->lwrite('end loading queryMySQLAttributes : ' . (float)(Utils::timer() - $this->start) );

		
		// Query Attributes via API (Optional)
		if(isset($this->layerConfig['API'])){
			$attributes = $this->queryAPIAttributes();
			//match attributes to master_collection and save
			$myGeom = $this->layerConfig['API']['Geom'];
			foreach($attributes as $row){
				$master_collection[$row[$myGeom]]['data'] =  $row;
			}
		}
		
		
		$attributes = array();
		foreach($master_collection as $row){
			if(isset($row['geom']))
				$attributes[] = $row['data'];
		}
		
		$this->setContentType('xml');
		echo Utils::toXml($attributes, 'root', 'data');
		
	}
	protected function GetLegendGraphic(){
	
		//Request Version
		$this->requestParams['VERSION'] = $_GET['VERSION'];
		$this->requestParams['FORMAT']	= $_GET['FORMAT'];
		
		//optional
		$this->requestParams['STYLE'] 		= isset($_GET['STYLE']) ? $_GET['STYLE'] : null;
		$this->requestParams['SLD_BODY'] 	= isset($_GET['SLD_BODY']) ? $_GET['SLD_BODY'] : null;
		$this->requestParams['WIDTH'] 		= isset($_GET['WIDTH']) ? (int)$_GET['WIDTH'] : 20;
		$this->requestParams['HEIGHT'] 		= isset($_GET['HEIGHT']) ? (int)$_GET['HEIGHT'] : 20;
		
		//Load SLD
		$this->loadSLD($this->requestParams['STYLE'], array($this->requestParams['STYLE']));
		
		$im = $this->renderLegendImage();

		// Output according to requested format
		switch($this->requestParams['FORMAT']){
			case 'image/png':
				$this->setContentType('png');
				imagepng($im);
			break;
			case 'image/jpeg':
				$this->setContentType('jpg');
				imagejpeg($im);
			break;
			case 'image/gif':
				$this->setContentType('gif');
				imagegif($im);
			break;
		}
		
		// Destroy
        imagedestroy($im);
		
		
	}	
	
	
	protected function GetMap(){
	
		//get params
		$this->buildGetMapRequestParams();
		
		// Get Layer config
		include './protected/config/layers.conf.php';
		//only support single layer currently
		list($group, $layer) = explode( ':' , $this->requestParams['LAYERS'][0]);
		$this->layerConfig = $layer_config[$group][$layer];
		
		
		// Load Style SLD
		$this->loadSLD($this->requestParams['STYLES']);
		
		$this->log->lwrite('end loading SLD : ' . (float)(Utils::timer() - $this->start) );
		
		// Query GIS Data
		$master_collection = $this->queryGIS();
		
		$this->log->lwrite('end loading queryGIS : ' . (float)(Utils::timer() - $this->start) );

		
		// Query Attributes via MySQL (Optional)
		if(isset($this->layerConfig['MySQL'])){
			$attributes = $this->queryMySQLAttributes();
			//match attributes to master_collection and save
			$myGeom = $this->layerConfig['MySQL']['Geom'];
			foreach($attributes as $row){
				$master_collection[$row[$myGeom]]['data'] =  $row;
			}
		}
		
		$this->log->lwrite('end loading queryMySQLAttributes : ' . (float)(Utils::timer() - $this->start) );

		
		// Query Attributes via API (Optional)
		if(isset($this->layerConfig['API'])){
			$attributes = $this->queryAPIAttributes();
			//match attributes to master_collection and save
			$myGeom = $this->layerConfig['API']['Geom'];
			foreach($attributes as $row){
				$master_collection[$row[$myGeom]]['data'] =  $row;
			}
		}
		
		// Use GD to Render Image from Master Data 
		$im = $this->renderMapImage($master_collection);
	
		$this->log->lwrite('end loading renderImage : ' . (float)(Utils::timer() - $this->start) );
	
		// Output according to requested format
		switch($this->requestParams['FORMAT']){
			case 'image/png':
				$this->setContentType('png');
				imagepng($im);
			break;
			case 'image/jpeg':
				$this->setContentType('jpg');
				imagejpeg($im);
			break;
			case 'image/gif':
				$this->setContentType('gif');
				imagegif($im);
			break;
		}
		
		// Destroy
        imagedestroy($im);
	}
	
	protected function renderLegendImage(){
	
		//init vars
		$font = Doo::conf()->SITE_PATH . Doo::conf()->PROTECTED_FOLDER  . 'class/fonts/cour.ttf';
		//var_dump($font); exit;
		$count = count($this->sld['sld:StyledLayerDescriptor']['sld:NamedLayer']['sld:UserStyle']['sld:FeatureTypeStyle']['sld:Rule']);
		$height = $this->requestParams['HEIGHT'];
		$width = $this->requestParams['WIDTH'];
		
		$width = 230;
		$height = 26;
		
		// create image
		$im = imagecreatetruecolor($width, $height * ($count + 1));
		
		//black border
		imagefilledrectangle($im, 0, 0, $width, $height * ($count + 1), imagecolorallocate($im, 255, 255, 255));
		//white background
		imagerectangle($im, 0, 0, $width - 1, $height * ($count + 1) - 1, imagecolorallocate($im, 0, 0, 0));

		// Legend Title
		imagettftext($im, 11, 0, 10, 20, imagecolorallocate($im, 0, 0, 0), $font, "Legend - " . $this->sld['sld:StyledLayerDescriptor']['sld:NamedLayer']['sld:Name']);
		
		
		//Build Legend Body
		foreach ($this->sld['sld:StyledLayerDescriptor']['sld:NamedLayer']['sld:UserStyle']['sld:FeatureTypeStyle']['sld:Rule'] as $key => $rule) 
		{
		
			//Get Symbolizer 
			$syms[] = isset($rule['sld:PolygonSymbolizer']) ? SLD::polygonSymbolizer($rule['sld:PolygonSymbolizer']) : null;
			$syms[] = isset($rule['sld:PointSymbolizer']) ? SLD::pointSymbolizer($rule['sld:PointSymbolizer']) : null;
			

			foreach($syms as $sym){
			
				if(!isset($sym))
					continue;
				
				list($r, $g, $b ) = $this->rgb2array($sym->fill);
			
				//color rectangle 
				imagefilledrectangle($im, 10, $height * ($key + 1), $this->requestParams['WIDTH'] + 10, ($height * ($key + 1)) + 15,  imagecolorallocate($im, $r, $g, $b));
				imagerectangle($im, 10, $height * ($key + 1), $this->requestParams['WIDTH'] + 10, ($height * ($key + 1)) + 15,  imagecolorallocate($im, 0, 0, 0));
			
			}
		
			//set legend category name
			imagettftext($im, 10, 0, 80,  ($height * ($key + 1)) + 12, imagecolorallocate($im, 0, 0, 0), $font, $rule['sld:Title']);
		}
		
		return $im;
	
	}
	protected function renderMapImage($master_collection){
	
		//Initalize Image
		$im = imagecreatetruecolor($this->requestParams['WIDTH'], $this->requestParams['HEIGHT']);
		$blank = imagecolorallocate( $im, 255, 255, 254 );
		imagefilledrectangle( $im, 0, 0, $this->requestParams['WIDTH'], $this->requestParams['HEIGHT'], $blank );
		
		//Loop though master_collection
		foreach($master_collection as $r){
		
			if(!isset($r["geom"])) break;
			
			$data = $r["data"];
			$geometry_collection = $r["geom"];
			$geom_type = get_class($geometry_collection);
			
			//Build Array of pointlist from Coords
			$pointlist = array();
			if(preg_match('/Multi/', $geom_type)){
				foreach($geometry_collection->getComponents() as $component){
				
					$linear_ring = $component->getComponents();
					$coords = $linear_ring[0]->getCoordinates();
					$pointlist  = $this->coords2pixels($coords);
				
				}
			}else{
			//TODO
			}
			
			$symbolizer = SLD::getStyleSymbolizer($this->sld, $data, $geom_type);
				
			$this->log->lwrite('end loading got Style Symbolizer : ' . (float)(Utils::timer() - $this->start) );
					
					
			switch(get_class($symbolizer)){
				case 'polygonSymbolizer':
					$this->generatePolygon($im, $pointlist, $symbolizer);
				break;
				case 'lineSymbolizer';
					$this->generateLine($im, $pointlist, $symbolizer);
				break;
				case 'pointSymbolizer';
					$this->generatePoint($im, $pointlist, $symbolizer);
				break;
			}
			
			$this->log->lwrite('end loading render poly : ' . (float)(Utils::timer() - $this->start) );

		}

		// Add transparency
		if($this->requestParams['TRANSPARENT'] == 'TRUE') 
			imagecolortransparent($im, $blank);
			
		return $im;
	
	}
	protected function generatePolygon(&$im, $pointlist, $symbolizer){

		//allocate fill
		list($r, $g, $b ) = $this->rgb2array($symbolizer->fill);
		$fill = imagecolorallocatealpha($im, $r, $g, $b, $this->opacity2transparency($symbolizer->fill_opacity));	

		//allocate border
		list($r, $g, $b ) = $this->rgb2array($symbolizer->stroke);
		$stroke = imagecolorallocatealpha($im, $r, $g, $b, $this->opacity2transparency($symbolizer->stroke_opacity));	

		//Polygon
		// Need at least 3 points to plot a polygon
		if(count($pointlist) > 6){
			//fill
			imagefilledpolygon($im, $pointlist, count($pointlist)/2, $fill);
			//border
			imagepolygon($im, $pointlist, count($pointlist)/2, $stroke);
			imagesetthickness($im, $symbolizer->stroke_width);
		}

	}
	protected function opacity2transparency($opacity){
		return floor((1-$opacity) * 127);
	}
	protected function color2rgb($color) {
		return str_pad(base_convert($color, 10, 16), 6, 0, STR_PAD_LEFT);
	}
	protected function rgb2array($rgb) {
		//strip # from leading RGB if exist
		$rgb = str_replace('#','',$rgb);
		return array(
			base_convert(substr($rgb, 0, 2), 16, 10),
			base_convert(substr($rgb, 2, 2), 16, 10),
			base_convert(substr($rgb, 4, 2), 16, 10),
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
	protected function pixels2coords($x, $y){
		//set variables
		$minx = $this->requestParams['BBOX']['minx'];
		$miny = $this->requestParams['BBOX']['miny']; 
		$maxx = $this->requestParams['BBOX']['maxx'];
		$maxy = $this->requestParams['BBOX']['maxy'];
		
		$height = $this->requestParams['HEIGHT'];
		$width = $this->requestParams['WIDTH'];
		
		//calc lng coord
		$lngSpan = $maxx - $minx;
		$pixelsPerDegLng = abs($width/$lngSpan);	
		$lng = ($x / $pixelsPerDegLng) + $minx;
		
		//calc lat coord
		$latSpan = $maxy-$miny;
		$pixelsPerDegLat = abs($height/$latSpan);	
		$lat = (($height - $y)/$pixelsPerDegLat) + $miny;
		
		return array( $lat, $lng);

	}
	protected function coords2pixels($coords){
		//set variables
		$minx = $this->requestParams['BBOX']['minx'];
		$miny = $this->requestParams['BBOX']['miny']; 
		$maxx = $this->requestParams['BBOX']['maxx'];
		$maxy = $this->requestParams['BBOX']['maxy'];
		
		$height = $this->requestParams['HEIGHT'];
		$width = $this->requestParams['WIDTH'];
	
		$pointlist= array();
		
		foreach($coords as $c){
		
			$lng = (float)$c[0];
			$lat = (float)$c[1];
			
			//calc x pixel
			$lngSpan = $maxx - $minx;
			$pixelsPerDegLng = abs($width/$lngSpan);	
			$x = ceil(( $lng - $minx  )* $pixelsPerDegLng);
			
			//calc y pixel
			$latSpan = $maxy-$miny;
			$pixelsPerDegLat = abs($height/$latSpan);	
			$y = ceil(($lat-$miny) * $pixelsPerDegLat);
			$y = $height - $y;

			//save xy
			$pointlist[] = (int)$x;
			$pointlist[] = (int)$y;
		}
		
		return $pointlist;
	}
	
	//
	// Query PostGIS Data
	//
	protected function queryGIS(){
	
		$pgDB = new DooSqlMagic;
		$pgDB->setDb($this->dbconfig, $this->layerConfig['GIS']['DB_Config']);
		$pgDB->connect();
		$pgPK = $this->layerConfig['GIS']['PK'];
		$pgGeom = $this->layerConfig['GIS']['Geom'];
		$pgSelect = $this->layerConfig['GIS']['Select'];
		$srid = $this->requestParams['SRID'];
		$minx = $this->requestParams['BBOX']['minx'];
		$miny = $this->requestParams['BBOX']['miny']; 
		$maxx = $this->requestParams['BBOX']['maxx'];
		$maxy = $this->requestParams['BBOX']['maxy'];
		
		//fetch items inside bounding box
		$sql = "SELECT $pgPK, ST_AsText(ST_Transform($pgGeom, $srid)) as Coords from ($pgSelect) t1 WHERE ST_Transform($pgGeom, $srid) && SetSRID('BOX( $minx $miny, $maxx $maxy)'::box2d, $srid)";
		
		$data = $pgDB->fetchAll($sql);
		
		$this->log->lwrite('end loading queryGIS fetchALL : ' . (float)(Utils::timer() - $this->start) );
		
		//parse Well Known Text
		$p = new WKT();
			
		//build master collection
		$master_collection = array();
		
		foreach($data as $r){
		
			if(!$geom = $this->cache->get($r[$pgPK])){
				$geom = $p->read($r["coords"]);
				$this->cache->set($r[$pgPK], $geom, 86400 );
			}
		
			$master_collection[$r[$pgPK]] = array('data'=> array(), 'geom'=> $geom);
			
			
			
			//$master_collection[$r[$pgPK]] = array('data'=> array(), 'geom'=> $p->read($r["coords"]));
		}
		
		$this->log->lwrite('end loading queryGIS master_collection : ' . (float)(Utils::timer() - $this->start) );
		
		return $master_collection;
	}
	
	// 
	// Query MySQL Attributes (must be grouped by $myGeom  field and be contained in select statement)
	//
	protected function queryMySQLAttributes(){

		$myDB = new DooSqlMagic;
		$myDB->setDb($this->dbconfig, $this->layerConfig['MySQL']['DB_Config']);
		$myDB->connect();
		
		$myGeom 	= $this->layerConfig['MySQL']['Geom'];
		$mySelect 	= $this->layerConfig['MySQL']['Select'];
		
		//Add PDO view params
		$params = null;
		if(isset($this->requestParams['VIEWPARAMS'])){
			$params = array();
			foreach($this->requestParams['VIEWPARAMS'] as $param){
				list($param, $value) = explode(':', $param);
				$params[":$param"] = $value;
			}
		}
				
		//fetch attributes
		return $myDB->fetchAll($mySelect, $params);
	}
	
	protected function queryAPIAttributes(){ 
	
	/*TODO Implement + Add Memcache	*/
	
	}
	
	protected function queryInfoGIS($lat, $lng){
	
		$pgDB = new DooSqlMagic;
		$pgDB->setDb($this->dbconfig, $this->layerConfig['GIS']['DB_Config']);
		$pgDB->connect();
		$pgPK = $this->layerConfig['GIS']['PK'];
		$pgGeom = $this->layerConfig['GIS']['Geom'];
		$pgSelect = $this->layerConfig['GIS']['Select'];
		$srid = $this->requestParams['SRID'];
		$minx = $this->requestParams['BBOX']['minx'];
		$miny = $this->requestParams['BBOX']['miny']; 
		$maxx = $this->requestParams['BBOX']['maxx'];
		$maxy = $this->requestParams['BBOX']['maxy'];
		$count = $this->requestParams['FEATURE_COUNT'];
		
		//fetch items inside bounding box
		$sql = "SELECT $pgPK, ST_AsText(ST_Transform($pgGeom, $srid)) as Coords from ($pgSelect) t1 
			WHERE ST_Transform($pgGeom, $srid) && SetSRID('BOX( $minx $miny, $maxx $maxy)'::box2d, $srid)
				AND ST_Intersects(ST_Transform(the_geom, $srid),ST_SetSRID(ST_Point($lng, $lat), $srid)) LIMIT $count";
				
		$data = $pgDB->fetchAll($sql);
		
		//parse Well Known Text
		$p = new WKT();
			
		//build master collection
		$master_collection = array();
		foreach($data as $r){
			$master_collection[$r[$pgPK]] = array('data'=> array(), 'geom'=> $p->read($r["coords"]));
		}
		
		return $master_collection;
	}

	
}
?>