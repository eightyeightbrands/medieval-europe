<?php defined('SYSPATH') OR die('No direct access allowed.');

class PremiumBonus_professionaldesk_Model extends PremiumBonus_Model
{
	
	var $name = '';
	var $info = array();
	var $canbeboughtonce = true;
			
	function __construct()
    {
        $this -> name = 'professionaldesk';
	}
	
	function postsaveactions( $char, $cut, $par, &$message )
	{
		$char -> modify_stat(
			'professionaldeskslot', 
			15, 
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