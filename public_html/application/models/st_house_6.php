
<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_House_6_Model extends ST_House_Model
{	
	
	public function init()
	{
		parent::init();
		$this -> setBaseprice( 33600 );	
		$this -> setStorage( 22400000 );
		$this -> setRestFactor( 56 );
	}
	
	
	
}
