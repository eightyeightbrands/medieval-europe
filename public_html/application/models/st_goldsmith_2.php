
<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Goldsmith_2_Model extends ST_Goldsmith_1_Model
{	

	public function init()
	{
		parent::init();
		$this -> setCurrentLevel(2);
	}

}
