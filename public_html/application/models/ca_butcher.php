<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Butcher_Model extends Character_Action_Model
{
	// Costanti
	const DELTA_GLUT = 6;
	const DELTA_ENERGY = 12;

	const MEAT_FOR_ANIMAL_COW = 6;
	const LEATHER_FOR_ANIMAL_COW = 10;
	const MANURE_FOR_ANIMAL_COW = 1;
	const TIME_FOR_ANIMAL_COW = 0.5; // 30 minuti per capo

	const MEAT_FOR_ANIMAL_SHEEP = 0;
	const WOOL_FOR_ANIMAL_SHEEP = 13;
	const MANURE_FOR_ANIMAL_SHEEP = 1;
	const TIME_FOR_ANIMAL_SHEEP = 0.5; 

	const MEAT_FOR_ANIMAL_PIG = 12;
	const MANURE_FOR_ANIMAL_PIG = 4;
	const TIME_FOR_ANIMAL_PIG = 0.5; // 30 minuti per capo
	
	const SILK_FOR_ANIMAL_SILKWORM = 0.3;
	const TIME_FOR_ANIMAL_SILKWORM = 0.0075; 
	
	const WAX_FOR_ANIMAL_BEE = 0.05;
	const TIME_FOR_ANIMAL_BEE = 0.00138;
	
	protected $cancel_flag = true;
	protected $immediate_action = false;
	
	protected $basetime       = null;  // 1 hour
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
				'consume_rate' => 'high'
			),
			'torso' => array
			(
				'items' => array('any'),
				'consume_rate' => 'high'
			),
			'legs' => array
			(
				'items' => array('any'),
				'consume_rate' => 'high'
			),
			'right_hand' => array
			(
				'items' => array(),
				'consume_rate' => 'high',
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
			'right_hand' => 'knife'
		),
		'breeding_sheep' => array
		(
			'right_hand' => 'knife'
		),
		'breeding_pig' => array
		(
			'right_hand' => 'knife'
		),
	);
	
	// Effettua tutti i controlli relativi al butcher, sia quelli condivisi
	// con tutte le action che quelli peculiari del butcher
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

		// Controllo che il char abbia l' energia necessaria
		if (
			$par[1] -> energy < self::DELTA_ENERGY or
			$par[1] -> glut < self::DELTA_GLUT )
		{
			$message = Kohana::lang("charactions.notenoughenergyglut");
			return false;
		}
		
		if ($par[0] -> attribute4 == 0)
		{ $message = kohana::lang( 'ca_butcher.error-notimetobuther'); return false; }
		
		// se i capi sono 0, errore
		
		if ($par[0] -> attribute1 == 0)
		{ $message = kohana::lang( 'ca_butcher.error-noanimals'); return false; }
		
		// controllo che la struttura abbia spazio in magazzino		
		if ( $par[0] -> get_storableweight() <= 0 )
			{ $message = kohana::lang('charactions.structure_fullinventory'); return FALSE; }		

		// c'è già una raccolta in atto?
		
		$butcherinprogress = ORM::factory('character_action' ) -> 
			where ( array( 
				'action' => 'butcher',
				'status' => 'running',
				'param1' => $par[0] -> id ) ) -> count_all();
				
		if ( $butcherinprogress > 0 )
		{ $message = kohana::lang('ca_butcher.error-butcheralreadyinprogress'); return FALSE; }
		
		$message = kohana::lang( 'ca_butcher.butcher-ok' ); 
		
		return true;
	}
	
	protected function append_action( $par, &$message )
	{
				
		$this->character_id = Session::instance()->get('char_id');
		$this->starttime = time();			
		$this->status = "running";
		
		// Imposto il tempo in base agli animali presenti nell'allevamento
		// e in base al tipo di allevamento
		$info = $this->get_breedingtype_data( $par[0]->structure_type->type );
		$this -> basetime = $info['time'] * $par[0] -> attribute1;
		
		//var_dump( $basetime ); exit;
		
		$this -> endtime = $this -> starttime + $this -> get_action_time( $par[1] );
		
		// Memorizzo l'id dell'allevamento
		$this -> param1 = $par[0]->id;
		$this -> param2 = $this -> basetime;
		$this -> save();				
				
		Structure_Event_Model::newadd( 
			$par[0] -> id, 
			'__events.startbutcher' . ';' .
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

		if ($structure)
		{
			
			$char = ORM::factory('character', $data->character_id );
			
			// Sottraggo l'energia e la sazietà al char		
			$char->modify_energy ( -self::DELTA_ENERGY, false, 'butchering' );
			$char->modify_glut ( -self::DELTA_GLUT );
			$char->save();
			
			// Consumo degli items/vestiti indossati
			Item_Model::consume_equipment( $this->equipment, $char );	
			
			// Aggiungo all'inventario del char i pezzi di carne, cuoio, lana ecc...
			// in base al numero degli animali ancora vivi	
			
			$info = $this -> get_breedingtype_data( $structure->structure_type->type );
			foreach ( $info['product'] as $product )
			{
				$item = Item_Model::factory( null, $product['type'] );
				$quantity = intval($product['quantity'] * $structure->attribute1);
				
				// Applica fattore 'carestia'
				kohana::log('debug', "-> Original quantity: {$quantity}");
				
				$productionfactor = Kohana::config('medeur.productionfactor');								
				$quantity = max(1, round($quantity * $productionfactor / 100, 0));
				
				kohana::log('debug', "-> Post Prod: {$productionfactor}, Factor quantity: {$quantity}");			
				
				$item->additem("structure", $structure->id, $quantity); 				
				// stats
				$char -> modify_stat( 
					'itemproduction', 
					$quantity, 
					$item -> cfgitem -> id );						
			}
			
			// mando un evento al proprietario
			// invio evento informativo		
			
			Character_Event_Model::addrecord( $char -> id, 'normal', '__events.butcheringfinished' ); 
			
			// setto il flag di macellazione a 2, cosi non appare ancora il flag
			
			$structure->attribute1 = 0;
			$structure->attribute4 = 2;
			$structure->save();
		}
		
		Structure_Event_Model::newadd( 
			$structure -> id, 
			'__events.endbutcher' . ';' .
			$char -> name 
		);

		/////////////////////////////////////////////////////////////////////////////////////////////////
		// dai la paga oraria
		/////////////////////////////////////////////////////////////////////////////////////////////////

		if ( $char -> id != $structure -> character_id )
			Job_Model::givehourlywage( $structure, $char, $data -> param2 );

		
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
		$info = array ( 'foodtype', 'foodquantity' );
		
		if ($breedingtype == 'breeding_cow')
		{
			$info['product'][0]['type'] = 'meat';
			$info['product'][0]['quantity'] = self::MEAT_FOR_ANIMAL_COW;
			$info['product'][1]['type'] = 'leather_piece';
			$info['product'][1]['quantity'] = self::LEATHER_FOR_ANIMAL_COW;
			$info['product'][2]['type'] = 'manure';
			$info['product'][2]['quantity'] = self::MANURE_FOR_ANIMAL_COW;
			$info['time'] = self::TIME_FOR_ANIMAL_COW ;
		}
		
		if ($breedingtype == 'breeding_sheep')
		{
			
			$info['product'][1]['type'] = 'wool_yarn';
			$info['product'][1]['quantity'] = self::WOOL_FOR_ANIMAL_SHEEP;
			$info['product'][2]['type'] = 'manure';
			$info['product'][2]['quantity'] = self::MANURE_FOR_ANIMAL_SHEEP;
			$info['time'] = self::TIME_FOR_ANIMAL_SHEEP ;
		}
		
		if ($breedingtype == 'breeding_pig')
		{
			$info['product'][0]['type'] = 'meat';
			$info['product'][0]['quantity'] = self::MEAT_FOR_ANIMAL_PIG;
			$info['product'][1]['type'] = 'manure';
			$info['product'][1]['quantity'] = self::MANURE_FOR_ANIMAL_PIG;
			$info['time'] = self::TIME_FOR_ANIMAL_PIG ;
		}

		if ($breedingtype == 'breeding_silkworm')
		{			
			$info['product'][0]['type'] = 'silk_yarn';
			$info['product'][0]['quantity'] = self::SILK_FOR_ANIMAL_SILKWORM;
			$info['time'] = self::TIME_FOR_ANIMAL_SILKWORM ;
		}

		if ($breedingtype == 'breeding_bee')
		{			
			$info['product'][0]['type'] = 'wax_piece';
			$info['product'][0]['quantity'] = self::WAX_FOR_ANIMAL_BEE;
			$info['time'] = self::TIME_FOR_ANIMAL_BEE;
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
				
		$structure = StructureFactory_Model::create( null, $pending_action -> param1 );
		
		if ( $pending_action -> loaded )
		{
			$now = date("F d, Y H:i:s", time() );
			if ( $type == 'long' )
			{
				if ( $structure->structure_type->type == 'breeding_silkworm' )
					$message = '__regionview.gettingsilk_longmessage';				
				else
					$message = '__regionview.butcher_longmessage';				
			}
			else
			{
				if ( $structure->structure_type->type == 'breeding_silkworm' )
					$message = '__regionview.gettingsilk_shortmessage';				
				else
					$message = '__regionview.butcher_shortmessage';	
			}
		}
		return $message;
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
