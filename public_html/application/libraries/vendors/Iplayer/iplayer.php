<?php

/**
 * Class IPlayer
 * Class for work with iPlayer server
 */
class IPlayer
{
	/**
	 * @var string Server domain
	 */
	public static $api_domain = 'iplayer.org';
	/**
	 * @var string Key for access_token
	 */
	public static $token_key = 'access_token';


	/**
	 * @var string Access token
	 */
	protected $access_token;

	/**
	 * Handle iPlayer server request
	 * @return IPlayer
	 * @throws IPlayer_Exception
	 */
	public static function handle_request(){
		if(!array_key_exists(self::$token_key, $_GET)){
			throw new IPlayer_Exception('Unknown access token');
		}

		return new self($_GET[self::$token_key]);
	}


	/**
	 * @return object User information
	 * @throws IPlayer_Exception
	 */
	public function user_info()
	{
		return $this->request('/user/info', $this->access_token);
	}

	/**
	 * @param $access_token
	 */
	protected function __construct($access_token){
		$this->access_token = $access_token;
	}

	/**
	 * Make API request
	 * @param $action
	 * @param $access_token
	 * @return mixed
	 * @throws IPlayer_Exception
	 */
	protected function request($action, $access_token)
	{
		$url = $this->api_url() . $action . '?access_token=' . $access_token;
		$response = @file_get_contents($url);
		if($response === false){
			throw new IPlayer_Exception('Something wrong');
		}
		return json_decode($response, true);
	}

	/**
	 * Build api url
	 * @return string api url;
	 */
	protected function api_url()
	{
		return 'https://' . self::$api_domain . '/oauth';
	}
}

/**
 * Class IPlayer_Exception
 */
class IPlayer_Exception extends Exception{

}