<?php defined('SYSPATH') OR die('No direct access allowed.');

class NpcFactory_Model
{

	/**
	* Create NPC
	* @params string $type (example:smallrat) Type of NPC
	*/
	
	public function create( $type, $id = null )
	{
		$type = ucfirst($type);
		$class = "Character_Npc_" .  $type . "_Model";
		
		kohana::log('debug', "-> Factory: Tring to Create class {$class}" );
     
		// se viene passato l' id carichiamo direttamente la classe.
		if (!is_null($id))
		{
			$npc = ORM::factory('character', $id );
			$class = 'Character_NPC_' . ucfirst( $npc -> npctag) . '_Model' ;
		}
		elseif (!is_null($type))
		{
			$class = "Character_NPC_" . ucfirst($type) . '_Model';
		}
		else
		{
			throw new Exception("-> NPC Factory: Please specify at least one parameter.");
		} 
		
		if (class_exists($class)) {
			if (!is_null($id))
				$instance = ORM::factory( 'character_npc_' . $npc -> npctag, $id );
			else
			{
				$instance = new $class();				
			}
		}	
		else {
			throw new Exception("-> NPC Factory: Invalid class given [($class)].");
		}	
		
		return $instance;		
	
	}	
}
