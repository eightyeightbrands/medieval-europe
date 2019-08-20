<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Retire_Model extends Character_Action_Model
{
	// Costanti	
	const DELTA_GLUT = 0;
	const DELTA_ENERGY = 0;	
	const COOLDOWN = 1296000; // 15 days
	protected $cancel_flag = true;
	protected $immediate_action = false;	
	protected $enabledifrestrained = true;
	protected $basetime       = 24;   // 1 day
	protected $attribute      = 'none';  // nessun attributo
	protected $appliedbonuses = array ( 'none' ); // bonuses da applicare
	
	public function __construct()
	{		
		parent::__construct();		
		$this->blocking_flag = true;			
		return $this;
	}
	
	// @input: 
	//	$par[0] = char, 
	//  $par[1] = giorni di ritiro
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function check( $par, &$message )
	{ 
	
		if ( ! parent::check( $par, $message ) )					
			return false;

		// controllo giorni
		if ( $par[1] <= 0)
			{ $message = kohana::lang('charactions.negative_quantity'); return FALSE; }
		
		// Il periodo di meditazione è funzione dell' età del personaggio.		
		// e del possesso di un titolo nobiliare.
		
		$maxperiod = min( round( pow($par[0] -> get_age(), 1.2)  / 10 ), 180 ) ;
		$noblebonus = Character_Model::get_premiumbonus( $par[0] -> id, 'basicpackage' );
		
		if ( $noblebonus !== false)
		{
			$daysleft = round ( ( $noblebonus['endtime'] - time() ) / ( 24 * 3600 ) );
			
			if ( $daysleft >= 30 )
				$maxperiod = min( round( pow($par[0] -> get_age(), 1.2) / 5 ), 180 ) ;
		}
		
		//var_dump( $maxperiod ); exit;
		
		if ( $par[1] > $maxperiod )
		{ $message = kohana::lang('ca_retire.maxdaysexceeded', $maxperiod ); return FALSE; }
				
		// Controllo cooldown
		
		$lastretiretime = Character_Model::get_stat_d( $par[0] -> id, 'lastretiretime' ); 
		
		if ( !is_null( $lastretiretime ) and time() - $lastretiretime -> value 	< self::COOLDOWN )
		{ $message = kohana::lang('ca_retire.cooldownnotexpired'); return FALSE; }	
		
		// Se è malato non può andare in meditazione
	
		if ( $par[0] -> is_sick() )
		{ $message = kohana::lang('charactions.charissick'); return FALSE; }	
			
		return true;
	}


	// Funzione per l'inserimento dell'azione nel DB.
	// Questa funzione appende solo una azione _non la esegue_
	// Al momento non si prevedono azioni istantanee.
	
	protected function append_action( $par, &$message )
	{
				
		$this -> character_id = $par[0];
		$this -> starttime = time();
		$this -> endtime = $this->starttime + $this -> get_action_time( $par[0] ) * $par[1];
		$this -> status = 'running';
		$this -> save();
				
		$par[0] -> modify_stat( 
			'lastretiretime', 
			time(), 
			null, 
			null, 
			true );
		
				
		Character_Event_Model::addrecord( $par[0] -> id, 
			'normal', '__events.meditating' . 			
			';' . Utility_Model::format_datetime( $this -> endtime ));			
				
		$message = kohana::lang('ca_retire.ok');
		
		return true;
	
	}

	// Esecuzione dell' azione. 
	// Questa funzione viene chiamata quando viene invocata una 
	// complete_expired_action e gestisce le azioni
	// inserite nella character_actions
	// - si caricano i parametri dal database
	// - si esegue l' azione in base ai parametri
	// - si mette l' azione in stato completed
	
	public function complete_action( $data ) {}

	
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
			$message = '__regionview.retire_longmessage';
			else
			$message = '__regionview.retire_shortmessage';
		}
		
		return $message;
	
	}
	
}
