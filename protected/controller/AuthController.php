<?php
 
class AuthController extends DooController {

	protected $res;
	protected $username = '';
	
	public function beforeRun($resource, $action){
	
		$this->res = new response();
		Doo::loadModel('Users');
		Doo::loadModel('Log');		
	}
	
	public function afterRun($routeResult){
		//Display
		$this->setContentType("json");
		echo $this->res->to_json();
	}
	
	function login(){
	
		//check for login info
		if(!isset($_POST['username']) || !isset($_POST['password']) ){
		
			//build response
			$this->res->success = false;
			$this->res->message = "Invalid Request";
			return;
		}
		
		//sanitize 
		$username = $_POST['username'];
		$password = $_POST['password'];
		
		//query database
		$user = Doo::db()->getOne('Users', array('where' => "username =:username", 'param' => array(':username' => $username)));
		//$user = Doo::db()->getOne('Users', array('where' => 'username = \'' . $username . '\' '));
		
		//check for result row
		if(empty($user)){
			//build response
			$this->res->success = false;
			$this->res->message = "Invalid username and/or password. Note: Both fields are case sensitive";
			return;
		}

		//see if user is blocked
		if(!$user->is_enabled) {
			//build response
			$this->res->success = false;
			$this->res->message = "User is blocked. Contact your administrator";
			return;
		}
		
		//compare hash values
		if($user->password === md5($password)) {
		
			session_start();
			session_regenerate_id();
			unset($_SESSION['user']);
				
			//save user session vars
			$_SESSION['user'] = array(
									'id' 			=> $user->id, 
									'username' 		=> $user->username, 	
									'email'			=> $user->email,
									'cluster_zoom_level' => $user->cluster_zoom_level
								);
								
			//build response
			$this->res->success = true;
			$this->res->message = "Login Successful.";
			

			//update user
			$user->client_ip = $this->clientIP();
			date_default_timezone_set('UTC');
			$user->last_login_utc = date( 'Y-m-d H:i:s', time());	
			$user->update();
			
			//update log
			$log = new Log;
			$log->username = $user->username;
			$log->ip_address = $this->clientIP();
			$log->client_ip = $this->clientIP();
			$log->insert();
			
			//change password
			if($user->change_password_on_login == 1){
				//build response
				$this->res->success = true;
				$this->res->message = "Change Password Requested";
				return;
			}
			
		} else{
		
			//build response
			$this->res->success = false;
			$this->res->message = "Invalid username and/or password. Note: Both fields are case sensitive";	
		}
	
	}
	
	function logout(){
		//start session
		session_start();
				
		//destory session
		unset($_SESSION['user']);
		session_destroy();
		
		//build response
		$this->res->success = true;
		$this->res->message = "Logout";
		
	}


	function forgotpassword(){
		
		$email = $_POST['email'];
		//echo "sss";
                $v = new DooValidator;
                //validate
                if(($error = $v->testEmail($_POST['email'])) != null){
                        $this->res->success = false;
                        $this->res->message = $error;
                        return;
                }

                //check if email already exist in users db
                $u = Doo::db()->getOne('Users', array(
                        'where' => " email = :email",
                        'param' => array( ':email' => $email)
                        )
                );
		if(empty($u)){
                        //build response
                        $this->res->success = false;
                        $this->res->message = "Email does not exist";
                        return;

                }

                //generate temp password
                $tmpPassword = $this->str_rand(6);


                //send email with temp password
                $to = $email;

                // Your subject
                $subject = "Your temp password here";

                // From
                $header="from: Register-EVAS <sandip.sandhu@ericsson.com>";
                //$header = null;

                // Your message
                $message = "Your new password \r\n";
                $message.= "$tmpPassword";

                //ini_set ( "SMTP", "smtp-server.example.com" );
		// send email
                if(!mail($to, $subject, $message, $header)){
                        //build response
                        $this->res->success = false;
                        $this->res->message = "Error sending email to $email";
                        return;
                }

                //store new user temp password
                Doo::loadModel('Users');
                //$u = new Users;
                //$u->username = $username;
                //$u->email = $email;
                //$u->isEnabled = 1;
                $u->change_password_on_login = 1;
                $u->password = md5($tmpPassword);
                // $u->default_basemap = "Google Streets";
                //$u->cluster_zoom_level = 15;
                $u->is_enabled = 1;
                //$u->client = "T-Mobile";
                $u->update();

                //success!
                $this->res->success = true;


	}
		
