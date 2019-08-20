<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Editannouncement_Model extends Character_Action_Model
{

	protected $immediate_action = true;

	// Effettua tutti i controlli relativi all' azione, sia quelli condivisi
	// con tutte le action che quelli peculiari 
	// @input: array di parametri
	// par[0]: oggetto char
	// par[1]: oggetto announcement
	// par[2]: titolo
	// par[3]: testo
	
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $message contiene il messaggio di ritorno	
	
	protected function check( $par, &$message )
	{ 
		
		if ( ! parent::check( $par, $message ) )					
		{ return false; }		
		
		$role = $par[0] -> get_current_role();		
		
		// controlli
				
		if ( $role -> tag != 'vassal' and $role -> tag != 'king' )
		{	$message = kohana::lang('global.operation_not_allowed') ; return false; }
		
		// il re può editare solo se dello stesso regno
		if ( $role -> tag == 'king' and $role -> region -> kingdom -> id != $par[0] -> region -> kingdom -> id )
		{	$message = kohana::lang('global.operation_not_allowed'); return false; }	
		
		
		// il vassallo può editare solo se controlla la regione ed il tipo di annuncio è region
		$controllingvassal = $par[1] -> region -> get_controllingvassal(); 
		
		if ( $role -> tag == 'vassal' and ( $par[1] -> type != 'region' or $par[0] -> id != $controllingvassal -> id ) )
			{	$message = kohana::lang('global.operation_not_allowed'); return false; }	
				
		return true;
	}

	
	// nessun controllo particolare
	protected function append_action( $par, &$message )
	{	}

	public function execute_action ( $par, &$message) 
	{
		
		$par[1]->title = $par[2];
		$par[1]->text = $announcement->text = wordwrap( $par[3], 120, "\n", true);
		$par[1]->save();	
		$message = kohana::lang( 'charactions.castle_editannouncement_ok');
	
		return true;

	}
}
