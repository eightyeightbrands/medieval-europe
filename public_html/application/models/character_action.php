<?php defined('SYSPATH') OR die('No direct access allowed.');

class Character_Action_Model extends ORM
{
	
	protected $table_name = "character_actions";
	protected $immediate_action = true;
	protected $cancel_flag = false;
	protected $enabledifrestrained = false;
	protected $requiresequipment = false;	
	protected $callablebynpc = false;	
	protected $appliabletonpc = false;	
	
	const DELTA_ENERGY = 0;
	
	// Funzione che istanzia il giusto modello figlio e ne inizializza il campo action
	// @input: stringa azione da istanziare
	
	public static function factory( $action )
	{
		$model = ("CA_".ucfirst( $action ) . "_Model");
		$o = new $model;
		
		$o -> status = 'running'; 
		$o -> action = $action;			
		
		return $o;
	}

	/**
	* Funzione che controlla se è possibile effettuare un azione
	* Contiene i controlli comuni a tutte le azioni.
	* @param array $par parametri vari (non usato)
	* @param string $message messaggio d'errore ritornato
	* @param int $callerchar_id ID personaggio che ESEGUE l' azione.
	* @param int $targetchar_id ID personaggio che SUBISCE l' azione.
	* @return boolean
	*/
	
	protected function check( $par, &$message, $callerchar_id = null, $targetchar_id = null )
	{
		
		// Verifica se c'è una azione ancora in corso, verificando l' azione che 
		// è in stato running. Se il character_id passato è null, prendo quello in sessione.		
		
		if ( is_null( $callerchar_id ) )
			$char = Character_Model::get_info( Session::instance() -> get('char_id') ); 
		else
			$char = ORM::factory('character', $callerchar_id  ); 
		
		$this -> character_id = $char -> id;
		
		// controllo se l' utente è validato
		
		if ( $char -> user -> status != 'active' )
		{
			$message = Kohana::lang("charactions.userisnotactive");			
			return false;
		}		
		
		// controllo se l' azione è applicabile ad un npc
		
		if ($this -> callablebynpc == false and $char -> type == 'npc' )
		{
			$message = Kohana::lang("charactions.notcallablebynpc");			
			return false;			
		}
				
		if ( ! is_null( $targetchar_id ) )
		{

			$targetchar = ORM::factory('character', $targetchar_id  ); 
			// controllo se l' azione è applicabile ad un npc
		
			if ($this -> appliabletonpc == false and $targetchar -> type == 'npc' )
			{
				$message = Kohana::lang("charactions.notappliabletonpc");			
				return false;			
			}
			
		}		
		
		// Verifica se c'è una azione ancora in corso, verificando l' azione che 
		// è in stato running. 

		kohana::log('info', '-> Checking if a pending action exists...');		
				
		$action = Character_Model::get_currentpendingaction( $char -> id );
		
		if ( is_array( $action ) )
		{			
			kohana::log('debug', '-> Char: ' . $char -> name . ', pending action DOES exists (' . $action['action'] . ')' );
			$message = Kohana::lang("charactions.pending_action_exists");			
			return false;
		}
		
		kohana::log('debug', '-> Char: ' . $char -> name  . '-> pending action DOES NOT exists.');
		
		kohana::log('info', '-> Checking if action can be done while being restrained...' );
		
		if ( $this -> enabledifrestrained == false and 
			Character_Model::is_restrained($char -> id) )
		{$message = Kohana::lang("charactions.error-notenabledwhenrestrained");return false;}
		
		
		kohana::log('info', '-> Checking if char has the correct equipment...');
		
		// Check: l'azione richiede un determinato equipaggiamento
		if ( $this -> requiresequipment  )
		{
			// Verifico se l'equipaggiamento indossato è corretto
			if ( ! $this -> check_equipment( $this -> equipment, $char, $equipmentincorrect ) )
			{
				
				// missing items
				//kohana::log('debug', kohana::debug($equipmentincorrect));
				if (isset($equipmentincorrect['missing']))
				{
					foreach ($equipmentincorrect['missing'] as $bodypart => $itemsthatshouldbeworn )					
						foreach ((array)$itemsthatshouldbeworn as $itemthatshouldbeworn)			
							$items[$bodypart][] = kohana::lang('items.'.$itemthatshouldbeworn.'_name');					
					$text = '';
					foreach ($items as $bodypart => $itemstowear )
					{
						if ($bodypart == 'body')
							$_bodypart = 'bodyortorsopluslegs';
						else
							$_bodypart = $bodypart;
						$text .= '<b>'.kohana::lang('items.'.$_bodypart) .'</b>: ' . implode($itemstowear, ' ' . 
						kohana::lang('global.or') .' ') . '&nbsp;' ;						
					}
					$message = Kohana::lang("charactions.equipmentfailed_missing", $text);
				}
				// wrong items
				if (isset($equipmentincorrect['wrong']))
				{
					foreach ($equipmentincorrect['wrong'] as $bodypart => $itemsthatshouldbeworn )
						foreach ((array)$itemsthatshouldbeworn as $itemthatshouldbeworn)			
							$items[$bodypart][] = kohana::lang('items.'.$itemthatshouldbeworn.'_name');					
					
					$text = '';
					foreach ($items as $bodypart => $itemstowear )					
						if ($bodypart == 'body')
							$_bodypart = 'bodyortorsopluslegs';
						else
							$_bodypart = $bodypart;
						$text .= '<b>'.kohana::lang('items.'.$_bodypart) .'</b>: ' . implode($itemstowear, ' ' . kohana::lang('global.or') .' '). '<br/>' ;											
					$message = Kohana::lang("charactions.equipmentfailed_missing", $text);
				}
								
				return false;
			}
		}
	
		// Tutti i controlli sono stati superati
		
		kohana::log('info', '-> Basic checks passed.');
		
		return true;
	}

