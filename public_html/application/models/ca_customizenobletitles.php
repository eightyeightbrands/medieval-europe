<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Customizenobletitles_Model extends Character_Action_Model
{
	// Flag azione immediata
	protected $immediate_action = true;

	// Effettua tutti i controlli relativi all' azione, sia quelli condivisi
	// con tutte le action che quelli peculiari 
	// @input: array di parametri
	// $par[0] = Characther che compie l'azione
	// $par[1] = Struttura da cui viene lanciata la customize
	// $par[2] = Titolo originale
	// $par[3] = Titolo customizzato maschile
	// $par[4] = Titolo customizzato femminile
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $message contiene il messaggio di ritorno	
	
	protected function check( $par, &$message )
	{ 
		if ( ! parent::check( $par, $message ) )					
		{ return false; }
		
		// Prelevo l'attuale ruolo del char
		$role = $par[0] -> get_current_role();
		
		// Check:
		// 1) Il ruolo esiste
		// 2) Il ruolo non è quello di King
		if
		(
			$role and 
			$role->tag != 'king'
		)
		{
			// Ritorno con un messaggio di errore
			// Operazione non permessa
			$message = kohana::lang( 'global.operation_not_allowed');
			return false;
		}
		
		// Tutti i controlli sono stati superati
		return true;
	}

	
	// Nessun controllo particolare
	protected function append_action( $par, &$message ) {}

	
	public function execute_action ( $par, &$message) 
	{
		$knt = new Kingdom_Nobletitle_Model();
		$result = $knt->insert_or_update($par[1]->region->kingdom_id, $par[2], $par[3], $par[4], $par[5]);
		
		if ($result)
		{
			$message = kohana::lang('ca_customizenobletitles.customizetitle-ok');
			return true;
		}
		else
		{
			$message = kohana::lang('ca_customizenobletitles.custom-image-error');
			return false;
		}		
	}
}
