<?php defined('SYSPATH') OR die('No direct access allowed.');


class CA_collectwater_Model extends Character_Action_Model
{
	// Costanti	
	const DELTA_GLUT = 5;
	const DELTA_ENERGY = 10;
	const NUM_BOTTLES = 10;

	protected $cancel_flag = true;
	protected $immediate_action = false;

	protected $basetime       = 3;  // 3 ore
	protected $attribute      = 'str';  // attributo forza
	protected $appliedbonuses = array ('workerpackage'); // bonuses da applicare
	
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
			'right_hand' => array
			(
				'items' => array('iron_bucket'),
				'consume_rate' => 'medium',
			),
		),
	);
	
	// Effettua tutti i controlli relativi al move, sia quelli condivisi
	// con tutte le action che quelli peculiari del move
	// @input: $par[0] = structure, $par[1] = char, $par[2] = parametro code
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function check( $par, &$message )
	{ 
		$message = "";
		
		if ( ! parent::check( $par, $message ) )					
			return false;
			
		// Controllo che la regione sia conquistata
		if ( $par[0] -> region -> is_independent() )
		{	$message = Kohana::lang("charactions.regionisindependent"); return false;}	
		
		/////////////////////////////////////////////////////////////////////////////////////
		// verifica la relazione diplomatica
		/////////////////////////////////////////////////////////////////////////////////////
		
		$rd = Diplomacy_Relation_Model::get_diplomacy_relation( 
			$par[0] -> region -> kingdom_id, 
			$par[1] -> region -> kingdom_id );
		
		if ( !is_null( $rd ) and ( $rd['type'] == 'hostile' or $rd['type'] == 'neutral' ))
		{		
			$message = kohana::lang('structures_market.error-hostileaccessdenied'); 
			return false;				
		}
		
		// controllo parametri URL
		$queuebonus = false;
		if ( Character_Model::get_premiumbonus( $par[1] -> id, 'workerpackage') !== false )			
			$queuebonus = true;
		
		// Controllo, se il moltiplicatore è > 1, il char deve avere il bonus
		if ( !in_array ( $par[2], array( 1, 2, 3 )) or ($par[2] > 1 and ! $queuebonus ) )
				{ $message = Kohana::lang("global.operation_not_allowed"); return false; }
		
		// Controllo che il char abbia l'energia e la sazieta' richiesti
		if (
			$par[1]->energy < (self::DELTA_ENERGY * $par[2])  or
			$par[1] -> glut < (self::DELTA_GLUT * $par[2]) )
		{   $message = Kohana::lang("charactions.notenoughenergyglut"); return false; }
			
		// Controllo che il char abbia sufficienti contenitori di vetro
		if ( $par[1]->get_item_quantity( 'glassbottle' ) < $par[2]*self::NUM_BOTTLES )
		{ $message = kohana::lang('ca_collectwater.error-notenoughbottles',
			$par[2]*self::NUM_BOTTLES ); return FALSE; }
		
		return true;
	}


	// Funzione per l'inserimento dell'azione nel DB.
	// Questa funzione appende solo una azione _non la esegue_
	// Al momento non si prevedono azioni istantanee.
	// @input: $par[0] = structure, $par[1] = char, $par[2] = qta
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function append_action( $par, &$message )
	{
		
		
		// Rimuovo i contenitori di vetro dal char
		
		$glassbottle = Item_Model::factory( null, 'glassbottle' );
		$glassbottle -> removeitem( 'character', $par[1]->id, self::NUM_BOTTLES * $par[2] );
		
		$timeaction = $this -> get_action_time( $par[1] )* $par[2];
		$this->character_id = $par[1]->id;
		$this->starttime = time();			
		$this->status = 'running';			
		$this->endtime = $this->starttime + $timeaction;
		$this->param1 = $par[0] -> id;		
		$this->param3 = $par[2];
		$this->save();		
		
		$itemonrighthand = $par[1] -> get_bodypart_item('right_hand');
		if ( !is_null( $itemonrighthand ) )
		{
			$dex = $par[1] -> get_attribute( 'dex' );
			$consume = $itemonrighthand -> get_proper_item_consume('dex', $dex);
			$itemonrighthand -> consume( $consume * $par[2], 'action-collectwater');
		}
		
		$message = kohana::lang('ca_collectwater.info-startaction');
		
		// Consumo degli items/vestiti obbligatori indossati
		Item_Model::consume_equipment( $this->equipment, $par[1], $par[2] );
		
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
		$char = ORM::factory('character') -> find( $data->character_id );
		
		///////////////////////////////////////////////////////////////////
		// Sottraggo l'energia e la sazietà al char
		///////////////////////////////////////////////////////////////////
		
		$char -> modify_energy ( - self::DELTA_ENERGY * $data -> param3, false, 'collectwater');
		$char -> modify_glut ( - self::DELTA_GLUT * $data -> param3);			
		$char -> save();
		
		// fornisce 8 bottiglie d'acqua
		$item = Item_Model::factory( null, 'waterbottle' );						
		$item -> additem('character',
			$char -> id, (self::NUM_BOTTLES * $data -> param3 ));
		
		Character_Event_Model::addrecord( 
			$char -> id, 
			'normal', 
			'__events.collectwaterok' . 
			';' . (self::NUM_BOTTLES * $data -> param3) .
			';__' . $item ->cfgitem -> name,
			'normal' );

		$char -> modify_stat( 'itemproduction', (self::NUM_BOTTLES * $data -> param3 ), 
			$item -> cfgitem -> id );		
		
	}
	
	protected function execute_action() {}
	
	public function cancel_action( ) 
	{ return true; }
	
	// Questa funzione costruisce un messaggio da visualizzare 
	// in attesa che la azione sia completata.
	
	public function get_action_message( $type = 'long') 
	{
		$pending_action = $this->get_pending_action();
		$message = "";				
		
		if ( $pending_action -> loaded )
		{
			if ( $type == 'long' )		
			$message = '__regionview.collectwater_longmessage';
			else
			$message = '__regionview.collectwater_shortmessage';
		}
		
		return $message;
	
	}

	
}
