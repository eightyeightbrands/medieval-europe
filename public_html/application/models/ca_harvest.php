	<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Harvest_Model extends Character_Action_Model
{
	// Costanti
	const DELTA_GLUT = 5;
	const DELTA_ENERGY = 10;    // Energia necessaria per la raccolta	
	protected $cancel_flag = true;
	protected $immediate_action = false;

	protected $basetime       = 2;   // 2 ore
	protected $attribute      = 'str';  // attributo forza
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
				'consume_rate' => 'veryhigh'
			),
			'torso' => array
			(
				'items' => array('any'),
				'consume_rate' => 'veryhigh'
			),
			'legs' => array
			(
				'items' => array('any'),
				'consume_rate' => 'veryhigh'
			),
			'right_hand' => array
			(
				'items' => array('sickle'),
				'consume_rate' => 'veryhigh',
			),
		),
	);
	
	// Effettua tutti i controlli relativi al seed, sia quelli condivisi
	// con tutte le action che quelli peculiari del move
	// @input: par[0] - oggetto char
	// @input  par[1] - oggetto struttura
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function check( $par, &$message )
	{ 
		// Check classe madre (compreso il check_equipment)
		if ( ! parent::check( $par, $message ) )					
		{ return false; }
				
		if ($par[0] -> energy < self::DELTA_ENERGY or	$par[0] -> glut < self::DELTA_GLUT )
		{
			$message = Kohana::lang("charactions.notenoughenergyglut");
			return false;
		}
		
		// Controllo che il campo sia effettivamente maturo
		
		if ( $par[1] -> attribute1 != 2 )
		{ $message = kohana::lang('ca_harvest.error-fieldisnotripe'); return FALSE; }

		$message = kohana::lang('ca_harvest.harvest-ok'); 
		
		// c'� già una raccolta in atto?
		
		$harvestinprogress = ORM::factory('character_action' ) -> 
			where ( array( 
				'action' => 'harvest',
				'status' => 'running',
				'param1' => $par[1] -> id ) ) -> count_all();
				
		if ( $harvestinprogress > 0 )
		{ $message = kohana::lang('ca_harvest.error-harvestalreadyinprogress'); return FALSE; }
		
		return true;
		
	}

	// Funzione per l'inserimento dell'azione nel DB.
	// Questa funzione appende solo una azione _non la esegue_
	// @input: array di parametri. 	
	// Per l'azione move uso tre parametri
	// $par[0] - struttura
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	public function append_action( $par, &$message )
	{
		
		$char = Character_Model::get_info( Session::instance()->get('char_id') );
				
		$this -> character_id = $par[0] -> id; 
		$this -> starttime = time();			
		$this -> status = "running";				
		$this -> endtime = $this -> starttime + $this -> get_action_time( $par[0] );
		$this -> param1 = $par[1] -> id; 
		$this -> save();
		
		/////////////////////////////////////////////////////////////////////////////////////////////////
		// evento
		/////////////////////////////////////////////////////////////////////////////////////////////////
		
		Structure_Event_Model::newadd( 
			$par[1] -> id, 
			'__events.startharvest' . ';' .
			$par[0] -> name ); 
										
		return true;
	}


	// Eseguo l'azione semina aggiornando lo status del terreno e aggiungendo
	// all'inventario del campo gli items del raccolto
	
	protected function complete_action( $data )
	{
	
		$char = ORM::factory("character", $data -> character_id );
		
		// Consumo degli items/vestiti indossati
		Item_Model::consume_equipment( $this->equipment, $char );	
		
		// Recupero il terreno che sto seminando tramite il primo parametro
				
		$terrain = StructureFactory_Model::create('terrain', $data -> param1);

		// Recupero i dati del mio char e gli assegno gli items (spare1 e spare3)
		// del raccolto (uso l'attributo 2 che memorizza l'id del tipo di item seminato)
		
		$item_seeded = ORM::factory( "cfgitem", $terrain->attribute2 );
		
		if ( $item_seeded -> loaded )	
	  {	
		
			/////////////////////////////////////////////////////////////////////////////////////////////////
			// Mettiamo i prodotti nel terreno in base al rapporto TIPO TERRENO - CLIMA della regione
			// in cui sto coltivando
			/////////////////////////////////////////////////////////////////////////////////////////////////
		
			// [SPARE2] Memorizza i dati relativi ad un terreno: PLAINS
			// [SPARE3] Memorizza i dati relativi ad un terreno: HILLS
			// [SPARE4] Memorizza i dati relativi ad un terreno: MOUNTAINS
			
			// Estrazione del giusto fattore di produzione in base alla regione
			
			$multiply = 0;
			switch ($terrain->region->geography)
			{
				case "plains": 
				$fieldfactors = explode( ';', $item_seeded -> spare2 ); break;
				case "hills":
				$fieldfactors = explode( ';', $item_seeded -> spare3 ); break;
				case "mountains":
				$fieldfactors = explode( ';', $item_seeded -> spare4 ); break;
			}
			foreach ( $fieldfactors as $fieldfactor )
			{
				list($factor, $clima) = explode( '-', $fieldfactor );
				if ($clima == $terrain->region->clima)
					$multiply = (float) $factor;
			}

			$fieldproducts = explode( ';', $item_seeded -> spare1 );
			$quantity = 0;
			
			foreach ( $fieldproducts as $fieldproduct )
			{
			
				kohana::log('debug', 'Geography:' . $terrain -> region -> geography );
				kohana::log('debug', 'Clima:' . $terrain -> region -> clima );				
				list($basequantity, $product) = explode( '-', $fieldproduct );
				kohana::log('debug', 'Base Quantity:' . $basequantity );
				kohana::log('debug', 'Multiply:' . $multiply );
				kohana::log('debug',' Product:' . $product );		
				
				$item = Item_Model::factory( null, $product );
				
				if ( $item -> cfgitem -> subcategory != 'seed' )
					$quantity = round( (float) $basequantity * $multiply);
				else
				{				
					$quantity = 2;
				}
				
				// Applica fattore 'carestia'
				kohana::log('debug', "-> Original quantity: {$quantity}");
				
				$productionfactor = Kohana::config('medeur.productionfactor');								
				$quantity = max(1, round($quantity * $productionfactor / 100, 0));
				
				kohana::log('debug', "-> Post Prod: {$productionfactor}, Factor quantity: {$quantity}");
				
				
				$item -> additem("structure", $data -> param1, $quantity );
				$char -> modify_stat( 'itemproduction', $quantity, $item -> cfgitem -> id );			
			
			}		
		
			// Cambio lo stato del terreno a "Incolto"
			
			$terrain->attribute1 = 0;
			$terrain->attribute2 = NULL;
			$terrain->save();
		
			// Sottraggo l'energia e sazietà al char
		
			$char->modify_energy ( - self::DELTA_ENERGY, false, 'harvest');
			$char->modify_glut (  - self::DELTA_GLUT);				
			$char->save();	
			
			// evento per quest
			
			$par[0] = $item;
			GameEvent_Model::process_event( $char, 'harvestfield', $par );
		
			
			
			Character_Event_Model::addrecord( 
				$char -> id, 
				'normal', '__events.harvestok',			
				'normal' );
				
			Structure_Event_Model::newadd( 
				$terrain -> id, 
				'__events.endharvest' . ';' .
				$char -> name 				
			); 	

			/////////////////////////////////////////////////////////////////////////////////////////////////
			// dai la paga oraria
			/////////////////////////////////////////////////////////////////////////////////////////////////
			
			if ( $char -> id != $terrain -> character_id )
				Job_Model::givehourlywage( $terrain, $char, $this -> basetime );
			
			
		}
		else
			kohana::log('error', 'warning, trying to harvest an unseeded field! ' . $terrain -> id ); 	  
	}

	
	public function cancel_action() {
	
		// evento in struttura
		
		$character = ORM::factory('character', $this -> character_id );
		$structure = StructureFactory_Model::create( null, $this -> param1 );
		
		if ( Structure_Grant_Model::get_chargrant( $structure, $character, 'worker' ) == true )
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
			$terrain = ORM::factory('structure', $pending_action->param1);
			$item_seeded = ORM::factory('cfgitem', $terrain->attribute2);

			if ( $type == 'long' )		
				$message = '__regionview.harvest_longmessage;__' . $item_seeded->name; 
			else
				$message = '__regionview.harvest_shortmessage';
		}
		return $message;
	
	}
		
	
}
