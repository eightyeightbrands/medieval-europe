<?php defined('SYSPATH') OR die('No direct access allowed.');

class Google_Bridge_Model {

const CLIENTID = '283030588706-vklua2bcrrh2qufoof76e3u4rqmfjhi1.apps.googleusercontent.com'; 
const CLIENTSECRET = 'NGp4nGqjcSAune8YYXYqIcJ1';
private $client = null;
private $redirect_uri = null;
private $service = null;

	public function __construct()
	{
		require_once('application/libraries/vendors/google-api-php-client-master/src/Google/autoload.php');					
		$this -> client = new Google_Client();		
		$this -> client -> setClientId(self::CLIENTID);
		$this -> client -> setClientSecret(self::CLIENTSECRET);
		$this -> client -> setRedirectUri(url::base(true, 'https') . 'user/google_login');
		$this -> client -> addScope("https://www.googleapis.com/auth/plus.login");
		$this -> client -> addScope("https://www.googleapis.com/auth/userinfo.email");
		$this -> service = new Google_Service_Plus($this -> client);
	}

	public function get_google_login_url()
	{					
		return $this -> client -> createAuthUrl();		
	}
	
	public function get_service()
	{
		return $this -> service;		
	}
	
	public function get_client()
	{
		return $this -> client;		
	}

}
?>
				
		
