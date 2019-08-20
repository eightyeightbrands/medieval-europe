
<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Carpenter_2_Model extends ST_Carpenter_1_Model
{	
	public function init()
	{
		parent::init();
		$this -> setCurrentLevel(2);
	}

}
