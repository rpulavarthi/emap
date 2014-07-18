<?php

class SearchController extends DooController {

	public function beforeRun($resource, $action){
		$this->res = new response;

		//check if authenicated
		session_start();
		if(!isset($_SESSION['user'])){
			$this->res->success = false;
			$this->res->message = "Not Logged In!";
			$this->setContentType("json");
			echo $this->res->to_json();
			//return out of controller and afterRun
			return 200;
		}
		
	}
	
	function index() {
		$layer = $this->params['layer'];
		
		$page 		= $_GET['page']; // get the requested page
		$limit 		= $_GET['rows']; // get how many rows we want to have into the grid
		$sidx 		= $_GET['sidx']; // get index row - i.e. user click to sort
		$sord 		= $_GET['sord']; // get the direction
		$centerlat 	= $_GET['centerLat'];
		$centerlng 	= $_GET['centerLng'];		
		$bottom 	= $_GET['bottom'];
		$top 		= $_GET['top'];
		$left 		= $_GET['left'];
		$right 		= $_GET['right'];
		
		$searchTerm = $_GET['searchTerm'];
		
		if(!$sidx) $sidx = 1;
		
		$response = new searchResponse;
		
		//parse prefixes
		
		//Address (Geocode)
		if(preg_match('/^A:/', $searchTerm)){
			$g = new geocoder($centerlat, $centerlng, abs($top - $bottom), abs($left - $right) );
			
			if(strlen(preg_replace('/^A:/', '',  $searchTerm)) < 4) return;
			
			$placemarks = $g->geocode(preg_replace('/^A:/', '',  $searchTerm));
		
			$data = array();
			foreach($placemarks as $p){
				$data[] = array('key'=>$p->getAddress(), 'lat'=> $p->getPoint()->getLatitude(), 'lng' => $p->getPoint()->getLongitude());
			}

			$response->rows = $data;
		
		}
		//Cascade
		if(preg_match('/^C:/', $searchTerm)){
			$searchTerm = preg_replace('/^C:/', '',  $searchTerm);	
			$res = Doo::db()->fetchAll("SELECT  key || '   (' || switch_cell || ')' as key, latitude as lat, longitude as lng, ST_Distance(ST_Transform(ST_GeomFromText('POINT($centerlng $centerlat)',4326), 900913), geom) as distance
										FROM cells  
										WHERE layer = '$layer' AND key like '%$searchTerm%' ORDER BY distance limit 10");
			$response->rows = $res;
		}
		
		//Switch Cell
		if(preg_match('/^S:/', $searchTerm)){
			$searchTerm = preg_replace('/^S:/', '',  $searchTerm);	
			$res = Doo::db()->fetchAll("SELECT key || '   (' || switch_cell || ')' as key, latitude as lat, longitude as lng, ST_Distance(ST_Transform(ST_GeomFromText('POINT($centerlng $centerlat)',4326), 900913), geom) as distance
										FROM cells  
										WHERE layer = '$layer' AND switch_cell like '%$searchTerm%' ORDER BY distance limit 10");
			$response->rows = $res;
		}
		
		//default		
		echo json_encode($response);
		
	}

}
?>