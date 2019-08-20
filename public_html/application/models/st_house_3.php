
<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_House_3_Model extends ST_House_Model
{	
	
	public function init()
	{
		parent::init();
		$this -> setBaseprice( 4200 );	
		$this -> setStorage( 2800000 );
		$this -> setRestFactor( 7 );
	}
	
	
	
}
