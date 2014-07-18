<?php

class GridController extends DooController {

	public function beforeRun($resource, $action){
		Doo::db()->reconnect('dev_postgis');
	}
	
	function index() {
	
		//Navigation
		$page = $_GET['page']; 
		$limit = $_GET['rows']; 
		$sidx = $_GET['sidx']; 
		$sord = $_GET['sord']; 
	
		//GIS
		$layer = $this->params['layer'];
		$minx = $_GET["minx"];
		$maxx = $_GET["maxx"];
		$miny = $_GET["miny"];
		$maxy = $_GET["maxy"];
		
		$srid = 4326;
		
		//get total results
		$sql = "SELECT count(*) as cnt FROM cells 
					WHERE
					layer = '$layer'
					AND latitude between $minx and $maxx
					AND longitude between $miny and $maxy";
					
		$res = Doo::db()->fetchRow($sql);
		$count = $res["cnt"];
		
		// calculate the total pages for the query 
		if( $count > 0 && $limit > 0) { 
			$total_pages = ceil($count/$limit); 
		} else { 
			$total_pages = 0; 
		} 
 
		if ($page > $total_pages) 
			$page = $total_pages;

		$start = $limit * $page - $limit;	 
		if($start <0) $start = 0; 
			
		//full sql
		$sitesql = "SELECT key, switch_cell FROM cells 
					WHERE
					layer = '$layer'
					AND latitude between $minx and $maxx
					AND longitude between $miny and $maxy
					ORDER BY $sidx $sord 
					LIMIT $limit OFFSET $start";

		$sitedata = Doo::db()->fetchAll($sitesql);
		
		$rows = array();
		foreach($sitedata as $site){
			$rows[] = array('id' => $site['key'],  'cell' => array($site['key'], $site['switch_cell']));
		
		}
	
		echo json_encode(array('page' => $page, 'total' => $total_pages, 'records'=> $count , 'rows' => $rows));
	}
}
?>