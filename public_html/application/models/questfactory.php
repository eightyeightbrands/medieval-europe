<?php defined('SYSPATH') OR die('No direct access allowed.');

class QuestFactory_Model
{
	
	public function createQuest( $name )
	{
		// assumes the use of an autoloader
        
		$quest = "Quest_" . $name . "_Model";
		kohana::log('debug', "-> Factory: Creating quest : [{$quest}]");
					
			if (class_exists($quest)) {
				return new $quest();
			}
			else {
				throw new Exception("-> QuestFactory: Invalid quest class given ($name).");
			}		
		
		return $quest;
	
	}
	
	/**
	* ritorna la descrizione del quest
	* @param none
	* @return descrizione
	*/
	
	function get_description()
	{
		return 'No description provided' ;
	}

}
