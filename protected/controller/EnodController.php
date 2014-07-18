<?php

class EnodController extends DooController{

	public function beforeRun($resource, $action){

		if(preg_match('/(iPhone|Android|iPod|iPad)/i', $_SERVER['HTTP_USER_AGENT']))
			//mobile site
			header('Location: mobile');

		if($action != 'login'){

			//check if authenicated
			session_start();
			if(!isset($_SESSION['user']))
				header('Location: login');
		}
	}
	
	function index() {

		//get user's info
		$user = Doo::db()->getOne('Users', array('where' => 'id = '.$_SESSION['user']['id'] , 'asArray' => true) );

		//Get Layer data
		$res = Doo::db()->find('Layers', array('where' => "client = '" . $user["client"] . "'", 'asc' => 'name, `order`'));
		$layers = array();
		//$host="";
		foreach($res as $l){
			//$host=$l->host;
			$layers[$l->client][$l->name][$l->order] = array(
											'type' => $l->type,
											'url' => $l->host . $l->url ,
											'options' => json_decode($l->options, true),
											'search_url' => $l->host . $l->search_url,
											'grid_url' => $l->host . $l->grid_url,
											'legend_url' => $l->host . $l->legend_url,
										);
		}

		//get user's info
		$user = Doo::db()->getOne('Users', array('where' => 'id = '.$_SESSION['user']['id'] , 'asArray' => true) );

		//get links
		$links = Doo::db()->find('Links', array('where' => "client = '" . $user["client"] . "'"));

		//get clients
		$clients = Doo::db()->find('Clients', array('asc' => 'name'));

		//get tables info for drive-test tab
		$sql="SELECT distinct year from enod_table_config order by year desc";
		$dtest_year = Doo::db()->fetchAll($sql);
		$default_year = $dtest_year[0]['year'];
		
		$sql="SELECT data_source, data_source_name from enod_table_config where year ='". $default_year."' order by data_source_name";
		$dtest_location = Doo::db()->fetchAll($sql);
		$default_ds = $dtest_location[0]['data_source'];
		
		$sql="SELECT query_params_values from enod_table_config where year = '". $default_year."' and data_source='". $default_ds."' order by year";
		$dtest_params = Doo::db()->fetchAll($sql);
		//
		
		//render
//		$this->renderc('DesktopMain', array('layers' => $layers, 'user' => $user, 'links' => $links, 'clients' => $clients));
		$this->renderc('EnodMain', array('layers' => $layers, 'user' => $user, 'links' => $links, 'clients' => $clients, 'dtest_year' => $dtest_year, 'dtest_location' => $dtest_location, 'dtest_params' => $dtest_params));
	}

	function login() {
		$this->renderc('DesktopLogin', null);
	}
}
?>
