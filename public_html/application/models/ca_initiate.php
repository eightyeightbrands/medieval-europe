<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Initiate_Model extends Character_Action_Model
{
	// Costanti	
	const DELTA_GLUT = 5;
	const DELTA_ENERGY = 5;
	const GOLDENBASIN_WEAR = 25;
	const REQUESTEDFP = 1;
	const INITIALFAITHLEVEL = 30;
	const FAITHLEVELREQUESTED = 90;
	const EXCOMMUNICATIONPERIOD = 7776000; // 3 mesi 
	
	// Azione cancellabile
	protected $cancel_flag = true;
	protected $immediate_action = false;
	protected $fpcost = 0;

	protected $basetime       = 2.5;  
	protected $attribute      = 'intel';  // attributo intelligenza
	protected $appliedbonuses = array ( 'workerpackage' ); // bonuses da applicare
	
	// L'azione richiede che il personaggio indossi
	// un determinato equipaggiamento
	protected $requiresequipment = true;
	
	// Equipaggiamento o vestiario necessario in base al ruolo
	protected $equipment = array
	(
		'church_level_4' => array
		(
			'right_hand' => array
			(
				'items' => array('holybook'),
				'consume_rate' => 'high',
			),
			'body' => array
			(
				'items' => array('tunic_church_level_4_rome', 'tunic_church_level_4_turnu', 'tunic_church_level_4_kiev', 'tunic_church_level_4_cairo','tunic_church_level_4_norse'),
				'consume_rate' => 'high',
			),
			'feet' => array
			(
				'items' => array('shoesm_1', 'shoesf_1'),
				'consume_rate' => 'high',
			),
		),
	);
	
	
	/* 
	* Controlli preliminari al lancio/esecuzione dell'azione
	* @input: 
	* $par[0] = char che esegue iniziazione,
	* $par[1] = char che viene iniziato
	* $par[2] = struttura controllata dal personaggio che esegue l'iniziazione, 	
	* @output: TRUE = azione disponibile, FALSE = azione non disponibile
	*          $messages contiene gli errori in caso di FALSE
	*/
	protected function check( $par, &$message )
	{ 
		
		$message = "";
		
		if ( ! parent::check( $par, $message ) )					
			return false;
		
		// Check: qualche parametro non è caricato
		
		if
		(
			!$par[0] -> loaded or 
			!$par[1] -> loaded or 
			!$par[2] -> loaded
		)
		{ $message = Kohana::lang("global.operation_not_allowed"); return false; }		
				
		// Il char deve essere ateo		
		
		if ($par[1] -> church -> name != 'nochurch' )
		{ $message = Kohana::lang("ca_initiate.error-targetcharisnotatheist"); return false; }
		
		// Check: il char che esegui l'iniziazione non ha un ruolo
		// religioso di livello 4
		
		$role = $par[0] -> get_current_role();
		if ( $role->tag != 'church_level_4' )
		{ $message = Kohana::lang("ca_initiate.error-onlylevel4canbaptize"); return false; }
	
		// Check: i personaggi non si trovano nella stessa regione della
		// struttura religiosa controllata dal sacerdote
		
		if
		(
			$par[2] -> region -> id != $par[0] -> position_id or
			$par[0] -> position_id != $par[1] -> position_id
		)
		{ $message = Kohana::lang("ca_initiate.error-notinsameregionofstructure"); return false; }
		
		// Check: il char è scomunicato (non può essere battezzato per 3 mesi)
		
		$excommunication = $par[0]-> get_stat( 
			'excommunication', $par[2] -> structure_type -> church_id ); 
		if
		( 
			$excommunication -> loaded and 
			( $excommunication -> value + self::EXCOMMUNICATIONPERIOD ) > time() 
		)
		{ $message = Kohana::lang("ca_initiate.charisexcommunicated"); return false; }		
						
		// Check: il char da iniziare impegnato in altra azione
		
		$pendingaction = Character_Action_Model::get_pending_action( $par[1] -> id ); 
		if ( !is_null( $pendingaction ) )
		{ $message = Kohana::lang("global.error-characterisbusy", $par[1] -> name ); return false; }
		
		// Check: il sacerdote non ha livello fede sufficiente
		
		$fl = $par[0] -> get_stat( 'faithlevel' );
		if ( $fl -> value < self::FAITHLEVELREQUESTED )
		{ $message = Kohana::lang("global.error-charisnotfaithfulenough", self::FAITHLEVELREQUESTED); return false; }				
		
		// Check: il char che esegue l'iniziazione non ha energia o sanietà sufficienti
		
		if
		(
			$par[0] -> energy < (self::DELTA_ENERGY )  or
			$par[0] -> glut < (self::DELTA_GLUT)
		)
		{ $message = Kohana::lang("charactions.notenoughenergyglut"); return false; }
		
		// Check: la struttura religiosa non contiene almeno un golden basin al 25%
		
		$exists = false;
		foreach ( $par[2] -> item as $item )
			if ( $item -> cfgitem -> tag == 'goldenbasin' and $item -> quality >= self::GOLDENBASIN_WEAR )
				$exists = true;
		if ( $exists == false )
		{
			$message = Kohana::lang
			(
				"ca_initiate.goldenbasinnotexists",
				kohana::lang('global.' . $role -> tag . '_' . $par[2] -> character -> church -> name )
			);
			return false;
		}
		
		// Check: la struttura non ha sufficienti FP
		
		$fp = Structure_Model::get_stat_d( $par[2] -> id, 'faithpoints' ); 		
		$this -> fpcost = $this -> get_neededfp( $par );				
		if
		(
			! $fp -> value or 
			$fp -> value < $this -> fpcost 
		)
		{
			$message = Kohana::lang("ca_initiate.notenoughfp");
			return false;
		}		
		
		return true;
	
	}

	
	/* 
	* Funzione per l'inserimento dell'azione nel DB.
	* @input: 
	* $par[0] = char che esegue iniziazione,
	* $par[1] = char che viene iniziato
	* $par[2] = struttura controllata dal che che esegue l'iniziazione, 	
	* @output: TRUE = azione disponibile, FALSE = azione non disponibile
	*          $messages contiene gli errori in caso di FALSE
	*/
	protected function append_action( $par, &$message )
	{
		// Salva una action blocking per chi deve ricevere l' iniziazione
		// **************************************************************
		$this -> character_id = $par[1] -> id;
		$this -> starttime = time();		
		$this -> status = "running";	
		
		// Salva il char di chi svolge la funzione religiosa
		$this -> param1 = $par[0] -> id;

		// Salva il char di chi subisce la funzione religiosa
		$this -> param2 = $par[1] -> id;
		
		// Salva l' id della struttura religiosa
		$this -> param3 = $par[2] -> id;
		$this -> endtime = $this -> starttime + $this -> get_action_time( $par[0] );
		$this -> save();
		
		// Salva una action blocking per chi sta svolgendo l'iniziazione
		// *************************************************************
		$a = new Character_Action_Model();		
		$a -> character_id = $par[0] -> id;
		$a -> action = 'initiate';
		$a -> starttime = time();			
		$a -> status = "running";	
		$a -> param1 = $par[0] -> id;
		
		// Refresh della cache del sacerdote...
		My_Cache_Model::delete(  '-charinfo_' . $a -> character_id . '_currentpendingaction');	
		
		// Salva il char di chi subisce la funzione religiosa
		$a -> param2 = $par[1] -> id;	
		
		// Salva l' id della struttura religiosa
		$a -> param3 = $par[2] -> id;
		$a -> endtime = $this->starttime + $this -> get_action_time( $par[0] );
		$a -> save();		
				
		// Consuma subito il goldenbasin
		Item_Model::consumeitem_instructure( 'goldenbasin', $par[2] -> id, self::GOLDENBASIN_WEAR );
		
		// Consuma subito i faith points
		$par[2] -> modify_stat ('faithpoints', - $this -> fpcost );
		
		$message = kohana::lang('ca_initiate.initiate-ok');						
						
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
		// Char che esegue l'azione
		$charaction = ORM::factory('character', $data -> character_id );
		// Char che esegue esegue l'iniziazione
		$charsource = ORM::factory('character', $data -> param1 );
		// Char che viene iniziato
		$chartarget = ORM::factory('character', $data -> param2 );
		// Struttura religiosa
		$structure = StructureFactory_Model::create( null, $data -> param3 );
				
		//////////////////////////////////////////////////////////////////
		// Sottraggo l'energia e la sazietà al char
		///////////////////////////////////////////////////////////////////
		$charaction -> modify_energy ( - self::DELTA_ENERGY, false, 'initiate' );
		$charaction -> modify_glut ( - self::DELTA_GLUT );
		$charaction -> save();	
		
		// Azioni relative a chi ha officiato la funzione
		if ( $charaction -> id == $charsource -> id )
		{
			// Consumo degli items/vestiti indossati
			Item_Model::consume_equipment( $this->equipment, $charsource );
					
			// Save statistic
			$charsource -> modify_stat( 'initiations', +1, $structure -> structure_type -> church -> id );		
			$structure ->  modify_stat( 'initiations', +1 );
		}
		
		// Azioni relative a chi ha subìto la funzione
		if ( $charaction -> id == $chartarget -> id )
		{
			// Setto la religione/chiesa
			$chartarget -> church_id = $structure -> structure_type -> church -> id ;
			
			// setto il Faith Level			
			$chartarget -> modify_faithlevel( self::INITIALFAITHLEVEL, true );
			$chartarget -> save();
				
			// setto la stat joindate
			$chartarget -> modify_stat( 
				'churchjoindate', 
				time(), 
				null, 
				null, 
				true );				
				
			// evento per il char
			
			Character_Event_Model::addrecord( $chartarget -> id, 
			'normal', '__events.initiatetargetok' . 
			';__' . 'religion.church-' . $structure -> structure_type -> church -> name );
				
			// evento per chi ha officiato
			
			Character_Event_Model::addrecord( $charsource -> id, 
			'normal', '__events.initiatesourceok' . 
			';' . $chartarget -> name . 
			';__' . 'religion.church-' . $structure -> structure_type -> church -> name );
			
			// evento permanente di battesimo
			
			Character_Permanentevent_Model::add( $chartarget -> id, 
			'__permanentevents.initiation' . ';' .
			'__' . 'religion.church-' . $structure -> structure_type -> church -> name . ';' .
			'__' . $structure -> region -> name . ';' .
			$charsource -> name 
			);
			
			// evento per town crier
						
			Character_Event_Model::addrecord( null, 
			'announcement', '__events.initiation' . 
			';' . $chartarget -> name . 
			';__' . 'religion.church-' . $structure -> structure_type -> church -> name );
		}
		
	}
	
	protected function execute_action() {}
	
	public function cancel_action() { 
			
		// trova entrambe le azioni
		
		$sourcecharaction = ORM::factory('character_action') -> 
			where ( 
				array(
					'action' => $this -> action,
					'character_id' => $this -> param1,
					'status' => 'running')
				) -> find();
		
		$targetcharaction = ORM::factory('character_action') -> 
			where ( 
				array(
					'action' => $this -> action,
					'character_id' => $this -> param2,
					'status' => 'running')
				) -> find();		
		
		$targetchar = ORM::factory('character', $this -> param2);		
		kohana::log('debug', '-> Target char is: ' . $targetchar -> name);
		
		$sourcechar = ORM::factory('character', $this -> param1);
		kohana::log('debug', '-> Source char is: ' . $sourcechar -> name);
		
		// cancelliamo l' altra azione. Non possiamo chiamare il metodo 
		// character_action -> cancel_pending_action altrimenti potrebbe 
		// fallire la chiamata con il source char_id.
		
		if ($this -> character_id == $targetchar -> id )
		{
			
			$sourcecharaction -> status = 'canceled' ;
			$sourcecharaction -> save();
			
			Character_Event_Model::addrecord
			( 
				$sourcechar -> id, 
				'normal', 
				'__events.initiatecanceled'.';'.$targetchar -> name
			);
			
			My_Cache_Model::delete(  '-charinfo_' . $sourcechar -> id . '_currentpendingaction' );
			
		}
		
		if ($this -> character_id == $sourcechar -> id )
		{
			$targetcharaction -> status = 'canceled' ;
			$targetcharaction -> save();
			Character_Event_Model::addrecord
			( 
				$targetchar -> id, 
				'normal', 
				'__events.initiatecanceled'.';'.$sourcechar -> name
			);
			My_Cache_Model::delete(  '-charinfo_' . $targetchar -> id . '_currentpendingaction' );
		}
				
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
			$message = '__regionview.initiate_longmessage';
			else
			$message = '__regionview.initiate_shortmessage';
		}
		return $message;
	
	}
	
	// calcola i punti FP necessari
	
	protected function get_neededfp( $par )
	{
		$info = Church_Model::get_info($par[2] -> structure_type -> church_id);
		$cost = max(1, round( self::REQUESTEDFP * $info['followers'] / $info['parishchurches'] )); 
		kohana::log('debug', '-> craft - FP cost: ' . $cost ); 				
		return $cost ;
	}
	
}
