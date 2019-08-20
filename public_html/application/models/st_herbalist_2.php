
<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Herbalist_2_Model extends ST_Herbalist_1_Model
{	

	public function init()
	{
		parent::init();
		$this -> setCurrentLevel(2);
	}

}