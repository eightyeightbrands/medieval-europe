
<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_House_1_Model extends ST_House_Model
{	
	
	public function init()
	{
		parent::init();
		$this -> setBaseprice( 360 );	
		$this -> setStorage( 480000 );
		$this -> setRestFactor( 1.2 );
	}
	
	
}
