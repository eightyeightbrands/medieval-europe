
<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_House_5_Model extends ST_House_Model
{	
	
	public function init()
	{
		parent::init();
		$this -> setBaseprice( 16800 );	
		$this -> setStorage( 11200000 );
		$this -> setRestFactor( 28 );
	}
	
	
	
}
