<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Deletelaw_Model extends Character_Action_Model
{

	protected $immediate_action = true;

	// Effettua tutti i controlli relativi all' azione, sia quelli condivisi
	// con tutte le action che quelli peculiari 
	// @input: array di parametri
	// par[0]: oggetto char
	// par[1]: oggetto struttura
	// par[2]: oggetto legge
	
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $message contiene il messaggio di ritorno	
	
	protected function check( $par, &$message )
	{ 
		
		if ( ! parent::check( $par, $message ) )					
		{ return false; }
		
		// il chiamante deve
		// - vassallo, e governare la struttura
		// - la legge deve esistere		
		
		$role = $par[0]->get_current_role();
		
		if ( $role and $role->tag != 'king' )
		{
			$message = kohana::lang( 'global.operation_not_allowed');
			return false;
		}
		
		// se la legge non esiste o è associata ad un nodo diverso dalla struttura -> errore
		if ( !$par[2]->loaded or $par[2] -> kingdom_id != $par[1] -> region -> kingdom -> id )
		{
			$message = kohana::lang( 'global.operation_not_allowed');
			return false;				
		}
		
		/*
		if ( $appointer_role->region->id != Character_Model::get_info( Session::instance()->get('char_id') ) -> position_id )
		{
			$message = kohana::lang( 'global.operation_not_allowed');
			return false;				
		}
		*/
		return true;
	}

	
	// nessun controllo particolare
	protected function append_action( $par, &$message )
	{	}

	public function execute_action ( $par, &$message) 
	{
		// pubblica annuncio
		$role = $par[0]->get_current_role();
		
		$par[2]->delete();
	
		$message = kohana::lang( 'charactions.castle_deletelaw_ok');
	
		return true;

	}
}
