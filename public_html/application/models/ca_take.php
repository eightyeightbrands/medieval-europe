<?php defined('SYSPATH') OR die('No direct access allowed.');


class CA_Take_Model extends Character_Action_Model
{

	protected $immediate_action = true;

	// con tutte le action che quelli peculiari del seed
	// @input: array di parametri	
	// par[0] : oggetto structure
	// par[1] : oggetto item
	// par[2] : quantità
	// par[3] : oggetto char
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function check( $par, &$message )
	{ 
		
		kohana::log('debug', "--- CA_TAKE ---");
		
		$message = "";
		
		if ( ! parent::check( $par, $message ) )					
		{ return FALSE; }		
				
		
		// check: la struttura esiste ed il char è proprietario?
		if ( $par[0] -> structure_type -> supertype != 'battlefield' and (!$par[0]->loaded ) )
		{
			$message = kohana::lang('global.operation_not_allowed'); 
			return false;
		}
				
		// check: l' oggetto è nella struttura, la quantità è corretta?		
		if ( ! $par[1] -> loaded or
					 $par[1] -> structure_id != $par[0] -> id or
					 $par[1] -> quantity < $par[2] )		
		{				
			$message = kohana::lang('structures.generic_itemsnotowned'); 
			kohana::log('debug', "-> No enough items. {$par[2]} {$par[1]->cfgitem->tag}");
			return false;
		}

		// l' oggetto si puo prendere dalla struttura?
		if ($par[1] -> cfgitem ->  takeable == false )
		{
			$message = kohana::lang('structures.error-itemcannotbetaken'); 
			return false;
		}
		
		// check: controllo sulla quantità
		if ( $par[2] <= 0 )
		{
			$message = kohana::lang( 'charactions.negative_quantity');
			return false;
		}		
		
		/////////////////////////////////////////////////////////////////////////////////////
		// check: il char sta trasportando troppo peso?
		// se l' item è un carretto, niente controllo		
		/////////////////////////////////////////////////////////////////////////////////////
		
		if ( $par[3] -> get_transportableweight() <= 0 and $par[1] -> cfgitem -> subcategory != 'cart' )
		{
			$message = kohana::lang('structures.maxtransportableweightreached'); 
			return false;				
		}		
		
		// check proprietari
				
		$_message = null; 	
		if ( $par[1] -> take_do_proprietary_check( $par[2], $_message ) == false )
		{
			$message = kohana::lang( $_message ); 
			return false;				
		}
	
	
		
		return true;
		
	}

	protected function append_action( $par, &$message )
	{
	}

	public function complete_action( $data )
	{
	}
	
	public function execute_action ( $par, &$message ) 
	{
	
		
		// evento per quest			
		$_par[0] = $par[1];
		GameEvent_Model::process_event( $par[3], 'takeitemsfrominventory', $_par );
		
		// Update transazionale
		
		$db = Database::instance();		

		if ($par[1] -> cfgitem -> tag == 'silvercoin' )		
		{
			$par[0] -> modify_coins( -$par[2], 'takefromstructure');
			$par[3] -> modify_coins( $par[2], 'takefromstructure');
		}
		elseif ($par[1] -> cfgitem -> tag == 'coppercoin' )		
		{
			$par[0] -> modify_coins( -$par[2]/100, 'takefromstructure');
			$par[3] -> modify_coins( $par[2]/100, 'takefromstructure');
		}
		elseif ($par[1] -> cfgitem -> tag == 'doubloon' )		
		{
			$par[0] -> modify_doubloons( -$par[2], 'takefromstructure');
			$par[3] -> modify_doubloons( $par[2], 'takefromstructure');
		}						
		else
		{		
			
			$par[1] -> additem( "character", $par[3]->id, $par[2] );			
			$par[1] -> removeitem( "structure", $par[0]->id, $par[2] );			
		}
		
		$par[3] -> save();
		$par[0] -> save();
		
		// evento alla struttura
		
		$text = '__events.structuretake;'. $par[3]->name . ';' . $par[2] . ';' . '__' . $par[1]->cfgitem->name ;
		 Structure_Event_Model::newadd( $par[0], $text );
		
		// evento a chi prende gli item
		
		Character_Event_Model::addrecord(
			$par[3] -> id, 
			'normal', 
			'__events.playertake' . 
			';' . $par[2] . 
			';__' . $par[1] -> cfgitem -> name . 
			';__' . $par[0] -> structure_type -> name . 
			';' . $par[0] -> character -> name . 
			';__' . $par[0] -> region -> name);
			
		
		$message = kohana::lang('charactions.take_ok'); 
		return true;
		
	}
	
}
