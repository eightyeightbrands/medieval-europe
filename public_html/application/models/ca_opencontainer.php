<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Opencontainer_Model extends Character_Action_Model
{
	// Parametri
	const DELTA_GLUT = 1;
	const DELTA_ENERGY = 1;

	protected $cancel_flag = true;
	protected $immediate_action = false;
	
	protected $basetime       = 0.05; 
	protected $attribute      = 'intel';  // attributo forza
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
			'right_hand' => array
			(
				'items' => array('hammer'),
				'consume_rate' => 'veryhigh',
			),
		),
	);

	
	/*
	* Effettua tutti i controlli relativi alla open container e 
	* quelli condivisi con tutte le action 
	* @input:  $par[0] = item id
	* @input:  $par[1] = char
	* @output: TRUE = azione disponibile, FALSE = azione non disponibile
	* @output: $messages contiene gli errori in caso di FALSE
	*/
	protected function check( $par, &$message )
	{ 
		// Check classe madre (compreso il check_equipment)
		if ( ! parent::check( $par, $message ) )	
		{ return false; }
		
		// Istanzio la easter egg
		$container = ORM::factory('item', $par[0]);
		
		// Check: l'item non è nell'inventario del char
		if ( $container->character_id != $par[1]->id )
		{ $message = Kohana::lang("ca_opencontainer.item-not-inventory"); return false; }
		
		// Check: l'item non è un easteregg
		if ( $container->cfgitem->tag != "easteregg" )
		{ $message = Kohana::lang("ca_opencontainer.item-wrong"); return false; }
				
		// Check: il char non ha l'energia sufficiente
		// Check: il char non ha la saizetà sufficiente
		if
		(
			$par[1]->energy < (self::DELTA_ENERGY) or
			$par[1]->glut < (self::DELTA_GLUT)
		)
		{  $message = Kohana::lang("charactions.notenoughenergyglut"); return false; }
		
		// Tutti i check sono stati superati
		return true;
	}

	
	/*
	* Funzione per l'inserimento dell'azione nel DB.
	* @input:  $par[0] = item id
	* @input:  $par[1] = char
	* @output: TRUE = azione disponibile, FALSE = azione non disponibile
	* @output: $messages contiene gli errori in caso di FALSE
	*/
	protected function append_action( $par, &$message )
	{
		$this->character_id = $par[1]->id;
		$this->starttime = time();			
		$this->status = "running";			
		$this->endtime = $this -> starttime + $this -> get_action_time( $par[1] );
		
		//$this -> starttime + 180;

		$this->param1 = $par[0]; // Id del container
		$this->save();		
		
		$message = kohana::lang('ca_opencontainer.open-ok');	
		
		return true;
	}
	
	
	/*
	* Esecuzione dell'azione
	* @input:  $data  dati della char action
	*/
	public function complete_action( $data )
	{
		// Istanzio il character e il contaier
		$char      = ORM::factory('character', $data -> character_id);
		$container = ORM::factory('item', $data -> param1);

		// Consumo degli items/vestiti indossati
		Item_Model::consume_equipment( $this->equipment, $char );	
		
		// Sottraggo l'energia e la sazietà al char
		$char->modify_energy( -self::DELTA_ENERGY );
		$char->modify_glut( -self::DELTA_GLUT );
		$char->save();	

		kohana::log('info', "Char: {$char -> name}, rnd: {$container->param1}");
		
		// Analizzo il contenuto della easteregg
		Character_Event_Model::addrecord
		( 
			$char -> id, 
			'normal',
			'__events.prize-item' .
			';' . $container -> param1,
			'evidence'
		);

		$container -> destroy();		
		
		return; 
		
	}
	
	
	protected function execute_action() {}
	
	
	public function cancel_action( )
	{ return true; }
	
	
	/*
	* Questa funzione costruisce un messaggio da visualizzare 
	* in attesa che la azione sia completata.
	* @input: $type  string  tipo di messaggio da restituire (forma estesa o corta)
	*/
	public function get_action_message( $type = 'long') 
	{
		$pending_action = $this->get_pending_action();
		$message = "";				
		
		if ( $pending_action->loaded )
		{
			if ( $type == 'long' )					
			$message = '__regionview.opencontainer_longmessage';
			else
			$message = '__regionview.opencontainer_shortmessage';
		}
				
		return $message;
	}
	
}
