<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Orderconquerir_Model extends Character_Action_Model
{

	protected $immediate_action = true;
	protected $captain = null;
	protected $region = null;
	
	// Effettua tutti i controlli relativi all' azione, sia quelli condivisi
	// con tutte le action che quelli peculiari 
	// @input: array di parametri
	// par[0]: oggetto char
	// par[1]: nome del capitano
	// par[2]: nome regione da conquistare
	// par[3]: note
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $message contiene il messaggio di ritorno	
	
	protected function check( $par, &$message )
	{ 
		
		if ( ! parent::check( $par, $message ) )					
		{ return false; }
		
		// controllo parametri		
		
		$this -> captain = ORM::factory('character') -> where ( 'name', $par[1]) -> find();		
		if ( !$this -> captain -> loaded )
		{$message = kohana::lang( 'global.error-characternotfound');	return false;}
		
		$this -> region = ORM::factory('region') -> 
			where ( 'name', 'regions.' . strtolower($par[2]) ) -> find();		
			
		if ( !$this -> region -> loaded )
		{$message = kohana::lang( 'global.error-regionnotfound');return false;}		
		
		// il char ha 1 pezzo di carta e la ceralacca?		
		if ( ! Character_Model::has_item( $par[0]->id, 'paper_piece', 1 )  
			or ! Character_Model::has_item( $par[0]->id, 'waxseal', 1 )) 
		{ $message = kohana::lang('charactions.paperpieceandwaxsealneeded'); return FALSE; }
		
		// Il regno è in guerra?
		$data = null;				
		$iskingdomfighting = Kingdom_Model::is_fighting( $par[0] -> region -> kingdom_id, $data ) ;		
		if ( $iskingdomfighting == true )
		{ $message = kohana::lang( 'ca_declarewaraction.error-kingdomisonwar' ) ; return false; }
		
		return true;
	}
	
	// nessun controllo particolare
	protected function append_action( $par, &$message )
	{	}

	public function execute_action ( $par, &$message) 
	{
		// genera l' oggetto scroll_order_ir
		
		$order = Item_Model::factory( null, 'scroll_conquerirorder' );
		
		$order -> param1 = $par[0] -> id . ';' . $this -> captain -> id . ';' . 
			$this -> captain -> name . ';' . $this -> region -> id . ';' . $this -> region -> name . ';' . (time() + (7*24*3600)) ;
		$order -> param2 = $par[3];
		
		
		$order -> additem( 'character', $par[0] -> id, 1 ) ; 
		
		////////////////////////////////////////////////////////////
		// togli il pezzo di carta
		////////////////////////////////////////////////////////////
		
		$paper_piece = Item_Model::factory( null, 'paper_piece' );
		$paper_piece -> removeitem( "character", $par[0]->id, 1 );
		
		$waxseal = Item_Model::factory( null, 'waxseal' );
		$waxseal -> removeitem( "character", $par[0]->id, 1 );
				
		$message = kohana::lang( 'structures_royalpalace.order_ok' ); 
		return true;

	}
	
}
