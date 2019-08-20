<?php defined('SYSPATH') OR die('No direct access allowed.');
class CA_Loot_Model extends Character_Action_Model
{

	protected $immediate_action = true;
	protected $targetchar = null;

	// con tutte le action che quelli peculiari del seed
	// @input: array di parametri	
	// par[0] : oggetto char che prende gli item
	// par[1] : oggetto char da cui si prendono gli item	
	// par[2] : lista item da rubare
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function check( $par, &$message )
	{ 
		$message = "";
		
		if ( ! parent::check( $par, $message ) )					
		{ return FALSE; }		
				
		// check: il char è nella stessa location del char derubato?
		
		if ( 
			$par[1] -> loaded == false 
			or 
			($par[0] -> position_id != $par[1] -> position_id ) )
		{ $message = kohana::lang('global.operation_not_allowed'); return FALSE; }
		
		// c'è qualche item da prendere?
		if ( count( $par[2] ) == 0 )
		{ $message = kohana::lang('ca_loot.error-noitemstoloot'); return FALSE; }		
		
		// check: il char è svenuto?		
				
		if ( Character_Model::is_recovering( $par[1] -> id ) != true )
		{ $message = kohana::lang('ca_loot.error-targetcharisnotrecovering'); return FALSE; }
		
		$currentaction = Character_Model::get_currentpendingaction( $par[1] -> id );
		
		if ( $currentaction['param1'] == 'duel' )
		{ $message = kohana::lang('ca_loot.error-lootnotpossiblewhendueling'); return FALSE; }
				
		// calcolo il peso totale degli item, il char può trasportare cosi tanti oggetti?
		
		$totalweight = 0;
		foreach ( $par[2] as $item ) 	
		{
			
			$itemweight = ( $item -> weight * $item -> quantity );			
			$totalweight += $itemweight;			
		}	
		$totalweight /= 100;
		$transportableweight = $par[0] -> get_transportableweight() / 1000;
		
		if ( $transportableweight < $totalweight )		
		{ $message = kohana::lang('ca_loot.error-toomuchweight'); return FALSE; }
		
		// todo: controllo che non si possano prendere + item del dovuto.
		
		foreach ( $par[2] as $itemtoloot ) 	
		{
			
			$item = ORM::factory('item', $itemtoloot -> id );
			kohana::lang('debug', '-> item quantity: ' . 
				$item -> quantity . ' itemlooted quantity: ' . $itemtoloot -> quantity );
			
			// In caso di parallelismo nel loot gli item 
			// selezionati prima potrebbero non esserci più o presenti
			// ma in minore quantità... In questo caso saltiamo l' item
			
			if ( $item -> loaded == false or $item -> quantity < $itemtoloot -> quantity )
			{ 	$message = kohana::lang('ca_loot.error-quantityproblem',
				$itemtoloot -> quantity, kohana::lang($item -> cfgitem -> name ));
				return FALSE; 
			}
			
		}
		
		return true;
		
	}

	protected function append_action( $par, &$message )
	{}

	public function complete_action( $data )
	{}
	
	public function execute_action ( $par, &$message ) 
	{	
		$disappeareditems = false;
		$looteditems = 0;
		
		foreach ( $par[2] as $itemtoloot ) 	
		{
			$looteditems ++;
			$item = ORM::factory('item', $itemtoloot -> id );
			kohana::lang('debug', '-> item quantity: ' . 
				$item -> quantity . ' itemlooted quantity: ' . $itemtoloot -> quantity );
			
			// In caso di parallelismo nel loot gli item 
			// selezionati prima potrebbero non esserci più o presenti
			// ma in minore quantità... In questo caso saltiamo l' item
			
			if ( $item -> loaded == false or $item -> quantity < $itemtoloot -> quantity )
			{
				$disappeareditems = true;
				continue;
			}
			
			// aggiunge l' item al character
			
			$ret_1 = $item -> additem( "character", $par[0] -> id, 
				$itemtoloot -> quantity );
			$ret_2 = $item -> removeitem( "character", $par[1] -> id, 
				$itemtoloot -> quantity );
			
			Character_Event_Model::addrecord(
				$par[0] -> id,
				'normal', 
				'__events.looteditemsource;' . $par[1] -> name . 
				';' . $itemtoloot -> quantity . 
				';__' . $item -> cfgitem -> name,				
				'evidence'
			);
			
			Character_Event_Model::addrecord(
				$par[1] -> id,
				'normal', 
				'__events.looteditemtarget;' .
				$itemtoloot -> quantity . 
				';__' . $item -> cfgitem -> name,				
				'evidence'
			);
			
		}
		
		// TODO: check per riconoscimento LADRO
		
		if ( $looteditems > 0 )
		{
			$sumlooter = 
				$par[0] -> get_attribute( 'intel' ) + 
				$par[0] -> get_attribute( 'dex' );
				
			$sumlooted = 
				$par[1] -> get_attribute( 'intel' ) + 
				$par[1] -> get_attribute( 'car' );
								
			$chancetobediscovered = intval($sumlooted / (( $sumlooted + $sumlooter ) / 5));			
			mt_srand();
			$r = mt_rand(1,100);
			
			kohana::log('debug', '-> sumlooter: ' . $sumlooter . ' sumlooted: ' . $sumlooted .', chance to be disc: ' . $chancetobediscovered . 
			' - ' . ' r: ' . $r );
			
			if ( $r <= $chancetobediscovered )
			{
				$par[0] -> modify_honorpoints( -1, 'lootdiscovered');
				
				if ( 	$par[1] -> get_attribute( 'intel' ) >= 
						$par[1] -> get_attribute( 'car' ) )
						$text = '__events.looteddiscoveredintelligence;';
					else
						$text = '__events.looteddiscoveredcharisma;';
				
				// evento per looter
				
				Character_Event_Model::addrecord(
					$par[0] -> id,
					'normal', 
					'__events.looterdiscovered;' . $par[1] -> name,					
					'evidence'
				);
				
				// evento per looted
				
				Character_Event_Model::addrecord(
					$par[1] -> id,
					'normal', 
					$text . $par[0] -> name,					
					'evidence'
				);	
			}
		}
		
		if ( $disappeareditems )
		{
			Character_Event_Model::addrecord(
				$par[0] -> id,
				'normal', 
				'__events.looterdisappeareditems;'.
				$par[1] -> name,
				'evidence'
			);
		}
		
		$message = kohana::lang('ca_loot.info-lootok');
		return true;		
	}
	
}
