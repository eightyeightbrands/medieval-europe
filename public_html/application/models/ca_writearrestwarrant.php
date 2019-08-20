<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Writearrestwarrant_Model extends Character_Action_Model
{
	protected $immediate_action = true;
	
	
	// Effettua tutti i controlli 
	// @input: array di parametri (par[0] memorizza l'id dell'oggetto che indosso)
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	// par[0]: Char che scrive
	// par[1]: struttura
	// par[2]: procedura di incriminazione
	
	protected function check( $par, &$message )
	{ 
		
		// controllo parametri
		
		if ( !$par[0] -> loaded or !$par[1] -> loaded or !$par[2] -> loaded)
		{ $message = kohana::lang('global.operation_not_allowed'); return FALSE; }				
				
		//controllo che la procedura di incriminazione sia aperta.
		if ( $par[2] -> status != 'new' )
		{ $message = kohana::lang('ca_writearrestwarrant.invalidcrimeprocedure'); return FALSE; }				
		
		// il giocatore ha gli item nesessari 
		if ( ! Character_Model::has_item( $par[0]->id, 'paper_piece', 1 ) 
			or ! Character_Model::has_item( $par[0]->id, 'waxseal', 1 ) ) 
		{ $message = kohana::lang('charactions.paperpieceandwaxsealneeded'); return FALSE; }				
		
		return true;
	
	}

	
	// nessun controllo particolare
	protected function append_action( $par, &$message )	{	}

	public function complete_action( $data)	{ }
	
	public function execute_action ( $par, &$message ) 
	{	
		
		$arrestwarrant = Item_Model::factory( null, 'scroll_arrestwarrant' );
		$criminal = ORM::factory('character', $par[2] -> character_id ); 
		
		$arrestwarrant -> param1 = $par[2] -> id . ';' . $par[0] -> id . ';' . $criminal -> id  
			. ';' . $par[0] -> name . ';' . $criminal -> name . ';' . (time());	
				
		$arrestwarrant -> param2 = $par[2] -> text;			
		
		$arrestwarrant -> param3 = $criminal -> id ;
		
		$arrestwarrant -> additem( 'character', $par[0] -> id, 1 ) ; 
		
		////////////////////////////////////////////////////////////
		// togli il pezzo di carta
		////////////////////////////////////////////////////////////
		
		$paper_piece = Item_Model::factory( null, 'paper_piece' );
		$paper_piece -> removeitem( "character", $par[0]->id, 1 );
		
		$waxseal = Item_Model::factory( null, 'waxseal' );
		$waxseal -> removeitem( "character", $par[0]->id, 1 );
		
		$message = kohana::lang( 'ca_writearrestwarrant.written_ok');
		return true;
	}
	
}
