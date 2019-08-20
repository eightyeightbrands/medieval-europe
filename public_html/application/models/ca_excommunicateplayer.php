<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Excommunicateplayer_Model extends Character_Action_Model
{
	
	// Costanti	
	
	const DELTA_GLUT = 36;
	const DELTA_ENERGY = 50;
	const REQUESTEDFP = 300;
	const FAITHLEVELREQUESTED = 85;
	
	protected $cancel_flag = true;
	protected $immediate_action = false;
	protected $fpcost = 0;
	
	protected $basetime       = 6;  // 6 ore
	protected $attribute      = 'intel';  // attributo forza
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
				'consume_rate' => 'veryhigh',
			),
			'right_hand' => array
			(
				'items' => array('mysticrod_turnu','scepter_kiev','pastoral_rome', 'mysticrod_cairo','scepter_norse'),
				'consume_rate' => 'veryhigh',
			),
			'body' => array
			(
				'items' => array('tunic_church_level_1_rome', 'tunic_church_level_1_turnu', 'tunic_church_level_1_kiev', 'tunic_church_level_1_cairo','tunic_church_level_1_norse'),
				'consume_rate' => 'veryhigh',
			),
			'feet' => array
			(
				'items' => array('shoesm_1', 'shoesf_1'),
				'consume_rate' => 'veryhigh',
			)
		),
		'church_level_2' => array
		(
			'head' => array
			(
				'items' => array('mitra_rome','panzva_turnu','hat_cairo','hat_kiev','hat_norse'),
				'consume_rate' => 'veryhigh',
			),
			'right_hand' => array
			(
				'items' => array('mysticrod_turnu','scepter_kiev','pastoral_rome', 'mysticrod_cairo','scepter_norse'),
				'consume_rate' => 'medium',
			),
			'body' => array
			(
				'items' => array('tunic_church_level_2_rome', 'tunic_church_level_2_turnu', 'tunic_church_level_2_kiev', 'tunic_church_level_2_cairo','tunic_church_level_2_norse'),
				'consume_rate' => 'medium',
			),
			'feet' => array
			(
				'items' => array('shoesm_1', 'shoesf_1'),
				'consume_rate' => 'veryhigh'
			)		),
		'church_level_3' => array
		(
			'head' => array
			(
				'items' => array('mitra_rome','panzva_turnu','hat_cairo','hat_kiev','hat_norse'),
				'consume_rate' => 'veryhigh'
			),
			'right_hand' => array
			(
				'items' => array('mysticrod_turnu','scepter_kiev','pastoral_rome', 'mysticrod_cairo','scepter_norse'),
				'consume_rate' => 'veryhigh',
			),
			'body' => array
			(
				'items' => array('tunic_church_level_3_rome', 'tunic_church_level_3_turnu', 'tunic_church_level_3_kiev', 'tunic_church_level_3_cairo','tunic_church_level_3_norse'),
				'consume_rate' => 'veryhigh',
			),
			'feet' => array
			(
				'items' => array('shoesm_1', 'shoesf_1'),
				'consume_rate' => 'veryhigh',
			)
		),
		'church_level_4' => array
		(
			'right_hand' => array
			(
				'items' => array('holybook'),
				'consume_rate' => 'veryhigh',
			),
			'body' => array
			(
				'items' => array('tunic_church_level_4_rome', 'tunic_church_level_4_turnu', 'tunic_church_level_4_kiev', 'tunic_church_level_4_cairo','tunic_church_level_4_norse'),
				'consume_rate' => 'veryhigh',
			),
			'feet' => array
			(
				'items' => array('shoesm_1', 'shoesf_1'),
				'consume_rate' => 'veryhigh',
			),
		),
		'all' => array
		(
			'body' => array
			(
				'items' => array('any'),
				'consume_rate' => 'veryhigh'
			),
			'torso' => array
			(
				'items' => array('any'),
				'consume_rate' => 'veryhigh'
			),
			'legs' => array
			(
				'items' => array('any'),
				'consume_rate' => 'veryhigh'
			),
			'feet' => array
			(
				'items' => array('any'),
				'consume_rate' => 'veryhigh'
			),
		),
	);
	
	
	
	// Effettua tutti i controlli relativi al move, sia quelli condivisi
	// con tutte le action che quelli peculiari del move
	// @input: 
	// $par[0] = oggetto char che invoca l' azione
	// $par[1] = oggetto char che subisce l' azione
	// $par[2] = struttura da cui parte l' azione
	// $par[3] = motivazione
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function check( $par, &$message )
	{ 
		$message = "";
		
		if ( ! parent::check( $par, $message ) )					
			return false;
		
		if ( !$par[1] -> loaded or $par[1] -> name == '' )
		{ $message = Kohana::lang("global.error-characterunknown"); return false; }				
		
		if ( $par[3] == '' )
		 { $message = Kohana::lang("ca_excommunicateplayer.reasonismandatory"); return false; }				
		
		// controllo parametri URL
		if ( !$par[1] -> loaded or !$par[2] -> loaded or $par[2] -> region -> id != $par[0] -> position_id )
		{ $message = Kohana::lang("global.operation_not_allowed"); return false; }				
		 
		// controllo faith points
		$this -> fpcost = $this -> get_neededfp ( $par ); 
		$fppoints = Structure_Model::get_stat_d( $par[2] -> id, 'faithpoints' ) ;		
		if ( !$fppoints -> loaded or $fppoints -> value < $this -> fpcost )
		{ $message = Kohana::lang("global.error-notenoughfp", $this -> fpcost ); return false; }		
		
		// check faith level
		$fl = Character_Model::get_stat_d( $par[0] -> id, 'faithlevel' );
		if ( !$fl -> loaded or $fl -> value < self::FAITHLEVELREQUESTED )
		{ $message = Kohana::lang("religion.faithleveltoolow", self::FAITHLEVELREQUESTED ); return false; }
		
		// Controllo che il char abbia l'energia e la sazieta' richiesti
		if (
			$par[0] -> energy < self::DELTA_ENERGY  or
			$par[0] -> glut < self::DELTA_GLUT )
		{ $message = Kohana::lang("charactions.notenoughenergyglut"); return false; }		
				
		// Controllo che il char target sia della stessa chiesa
		if ( $par[2] -> structure_type -> church_id != $par[1] -> church_id )
		{   $message = Kohana::lang("global.error-charnotcorrectchurch"); return false; }		
		
		// controllo scroll presente
		if ( Character_Model::has_item( $par[0]->id, 'paper_piece', 1 ) == false )
		{   $message = kohana::lang('charactions.paperpieceneeded'); return false; }		
		
		// il giocatore non può avere ruoli religiosi.
		$role = $par[1] -> get_current_role();
		if ( !is_null($role) and $role -> get_roletype() == 'religious' )
		{   $message = Kohana::lang("ca_excommunicateplayer.error-playerhasreligiousrole"); return false; }				
		
		return true;
	}
	
	protected function append_action( $par, &$message )
	{
		$timeaction = $this -> get_action_time( $par[0] );
		$this -> character_id = $par[0] -> id;
		$this -> starttime = time();			
		$this -> status = "running";	
		$this -> param1 = $par[1] -> id;
		$this -> param2 = $par[3]; 
		$this -> endtime = $this -> starttime + $timeaction;			
		$this -> save();
		
		// rimuovi fp points
		$par[2] -> modify_fp( - $this -> fpcost, 'excommunication' );
				
		// rimuovi un paper
		$item = Item_Model::factory(null, 'paper_piece');
		$item -> removeitem( "character", $par[0] -> id, 1);
		
		$message = kohana::lang('ca_excommunicateplayer.excommunicate-ok');						
								
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
			
		$char = ORM::factory('character', $data -> character_id );
		$target = ORM::factory('character', $data -> param1 );
		
		// Consumo degli items/vestiti indossati
		Item_Model::consume_equipment( $this->equipment, $char );
		
		///////////////////////////////////////////////////////////////////
		// Sottraggo l'energia e la sazietà al char
		///////////////////////////////////////////////////////////////////
				
		$char -> modify_energy ( - self::DELTA_ENERGY, false, 'excommunicate' );
		$char -> modify_glut ( - self::DELTA_GLUT );
		$char -> save();	
		
		$fl_newvalue = 0;
		$afp_decrement = 0;
	
		//////////////////////////////////////////////////////////////////////
		// Diminuisci il FL del target (100%)
		//////////////////////////////////////////////////////////////////////
		
		$target -> modify_faithlevel( $fl_newvalue, true );
		
		//////////////////////////////////////////////////////////////////////
		// Diminuisci i faith point accumulati
		//////////////////////////////////////////////////////////////////////
				
		$contributedfps = $target -> get_stats( 'fpcontribution', $target -> church_id );		
		
		if ( !is_null( $contributedfps ) )
			$target -> modify_stat( 
				'fpcontribution', 
				round( $contributedfps[0] -> value * $afp_decrement / 100 ), 
				$target -> church_id, 
				null, 
				true );
		
		//////////////////////////////////////////////////////////////////////
		// Aggiungi statistica di Scomunica
		//////////////////////////////////////////////////////////////////////				
		
		$target -> modify_stat( 
			'excommunication', 
			time(), 
			$target -> church_id, 
			null, 
			true );
				
		//////////////////////////////////////////////////////////////////////
		// Scomunica
		//////////////////////////////////////////////////////////////////////				
		
		$target -> church_id = 4;
		$target -> save();		
		
		//////////////////////////////////////////////////////////////////////
		// Events
		//////////////////////////////////////////////////////////////////////
				
		Character_Event_Model::addrecord( 
		$char -> id, 
		'normal', 
		'__events.excommunicationsource' . 
		';' . $target -> name .
		';' . $data -> param2,
		'evidence' );						
				
		Character_Event_Model::addrecord( 
		$target -> id, 
		'normal', 
		'__events.excommunicationtarget' . 
		';' . $char -> name .
		';' . $data -> param2,
		'evidence' );				
		
		Character_Event_Model::addrecord( 
		null, 
		'announcement', 
		'__events.excommunicatedplayer' . 
		';' . $char -> name .
		';' . $target -> name .
		';' . $data -> param2,		
		'evidence' );				
		
	}
	
	public function cancel_action() { return true ; }

		public function get_action_message( $type = 'long') 
	{
		$message = "";				
		$pending_action = $this -> get_pending_action();
		$target = ORM::factory('character', $pending_action -> param1 ); 

		if ( $pending_action -> loaded )
		{
			if ( $type == 'long' )		
				$message = '__regionview.excommunicate_longmessage;' . $target -> name;
			else
				$message = '__regionview.excommunicate_shortmessage';
		}
		return $message;
	
	}
	
	/**
	* Costo in FP - dipende dalla anzianità del char
	*/
	
	/**
	* Costo in FP - dipende dalla fedeltà del char (AFP accumulati)
	*/
	
	protected function get_neededfp( $par )
	{				
			  
		$info = Church_Model::get_info($par[2] -> structure_type -> church_id);		
		$fpcontributionachievement = Character_Model::get_achievement( $par[0] ->id, 'stat_fpcontribution');
		
		if (!is_null($fpcontributionachievement))
			$cost = max (1, self::REQUESTEDFP * pow( $fpcontributionachievement['stars'], 2 ) );		
		else
			$cost = 10;
		
		return $cost ; 
	
	}
}
