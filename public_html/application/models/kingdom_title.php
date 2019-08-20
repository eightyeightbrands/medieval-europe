<?php defined('SYSPATH') OR die('No direct access allowed.');

class Kingdom_Title_Model extends ORM
{
	protected $belongs_to = array('kingdom', 'cfgachievement' );
    
	
}
