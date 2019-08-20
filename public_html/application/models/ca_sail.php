<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Sail_Model extends Character_Action_Model
{
	// Costanti
	const COST_FOR_60_MIN = 1;

	protected $cancel_flag = true;
	protected $immediate_action = false;
	protected $_par;
	protected $attribute = 'none';
	protected $basetime = 1; // 1 ora
	protected $appliedbonuses =  array(); // bonuses da applicare
	
	// L'azione richiede che il personaggio indossi
	// un determinato equipaggiamento
	protected $requiresequipment = true;
	
	// Equipaggiamento o vestiario necessario in base al ruolo
	// Consume_rate = percentuale di consumo dell'item
	protected $equipment = array
	(
		'all' => array
		(
			'body' => array
			(
				'items' => array('any'),
				'consume_rate' => 'verylow'
			),
			'torso' => array
			(
				'items' => array('any'),
				'consume_rate' => 'verylow'
			),
			'legs' => array
			(
				'items' => array('any'),
				'consume_rate' => 'verylow'
			),
			'feet' => array
			(
				'items' => array('any'),
				'consume_rate' => 'verylow'
			),
		),
	);
	
	// Effettua tutti i controlli relativi al SAIL, sia quelli condivisi
	// con tutte le action che quelli peculiari del SAIL
	// @input: array di parametri	
	// par[0]: oggetto regione destinazione
	// par[1]: se si muove nel battlefield
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function check( $par, &$message )
	{ 
		// Check classe madre (compreso il check_equipment)
		if ( ! parent::check( $par, $message ) )					
		{ return false; }
		
		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		$bonuses = Character_Model::get_premiumbonuses( $char -> id ); ;

		$region_path = ORM::factory('region_path')->where(array('region_id' => $char->position_id, 'destination' => $par[0] -> id ))->find();
		$currentregion = ORM::factory('region', $char -> position_id );
		
		// Controllo che le due regioni siano effettivamente collegate
		if (! $region_path->loaded)
		{ $message = kohana::lang('ca_sail.no-paths-avaible'); return FALSE; }

		// Se il path è esistente estraggo le informazioni
		$this -> _par['weightinexcess'] = 	$char -> get_weightinexcess(); 
		$this -> _par['bonuses'] = 	$bonuses;
		$this -> _par['hasshoes'] = $char -> get_bodypart_item ("feet"); 
		$this -> _par['char'] = $char ;


		$this -> _par['type'] = $region_path -> type;
		$this -> _par['time'] = $region_path -> time;
		$this -> _par['destname'] = $par[0] -> name;
		$this -> _par['sourcename'] = $currentregion -> name;

		$travelinfo = Region_Path_Model::get_travelinfo( $this -> _par );
		$this -> basetime = $travelinfo['realtraveltime']/60;
		
		// controllo se il char ha i soldi necessario
		
		if ( $char -> check_money( $travelinfo['cost'] ) == false )
		{ $message = kohana::lang( 'charactions.global_notenoughmoney'); return FALSE; }
	
		// Se mi trovo sulla terraferma e sono diretto in mare controllo
		// che la città abbia effettivamente un porto
		
		$current_region = ORM::factory('region', $char -> position_id );
		$current_region_harbor = $current_region -> get_structure( 'harbor' ); 
		$dest_region_harbor = $par[0] -> get_structure( 'harbor' ); 
		
		if ( ($current_region->type == "land" && $par[0]->type == "sea") && is_null ( $current_region_harbor ) )
		{ $message = kohana::lang('charactions.sail_no_porto'); return FALSE; }

		// Se mi trovo in mare controllo e sono diretto sulla terra
		// che la città abbia effettivamente un porto
		
		if ( ($current_region->type == "sea" && $par[0]->type == "land") && is_null ( $dest_region_harbor ) )
		{ $message = kohana::lang('ca_sail.destination-has-no-harbor'); return FALSE; }
		
		/////////////////////////////////////////////////////
		// Controllo che se movetobattlefield è true, nella 
		// regione ci sia un battlefield!
		/////////////////////////////////////////////////////
		
		if ( $par[1] == true )
		{
			$cdb = $par[0] -> get_structure( 'battlefield' );
			if ( $cdb == null )
			{ $message = kohana::lang('ca_move.battlefield-not-existing'); return FALSE; }
		}
		
		/////////////////////////////////////////////////////////////////////////////////////
		// Verifica la relazione diplomatica. Se è ostile e non è già nel regno non si può
		// muovere a meno che abbia un permesso di accesso individuale. 
		// E' possibile sempre muoversi nel battlefield e verso il mare
		/////////////////////////////////////////////////////////////////////////////////////
		
		$rd = Diplomacy_Relation_Model::get_diplomacy_relation( $par[0] -> kingdom_id, $char -> region -> kingdom_id );
		$stat = $char -> get_stat_d( $char -> id, 'accesspermit', $par[0] -> kingdom_id ); 
		
		if ( !is_null( $rd ) and $rd['type'] == 'hostile' 		
			and $currentregion -> kingdom_id != $par[0] -> kingdom_id
			and $par[0] -> type != 'sea' 
			and $par[1] == false
			and ( !$stat -> loaded or $stat -> value < time() )
			)
		{
			$message = kohana::lang('ca_move.error-hostileaccessdenied'); 
			return false;				
		}
		
		/////////////////////////////////////////////////////
		// controlla se il char non può lasciare il regno
		// ordine di restraint
		/////////////////////////////////////////////////////
		
		if ( 
			Character_Model::is_restrained( $char -> id )
			and 
			$par[0] -> kingdom -> id != $currentregion -> kingdom_id )
		{ $message = kohana::lang('charactions.move_charisrestrained') ; return FALSE; }	
		
		return true;
	}

	// Funzione per l'inserimento dell'azione nel DB.
	// Questa funzione appende solo una azione _non la esegue_
	// Al momento non si prevedono azioni istantanee.
	// @input: array di parametri. 	
	// Per l'azione move uso un solo parametro
	// $par[0] che rappresenta l'id del nodo di destinazione
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function append_action( $par, &$message )
	{
		
		$char = Character_Model::get_info( Session::instance()->get('char_id') ); 
		$region_path = ORM::factory('region_path')
			->where(array('region_id' => $char->position_id, 'destination' => $par[0]))
			->find();		
			
		$travelinfo = Region_Path_Model::get_travelinfo( $this -> _par );
		
		$this->character_id = $char -> id;
		$this->starttime = time();			
		$this->status = "running";					
		$this->basetime = $travelinfo['realtraveltime']/60; // hours of travel
		$this->endtime = $this -> starttime + $this -> get_action_time( $char ); 
		//var_dump(Utility_Model::format_datetime($this->starttime)); 
		//var_dump(Utility_Model::format_datetime($this->endtime)); 
		
		// se il char si muove direttamente nel battlefield, 
		// setto il parametro
		
		$this -> param3 = $par[1];
		
		// se è in un battlefield, lo tolgo automaticamente
		// dallo schieramento e marco che parte dal battlefield
		
		$frombattlefield = false;
		
		if ( Character_Model::is_fighting( $char -> id ) == true ) 	
		{
			$db = Database::instance();
			$sql = "delete from battle_participants where
				battle_id in ( select id from battles where status = 'running' ) 
				and character_id = " . $char -> id ; 
			$db -> query( $sql ); 
			$char -> modify_stat( 
				'fighting', 
				false,
				null,
				null,
				true );
				
			$frombattlefield = true;
			$this -> param3 = 2;
		}
		
		// penalty del 400% se affamato o senza energia.
		
		$this->param1 = $char->position_id;
		$this->param2 = $par[0];			
		$this->save();
		$char->modify_location( 0 );
		$char->modify_coins ( - $travelinfo['cost'], 'sailcost' );
		$char->save ();
		
		// evento
		Character_Event_Model::addrecord(
			$char->id, 
			'normal',  
			'__events.sailcost'.
			';'. $travelinfo['cost']
			);		
		
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
		
		$currentregion = ORM::factory('region', $data -> param1 );
		$destregion = ORM::factory('region', $data -> param2 );

		$this -> _par['weightinexcess'] =   $char -> get_weightinexcess();
		$this -> _par['bonuses'] = 	$bonuses;
		$this -> _par['hasshoes'] = $char -> get_bodypart_item ("feet");
		$this -> _par['char'] = $char ;
		$this -> _par['type'] = $region_path -> type;
		$this -> _par['time'] = $region_path -> time;
		$this -> _par['destname'] = $destregion -> name;
		$this -> _par['sourcename'] = $currentregion -> name;

		$travelinfo = Region_Path_Model::get_travelinfo( $this -> _par );

		// Consumo degli items/vestiti indossati
		Item_Model::consume_equipment( $this->equipment, $char );
		
		
		// evento per quest
		$par[0] = $destregion;
		GameEvent_Model::process_event( $char, 'travel', $par );
		
		// /////////////////////////////////////////////////
		// se il char si muove direttamente nel battlefield,
		// (param3=true), ed esiste il battlefield nella 
		// regione di destinazione, setto lo stato a fighting
		//////////////////////////////////////////////////////
		
		kohana::log('debug', 'finding the battlefield...' ); 
				
		$cdb_d = $destregion -> get_structure( 'battlefield' );
		
		if ( $data -> param3 == true and !is_null( $cdb_d ) )
		{
			kohana::log('debug', 'Placing char in battlefield...');
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
		
		// Aggiorno la posizione del char		
		
		$char -> modify_location( $data -> param2 );
		$char -> save();
	}
	
	protected function execute_action() {}
		
	public function cancel_action( &$message )
	{		
		
		kohana::log('debug', '-> Sail: canceling action.');
		
		$char = Character_Model::get_info( Session::instance()->get('char_id') );		
		
		// Se l'azione è già oltre i 10 minuti non si può cancellare.
		
		if ( (time() - $this -> starttime) > (10*60) )
		{
			$message = 'ca_sail.error-cantcanceltoomuchtimehaspassed';
			return false;			
		}
		
		// se proveniva da un battlefield e cancella,
		// lo rimetto nel battlefield. Se non c'è il battlefield, 
		// annullo la cancellazione.
		
		$region = ORM::factory('region', $this -> param1);		
		$cdb = $region -> get_structure( 'battlefield' );		
		
		if ( $this -> param3 == 2 and !is_null( $cdb ) ) 
		{
			kohana::log('debug', '-> Sail: switching back on fight status.');
			
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
			'__events.sail_canceled;__' . $region -> name );
		
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
				$message = '__regionview.sail_longmessage;__' . $startregion->name . ';__' . $destregion->name ;									
			else
				$message = '__regionview.sail_shortmessage';
									
		}
	
		return $message;
	}
	
}
