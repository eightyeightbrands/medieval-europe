<?php defined('SYSPATH') OR die('No direct access allowed.');

class Character_Permanentevent_Model extends ORM
{
  protected $sorting = array('timestamp' => 'asc');

	public function add( $character_id, $text)
	{
		
		$a = new Character_Permanentevent_Model();		
		$a -> id = null;
		$a -> type = 'normal';
		$a -> character_id = $character_id;
		$a -> description = $text;
		$a -> timestamp = time();				
		$a -> save();
	
	}
	
}
