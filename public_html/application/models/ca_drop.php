<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Drop_Model extends Character_Action_Model
{
	protected $immediate_action = true;
	
	// check
	// @input: parametri
	//  - par[0]: oggetto struttura dove si vuole droppare l' item 
	//  - par[1]: item da droppare
	//  - par[2]: quantità
	//  - par[3]: oggetto char che fa l' azione
	
	protected function check( $par, &$message )
	{ 
		
		if ( ! parent::check( $par, $message ) )					
			return false;
	
		// check: la struttura esiste ed il char è proprietario?
		// eccezione per il battlefield
		
		if ( $par[0] -> structure_type -> supertype != 'battlefield' and (!$par[0]->loaded ) )
		{
			$message = kohana::lang('global.operation_not_allowed'); 
			return false;
		}
		
		if ( $par[2] < 0 ) 
		{
			$message = kohana::lang('charactions.negative_quantity'); 
			return false;
		}
		
		//check: il char effettivamente ha gli item nella quantità specificata?
		//var_dump($par[1]); exit;
		if ( $par[2] > $par[1] -> quantity or $par[1] -> character_id != $par[3] -> id )
		{ $message = kohana::lang( 'structures.generic_itemsnotowned'); return false; }
				
		// check: il peso degli item supera la capacità di immagazzinamento della struttura?
		
		$itemsweight = $par[1] -> cfgitem -> weight * $par[2];
		$storableweight = $par[0] -> get_storableweight( $par[0] );
		
		if ( $storableweight < $itemsweight )
		{
			$message = kohana::lang('charactions.drop_storablecapacityfinished'); 
			return false;		
		}
		
		// se l' oggetto è locked,  non si può lasciare in una struttura.
		//var_dump($par[0]-> structure_type); exit;
		if ( $par[1] -> locked and $par[0] -> structure_type -> subtype != 'player' )			
		{ $message = kohana::lang('charactions.marketsellitem_itemislocked'); return FALSE; }		
		// si può droppare l' oggetto?
		
		if ( $par[1] -> cfgitem -> droppable == false )
		{
			$message = kohana::lang('structures.generic_itemsnotdroppable'); 
			return false;
		}
		
		return true;
	}
	
	protected function append_action( $par, &$message ){	}
	
	public function execute_action ( $par, &$message ) 
	{
		
		// Update transazionale
		kohana::log('debug', 'Starting transaction.');
		
		$db = Database::instance();		

		if ($par[1] -> cfgitem -> tag == 'silvercoin' )		
		{
			$par[0] -> modify_coins( $par[2], 'droptostructure' );			
			$par[3] -> modify_coins( -$par[2], 'droptostructure' );			
		}
		elseif ($par[1] -> cfgitem -> tag == 'coppercoin' )		
		{
			$par[0] -> modify_coins( $par[2]/100 , 'droptostructure');			
			$par[3] -> modify_coins( -$par[2]/100, 'droptostructure' );			
		}
		elseif ($par[1] -> cfgitem -> tag == 'doubloon' )		
		{
			$par[0] -> modify_doubloons( $par[2], 'droptostructure' );			
			$par[3] -> modify_doubloons( -$par[2], 'droptostructure' );			
		}			
		else
		{
			kohana::log('debug', 'Adding item...');
			$par[1] -> additem( "structure", $par[0] -> id, $par[2] );			
			kohana::log('debug', 'Removing item...');
			$ret_2 = $par[1] -> removeitem( "character", $par[3]->id, $par[2] );
		}
		
		$par[3] -> save();
		$par[0] -> save();
		
		// evento struttura
		
		$text = '__events.structuredrop;'. $par[3]->name . ';' . $par[2] . ';' . '__' . $par[1]->cfgitem->name ;
		Structure_Event_Model::newadd( $par[0], $text );
	
		// evento al depositante
		
		Character_Event_Model::addrecord(
			$par[3] -> id, 
			'normal', 
			'__events.playerdrop' . 
			';' . $par[2] . 
			';__' . $par[1] -> cfgitem -> name . 
			';__' . $par[0] -> structure_type -> name . 
			';'   . $par[0] -> character -> name . 
			';__' . $par[0] -> region -> name);
						
		
		$message = kohana::lang('charactions.drop_ok'); 
			
		// evento struttura
		
		return true;
	
	}
	
}
