<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Marketsellitem_Model extends Character_Action_Model
{

	protected $immediate_action = true;
	protected $recipient = null;

	// Effettua tutti i controlli relativi al buyterrain, sia quelli condivisi
	// con tutte le action che quelli peculiari del buyterrain
	// @input: array di parametri
	// par[0]: oggetto struttura
	// par[1]: oggetto char
	// par[2]: oggetto item
	// par[3]: quantity
	// par[4]: sellingprice
	// par[5]: tassa
	// par[6]: nome char destinatario della vendita
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $message contiene il messaggio di ritorno	
	
	protected function check( $par, &$message )
	{ 
		
		if ( ! parent::check( $par, $message ) )					
		{ return false; }
		
		$par[3] = intval( $par[3] );
		
		// check input		
		if ( $par[3] <= 0 )
		{
			$message = kohana::lang( 'charactions.negative_quantity');
			return false;
		}
				
		// Recipient
		
		if (!empty($par[6]))
		{
			
			$this -> recipient = ORM::factory('character')
				-> where (
					array(
					'name' => $par[6]
					)) 
				-> find();
			
			if ( !$this -> recipient -> loaded )
			{
				$message = kohana::lang( 'global.error-characterunknown');
				return false;				
			}
			
			if( $this -> recipient -> id == $par[1] -> id )
			{
				$message = kohana::lang( 'ca_marketsellitem.error-cannotselltoitself');
				return false;				
				
			}
			
		}
		
		// check: esiste la struttura nel nodo in cui è l' utente?
		if ( $par[0] and 
			$par[0] -> region_id == $par[1]->position_id and 			
			$par[0] -> structure_type -> parenttype == 'market' ) 
			;
		else
		{
			$message = kohana::lang( 'structures.generic_structurenotfound');
			return false;
		}
		
		//check: il prezzo è > 0?
		
		if ( $par[4] <= 0 )
		{
			$message = kohana::lang( 'items.pricelessthanzero');
			return false;				
		}
		//print kohana::debug( $par[3]) ; exit();
		//check: la quantità da vendere deve essere <= alla disponibilità
		if ( $par[3] > $par[2]->quantity )
		{
			$message = kohana::lang( 'charactions.itemsquantitynotowned');
			return false;				
		}
		
		// check se il char ha effettivamente gli item
		if ( $par[2]->character_id != $par[1]->id )
		{
			$message = kohana::lang( 'charactions.itemsquantitynotowned');
			return false;				
		}
		
		// se l' item è locked non può essere venduto.
		
		if ( $par[2] -> locked )
		{
			$message = kohana::lang( 'charactions.marketsellitem_itemislocked');
			return false;	
		}
		
		// check sui valori imputati; la moltiplicazione del numero di oggetti e il prezzo
		// non può superare 1 milione.
		if ( $par[3] * $par[4] > 1000000 )
		{
			$message = kohana::lang( 'structures.maxsellingpricereached');
			return false;		
		}
		
		/////////////////////////////////////////////////////////////////////////////////////
		// verifica la relazione diplomatica
		/////////////////////////////////////////////////////////////////////////////////////
		
		$dr = Diplomacy_Relation_Model::get_diplomacy_relation( 
			$par[0] -> region -> kingdom_id, 
			$par[1] -> region -> kingdom_id );
		
		if ( !is_null( $dr ) and $dr['type'] == 'hostile')
		{
			$message = kohana::lang('structures_market.error-hostileaccessdenied'); 
			return false;				
		}
		
		/////////////////////////////////////////////////////////////////////////////////////
		// controlli particolari sull' oggetto
		/////////////////////////////////////////////////////////////////////////////////////
		$_message=null;
		if ( $par[2] -> sell_do_proprietary_check( $_message ) == false )
		{
			$message = kohana::lang( $_message ); 
			return false;				
		}
		
		return true;
	}

	
	// nessun controllo particolare
	protected function append_action( $par, &$message )
	{}

	public function execute_action ( $par, &$message) 
	{
			
		// assegna l' item al mercato
		
		$par[2] -> seller_id = $par[1] -> id;
		if (!is_null($this -> recipient ))
			$par[2] -> recipient_id = $this -> recipient -> id;
		$par[2] -> price = $par[4]; 
		$par[2] -> quantity = $par[3]; 
		$par[2] -> tax_citizen = $par[5] -> citizen;
		$par[2] -> tax_neutral = $par[5] -> neutral;
		$par[2] -> tax_friendly = $par[5] -> friendly;
		$par[2] -> tax_allied = $par[5] -> allied;
		$par[2] -> salepostdate = time();
		
		$par[2] -> additem( 'structure', $par[0] -> id, $par[3], false);				
		$par[2] -> removeitem( 'character', $par[1] -> id, $par[3] );
		
		// evento per quest		
		
		$_par[0] = $par[2]; // item
		$_par[1] = $par[1]; // venditore
		$_par[2] = $par[3]; // quantità
		$_par[3] = $par[4]; // prezzoq
		$_par[4] = null;
		$_par[5] = null;
		$_par[6] = null;
				
		GameEvent_Model::process_event( $par[1], 'sellitemmarket', $_par );
		
		if ( is_null ($this -> recipient) ) 		
		{			
			Character_Event_Model::addrecord( 
				$par[1] -> id, 
				'normal', 
				'__events.market_posteditemforsale' .
				';' . $par[3] . 
				';__' . $par[2]->cfgitem -> name .
				';' . $par[4] . 
				';__' . $par[0] -> region -> name ) ;
		}
		else		
		{
			$recipient = ORM::factory('character', $this -> recipient -> id );
			
			Character_Event_Model::addrecord( 
				$par[1] -> id, 
				'normal', 
				'__events.market_posteditemforprivatesale' .
				';' . $recipient -> name . 				
				';' . $par[3] . 
				';__' . $par[2] -> cfgitem -> name .
				';' . $par[4] . 
				';__' . $par[0] -> region -> name ) ;
				
			Character_Event_Model::addrecord( 
				$recipient -> id, 
				'normal', 
				'__events.market_posteditemforprivatesalerecipient' .
				';' . $par[1] -> name . 				
				';' . $par[3] . 
				';__' . $par[2] -> cfgitem -> name .
				';' . $par[4] . 
				';__' . $par[0] -> region -> name ) ;	
		}
		
		
		$message = 
			kohana::lang('ca_marketsellitem.info-solditem',
				$par[3], 
				kohana::lang($par[2] -> cfgitem -> name),
				$par[4] );
			
		return true;				
		
	}
}
