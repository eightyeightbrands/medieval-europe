<?php 

class CallHelper {
  
	/**
	 * @return access token
	 */
	public static function getCurl($appID, $appSecret, $idCode) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://www.id.net/oauth/token");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, "idnet-php-example");
		curl_setopt($ch, CURLOPT_POSTFIELDS, array(
							"grant_type" => "authorization_code",
							"client_id" => $appID,
							"client_secret" => $appSecret,
							"code" => $idCode
							)
		);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result; 
	}
	
	
	/**
	 * @return user extra data
	 */
	public static function getUserDataCurl($access_token) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://www.id.net/api/v1/json/profile");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer ".$access_token));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, 'idnet-php-example');
		$result = curl_exec($ch);
		curl_close($ch);
		$profile = json_decode($result, true);
		return $profile; 
	}
}
?>