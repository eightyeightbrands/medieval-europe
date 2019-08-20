<?php defined('SYSPATH') OR die('No direct access allowed.');
class CA_Arrest_Model extends Character_Action_Model
{
	protected $immediate_action = false;
	protected $procedure = null;
	protected $role = null;
	protected $currentregion = null;
	protected $cancel_flag = false;		
	const ENERGY_FOR_30_MIN = 2;
	const GLUT_FOR_30_MIN = 1;
	
	protected $basetime  = 2;         // 2 ore
	protected $attribute = 'none';    // nessun attributo
	protected $premium   = 'none';    // nessun pacchetto premium
	
	// Effettua tutti i controlli relativi alla wear, sia quelli condivisi
	// con tutte le action che quelli peculiari della wear
	// @input: array di parametri (par[0] memorizza l'id dell'oggetto che indosso)
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	// par[0]: oggetto char dello sceriffo 
	// par[1]: oggetto char di chi viene arrestato
	
	protected function check( $par, &$message )
	{ 
		
		if ( ! parent::check( $par, $message, $par[0] -> id, $par[1] -> id ) )					
		{ return false; }
		
		/////////////////////////////////////////////////////
		// controllo dati
		/////////////////////////////////////////////////////
		
		if ( !$par[0]->loaded or !$par[1]->loaded )
		{ $message = kohana::lang('global.operation_not_allowed'); return FALSE; }
		
		/////////////////////////////////////////////////////
		// controlliamo che il capitano abbia un mandato 
		// d'arresto valido
		/////////////////////////////////////////////////////
		
		// trovo tutti gli arrest warrant nell' inventory del capitano
		// con riferimento al criminale
		
		$db = Database::instance(); 		
		$rset = $db -> query ( "select i.* from items i, cfgitems c, character_sentences cs
			where i.cfgitem_id = c.id 
			and   c.tag = 'scroll_arrestwarrant'			
			and   i.param3 = " . $par[1] -> id ) -> as_array();
		
		// se c'è n'è almeno uno che fa riferimento ad una procedura aperta, il controllo
		// è passato.
		
		$validaw = false;		
		foreach ( $rset as $r )
		{
			list( $procedure_id, $judge_id, $criminal_id, $judge_name, $criminal_name, $createdtime )	= explode( ';', $r -> param1 );
			$this -> procedure = ORM::factory('character_sentence', $procedure_id );
			
			if ( $this -> procedure -> status == 'new' )
			{ $validaw = true ; break; }
		}
		
		if ( $validaw == false )
		{ $message = kohana::lang('ca_arrest.nonvalidarrestwarrant'); return FALSE; }
				
		///////////////////////////////////////////////////////////////////////		
		// non si puÃ² arrestare un reggente o un leader religioso
		// (a meno che sia malato)
		///////////////////////////////////////////////////////////////////////
		
		$role = $par[1] -> get_current_role() ; 		
		if ( 
			!is_null( $role) and 
			in_array( 
				$role -> tag, array( 'church_level_1', 'king' )  ) and
			$par[1] -> is_sick() == false 
		)
		{ $message = kohana::lang('ca_arrest.notenoughpower' ); return FALSE; }
		
		//////////////////////////////////////////////////////////////////////
		// Il regno del giocatore è in guerra con il regno di chi tenta di
		// arrestarlo?
		//////////////////////////////////////////////////////////////////////
		
		$guardcaptainkingdomrunningwars = Kingdom_Model::get_kingdomwars( $par[0] -> region -> kingdom_id, 'running');
		$criminalkingdomrunningwars = Kingdom_Model::get_kingdomwars( $par[1] -> region -> kingdom_id, 'running');
		
		if (
			count($guardcaptainkingdomrunningwars) > 0 
			and  
			count($criminalkingdomrunningwars) > 0 
			and 
			$guardcaptainkingdomrunningwars[0]['war'] -> id == $criminalkingdomrunningwars[0]['war'] -> id )
		{ $message = kohana::lang( 'charactions.error-characterisofenemykingdom'); return false;}	
		
		/////////////////////////////////////////////////////
		// controllo che il char sia un cdg
		/////////////////////////////////////////////////////
		
		$this -> role = $par[0] -> get_current_role();		
		
		if ( !is_null( $this -> role ) and $this -> role -> tag != 'sheriff' )		
		{ $message = kohana::lang('global.operation_not_allowed'); return FALSE; }
		
		/////////////////////////////////////////////////////
		// non si puÃ² arrestare in mare
		////////////////////////////////////////////////////
		
		$location = ORM::factory('region', $par[0] -> position_id );
		if ($location -> type == 'sea' )
		{ $message = kohana::lang('global.operation_not_allowed'); return FALSE; }		
		
		//////////////////////////////////////////////////////////////////////
		// Se il regno è in rivolta non si puÃ² arrestare
		//////////////////////////////////////////////////////////////////////		
		
		$runningbattles = Kingdom_Model::get_runningbattles( $par[1] -> region -> kingdom_id );		
		foreach( (array) $runningbattles as $runningbattle )
		{ 
			if ( $runningbattle -> type == 'revolt' )
			{
				$message = kohana::lang('ca_restrain.kingdomisonrevolt'); 
				return FALSE; 
			}
		}	
		
		/////////////////////////////////////////////////////
		// controllo che il prigioniero non sia in uno stato 
		// che gli impedisca l' arresto
		/////////////////////////////////////////////////////
		
		if ( Character_Model::is_fighting( $par[1] -> id ) == true ) 
		{ $message = sprintf(kohana::lang('ca_arrest.charisfighting', $par[1]->name)); return FALSE; }
		
		if ( Character_Model::is_restrained( $par[1] -> id ) )
		{ $message = sprintf(kohana::lang('ca_arrest.charisrestrained', $par[1]->name)); return FALSE; }

		if ( Character_Model::is_imprisoned( $par[1] -> id ) )
		{ $message = sprintf(kohana::lang('ca_arrest.charisimprisoned', $par[1]->name)); return FALSE; }		
		
		if ( $par[1] -> is_meditating( $par[1] -> id ) )
		{ $message = sprintf(kohana::lang('ca_arrest.charismeditating', $par[1]->name)); return FALSE; }						
	
		if ( Character_Model::is_traveling( $par[1] -> id ) )
		{ $message = sprintf(kohana::lang('ca_arrest.charistraveling', $par[1]->name)); return FALSE; }				
		
		$this -> currentregion = ORM::factory('region', $par[0] -> position_id );
		
		return true;
		
	}

	public function append_action ( $par, &$message ) 
	{
		
		$message = '';
		
		//////////////////////////////////////////////////////
		// interrompo qualsiasi azione sta facendo il char
		//////////////////////////////////////////////////////
		
		Kohana::log('info', '-> Arrest: Canceling pending actions...');
		
		$pendingaction = Character_Action_Model::get_pending_action( $par[1] -> id ); 
		if ( !is_null( $pendingaction ) )		
		{
			$pendingaction -> cancel_pending_action( $par[1] -> id, true, $message);
			Kohana::log('info', '-> Arrest: return message: ' . $message);
		}
		
		//////////////////////////////////////////////////////
		// Appendo una azione bloccante
		//////////////////////////////////////////////////////
		
		$message = null;
		
		//////////////////////////////////////////////////////
		// Calcolo il tempo di viaggio
		// distanza tra la corrente regione
		// e la regione dove sta la corte
		// di chi ha aperto la procedura di incriminazione.
		//////////////////////////////////////////////////////
		
		$starttime = time();
		$endtime   = $starttime + $this -> get_action_time( $par );
		
		$court = ORM::factory( 'structure', $this -> procedure -> structure_id );
		// azione bloccante per chi arresta
		$a =  Character_Action_Model::factory("arrest");		
		$a -> starttime = $starttime;
		$a -> endtime = $endtime;
		$a -> status = 'running';
		$a -> param1 = $this -> currentregion -> id ;
		$a -> param2 = $court -> region -> id ;
		// parametro per differenziare chi è arrestato
		
		$a -> param4 = false;				
		$a -> character_id = $par[0] -> id; 
		$a -> save();
		$par[0] -> modify_location(0);
		
		$par[0] -> save();
		
		// azione bloccante per chi è arrestato
		$a = Character_Action_Model::factory("arrest");		
		$a -> starttime = $starttime;
		$a -> endtime = $endtime;
		$a -> status = 'running';
		$a -> param1 = $this -> currentregion -> id ;
		$a -> param2 = $court -> region -> id ;
		
		// parametro per differenziare chi è arrestato		
		$a -> param4 = true;		
		$a -> character_id = $par[1] -> id; 
		$a -> save();
		$par[1] -> modify_location(0);
		
		$par[1] -> save();
		
		// aggiunge statistica al cdg
		
		$par[0] -> modify_stat( 
			'arrests',
			+1 );
				
		
		// eventi
		
		Character_Event_Model::addrecord( 
			$par[1]->id,
			'normal', 
			'__events.arrest'.
			';' . $par[0] -> name,
			'evidence'
			);
		
		
		Character_Event_Model::addrecord( 
			$par[0]->id,
			'normal', 
			'__events.arrested'.
			';' . $par[1] -> name,
			'evidence'
			);

		
		$message = kohana::lang( 'ca_arrest.arrest_ok',  $par[1]->name );
		
		return true;
	}
	
	protected function execute_action( $par, &$message )
	{	}

	public function complete_action( $data)
	{
	
		$char = ORM::factory('character')->find( $data->character_id );
	
		// tolgo energia e glut in base alla distanza
		
		$sourceregion = ORM::factory('region', $data -> param1 );
		$targetregion = ORM::factory('region', $data -> param2 );
		
		$distance = Region_Path_Model::compute_distance( $sourceregion -> name, $targetregion -> name );
		$glut = min( 50, round( self::GLUT_FOR_30_MIN * ( $distance / 30 ) ) ) ;
		$energy = min( 50, round( self::ENERGY_FOR_30_MIN * ( $distance / 30 ) ) );

		$char -> modify_energy ( - $energy, false, 'arrest' );
		$char -> modify_glut ( - $glut );
		
		// trasporto il char a destinazione e 
		// resetto lo stato 
		
		$char -> modify_location( $data -> param2 );		
		$char -> save();
		
		// se param3 è true, appendo un ordine di restrizione per 2 giorni
		
		if ( $data -> param4 == true )
		{
		
			$a = new Character_action_Model();
			$a -> character_id = $char -> id;		
			$a -> starttime = time();
			$a -> blocking_flag = false;
			$a -> action = 'restrain';
			$a -> status = "running";			
			$a -> param1 = $targetregion -> id;			
			$a -> endtime = $a -> starttime + ( 168 * 3600 ); 
			$a -> save();
			
			// lo avviso che è bloccato
			
			
			Character_Event_Model::addrecord( 
			$char -> id, 
			'normal',  
			'__events.arrest_restrained');		
			
		}

	}
	
	// Questa funzione costruisce un messaggio da visualizzare 
	// in attesa che la azione sia completata.
	
	public function get_action_message( $type = 'long') 
	{
		$pending_action = $this->get_pending_action();
		$message = "";				
		
		$startregion = ORM::factory('region', $pending_action -> param1);
		$destregion =  ORM::factory('region', $pending_action -> param2);		
		
		if ( $pending_action -> loaded )
		{
			if ( $type == 'long' )		
				$message = '__ca_arrest.longmessage;__'. $startregion -> name . ';__' . $destregion -> name ;
			else
				$message = '__ca_arrest.shortmessage';
		}
		
		return $message;
	
	}

	// Funzione che stabilisce quanto dura l' azione
	
	public function get_action_time( $par )
	{		
		
		$court = ORM::factory( 'structure', $this -> procedure -> structure_id );		
		
		$distance = max( Region_Path_Model::compute_distance( 
			$this -> currentregion -> name, 
			$court -> region -> name), 10 );			
		
		// 40 km/ora (cavallo), max 24 ore
		
		$time = min ( 24 * 3600, $distance * ( 3600 / 40 ) );		
		
		return $time ;	
	}
}
