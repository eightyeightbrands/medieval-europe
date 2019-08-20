<?php defined('SYSPATH') OR die('No direct access allowed.');

class PremiumBonus_Rosary_Model extends PremiumBonus_Model
{
	
	var $name = '';
	var $info = array();
	var $canbeboughtonce = false;
			
	function __construct()
    {
        $this -> name = 'rosary';
	}
}
