
<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Distillery_2_Model extends ST_Distillery_1_Model
{	

	public function init()
	{
		parent::init();
		$this -> setCurrentLevel(2);
	}

}
