
<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Chef_2_Model extends ST_Chef_1_Model
{	
	
	public function init()
	{
		parent::init();
		$this -> setCurrentLevel(2);
	}
}
