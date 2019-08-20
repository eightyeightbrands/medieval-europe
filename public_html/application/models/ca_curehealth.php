<?php defined('SYSPATH') OR die('No direct access allowed.');


class CA_Curehealth_Model extends Character_Action_Model
{
	// Glut ed energia necessari per eseguire l'azione
	const DELTA_GLUT = 10;
	const DELTA_ENERGY = 10;
	// Punti fede necessari per eseguire l'azione
	// Devono essere presenti nella struttura governata dal sacerdote
	const REQUESTEDFP = 50;
	
	// Livello di fede minima richiesti al char che viene curato
	// e al sacerdote che esegue le cure
	/*const CHAR_FAITHLEVELREQUESTED = 75;*/
	const PRIEST_FAITHLEVELREQUESTED = 90;
	
	// Azione è cancellabile?
	protected $cancel_flag = true;
	// Azione non immediata
	protected $immediate_action = false;

	protected $basetime       = 2;  
	protected $attribute      = 'intel';  // attributo intelligenza
	protected $appliedbonuses = array ( 'workerpackage' ); // bonuses da applicare

	// L'azione richiede che il personaggio indossi
	// un determinato equipaggiamento
	protected $requiresequipment = true;
	protected $controlledstructure = null;

	// Equipaggiamento o vestiario necessario in base al ruolo
	protected $equipment = array
	(
		'church_level_1' => array
		(
			'head' => array
			(
				'items' => array('mitra_rome','panzva_turnu','hat_cairo','hat_kiev','hat_norse'),
				'consume_rate' => 'high',
			),
			'right_hand' => array
			(
				'items' => array('mysticrod_turnu','scepter_kiev','pastoral_rome', 'mysticrod_cairo','scepter_norse'),
				'consume_rate' => 'high',
			),
			'body' => array
			(
				'items' => array('tunic_church_level_1_rome', 'tunic_church_level_1_turnu', 'tunic_church_level_1_kiev', 'tunic_church_level_1_cairo','tunic_church_level_1_norse'),
				'consume_rate' => 'high',
			),
			'feet' => array
			(
				'items' => array('any'),
				'consume_rate' => 'high',
			)
		),
		'church_level_2' => array
		(
			'head' => array
			(
				'items' => array('mitra_rome','panzva_turnu','hat_cairo','hat_kiev','hat_norse'),
				'consume_rate' => 'high',
			),
			'right_hand' => array
			(
				'items' => array('mysticrod_turnu','scepter_kiev','pastoral_rome', 'mysticrod_cairo','scepter_norse'),
				'consume_rate' => 'high',
			),
			'body' => array
			(
				'items' => array('tunic_church_level_2_rome', 'tunic_church_level_2_turnu', 'tunic_church_level_2_kiev', 'tunic_church_level_2_cairo','tunic_church_level_2_norse'),
				'consume_rate' => 'high',
			),
			'feet' => array
			(
				'items' => array('any'),
				'consume_rate' => 'high'
			)		),
		'church_level_3' => array
		(
			'head' => array
			(
				'items' => array('mitra_rome','panzva_turnu','hat_cairo','hat_kiev','hat_norse'),
				'consume_rate' => 'high'
			),
			'right_hand' => array
			(
				'items' => array('mysticrod_turnu','scepter_kiev','pastoral_rome', 'mysticrod_cairo','scepter_norse'),
				'consume_rate' => 'high',
			),
			'body' => array
			(
				'items' => array('tunic_church_level_3_rome', 'tunic_church_level_3_turnu', 'tunic_church_level_3_kiev', 'tunic_church_level_3_cairo','tunic_church_level_3_norse'),
				'consume_rate' => 'high',
			),
			'feet' => array
			(
				'items' => array('any'),
				'consume_rate' => 'high',
			)
		),
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
				'items' => array('any'),
				'consume_rate' => 'high',
			),
		),
	);
	
	
	// Effettua tutti i controlli relativi alla cura malattia, sia quelli condivisi
	// con tutte le action che quelli peculiari della cura
	// @input: 
	// $par[0] = char che cura
	// $par[1] = char che è curato
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function check( $par, &$message )
	{ 
		$message = "";
		
		$has_dogma_bonus = Church_Model::has_dogma_bonus($par[0] -> church_id, 'curehealthextended');	
				
		// Check: controlli modello padre (check_equipment)
		if ( ! parent::check( $par, $message, $par[0] -> id, $par[1] -> id ) )					
			return false;
		
		// Check: il char che cura non ha un ruolo religioso
		if ( ! $par[0] -> has_religious_role() )
		{ $message = Kohana::lang("global.operation_not_allowed"); return false; }		
		
		$role = $par[0] -> get_current_role();		
		$this -> controlledstructure = $role -> get_controlledstructure();  
		
		// Check: char che cura non esiste
		// Check: char che viene curato non esiste
		// Check: Struttura dove si cura non esiste
		
		if
		( 
			!$par[0] -> loaded or 
			!$par[1] -> loaded			
		)
		{ $message = Kohana::lang("global.operation_not_allowed"); return false; }	

		
		// Check: il char che cura non ha energia sufficiente
		// Check: il char che cura non ha sazietà sufficiente
		if
		(
			$par[0] -> energy < (self::DELTA_ENERGY)  or
			$par[0] -> glut < (self::DELTA_GLUT)
		)
		{ $message = Kohana::lang("charactions.notenoughenergyglut"); return false; }
		
		// Check: il curante non ha livello fede sufficiente
		$fl = $par[0] -> get_stat( 'faithlevel' );
		if ( $fl -> value < self::PRIEST_FAITHLEVELREQUESTED )
		{ $message = Kohana::lang("global.error-charisnotfaithfulenough", self::PRIEST_FAITHLEVELREQUESTED); return false; }
		
		// Check: la chiesa non ha il bonus dogma
		// Check: i char non sono nella stessa regione della struttura religiosa		
		if 
		( 
			! $has_dogma_bonus and
			(		
				$par[0] -> position_id != $this -> controlledstructure -> region_id
			)
		)
		{ 
			$message = Kohana::lang("ca_cure.error-farfromstructure"); 
			return false; 
		}
				
		// Check: i char sono in regioni differenti
		
		if ( $par[0] -> position_id != $par[1] -> position_id )
		{ 
			$message = Kohana::lang("ca_cure.error-charsarenotinsamelocation"); 
			return false; 
		}	
						
		// Check: il char che viene curato è impegnato in un'altra azione
		// a meno che sia recovery
		
		$pendingaction = Character_Action_Model::get_pending_action( $par[1] ); 
		if ( !is_null( $pendingaction ) and Character_Model::is_recovering( $par[1] -> id ) == false )		
		{ $message = Kohana::lang("global.error-characterisbusy", $par[1] -> name ); return false; }
				
		// Check: la struttura non ha abbastanza FP per curare il char
		$fp = Structure_Model::get_stat_d( $this -> controlledstructure -> id, 'faithpoints' ); 		
		if ( ! $fp -> value or	$fp -> value < self::REQUESTEDFP )
		{ $message = Kohana::lang("global.error-notenoughfp", self::REQUESTEDFP); return false; }
		
		// Check: Il curatore ha il medical kit?
		if ( ! Character_Model::has_item( $par[0] -> id, 'medicalkit') )
		{ $message = Kohana::lang("ca_cure.error_no_medikit" ); return false; }
		
		// Check: la religione non ha il bonus dogma
		// Check: il sacerdote è di livello 1,2 o 3		
		
		if 
		(
			! $has_dogma_bonus and
			in_array ($role->tag, array('church_level_1', 'church_level_2', 'church_level_3'))
		)
		{ $message = Kohana::lang("global.operation_not_allowed"); return false; }
		
		// Check: il char che deve essere curato è ateo 
		// Check: la chiesa non ha il bonus dogma
		if 
		(
			$par[1] -> church -> name == 'nochurch' and ! $has_dogma_bonus
		)
		{ $message = Kohana::lang("ca_cure.error-cantcureatheist"); return false; }	
		
		
		// Check: il personaggio da curare ha una religione diversa dal char che cura		
		if
		(
			$par[1] -> church_id != $par[0] -> church_id 
			and
			$par[1] -> church -> name != 'nochurch'
		)
		{ $message = Kohana::lang("ca_cure.error-cantcuredifferentfaithfollower"); return false; }				
		
		// Tutti i checks sono stati superati
		
		return true;
	}

	/*
	* Funzione per l'inserimento dell'azione nel DB.
	* Questa funzione appende solo una azione _non la esegue_
	* @param  array    $par       $par[0] = char che cura, $par[1] = char che viene curato
	*                             $par[1] = struttura del char che cura
	* @output boolean             TRUE = azione disponibile, FALSE = azione non disponibile
	* @output string   $messages  contiene gli errori in caso di FALSE
	*/
	
	protected function append_action( $par, &$message )
	{
		
		// Carico l'eventuale bonus sul cura malattie
		$church = ORM::factory('church', $this -> controlledstructure->structure_type->church_id );
		
		
		$has_dogma_bonus = Church_Model::has_dogma_bonus($this -> controlledstructure->structure_type->church_id,'curehealthextended');
		
		// Imposto il tempo di cura in base a: ateo / fedele
		if 
		(
			$par[1] -> church -> name == 'nochurch' and 
			$has_dogma_bonus
		)
		{ $this -> basetime = 4; }
		
		if 
		(
			$par[1] -> church -> name != 'nochurch' and 
			$has_dogma_bonus
		)
		{ $this -> basetime = 2; }
		
		// Se il char sta recuperando salute, l' azione recovery va cancellata.
		
		$was_recovering = false;
		
		if ( Character_Model::is_recovering( $par[1] -> id) == true )
		{
			$was_recovering = true;
			kohana::log('debug', '-> Trying to cancel recovering action...');
			$rc = Character_Action_Model::cancel_pending_action( $par[1] -> id, true, $message );
			if ($rc == false )
				return $rc;
		}
		
		/////////////////////////////////////////////////
		// Salva una action blocking per chi deve curare
		// solo se il prete non sta curando sè stesso
		/////////////////////////////////////////////////
		
		if ( $par[0]-> id != $par[1] -> id )
		{
		
			$this -> character_id = $par[0] -> id;
			$this -> starttime = time();			
			$this -> status = "running";	
			
			// salva il char di chi cura	
			$this -> param1 = $par[0] -> id;
			// salva il char di chi è curato
			$this -> param2 = $par[1] -> id;		
			// salva l' id della struttura religiosa		
			$this -> param3 = $this -> controlledstructure -> id;
			
			if ( $was_recovering == true )
				$this -> param4 = true;
		
			$this -> endtime = $this -> starttime + $this -> get_action_time( $par[0], $par[1] );
			$this -> save();		
		}
		
		/////////////////////////////////////////////////
		// Salva una action blocking per chi è curato		
		/////////////////////////////////////////////////
		
		$a = new Character_Action_Model();		
		$a -> character_id = $par[1] -> id;
		$a -> action = 'curehealth';
		$a -> starttime = time();			
		$a -> status = "running";	
		
		// Id del char di chi cura	
		$a -> param1 = $par[0] -> id;
		// Id del char di chi è curato
		$a -> param2 = $par[1] -> id;	
		// Id della struttura religiosa	controllata dal curante
		$a -> param3 = $this -> controlledstructure -> id;
		
		if ( $was_recovering == true )
			$a -> param4 = true;
		
		
		$a -> endtime = $a->starttime + $this -> get_action_time( $par[0], $par[1] );
		$a -> save();		

		// Rimuove medical kit
		$i = Item_Model::factory( null, 'medicalkit' );
		$i -> removeitem( "character", $par[0]->id, 1 );
		
		// refresh della cache del prete...		
		My_Cache_Model::delete(  '-charinfo_' . $par[0] -> id . '_currentpendingaction');				
		
		// Consuma i faith points dalla struttura controllata
		// dal char che cura		
		
		$this -> controlledstructure -> modify_stat
		(
			'faithpoints', 
			- self::REQUESTEDFP
		);
		
		// Messaggio da visualizzare al curante
		$message = kohana::lang('ca_cure.info-cure-ok');		
		
		// Notifica eventi di inizio cura
		// *************************************
		// Evento al char che viene curato
		Character_Event_Model::addrecord
		( 
			$par[1] -> id, 
			'normal', 
			'__events.curestartedtarget'.';'.$par[0] -> name
		);
		// Notifica al char che cura		
		Character_Event_Model::addrecord
		( 
			$par[0] -> id, 
			'normal', 
			'__events.curestartedsource'.';'.$par[1] -> name
		);
		
		// Append andata a buon fine
		return true;
	}

	/*
	* Esecuzione dell' azione di cura salute.
	* Questa funzione viene chiamata quando viene invocata una complete_expired_action 
	* e gestisce le azioni inserite nella character_actions
	* - Si caricano i parametri dal database
	* - Si esegue l'azione in base ai parametri
	* - Si mette l'azione in stato completed
	* @param  array    $data      [0] = id char che cura, [1] = id char che viene curato
	*                             [2] = id struttura del char che cura
	* @output boolean             TRUE = azione disponibile, FALSE = azione non disponibile
	* @output string   $messages  contiene gli errori in caso di FALSE
	*/
	
	public function complete_action( $data )
	{
		
		kohana::log('debug', '-> Completing action curedisease for char: ' . $data -> character_id );
		// Char a cui è legata l'azione da completare
		$charaction = ORM::factory('character', $data -> character_id );
		// Char che ha eseguito la cura
		$charsource = ORM::factory('character', $data -> param1 );
		// Char che è stato curato
		$chartarget = ORM::factory('character', $data -> param2 );
		// Struttura che è controllata dal curante
		$structure  = ORM::factory('structure', $data -> param3 );

		/*******************************
		* Azioni relative al curante
		********************************/
		
		if ( $charaction -> id == $charsource -> id )
		{			
			
			// Consumo degli items/vestiti indossati
			
			Item_Model::consume_equipment( $this->equipment, $charsource );					
			// Aggiorno le stat del char che cura
			
			$charsource -> modify_stat
			( 
				'cure', 
				+1, 
				$structure -> structure_type -> church -> id
			);
				
			// Aggiorno le stat della struttura controllata dal curante
			
			$structure ->  modify_stat
			( 
				'cure',
				+1
			);
			
			// Sottraggo energia e sazietà
			
			$charsource -> modify_energy( - self::DELTA_ENERGY, false, 'curehealth' );
			$charsource -> modify_glut( - self::DELTA_GLUT );
			$charsource -> save();
			
		}
				
		/*****************************************
		 * Azioni relative a chi è stato curato
		 *****************************************/
		
		if ( $charaction -> id == $chartarget -> id )
		{	
			// Carico l'eventuale bonus sul cura malattie
			
			$church = ORM::factory('church', $structure->structure_type->church_id );			
			$has_dogma_bonus = Church_Model::has_dogma_bonus($structure->structure_type->church_id,'curehealthextended');
			
			// Ripristina  la salute
			
			$hptorestore = CA_Curehealth_Model::get_hptorestore( $chartarget, $has_dogma_bonus);
				
			$chartarget -> modify_health ( $hptorestore , true );							
			$chartarget -> save();
			
			// Notifica evento per il char curato
			Character_Event_Model::addrecord
			( 
				$chartarget -> id, 
				'normal', 
				'__events.curefinishedoktarget'
			);
			// Notifica evento per il char che cura	
			Character_Event_Model::addrecord
			( 
				$charsource -> id, 
				'normal', 
				'__events.curefinishedoksource'.';'.$chartarget -> name
			);
			// Notifica evento per struttura
			Structure_Event_Model::newadd
			( 
				$structure -> id, 
				'__events.curefinished;' . $chartarget -> name . ';__' . 'character.disease_' . $data -> param4
			);
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
		
		// L' azione non si può cancellare se l' azione di cura è conseguente
		// ad una recovery
		
		if ( $this -> param4 == true )
			return false;
		
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
				'__events.curecanceled'.';'.$targetchar -> name
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
				'__events.curecanceled'.';'.$sourcechar -> name
			);
			My_Cache_Model::delete(  '-charinfo_' . $targetchar -> id . '_currentpendingaction' );
		}
				
		return true;
	}

	// Questa funzione costruisce un messaggio da visualizzare 
	// in attesa che la azione sia completata.
	
	public function get_action_message( $type = 'long') { }
	
	/*
	* Calcola tempo elapsed della funzione (Overriden)
	* @param Character_Model $sourcecharacter personaggio che cura
	* @param Character_Model $stargetcharacter personaggio che è curato
	* @return int $time tempo in secondi
	*/
	
	public function get_action_time( $sourcecharacter, $targetcharacter )
	{
		
		// Calcola il tempo reale (applicando attributi e bonus ecc.)
		$time = parent::get_action_time( $sourcecharacter );
		
		kohana::log('debug', '-> Applying Faithful Bonus...');
		
		kohana::log('debug', '-> Time now: '. Utility_Model::secs2hmstostring($time));
		
		return $time;
	}
	
	/*
	* Torna quanti HP vanno ripristinati
	* @param Character_Model $character personaggio da curare
	* @param bollean $has_dogma_bonus Flag che indica se la CHiesa ha
  *	il bonus esteso
	* @return int $hp numero di HP da ristorare
	*/
	
	public function get_hptorestore( $character, $has_dogma_bonus )
	{
		
		$hptorestore = $character -> health;
		
		// In assenza del bonus esteso il recupero 
		// corrisponde al proprio FL

		if ( ! $has_dogma_bonus )
		{
			// Recupero la salute solo se il FL è
			// Maggiore della salute attuale del char
			$fl = Character_Model::get_stat_d( $character->id, 'faithlevel' );
			
			if ( $fl -> value > $character -> health )
				$hptorestore = $fl -> value ;			
		}			

		// In presenza del bonus:
		// L'ateo recupera il 100%, il fedele recupera il 100%
		// Se il proprio FL è > dell'80% altrimenti il proprio FL

		if ( $has_dogma_bonus )
		{
			// Il char curato è ateo
			
			if ( $character -> church -> name == 'nochurch' )			
				$hptorestore = 100 ;					
			else
			{
				// Il char curato è un fedele
				$fl = Character_Model::get_stat_d( $character -> id, 'faithlevel' );
				
				kohana::log('debug', 'Faith Level of cured: ' . $fl -> value );
				
				// Se ha ul FL >= 75 recupera il 100%
				if ( $fl -> value >= 75 )
					$hptorestore = 100 ;
				
				// Altrimenti recupera il proprio FL, ammesso che
				// sia più alto del suo livello di salute
				
				elseif ( $fl -> value > $character -> health )				
					$hptorestore = $fl -> value ;					
				
			}
		}
		
		return $hptorestore;
		
	}
	
}
