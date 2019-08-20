<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Donate_Model extends Character_Action_Model
{
	protected $immediate_action = true;
	protected $enabledifrestrained = true;

	// check
	// @input: parametri
	//  - par[0]: struttura a cui si vuole donare gli item
	//  - par[1]: item
	//  - par[2]: quantità
	//  - par[3]: char che fa l' azione
	
	protected function check( $par, &$message )
	{ 
		if ( Character_Model::is_imprisoned( $par[3] -> id ) == false 
			and 
			! parent::check( $par, $message ) )					
			return false;
		
		// check input
		
		if ( !$par[0] -> loaded or !$par[1] -> loaded ) 
		{ $message = kohana::lang( 'global.operation_not_allowed'); return false; }
		
		if ( intval( $par[2] ) <= 0 ) 
		{	$message = kohana::lang( 'charactions.negative_quantity'); return false; }
		
		// l' oggetto è locked?
		
		if ( $par[1] -> locked )
		{ $message = kohana::lang('charactions.marketsellitem_itemislocked'); return FALSE; }				
				
		// il giocatore è nella stessa regione della struttura?		
		if ( $par[0] -> region_id != $par[3] -> position_id )
		{ $message = kohana::lang( 'global.operation_not_allowed'); return false; }
		
		//check: il char effettivamente ha gli item nella quantità specificata?		
		if ( $par[2] > $par[1] -> quantity or $par[1] -> character_id != $par[3] -> id )
		{ $message = kohana::lang( 'structures.generic_itemsnotowned'); return false; }
		
		// si può donare/trashare l' oggetto?
		
		if ( $par[0] -> structure_type -> type != 'dump' and $par[1] -> cfgitem -> canbedonated == false )
		{	$message = kohana::lang('structures.generic_itemsnotdroppable'); return false;}
		
		if ( $par[0] -> structure_type -> type == 'dump' and $par[1] -> cfgitem -> trashable == false )
		{	$message = kohana::lang('structures.generic_itemsnotdroppable'); return false;}
				
		// check: il peso degli item supera la capacità di immagazzinamento della struttura?
		$itemsweight = $par[1] -> get_totalweight( $par[2] ); 
		$storableweight = $par[0] -> get_storableweight( $par[0] );
		
		// check: si può donare SOLO a strutture pubbliche
		if ( !in_array( $par[0] -> structure_type -> subtype, array ('government', 'church') ) and 
		!in_array( $par[0] -> structure_type -> supertype , array( 'dump', 'buildingsite' ) ) )
		{	$message = kohana::lang('global.operation_not_allowed'); return false;}
		
		// se imprigionato, si può donare solo alla prigione
		if ( Character_Model::is_imprisoned( $par[3] -> id ) and $par[0] -> structure_type -> supertype != 'barracks' )
		{ $message = kohana::lang('ca_donate.error-imprisonedcandonateonlytoprison'); return false;	}
		
		if ( $storableweight < $itemsweight )
		{ $message = kohana::lang('global.operation_not_allowed'); return false;	}
		
		return true;
	}
	
	protected function append_action( $par, &$message ){	}

	public function execute_action ( $par, &$message ) 
	{
		
		///////////////////////////////////////////////////////////////////
		// aggiunge l' item sulla struttura.
		// se la struttura target è il dump, e 
		// l' oggetto ha il flag destroy on trash, 
		// non aggiungerlo e distruggilo definitivamente
		//////////////////////////////////////////////////////////////////////
		
		if ($par[1] -> cfgitem -> tag == 'silvercoin' )		
		{
			$par[0] -> modify_coins( $par[2], 'donatetostructure' );			
			$par[3] -> modify_coins( -$par[2], 'donatetostructure' );			
		}
		elseif ($par[1] -> cfgitem -> tag == 'coppercoin' )		
		{
			$par[0] -> modify_coins( $par[2]/100, 'donatetostructure' );			
			$par[3] -> modify_coins( -$par[2]/100, 'donatetostructure' );			
		}
		elseif ($par[1] -> cfgitem -> tag == 'doubloon' )		
		{
			$par[0] -> modify_doubloons( $par[2], 'donatetostructure' );			
			$par[3] -> modify_doubloons( -$par[2], 'donatetostructure' );			
		}
		else
		{
			if ( 
				$par[0] -> structure_type -> type == 'dump' and 
				$par[1] -> cfgitem -> destroyontrash )
				;
			else
				$ret_1 = $par[1] -> additem( "structure",  $par[0] -> id, $par[2] );				
			
			$ret_2 = $par[1] -> removeitem( "character", $par[3] -> id, $par[2] );		
		}
		
		$par[3] -> save();
		$par[0] -> save();
		
		// invia evento alla struttura
		
		if ( $par[0] -> structure_type -> type != 'dump' )
		{
			$text = '__events.structuredonation;'. $par[3] -> name . ';' . $par[2] . ';' . '__' . $par[1] -> cfgitem -> name ;
			 Structure_Event_Model::newadd( $par[0] -> id, $text );
		}
		
		if ( $par[0] -> structure_type -> type != 'dump' )
			$text = '__events.structuredonationchar;'. $par[2] . ';' . '__' . $par[1] -> cfgitem -> name . ';__' . $par[0] -> structure_type -> name . ';__' . $par[0] -> region -> name ;
		else
			$text = '__events.itemtrashed;' . $par[2] . ';' . '__' . $par[1] -> cfgitem -> name  ;		
		
		Character_Event_Model::addrecord( $par[3] -> id, 'normal', $text );
			
		if ( $par[0] -> structure_type -> type != 'dump' )
			$message = kohana::lang('charactions.donate_ok'); 
		else
			$message = kohana::lang('charactions.itemtrashed_ok'); 
					
		return true;		
	}
	
}
