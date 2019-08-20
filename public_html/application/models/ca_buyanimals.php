<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Buyanimals_Model extends Character_Action_Model
{

	protected $immediate_action = true;
	protected $price = 0;
	protected $basicprice = 0;
	protected $structure = null;
	
	// Effettua tutti i controlli relativi al buybreeding, sia quelli condivisi
	// con tutte le action che quelli peculiari del buybreeding
	// @input: array di parametri
	//  par[0] = oggetto char
	//  par[1] = tipo allevamento
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $message contiene il messaggio di ritorno
	
	protected function check( $par, &$message )
	{ 
	
		if ( ! parent::check( $par, $message ) )					
		{ return false; }
		
		// la regione ha effettivamente questa risorsa?
		
		$currentregion = ORM::factory('region', $par[0] -> position_id );
		
		$this -> structure = $currentregion -> get_structure( $par[1], 'type' );		
		
		if ( is_null( $this -> structure ) )
		{ $message = kohana::lang('global.operation_not_allowed'); return FALSE; }
				
		/////////////////////////////////////////////////////////////////////////////////////
		// verifica la relazione diplomatica
		/////////////////////////////////////////////////////////////////////////////////////
		
		$rd = Diplomacy_Relation_Model::get_diplomacy_relation( $this -> structure -> region -> kingdom_id, $par[0] -> region -> kingdom_id );
		
		if
		(
			!is_null( $rd ) and 
			( $rd['type'] == 'hostile' or $rd['type'] == 'neutral' )
		)
		{
			$message = kohana::lang('structures_market.error-hostileaccessdenied'); 
			return false;				
		}
		
		// prezzo con tasse
		$structureinstance = StructureFactory_Model::create( $par[1] );	
		
		$this -> price = $structureinstance -> getPrice( $par[0], $currentregion); 		
		$this -> basicprice = $structureinstance -> getBaseprice(); 		
		
		//var_dump('Prezzo senza tasse: ' . $this -> basicprice); 
		//var_dump('Prezzo con tasse: ' . $this -> price); 
		
		
		
		// controllo che la regione non sia indipendente
		
		if ( $this -> structure -> region -> is_independent() )
		{ $message = kohana::lang('("charactions.regionisindependent'); return FALSE; }		

		// Controllo che il char abbia i soldi necessari all'acquisto
		
		if (! $par[0] -> check_money( $this -> price ) )
			{ $message = kohana::lang('structures.breeding_error2'); return FALSE; }
		
		// controllo che ci siano le necessarie risorse
		
		$resource_status = $this -> structure -> check_resource_status( 
			$this -> get_resourcetype( $this -> structure ), 
			$this -> structure -> structure_type -> attribute2  );		
		
		if ( $resource_status == false )
			{ $message = kohana::lang('ca_buyanimals.resourceisdepleted'); return FALSE; }		
				
		return true;
		
		
		
	}
	
	/** 
	* torna la risorsa associata con la struttura
	*/
	
	private function get_resourcetype( $structure )
	{
		$resource = array (
			'breeding_cow_region' => 'cows',
			'breeding_sheep_region' => 'sheeps',
			'breeding_pig_region' => 'pigs',
			'breeding_bee_region' => 'bees',
			'breeding_silkworm_region' => 'silkworms'			
			);
		
		return $resource[ $structure -> structure_type -> type ] ;
	
	}
	
	// nessun controllo particolare
	
	protected function append_action( $par, &$message )	{	}

	public function execute_action ( $par, &$message ) 
	{
		
		$breeding = StructureFactory_Model::create( $this -> structure -> structure_type -> attribute1, null );
		
		$breeding -> region_id = $this -> structure -> region_id;
		
		//////////////////////////////////////
		// Assegno la struttura al char.
		//////////////////////////////////////
		
		$breeding -> character_id = $par[0] -> id;
		$breeding -> attribute1 = $this -> structure -> structure_type -> attribute2;
		$breeding -> attribute2 = 100;
		$breeding -> attribute3 = 0;
		$breeding -> attribute4 = 0;
		$breeding -> save();
		
		//////////////////////////////////////
		// toglie la quantità alle risorse
		//////////////////////////////////////
		
		foreach ( $this -> structure -> structure_resource as $resource )
		if ( $resource -> resource == $this -> get_resourcetype( $this -> structure ) )	
		{
			$quantity = $this -> structure -> structure_type -> attribute2;								
			$resource -> modify_quantity( - $quantity );
			$resource -> save();
		}		
		
		// toglie il denaro al char.
		
		$par[0] -> modify_coins( - $this -> price, 'buyfarm' );
		$par[0] -> save();	
		
		// applica la tassa				
		
		$_par[0] = $this -> structure;
		$_par[1] = $par[0];
		$_par[2] = $this -> price;
		$_par[3] = 'license';
		$_par[4] = null;
		$_par[5] = 'breedinglicense';
		$_par[6] = $this -> basicprice;
		
		$net = Tax_ValueAdded_Model::apply( $_par );
				
		//////////////////////////////////////
		// Istanzio la growbreeding che gestirà
		// la vita dell'allevamento
		//////////////////////////////////////
		
		$error = "";
		$ca = Character_Action_Model::factory("growbreeding");
		$ca -> do_action( $breeding,  $error );
		
		//////////////////////////////////////
		// invia evento di acquisto al compratore
		//////////////////////////////////////
		
		Character_Event_Model::addrecord( $par[0] -> id, 
			'normal', '__events.boughtbreeding' . ';' . 
			$this -> price . ';__' . $breeding -> structure_type -> name  ) ;
		
		// Cache
		
		My_Cache_Model::delete('-cfg-regions-resources');
		
		
		$message = kohana::lang('charactions.breedingbuy_ok', $this -> price );		
		
		return true;
	}
}
