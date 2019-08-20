
<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Blacksmith_2_Model extends ST_Blacksmith_1_Model
{	

	public function init()
	{
		parent::init();
		$this -> setCurrentLevel(2);
	}

}