	/** 
	* Funzione che esegue l' action e chiama 
	* append o execute_action a seconda che get_timetocomplete
	* sia = 0 o > 0
	* @param  $par vettore di parametri
	* @param  $message messaggio da ritornare al chiamante
	* @return niente
	*/
	
	public function do_action( $par, &$message )
	{
		$ret = false;			
	
		// Chiama la check della azione.
		
		if ( ! $this -> check( $par, $message ) )
		{ return false; }
		
		// se get_timetocomplete è = 0 semplicemente chiamo execute_action
		// altrimenti appendo l' action
		
		try
		{
			
			$db = Database::instance();						
			$db -> query("set autocommit = 0");
			$db -> query("start transaction");
			$db -> query("begin");
			
			if ( $this -> immediate_action )
			{
				kohana::log('debug', '-> This action is immediate, calling execute action.');
				$ret = $this -> execute_action( $par, $message );
			}
			else
			{
				
				$ret = $this -> append_action( $par, $message );		
				
				kohana::log( 'debug', "-> ------- APPENDED ACTION: " . $this -> action . ' -------' );
				// aggiorno la cache, solo se la action è non immediata		
				kohana::log( 'debug', "-> Deleting currentpendingaction from cache for char {$this -> character_id}");
				My_Cache_Model::delete(  '-charinfo_' . $this -> character_id . '_currentpendingaction');				
			}
			
			kohana::log('info', '-> doaction ***commit***.');	
			$db -> query('commit');
			
		} catch (Kohana_Database_Exception $e)
		{					
			kohana::log('info', $e->getMessage());
			kohana::log('error', 	"-> An error occurred, rollbacking action: [{$this->action}] for char: [{$this -> character_id}]");
			$db -> query("rollback");			
		}
		
		$db -> query("set autocommit = 1");
		
		return $ret;
	
	}
	
	/**
	* Verifica se ci sono azioni pending che sono expired e le completa. 	
	* La tabella character_actions è stata modificata
	* a INNODB, per gestire la concorrenza su questa tabella
	* infatti se due utenti effettuassero la stessa query 
	* prima che lo stato della azione sia cambiato, la stessa
	* azione potrebbe essere completata n volte.
	* @param  charflag. se true, completa le azioni del char (caso in cui
	*         il char è loggato)
	* @return niente
	*/
	
