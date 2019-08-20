<?php defined('SYSPATH') OR die('No direct access allowed.');

class PremiumBonus_supercart_Model extends PremiumBonus_Model
{
	var $name = '';
	var $info = array();
	var $canbeboughtonce = false;
	
	function __construct()
    {
        $this -> name = 'supercart';
	}
	
	function postsaveactions( $char, $cut, $par, &$message )
	{
		
		if ( Character_Model::has_item($char->id, 'cart_3', 1) == false )
		{		
			$item = Item_Model::factory( null, 'cart_3' );		
			$item -> additem( 'character', $char-> id, 1 );		
		}
		
		parent::postsaveactions($char, $cut, $par, $message);
		return true;
	}

}
