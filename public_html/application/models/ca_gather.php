<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Gather_Model extends Character_Action_Model
{
	// Costanti
	const DELTA_GLUT = 6;
	const DELTA_ENERGY = 12;

	protected $cancel_flag = true;
	protected $immediate_action = false;
	
	protected $basetime       = null; 
	protected $attribute      = 'dex';  // attributo destrezza
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
			'right_hand' => array
			(
				'items' => array(),
				'consume_rate' => 'low',
			),
		),
	);
	
	// Tools necessari in base al tipo di allevamento
	protected $tools = array
	(
		'breeding_silkworm' => array
		(
			'right_hand' => 'iron_bucket'
		),
		'breeding_bee' => array
		(
			'right_hand' => 'bellow'
		),
		'breeding_cow' => array
		(
			'right_hand' => 'iron_bucket'
		),
		'breeding_sheep' => array
		(
			'right_hand' => 'iron_bucket'
		),
		'breeding_pig' => array
		(
			'right_hand' => 'iron_bucket'
		),
	);	
	
	// Effettua tutti i controlli relativi al milk, sia quelli condivisi
	// con tutte le action che quelli peculiari del milk
	// @input: $par[0] = structure
	// @input: $par[1] = char
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function check( $par, &$message )
	{
		// Verifico il tool necessario per eseguire l'operazione in base al tipo
		// di allevamento
		$rt = $this->get_required_tool($par[0]->structure_type->supertype, 'right_hand');
		$this->equipment['all']['right_hand']['items'][] = $rt;
		
		// Check classe madre (compreso il check_equipment)
		if ( ! parent::check( $par, $message ) )					
		{ return false; }

		// Controllo che il char abbia almeno 5 punti di energia
		if (
			$par[1] -> energy < self::DELTA_ENERGY or
			$par[1] -> glut < self::DELTA_GLUT )
		{
			$message = Kohana::lang("charactions.notenoughenergyglut");
			return false;
		}
		
		// La struttura non ha il flag della raccolta attivo
		if ($par[0] -> attribute3 == 0)
		{ $message = kohana::lang( 'ca_gather.error-notimetogather'); return false; }
		
		// silkworm: non è possibile raccogliere niente
		if ( $par[0]->structure_type->type == 'breeding_silkworm' )
		{
			$message = Kohana::lang("global.operation_not_allowed");
			return false;
		}
		
		// c'è già una raccolta in atto?
		$gatherinprogress = ORM::factory('character_action' ) -> 
			where ( array( 
				'action' => 'gather',
				'status' => 'running',
				'param1' => $par[0] -> id ) ) -> count_all();
				
		if ( $gatherinprogress > 0 )
		{ $message = kohana::lang('ca_gather.error-gatheralreadyinprogress'); return FALSE; }
		
		$message = kohana::lang( 'ca_gather.gather-ok' ); 
		
		return true;
	}
	
	protected function append_action( $par, &$message )
	{
		
		$this -> character_id = $par[1] -> id;
		$this -> starttime = time();			
		$this -> status = "running";
		$info = $this->get_breedingtype_data( $par[0]->structure_type->type );
		$this -> basetime = $info['time'] * $par[0]->attribute1; 
		$this -> endtime = $this->starttime + $this->get_action_time( $par[1] );
		
		// Memorizzo l'id dell'allevamento
		$this -> param1 = $par[0]->id;		
		$this -> param2 = $this -> basetime;
		$this -> save();				
		
		Structure_Event_Model::newadd( 
			$par[0] -> id, 
			'__events.startgather' . ';' .
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
		$structure = StructureFactory_Model::create( null, $data -> param1 );
		
		if ( $structure -> loaded )
		{
			$char = ORM::factory( 'character', $data->character_id );
			
			// Consumo degli items/vestiti indossati
			Item_Model::consume_equipment( $this->equipment, $char );
			
			// Sottraggo l'energia e la sazietà al char
			
			$char -> modify_energy ( - self::DELTA_ENERGY, false, 'gather' );
			$char -> modify_glut ( - self::DELTA_GLUT );
			$char -> save();
			

			// Aggiungo all'inventario del char i pezzi di latte, letame, miele ecc...
			// in base al numero degli animali ancora vivi	
			
			$info = $this -> get_breedingtype_data( $structure->structure_type->type );			
			foreach ( $info['product'] as $product )
			{
				kohana::log('debug', kohana::debug( $product )); 
				$item = Item_Model::factory( null, $product['type'] );
				$quantity = intval( $product['quantity'] * $structure -> attribute1 );
				
				// Applica fattore 'carestia'
				kohana::log('debug', "-> Original quantity: {$quantity}");
				
				$productionfactor = Kohana::config('medeur.productionfactor');								
				$quantity = max(1, round($quantity * $productionfactor / 100, 0));
				
				kohana::log('debug', "-> Post Prod: {$productionfactor}, Factor quantity: {$quantity}");
				
				$item -> additem("structure", $structure->id, $quantity); 								
				$char -> modify_stat( 'itemproduction', $quantity, $item->cfgitem->id );						
			} 
			
			// Riporto la flag dell'allevamento a non mungibile
			
			$structure->attribute3 = 0;
			$structure->save();
			
			// invio evento informativo		
			
			Character_Event_Model::addrecord( $char->id, 'normal', '__events.gatheringfinished' ); 			
			
			Structure_Event_Model::newadd( 
				$structure -> id, 
				'__events.endgather' . ';' .
				$char -> name 
			); 	
			
			/////////////////////////////////////////////////////////////////////////////////////////////////
			// dai la paga oraria
			/////////////////////////////////////////////////////////////////////////////////////////////////
			
			if ( $char -> id != $structure -> character_id )
				Job_Model::givehourlywage( $structure, $char, $data -> param2 );

				
		}
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
				$message = '__regionview.gather_longmessage';				
			else
				$message = '__regionview.gather_shortmessage';
		
		}
		
		return $message;
	
	}

	/**
	* Torna i dati necessari per la computazione a seconda
	* del tipo di allevamento
	* @param  breedingtype tipo allevamento
	* @return info vettore con info:
	* 	product array di prodotti
	*   	tipo: tipo prodotto
	*     quantità: quantità per capo
	*   time: tempo per sfamare un animale
	*
	*/
	protected function get_breedingtype_data( $breedingtype )
	{
		$info = array ( 'foodtype' => null, 'foodquantity' => 0, 'type' => null, 'quantity' => 0);
		
		if ($breedingtype == 'breeding_cow')
		{
			$info['product'][0]['type'] = 'jar_milk';
			$info['product'][0]['quantity'] = 6;
			$info['product'][2]['type'] = 'manure';
			$info['product'][2]['quantity'] = 1;
			$info['time'] = 0.25;
		}
		
		if ($breedingtype == 'breeding_sheep')
		{
			$info['product'][0]['type'] = 'jar_milk';
			$info['product'][0]['quantity'] = 4;
			$info['product'][2]['type'] = 'manure';
			$info['product'][2]['quantity'] = 1;
			$info['time'] = 0.25;
		}
		
		if ($breedingtype == 'breeding_pig')
		{
			$info['product'][2]['type'] = 'manure';
			$info['product'][2]['quantity'] = 4;
			$info['time'] = 0.25;
		}

		if ($breedingtype == 'breeding_bee')
		{			
			$info['product'][0]['type'] = 'honey';
			$info['product'][0]['quantity'] = 0.01	;
			$info['time'] = 0.00139;
		}
		
		return $info;
	
	}

	/*
	* Restituisce il tool necessario per lanciare l'azione
	* @input  string  $shop   tipo di allevamento
	* @input  string  $type   cosa verificare right_hand/structure
	* @return string          tag dell'oggetto necessario
	*/
	public function get_required_tool( $shop, $type )
	{
		return $this->tools[$shop][$type];
	}
}
