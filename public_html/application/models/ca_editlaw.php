<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Editlaw_Model extends Character_Action_Model
{

	protected $immediate_action = true;

	// Effettua tutti i controlli relativi all' azione, sia quelli condivisi
	// con tutte le action che quelli peculiari 
	// @input: array di parametri
	// par[0]: oggetto char
	// par[1]: oggetto struttura
	// par[2]: oggetto legge
	// par[3]: nuovo nome
	// par[4]: nuovo testo
	
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $message contiene il messaggio di ritorno	
	
	protected function check( $par, &$message )
	{ 
		
		if ( ! parent::check( $par, $message ) )					
		{ return false; }
		
		
		// se la legge non esiste o è associata ad un nodo diverso dalla struttura -> errore
		if ( !$par[2] -> loaded or $par[0] -> region -> kingdom -> id != $par[2] -> kingdom_id )
		{
			$message = kohana::lang( 'global.operation_not_allowed');
			return false;				
		}
		
		// se 24 ore sono passate non è più possibile editarla
		
		if ( time() > ($par[2] -> timestamp + ( 3 * 24 * 3600 ) ) )
		{
			$message = kohana::lang( 'ca_editlaw.error-lawtooold');
			return false;				
		}
		
		return true;
	}

	
	// nessun controllo particolare
	protected function append_action( $par, &$message )
	{	}

	public function execute_action ( $par, &$message) 
	{
		
		$par[2] -> name = $par[3];
		$par[2] -> description = $par[4];
		$par[2] -> createdby = $par[0] -> id;	
		$par[2] -> signature = $par[0] -> get_signature(true);		
		
		$par[2] -> save();
	
		$message = kohana::lang( 'charactions.castle_editlaw_ok');
	
		return true;

	}
}