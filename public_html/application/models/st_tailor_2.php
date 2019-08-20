
<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Tailor_2_Model extends ST_Tailor_1_Model
{	

	public function init()
	{
		parent::init();
		$this -> setCurrentLevel(2);
	}

}