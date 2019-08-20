<?php defined('SYSPATH') OR die('No direct access allowed.');

class Character_stat_Model extends ORM
{
	
	protected $belongs_to = array( 'character' );
	protected $sorting = array('value' => 'desc' );
 
}
