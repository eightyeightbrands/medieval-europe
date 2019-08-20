<?php defined('SYSPATH') OR die('No direct access allowed.');

class GameEvent_Model
{
	/**
	* Dispatcha l' evento alla corretta classe di 
	* quest.
	* @param $char oggetto $char
	* @param $event nome evento, esempio 'configuration'
	* @param $par vettore di parametri
	* @return none
	*/
	
	function process_event( $char, $event, $par )
	{
		kohana::log('debug', '-> GameEvent: Processing game event: ' . $event );
		
		// trova tutti i quest che sono registrati per questo evento (e attivi
		// in questo momento)
		
		$queststoprocess = array();		
		$registeredquests = ORM::factory('cfgquest_event') -> where ( 'event', $event ) -> find_all ();
		
		if ( count( $registeredquests ) > 0 )
		{
			kohana::log('debug', "-> GameEvent: registeredquests: " . count( $registeredquests ) );
			foreach ( $registeredquests as $registeredquest )
			{
				$questinstance	= Character_Model::get_stat_d( 
						$char -> id,
						'quest',
						$registeredquest -> cfgquest -> name );
					
				if ( $questinstance -> loaded and $questinstance -> param2 == 'active' )
					$queststoprocess[] = $questinstance;
			
			}
					
			foreach ( $queststoprocess as $questtoprocess )
			{
				$quest = QuestFactory_Model::createQuest( $questtoprocess -> param1 );
				$quest -> process_event( $char, $event, $par, $questtoprocess );
			
			}
		}
		
		return;
	
	}
}
