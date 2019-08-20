<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_CelebrateMarriage_Model extends Character_Action_Model
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
				'items' => array('shoesm_1', 'shoesf_1'),
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
				'items' => array('shoesm_1', 'shoesf_1'),
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
				'items' => array('shoesm_1', 'shoesf_1'),
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
				'items' => array('shoesm_1', 'shoesf_1'),
				'consume_rate' => 'high',
			),
		),
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
			'feet' => array
			(
				'items' => array('any'),
				'consume_rate' => 'high'
			),
		),
	);
	
	
	// @input: 
	// $par[0] = obj: Character who celebrates the wedding
	// $par[1] = obj: Structure where the celebration happens
	// $par[2] = obj: Husband
	// $par[3] = obj: wife
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
		
		if ( !$par[3] -> loaded )
		{ $message = Kohana::lang("global.error-characterunknown"); return false; };		
		
		// a proposal accepted exists?
		
		$proposal = ORM::factory('message') -> where
		( array( 			
			'fromchar_id' => $par[2] -> id,
			'tochar_id' => $par[3] -> id,
			'type' => 'weddingproposal', 
			'param1' => 1 ) ) -> find();
		
		if ($proposal -> loaded == false )
		{ $message = Kohana::lang("ca_celebratemarriage.error-weddingproposalnotaccepted"); return false; };		
		
		// husband or wife are already married?
		
		if ( !is_null(Character_Model::is_married( $par[2] -> id )) 
			or
			!is_null(Character_Model::is_married( $par[3] -> id )) 
		)
		{ $message = Kohana::lang("ca_celebratemarriage.error-characteralreadymarried"); return false; };
		
		// wife and husband are busy?
		$pendingaction = Character_Action_Model::get_pending_action( $par[2] -> id ); 
		
		if ( !is_null( $pendingaction ) )		
		{ $message = Kohana::lang("global.error-characterisbusy", $par[2] -> name); return false; }				
		$pendingaction = Character_Action_Model::get_pending_action( $par[3] -> id ); 
		if ( !is_null( $pendingaction ) )		
		{ $message = Kohana::lang("global.error-characterisbusy", $par[3] -> name); return false; }	
		// wife or husband are in location?
		if ( $par[2] -> position_id != $par[1] -> region -> id )
		{ $message = Kohana::lang("global.error-characternotinlocation", $par[2] -> name, kohana::lang($par[1] -> region -> name)); return false; }
		if ( $par[3] -> position_id != $par[1] -> region -> id )
		{ $message = Kohana::lang("global.error-characternotinlocation", $par[3] -> name, kohana::lang($par[1] -> region -> name)); return false; }		
				
		// char are available?
		if ( $par[2] -> user -> availableregfunctions != 'Y' )
		{ $message = Kohana::lang("charactions.error-charnotavailableforregfunctions", $par[2] -> name); return false; }
			
		if ( $par[3] -> user -> availableregfunctions != 'Y' )
		{ $message = Kohana::lang("charactions.error-charnotavailableforregfunctions", $par[3] -> name); return false; }	
		
		// husband is male, and wife is female?
		if ( $par[2] -> sex != 'M' or $par[3] -> sex != 'F' )
		{ $message = Kohana::lang("ca_celebratemarriage.error-genderincorrect"); return false; }				
		
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
		
		// do husband and wife belong to same religion
		
		if ( $par[2] -> church_id != $par[3] -> church_id )
		{ $message = Kohana::lang("ca_celebratemarriage.error-husbandandwifenotofsamereligion"); return false; }
		
		if ( $par[2] -> church_id != $par[0] -> church_id )
		{ $message = Kohana::lang("ca_celebratemarriage.error-husbandandwifenotofsamereligionofpriest"); return false; }
		
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
		
		if (
			$par[3] -> energy < (self::DELTA_ENERGY )  or
			$par[3] -> glut < (self::DELTA_GLUT) )
		{   $message = Kohana::lang("global.error-characternotenoughenergyorglut", $par[3] -> name); return false; }
				
		// do church have all required items?
		
		if ( $par[1] -> contains_item( 'flowers', 100 ) == false  )
		{   $message = Kohana::lang( "global.error-missingiteminstructure", 100, Kohana::lang('items.flowers_name')); return false;}		
		
		if ( $par[1] -> contains_item( 'mulberry_cake', 20 ) == false  )
		{   $message = Kohana::lang("global.error-missingiteminstructure", 20, kohana::lang('items.mulberry_cake_name')); return false; }
		
		if ( $par[1] -> contains_item( 'winebottle', 20 ) == false  )
		{   $message = Kohana::lang("global.error-missingiteminstructure", 20, kohana::lang('items.winebottle_name')); return false; }
		
		if ( $par[1] -> contains_item( 'beerbottle', 10 ) == false  )
		{   $message = Kohana::lang("global.error-missingiteminstructure", 10, kohana::lang('items.beerbottle_name')); return false; }
		
		if ( $par[1] -> contains_item( 'mulberrybrandybottle', 5 ) == false  )
		{   $message = Kohana::lang("global.error-missingiteminstructure", 5, kohana::lang('items.mulberrybrandybottle_name')); return false; }
		
		if ( $par[1] -> contains_item( 'meadbottle', 5 ) == false  )
		{   $message = Kohana::lang("global.error-missingiteminstructure", 5, kohana::lang('items.meadbottle_name')); return false; }
		
		// C'è almeno un golden basin al 25%?
		$exists = false;
		foreach ( $par[1] -> item as $item )
			if ( $item -> cfgitem -> tag == 'goldenbasin' and $item -> quality >= self::GOLDENBASIN_WEAR )
				$exists = true;
		
		if ( $exists == false )
		{ $message = Kohana::lang("ca_celebratemarriage.goldenbasinnotexists");
		 return false; }
		
		// do bride and groom have each one a ring of the same type?
		
		$passed = false;
		
		if (
			
			(Character_Model::has_item( $par[2] -> id, 'ringemerald', 1) == true
			or
			Character_Model::has_item( $par[2] -> id, 'ringruby', 1) == true
			or
			Character_Model::has_item( $par[2] -> id, 'ringdiamond', 1) == true
			or 
			Character_Model::has_item( $par[2] -> id, 'ringsapphire', 1) == true
			)
			and
			(Character_Model::has_item( $par[3] -> id, 'ringemerald', 1) == true
			or
			Character_Model::has_item( $par[3] -> id, 'ringruby', 1) == true
			or
			Character_Model::has_item( $par[3] -> id, 'ringsapphire', 1) == true
			or 
			Character_Model::has_item( $par[3] -> id, 'ringdiamond', 1) == true)			
		)
			$passed = true;		
		
		if ( $passed == false )
		{   $message = Kohana::lang("ca_celebratemarriage.error-missingrings"); return false; }
		
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
		$this -> param3 = $par[3] -> id;
		$this -> param4 = $par[1] -> id;		
		$this -> endtime = $this -> starttime + $this -> get_action_time( $par[0] );
		$this -> save();		
		
		// save a blocking action for husband and wife
				
		$a = new Character_Action_Model();		
		$a -> character_id = $par[2] -> id;
		$a -> action = 'celebratemarriage';
		$a -> starttime = time();			
		$a -> param1 = $par[0] -> id;
		$a -> param2 = $par[2] -> id;
		$a -> param3 = $par[3] -> id;
		$a -> param4 = $par[1] -> id;
		$a -> status = "running";	
		$a -> endtime = $this -> starttime + $this -> get_action_time( $par[0]);
		$a -> save();		
		
		$b = new Character_Action_Model();		
		$b -> character_id = $par[3] -> id;
		$b -> action = 'celebratemarriage';
		$b -> starttime = time();		
		$b -> param1 = $par[0] -> id;
		$b -> param2 = $par[2] -> id;
		$b -> param3 = $par[3] -> id;
		$b -> param4 = $par[1] -> id;
		$b -> status = "running";	
		$b -> endtime = $this->starttime + $this -> get_action_time( $par[0]);
		$b -> save();	
		
		// destroy and consume items
		
		Item_Model::consumeitem_instructure( 'goldenbasin', $par[1] -> id, self::GOLDENBASIN_WEAR );
				
		$item = Item_Model::factory(null, 'flowers');
		$item -> removeitem( 'structure', $par[1] -> id, 100 );
		
		$item = Item_Model::factory(null, 'mulberry_cake');
		$item -> removeitem( 'structure', $par[1] -> id, 20 );
		
		$item = Item_Model::factory(null, 'winebottle');
		$item -> removeitem( 'structure', $par[1] -> id, 20 );
		
		$item = Item_Model::factory(null, 'beerbottle');
		$item -> removeitem( 'structure', $par[1] -> id, 10 );
		
		$item = Item_Model::factory(null, 'mulberrybrandybottle');
		$item -> removeitem( 'structure', $par[1] -> id, 5 );
		
		$item = Item_Model::factory(null, 'meadbottle');
		$item -> removeitem( 'structure', $par[1] -> id, 5 );
		
		// consume faith points
		
		$par[1] -> modify_stat ('faithpoints', - $this -> fpcost );		
		$message = kohana::lang('ca_celebratemarriage.info-celebrateok');						
		
		Character_Event_Model::addrecord( 
			$par[0] -> id , 
			'normal', 
			'__events.celebratemarriage_start' );
		
		Character_Event_Model::addrecord( 
			$par[2] -> id , 
			'normal', 
			'__events.celebratemarriage_start' );
		
		Character_Event_Model::addrecord( 
			$par[3] -> id , 
			'normal', 
			'__events.celebratemarriage_start' );
					
		return true;
	}

	public function complete_action( $data )
	{
		
		$charaction = ORM::factory('character', $data -> character_id );
		$officer = ORM::factory('character', $data -> param1 );
		$husband = ORM::factory('character', $data -> param2 );
		$wife = ORM::factory('character', $data -> param3 );		
		$structure = StructureFactory_Model::create( null, $data -> param4 );
		
		kohana::log('debug', '-> Husband: ' . $husband -> name );
		kohana::log('debug', '-> Wife: ' . $wife -> name );
		kohana::log('debug', '-> Officer: ' . $officer -> name );
				
		//////////////////////////////////////////////////////////////////
		// Sottraggo l'energia e la sazietà al char
		///////////////////////////////////////////////////////////////////
				
		$charaction -> modify_energy ( - self::DELTA_ENERGY, false, 'celebratemarriage' );
		$charaction -> modify_glut ( - self::DELTA_GLUT );
		$charaction -> save();	
		
		// Azioni relative a chi ha officiato la funzione
		
		if ( $charaction -> id == $officer -> id )
		{
		
			// Consumo degli items/vestiti indossati
			Item_Model::consume_equipment( $this->equipment, $officer );
		
			// rendo la proposta non più valida.
			
			$proposal = ORM::factory('message') -> where
			( array( 			
			'fromchar_id' => $husband -> id,
			'tochar_id' => $wife -> id,
			'type' => 'weddingproposal',
			'param1' => 1 ) ) -> find();
			
			$proposal -> param1 = 2;
			$proposal -> save();
						
			// Save statistics
			
			$charaction -> modify_stat( 'weddings', +1, $structure -> structure_type -> church -> id );		
			$structure ->  modify_stat( 'weddings', +1 );
			
			Character_Event_Model::addrecord( 
				$officer -> id , 
				'normal', 
				'__events.celebratemarriage_end' );				
				
			// evento per chi ha officiato

			Character_Event_Model::addrecord( $officer -> id, 
			'normal', '__events.celebrateweddingofficer' . 
			';' . Character_Model::create_publicprofilelink($husband -> id, $husband -> name) . 
			';' . Character_Model::create_publicprofilelink($wife -> id, $wife -> name),
			'normal' );
			
			// evento per town crier
						
			Character_Event_Model::addrecord( null, 
			'announcement', '__events.weddingcelebrated' . 
			';' . Character_Model::create_publicprofilelink($officer -> id, $officer -> name) . 
			';__' . $structure -> structure_type -> name .
			';__' . $structure -> region -> name .
			';' . Character_Model::create_publicprofilelink($husband -> id, $husband -> name) . 
			';' . Character_Model::create_publicprofilelink($wife -> id, $wife -> name) ,	
			'normal' );
				
		}
		
		// Azioni relative a chi ha subìto la funzione
		// 1. Setta relationship
		
		if ( $charaction -> id == $husband -> id )
		{		
			Character_Relationship_Model::add( $charaction -> id, $wife -> id, 'husband' );		
			Character_Event_Model::addrecord( 
				$husband -> id , 
				'normal', 
				'__events.celebratemarriage_end' );				
			
			Character_Event_Model::addrecord( 
			$husband -> id, 
			'normal', 
			'__events.celebrateweddingspouses' . 
			';' . Character_Model::create_publicprofilelink($wife -> id, $wife -> name) . 
			';__' . $structure -> structure_type -> name .
			';__' . $structure -> region -> name,
			'normal' );
			
			// evento permanente di matrimonio

			Character_Permanentevent_Model::add( $husband -> id, 
			'__permanentevents.married' .
			';' . Character_Model::create_publicprofilelink($wife -> id, $wife -> name) . 
			';__' . $structure -> structure_type -> name .
			';__' . $structure -> region -> name .
			';' . Character_Model::create_publicprofilelink($officer -> id, $officer -> name) 
			);
	
		}
		
		if ( $charaction -> id == $wife -> id )
		{		
			Character_Relationship_Model::add( $charaction -> id, $husband -> id, 'wife' );		
			Character_Event_Model::addrecord( 
				$wife -> id , 
				'normal', 
				'__events.celebratemarriage_end' );				
			
			Character_Event_Model::addrecord( $wife -> id, 
			'normal', 
			'__events.celebrateweddingspouses' . 
			';' . Character_Model::create_publicprofilelink($husband -> id, $husband -> name) . 
			';__' . $structure -> structure_type -> name .
			';__' . $structure -> region -> name,
			'normal' );
			
			Character_Permanentevent_Model::add( $wife -> id, 
			'__permanentevents.married' .
			';' . Character_Model::create_publicprofilelink($husband -> id, $husband -> name) . 
			';__' . $structure -> structure_type -> name .
			';__' . $structure -> region -> name			
			);

		}
		
	}
		
	protected function execute_action() {}
	
	public function cancel_action() { return true; }

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
		
		$info = Church_Model::get_info($par[1] -> structure_type -> church_id);
		$cost = max(1, round( self::REQUESTEDFP * $info['followers'] / $info['parishchurches'] )); 
		kohana::log('debug', '-> craft - FP cost: ' . $cost ); 				
		return $cost ;
	}
	
}
