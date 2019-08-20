<?php defined('SYSPATH') OR die('No direct access allowed.');


class CA_Move_Model extends Character_Action_Model
{

	protected $cancel_flag = true;
	protected $immediate_action = false;
	protected $_par;
	protected $enabledifrestrained = true;
	protected $basetime = null;
	protected $attribute      = 'none';
	protected $appliedbonuses =  array(); // bonuses da applicare	
	protected $callablebynpc = true;	
	
	const CART_USAGE_CONSUMPTION = 0.277;

	// L'azione richiede che il personaggio indossi
	// un determinato equipaggiamento
	
	protected $requiresequipment = false;
		
	// Effettua tutti i controlli relativi al move, sia quelli condivisi
	// con tutte le action che quelli peculiari del move
	// @input: $par[0] -> oggetto regione di destinazione	
	//         $par[1] -> se si muove al battlefield
	//         $par[2] -> oggetto char/npc che si muove
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function check( $par, &$message )
	{ 
		// Check classe madre (compreso il check_equipment)
		if ( ! parent::check( $par, $message, $par[2] -> id ) )					
		{ return false; }
		
		$bonuses = Character_Model::get_premiumbonuses( $par[2] -> id ); ;
		$regionpaths = Configuration_Model::get_cfg_regions_paths2();
			
		// la regione di destinazione esiste?
		//var_dump($regionpaths);exit;
		if ( !isset( $regionpaths[ $par[2] -> position_id][ $par[0] -> id ] ) )
		{
			$message = kohana::lang('ca_move.path-not-on-land'); return FALSE;
		}
		
		$region_path = $regionpaths[ $par[2] -> position_id][ $par[0] -> id ];				
		$currentregion = ORM::factory('region', $par[2] -> position_id );
		
		kohana::log('info', "Char {$par[2] -> name} is moving from {$currentregion -> name} to {$par[0] -> name}.");		
		
		// Controllo che il char non sia già nella stessa locazione
	
		if ( $par[0] -> id == $par[2] -> position_id )
		{ $message = kohana::lang('ca_move.already-in-location'); return FALSE; }
		
		// se il path è fasttravel e il char non ha il bonus travel, errore
		
		if ( $region_path['data'] -> type == 'fastland' and Character_Model::get_premiumbonus( $par[2] -> id, 'travelerpackage' ) === false )
		{ $message = kohana::lang('global.operation_not_allowed'); return FALSE; }
		
		// se il path è fasttravel ma una ragione attraversata è ostile, errore		
		
		if ( 
			$region_path['data'] -> type == 'fastland' and 
			Region_Path_Model::ispathcrossable($par[2], $currentregion, $region_path ) == false)
		{ $message = kohana::lang('ca_move.error-pathisnotcrossable'); return FALSE; }
		
		
		// se il peso in eccesso è del x% errore
		
		$weightineccess = $par[2] -> get_weightinexcess(); 
		$encumbrance = $par[2] -> get_encumbrance();
		
		/*
		if ($encumbrance > 50)
		{$message = kohana::lang('ca_move.error-toomuchweightcarried'); return FALSE;}
		*/
		
		$this -> _par['weightinexcess'] = 	$par[2] -> get_weightinexcess(); 
		$this -> _par['bonuses'] = 	$bonuses;
		$this -> _par['hasshoes'] = $par[2] -> get_bodypart_item ("feet");
		$this -> _par['char'] = $par[2] ;
		$this -> _par['type'] = $region_path['data'] -> type;
		$this -> _par['time'] = $region_path['data'] -> time;
		$this -> _par['destname'] = $par[0] -> name;
		$this -> _par['sourcename'] = $currentregion -> name;

		$travelinfo = Region_Path_Model::get_travelinfo( $this -> _par );
		
		
		if ( $travelinfo['realtraveltime'] > ( ( 18 * 60 ) / Kohana::config('medeur.serverspeed') ) )
		{$message = kohana::lang('ca_move.error-movetimetoogreat'); return FALSE;}
		
		/////////////////////////////////////////////////////
		// Controllo che tra le due regioni ci sia un 
		// collegamento via terra (per evitare di usare il 
		// MOVE per spostarsi verso le regioni marine e
		// non pagare il dazio)
		// Controllo effettuato solo se non ci si muove verso 
		// il battlefield.
		/////////////////////////////////////////////////////
		
		
		if ( ($travelinfo['type'] == "sea" or $travelinfo['type'] == "mixed") 
			and
			$par[1] == false 
		)
		{ $message = kohana::lang('ca_move.path-not-on-land'); return FALSE; }
		
		/////////////////////////////////////////////////////
		// Controllo che se movetobattlefield è true, nella 
		// regione ci sia un battlefield!
		/////////////////////////////////////////////////////
		
		if ( $par[1] == true )
		{
			$cdb = $par[0]->get_structure( 'battlefield' );
			if ( is_null($cdb) )
			{ $message = kohana::lang('ca_move.battlefield-not-existing'); return FALSE; }
		}
		
		/////////////////////////////////////////////////////
		// controlla se il char non può lasciare il regno
		// ordine di restraint
		/////////////////////////////////////////////////////
		
		if ( 
			Character_Model::is_restrained( $par[2] -> id )
			and 
			$par[0] -> kingdom -> id != $currentregion -> kingdom_id )
		{ $message = kohana::lang('charactions.move_charisrestrained') ; return FALSE; }
		
		
		// Verifica trattati diplomatici (validi solo per Giocatori controllati da persone)
		
		$possibletomove = Region_Model::canmoveto( $par[2], $par[0], $currentregion, $par[1], $message );
		if ($possibletomove == false and $par[2] -> type == 'pc' )
		{
			return false;
		}
		
		return true;
	
	}

