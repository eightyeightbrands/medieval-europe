<?php defined('SYSPATH') OR die('No direct access allowed.');

class User_Referral_Model extends ORM
{
	  
	protected $sorting = array('id' => 'desc');
	
	/*
	** Torna il referrer di un char.
	* @param obj $referral Character_Model (referral)
	* @return mixed NULL o Character_Model (referrer)
	*/
	
	function get_referrer( $referral )
	{
		kohana::log('debug', "-> Getting referrer of user: {$referral->user_id}");
		$db = Database::instance();
		$sql = "
		SELECT c.id
		FROM   characters c, user_referrals ur
		WHERE  ur.referred_id = {$referral->user_id}
		AND    ur.user_id = c.user_id LIMIT 1";
		
		$res = $db -> query($sql);
		if (count( $res )==0)
			return null;
		else
			return ORM::factory('character', $res[0] -> id);
		
	}
	
}