	public function complete_expired_actions ( $charflag = false )
	{	
	
		$char = null;
		
		kohana::log( 'info', "-> ========================================== <- " ); 
			
		if ( $charflag == true )
		{
			$char = Character_Model::get_info( Session::instance() -> get('char_id') );
			kohana::log( 'info', "-> Completing actions, charflag: [{$charflag}]");
		}
		
		if ( $charflag == true and is_null( $char ) )
			return;
		
		// Verifico se c'è un azione running nel db scaduta
		
		$key = substr( md5(mt_rand()), 0, 50 );			
		$db = Database::instance();						
		$db -> query("set autocommit = 0");
		$db -> query("start transaction");
		$db -> query("begin");

		// le righe selezionate vengono riservate in scrittura
		// in modo che siano processate solo da questo thread.		
		
		kohana::log('info', "-> Locking actions with key: $key " );

		$sql = "
			update character_actions set keylock = '$key' 
			where status = 'running' 
			and keylock is null 
			and endtime <= unix_timestamp()
			"; 		
		
		if ( $charflag == true )
			$sql .= " and character_id = " . $char -> id ; 
		
		$res = $db -> query( $sql ); 
		kohana::log('info', "-> " . $res -> count() . " rows locked." ); 
	
		// ora seleziono solo le righe con la chiave generata
		// da questo thread
		
		$sql = "select * from 
			character_actions
			where status = 'running' 
			and keylock = '$key' 
			and endtime <= unix_timestamp() "; 
		
		if ( $charflag == true )
			$sql .= " and character_id = " . $char -> id ; 		
		
		$sql .= " for update";
		
		$result = $db -> query ( $sql ) ; 
		
		// try-catch. Se si verifica un errore, l' azione che commette l' errore viene rollbackata
		
		foreach ( $result as $row )
		{
			try 
			{
				
					
				$o = $this -> factory( $row -> action );
				
				$o -> complete_action ( $row );
				
				kohana::log('info', "----- COMPLETED ACTION: char id: [{$row->character_id}] {$row->action} -----");
								
					
				// Se l'azione non è ciclica allora la setto come completata
				
				if ( $row -> cycle_flag == FALSE )
				{ 
					$db -> query( "update character_actions set status = 'completed' where id = " . $row -> id ); 
					My_Cache_Model::delete(  '-charinfo_' . $row -> character_id . '_currentpendingaction' );
					
					// metto il char a dormire se l' azione è blocking, ed evito il loop.
					
					if ( 
						$row -> blocking_flag == true and 
						!in_array( $row -> action, array( 'rest', 'resttavern' ) ) )
						Character_Model::makecharsleep( $row -> character_id );					
					
				}
								
				// Se l'azione è ciclica allora blanko la chiave per la prossima complete		
				
				if ( $row -> cycle_flag == TRUE )
					$db -> query( "update character_actions set keylock = null where id = " . $row -> id ); 
				
				kohana::log('info', '-> completeexpiredaction ***commit***.');	
				$db -> query('commit');
				
				} catch (Kohana_Database_Exception $e)
				{					
					kohana::log('info', $e->getMessage());
					kohana::log('error', 	"-> An error occurred, rollbacking action: [{$row->action}], id: [{$row->id}]");
					$db -> query("rollback");			
				}	
		
		}		
		
		$db -> query("set autocommit = 1");
		
		return $result -> count(); 
		
	}
	
	/**
	* Trova la pending action "blocking" per il char passato
	* e ritorna la corretta classe istanziata
	* @param:  character_id. 
	* @return: oggetto character_action (la vera classe)
	*/
	
	public static function get_pending_action( $character_id = null )
	{					
		if ( is_null( $character_id ) )
			$character_id = Session::instance() -> get('char_id');
		
		kohana::log( 'debug', '-> Searching pending actions for char: ' . $character_id );
		
		$o = ORM::factory('character_action')
			->where(	array( 
				'character_id' => $character_id,
				'status' => 'running',
				'blocking_flag' => true ))->find();
		
		if ( $o -> loaded )
		{
			if ( Character_Action_Model::factory( $o -> action ) -> immediate_action == true )
				return null;
			else
				return $o;
		}
		else
			return null;
	}	
	
	/** 
	* Annulla l' azione pending
	* @param  character_id id del char a cui deve essere annullata l' azione pending
	* @param  force_flag se true, l' azione viene forzata anche se non è stata ordinata
	*         dal giocatore
	* @return boolean true = ALL OK, false = error
	*/
	
