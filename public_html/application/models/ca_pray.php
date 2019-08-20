<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Pray_Model extends Character_Action_Model
{
	// Costanti	
	
	const DELTA_GLUT = 3;
	const DELTA_ENERGY = 3;
	const DELTA_FAITHPERHOUR = 4;

	protected $cancel_flag = true;
	protected $immediate_action = false;
	
	// L'azione richiede che il personaggio indossi
	// un determinato equipaggiamento
	
	protected $requiresequipment = true;
	
	// Equipaggiamento o vestiario necessario in base al ruolo
	// Consume_rate = percentuale di consumo dell'item
	
	protected $equipment = array
	(
		'church_level_1' => array
		(
			'head' => array
			(
				'items' => array('mitra_rome','panzva_turnu','hat_cairo','hat_kiev','hat_norse'),
				'consume_rate' => 'medium',
			),
			'right_hand' => array
			(
				'items' => array('mysticrod_turnu','scepter_kiev','pastoral_rome', 'mysticrod_cairo','scepter_norse'),
				'consume_rate' => 'medium',
			),
			'body' => array
			(
				'items' => array('tunic_church_level_1_rome', 'tunic_church_level_1_turnu', 'tunic_church_level_1_kiev', 'tunic_church_level_1_cairo','tunic_church_level_1_norse'),
				'consume_rate' => 'medium',
			),
			'feet' => array
			(
				'items' => array('any'),
				'consume_rate' => 'medium',
			)
		),
		'church_level_2' => array
		(
			'head' => array
			(
				'items' => array('mitra_rome','panzva_turnu','hat_cairo','hat_kiev','hat_norse'),
				'consume_rate' => 'medium',
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
				'items' => array('any'),
				'consume_rate' => 'medium'
			)		),
		'church_level_3' => array
		(
			'head' => array
			(
				'items' => array('mitra_rome','panzva_turnu','hat_cairo','hat_kiev','hat_norse'),
				'consume_rate' => 'medium'
			),
			'right_hand' => array
			(
				'items' => array('mysticrod_turnu','scepter_kiev','pastoral_rome', 'mysticrod_cairo','scepter_norse'),
				'consume_rate' => 'medium',
			),
			'body' => array
			(
				'items' => array('tunic_church_level_3_rome', 'tunic_church_level_3_turnu', 'tunic_church_level_3_kiev', 'tunic_church_level_3_cairo','tunic_church_level_3_norse'),
				'consume_rate' => 'medium',
			),
			'feet' => array
			(
				'items' => array('any'),
				'consume_rate' => 'medium',
			)
		),
		'church_level_4' => array
		(
			'right_hand' => array
			(
				'items' => array('holybook'),
				'consume_rate' => 'medium',
			),
			'body' => array
			(
				'items' => array('tunic_church_level_4_rome', 'tunic_church_level_4_turnu', 'tunic_church_level_4_kiev', 'tunic_church_level_4_cairo','tunic_church_level_4_norse'),
				'consume_rate' => 'medium',
			),
			'feet' => array
			(
				'items' => array('any'),
				'consume_rate' => 'medium',
			),
		),
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
				'consume_rate' => 'medium'
			),
		),
	);
	
	protected $basetime       = 2;
	protected $attribute      = 'intel';  // attributo forza
	protected $appliedbonuses = array ( 'workerpackage' ); // bonuses da applicare
	
	// Effettua tutti i controlli relativi alla Pray, sia quelli condivisi
	// con tutte le action che quelli peculiari della Pray
	// @input: 
	// $par[0] = structure, 
	// $par[1] = char, 
	// $par[2] = parametro code
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function check( $par, &$message )
	{ 
	
		$message = "";
		
		if ( ! parent::check( $par, $message ) )					
			return false;
		
		// controllo parametri URL
		if ( !$par[0] -> loaded or !$par[1] -> loaded or $par[0] -> region -> id != $par[1] -> position_id )
		{ $message = Kohana::lang("global.operation_not_allowed"); return false; }		
		
		if ( !in_array ( $par[2], array( 1, 2, 3 )) or ($par[2] > 1 and (Character_Model::get_premiumbonus( $par[1] -> id, 'workerpackage' ) === false )) )
				{ $message = Kohana::lang("global.operation_not_allowed"); return false; }
		
		// Controllo che il char abbia l'energia e la sazieta' richiesti
		if (
			$par[1] -> energy < (self::DELTA_ENERGY * $par[2])  or
			$par[1] -> glut < (self::DELTA_GLUT * $par[2]) )
		{   $message = Kohana::lang("charactions.notenoughenergyglut"); return false; }		
		
		// Controllo che il char sia della stessa chiesa
		if ( $par[0] -> structure_type -> church_id != $par[1] -> church_id )
		{   $message = Kohana::lang("ca_pray.charnotofsamechurch"); return false; }		
				
		
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
			
		$this->character_id = $par[1]->id;
		$this->starttime = time();			
		$this->status = "running";	
		$this->param1 = $par[0] -> id;
		$this->param2 = null;
		$this->param3 = $par[2];
		$this->endtime = $this -> starttime + $this -> get_action_time( $par[1] ) * $par[2] ;
		$this->save();		
								
		$message = kohana::lang('ca_pray.pray-ok');						
								
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
	
		$char = ORM::factory('character')->find( $data->character_id );
		
		// Consumo degli items/vestiti indossati
		Item_Model::consume_equipment( $this->equipment, $char, $data->param3 );
		
		///////////////////////////////////////////////////////////////////
		// Sottraggo l'energia e la sazietà al char
		///////////////////////////////////////////////////////////////////
				
		$char -> modify_energy ( - self::DELTA_ENERGY * $data -> param3, false, 'pray' );
		$char -> modify_glut ( - self::DELTA_GLUT * $data -> param3);	
		$char -> save();	

		// Guardia: se il char non appartiene una casa, 
		// ritorna
		
		if ( $char -> church -> name == 'nochurch' )
			return; 
		
		//////////////////////////////////////////////////////////////////////
		// Aumenta Faith Level
		//////////////////////////////////////////////////////////////////////
		if ( Character_Model::get_premiumbonus(  $data-> character_id, 'rosary' ) !== false )
		{
			$char -> modify_faithlevel( 2* $data ->  param3 * self::DELTA_FAITHPERHOUR );
		}
		else
		{
			$char -> modify_faithlevel( $data -> param3 * self::DELTA_FAITHPERHOUR );
		}	
		//////////////////////////////////////////////////////////////////////
		// Aumenta Faith Points della gerarchia
		//////////////////////////////////////////////////////////////////////
		
		$contributedfp = 0;
		$structure = StructureFactory_Model::create( null, $data -> param1 );		
		
		if ( $structure -> loaded )
		{
			
			$hasrosarybonus = Character_Model::get_premiumbonus(  $data-> character_id, 'rosary' );
			
			if ( $hasrosarybonus !== false )
				$points = 4 * $data -> param3 ;				
			else			
				$points = 2 * $data -> param3 ;	
			
			$structure -> modify_fp( $points, 'prayer');
			$contributedfp += $points;
			
			Structure_Event_Model::newadd(
				$structure -> id,
				'__events.prayfp' . 
				';' . $char -> name . 
				';' . $points );
						
			$structure -> modify_coins( 0.7 * $data -> param3, 'prayer' );
			
			// search parent structure
			$p1structure = ORM::factory('structure', $structure -> parent_structure_id );
			if ( $p1structure -> loaded )
			{
				if ( $hasrosarybonus !== false )
					$points = 2 * $data -> param3 ;				
				else			
					$points = 1 * $data -> param3 ;	
				
				
				$p1structure -> modify_fp( $points, '__religion.prayer');
				$contributedfp += $points;				
				
				$p1structure -> modify_coins( 0.40 * $data -> param3, 'prayer' );
				
				$p2structure = ORM::factory('structure', $p1structure -> parent_structure_id );
				if ( $p2structure -> loaded )
				{
					if ( $hasrosarybonus !== false )
						$points = 2 * $data -> param3 ;				
					else			
						$points = 1 * $data -> param3 ;	
					
					$p2structure -> modify_fp( $points, '__religion.prayer');
					$contributedfp += $points;					
					
					Structure_Event_Model::newadd(
					$p2structure -> id,
					'__events.prayfp' . 
					';' . $char -> name . 
					';' . $points );
					
					$p2structure -> modify_coins( 0.30 * $data -> param3, 'prayer' );
					
					$p3structure = ORM::factory('structure', $p2structure -> parent_structure_id );
					if ( $p3structure -> loaded )				
					{
						if ( $hasrosarybonus !== false )
							$points = 4 * $data -> param3 ;				
						else			
							$points = 2 * $data -> param3 ;	
						
						
						$p3structure -> modify_fp( $points, '__religion.prayer');
						$contributedfp += $points;						
						
						Structure_Event_Model::newadd(
						$p3structure -> id,
						'__events.prayfp' . 
						';' . $char -> name . 
						';' . $points );
						
						$p3structure -> modify_coins( 0.10 * $data -> param3, 'prayer' );
						
					}
				}
			}
		}
		
		//////////////////////////////////////////////////////////////////////
		// Save statistic
		//////////////////////////////////////////////////////////////////////
		
		$char -> modify_stat( 
			'fpcontribution', 
			$contributedfp, 
			$char -> church_id,
			null,
			false
		);
		
		kohana::log('debug', '-> Adding ' . $contributedfp . ' AFP to char: ' . $char -> name );
		
		$structure -> modify_stat( 
			'fpcontribution', 
			$contributedfp,
			null,
			null,
			null,
			null,
			false
		);
		
		//////////////////////////////////////////////////////////////////////
		// Events
		//////////////////////////////////////////////////////////////////////
				
		Character_Event_Model::addrecord( 
			$char -> id, 
			'normal', 
			'__events.prayok' . 
			';' . $contributedfp 
		);		
		
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
			$message = '__regionview.pray_longmessage';
			else
			$message = '__regionview.pray_shortmessage';
		}
		return $message;
	
	}
}
