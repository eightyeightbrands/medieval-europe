<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Feed_Model extends Character_Action_Model
{
	// Costanti
	const TIME_ONE_ANIMAL_COWS = 0.25;    
	const TIME_ONE_ANIMAL_PIGS = 0.25;   
	const TIME_ONE_ANIMAL_SHEEPS = 0.25;  
	const TIME_ONE_ANIMAL_SILKWORMS = 0.0075;   
	const TIME_ONE_ANIMAL_BEES = 0.00138;      
	const DELTA_GLUT          = 5;     // consumo di sazietà	
	const DELTA_ENERGY        = 10;    // Energia necessaria per la semina	
	const HAY_FOR_COWS        = 1;     // balle di fieno per ogni mucca
	const WHEATBAGS_FOR_PIGS  = 1;     // balle di fieno per ogni pecora
	const HAY_FOR_SHEEPS      = 1;     // sacchi di grano
	const LEAVES_FOR_SILKWORMS = 0.05;   // foglie di gelso per capo
	const FLOWERS_FOR_BEES     = 0.005; // fiori necessari per ogni ape
	
	protected $cancel_flag = true;
	protected $immediate_action = false;
	protected $basetime       = null;
	protected $attribute      = 'dex';  // attributo forza
	protected $appliedbonuses = array ( 'workerpackage'); // bonuses da applicare

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
				'consume_rate' => 'low'
			),
			'torso' => array
			(
				'items' => array('any'),
				'consume_rate' => 'low'
			),
			'legs' => array
			(
				'items' => array('any'),
				'consume_rate' => 'low'
			),
		),
	);
	
	// Effettua tutti i controlli relativi al milk, sia quelli condivisi
	// con tutte le action che quelli peculiari del milk
	// @input: 
	// $par[0] = structure
	// $par[1] = char
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function check( $par, &$message )
	{ 
		$char = Character_Model::get_info( Session::instance()->get('char_id') );	
		
		// Check classe madre (compreso il check_equipment)
		if ( ! parent::check( $par, $message ) )					
		{ return false; }
		
		// check dati
		if ( ! $par[0] -> loaded )
		{ $message = kohana::lang('global.operation_not_allowed'); return FALSE; }
		
		// La struttura non è un allevamento.
		if ( ! in_array ($par[0]->structure_type->type , array( 'breeding_silkworm', 'breeding_cow', 'breeding_sheep', 'breeding_pig', 'breeding_bee')) )
		{ $message = kohana::lang('global.operation_not_allowed'); return FALSE; }
		
		if (
			$char -> energy < self::DELTA_ENERGY or
			$char -> glut < self::DELTA_GLUT )
		{
			$message = Kohana::lang("charactions.notenoughenergyglut");
			return false;
		}
		
		// se i capi sono 0, errore
		
		if ($par[0] -> attribute1 == 0)
		{ $message = kohana::lang( 'ca_feed.error-noanimals'); return false; }
		
		// La sazietà degli animali è già al massimo (100)
		if ($par[0]->attribute2 == 100)
		{ $message = kohana::lang( 'ca_feed.error-animalsarefed'); return false; }		
		
		$info = $this -> get_breedingtype_info( $par[0]->structure_type->type );
			
		// Controllo che nel magazzino dell' allevamento ci sia cibo abbastanza.
		
		if ( $par[0]->get_item_quantity( $info['foodtype'] ) < max(1, round( $info['foodquantity']*$par[0]->attribute1, 0) ) )
		{ $message = kohana::lang('ca_feed.error-notenoughfood'); return FALSE; }
		
		// c'è già una raccolta in atto?
		
		$gatherinprogress = ORM::factory('character_action' ) -> 
			where ( array( 
				'action' => 'feed',
				'status' => 'running',
				'param1' => $par[0] -> id ) ) -> count_all();
				
		if ( $gatherinprogress > 0 )
		{ $message = kohana::lang('ca_feed.error-feedalreadyinprogress'); return FALSE; }
		
		$message = kohana::lang('ca_feed.feed-ok');
		
		return true;
	
	}
	
	protected function append_action( $par, &$message )
	{
				
										
		$this->character_id = $par[1] -> id;
		$this->starttime = time();			
		$this->status = "running";
		
		$info = $this -> get_breedingtype_info( $par[0] -> structure_type -> type );
		$this -> basetime = $info['time'] * $par[0] -> attribute1;		
		$this -> endtime = $this -> starttime + $this -> get_action_time( $par[1] ); 
				
		// Memorizzo l'id dell'allevamento
		$this->param1 = $par[0]->id;
		$this->param2 = $this -> basetime;
		$this->save();
		
		$info = $this->get_breedingtype_info( $par[0]->structure_type->type );
		// rimuovo cibo dal magazzino
		$item = Item_Model::factory( null, $info['foodtype'] );								
		kohana::log('debug', 'removing ' .  intval(max(1, round( $info['foodquantity']*$par[0]->attribute1, 0) )) . ' ' . $info['foodtype'] . ' from storage of structure: ' . $par['0'] -> id ); 
		$item->removeitem("structure", $par[0]->id, intval(max(1, round( $info['foodquantity']*$par[0]->attribute1, 0) )));			
		
		Structure_Event_Model::newadd( 
			$par[0] -> id, 
			'__events.startfeed' . ';' .
			$par[1] -> name );			
		
		
							
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
		$structure = StructureFactory_Model::create( null, $data->param1 );
		
		if ( !is_null($structure) )
		{
			$char = ORM::factory('character', $data -> character_id ); 
			
			// Consumo degli items/vestiti indossati
			Item_Model::consume_equipment( $this->equipment, $char );	
			
			// Sottrae l'energia e la salute al char
			$char->modify_energy ( - self::DELTA_ENERGY, false, 'feedanimal' );
			$char->modify_glut ( - self::DELTA_GLUT );
			
			$char -> save();
			
			// Riporto la salute 
			// (se applicabile)
						
			$structure->attribute2 = min( 100, $structure->attribute2 + 20 );
			$structure->save();					
		
			Structure_Event_Model::newadd( 
				$structure -> id, 
				'__events.endfeed' . ';' .
				$char -> name 
			);

			/////////////////////////////////////////////////////////////////////////////////////////////////
			// dai la paga oraria
			/////////////////////////////////////////////////////////////////////////////////////////////////
			
			if ( $char -> id != $structure -> character_id )
				Job_Model::givehourlywage( $structure, $char, $data -> param2 );
			
		}
	}
	
	/**
	* Torna i dati necessari per la computazione a seconda
	* del tipo di allevamento
	* @param  breedingtype tipo allevamento
	* @return info vettore con info:
	* 	foodtype: tipo cibo (item.tag)
	*   foodquantity: quantità di cibo per capo
	*   time: tempo per sfamare un animale
	*
	*/
	protected function get_breedingtype_info( $breedingtype )
	{
		$info = array ( 'foodtype' => 0, 'foodquantity' => 0, 'time' => 0 );
		
		if ($breedingtype == 'breeding_cow')
		{
			$info['foodquantity'] = self::HAY_FOR_COWS;
			$info['foodtype'] = 'hay' ;
			$info['time'] = self::TIME_ONE_ANIMAL_COWS ;
		}	
		if ($breedingtype == 'breeding_sheep')
		{
			$info['foodquantity'] = self::HAY_FOR_SHEEPS;
			$info['foodtype'] = 'hay' ;
			$info['time'] = self::TIME_ONE_ANIMAL_SHEEPS ;
		}
		if ($breedingtype == 'breeding_pig')
		{
			$info['foodquantity'] = self::WHEATBAGS_FOR_PIGS;
			$info['foodtype'] = 'wheat_bag' ;
			$info['time'] =  self::TIME_ONE_ANIMAL_PIGS ;
		}

		if ($breedingtype == 'breeding_silkworm')
		{			
			$info['foodtype'] = 'mulberry_leaf' ;
			$info['foodquantity'] = self::LEAVES_FOR_SILKWORMS;	
			$info['time'] = self::TIME_ONE_ANIMAL_SILKWORMS ;
		}

		if ($breedingtype == 'breeding_bee')
		{
			$info['foodquantity'] = self::FLOWERS_FOR_BEES;
			$info['foodtype'] = 'flowers' ;
			$info['time'] = self::TIME_ONE_ANIMAL_BEES ;
		}
		
		return $info;
	
	}
		
	protected function execute_action() {}
	
	public function cancel_action() {
	
		// evento in struttura
		
		$character = ORM::factory('character', $this -> character_id );		
		$structure = StructureFactory_Model::create( null, $this -> param1 );
		
		if ( Structure_Grant_Model::get_chargrant( $structure, $character, 'workerpackage' ) == true )
			return false;
				
		Structure_Event_Model::newadd( $this -> param1,
			'__events.actioncanceled' . ';' . 
			$character -> name . ';' . 
			$this -> get_action_message('short')
			); 
			
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
				
			$now = date("F d, Y H:i:s", time() );
			if ( $type == 'long' )		
				$message = '__regionview.feed_longmessage';				
			else
				$message = '__regionview.feed_shortmessage';
		
		}
		
		return $message;
	
	}
	
}
