<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Marketbuyitem_Model extends Character_Action_Model
{
	protected $immediate_action = true;
	protected $totalprice;
	protected $totalbasicprice;
	protected $callablebynpc = true;

	// Effettua tutti i controlli relativi al buyterrain, sia quelli condivisi
	// con tutte le action che quelli peculiari del buyterrain
	// @input: array di parametri
	// par[0]: oggetto struttura
	// par[1]: oggetto char che compra
	// par[2]: oggetto item
	// par[3]: quantity
	
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $message contiene il messaggio di ritorno	
	
	protected function check( $par, &$message )
	{ 
	
	
		
		if ( ! parent::check( $par, $message, $par[1] -> id ) )					
		{ return false; }

		////////////////////////////////////////////////////
		// check: esiste la struttura nel nodo in cui è l' utente?
		//////////////////////////////////////////////////////
		
		if ( $par[0] -> loaded
		and 
			$par[0] -> region_id == $par[1] -> position_id 
		and 			
			$par[0] -> structure_type -> supertype == 'market' 
		) 
			;
		else
		{
			$message = kohana::lang( 'structures.generic_structurenotfound');
			return false;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////
		// check input
		/////////////////////////////////////////////////////////////////////////////////////

		if ( intval( $par[3] ) <= 0 )
		{
			$message = kohana::lang( 'charactions.negative_quantity');
			return false;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////				
		// check parametri
		/////////////////////////////////////////////////////////////////////////////////////

		if ( $par[3] > $par[2]->quantity )
		{
			$message = kohana::lang( 'charactions.market_itemsnotowned');
			return false;						
		}
		
		/////////////////////////////////////////////////////////////////////////////////////
		// non è possibile comprare i propri item
		/////////////////////////////////////////////////////////////////////////////////////

		if ( $par[2]->seller_id == $par[1]->id )
		{
			$message = kohana::lang( 'charactions.marketbuyitem_cannotbuyownitems');
			return false;						
		
		}
		
		/////////////////////////////////////////////////////////////////////////////////////
		// verificare se è vendita privata
		/////////////////////////////////////////////////////////////////////////////////////

		if (!is_null($par[2]->recipient_id) and $par[2]->recipient_id != $par[1]->id )
		{
			$message = kohana::lang( 'charactions.marketbuyitem_cannotbuyreserveditem');
			return false;		
			
		}
		
		/////////////////////////////////////////////////////////////////////////////////////
		// check se il char ha abbastanza soldi
		/////////////////////////////////////////////////////////////////////////////////////
		
		$vat = Region_Model::get_appliable_tax( 
			$par[0] -> region, 
			'valueaddedtax', 
			$par[1] );
		
		$this -> totalprice = Item_Model::compute_realprice( 
			$par[2], 
			$par[1],
			$vat 
			) * $par[3];
			
		$this -> totalbasicprice = $par[2] -> price * $par[3]; 
		
		if (! $par[1] -> check_money( $this -> totalprice ) )		
		{
			$message = kohana::lang( 'charactions.global_notenoughmoney');
			return false;				
		}
		
		/////////////////////////////////////////////////////////////////////////////////////
		// check: il char sta trasportando troppo peso?
		// se l' item è un carretto, niente controllo		
		/////////////////////////////////////////////////////////////////////////////////////
		
		if ( $par[1] -> get_transportableweight() <= 0 
			and $par[2] -> cfgitem -> subcategory != 'cart' )
		{
			$message = kohana::lang('structures.maxtransportableweightreached'); 
			return false;				
		}
		
		/////////////////////////////////////////////////////////////////////////////////////
		// verifica la relazione diplomatica
		/////////////////////////////////////////////////////////////////////////////////////
		
		$dr = Diplomacy_Relation_Model::get_diplomacy_relation( 
			$par[0] -> region -> kingdom_id, 
			$par[1] -> region -> kingdom_id );
		//var_dump( $dr ); exit; 
		if ( !is_null( $dr ) and $dr['type'] == 'hostile')
		{		
			$message = kohana::lang('structures_market.error-hostileaccessdenied'); 
			return false;				
		}
		
		/////////////////////////////////////////////////////////////////////////////////////
		// controlli particolari sull' oggetto
		/////////////////////////////////////////////////////////////////////////////////////
		
		$_message = null; 	
		if ( $par[2] -> buy_do_proprietary_check( $par[3], $_message ) == false )
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
		
		// cancel all private sales after 48 hours
		
		// trace
		Trace_Sale_Model::add( 
			$par[2] -> cfgitem -> id,
			$par[3],
			$this -> totalprice);		
		
		// Importo delle tasse sulla vendita
		$currentsalestax = Region_Model::get_appliable_tax( 
			$par[0] -> region, 'marketsalestax', $par[1] );
		
		
		// venditore
		$seller = ORM::factory('character', $par[2] -> seller_id );
		
		// distribuisci tasse
		
		$_par[0] = $par[0];
		$_par[1] = $par[1];
		$_par[2] = $this -> totalprice;
		$_par[3] = 'good';
		$_par[4] = $par[2];
		$_par[5] = 'marketsale';
		$_par[6] = $this -> totalbasicprice;
		$net = Tax_ValueAdded_Model::apply( $_par );
		
		// invia evento di acquisto al compratore		
		Character_Event_Model::addrecord( 
			$par[1]->id, 
			'normal', '__events.market_boughtitem;' .$par[3] .
				';__' . $par[2] -> cfgitem -> name . 
				';' . $seller -> name . 
				';' . $this -> totalprice ) ;

		// invia evento di acquisto al venditore
		Character_Event_Model::addrecord( 
			$par[2] -> seller_id, 
			'normal', '__events.market_solditem;' .
			$par[3] .
			';__' .	$par[2]->cfgitem->name .
			';'. $par[1]->name . 
			';' . $this -> totalbasicprice .
			';__' . $par[0] -> region -> name				
			) ;
		
		// togli e dai denaro
		
		$seller -> modify_coins( $net, 'marketsale' );
		$par[1] -> modify_coins( - $this -> totalprice, 'marketsale' );
		
		// assegna l' item al char (resetta il prezzo)		
		
		$buyingprice = $par[2] -> price;
		$seller_id = $par[2] -> seller_id;		
		
		// azioni particolari sull oggetto
		$message = '';
		$par[2] -> buy_do_proprietary_action( $message );
		
		// toglie l' item dal mercato
		
		$par[2] -> additem( 'character', $par[1] -> id, $par[3] );			
		$par[2] -> removeitem( "structure", $par[0] -> id, $par[3] );

		// evento al vassallo
		
		$castle = $par[0] -> region -> get_controllingcastle();
		Structure_Event_Model::newadd( 
			$castle -> id,
			'__events.info-itemsoldatmarket' . 
			';' . $par[1] -> name . 
			';' . $this -> totalprice .
			';' . $par[3] . 
			';__' . $par[2] -> cfgitem -> name .			
			';' . $seller -> name .
			';__' . $par[0] -> region -> name );
		
		// evento al compratore

		
				
		// evento per quest
		
		$_par[0] = $par[2]; // item
		$_par[1] = $seller; // venditore
		$_par[2] = $par[3]; // quantità
		$_par[3] = $buyingprice; // quantità
		$_par[4] = $par[1]; // compratore
		$_par[5] = null;
		$_par[6] = null;
		
			
		// event for buyer
		GameEvent_Model::process_event( $par[1], 'boughtitemmarket', $_par );		
		// event for seller
		GameEvent_Model::process_event( $seller, 'boughtitemmarket', $_par );		
		
		
		$message = kohana::lang( 'ca_marketbuyitem.info-boughtitemfrommarket', 
			$par[3], kohana::lang($par[2] -> cfgitem -> name ));
		
		return true;				
		
	}
}
