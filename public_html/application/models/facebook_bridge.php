<?php defined('SYSPATH') OR die('No direct access allowed.');

class Facebook_Bridge_Model {

	private $fbinstance = null;
	
	public function __construct()
	{
		require_once( "application/libraries/vendors/facebook-php-sdk-v4-5.0-dev/src/Facebook/autoload.php");
		
		$this -> fbinstance = new Facebook\Facebook([
			'app_id'     => kohana::config("medeur.facebook_app_id"),
			'app_secret' => kohana::config("medeur.facebook_app_secret"),
			'default_graph_version' => 'v2.2'
		]);	
	}
	
	private function get_instance()
	{
		return $this -> fbinstance;		
	}
	
	public function get_login_url()
	{			
		$helper = $this -> get_instance() -> getRedirectLoginHelper();
		$permissions = ['email']; // optional
		$callback = kohana::config("medeur.facebook_callback");
		$loginUrl = $helper->getLoginUrl($callback, $permissions);		
		kohana::log('debug', $loginUrl);
		return $loginUrl;	
	}
	
}
?>
				
		