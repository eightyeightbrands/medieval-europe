<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Becomeking_Model extends Character_Action_Model
{

	protected $immediate_action = true;

	// Effettua tutti i controlli relativi all' azione, sia quelli condivisi
	// con tutte le action che quelli peculiari 
	// @input: array di parametri
	// par[0]: oggetto char
	// par[1]: oggetto struttura
	
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $message contiene il messaggio di ritorno	
	
	protected function check( $par, &$message )
	{ 
		
		if ( ! parent::check( $par, $message ) )					
		{ return false; }
		
		//la struttura deve essere il palazzo reale
		if ( $par[1]->structure_type->parenttype != 'royalpalace' )
		{
			$message = kohana::lang( 'global.operation_not_allowed');
			return false;
		}		
		
		// la struttura non deve avere un char associato, ossia il
		// posto di re deve essere vacante
		if ( !is_null ($par[1]->character_id ) )
		{
			$message = kohana::lang( 'ca_becomeking.kingrole_novacant');
			return false;
		}
		
		// E' possibile diventare Re solo nel regno di appartenenza.
		if ( $par[0]-> region -> kingdom -> id != $par[1]->region->kingdom->id )
		{
			$message = kohana::lang( 'ca_becomeking.kingrole_samekingdom');
			return false;
		}
		
		// Il personaggio ha abbastanza soldi?
		if ( $par[0]->get_item_quantity( 'silvercoin') < $par[1]->region->kingdom->get_regent_cost() )
		{
			$message = kohana::lang( 'charactions.global_notenoughmoney');
			return false;
		}
		
		// Il personaggio ha le corrette caratteristiche?
		if ( Character_Role_Model::check_eligibility( $par[0], 'king', null, $message ) == false )
		{			
			return false;
		}

		// Il candidato ha già un ruolo?
		if ( $par[0] -> get_current_role() )
		{
			$message = kohana::lang( 'charactions.royalp_candidateisincharge');
			return false;		
		}
		
		return true;
		
	}

	
	// nessun controllo particolare
	protected function append_action( $par, &$message )
	{	}

	public function execute_action ( $par, &$message) 
	{
		
		$par[1] -> region -> kingdom -> crown_king( $par[0] );
		
		$regent_cost =  $par[1] -> region -> kingdom -> get_regent_cost();
		
		// togli i denari al char
		$par[0]->modify_coins( - $regent_cost, 'becomeking' );
		$par[0]->save();
		
		// 40% va perso
		$regent_cost = intval( $regent_cost * 60 / 100 );
		
		kohana::log('debug', 'Total money to distribute: ' . $regent_cost ); 
		
		// find all kingdom castles
		
		$castles = $par[1] -> region -> kingdom -> get_structures( 'castle' ); 
		$sumforeachcastle = $regent_cost / count($castles); 
		
		kohana::log('debug', 'Money to distributeto each castle: ' . $sumforeachcastle ); 
		
		foreach ( $castles as $castle )
		{
			
			$s = ORM::factory('structure', $castle -> id );
			$s -> modify_coins( $sumforeachcastle, 'becomeking' );
			
			 
			Structure_Event_Model::newadd( 
			$castle -> id, 
				'__events.crowningmoneyreceived' . ';' . $sumforeachcastle
			);
			
		}
		
		$message = kohana::lang( 'ca_becomeking.becomeking_ok' );
		return true;

	}
}
