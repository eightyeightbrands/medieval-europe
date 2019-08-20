<?php defined('SYSPATH') OR die('No direct access allowed.');

class PremiumBonus_professionaldeskslot_Model extends PremiumBonus_Model
{
	
	function __construct()
    {
        $this -> name = 'professionaldeskslot';
	}
	
	function postsaveactions( $char, $cut, $par, &$message )
	{
		$char -> modify_stat(
			'professionaldeskslot', 
			25, 
			null,
			null,
			false,
			null,
			null,
			null,
			null,
			null,
			null );
		parent::postsaveactions($char, $cut, $par, $message);
		return true;
	}

}
