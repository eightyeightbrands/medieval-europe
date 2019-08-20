<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Watcharea_Model extends Character_Action_Model
{	
	const DELTA_ENERGY_X_CHAR = 5;
	const DELTA_GLUT_X_CHAR = 3;
	
	protected $cancel_flag = false;
	protected $immediate_action = false;
	protected $region ;
	protected $presentchars;
	protected $role;
	
	protected $basetime       = 0.5;
	protected $attribute      = 'none';  // attributo forza
	protected $appliedbonuses = array ( 'none' ); // bonuses da applicare
	
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
		),
	);
	
	/**
    *
	* @param: par
	* par[0] = char che indaga	
	* par[1] = regione da visualizzare
	* @return: TRUE = azione disponibile, FALSE = azione non disponibile
	*
	*/
	
	protected function check( $par, &$message )
	{ 
		$message = "";
		
		if ( ! parent::check( $par, $message ) )					
			return false;
		
		// Si può indagare solo nella regione della struttura chiamante e 
		// nelle adiacenti
		
		if ( 
			$par[0] -> energy < intval(self::DELTA_ENERGY_X_CHAR) or 
			$par[0] -> glut < intval(self::DELTA_GLUT_X_CHAR) )  
		{ $message = kohana::lang('structures.not_enough_energy'); return FALSE; }
		
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
		
		Item_Model::consumeclothes( $par[0], $this -> action, $this -> basetime );
		
						
		$timeaction = $this -> basetime;
		$this -> character_id = $par[0]->id;
		$this -> starttime = time();
		$this -> status = "running";					
		$this -> endtime = $this -> starttime + $this -> get_action_time( $par[0] );	
		$this -> save();
		
		// evento di inizio indagine
		
		Character_Event_Model::addrecord( $par[0] -> id, 
			'normal',
			'__events.watcharea_start'			
			);
		
		$message = kohana::lang('ca_watcharea.info-watchok');
		
		return true;
		
	}

	// Esecuzione dell' azione. 
	// Questa funzione viene chiamata quando viene invocata una 
	// complete_expired_action e gestisce le azioni
	// inserite nella character_actions
	// - si caricano i parametri dal database
	// - si esegue l' azione in base ai parametri
	// - si mette l' azione in stato completed
	
	public function complete_action( $data ) {
	
		// togli energia e fame
		
		$character = ORM::factory('character', $data -> character_id);		
		$character -> modify_energy( - self::DELTA_ENERGY_X_CHAR, false, 'watcharea' );
		$character -> modify_glut( - self::DELTA_GLUT_X_CHAR );
		$character -> save();
		
		// Consumo degli items/vestiti obbligatori indossati
		Item_Model::consume_equipment( $this->equipment, $character );
	}
	
	protected function execute_action() {}
	
	public function cancel_action() { return true; }
	
	// Questa funzione costruisce un messaggio da visualizzare 
	// in attesa che la azione sia completata.
	
	public function get_action_message( $type = 'long') 
	{
		$pending_action = $this->get_pending_action();
		$message = "";	
		$target = ORM::factory('character', $pending_action -> param1 );		
		
		if ( $pending_action -> loaded )
		{
			if ( $type == 'long' )		
				$message = '__regionview.watchtower_longmessage;';
			else
				$message = '__regionview.watchtower_shortmessage';
		}
		return $message;
	
	}
	
}
