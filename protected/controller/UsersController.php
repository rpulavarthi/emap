<?php
class UsersController extends DooController {

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
		
		Doo::loadModel('Users');	
	}
	
	public function afterRun($routeResult){
			//Display Results
			$this->setContentType("json");
			echo $this->res->to_json();
	}
	
	function update() {
		$u = new Users;
		$u->id 				= $_SESSION['user']['id'];
		$u->cluster_zoom_level = $_POST['cluster_zoom_level'];
		$u->home_latitude 	= $_POST['home_latitude'];
		$u->home_longitude 	= $_POST['home_longitude'];
		$u->home_zoom 		= $_POST['home_zoom'];
		$u->default_basemap = $_POST['default_basemap'];
		$u->default_layer 	= $_POST['default_layer'];
		$u->change_password_on_login = $_POST['change_password_on_login'];
		$u->client = $_POST['client'];
		
		$_SESSION['user']['cluster_zoom_level'] = $_POST['cluster_zoom_level'];
		
		$u->update();
		$this->res->success = true;
	}

}
?>