


<?php defined('SYSPATH') OR die('No direct access allowed.');

class Character_Event_Model extends ORM
{
	protected $sorting = array('id' => 'desc');
	
	/**
	* Adds a character event or a boardmessage
	* @param int characterid
	* @param string eventtype ('announcement' => towncrier event 'normal' => character event)
	* @param string text event text
	* @param class css class
	*/
	
	public static function addrecord( $character_id, $eventtype, $text, $eventclass = null)
	{		
	
		if ( $eventtype == 'announcement' )
			Boardmessage_Model::systemadd( 1, 'europecrier', $text, $eventclass);
		else
		{
			$a = new Character_Event_Model();	
			$a -> id = null;
			$a -> character_id = $character_id;
			$a -> type = $eventtype;
			$a -> description = $text;
			$a -> timestamp = time();		
			$a -> eventclass = $eventclass;						
			$a -> save();
			
			My_Cache_Model::delete(  '-charinfo_' . $character_id . '_unreadevents' ); 			
			
			
		
		}
	}
	
}

