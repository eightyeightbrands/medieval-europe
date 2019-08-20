<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Craft_Model extends Character_Action_Model
{
	
	// Punti sazietà necessari per un'ora di lavoro
	const GLUT_HOUR = 1.325;
	
	// Punti energia necessari per un'ora di lavoro
	const ENERGY_HOUR = 1.325;

	protected $immediate_action = false;
	protected $cancel_flag = true;     // se true, la azione e' cancellabile dal pg.

	protected $basetime       = null;
	protected $attribute      = 'intel';  // attributo forza
	protected $appliedbonuses = array ( 'workerpackage', 'craftblessing' ); // bonuses da applicare
	
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
			'feet' => array
			(
				'items' => array('any'),
				'consume_rate' => 'low'
			),
			'right_hand' => array
			(
				'items' => array(),
				'consume_rate' => 'medium',
			),
		),
	);
	
	// Tools necessari in base al tipo di shop
	
	protected $tools = array
	(
		'blacksmith' => array
		(
			'right_hand' => 'hammer'
		),
		'carpenter' => array
		(
			'right_hand' => 'saw'
		),
		'chef' => array
		(
			'right_hand' => 'cookingpot'
		),
		'herbalist' => array
		(
			'right_hand' => 'mortarpestle'
		),
		'tailor' => array
		(
			'right_hand' => 'scissor'
		),
		'goldsmith' => array
		(
			'right_hand' => 'bellow'
		),
		'potter' => array
		(
			'right_hand' => 'pottertools'
		),
		'distillery' => array
		(
			'right_hand' => 'none'
		),
	);
	
	/*
	* Funzione per settare il valore di basetime
	* dall esterno (per visualizzare il tempo previsto di lavoro)
	*/
	
	public function set_basetime( $basetime )
	{
		$this -> basetime = $basetime;		
	}
	
	// Effettua tutti i controlli relativi all' azione, sia quelli condivisi
	// con tutte le action che quelli peculiari 
	// @input: array di parametri
	// par[0]: oggetto cfgitem che si vuole craftare
	// par[1]: oggetto char
	// par[2]: oggetto struttura
	// par[3]: moltiplicatore craft
	
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $message contiene il messaggio di ritorno	
	
	protected function check( $par, &$message )
	{
		// Verifico il tool necessario per operare nella bottega
		
		
		$rt = $this->get_required_tool( $par[2]->getSupertype(), 'right_hand');
		
		$this->equipment['all']['right_hand']['items'][] = $rt;

		// Check classe madre (compreso il check_equipment)
		if ( ! parent::check( $par, $message ) )					
		{ return false; }

		// Check: cfgitem non caricato
		// Check: char non caricato
		// Check: struttura non caricata
		if 
		(
			!$par[0]->loaded or 
			!$par[1]->loaded or 
			!$par[2]->loaded
		)
		{
			$message = kohana::lang( 'global.operation_not_allowed');
			return false;
		}
	
		// se la struttura è locked, può cragtare solo bread.
		
		if ($par[2] -> locked == true and $par[0] -> tag != 'bread' )
		{
			$message = kohana::lang('ca_craft.error-cancraftonlybread');
			return false;			
		}
	
		//////////////////////////////////////////////////////////////////////
		// controllo parametri URL
		//////////////////////////////////////////////////////////////////////
		
		$queuebonus = false;		
		if ( Character_Model::get_premiumbonus( $par[1] -> id, 'workerpackage' ) !== false  ) 		
			$queuebonus = true;
		
		//////////////////////////////////////////////////////////////////////
		// Controllo, se il moltiplicatore è > 1, il char deve avere il bonus
		//////////////////////////////////////////////////////////////////////

		if ( !in_array ( $par[3], array( 1, 2, 3 )) or ($par[3] > 1 and ! $queuebonus ) )
		{ $message = Kohana::lang("global.operation_not_allowed"); return false; }
		
		//////////////////////////////////////////////////////////////////////
		// Controllo, se l' item è craftabile con questo tipo di negozio		
		//////////////////////////////////////////////////////////////////////
		
		$craftableitems = Configuration_Model::get_craftableitems_structuretype();
		$structurecraftableitems = $craftableitems[$par[2] -> structure_type -> type];
		
		if ( !isset($structurecraftableitems[$par[0]->tag]	) )
		{ $message = Kohana::lang("global.operation_not_allowed"); return false; }
		
		// Check: il char non ha abbastanza energia
		// Chekc: il char non ha abbastanza sazietà
		// Il calcolo di energia e sazietà viene eseguito su base oraria
		// (ore base necessarie per produrre l'oggetto) al lordo di tutti i bonus
		// Calcolo le ore necessarie alla produzione dell'oggetto
		
		$data = self::get_required_energyglut( $par[0] -> spare2, $par[3] );
		
		kohana::log('debug', 'Craftyng Cycles: ' . $par[3]);
		kohana::log('debug', "Energy req: " . $data['requiredenergy'] . ", Glut req: " .	$data['requiredglut']);
		
		if
		(
			$par[1] -> energy < $data['requiredenergy']
			or
			$par[1] -> glut < $data['requiredglut']
		)
		{
			$message = Kohana::lang("charactions.notenoughenergyglut");
			return false;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////////////
		// se l' inventario della struttura � piena, non si può craftare		
		/////////////////////////////////////////////////////////////////////////////////////////////////
		
		if ( $par[2] -> get_storableweight() < 0 )
		{
			$message = kohana::lang( 'charactions.structure_fullinventory');
			return false;
		}		
		
		// check per crafting con 1 slot (il materiale deve essere * $par[3])
		
		$neededitems = $par[0] -> get_needed_items();			
		foreach ( $neededitems as $item )
		{
			if ( $item -> type == 'item' )
				if ( $par[2] -> contains_item( $item -> tag, $item -> quantity * $par[3] ) == false )			
				{
					$message = kohana::lang( 'charactions.craft_neededitemsmissing');
					return false;		
				}
			if ( $item -> type == 'tool' )
				if ( $par[2] -> contains_item( $item -> tag, $item -> quantity ) == false )
				{
					$message = kohana::lang( 'charactions.craft_neededitemsmissing');
					return false;		
				}
		}		
		
		// Tutti i check sono stati superati
		
		return true;
	}
	
	public function cancel_action() 
	{
	
		// evento in struttura
		
		$character = ORM::factory('character', $this -> character_id );		
		$structure = StructureFactory_Model::create( null, $this -> param2 );
		
		if ( Structure_Grant_Model::get_chargrant( $structure, $character, 'worker' ) == true )
			return false;

		Structure_Event_Model::newadd( $this -> param2,
			'__events.actioncanceled' . ';' . 
			$character -> name . ';' . 
			$this -> get_action_message('short')
			); 
			
		return true;
		
	}
	
	protected function append_action( $par, &$message )
	{
		
		// Rimuovo gli item necessari.
		$neededitems = $par[0] -> get_needed_items();
		foreach ( $neededitems as $item)
		{
			if ( $item -> type == 'item' )
			{
				$i = Item_Model::factory( null, $item -> tag );	
				$i -> removeitem( "structure", $par[2] -> id, $item -> quantity * $par[3]);
			}
		}
		
		// appendi l' azione.
		
		$this -> character_id = $par[1]->id;
		$this -> structure_id = $par[2]->id;		
		$this -> starttime = time();			
		$this -> status = "running";
		$this -> param1 = $par[0] -> id;
		$this -> param2 = $par[2] -> id;
		$this -> param3 = $par[3]; // memorizzo il moltiplicatore		
		$this -> basetime = $par[0] -> spare2 / 60;
		$this -> param4 = $par[0] -> spare2;
		
		// Calcolo del tempo di esecuzione
		
		$this -> endtime = $this -> starttime + $this -> get_action_time( $par[1] ) * $par[3];		
		$this -> save();
		
		// Consuma i tool in struttura (distiller ecc) 
		// qualora ci siano tool necessari per il crafting
		// per ogni working cycle
				
		foreach ( $neededitems as $item )
		{			
			if ( $item -> type == 'tool' )
				Item_Model::consumeitem_instructure( 
					$item -> tag, 
					$par[2] -> id, $item -> spare7 * $par[3]);
		}	
		
		// evento
		
		Structure_Event_Model::newadd( 
			$par[2] -> id, 
			'__events.startcraft' . ';' .
			$par[1] -> name . ';__' . 
			$par[0] -> name ); 
			
		Character_Event_Model::addrecord( 
			$par[1] -> id, 
			'normal', 
			'__events.startedcrafting' . 			
			';__' . $par[0] -> name . 
			';' . $par[3]);	
		
		$message = kohana::lang( 'charactions.shop_craftok');
		return true;
	}

	public function execute_action ( $par, &$message) {}
	
	public function complete_action( $data )
	{		
		
		$cfgitem = ORM::factory("cfgitem", $data->param1);
		$i = Item_Model::factory( null, $cfgitem->tag);
		$char = ORM::factory( "character", $data->character_id );
		$structure = StructureFactory_Model::create( null, $data -> param2 ); 
		
		// Creiamo l' oggetto
		
		$quantity = rand( $cfgitem->spare5,$cfgitem->spare6 ) * $data->param3;
		$i -> additem( "structure", $data -> param2, $quantity );	
		$char -> modify_stat( 'itemproduction', $quantity, $cfgitem -> id );	
		
		// triggera evento per quest
		
		$par[0] = $i;
		$par[1] = $quantity;
		$par[2] = $structure;
		
		GameEvent_Model::process_event( $char, 'craft', $par );		
		
		// evento a struttura
		
		Structure_Event_Model::newadd( 
			$structure -> id, 
			'__events.endcraftitem' . ';' .
			$char -> name . ';__' . 
			$cfgitem -> name . ';' . 
			$quantity
		); 					
		
		Character_Event_Model::addrecord( 
		$char -> id, 
		'normal', 
		'__events.finishedcrafting' . 
		';__' . $cfgitem -> name .
		';' . $quantity,
		'normal' );	
		
		// Aggiorna energia e glut
		// param4 contiene le ore originali necessarie per il crafting
		
		$_data = self::get_required_energyglut( $data -> param4, $data -> param3 );	
		
		$char -> modify_energy( - $_data['requiredenergy'], false, 'craft' );
		$char -> modify_glut( - $_data['requiredglut'] );	
		
		// Consumo degli items/vestiti indossati
		
		Item_Model::consume_equipment( $this->equipment, $char, $data->param3 );		

		// dai la paga oraria
		
		if ( $char -> id != $structure -> character_id )
			Job_Model::givehourlywage( $structure, $char, round( ($data -> param4 / 60) * $data -> param3 ) );

		$char -> save();		
		
	}
	
	// Questa funzione costruisce un messaggio da visualizzare 
	// in attesa che la azione sia completata.
	
	public function get_action_message( $type = 'long') 
	{
		$pending_action = $this -> get_pending_action();
		$message = "";
		$cfgitem = ORM::factory("cfgitem", $pending_action->param1);
		
		if ( $pending_action -> loaded )
		{

			if ( $type == 'long' )		
				$message = '__regionview.craft_longmessage';
			else
				$message = '__regionview.craft_shortmessage;__' . $cfgitem->name ;
		}
		return $message;
	
	}
	
	
	/*
	** Calcola energia e glut necessari 
	* @param int $craftingtime Tempo reale di crafting in minuti
	* @param int $cycles Numero di lavorazioni
	* @return array 
	* 	requiredenergy: energia richiesta (punti)
	*   required glut: glut richiesta (punti)
	*/

	static function get_required_energyglut( $craftingtime, $cycles )
	{
		
		kohana::log('debug', "-> Craftingtime: {$craftingtime} minutes, Cycles: {$cycles}");
		
		$data = array ('requiredenergy' => 0, 'requiredglut' => 0);
		
		$data['requiredenergy'] = min(50, round( $craftingtime / 60, 2 ) * self::ENERGY_HOUR * $cycles);
		$data['requiredglut'] 	= min(50, round( $craftingtime / 60, 2 ) * self::GLUT_HOUR * $cycles);
		
		kohana::log('debug',kohana::debug($data));
		
		return $data;
	}
	
	/*
	* Restituisce il tool necessario per lanciare l'azione
	* @input  string  $shop   tipo di negozio
	* @input  string  $type   cosa verificare right_hand/structure
	* @return string          tag dell'oggetto necessario
	*/
	
	public function get_required_tool( $shop, $type )
	{
		return $this->tools[$shop][$type];
	}
	
}
