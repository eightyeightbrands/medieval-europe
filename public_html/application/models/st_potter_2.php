
<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Potter_2_Model extends ST_Potter_1_Model
{	

	public function init()
	{
		parent::init();
		$this -> setCurrentLevel(2);
	}

}