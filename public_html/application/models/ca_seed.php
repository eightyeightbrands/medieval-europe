<?php defined('SYSPATH') OR die('No direct access allowed.');


class CA_Seed_Model extends Character_Action_Model
{
	// Costanti
	const DELTA_GLUT = 7; 					// consumo di sazietà	
	const DELTA_ENERGY = 8;         // Energia necessaria per la semina		
	const TIME_TO_GROW = 36000 ;    // Tempo necessario per la crescita

	protected $cancel_flag = true;     // se true, la azione è cancellabile dal pg.	
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
				'consume_rate' => 'medium'
			),
			'torso' => array
			(
				'items' => array('any'),
				'consume_rate' => 'medium'
			),
			'legs' => array
			(
				'items' => array('any'),
				'consume_rate' => 'medium'
			),
			'right_hand' => array
			(
				'items' => array('hoe'),
				'consume_rate' => 'medium',
			),
		),
	);
	
	// Effettua tutti i controlli relativi al seed, sia quelli condivisi
	// con tutte le action che quelli peculiari del seed
	// @input: array di parametri 
	// par[0] = terreno, 
	// par[1] = oggetto da seminare
	// par[2] = oggetto char
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE	
	
	protected function check( $par, &$message )
	{ 
		// Check classe madre (compreso il check_equipment)
		if ( ! parent::check( $par, $message ) )					
		{ return false; }
		
		/////////////////////////////////////////////////////////////////////////////////////////////////
		// Controllo che il char abbia energia a sufficienza
		/////////////////////////////////////////////////////////////////////////////////////////////////
		
		if ( $par[2] -> energy < self::DELTA_ENERGY or $par[2] -> glut < self::DELTA_GLUT )
		{
			$message = Kohana::lang("charactions.notenoughenergyglut");
			return false;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////////////
		// Controllo che ci sia nell' inventario del terreno il seme selezionato		
		/////////////////////////////////////////////////////////////////////////////////////////////////

		if ( $par[0] -> contains_item( $par[1] -> cfgitem -> tag, 1 ) == false )			
		{ $message = kohana::lang('ca_seed.error-noseed'); return FALSE; }

		if ( $par[0] -> contains_item( 'fertilizer', 1 ) == false )			
		{ $message = kohana::lang('ca_seed.error-nofertilizer'); return FALSE; }
	
		/////////////////////////////////////////////////////////////////////////////////////////////////
		// Controllo che il campo sia incolto
		/////////////////////////////////////////////////////////////////////////////////////////////////
		
		if ($par[0]->attribute1 != 0)
		{ $message = kohana::lang('ca_seed.error-fieldmustbeuncultivated'); return FALSE; }

		
		// c'è già una semina in atto?
		
		$seedinprogress = ORM::factory('character_action' ) -> 
			where ( array( 
				'action' => 'seed',
				'status' => 'running',
				'param1' => $par[0] -> id ) ) -> count_all();
				
		if ( $seedinprogress > 0 )
		{ $message = kohana::lang('ca_seed.error-seedalreadyinprogress'); return FALSE; }
		
		return true;
	}

	// Funzione per l'inserimento dell'azione nel DB.
	// Questa funzione appende solo una azione _non la esegue_
	// @input: array di parametri. 	
	// Per l'azione move uso due parametri
	// $par[0] = campo da seminare
	// $par[1] = item da seminare
	// $par[2] = oggetto char
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function append_action( $par, &$message )
	{
	
		$char = Character_Model::get_info( Session::instance()->get('char_id') );		
		
		$this -> starttime = time();			
		$this -> status = "running";
		$this -> character_id = $par[2] -> id;
		
		// Il tempo di semina varia in base alla forza del char
		$this->endtime = $this -> starttime + $this -> get_action_time( $char );
				
		// Id del campo da seminare
		$this -> param1 = $par[0]->id;
		
		// Memorizzo l'id del tipo di item che sto coltivando
		
		$this -> param2 = $par[1] -> cfgitem -> id;
		$this -> save();
				
		// Consumo l'item che ho seminato
		$par[1] -> removeitem( "structure", $par[0]->id, 1 );

		// Consumo il fertilizzante
		$fertilizer = Item_Model::factory( null, 'fertilizer');
		$fertilizer -> removeitem( "structure", $par[0]->id, 1 );		
		
		/////////////////////////////////////////////////////////////////////////////////////////////////
		// evento
		/////////////////////////////////////////////////////////////////////////////////////////////////
		
		Structure_Event_Model::newadd( 
			$par[0] -> id, 
			'__events.startseed' . ';' .
			$par[2] -> name . ';__' . 
			$par[1] -> cfgitem -> name ); 
			
		$message = kohana::lang('ca_seed.seed-ok');	


		return true;
	}

	// Funzione che annulla l'azione seed. Il char perde il sacco di grano che stava seminando
	
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
	
	// Eseguo l'azione semina:
	// - Aggiorno lo status del terreno
	// - Memorizzo sul terreno l'id del tipo di item seminato
	// - Memorizzo la data di fine maturazione del campo
	// - Sottraggo l'energia al char	
	
	public function complete_action( $data )
	{
	
		$char = ORM::factory("character", $data -> character_id );
		
		// Consumo degli items/vestiti indossati
		Item_Model::consume_equipment( $this->equipment, $char );	
		
		// Recupero il terreno che sto seminando tramite il primo parametro
		
		$terrain = StructureFactory_Model::create('terrain', $data -> param1);
		
		// Cambio lo stato del terreno a "Seminato"
		$terrain->attribute1 = 1;
		
		// Memorizzo l'id del tipo di item che sto coltivando
		
		$terrain->attribute2 = $data->param2;
		$terrain->attribute3 = (self::TIME_TO_GROW / kohana::config('medeur.serverspeed')) + time();
		$terrain->save();

		// Sottraggo l'energia e 1 punto di sazietà al char

		$char -> modify_energy ( - self::DELTA_ENERGY, false, 'seed' );
		$char -> modify_glut ( - self::DELTA_GLUT );
		$char -> save();
		
		// Appendo una char action chained: growfield
		$message=null;
		$a = Character_Action_Model::factory("growfield");
		
		$par[0] = $terrain->id;
		$par[1] = $data->param2;
		
		// Il tempo varia in base al tipo di coltivazione;
		
		$typeseed = ORM::factory("cfgitem", $data->param2);	
		$par[2] = $typeseed -> spare5;
		$par[3] = (self::TIME_TO_GROW/kohana::config('medeur.serverspeed'));
		$par[4] = $char;		

		$a -> do_action( $par, $message );
		
		// evento
		
		Character_Event_Model::addrecord( 
			$char -> id, 
			'normal', '__events.seedok',			
			'normal' );
		
		Structure_Event_Model::newadd( 
				$terrain -> id, 
				'__events.endseed' . ';' .
				$char -> name . ';__' .
				$typeseed -> name
		); 	

		
		
		// evento per quest
		$par[0] = $typeseed;
		GameEvent_Model::process_event( $char, 'seedfield', $par );
		
		/////////////////////////////////////////////////////////////////////////////////////////////////
		// dai la paga oraria
		/////////////////////////////////////////////////////////////////////////////////////////////////
		
		if ( $char -> id != $terrain -> character_id )
			Job_Model::givehourlywage( $terrain, $char, $this -> basetime );
		
	}

	// Questa funzione costruisce un messaggio da visualizzare 
	// in attesa che la azione sia completata.
	
	public function get_action_message( $type = 'long') 
	{
		$pending_action = $this->get_pending_action();
		$message = "";
		
		if ( $pending_action -> loaded )
		{
			$item_seeded = ORM::factory('cfgitem', $pending_action->param2);

			if ( $type == 'long' )		
				$message = '__regionview.seed_longmessage;__' . $item_seeded->name;
			else
				$message = '__regionview.seed_shortmessage';
		}
		return $message;
	
	}
	
}
