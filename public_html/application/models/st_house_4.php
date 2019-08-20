
<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_House_4_Model extends ST_House_Model
{	
	
	public function init()
	{
		parent::init();
		$this -> setBaseprice( 8400 );	
		$this -> setStorage( 5600000 );
		$this -> setRestFactor( 14 );
	}
	
	
	
}
