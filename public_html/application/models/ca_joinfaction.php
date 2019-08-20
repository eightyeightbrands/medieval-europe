<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Joinfaction_Model extends Character_Action_Model
{
	protected $immediate_action = true;
	protected $character_warcategorization = null;
	const REVOLTMINIMUMAGETOATTACK = 60;
	const REVOLTMINIMUMAGETODEFEND = 30;
	
	/*
	 * par[0] = oggetto char
	 * par[1] = oggetto struttura
	 * par[2] = fazione
	 
	*/
	
	protected function check( $par, &$message )
	{ 
		
		if ( ! parent::check( $par, $message ) )					
		{ return false; }
		
		if ( $par[1] -> region_id <> $par[0] -> position_id) 
		{ $message = kohana::lang('global.operation_not_allowed'); return false; }
		
		$battle = ORM::factory('battle', $par[1] -> attribute1 ); 
		$attackingregion = ORM::factory('region', $battle -> source_region_id );
		$attackedregion = ORM::factory('region', $battle -> dest_region_id );		
		$battletype = Battle_TypeFactory_Model::create( $battle -> type );				
		
		// è già schierato?
		
		$participant = ORM::factory('battle_participant') 
			-> where ( 
				array(
				'battle_id' => $battle -> id,
				'character_id' => $par[0] -> id,
				'status' => 'alive'
				)
			) -> find();
			
		if ($participant -> loaded and $participant -> faction == $par[2] )
		{ $message = kohana::lang('ca_joinfaction.error-alreadyjoinedthisfaction'); return false; }
		
		
		$this -> character_warcategorization = 
			$battletype -> categorize ( $par[0], $attackingregion, $attackedregion, $battle -> type );		
		
		kohana::log('info', '-> War Categorization for char: ' . $par[0] -> name . ' is: ' . $this -> character_warcategorization );
				
		////////////////////////////////////////////////////
		// la battaglia deve essere in corso
		////////////////////////////////////////////////////
				
		if ( !$battle -> loaded or $battle -> status != 'running' )
		{ $message = kohana::lang('ca_joinfaction.error-battleiscompleted'); return false; }
		
		////////////////////////////////////////////////////
		// non è possibile joinare la battaglia se l'orario
		// della battaglia è già passato
		////////////////////////////////////////////////////
		
		$battleround = ORM::factory('character_action') 
			-> where ( 
				array (
					'action' => 'battleround',
					'param2' => $battle -> id,
					'status' => 'running'				
				) 		
			)-> find(); 
		
		if ( $battleround -> loaded and time() > $battleround -> starttime )		
		{ $message = kohana::lang('ca_joinfaction.error-youcantjoinnow'); return false; }		
		
		////////////////////////////////////////////////////
		// il char deve avere una certà età di gioco
		////////////////////////////////////////////////////
		
		if ( $par[0] -> get_age() < kohana::config('medeur.mindaystofight') )
		{ $message = kohana::lang('ca_joinfaction.tooyoungtofight',
			kohana::config('medeur.mindaystofight')); return false; }				
		
		////////////////////////////////////////////////////
		// Native Revolt
		// se la battaglia è nativerevolt non ci si può schierare come attaccante
		////////////////////////////////////////////////////
		
		if ( $battle -> type == 'nativerevolt' and $par[2] == 'attack' )
		{ $message = kohana::lang('ca_joinfaction.error-cantjoinnativerevolt_attack'); return false; }
	
		// Possono difendere solo alleati o cittadini
		
		if ( 
				$battle -> type == 'nativerevolt' 
				and 
				$par[2] == 'defend' 
				and !in_array($this -> character_warcategorization, array('defenderorally'))
			)
		{ $message = kohana::lang('ca_joinfaction.error-cantjoinnativerevolt_defend'); return false; }
		
		////////////////////////////////////////////////////
		// Revolt
		// se la battaglia è una rivolta, il char deve essere del regno
		////////////////////////////////////////////////////
		
		if ( 
			$battle -> type == 'revolt' 
			and $par[0] -> region -> kingdom_id != $par[1] -> region -> kingdom_id )
		{ $message = kohana::lang('ca_joinfaction.cantjoinrevolt_foreign'); return false; }
		
		// il char deve essere residente da almeno x giorni 
		// per schierarsi in attacco
		
		$kingdomchangeddays = $par[0] -> get_timesincelastresidencechange();
		
		if ( 
			$battle -> type == 'revolt' 
			and  
			$par[2] == 'attack'
			and
			(
				(is_null($kingdomchangeddays) and $par[0] -> get_age() < kohana::config('medeur.revoltminimumage') )
				or 
				(!is_null($kingdomchangeddays) and $kingdomchangeddays < kohana::config('medeur.revoltminimumage'))
			) 
		)		
		{ 
			$message = kohana::lang('ca_joinfaction.chooserevoltattackfaction_notoldenough', kohana::config('medeur.revoltminimumage')); 
			return false; 
		}
		
		// il char deve essere residente da almeno x giorni.
		// per schierarsi in attacco				
		
		if ( 
			$battle -> type == 'revolt' 
			and 
			$par[2] == 'defend'
			and  
			(
				(is_null($kingdomchangeddays) and $par[0] -> get_age() < kohana::config('medeur.revolt_defenderdayslimit') )
				or 
				(!is_null($kingdomchangeddays) and $kingdomchangeddays < kohana::config('medeur.revolt_defenderdayslimit'))
			)				
		)
		{ 
			$message = kohana::lang('ca_joinfaction.chooserevoltdefensefaction_notoldenough', kohana::config('medeur.revolt_defenderdayslimit')); 
			return false; 
		}		
		
		// il Re non può schierarsi con i rivoltosi		
		
		$role = $par[0] -> get_current_role();
		if ( $battle -> type == 'revolt' and !is_null( $role) and $role -> tag == 'king' and $par[2] == 'attack' )
		{
			$message = kohana::lang( 'charactions.revolt_kingcantsupportrevolt');
			return false;
		}	
			
		// L' organizzatore della rivolta non può supportare il Re.
		
		if ( $battle -> type == 'revolt' and $par[0]->id == $battle->source_character_id and $par[2] == 'defend' )
		{
			$message = kohana::lang( 'charactions.revolt_leadercantsupportking');
			return false;
		}
		
		////////////////////////////////////////////////////
		// Conquer Region
		// DIFESA
		// Solo se si è:
		// . Cittadini del regno che attacca (da almeno un mese)
		// . Alleati degli attaccanti (da almeno un mese)		
		////////////////////////////////////////////////////
		
		if ( 
			in_array( $battle -> type, array( 'conquer_r', 'raid', 'conquer_ir' ) ) 
			and			
			$par[2] == 'attack' and 
			!in_array( 
				$this -> character_warcategorization, array( 'attackerorally' ) ) )		
		{ $message = kohana::lang( 'ca_joinfaction.error-cannotjoinattackers'); return false; }
		
		////////////////////////////////////////////////////
		// DIFESA: solo alleati o mercenari
		////////////////////////////////////////////////////
		
		if ( 
			in_array( $battle -> type, array( 'conquer_r', 'raid', 'conquer_ir' ) ) 
			and
			$par[2] == 'defend' 
			and 
			!in_array( 
				$this -> character_warcategorization, array( 'defenderorally' ) ) )		
		{ $message = kohana::lang( 'ca_joinfaction.error-cannotjoindefenders'); return false; }
						
		return true;
	
	}

	
	// nessun controllo particolare
	protected function append_action( $par, &$message )
	{}

	public function execute_action ( $par, &$message ) 
	{
			
		// il giocatore è già schierato?
		
		Database::instance() -> query (
		"delete from 
		battle_participants
		where battle_id = " . $par[1] -> attribute1 . " 
		and   character_id = " . $par[0] -> id ); 
		
		$part = new Battle_Participant_Model();		
		$part -> battle_id = $par[1] -> attribute1;
		$part -> character_id = $par[0] -> id;
		$part -> faction = $par[2];
		$part -> status = 'alive'; 	
		$part -> categorization = $this -> character_warcategorization;
		$part -> save();
		
		// Modifico lo stato del char 
		
		$par[0] -> modify_stat( 
				'fighting', 
				true,
				null,
				null,
				true );			
		
		// 
		
		Character_Event_Model::addrecord( $par[0] -> id , 
			'normal',
			'__events.charjoinedfaction' . 
			';' . $par[0] -> name . 
			';__structures_battlefield.fightmode_' . $par[2] . 
			';' . $par[1] -> attribute1			
		);
		
		kohana::log('info', "{$par[0] -> name} joined {$par[2]} Faction for battle id: {$par[1] -> attribute1}.");
		
		$message = kohana::lang( 'ca_joinfaction.joinedfactionok');
		return true;
	}
}
