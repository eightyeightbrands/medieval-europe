<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_CancelMarriage_Model extends Character_Action_Model
{
	// Costanti	
	const DELTA_GLUT = 5;
	const DELTA_ENERGY = 5;
	const GOLDENBASIN_WEAR = 25;
	const REQUESTEDFP = 2;
	const FAITHLEVELREQUESTED = 75;
	
	protected $cancel_flag = false;
	protected $immediate_action = false;
	protected $fpcost = 0;

	protected $basetime       = 2.5;  
	protected $attribute      = 'intel';  // attributo intelligenza
	protected $appliedbonuses = array ( 'workerpackage' ); // bonuses da applicare
	protected $partner;
	
	// L'azione richiede che il personaggio indossi
	// un determinato equipaggiamento
	
	protected $requiresequipment = true;
	
	// Equipaggiamento o vestiario necessario in base al ruolo
	
	protected $equipment = array
	(
		'church_level_1' => array
		(
			'head' => array
			(
				'items' => array('mitra_rome','panzva_turnu','hat_cairo','hat_kiev','hat_norse'),
				'consume_rate' => 'verylow',
			),
			'right_hand' => array
			(
				'items' => array('mysticrod_turnu','scepter_kiev','pastoral_rome', 'mysticrod_cairo','scepter_norse'),
				'consume_rate' => 'verylow',
			),
			'body' => array
			(
				'items' => array('tunic_church_level_1_rome', 'tunic_church_level_1_turnu', 'tunic_church_level_1_kiev', 'tunic_church_level_1_cairo','tunic_church_level_1_norse'),
				'consume_rate' => 'verylow',
			),
			'feet' => array
			(
				'items' => array('shoesm_1', 'shoesf_1'),
				'consume_rate' => 'verylow',
			)
		),
		'church_level_2' => array
		(
			'head' => array
			(
				'items' => array('mitra_rome','panzva_turnu','hat_cairo','hat_kiev','hat_norse'),
				'consume_rate' => 'verylow',
			),
			'right_hand' => array
			(
				'items' => array('mysticrod_turnu','scepter_kiev','pastoral_rome', 'mysticrod_cairo','scepter_norse'),
				'consume_rate' => 'verylow',
			),
			'body' => array
			(
				'items' => array('tunic_church_level_2_rome', 'tunic_church_level_2_turnu', 'tunic_church_level_2_kiev', 'tunic_church_level_2_cairo','tunic_church_level_2_norse'),
				'consume_rate' => 'verylow',
			),
			'feet' => array
			(
				'items' => array('shoesm_1', 'shoesf_1'),
				'consume_rate' => 'verylow'
			)		
		),
		'church_level_3' => array
		(
			'head' => array
			(
				'items' => array('mitra_rome','panzva_turnu','hat_cairo','hat_kiev','hat_norse'),
				'consume_rate' => 'verylow'
			),
			'right_hand' => array
			(
				'items' => array('mysticrod_turnu','scepter_kiev','pastoral_rome', 'mysticrod_cairo','scepter_norse'),
				'consume_rate' => 'verylow',
			),
			'body' => array
			(
				'items' => array('tunic_church_level_3_rome', 'tunic_church_level_3_turnu', 'tunic_church_level_3_kiev', 'tunic_church_level_3_cairo','tunic_church_level_3_norse'),
				'consume_rate' => 'verylow',
			),
			'feet' => array
			(
				'items' => array('shoesm_1', 'shoesf_1'),
				'consume_rate' => 'verylow',
			)
		),
		'church_level_4' => array
		(
			'right_hand' => array
			(
				'items' => array('holybook'),
				'consume_rate' => 'verylow',
			),
			'body' => array
			(
				'items' => array('tunic_church_level_4_rome', 'tunic_church_level_4_turnu', 'tunic_church_level_4_kiev', 'tunic_church_level_4_cairo','tunic_church_level_4_norse'),
				'consume_rate' => 'verylow',
			),
			'feet' => array
			(
				'items' => array('shoesm_1', 'shoesf_1'),
				'consume_rate' => 'verylow',
			),
		),
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
			'feet' => array
			(
				'items' => array('any'),
				'consume_rate' => 'verylow'
			),
		),
	);
	
	
	// @input: 
	// $par[0] = obj: Character who celebrates the wedding
	// $par[1] = obj: Structure where the celebration happens
	// $par[2] = obj: Char that wants to annull wedding
	// @output: boolean: true or false
	//          $message contains error message
	
	protected function check( $par, &$message )
	{
		$message = "";
		
		if ( ! parent::check( $par, $message ) )					
			return false;
		
		//wife, husband exists?
		
		if ( !$par[2] -> loaded )
		{ $message = Kohana::lang("global.error-characterunknown"); return false; };
		
		if ( is_null(Character_Model::is_married( $par[2] -> id )) )
		{ $message = Kohana::lang("ca_cancelmarriage.error-characterisnotmarried"); return false; };
				
		//find partner
		
		$data = Character_Relationship_Model::get_kinrelations( $par[2] -> id );
		
		if ( $par[2] -> sex == 'F' )
			$this -> partner = ORM::factory('character', $data['outgoingrelations']['wife']['id']);
		else
			$this -> partner = ORM::factory('character', $data['outgoingrelations']['husband']['id']);
		
		// an annullment letter has been sent?
		
		$annulmentletter = ORM::factory('message') -> where
		( array( 			
			'type' => 'weddingannulment',
			'fromchar_id' => $par[2] -> id,
			'tochar_id' => $this -> partner -> id,
			'param1' => 1)
		) -> find();		
		
		if ( $annulmentletter -> loaded == false )
		{ $message = Kohana::lang("ca_cancelmarriage.error-annulmentnoticenotsent"); return false; };
				
		// husband or wife are married?		
		
		// char 1 and 2, are married?
		if ( Character_Model::is_marriedto( $par[2] -> id, $this -> partner -> id, $relationtype ) == false )
		{ $message = Kohana::lang("ca_cancelmarriage.error-charactersarenotmarried"); return false; };
				
		// requesting char is busy?
		$pendingaction = Character_Action_Model::get_pending_action( $par[2] -> id ); 
		if ( !is_null( $pendingaction ) )		
		{ $message = Kohana::lang("global.error-characterisbusy", $par[2] -> name); return false; }		
				
		// wife or husband are in location?
		if ( $par[2] -> position_id != $par[1] -> region -> id )
		{ $message = Kohana::lang("global.error-characternotinlocation", $par[2] -> name, kohana::lang($par[1] -> region -> name)); return false; }
		
		// priest has enough Faith Level?		
		$role = $par[0] -> get_current_role();
		$fl = $par[0] -> get_stat( 'faithlevel' );		
		if ( $fl -> value < self::FAITHLEVELREQUESTED )
		{ $message = Kohana::lang("global.error-notenoughfaithful", self::FAITHLEVELREQUESTED ); return false; }				
		
		// FP points
		// Ci sono almeno X FP?
		
		$fp = Structure_Model::get_stat_d( $par[1] -> id, 'faithpoints' ); 		
		$this -> fpcost = $this -> get_neededfp( $par );				
		if ( ! $fp -> value  or $fp -> value < $this -> fpcost )
		{ $message = Kohana::lang("global.error-notenoughfp", $this -> fpcost ); return false; }
		
		// do officer has enough energy
		
		if (
			$par[0] -> energy < (self::DELTA_ENERGY )  or
			$par[0] -> glut < (self::DELTA_GLUT) )
		{   $message = Kohana::lang("global.error-characternotenoughenergyorglut", $par[0] -> name); return false; }
		
		// do husband and wife have enough energy
		
		if (
			$par[2] -> energy < (self::DELTA_ENERGY )  or
			$par[2] -> glut < (self::DELTA_GLUT) )
		{   $message = Kohana::lang("global.error-characternotenoughenergyorglut", $par[2] -> name); return false; }
		
		// officer must be of the same religion
		
		if ( $par[2] -> church_id != $par[0] -> church_id )
		{ $message = Kohana::lang("ca_celebratemarriage.error-husbandandwifenotofsamereligionofpriest"); return false; }		
		// C'è almeno un golden basin al 25%?
		$exists = false;
		foreach ( $par[1] -> item as $item )
			if ( $item -> cfgitem -> tag == 'goldenbasin' and $item -> quality >= self::GOLDENBASIN_WEAR )
				$exists = true;
		
		if ( $exists == false )
		{ $message = Kohana::lang("ca_celebratemarriage.goldenbasinnotexists");
		 return false; }	
			
		if ( $par[1] -> contains_item( 'goldenbasin', 1 ) == false  )
		{   $message = Kohana::lang("global.error-missingiteminstructure", 1, kohana::lang('items.goldenbasin_name')); return false; }		
		
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
		// save a blocking action for priest
		
		$this -> character_id = $par[0] -> id;
		$this -> starttime = time();
		$this -> param1 = $par[0] -> id;
		$this -> param2 = $par[2] -> id;
		$this -> param3 = $this -> partner -> id;
		$this -> param4 = $par[1] -> id;
		$this -> endtime = $this -> starttime + $this -> get_action_time( $par[0] );
		$this -> save();		
		
		// save a blocking action for partner
				
		$a = new Character_Action_Model();		
		$a -> character_id = $par[2] -> id;
		$a -> action = 'cancelmarriage';
		$a -> starttime = time();			
		$a -> param1 = $par[0] -> id;
		$a -> param2 = $par[2] -> id;
		$a -> param3 = $this -> partner -> id;
		$a -> param4 = $par[1] -> id;
		$a -> status = "running";	
		$a -> endtime = $this -> starttime + $this -> get_action_time( $par[0]);
		$a -> save();		
				
		// destroy and consume items
		
		Item_Model::consumeitem_instructure( 'goldenbasin', $par[1] -> id, self::GOLDENBASIN_WEAR );
		
		// consume faith points
		$par[1] -> modify_stat ('faithpoints', - $this -> fpcost );
				
		Character_Event_Model::addrecord( 
			$par[0] -> id , 
			'normal', 
			'__events.cancelmarriage_start' );
		
		Character_Event_Model::addrecord( 
			$par[2] -> id , 
			'normal', 
			'__events.cancelmarriage_start' );
		
		Character_Event_Model::addrecord( 
			$this -> partner -> id , 
			'normal', 
			'__events.cancelmarriage_start' );
		
		
		$message = kohana::lang('ca_cancelmarriage.cancel-ok');						
								
		return true;
	}

	public function complete_action( $data )
	{
		
		$charaction = ORM::factory('character', $data -> character_id );
		$officer = ORM::factory('character', $data -> param1 );
		$requester = ORM::factory('character', $data -> param2 );
		$partner = ORM::factory('character', $data -> param3 );		
		$structure = StructureFactory_Model::create( null, $data -> param4 );
		
		
		//////////////////////////////////////////////////////////////////
		// Sottraggo l'energia e la sazietà al char
		///////////////////////////////////////////////////////////////////
				
		$charaction -> modify_energy ( - self::DELTA_ENERGY, false, 'cancelmarriage' );
		$charaction -> modify_glut ( - self::DELTA_GLUT );
		$charaction -> save();	
		
		// Azioni relative a chi ha officiato la funzione
		
		if ( $charaction -> id == $officer -> id )
		{
			// Consumo degli items/vestiti indossati
			Item_Model::consume_equipment( $this->equipment, $charaction );
			
			// invalidate annulmentletter
			
			$annulmentletter = ORM::factory('message') -> where
			( array( 			
				'type' => 'weddingannulment',
				'fromchar_id' => $requester -> id,
				'tochar_id' => $partner -> id,
				'param1' => 1)
			) -> find();
			
			$annulmentletter -> param1 = 2;
			$annulmentletter -> save();
			
			// Save statistic
			$charaction -> modify_stat( 'weddingcancelations', +1, $structure -> structure_type -> church -> id );		
			$structure ->  modify_stat( 'weddingcancelations', +1 );			
			Character_Event_Model::addrecord( 
			$officer -> id,
			'normal', 
			'__events.cancelmarriage_end' );
			
			// evento per chi ha officiato
		
			Character_Event_Model::addrecord( $officer -> id, 
			'normal', '__events.cancelweddingofficer' . 
			';' . Character_Model::create_publicprofilelink($requester -> id, $requester -> name) . 
			';' . Character_Model::create_publicprofilelink($partner -> id, $partner -> name),
			'normal' );
			
			// evento per town crier
				
			Character_Event_Model::addrecord( null, 
			'announcement', '__events.weddingcanceled' . 
			';' . Character_Model::create_publicprofilelink($officer -> id, $officer -> name) . 
			';__' . $structure -> structure_type -> name .
			';__' . $structure -> region -> name .
			';' . Character_Model::create_publicprofilelink($requester -> id, $requester -> name) . 
			';' . Character_Model::create_publicprofilelink($partner -> id, $partner -> name) ,	
			'normal' );
				
		}
		
		if ( $charaction -> id == $requester -> id )
		{
			
			if ( $requester -> sex == 'M' )	
			{
				Character_Relationship_Model::remove( $requester -> id, $partner -> id, 'husband' );
				Character_Relationship_Model::remove( $partner -> id, $requester -> id, 
				'wife');
			}
			else
			{
				Character_Relationship_Model::remove( $requester -> id, $partner -> id, 'wife' );
				Character_Relationship_Model::remove( $partner -> id, $requester -> id, 'husband' );
			}
						
			// events, annullment completed.
			
			Character_Event_Model::addrecord( $requester -> id, 
			'normal', '__events.cancelweddingspouses' . 
			';' . Character_Model::create_publicprofilelink($partner -> id, $partner -> name) . 
			';__' . $structure -> structure_type -> name .
			';__' . $structure -> region -> name,
			'normal' );
			
			Character_Event_Model::addrecord( $partner -> id, 
			'normal', '__events.cancelweddingspouses' . 
			';' . Character_Model::create_publicprofilelink($requester -> id, $requester -> name) . 
			';__' . $structure -> structure_type -> name .
			';__' . $structure -> region -> name,
			'normal' );
			
			// events, permanent
			
			Character_Permanentevent_Model::add( $requester -> id, 
			'__permanentevents.marriedcanceled' .
			';' . Character_Model::create_publicprofilelink($partner -> id, $partner -> name) . 
			';__' . $structure -> structure_type -> name .
			';__' . $structure -> region -> name			
			);
			
			Character_Permanentevent_Model::add( $partner -> id, 
			'__permanentevents.marriedcanceled' .
			';' . Character_Model::create_publicprofilelink($requester -> id, $requester -> name) .
			'__' . 'religion.church-' . $structure -> structure_type -> church -> name . ';' .
			';__' . $structure -> structure_type -> name .
			';__' . $structure -> region -> name
			);

		}
				
	}
	
	protected function execute_action() {}
	
	public function cancel_action() { 	
	return true; }

	// Questa funzione costruisce un messaggio da visualizzare 
	// in attesa che la azione sia completata.

	public function get_action_message( $type = 'long') 
	{
		$pending_action = $this->get_pending_action();
		$message = "";				
		
		if ( $pending_action -> loaded )
		{
			if ( $type == 'long' )
			$message = '__regionview.cancelmarriage_longmessage';
			else
			$message = '__regionview.cancelmarriage_shortmessage';
		}
		return $message;
	
	}
	
	// calcola i punti FP necessari
	
	protected function get_neededfp( $par )
	{
		$info = Church_Model::get_info($par[1] -> structure_type -> church_id);
		$cost = max(1, round( self::REQUESTEDFP * $info['followers'] / $info['parishchurches'] )); 
		kohana::log('debug', '-> craft - FP cost: ' . $cost ); 				
		return $cost ;
	}
	
}
