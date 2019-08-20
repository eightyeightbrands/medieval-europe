<?php defined('SYSPATH') OR die('No direct access allowed.');

class PremiumBonus_travelerpackage_Model extends PremiumBonus_Model
{
	
	var $name = '';
	var $info = array();
	var $canbeboughtonce = false;
			
	function __construct()
    {
        $this -> name = 'travelerpackage';
	}
	
	function postsaveactions( $char, $cut, $par, &$message )
	{
		// event for quest
		$_par = array();
		GameEvent_Model::process_event( $char, 'acquiretravelbonus', $_par );		
	
		parent::postsaveactions($char, $cut, $par, $message );
		return true;
	}
	
	
}