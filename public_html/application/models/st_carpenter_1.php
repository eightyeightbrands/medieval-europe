
<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Carpenter_1_Model extends ST_Shop_Model
{	
	public function init()
	{
		parent::init();
		
		$this -> setParenttype('shop');
		$this -> setSuperType('carpenter');
		$this -> setCurrentLevel(1);
		$this -> setIsupgradable(true);
		$this -> setMaxlevel(2);
		$this -> setHoursfornextlevel(40);			
		$this -> setNeededmaterialfornextlevel(
			array(
				'iron_piece' => 15,
				'wood_piece' => 45,					
			)
		);
	}
}
