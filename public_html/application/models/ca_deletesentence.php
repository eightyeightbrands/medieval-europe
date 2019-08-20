<?php defined('SYSPATH') OR die('No direct access allowed.');
class CA_Deletesentence_Model extends Character_Action_Model
{
	protected $immediate_action = true;
	
	// Effettua tutti i controlli relativi alla wear, sia quelli condivisi
	// con tutte le action che quelli peculiari della wear
	// @input: array di parametri (par[0] memorizza l'id dell'oggetto che indosso)
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	// par[0]: oggetto char del magistrato
	// par[1]: oggetto sentenza
	// par[2]: oggetto struttura della magistratura
	
	protected function check( $par, &$message )
	{ 
		if ( ! parent::check( $par, $message ) )					
		{ return false; }
		// controllo parametri
		
		if ( ! $par[1]->loaded or !$par[2]->loaded)
		{ $message = kohana::lang('global.operation_not_allowed'); return FALSE; }
		
		// controllo che il player controlli la struttura
		
		if ( $par[2] -> allowedaccess( $par[0], $par[2] -> structure_type -> supertype ) == false )		
		{ $message = kohana::lang('global.operation_not_allowed'); return FALSE; }
		
		//controllo stato della sentenza
		if ( $par[1]->status != 'new' )
		{ $message = kohana::lang('charactions.deletesentence_notnew'); return FALSE; }
		
		return true;
	}

	
	// nessun controllo particolare
	protected function append_action( $par, &$message )
	{	}

	public function complete_action( $data)
	{ }
	
	// Sazia il char
	// @input: par[0] memorizza l'id dell'oggetto che sto mangiando
	public function execute_action ( $par, &$message ) 
	{
	
		$par[1]->status = 'deleted';
		$par[1]->save();
				
		// manda email
		$targetchar = ORM::factory("character", $par[1]->character_id);
		$role = $par[0]->get_current_role();
		
		/*
		// manda email a sceriffo
		
		$sheriffrole = $region->get_roledetails( 'sheriff') ;
		//print kohana::debug( $sheriff)	;exit();
				
		$subject = kohana::lang('charactions.publishsentence_messagesubject');
		$body = $a->text;
		$m = new Message_Model();
		if ( !is_null($sheriffrole))
			$m->send(  $par[0]->id, $sheriffrole->character_id, $subject, $body );				
		
		
		*/
		// manda email a destinatatio
			
		$message = kohana::lang( 'charactions.deletesentence_ok');
		return true;
	}
}
