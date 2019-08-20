<?php defined('SYSPATH') OR die('No direct access allowed.');

class Church_dogmabonus_Model extends ORM
{
	
	protected $belongs_to = array( 'church', 'cfgdogmabonus' );
 
}
