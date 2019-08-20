<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Addannouncement_Model extends Character_Action_Model
{

	protected $immediate_action = true;

	// Effettua tutti i controlli relativi all' azione, sia quelli condivisi
	// con tutte le action che quelli peculiari 
	// @input: array di parametri
	// par[0]: oggetto char
	// par[1]: oggetto struttura	
	// par[2]: titolo
	// par[3]: testo
	
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $message contiene il messaggio di ritorno	
	
	protected function check( $par, &$message )
	{ 
		
		
		$role = $par[0]->get_current_role();
		
		if ( !$par[1]->loaded or $par[1]->region->id != $par[0]->position_id or 
			( $role->tag != 'vassal' and $role->tag != 'king' ) )
		{
			$message = kohana::lang( 'global.operation_not_allowed');
			return false;
		}
		
		// no word long then 60 character (breaks layout) 
		
		return true;
	}

	
	// nessun controllo particolare
	protected function append_action( $par, &$message )
	{	}

	public function execute_action ( $par, &$message) 
	{
	
		$role = $par[0]->get_current_role() ;			
		$announcement = new Region_Announcement_Model();				
		
		if ( $role -> tag == 'king' )
			$type = 'kingdom';
		else
			$type = 'region'; 
			
		$announcement->id = null;
		$announcement->type = $type;
		$announcement->region_id = $par[1]->region->id;
		$announcement->character_id = $par[0]->id;
		$announcement->title = $par[2];
		$announcement->subtype = 'announcement'; 
		$announcement->text = wordwrap( $par[3], 120, "\n", true );
		$announcement->signature = $par[0]->signature;
		$announcement->timestamp = time();
		$announcement->save();
	
		
		$message = kohana::lang( 'charactions.castle_addannouncement_ok');
	
		return true;

	}
}
