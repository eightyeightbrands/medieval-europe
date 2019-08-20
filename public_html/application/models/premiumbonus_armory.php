<?php defined('SYSPATH') OR die('No direct access allowed.');

class PremiumBonus_Armory_Model extends PremiumBonus_Model
{
			
	function __construct()
    {
        $this -> name = 'armory';
		$this -> canbegifted = false;
		$this -> canbeboughtonce = false;
	}

	function addextrafields()
	{		
		$html = 
		'Add Bonus to Barrack in: ' .
		form::input( array( 
			'id' => 'region',
			'class' => 'region',
			'placeholder' => kohana::lang('global.selectaregion'),
			'name' => 'region_name',
		));
		
		return $html;
	}	
}