	function register(){
		//check for register info
		if(!isset($_POST['username']) || !isset($_POST['email']) ){
		
			//build response
			$this->res->success = false;
			$this->res->message = "Invalid Request";
			return;
		}
	
		//get inputs
		$username = $_POST['username'];
		$email = $_POST['email'];
		
		$v = new DooValidator;
		//validate
		if(($error = $v->testEmail($_POST['email'])) != null){
			$this->res->success = false;
			$this->res->message = $error;
			return;
		}
		
		//check if username or email already exist in users db
		$u = Doo::db()->find('Users', array(
			'where' => "username = :username OR email = :email", 
			'param' => array(':username' => $username, ':email' => $email)
			)
		);
		
		
		if(!empty($u)){
			//build response
			$this->res->success = false;
			$this->res->message = "Username or Email already exist";
			return;
		
		}
		
		//generate temp password
		$tmpPassword = $this->str_rand(6);
		

		//send email with temp password
		$to = $email;

		// Your subject
		$subject = "Your temp password here";

		// From
		$header="from: Register-EVAS <sandip.sandhu@ericsson.com>";
		//$header = null;
		
		// Your message
		$message = "Your Comfirmation password \r\n";
		$message.= "$tmpPassword";
		
		//ini_set ( "SMTP", "smtp-server.example.com" ); 
		
		// send email
		if(!mail($to, $subject, $message, $header)){
			//build response
			$this->res->success = false;
			$this->res->message = "Error sending email to $email";
			return;
		}
		
		//store new user temp password 
		Doo::loadModel('Users');
		$u = new Users;
		$u->username = $username;
		$u->email = $email;
		$u->isEnabled = 1;
		$u->change_password_on_login = 1;
		$u->password = md5($tmpPassword);
		$u->default_basemap = "Google Streets";
		$u->cluster_zoom_level = 15;
		$u->is_enabled = 1;
		 $u->client = "T-Mobile";
		$u->insert();
			
		//success!
		$this->res->success = true;
	}
	
	function changepassword(){
	
		session_start();
		
		//check for login info
		if(!isset($_SESSION['user']))
		{
		
		    $res = new response(); 
			$res->success = false;
			$res->message = "Unauthorized!";
			return;
		
		}
		
		//check for argument info
		if(!isset($_POST['password1']) || !isset($_POST['password2']))
		{
		
			//build response
			$this->res->success = false;
			$this->res->message = "Invalid Request.";
			return;
			
		}
		
		//check match
		if($_POST['password1'] != $_POST['password2'])
		{
		
			//build response
			$this->res->success = false;
			$this->res->message = "Passwords do not match.";
			return;
			
		}
		
		$userid = $_SESSION['user']['id'];
		$password = $_POST['password1'];
		$newpassword = $_POST['password2'];

		//check strength
		$passwordStrengthError = "";
		
		//if(!preg_match("#\W+#", $newpassword))
		//	$passwordStrengthError  = "New password must include at least one symbol.";	

		if(!preg_match("#[a-z]+#", $newpassword))
			$passwordStrengthError  = "New password must include at least one letter.";
			
		if(!preg_match("#[0-9]+#", $newpassword))
			$passwordStrengthError  = "New password must include at least one number.";				
			
		if(strlen($newpassword) >= 20)
			$passwordStrengthError  = "New password must be less than 20 chacaters long.";				
			
		if(strlen($newpassword) < 6)
			$passwordStrengthError  = "New password must be at least 6 characters long.";
			
		if($passwordStrengthError != "")
		{
		
			//build response
			$this->res->success = false;
			$this->res->message = $passwordStrengthError;
			return;
		
		}

		//update user
		$u = Doo::db()->getOne('Users', array('where' => "id = :id", 'param' => array(':id' => $userid)));
		$u->change_password_on_login = 0;
		$u->password = md5($newpassword);
		$u->update();
		
		//build response
		$this->res->success = true;
		$this->res->message = "Password successfully changed.";

	}
	private function str_rand($length = 8, $seeds = 'alphanum')
	{
	
		//possible seeds
		$seedings['alpha'] = 'abcdefghijklmnopqrstuvwqyz';
		$seedings['numeric'] = '0123456789';
		$seedings['alphanum'] = 'abcdefghijklmnopqrstuvwqyz0123456789';
		$seedings['hexidec'] = '0123456789abcdef';
		
		//choose seed
		if (isset($seedings[$seeds]))
		{
		
			$seeds = $seedings[$seeds];
			
		}
		
		//seed generator
		list($usec, $sec) = explode(' ', microtime());
		$seed = (float) $sec + ((float) $usec * 100000);
		
		mt_srand($seed);
		
		//generate
		$str = '';
		$seeds_count = strlen($seeds);
		
		for ($i = 0; $length > $i; $i++)
		{
		
			$str .= $seeds{mt_rand(0, $seeds_count - 1)};
			
		}
		
		return $str;
		
	}
		
}
