<?php defined('SYSPATH') OR die('No direct access allowed.');

class PremiumBonus_Diamondring_Model extends PremiumBonus_Model
{
	
	function __construct()
    {
        $this -> name = 'diamondring';
	}
	
	function postsaveactions( $char, $cut, $par, &$message )
	{
		$item = Item_Model::factory( null, 'ringdiamond' );		
		$item -> additem( 'character', $char -> id, 1 );
		parent::postsaveactions($char, $cut, $par, $message );
		return true;
	}
}