	protected function append_action( $par, &$message )
	{
	
		$region_path = ORM::factory('region_path') -> 
			where(array('region_id' => $par[2]->position_id, 'destination' => $par[0]))->find();		
		
		$travelinfo = Region_Path_Model::get_travelinfo( $this -> _par );
					
		$this -> character_id = $par[2] -> id;
		$this -> starttime = time();
		$this -> status = "running";	
//		var_dump($travelinfo); exit;
		$this -> basetime = $travelinfo['realtraveltime']/60;
		$this -> endtime = $this -> starttime + $this -> get_action_time( $par[2] ); 
		//var_dump(Utility_Model::format_datetime($this->starttime)); 
		//var_dump(Utility_Model::format_datetime($this->endtime)); 
		
		// se il char si muove direttamente nel battlefield, 
		// setto il parametro
		
		$this -> param3 = $par[1];
		
		// se è in un battlefield, lo tolgo automaticamente
		// dallo schieramento e marco che parte dal battlefield
		
		$frombattlefield = false;
		
		if ( Character_Model::is_fighting( $par[2] -> id ) == true ) 
		{
			$db = Database::instance();
			$sql = "
				delete from 
				battle_participants where
				battle_id in ( select id from battles where status = 'running' ) 
				and character_id = " . $par[2] -> id ; 
			$db -> query( $sql ); 
			
			$par[2] -> modify_stat( 
				'fighting', 
				false,
				null,
				null,
				true );
			
			Character_Event_Model::addrecord( $par[2] -> id , 'normal', '__events.battlefield_leave');		
					
			$frombattlefield = true;
			$this -> param3 = 2;
			
		}
		
		$this -> param1 = $par[2] -> position_id;
		$this -> param2 = $par[0] -> id;
		
		$this -> param4 = $this -> basetime;
		$this -> save();
		
		$currentregion = ORM::factory('region', $par[2] -> position_id ); 
		
		// il pg deve avere posizione 0 in fase di movimento
				
		$par[2] -> modify_location( 0 );		
		$par[2] -> save ();		
				
		Character_Event_Model::addrecord( 
			$par[2] -> id , 
			'normal', 
			'__events.move_start' . 
			';__' . $currentregion -> name . 
			';__' . $par[0] -> name );
		
		return true;
	
	}

