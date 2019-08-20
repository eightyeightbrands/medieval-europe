
<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_House_2_Model extends ST_House_Model
{	
	
	public function init()
	{
		parent::init();
		$this -> setBaseprice( 1800 );	
		$this -> setStorage( 1200000 );
		$this -> setRestFactor( 3 );
	}
	
	
	
}
