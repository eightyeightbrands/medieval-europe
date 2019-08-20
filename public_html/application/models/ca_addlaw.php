<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Addlaw_Model extends Character_Action_Model
{

	protected $immediate_action = true;

	// Effettua tutti i controlli relativi all' azione, sia quelli condivisi
	// con tutte le action che quelli peculiari 
	// @input: array di parametri
	// par[0]: oggetto char
	// par[1]: oggetto struttura	
	// par[2]: nome
	// par[3]: testo
	
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $message contiene il messaggio di ritorno	
	
	protected function check( $par, &$message )
	{ 
		
		if ( ! parent::check( $par, $message ) )					
		{ return false; }
				
		$role = $par[0] -> get_current_role();
		
		if ( $role and $role->tag != 'king' )
		{
			$message = kohana::lang( 'global.operation_not_allowed');
			return false;
		}
		
		return true;
	}

	
	// nessun controllo particolare
	protected function append_action( $par, &$message )
	{	}

	public function execute_action ( $par, &$message) 
	{
		// pubblica annuncio
		$role = $par[0] -> get_current_role();
		
		// Annuncio town-crier
		
		Character_Event_Model::addrecord( 
			null, 
			'announcement', 
			'__events.addlaw_announcement;' . 
			$par[0] -> name . 
			$par[0] -> get_rolename() . 			
			';' . $par[2]);
		
		$law = new Law_Model();		
		$law -> kingdom_id = $par[1] -> region -> kingdom -> id;
		$law -> name = $par[2];
		$law -> description = $par[3];		
		$law -> createdby = $par[0] -> id;	
		$law -> signature = $par[0] -> get_signature(true);		
		$law -> timestamp = time();
		$law -> save();
	
		$message = kohana::lang( 'ca_addlaw.addlaw-ok' );
	
		return true;

	}
}
