<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Terrain_1_Model extends ST_Terrain_Model
{
	
	public function init()
	{
		parent::init();
		$this -> setIsbuyable(true);
		$this -> setIssellable(true);
		$this -> setParenttype('terrain');
		$this -> setBaseprice(100);			
		$this -> setRestFactor(0);
		$this -> setMaxlevel(2);
		$this -> setCurrentLevel(1);
		$this -> setHoursfornextlevel(80);			
		$this -> setNeededmaterialfornextlevel(
			array(
				'iron_piece' => 30,
				'wood_piece' => 80,					
			)
		);
	}

}
