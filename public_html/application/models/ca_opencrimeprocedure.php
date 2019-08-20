<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Opencrimeprocedure_Model extends Character_Action_Model
{
	protected $immediate_action = true;
	
	
	// Effettua tutti i controlli 
	// @input: array di parametri (par[0] memorizza l'id dell'oggetto che indosso)
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	// par[0]: Char che scrive
	// par[1]: Char destinatario del mandato d'arresto
	// par[2]: Causale/Testo
	// par[3]: oggetto Struttura
	// par[4]: url processo
	
	protected function check( $par, &$message )
	{ 
		
		// controllo parametri
		
		if ( !$par[0] -> loaded or !$par[3] -> loaded )
		{ $message = kohana::lang('global.operation_not_allowed'); return FALSE; }				
		
		if ( $par[1] -> name == '' or !$par[1] -> loaded )
		{ $message = kohana::lang('global.error-characterunknown'); return FALSE; }				
				
		// è necessario specificare qualcosa nel contratto
		if ( strlen( $par[2] ) == 0 )
		{ $message = kohana::lang('ca_opencrimeprocedure.textismissing'); return FALSE; }
		
		if ( strlen( $par[4] ) == 0 )
		{ $message = kohana::lang('ca_opencrimeprocedure.trialurlismissing'); return FALSE; }
						
		/////////////////////////////////////////////////////
		// Non si può incriminare un Reggente o un Capo 
		// religioso.
		/////////////////////////////////////////////////////
		
		$offenderrole = $par[1] -> get_current_role();
		
		if ( !is_null ( $offenderrole ) and ( 
			$offenderrole->tag == 'king' or $offenderrole->tag == 'religiuosleader' ) 
		)
		{ $message = sprintf(kohana::lang('ca_opencrimeprocedure.notenoughautority', $par[1]->name)); return FALSE; }
		
		// se c'è una rivolta, non si può aprire crime procedure.
		
		$runningbattles = Kingdom_Model::get_runningbattles( $par[0] -> region -> kingdom_id );
		foreach( (array) $runningbattles as $runningbattle )
		{ 
			if ( $runningbattle -> type == 'revolt' )
			{
				$message = kohana::lang('ca_restrain.kingdomisonrevolt'); 
				return FALSE; 
			}
		}	
		
		return true;
	
	}

	
	// nessun controllo particolare
	protected function append_action( $par, &$message )	{	}

	public function complete_action( $data)	{ }
	
	public function execute_action ( $par, &$message ) 
	{	
		
		$crimeprocedure = new Character_Sentence_Model();
		$crimeprocedure -> character_id = $par[1] -> id;
		$crimeprocedure -> issued_by = $par[0] -> id;
		$crimeprocedure -> issuedate = time();
		$crimeprocedure -> text = $par[2];
		$crimeprocedure -> status = 'new';
		$crimeprocedure -> structure_id = $par[3] -> id; 
		$crimeprocedure -> trialurl = $par[4];
		$crimeprocedure -> save();
		
		$message = kohana::lang( 'ca_opencrimeprocedure.open_ok');
		return true;
	}
	
}