	public function cancel_pending_action( $character_id = null, $force_flag = false, &$message = '')
	{
		
		$message = 'global.action_cantbecanceled';
		
		kohana::log('debug', 
			'=> Canceling pending action for charid: [' . $character_id . '] force flag: [' . $force_flag . ']');

		if ( is_null( $character_id ) )
		{
			$char = Character_Model::get_info( Session::instance()->get('char_id') );
			//kohana::log('debug', kohana::debug( $char ));
			$character_id =  $char -> id ;
		}			
		
		$pendingaction = Character_Model::get_currentpendingaction( $character_id );			
		
		if ( $pendingaction != 'NOACTION' )
		{			
		
			kohana::log('debug', '=> Cancel: pending action: [' . $pendingaction['action'] . '] for char:' . 
				$character_id );
			
			$action = Character_Action_Model::factory( $pendingaction['action'] ) -> find( $pendingaction['id'] );			
			
			if ( $action -> cancel_flag or $force_flag )
			{
				kohana::log('debug', "-> Ecexuting child cancel code...");
				
				$rc = $action -> cancel_action( $message );
				
			kohana::log('debug', "-> Result from action cancel action: [{$rc}], message: [{$message}]" );
				
				if ( $rc ) 
				{
					$action -> status = "canceled" ;
					$action -> save();										
					
					My_Cache_Model::delete(  '-charinfo_' . Session::instance()->get('char_id') . '_currentpendingaction' ); 	
					return true;
				}
				else
					return false;
				
			}
			else
				return false;
		}
	}
	
	
	
	/** 
	* Cancella la action passata	
	* Chiama il metodo cancel action della azione e poi
	* mette in stato canceled la riga in character_action.
	* @param forceflag
	*/
	
	public function obs_cancel( $forceflag = false, $message = '')
	{	
	
		$action = $this -> factory( $this -> action ) -> find ( $this -> id ); 		
		if ( $forceflag == false and $action -> cancel_flag == false )
			return false ; 
		// Call the child action
		$rc = $action -> cancel_action( $message ); 
		$action -> status = 'canceled' ;
		$action -> save(); 	
		
		return $rc; 
		
	}
	

	function save()
	{
	
		My_Cache_Model::delete(  '-charinfo_' . $this -> character_id . '_currentpendingaction');	
		parent::save();
		
	}
	
	/*
	* torna le azioni pending associate al char di un certo tipo
	* @param action nome dell' azione
	* @param oggetto char
	* @return lista di azioni 
	*/
	
	function get_list( $action, $character )
	{
		$a = ORM::factory( 'character_action' ) -> 
			where ( array ( 
			'action' => $action, 			
			'character_id' => $character -> id ) ) -> find_all(); 
			
		return $a ; 
	
	}
	
	/*
	* Calcola il tempo necessario per l'azione
	* @param obj $character oggetto Character_Model
	* @return int $modifiedtime tempo effettivo dell'azione da eseguire in secondi
	*/
	