	// Esecuzione dell' azione. 
	// Questa funzione viene chiamata quando viene invocata una 
	// complete_expired_action e gestisce le azioni
	// inserite nella character_actions
	// - si caricano i parametri dal database
	// - si esegue l' azione in base ai parametri
	// - si mette l' azione in stato completed
	
	public function complete_action( $data )
	{
	
		$char = ORM::factory('character')->find( $data->character_id );
		$bonuses = Character_Model::get_premiumbonuses( $char -> id ); ;
		
		$region_path = ORM::factory('region_path')->where(array('region_id' => $data->param1, 'destination' => $data->param2))->find();		
		$destregion = ORM::factory('region', $data -> param2 );
		$currentregion = ORM::factory('region', $data -> param1 );
		
		$this -> _par['weightinexcess'] =   $char -> get_weightinexcess();
		$this -> _par['bonuses'] =   $bonuses;
		$this -> _par['hasshoes'] = $char -> get_bodypart_item ("feet"); 
		$this -> _par['char'] = $char ;
		$this -> _par['type'] = $region_path -> type;
		$this -> _par['time'] = $region_path -> time;
		$this -> _par['destname'] = $destregion -> name;
		$this -> _par['sourcename'] = $currentregion -> name;

		$travelinfo = Region_Path_Model::get_travelinfo( $this -> _par );
		
		// Consumo delle scarpe
		Item_Model::consumeclothes( $char, $this -> action, $data -> param4 );
		
		// consuma il carro, solo se non si ha il cart pro
		
		if ( Character_Model::get_premiumbonus(  $char -> id, 'supercart') == false )
		{
		
			if ( Character_Model::has_item( $char->id, 'cart_2') == true )
			{
				
				kohana::log('debug', '-> Char has a cart 2.');
				
				$res = Database::instance() -> query ( 
					"select i.id from items i, cfgitems ci where
					i.cfgitem_id = ci.id and ci.tag = 'cart_2' 
					and i.character_id = " . $char -> id );				
				$item = ORM::factory('item', $res['0'] -> id );
				
				if ($item -> quantity > 1 )
				{
					kohana::log('debug', '-> More than one cart, splitting.');
					$item -> quantity -= 1;
					$item -> save();
					
					$itemtoconsume = $item -> cloneitem();	
					$itemtoconsume -> quantity = 1;
					$itemtoconsume -> character_id = $char -> id;
					$itemtoconsume -> save();
				}
				else
					$itemtoconsume = $item;
					
				
				$consumeratio = $travelinfo['normaltraveltime'] / 30 * self::CART_USAGE_CONSUMPTION;
				$itemtoconsume -> consume( $consumeratio, 'move' ); 
				 
			}
			
			if ( Character_Model::has_item( $char->id, 'cart_1') == true )
			{		
				kohana::log('debug', '-> Char has a cart 1.');
				$res = Database::instance() -> query ( 
					"select i.id from items i, cfgitems ci where
					i.cfgitem_id = ci.id and ci.tag = 'cart_1' 
					and i.character_id = " . $char -> id );
				$item = ORM::factory('item', $res['0'] -> id );
				if ($item -> quantity > 1 )
				{
					kohana::log('debug', '-> More than one cart, splitting.');
					$item -> quantity -= 1;
					$item -> save();
					$itemtoconsume = $item -> cloneitem();	
					$itemtoconsume -> quantity = 1;
					$itemtoconsume -> character_id = $char -> id;
					$itemtoconsume -> save();
				}
				else
					$itemtoconsume = $item;
				
				$consumeratio = $travelinfo['normaltraveltime'] / 30 * self::CART_USAGE_CONSUMPTION;
				$itemtoconsume -> consume( $consumeratio, 'move' );  
			}
		}			
		
		// Sottraggo l'energia e la sazietà al char 		
		$char->modify_energy ( - $travelinfo['energy'], false, 'move' );
		$char->modify_glut ( - $travelinfo['glut'] );
		
		////////////////////////////////////////////////
		// se lo stato è fighting, vuol dire che il char
		// era nel battlefield. Se viaggia bisogna togli
		// erlo dallo schieramento e resettare lo stato
		////////////////////////////////////////////////
		
		if ( Character_Model::is_fighting( $char -> id ) == true )
		{
			Database::instance() -> query ( "delete from battle_participants bp, battles b 
				where b.id = bp.battle_id
				and   b.status != 'completed' 
				and 	character_id = " . $char -> id );   
		
			$char -> modify_stat( 
				'fighting', 
				false,
				null,
				null,
				true );
		}
		
		// /////////////////////////////////////////////////
		// se il char si muove direttamente nel battlefield,
		// (param3=true), ed esiste il battlefield nella 
		// regione di destinazione, setto lo stato a fighting
		//////////////////////////////////////////////////////
			
		kohana::log('debug', 'finding the battlefield...' ); 
		
		//var_dump ($destregion -> id ); exit; 
		
		$cdb_d = $destregion -> get_structure( 'battlefield' );
		
		//var_dump( $cdb_d ); 
		
		//kohana::log('debug', kohana::debug( $destregion )); 
		//var_dump ( $data -> param3 == true and !is_null( $cdb_d ) ); exit;  
		//kohana::log('debug', kohana::debug( $data )); 
		
		if ( $data -> param3 == true and !is_null( $cdb_d ) )
		{
		
			$char -> modify_stat( 
				'fighting', 
				true,
				null,
				null,
				true );			
		}
		else
			$char -> modify_stat( 
				'fighting', 
				false,
				null,
				null,
				true );
		
		kohana::log('debug', '-> 	Char status: ' . $char -> status ); 
		
		//exit; 
		
		$char -> modify_location( $data -> param2 );
		$char -> save();
		
		// evento per quest
		$par[0] = $destregion;
		GameEvent_Model::process_event( $char, 'travel', $par );

		
		Character_Event_Model::addrecord( $char -> id , 'normal', 
			'__events.move_end' . 			
			';__' . $destregion -> name );
		
	}
	
	protected function execute_action() {}
	
	public function cancel_action( &$message )
	{		
		
		kohana::log('debug', '-> Move: canceling action.');
		
		$char = Character_Model::get_info( Session::instance()->get('char_id') );		
		
		// Se l'azione è già oltre i 10 minuti non si può cancellare.
		
		if ( (time() - $this -> starttime) > (10*60) )
		{
			$message = 'ca_move.error-cantcanceltoomuchtimehaspassed';
			return false;			
		}
		
		// se proveniva da un battlefield e cancella,
		// lo rimetto nel battlefield. Se non c'è il battlefield, 
		// annullo la cancellazione.
		
		$region = ORM::factory('region', $this -> param1);		
		$cdb = $region -> get_structure( 'battlefield' );		
		
		if ( $this -> param3 == 2 and !is_null( $cdb ) ) 
		{
			kohana::log('debug', '-> Move: switching back on fight status.');
			$char -> modify_stat( 
				'fighting', 
				true,
				null,
				null,
				true );
		}
		else if ( $this -> param3 == 2 and is_null( $cdb ) ) 
		{
			$message = 'charactions.battlefielddismountedcantgoback';
			return false;
		}
			
			
		$char -> modify_location( $this -> param1 );		
		$char -> save();				
			
		Character_Event_Model::addrecord( 
			$char -> id , 
			'normal', 
			'__events.move_canceled;__' . $region -> name );
		
		return true; 
	
	}
	
	// Questa funzione costruisce un messaggio da visualizzare 
	// in attesa che la azione sia completata.
	
	public function get_action_message( $type = 'long') 
	{
		$pending_action = $this->get_pending_action();
		$message = "";				
	
		if ( $pending_action -> loaded )
		{
			$startregion = ORM::factory('region', $pending_action->param1);
			$destregion =  ORM::factory('region', $pending_action->param2);					
			$now = date("F d, Y H:i:s", time() );
			if ( $type == 'long' )		
				$message = '__regionview.move_longmessage;__' . $startregion->name . ';__' . $destregion->name ;									
			else
				$message = '__regionview.move_shortmessage';
									
		}
	
		return $message;
	
	}	
	
}
