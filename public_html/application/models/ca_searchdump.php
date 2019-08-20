<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Searchdump_Model extends Character_Action_Model
{
	// Costanti	
	const DELTA_GLUT = 3;
	const DELTA_ENERGY = 5;

	protected $cancel_flag = true;
	protected $immediate_action = false;
	protected $basetime       = 2;  // 2 ore
	protected $attribute      = 'intel';  // attributo forza
	protected $appliedbonuses = array ( 'workerpackage' ); // bonuses da applicare

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
		),
	);
	
	// Effettua tutti i controlli relativi al move, sia quelli condivisi
	// con tutte le action che quelli peculiari del move
	// @input: $par[0] = char, $par[1] = char
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function check( $par, &$message )
	{ 
		kohana::log('debug', '-> Calling ca_searchdump check.');
		
		// Check classe madre (compreso il check_equipment)
		if ( ! parent::check( $par, $message ) )					
		{ return false; }
			
		if ( ! $par[0] -> loaded or ! $par[1] -> loaded )
		{ $message = kohana::lang('global.operation_not_allowed'); return FALSE; }		
	
		// Check: età del char è < 30 giorni
		if ( $par[0] -> get_age() < 30 )
		{ $message = kohana::lang('character.agerequirementfailed', 30); return false; }
	
		if (
			$par[0] -> energy < self::DELTA_ENERGY or
			$par[0] -> glut < self::DELTA_GLUT )
		{
			$message = Kohana::lang("charactions.notenoughenergyglut");
			return false;
		}
		
		$message = kohana::lang('structures_dump.search_ok' );
		return true;
	}

	protected function append_action( $par, &$message )
	{
						
		$this -> character_id = $par[0] -> id;
		$this -> starttime = time();			
		$this -> status = "running";			
		$this -> endtime = $this -> starttime + $this -> get_action_time( $par[0] );
		$this -> save();		
								
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
		$char = ORM::factory('character')->find( $data -> character_id );		
		
		// Consumo degli items/vestiti indossati
		Item_Model::consume_equipment( $this->equipment, $char );	
		
		$char->modify_energy ( - self::DELTA_ENERGY, false, 'searchdump' );
		$char->modify_glut ( - self::DELTA_GLUT );
		$char->save();

		$chance = 40 + ($char -> get_attribute( 'intel' ) * 2) ;
		$r = rand( 1, 100 );
		
		kohana::log('debug', '-> Random: ' . $r . ', Chance: ' . $chance );
		
		// find the dump
		
		$region = ORM::factory('region', $char -> position_id ) ;
		$dump = $region -> get_structure ( 'dump' ) ; 
		$founditem = false;
		
		if ( $chance >= $r)
		{			

			$m = null;
			$items = $dump -> get_items();
			
			if ( count( $items ) > 0 )
			{
				//	kohana::log('debug', kohana::debug( $items) ) ; 
				$itemfound = $items[array_rand ( $items )] ; 				
				kohana::log('debug', 'item found: ' . $itemfound -> tag  );
			
				// carica l' item
				$item = ORM::factory('item', $itemfound -> id );
			
				// aggiunge l' item al character
				$ret_1 = $item -> additem( "character", $char -> id, $itemfound -> quantity );
		
				// toglie l' item dalla struttura
				$ret_2 = $item -> removeitem( "structure", $dump->id, $itemfound -> quantity );
				
				$founditem = true;
				
			}
		}
		
		if ( $founditem )
			Character_Event_Model::addrecord( $char -> id, 'normal', '__events.dumpobjectfound' . 
			';' . $itemfound -> quantity . 
			';__' . $itemfound -> name, 
			'evidence' );				
		else
			Character_Event_Model::addrecord( $char -> id, 'normal', '__events.dumpobjectfoundnothing' );			
		
		// lancio random per pulire il dump
		if ( Kohana::config( 'medeur.emptydump') == true )
		{
			$r = rand(1, 50);					
			kohana::log('info', "--> Trying to empty the dump. Random: $r <--"); 

			if ( $r == 1 )
			{
				$res = ORM::factory( 'item' ) -> where ( array( 'structure_id' => $dump -> id ) ) -> delete_all(); 			
				kohana::log('info', '--> The dump has been emptied <--'); 
			}	
		}
	}
	
	protected function execute_action() {}
	
	public function cancel_action() { return true; }
	
	// Questa funzione costruisce un messaggio da visualizzare 
	// in attesa che la azione sia completata.
	public function get_action_message( $type = 'long') 
	{
		$pending_action = $this->get_pending_action();
		$message = "";				
		
		if ( $pending_action -> loaded )
		{
			if ( $type == 'long' )		
			$message = '__regionview.searchdump_longmessage';
			else
			$message = '__regionview.searchdump_shortmessage';
		}
		return $message;
	
	}

}
