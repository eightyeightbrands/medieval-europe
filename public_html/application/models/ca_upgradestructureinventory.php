<?php defined('SYSPATH') OR die('No direct access allowed.');	
class CA_Upgradestructureinventory_Model extends Character_Action_Model
{
	// Costanti
	const DELTA_ENERGY = 18;
	const DELTA_GLUT = 16;
	const REQUIREDWOOD = 15; 
	const REQUIREDBRICK = 70;
	const STORAGELIMIT = 10250000; // 10.250 Kili
	const UPGRADE = 250000; // 250 Kg

	protected $cancel_flag = true;
	protected $immediate_action = false;
	protected $basetime       = 12;  // 12 hours
	protected $attribute      = 'str';  // attributo destrezza
	protected $appliedbonuses = array ( 'workerpackage' );
	
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
				'items' => array('hammer'),
				'consume_rate' => 'high',
			),
		),
	);
	
	// Effettua tutti i controlli relativi al move, sia quelli condivisi
	// con tutte le action che quelli peculiari del dig
	// @input: $par[0] = structure, $par[1] = char
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function check( $par, &$message )
	{ 
		$message = kohana::lang( 'structures.upgradeinventory_ok' ); 
		
		if ( ! parent::check( $par, $message ) )					
			return false;

		// Limite su capacità
	
		if ( $par[0] -> getStorage() >= self::STORAGELIMIT ) 
		{ $message = kohana::lang('structures.maxstoragelimitexceeded'); return FALSE; }
					
		// Controllo energia e sazietà
		
		if ($par[1]->energy < self::DELTA_ENERGY or $par[1]->glut < self::DELTA_GLUT )
		{ $message = kohana::lang('charactions.notenoughenergyglut'); return FALSE; }
		
		// Controllo item necessari
		if ( 
			$par[0] -> get_item_quantity( 'wood_piece' ) < self::REQUIREDWOOD   
		or
			$par[0] -> get_item_quantity( 'brick' ) < self::REQUIREDBRICK    )
		{ $message = kohana::lang('structures.notenoughmaterial'); return FALSE; }
		
		
		return true;
	}


	// Funzione per l'inserimento dell'azione nel DB.
	// Questa funzione appende solo una azione _non la esegue_
	// Al momento non si prevedono azioni istantanee.
	// @input: $par[0] = structure, $par[1] = char
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function append_action( $par, &$message )
	{
		
		$this -> character_id = $par[1]->id;
		$this -> structure_id = $par[0]->id;
		$this -> starttime = time();			
		$this -> status = "running";			
		$this -> endtime = $this->starttime + $this -> get_action_time( $par[1] );
		$this -> save();		

		// rimuovo gli oggetti dall' inventario
		$wood =  Item_Model::factory( null, 'wood_piece' );
		$stone = Item_Model::factory( null, 'brick' ); 
	
		$wood->removeitem( "structure", $par[0] -> id, self::REQUIREDWOOD) ; 
		$stone->removeitem( "structure", $par[0] -> id, self::REQUIREDBRICK) ; 
		
		/////////////////////////////////////////////////////////////////////////////////////////////////
		// evento
		/////////////////////////////////////////////////////////////////////////////////////////////////
		
		Structure_Event_Model::newadd( 
			$par[0] -> id, 
			'__events.startupgradeinventory' . ';' .
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
		$char = ORM::factory( 'character', $data->character_id  );
		// Sottraggo l'energia e la sazietà al char
		$char->modify_energy ( - self::DELTA_ENERGY, false, 'workonstructure' );
		$char->modify_glut ( - self::DELTA_GLUT );
		$char->save();	
	
		// aggiungi storage alla struttura
		$structure = StructureFactory_Model::create( null, $data -> structure_id ) ; 
		$structure -> customstorage = $structure -> getStorage() + self::UPGRADE ; 
		$structure -> save(); 
		
		// Consumo degli items/vestiti obbligatori indossati
		Item_Model::consume_equipment( $this->equipment, $char );
		
		// evento 
		
		$newstorage = $structure -> getStorage() / 1000;
		
		Character_Event_Model::addrecord( $char->id, 'normal', '__events.structureupgradedinventory' . ';__' . 	
		$structure -> structure_type -> name . ';__' . 	$structure -> region -> name . ';' . $newstorage , 'evidence' );		
		
		Structure_Event_Model::newadd( 
				$structure -> id, 
				'__events.endupgradeinventory' . ';' .
				$char -> name 				
			); 
	
	}
	
	protected function execute_action() {}
	
	public function cancel_action() {
	
		// evento in struttura
		
		$character = ORM::factory('character', $this -> character_id );
		$structure = StructureFactory_Model::create( null, $this -> structure_id );
		
		if ( Structure_Grant_Model::get_chargrant( $structure, $character, 'workerpackage' ) == true )
			return false;
		
		Structure_Event_Model::newadd( $this -> structure_id,
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
			if ( $type == 'long' )					
			$message = '__regionview.upgradestructureinventory_longmessage';
			else
			$message = '__regionview.upgradestructureinventory_shortmessage';
		}
				
		return $message;
		
	}

	
}
