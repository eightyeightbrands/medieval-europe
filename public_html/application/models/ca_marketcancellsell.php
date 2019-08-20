<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Marketcancellsell_Model extends Character_Action_Model
{

	protected $immediate_action = true;

	// Effettua tutti i controlli relativi all' azione, sia quelli condivisi
	// con tutte le action che quelli peculiari
	// @input: array di parametri
	// par[0]: oggetto struttura
	// par[1]: oggetto char
	// par[2]: oggetto item
	// par[3]: quantity
	
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $message contiene il messaggio di ritorno	
	
	protected function check( $par, &$message )
	{ 
		
		if ( ! parent::check( $par, $message ) )					
		{ return false; }
		
		// check: esiste la struttura nel nodo in cui è l' utente?
		if ( $par[0] and 
			$par[0] -> region_id == $par[1]->position_id and 			
			$par[0] -> structure_type -> supertype == 'market' ) 
			;
		else
		{
			$message = kohana::lang( 'structures.generic_structurenotfound');
			return false;
		}
					
		
		// non è possibile ritirare item non propri
		if ( $par[2]->seller_id != $par[1]->id )
		{
			$message = kohana::lang( 'charactions.marketbuyitem_cannotbuyownitems');
			return false;								
		}
		
		// la quantità deve essere > 0 e < del totale
		if ( $par[3] < 0 or $par[3] > $par[2] -> quantity )
		{
			$message = kohana::lang( 'global.operation_not_allowed');
			return false;								
		}
		
		
		/////////////////////////////////////////////////////////////////////////////////////
		// check: il char sta trasportando troppo peso?
		// se l' item è un carretto, niente controllo		
		/////////////////////////////////////////////////////////////////////////////////////	
		
		if ( $par[1] -> get_transportableweight() <= 0 and $par[2] -> cfgitem -> subcategory != 'cart' )
		{
			$message = kohana::lang('structures.maxtransportableweightreached'); 
			return false;				
		}
		
		// check proprietari
		$_message = null; 	
		
		if ( $par[2] -> take_do_proprietary_check( $par[3], $_message ) == false )
		{
			$message = kohana::lang( $_message ); 
			return false;				
		}

		return true;
	}

	
	// nessun controllo particolare
	protected function append_action( $par, &$message )
	{	}

	public function execute_action ( $par, &$message) 
	{
				
		$par[2] -> additem ( 'character', $par[1] -> id, $par[3] ); 
		$par[2] -> removeitem ( 'structure', $par[0] -> id, $par[3] ); 			
		
		Character_Event_Model::addrecord( $par[1]->id, 
			'normal', '__events.market_canceleditemsale;'.$par[3] . ';__' . $par[2]->cfgitem->name . ';__' . $par[0] -> region -> name ) ;
		
		$message = kohana::lang('charactions.marketcancellsell_ok'); 
		
		return true;				
		
	}
}
