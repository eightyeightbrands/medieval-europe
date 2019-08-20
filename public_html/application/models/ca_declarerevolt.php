<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Declarerevolt_Model extends Character_Action_Model
{
	protected $immediate_action = true;
	protected $cancel_flag = true;	
	protected $cost = 0; 
	
	
	// Effettua tutti i controlli relativi all' azione, sia quelli condivisi
	// con tutte le action che quelli peculiari 
	// @input: array di parametri
	// par[0]: oggetto char
	// par[1]: oggetto structure
	
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $message contiene il messaggio di ritorno	
	
	protected function check( $par, &$message )
	{ 		
				
		
		if ( ! parent::check( $par, $message ) )					
		{ return false; }
				
		
		// controllo dati
		
		if ( !$par[0] -> loaded or !$par[1] -> loaded )
		{
			$message = kohana::lang( 'global.operation_not_allowed');
			return false;
		}	
		
		/////////////////////////////////////////////////////////////
		// Se il regno è in guerra, non si può dichiarare rivolta
		/////////////////////////////////////////////////////////////
		
		$kingdomrunningwars = Kingdom_Model::get_kingdomwars( $par[0] -> region -> kingdom_id, 'running');
		if (count($kingdomrunningwars) > 0 )
		{ $message = kohana::lang( 'ca_declarerevolt.error-kingdomisonwar'); return false;}	
		
		
		// controlla che il char abbia i soldi necessari
		
		$this -> cost = Battle_Revolt_Model::compute_costs( $par[1] -> region -> kingdom ); 
		if ( ! $par[0]->check_money( $this -> cost ) )
		{
			$message = kohana::lang( 'charactions.global_notenoughmoney');
			return false;
		}		
		
		// il char può dichiarare una rivolta solo verso il suo regno
		if ( $par[1] -> region -> kingdom_id != $par[0] -> region -> kingdom_id )
		{
			$message = kohana::lang( 'ca_declarerevolt.error-cannotdeclarerevolttootherkingdom');
			return false;
		}
			
		// il char deve essere eligibile come RE.		
		if ( Character_Role_Model::check_eligibility( $par[0], 'king', null, $message ) == false )
		{ 
			$message = kohana::lang( 'ca_declarerevolt.error-noteligibleasking');
			return false; 
		}
				
		// il char deve essere residente da un certo periodo
		//var_dump('age:' . $par[0] -> get_age());
		//var_dump('revolt min age:' . kohana::config('medeur.revoltminimumage'));
		//var_dump('last resid change: ' . $par[0] -> get_timesincelastresidencechange());
		
		$kingdomchangeddays = $par[0] -> get_timesincelastresidencechange();
		
		if ( 
			(
				is_null( $kingdomchangeddays ) and $par[0] -> get_age() < kohana::config('medeur.revolt_declarerevoltdayslimit') 
			)
			or
			(
				!is_null( $kingdomchangeddays ) and $kingdomchangeddays < kohana::config('medeur.revolt_declarerevoltdayslimit')
			)
		)
		{
			$message = kohana::lang( 'ca_declarerevolt.error-residentforfewdays', kohana::config('medeur.revolt_declarerevoltdayslimit'));
			return false;
		}
		
		// il Re deve esistere ed essere in carica da almeno 3 giorni
		
		kohana::log('debug', '-> Checking if King has been in charge at least for a month');
		$kingrole = $par[1]->region->get_roledetails( 'king' );		
		if ( is_null( $kingrole) or ! $kingrole ->loaded )
		{
			$message = kohana::lang( 'ca_declarerevolt.error-kingdomhasnoking');
			return false;
		}		
		
		if ( intval ( ( time() - $kingrole -> begin) / ( 24 * 3600 ) ) < 3 )
		{
			$message = kohana::lang( 'ca_declarerevolt.error-kingistooyoung');
			return false;
		}
		
		// cooldown rivolta
		
		$res = ORM::factory('battle') 
			->where( array( 
			'type' => 'revolt',
			'dest_region_id' => $par[1] -> region -> id,
			'timestamp >' => time() - (kohana::config('medeur.revolt_cooldown') * 24 * 3600 ) ) ) ;
				
		
		if ( $res->count_all() > 0 )
		{
			$message = kohana::lang( 'ca_declarerevolt.error-revolt_cooldown', 
				kohana::config('medeur.revolt_cooldown'));
			return false;
		}
		
		// controllo che il char non sia un Re.
		$role = $par[0] -> get_current_role();
		if ( !is_null( $role) and $role->tag == 'king' )
		{
			$message = kohana::lang( 'global.operation_not_allowed');
			return false;
		}
			
		return true;		
		
	}
	
	// nessun controllo particolare
	protected function append_action( $par, &$message ) 	{	}

	function complete_action( $data ) 	{ }
	
	public function execute_action ( $par, &$message) 
	{
		// prende i soldi dal char
		$par[0]->modify_coins( - $this -> cost, 'declarerevolt' ); 
		$par[0]->save();
			
		// istanzia una battaglia
		
		$wd = new Battle_Model();
		$wd -> source_character_id = $par[0] -> id;				
		$wd -> dest_region_id = $par[1] -> region -> id;
		$wd -> source_region_id = $par[1] -> region -> id;
		$wd -> type = 'revolt' ;
		$wd -> status = 'running' ;
		$wd -> timestamp=time();
		$wd -> save();
		
		$wdr = new Battle_Report_Model();
		$wdr -> battle_id = $wd -> id;
		$wdr -> save();
		
		// informa il reggente		
				
		$king_role = $par[1]->region->get_roledetails( 'king' ) ;				
		Character_Event_Model::addrecord( $king_role -> character_id, 'normal', 
			'__events.revoltdeclaration_event' . 
			';' . $par[0] -> name, 'evidence' );
			
		// evento a town crier
		
				
		Character_Event_Model::addrecord( null, 'announcement', 			
			'__events.revoltdeclarationtowncrier_event' . 
			';' . $par[0] -> name .
			';' . $king_role -> character -> name .			
			';' . $par[1] -> region -> kingdom -> new_get_article2() . 
			';__' . $par[1] -> region -> kingdom -> get_name() ,			
			'evidence' );		
		
		// chaina la creazione del battlefield
		
		$a = new Character_Action_Model();			
		$a -> character_id = $par[0] -> id;
		$a -> action = 'createcdb';
		$a -> blocking_flag = false;
		$a -> cycle_flag = false;
		$a -> status = 'running';
		$a -> starttime = time() + kohana::config('medeur.revolt_battlefieldcreationtime') * 3600;
		$a -> endtime = $a -> starttime;		
		$a -> param1 = $wd -> id;
		$a -> save ();				
			
		$message = kohana::lang( 'ca_declarerevolt.info-declarationok') ;			
		return true;
	
	}
	
	public function cancel_action () {}
	
	}