	public function get_action_time( $character )
	{
		
		$basetime = $modifiedtime = $this -> basetime * 3600;		
		
		kohana::log('debug', '------ GET_ACTION_TIME ------');
		kohana::log('debug', '-> Original time: ' . Utility_Model::secs2hmstostring( $basetime ) );
	
		$bonus = 100;
		
		// apply server speed
		kohana::log('debug', '-> Applying server speed...');
		$basetime = $basetime / Kohana::config('medeur.serverspeed');
		kohana::log('debug', '-> Original time after serverspeed factor: ' . 		Utility_Model::secs2hmstostring($basetime ));
			
		// apply bonus speed
		kohana::log('debug', '-> Applying speed bonus...');
		$speedbonus = Character_Model::get_stat_from_cache($character -> id, 'speedbonus');
		//var_dump($speedbonus);exit;
		if ($speedbonus -> loaded and $speedbonus -> stat1 > time() )
		{			
			$basetime = $basetime / $speedbonus -> value;
			kohana::log('debug', '-> Original time after serverspeed factor: ' . 	Utility_Model::secs2hmstostring($basetime ));
			// destroy speedbonus
			// Character_Model::modify_stat_d($character -> id, 'speedbonus', 1, null, null, true );
		}
	
		// Calcolo del bonus malus in base all'attributo
		
		if ($this -> attribute != 'none')
		{
			kohana::log('debug', 
				'-> Applying '. $this -> attribute .' Attribute bonus...' . 
				$character -> get_attribute( $this -> attribute ) . '/' . Character_Model::get_attributelimit() );
			
			$bonus = $bonus * (( 100 - 30 * ( $character -> get_attribute( $this -> attribute )/Character_Model::get_attributelimit()))/100);			
			
			kohana::log('debug', '-> Bonus after attribute check: ' . $bonus );
		}

		// Calcolo del bonus in base al pacchetto premium
		
		if ( in_array( 'workerpackage', $this -> appliedbonuses ) and
			Character_Model::get_premiumbonus(  $character -> id, 'workerpackage') !== false )	 				
		{
			kohana::log('debug', '-> Applying workerpackage bonus...');
			$bonus *= 50 / 100;			
			kohana::log('debug', '-> Bonus after workerpackage check: ' . $bonus );			
		}	

		// Calcolo bonus religiosi
		// *****************************************************************
		// Bonus 'Resource Extraction Blessing'
		if
		(
			in_array( 'resourceextractionblessing', $this -> appliedbonuses ) and			
			Church_Model::has_dogma_bonus($character -> church_id, 'resourceextractionblessing') )
		{
			kohana::log('debug', '-> Applying resourceextractionblessing bonus...');
			// Verifico il faith level del char
			$fl = $character -> get_stat( 'faithlevel' );
			// Calcolo il bonus da applicare (Faith level applicato al 30%)
			$bonus -= (30 * $fl->value) / 100;
			kohana::log('debug', '-> Bonus after resourceextractionblessing check: ' . $bonus );			
		}
		
		// Bonus 'Craft Blessing'
		if
		(
			in_array( 'craftblessing', $this -> appliedbonuses ) and
			Church_Model::has_dogma_bonus($character -> church_id, 'craftblessing'))
		{
			kohana::log('debug', '-> Applying craftblessing bonus...');
			// Verifico il faith level del char
			$fl = $character -> get_stat( 'faithlevel' );
			// Calcolo il bonus da applicare (Faith level applicato al 30%)
			$bonus -= (30 * $fl->value) / 100;
			kohana::log('debug', '-> Bonus after craftblessing check: ' . $bonus );			
		}
		
		// Bonus 'Concentrate and learn'
		if
		(
			in_array( 'concentrateandlearn', $this -> appliedbonuses ) and
			Church_Model::has_dogma_bonus($character -> church_id, 'concentrateandlearn'))
		{
			kohana::log('debug', '-> Applying concentrateandlearn bonus...');
			// Verifico il badge del char
			$afpachievement = Character_Model::get_achievement( $character->id, 'stat_fpcontribution' );
			// Ad ogni livello badge corrisponde un bonus del 10% sul tempo
			if (is_null($afpachievement))
				$stars = 0;
			else
				$stars = $afpachievement['stars'];
			$maxbonus = 10*$stars;
			// Verifico il faith level del char
			$fl = $character -> get_stat( 'faithlevel' );
			// Calcolo il bonus da applicare (Faith level applicato alla % legata al badge)
			$bonus -= ($maxbonus * $fl->value) / 100;
			kohana::log('debug', '-> Bonus after concentrateandlearn check: ' . $bonus );			
		}
		// *****************************************************************
		
		// Debug
		kohana::log('debug', '-> The final time will be ' . $bonus . '% of the starting time.'); 
		
		// Tempo finale modificato in base ai bonus
		
		$modifiedtime = max(60, $basetime * $bonus / 100);
		// Debug
		kohana::log('debug', '-> Final time: ' . Utility_Model::secs2hmstostring( $modifiedtime ) );
		
		return $modifiedtime;
	}	

	/********************************************************************
	* Verifica se il char indossa gli indumenti/items idonei in base
	* al ruolo
	*
	* Come configurare le singole azioni:
	* $requiresequipment = true/false (abilita o disabilita il controllo)
	*
	* $equipment = array multidimensionale organizzato in:
	* ['role'] = array ['bodypart' = array ['item1', 'item2', item3'] ]
	* Per ogni ruolo è possibile specificare un array di zone del
	* corpo su cui eseguire i controlli e, per ognuna di essa, specificare
	* una lista di items da verificare.
	* Inserendo la key 'all' è possibile specificare gli items che devono
	* indossare tutti i chars che non hanno un ruolo
	* Il tag '*' significa che va bene qualsiasi tipo di item/vestito
	* Il check viene superato se nella zona del corpo è presente almeno uno
	* degli items specificato nella lista.
	*
	* @param  array   $equipment     array degli items da controllare
	* @param  obj     $char                 oggetto char su cui eseguire il controllo
	* @return bool    true/false            check superato o fallito
	********************************************************************/
	
