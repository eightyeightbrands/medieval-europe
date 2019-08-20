<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Lendarmoryitem_Model extends Character_Action_Model
{
	protected $immediate_action = true;
	
	// check
	// @input: parametri
	//  - par[0]: oggetto char che presta
	//  - par[1]: oggetto struttura armory
	//  - par[2]: oggetto char a cui si presta
	//  - par[3]: Vettore di item da prestare
	
	protected function check( $par, &$message )
	{
		if ( ! parent::check( $par, $message ) )					
			return false;		
		
		// check input		
		
		if ( !$par[0] -> loaded or !$par[1] -> loaded or !$par[2] -> loaded) 
		{ $message = kohana::lang( 'global.operation_not_allowed'); return false; }
		
		// ci deve essere almeno un item da prestare
		if ( is_null( $par[3] ))
		{ $message = kohana::lang( 'ca_lendarmoryitem.error-noitemstolend'); return false; }
		
		// se l' armory non ha il bonus, il target deve essere nella stessa regione
		// dell' armory
		
		$bonus = $par[1] -> get_premiumbonus('armory');
		if ( is_null( $bonus ) and $par[2] -> position_id != $par[1] -> region_id ) 
		{ $message = kohana::lang( 'ca_lendarmoryitem.error-charisnotinarmoryregion'); return false;}					
		
		if ( Character_Model::is_traveling( $par[2] -> id ) )
		{ $message = kohana::lang( 'ca_send.error-targetcharistraveling'); return false; }				
			
		return true;
	}
	
	protected function append_action( $par, &$message ){	}

	public function execute_action ( $par, &$message ) 
	{
		
		// scorre il vettore e manda le armature al giocatore
		
		foreach ( $par[3] as $itemtolend_id => $value )
		{
			
			// Registra il prestito
			
			$o = new Structure_Lentitem_Model();
			$o -> structure_id = $par[1] -> id;
			$o -> target_id = $par[2] -> id;
			$o -> lender = $par[0] -> name;
			$o -> lendtime = time();						
			
			$o -> save();
			
			
			// se il giocatore è nella stessa regione, la consegna è istantanea, 
			// altrimenti schedula un' azione di send.
			
			if ( $par[2] -> position_id == $par[1] -> region_id )
			{
				$itemtoremove = ORM::factory('item', $itemtolend_id );
				$itemtoadd = Item_Model::factory( $itemtoremove -> id, null );
				$itemtoadd -> lend_id = $o -> id; 
				$itemtoadd -> locked = true; 
				$itemtoremove -> removeitem( 'structure', $par[1] -> id, 1 );			
				$itemtoadd -> additem( 'character', $par[2] -> id, 1 );
				$deliverytime = time();
			}
			else
			{
				$sendorder = rand(1,time());
				
				// clono l' item e lo invio
				
				$itemtoremove = ORM::factory('item', $itemtolend_id );
				$itemtoadd = $itemtoremove -> cloneitem();
				$itemtoadd -> lend_id = $o -> id; 				
				$itemtoadd -> id = null;
				$itemtoadd -> locked = true; 
				$itemtoadd -> quantity = 1;
				$itemtoadd -> sendorder = $sendorder . ';' . $par[2]->id;
				$itemtoadd -> character_id = -1;
				$itemtoadd -> save();
				
				// rimuovo l' item
				
				$itemtoremove -> removeitem( 'structure', $par[1] -> id, 1 );			
				
				// schedulo l' azione
				
				$sendinfo = Item_Model::computesenddata( 1, $itemtolend_id, $par[0], $par[2] -> name, 'lend' );				
				$a = Character_Action_Model::factory( 'senditem' ); 
				$a -> character_id = $par[0] -> id ; 
				$a -> starttime = time();
				$a -> endtime = $sendinfo['time'];
				$a -> param1 = 1;
				$a -> param2 = $itemtoadd -> cfgitem_id ;
				$a -> param3 = $sendorder; 
				$a -> param4 = $par[2] -> id ;
				$a -> param5 = 'lent'; 
				$a -> save();				
				$deliverytime = $a -> endtime ;

			}
			
			// aggiorna il delivery time
			$lend = ORM::factory('structure_lentitem', $o -> id );
			
			$lend -> deliverytime = $deliverytime;
			$lend -> save();
			
			// Eventi			
			
			Structure_Event_Model::newadd( 
				$par[1] -> id, 
				'__events.structure_lentarmoryitem;' . $par[0] -> name . ';__' . $itemtoadd -> cfgitem -> name . ';' . $par[2] -> name );
			
			Character_Event_Model::addrecord( 
				$par[2] -> id,
				'normal',
				'__events.target_lentarmoryitem;__' . $par[1] -> region -> name . ';__' . $itemtoadd -> cfgitem -> name . 
				';' . Utility_Model::format_datetime ( $deliverytime ) 
				);			
		}
		
		$message = kohana::lang('ca_lendarmoryitem.itemslent_ok'); 	
		return true;		
	}
	
}
