<?php

class MobileController extends DooController {

	public function beforeRun($resource, $action){

		if($action != 'login'){
		
			//check if authenicated
			session_start();
			if(!isset($_SESSION['user']))
				header('Location: mobile/login');
		}
	}
	
	function index() {
		//Get Layer data
		$res = Doo::db()->find('Layers', array('asc' => 'name, "order"'));
		$layers = array();
		foreach($res as $l){
			$layers[$l->group][$l->name][$l->order] = array('type' => $l->type, 'url' => $l->url , 'options' => json_decode($l->options, true), 'search_url' => $l->search_url);	
		}
		
		//get user's info
		$user = Doo::db()->getOne('Users', array('where' => 'id = '.$_SESSION['user']['id'] , 'asArray' => true) );
		
		$this->renderc('MobileMain', array('layers' => $layers, 'user' => $user));  	
	}
	
	function login() {
		$this->renderc('MobileLogin', null);  
	}
}
?>