	public function obs_check_equipment ($equipment, $char, &$equipmentincorrect)
	{
		
		if (isset($equipment['all']['right_hand']['items']))
		{
						
			$righthandequipment = $equipment['all']['right_hand']['items'][0];			
			
			//Unequip items on right hand.
			Database::instance() -> query("
				UPDATE items 
				SET equipped = 'unequipped'
				WHERE equipped = 'right_hand'");
			
			$itemtoequip = Database::instance() -> query(
			"SELECT i.id, ci.tag 
			 FROM items i, cfgitems ci
			 WHERE i.character_id = {$char->id}
			 AND   i.cfgitem_id = ci.id 
			 AND   ci.tag = '{$righthandequipment}'
			 AND   i.quantity = 1
			 ORDER BY i.quality asc LIMIT 1");
			
			// Equip item in right hand
			
			if ( $itemtoequip -> count() == 1) 
			{
				
				$row = $itemtoequip[0];				
				kohana::log('debug', "-> Equipping item: {$row->tag}");
				Database::instance() -> query("
				UPDATE items
				SET equipped = 'right_hand'
				WHERE id = {$row->id}");
			}
		}
		
		$equipment_to_check = null;
		
		// Prelevo il ruolo del char
		$role = $char -> get_current_role();
		
		// Check: il char ha un ruolo
		// Check: il ruolo è presente nella lista di quelli da controllare
				
		if ( !is_null($role) and array_key_exists($role->tag, $equipment) )
		{
			$equipment_to_check = $equipment[$role->tag];
		}
		// Il char non ha un ruolo ma è definito un array
		// per il check di tutti i personaggi
		elseif ( array_key_exists('all', $equipment) )
		{
			$equipment_to_check = $equipment['all'];
		}
		
		// Check: equipment_to_check non è nulla
		kohana::log('debug', kohana::debug($equipment_to_check));
		if ( ! is_null($equipment_to_check) )
		{
			
			
			// Se nell'array sono definite le tre zone
			// del corpo: body, torso e legs
			// allora devo eseguire dei controlli dedicati perchè
			// è sufficiente che venga soddisfatta la presenza di items
			// o vestiti nel body oppure su torso+legs (visto che sono
			// zone del corpo che si escludono mutuamente)
			
			if
			(
				array_key_exists('body', $equipment_to_check) and
				array_key_exists('torso', $equipment_to_check) and
				array_key_exists('legs', $equipment_to_check)
			)
			{	
				// Inizializzo le variabili
				$b=$t=$l=true;
				
				// Carico l'array per il body e l'item indossato sul body
				
				$array_of_items = $equipment_to_check['body']['items'];
				$item_equipped = $char -> get_bodypart_item('body');				
				
				// Se l'item indossato è nullo ed è previsto che il char
				// indossi qualsiasi cosa oppure c'è un array definito di
				// oggetti e l'item indossato non rientra tra questi, allora
				// il controllo non è superato
				
				if 
				(
					( is_null($item_equipped) and in_array('any', $array_of_items) ) 
					or 
					( 
						!in_array('any', $array_of_items) 
						and 
						!in_array($item_equipped->cfgitem->tag, $array_of_items) 						
					)
				)
				{
					kohana::log('debug', '-> Body equipment missing or incorrect, setting false.');
					
					$b = false;
					
					if (is_null($item_equipped))
						$equipmentincorrect['missing']['body'] = 'any';
					else
						$equipmentincorrect['wrong']['body'] = $item_equipped -> cfgitem-> tag;					
				}
					
				// Eseguo gli stessi controlli del body anche per il torso
				$array_of_items = $equipment_to_check['torso']['items'];
				$item_equipped = $char->get_bodypart_item('torso');
				
				if 
				(
					( is_null($item_equipped) and in_array('any', $array_of_items) ) or 
					( ! in_array('any', $array_of_items) and ! in_array($item_equipped->cfgitem->tag, $array_of_items) )
				)
				{
					$t = false;
					kohana::log('debug', '-> Torso equipment missing or incorrect, setting false.');
					
					if (is_null($item_equipped))
						$equipmentincorrect['missing']['torso'] = 'any';
					else
						$equipmentincorrect['wrong']['torso'] = $item_equipped -> cfgitem-> tag;
				}

				// Eseguo gli stessi controlli del body anche per legs
				$array_of_items = $equipment_to_check['legs']['items'];
				$item_equipped = $char->get_bodypart_item('legs');
				
				if 
				(
					( is_null($item_equipped) and in_array('any', $array_of_items) ) or 
					( ! in_array('any', $array_of_items) and ! in_array($item_equipped->cfgitem->tag, $array_of_items) )
				)
				{
					$l = false;
					kohana::log('debug', '-> Legs equipment missing or incorrect, setting false.');
					
					if (is_null($item_equipped))
						$equipmentincorrect['missing']['legs'] = 'any';
					else
						$equipmentincorrect['wrong']['legs'] = $item_equipped -> cfgitem-> tag;
					
					
				}

				// Il controllo è superato solo se:
				// 1) il check sul body è negativo ma torso e legs vanno bene 
				// 2) il body supera direttamente il controllo
				if
				( 
					($b == false) and 
					($t == false or $l == false)
				)
				{
					kohana::log('debug', '-> Body + Torso + Legs Check is False, check failed.');
					// se torso = true vuol dire che ilplayer ha deciso che usa camicia piu legs, 
					// quindi tolgo il warning per il corpo.
					if ($t == true)
					{
						unset($equipmentincorrect['missing']['body']);
					}
						
					return false;
				}
				
				kohana::log('debug', '-> Body+Torso+Legs Check True.');
				
				// Se arrivo a questo punto significa che il controllo su
				// body, torso e legs è superato e quindi li rimuovo dall'array
				// delle zone del corpo da controllare per i rimanenti controlli
				
				unset ($equipment_to_check['body']);
				unset ($equipment_to_check['torso']);
				unset ($equipment_to_check['legs']);
				
			}
			
			// Seleziono il ruolo e prelevo l'array degli items da controllare 
			// per ogni parte del corpo. L'array viene prelevato nella modalitÃ  
			// Key (parte del corpo) => Value (array di items che puÃ² indossare)
			
			foreach ($equipment_to_check as $bodypart => $array_of_items)
			{
				
				// Carico l'item che il char ha attualmente
				// equippato nella parte del corpo
				kohana::log('debug', "-> Processing bodypart: {$bodypart}");
				$item_equipped = $char->get_bodypart_item($bodypart);
				kohana::log('debug', "-> Char is equipping on {$bodypart}: [{$item_equipped}]");
					
				// Check: nessun item equipaggiato
				// Check: il char deve indossare qualcosa in questa zona del corpo

				if 
				( 
					is_null($item_equipped) and 
					in_array('any', $array_of_items['items'])
				)
				{
					kohana::log('debug', '-> Char has nothing equipped, setting false.');
					$equipmentincorrect['missing'][$bodypart] = 'any';
					return false;
				}
				
				// Check: nessun item equipaggiato
				// Check: il char deve indossare un determinato item in questa zona del corpo
				
				//kohana::log('debug', kohana::debug($array_of_items['items']));
				
				if 
				( 
					is_null($item_equipped) and ! in_array('none', $array_of_items['items'])
				)
				{
					kohana::log('debug', '-> Char has nothing equipped and needs to equip , setting false.');
					$equipmentincorrect['missing'][$bodypart] = $array_of_items['items'];
					return false;
				}
				
				// Check: sono specificati uno o piÃ¹ items da indossare per eseguire l'azione
				// Check: l'item equippaggiato non compare tra quelli idonei ad eseguire l'azione
				
				if
				( 
					! is_null($item_equipped) and 
					! in_array('any', $array_of_items['items']) and 
					! in_array($item_equipped->cfgitem->tag, $array_of_items['items']) )
				{
						kohana::log('debug', '-> Char has wrong equipment, setting false.');
						$equipmentincorrect['wrong'][$bodypart] = $item_equipped -> cfgitem -> tag;
						return false;
					}
				}
			}

		// Tutti i controlli sono stati superati
		
		return true;
	}
	
	public function check_equipment ($equipment, $char, &$equipmentincorrect)
	{
		
		kohana::log('debug', '------- CHECK EQUIPMENT -------');
		
		if (isset($equipment['all']['right_hand']['items']))
		{
			
			$righthandequipment = $equipment['all']['right_hand']['items'][0];					
			
			$itemtoequip = Database::instance() -> query(
			"SELECT i.id, ci.tag 
			 FROM items i, cfgitems ci
			 WHERE i.character_id = {$char->id}
			 AND   i.cfgitem_id = ci.id 
			 AND   ci.tag = '{$righthandequipment}'
			 AND   i.quantity = 1
			 ORDER BY i.quality asc LIMIT 1");
			
			// Equip item in right hand
			
			if ( $itemtoequip -> count() == 1) 
			{
				
				kohana::log('debug', "-> Unequipping item from right hand.");
				
				// Unequip items on right hand.
			
				Database::instance() -> query("
					UPDATE items 
					SET equipped = 'unequipped'
					WHERE equipped = 'right_hand'
					AND   character_id = {$char->id}");
				
				$row = $itemtoequip[0];				
				kohana::log('debug', "-> Equipping item: {$row->tag}");
				
					Database::instance() -> query("
					UPDATE items
					SET equipped = 'right_hand'
					WHERE id = {$row->id}");
			}
		}
		
		$equipment_to_check = null;
		
		// Prelevo il ruolo del char
		$role = $char -> get_current_role();
		
		// Check: il char ha un ruolo
		// Check: il ruolo è presente nella lista di quelli da controllare
				
		if ( !is_null($role) and array_key_exists($role->tag, $equipment) )
		{
			$equipment_to_check = $equipment[$role->tag];
		}
		// Il char non ha un ruolo ma è definito un array
		// per il check di tutti i personaggi
		elseif ( array_key_exists('all', $equipment) )
		{
			$equipment_to_check = $equipment['all'];
		}
		
		//kohana::log('debug', kohana::debug($equipment_to_check));
		
				
		if ( !is_null($equipment_to_check) )
		{
			
			foreach ($equipment_to_check as $bodypart => $array_of_items )
			{
				
				kohana::log('debug', "-> Missing Items Check, Processing part: {$bodypart}");
				$correctitemstoequip = $array_of_items['items'];
				
				$item_equipped = $char -> get_bodypart_item($bodypart);		
					
				// Salvo nell' array l' equipment missing.
				
			  if ( is_null($item_equipped) and !in_array('none', $correctitemstoequip) )
				{					
					$equipmentincorrect['missing'][$bodypart] = $correctitemstoequip;
				}
			}
			
			//kohana::log('debug', kohana::debug($equipmentincorrect));

			
			// Solo se nell'array sono definite le tre zone
			// del corpo: body, torso e legs
			// allora devo eseguire dei controlli dedicati perchè
			// è sufficiente che venga soddisfatta la presenza di items
			// o vestiti nel body oppure su torso+legs (visto che sono
			// zone del corpo che si escludono mutuamente)
			
			
			if ( 
				array_key_exists('body', $equipment_to_check) and
				array_key_exists('torso', $equipment_to_check) and
				array_key_exists('legs', $equipment_to_check)
				)
				{
					if (
							isset($equipmentincorrect['missing']['body'])
							and
							(
								isset($equipmentincorrect['missing']['torso']) 
								or 
								isset($equipmentincorrect['missing']['legs'])
							)
						)
					{
						kohana::log('debug', '-> Legs, Torso and Body Check FAILED');
						//unset($equipmentincorrect['missing']['body']);
						unset($equipmentincorrect['missing']['torso']);
						unset($equipmentincorrect['missing']['legs']);
						
					}
					else
					{
						kohana::log('debug', '-> Legs, Torso and Body Check PASSED, cleaning up');
						unset($equipmentincorrect['missing']['body']);
						unset($equipmentincorrect['missing']['torso']);
						unset($equipmentincorrect['missing']['legs']);
					}	
				}
				
			if ( count($equipmentincorrect['missing']) > 0 )
				return false;
			else
				unset($equipmentincorrect['missing']);
				
			// Inizio controllo corretto equipment.
			reset($equipment_to_check);
			foreach ($equipment_to_check as $bodypart => $array_of_items )
			{
				$item_equipped = $char -> get_bodypart_item($bodypart);				
				$correctitemstoequip = $array_of_items['items'];
				kohana::log('debug', "-> Wrong Items, Processing part: {$bodypart}");
				
				
				if					
					(
						! is_null($item_equipped) and 					
						! in_array('any', $array_of_items['items']) and 
						! in_array('none', $array_of_items['items']) and 
						! in_array($item_equipped->cfgitem->tag, $correctitemstoequip) 
					)
					{
						kohana::log('debug', '-> Char has incorrect equipment on.');
						$equipmentincorrect['wrong'][$bodypart] = $correctitemstoequip;
					}
			}
			
			if ( count($equipmentincorrect) > 0 )
				return false;
			
			// Tutti i controlli sono stati superati
		
			return true;
		}
	}
}
