<?php defined('SYSPATH') OR die('No direct access allowed.');


class CA_Sellstructure_Model extends Character_Action_Model
{

	protected $immediate_action = true;	
	protected $controllingcastle = null;
	
	// Effettua tutti i controlli relativi all' azione, sia quelli condivisi
	// con tutte le action che quelli peculiari
	// @input: array di parametri	
	// par0 : oggetto structure
	// par1 : oggetto char
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli messagei in caso di FALSE	
	
	protected function check( $par, &$message )
	{ 
		
		// Metodo ereditato dal modello Character_Action. Controllo che non ci siano
		// altre azioni in corso				
	
		if ( ! parent::check( $par, $message ) )					
		{ return FALSE; }		
			
		if ( $par[0] -> locked == true )
		{$message = kohana::lang( 'global.operation_not_allowed');	return false;}
		
		$structureinstance = StructureFactory_Model::create( $par[0] -> structure_type -> type, $par[0] -> id );
		
		// la struttura si può vendere?

		if ( $structureinstance -> getIsSellable() == false )
		{
			$message = kohana::lang('global.operation_not_allowed');
			return false;
		}		
			
		// L' utente deve possedere la struttura ed essere nello stesso nodo.
		
		if ( $par[0] -> character_id != $par[1] -> id or $par[0] -> region_id != $par[1]->position_id )		
		{$message = kohana::lang( 'global.operation_not_allowed');	return false;}
				
		// la struttura contiene degli oggetti?				
		
		if ( count($par[0] -> item ) > 0 )
		{ $message = kohana::lang( 'structures.structurecontainsitems'); return false; } 		
		
		return true;
		
	}

	protected function append_action( $par, &$message )	{	}

	public function complete_action( $data ) {	}
		
	public function execute_action ( $par, &$message ) 
	{	
		$structureinstance = 
			StructureFactory_Model::create( $par[0] -> structure_type -> type, $par[0] -> id );
		
		$sellingprice = $par[0] -> structure_type -> price;		
		kohana::log('debug', "-> Original Structure Price: {$sellingprice}");

		/*
		$stat = Character_Model::get_stat_d( $par[1] -> id, 'structurebought');		
		if ($stat -> loaded )
		{
			$days = round((time() - $stat -> stat1)/(24*3600),0);
			kohana::log('debug', "-> Days since the char bought the structure: {$days}");
			
			//$this -> price = round (
			//max( 60, 80 - $days)/100 * $par[0] -> structure_type -> price, 0);
				
			$sellingprice = round (
				min( 80, 60 + $days)/100 * $sellingprice, 0);	
		}
		else
			$sellingprice = 0.8 * $sellingprice;
		*/
		
		$sellingprice = $par[0]-> getSellingprice($par[1], $par[0]->region);
		
		kohana::log('debug', "-> Selling Price: {$sellingprice}");
				
		// dai i soldi al venditore
		
		$par[1] -> modify_coins( $sellingprice, 'structuresold' );
		
		// eventi		
		
		Character_Event_Model::addrecord( $par[1] -> id, 'normal', '__events.charstructuresold' . 
			';__' . $par[0] -> structure_type -> name . 
			';' . $sellingprice );
				
		// pulisco cache		
		$cachetag = '-regionstructures_' . $par[0] -> region_id ;
		My_Cache_Model::delete( $cachetag );
		
		// distruggi la struttura
		$structureinstance -> destroy();
		
		$message = kohana::lang('ca_sellstructure.info-structuresold', $sellingprice ); 		
		
		return true;
		
	}
	
}