<?php defined('SYSPATH') OR die('No direct access allowed.');

class Kingdom_War_Model extends ORM
{
	
	/*
	* Termina una guerra 
	* @param str $reason (Ragione per cui è terminata)
	* @return none 
	*/

	function finish( $reason )
	{
		
		// cancel all battles linked to warcompleted
		
		$battles = ORM::factory('battle') 
			-> where( 
				array(
					'kingdomwar_id' => $this -> id,
					'status' => 'running')
			) -> find_all();
		
		foreach ( $battles as $battle )
		{
			// cancel battles
			Database::instance() -> query ("update battles set status = 'canceled' where id = {$battle->id}");
			// cancel all battlerounds
			Database::instance() -> query ("update character_actions set status = 'canceled' where action = 'battleround'
and status = 'running' and param2 in ( select id from battles where status = 'canceled')");
			// cancel all battlefield creations
			Database::instance() -> query ("update character_actions set status = 'canceled' where action = 'createcdb'
and status = 'running' and param1 in ( select id from battles where status = 'canceled')");
			// delete all battlefields
			Database::instance() -> query ("delete from structures 
				where structure_type_id = (select id from structure_types where parenttype = 'battlefield') 
				and attribute1 in (select id from battles where status = 'canceled' )");			
			$battle_participants = 
				ORM::factory('battle_participant')
				-> where ( 'battle_id', $battle -> id) 
				-> find_all();		
			// set fighting = false for all battle participants	
			foreach( $battle_participants as $battle_participant )
			{
				
				Character_Model::modify_stat_d(
					$battle_participant -> character_id, 
					'fighting', 
					false,
					null,
					null,
					true);					
			}
			
			// remove all battle participants			
			Database::instance() -> query("delete from battle_participants where battle_id = {$battle->id}");
			
		}
			
			
		
		$this -> end = time();
		$this -> status = 'completed';
		$this -> save();
		
		$cachetag = '-cfg-kingdomswars' ;		
		$cfg = My_Cache_Model::delete( $cachetag );		
		
		// king events
		
		$sourcekingdom = ORM::factory('kingdom', $this -> source_kingdom_id );
		$targetkingdom = ORM::factory('kingdom', $this -> target_kingdom_id );
		
		$sourceking = $sourcekingdom -> get_king();
		$targetking = $targetkingdom -> get_king();
		
		if (!is_null($sourceking))
			Character_Event_Model::addrecord(
				$sourceking -> id,
				'normal',
				'__events.warcompleted' . 			
				';__' . $targetkingdom -> name . 
				';__global.reason_' . $reason,
				'evidence'			
			);		
		
		if (!is_null($targetking))
			Character_Event_Model::addrecord(
				$targetking -> id,
				'normal',
				'__events.warcompleted' . 			
				';__' . $sourcekingdom -> name . 
				';__global.reason_' . $reason,
				'evidence'			
			);		
		
		// town crier 
		
		Character_Event_Model::addrecord( 
			null, 
			'announcement', 			
			'__events.warterminated' . 
			';__' . $sourcekingdom -> name . 
			';__' . $targetkingdom -> name,			
			'evidence' );			
	}

}
