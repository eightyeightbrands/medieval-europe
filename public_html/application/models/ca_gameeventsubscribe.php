<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Gameeventsubscribe_Model extends Character_Action_Model
{
	// Azione di tipo immediato
	protected $immediate_action = true;

	
	// L'azione richiede che il personaggio indossi
	// un determinato equipaggiamento
	
	protected $requiresequipment = false;
	protected $cfggameevent = null;
	
	// check
	// ***********************************************************
	// Eseguie tutti i controlli prima dell'esecuzione o
	// dell'append della charaction
	//
	// @param   par[0]: oggetto char di chi nomina
	// @param   par[1]: id evento
	// @param   par[2]: metodo di pagamento
	// ***********************************************************
	
	protected function check( $par, &$message )
	{ 
		if ( ! parent::check( $par, $message ) )					
		{ return false; }				
		
	
		////////////////////////////////////////////
		// Check parametri manipolabili
		////////////////////////////////////////////
		
		if ( !$par[0]->loaded )
		{ $message = kohana::lang( 'global.operation_not_allowed'); return false; }

		$this -> cfggameevent = ORM::factory('cfggameevent', $par[1]);
		if ( !$this -> cfggameevent->loaded )
		{ $message = kohana::lang( 'global.operation_not_allowed'); return false; }

		if ( time() < $this -> cfggameevent -> subscriptionstartdate )
		{ $message = kohana::lang( 'ca_gameeventsubscribe.error-subscriptionsarenotopen'); return false; }
	
		if ( time() > $this -> cfggameevent -> subscriptionenddate )
		{ $message = kohana::lang( 'ca_gameeventsubscribe.error-subscriptionsareclosed'); return false; }	
	
		foreach ($this -> cfggameevent -> gameevent_subscription as $subscription) 
			if ($subscription -> character_id == $par[0] -> id )
				{ $message = kohana::lang( 'ca_gameeventsubscribe.error-charisalreadysubscribed'); return false; }
		
		if ( $par[2] == 'silvercoins' and ! $par[0] -> check_money( $this -> cfggameevent -> silvercoins  ) )
			{ $message = kohana::lang('global.error-notenoughsilvercoins'); return FALSE; }
		
		if ( $par[2] == 'doubloons' and $par[0] -> get_item_quantity( 'doubloon' ) < $this -> cfggameevent -> doubloons )
			{ $message = kohana::lang('global.error-notenoughdoubloons'); return FALSE; }
		
		return true;
	}

	// Azione immediata -> nessun controllo
	
	protected function append_action( $par, &$message ) {}

	public function execute_action ( $par, &$message) 	
	{			
	
		// remove coins or doubloons
		
		if ($par[2] == 'doubloons' )		
			$par[0] -> modify_doubloons( - $this -> cfggameevent -> doubloons, 'gameventsubscription' );
		
		if ($par[2] == 'silvercoins' )
			$par[0] -> modify_coins( - $this -> cfggameevent -> silvercoins, 'gameventsubscription' );
		
		// subscribe character_id
		
		$subscription = ORM::factory('gameevent_subscription');
		$subscription -> cfggameevent_id = $this -> cfggameevent -> id;
		$subscription -> character_id = $par[0] -> id;
		$subscription -> kingdom_id = $par[0] -> region -> kingdom_id;
		if ($par[2] == 'doubloons' )
			$subscription -> doubloons = $this -> cfggameevent -> doubloons;
		else
			$subscription -> silvercoins = $this -> cfggameevent -> silvercoins;
		$subscription -> timestamp = time();
		$subscription -> save();		
		
		// Assegna un activitypoint.
		
		Character_Model::modify_stat_d(
			$par[0] -> id,
			'activitypoints',
			1,
			null,
			null,
			false			
		);
			
		$message = kohana::lang('ca_gameeventsubscribe.info-subscribed');
		
		return true;
	}
}
