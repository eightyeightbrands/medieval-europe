		<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_CleanPrisons_Model extends Character_Action_Model
{

	protected $cancel_flag = true;
	protected $immediate_action = false;
	protected $enabledifrestrained = true;
	
	const DELTA_GLUT = 1;		
	const DELTA_ENERGY = 8;	
	const MONEY = 5;
	
	protected $basetime       = 2.5;
	protected $attribute      = 'str';  // attributo forza
	protected $appliedbonuses = array ( 'workerpackage'); // bonuses da applicare

	// L'azione richiede che il personaggio indossi
	// un determinato equipaggiamento
	protected $requiresequipment = false;
	
	// Equipaggiamento o vestiario necessario in base al ruolo
	// Consume_rate = percentuale di consumo dell'item
	
	protected $equipment = array();
	
	// Effettua tutti i controlli relativi al move, sia quelli condivisi
	// con tutte le action che quelli peculiari del move
	// @input: array di parametri	
	// par[0]: character
	// par[1]: region
	// par[2]: moltiplicatore di coda
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function check( $par, &$message )
	{ 
		// Check classe madre (compreso il check_equipment)
		if ( ! parent::check( $par, $message ) )					
		{ return false; }
						
		$queuebonus = false;
		if ( Character_Model::get_premiumbonus( $par[0] -> id, 'workerpackage') !== false )						
			$queuebonus = true;
			
		// Controllo, se il moltiplicatore è > 1, il char deve avere il bonus
		if ( !in_array ( $par[2], array( 1, 2, 3 )) or ($par[2] > 1 and ! $queuebonus ) )
				{ $message = Kohana::lang("global.operation_not_allowed"); return false; }
		
		// Controllo che il char abbia l'energia e la sazieta' richiesti
		if (
			$par[0]->energy < (self::DELTA_ENERGY * $par[2])  or
			$par[0] -> glut < (self::DELTA_GLUT * $par[2]) )
		{ $message = Kohana::lang("charactions.notenoughenergyglut"); return false; }
			
		return true;
	}
	
	protected function append_action( $par, &$message )
	{
	
		$this -> character_id = Session::instance()->get('char_id');
		$this -> starttime = time();			
		$this -> status = "running";
		$this -> endtime = $this -> starttime + $this -> get_action_time( $par[0] ) * $par[2];
		$this -> param1 = $par[1] -> id;
		$this -> param3 = $par[2];
		$this -> save();
		$message = Kohana::lang("ca_cleanprison.cleanprison-ok");			
		
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
		
		$char = ORM::factory( 'character', $data->character_id );
		$region = ORM::factory( 'region', $data->param1 );
		
		// Consumo degli items/vestiti indossati
		//Item_Model::consume_equipment( $this->equipment, $char, $data->param3 );	
				
		$coins = self::MONEY * $data -> param3;
		$char -> modify_coins( $coins, 'cleanprisons' );
		
		// stats
		
		//$char -> modify_stat( 'itemproduction', +1, $item->cfgitem->id );					
		$char -> modify_energy( -self::DELTA_ENERGY * $data -> param3, false, 'cleanprison' );		
		$char -> modify_glut( -self::DELTA_GLUT * $data -> param3 );		
		$char -> save();	
		
		// evento per quest		
		$_par[0] = $data -> param3; // count
		GameEvent_Model::process_event( $char, 'cleanprison', $_par );	
		
		// events		
		Character_Event_Model::addrecord( 
			$char -> id, 
			'normal',  
			'__events.cleanprison_finished' .
			';' . $coins 
			);
		
	}
	
	protected function execute_action() {}
	
	public function cancel_action() { return true ; }
	
	// Questa funzione costruisce un messaggio da visualizzare 
	// in attesa che la azione sia completata.
	
	public function get_action_message( $type = 'long') 
	{
		
		$pending_action = $this->get_pending_action();
		$message = "";
		
		if ( $pending_action -> loaded )
		{
				
			$now = date("F d, Y H:i:s", time() );
			if ( $type == 'long' )		
				$message = '__regionview.cleanprisons_longmessage';				
			else
				$message = '__regionview.cleanprisons_shortmessage';
		
		}
		
		return $message;
	
	}
	
}
