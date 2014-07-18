<?php

class UtilityController extends DooController {

	function __construct(){
		set_time_limit(99999999);
		ini_set('memory_limit', '-1');
		
		$this->schema = 'gis_schema';
	
	}
	function cell(){
		$schema = $this->schema;
		$group =  $this->params['group'];
		$layer = $this->params['layer'];
		
		include(Doo::conf()->SITE_PATH . Doo::conf()->PROTECTED_FOLDER . 'config/layer.config.php');

		if(!isset($layer_config[$group][$layer])){
			echo 'unable to find layer in config';
			exit;
		}
		
		//remove old data
		Doo::db()->query("DELETE from $schema.cells where layer = '$group:$layer'");
			
		
		//No clustersing.. Insert directly into db
		Doo::db()->beginTransaction();
		$sql = $layer_config[$group][$layer]['cellSQL'];
		$res = Doo::db()->fetchAll($sql);
		
		foreach($res as $r){
			$lon = $r["lon"];
			$lat = $r["lat"];
			$key = $r["key"];
			$switch_cell = $r["switch_id"] . "_" . $r["cell_id"] ;
			
			$sql = "INSERT INTO $schema.cells(latitude, longitude,  layer, key, switch_cell, geom)
					VALUES ( $lat, $lon,  '$group:$layer', '$key', '$switch_cell', ST_Transform(ST_SetSRID(ST_MakePoint($lon, $lat),4326) ,900913))";
			Doo::db()->query($sql);
		}
		
		Doo::db()->commit();
		echo 'done';
	}

	function cluster() {
		$schema = $this->schema;
		$group =  $this->params['group'];
		$layer = $this->params['layer'];
	
		include(Doo::conf()->SITE_PATH . Doo::conf()->PROTECTED_FOLDER . 'config/layer.config.php');
		
		if(!isset($layer_config[$group][$layer])){
			echo 'unable to find layer in config';
			exit;
		}
			
		//remove old data
		Doo::db()->query("DELETE from $schema.clusters where layer = '$group:$layer'");
		
		$cellSQL = $layer_config[$group][$layer]['cellSQL'];
		
		$sql = "select zoom, scale from $schema.resolutions where zoom <= 15";	
		$zooms = Doo::db()->fetchAll($sql);
		
		//loop through each zoom level
		foreach($zooms as $z){
		
			$zoom = $z["zoom"];
			$scale = $z["scale"];
			
			//create temp table of markers
			$tempTable = "tmpMarkers";
			Doo::db()->query("DROP TABLE IF EXISTS $tempTable");
			Doo::db()->query("CREATE TEMPORARY TABLE $tempTable
				AS
				$cellSQL"
			);
			
			//add index
			Doo::db()->query("CREATE INDEX geom_idx ON $tempTable USING GIST ( geom )");
		
			//build cluster
			$clustered = $this->clusterMarkers($tempTable, 40, $scale);
		
			//import into database
			Doo::db()->beginTransaction();
			foreach($clustered as $r){
			
				$size = count($r);
				$lngsum = 0;
				$latsum = 0;
				
				foreach($r as $rr){
				
					$latsum += $rr['lat'];
					$lngsum += $rr['lon'];
					
				}
				
				$lon = $lngsum/$size;
				$lat = $latsum/$size;
				$key = ($size == 1) ? "'". $r[0]["key"] ."'": 'null';

				$sql = "INSERT INTO $schema.clusters(zoom, latitude, longitude, size, layer, key, geom)
					VALUES ( $zoom, $lat, $lon, $size, '$group:$layer', $key, ST_Transform(ST_SetSRID(ST_MakePoint($lon, $lat),4326) ,900913))";
				Doo::db()->query($sql);
			
			}
			Doo::db()->commit();
		}
		
		echo 'done';
	}
	
	
	function clusterMarkers($tempTable, $pixelDistance, $scale){
		$schema = $this->schema;
		$clustered = array();
		
		//get marker count
		$res = Doo::db()->fetchRow("SELECT count(*) as count from $tempTable");
		$count = $res["count"];
		
		while ($count) {
		
			//pick top marker
			$marker = Doo::db()->fetchRow("SELECT * FROM $tempTable LIMIT 1");
			
			$latitude = $marker["lat"];
			$longitude = $marker["lon"];
			
			//cluster
			$res = Doo::db()->fetchAll("SELECT key, lat, lon FROM $tempTable  WHERE ST_DWithin( geom, ST_Transform(ST_SetSRID(ST_MakePoint($longitude, $latitude), 4326), 900913) , ($scale * $pixelDistance))");
			
			//save
			$clustered[] = $res;
			
			//delete from marker table
			Doo::db()->query("DELETE FROM $tempTable  WHERE ST_DWithin( geom, ST_Transform(ST_SetSRID(ST_MakePoint($longitude, $latitude), 4326), 900913) , ($scale * $pixelDistance))");
			
			//get marker table count
			$res = Doo::db()->fetchRow("SELECT count(*) as count from $tempTable");
			$count = $res["count"];
		
		}
	
		return $clustered;
	}

}
?>