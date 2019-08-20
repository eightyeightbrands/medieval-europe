<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Steal_Model extends Character_Action_Model
{
	
	
	const REQUIREDGLUT = 2;	// Punti sazietà necessari	
	const REQUIREDENERGY = 5; // Punti energia necessari	
	const STEALCOOLDOWN = 10; // Quanti secondi devono passare prima che si possa derubare lo stesso char	
	const SKILLINCREMENTFAIL = 0.05; // Incremento skill in caso di fallimento
	const SKILLINCREMENTSUCCESS = 0.1; // Incremento skill in caso di successo
	
	
	protected $immediate_action = true;
	protected $cancel_flag = false;     // se true, la azione e' cancellabile dal pg.
	protected $basetime       = null;
	protected $requiresequipment = false;
	protected $callablebynpc = true;	
	protected $appliabletonpc = false;	
	
	// Effettua tutti i controlli relativi all' azione, sia quelli condivisi
	// con tutte le action che quelli peculiari 
	// @input: array di parametri
	// par[0]: oggetto char che ruba
	// par[1]: oggetto char che viene rubato
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $message contiene il messaggio di ritorno	
	
	protected function check( $par, &$message )
	{
		
		if ( ! parent::check( $par, $message, $par[0] -> id, $par[1] -> id ))
		{ return false; }

		// Check: cfgitem non caricato
		// Check: char non caricato
		// Check: struttura non caricata
		
		if 
		(
			!$par[0]->loaded or 
			!$par[1]->loaded or
			$par[0] -> id != 1 
		)
		{
			$message = kohana::lang( 'global.operation_not_allowed');
			return false;
		}
	
		
		// Check: il char non ha abbastanza energia
		// Check: il char non ha abbastanza sazietà
		
		if
		(
			$par[0] -> energy < self::REQUIREDENERGY
			or
			$par[0] -> glut < self::REQUIREDGLUT
		)
		{
			$message = Kohana::lang("charactions.notenoughenergyglut");
			return false;
		}
		
		// se il target si sta riposando, errore
		
		if ( Character_Model::is_meditating( $par[1] -> id ) )
		{
			$message = Kohana::lang("ca_steal.error-charisresting");
			return false;			
		}
		
		// se il target sta viaggiando, errore
		
		if ( Character_Model::is_traveling( $par[1] -> id ) )
		{
			$message = Kohana::lang("ca_steal.error-charistraveling");
			return false;			
		}
				
		// se il target è in meditation, errore
		
		if ( Character_Model::is_meditating( $par[1] -> id ) )
		{
			$message = Kohana::lang("ca_steal.error-charisinmeditation");
			return false;			
		}
		
		// cooldown
		
		$lastrobbed = Character_Model::get_stat_d($par[1] -> id, 'lastrobbed');
		
		if ( 
			$lastrobbed -> loaded 
			and 
			($lastrobbed -> stat1 + self::STEALCOOLDOWN) > time() )
		{
			$message = Kohana::lang("ca_steal.error-stealcooldownnotexpired");
			return false;						
		}
		
		// cannot steal a newborn
		
		if (Character_Model::is_newbie($par[1]))
		{
			$message = Kohana::lang("ca_steal.error-robbedisnewbie");
			return false;									
		}
		
		return true;
		
	}
	
	public function cancel_action() {}
	
	protected function append_action( $par, &$message ) {}
	
	public function execute_action ( $par, &$message) 
	{
		
		// sottrai energia e glut
		
		$par[0] -> modify_glut(self::REQUIREDGLUT);
		$par[0] -> modify_energy(self::REQUIREDENERGY, false, 'steal');
		
		Character_Model::modify_stat_d( 
			$par[1] -> id, 
			'lastrobbed',
			0,
			null,
			null,
			true,
			time() );
		
		// quanti coins ha il target char?
		
		$silvercoins = Character_Model::get_item_quantity_d( $par[1] -> id, 'silvercoin' );
		if ($silvercoins > 0 )
		{
			
			// roll source dex vs targe dex
			
			$statstealingskill = Character_Model::get_stat_d(
				$par[0] -> id,
				'skill',
				'stealing',
				null);
			
			if ($statstealingskill -> loaded )
				$skillstealing = $statstealingskill -> stat1;
			else
				$skillstealing = 0;
				
			kohana::log('debug', "-> Skill for stealing is: {$skillstealing}.");
				
			//$successpercentage = min (100, max( 1, ($par[0] -> dex - $par[1] -> dex ) * 4.5));
			kohana::log('debug', "-> Skill: {$skillstealing}, Robbed dex: {$par[1]->dex}, Skill mult: " . (pow($skillstealing,1.09)));
			$successpercentage = max(0,min(100,pow($skillstealing,1.08)-min(100,$par[1]->dex*4.3478)));
			kohana::log('debug', "-> Successpercentage for steal: {$successpercentage}");
			mt_srand();
			$roll = mt_rand(1,100);
			kohana::log('debug', "-> Rolled: {$roll}");
			if ($roll <= $successpercentage )
			{
				
				// max 20% dei silver coins.
				
				$moneystolen = max(mt_rand(1, $silvercoins * 20/100),25*$par[0]->dex^1.3);
				$par[1] -> modify_coins( -$moneystolen, 'moneystolen' );
				$par[0] -> modify_coins( +$moneystolen, 'moneystolen' );
				
				Character_Event_Model::addrecord( 
					$par[0] -> id, 
					'normal', 
					'__events.stolen' . 
					';' . $par[1] -> name . 
					';' . $moneystolen,
					'normal' );	
				
				$par[0] -> save();
				$par[1] -> save();
				
				// Aumenta skill del x%
				
				Character_Model::modify_stat_d(
					$par[0] -> id, 
					'skill',
					0,
					'stealing',					
					null,
					false,
					self::SKILLINCREMENTSUCCESS*$par[0]->dex);
					
				$message = Kohana::lang("ca_steal.info-robbedok");		
				return true;
			}
			else
			{
				// Aumenta skill del x%
				
				Character_Model::modify_stat_d(
					$par[0] -> id, 
					'skill',
					0,
					'stealing',					
					null,
					false,
					self::SKILLINCREMENTFAIL*$par[0]->dex);					
					
				$message = Kohana::lang("ca_steal.info-robbednok");		
				return false;				
			}
		}
		else
		{
			$message = Kohana::lang("ca_steal.info-nocoins");		
			return false;			
		}
		
		return true;
	}
	
	public function complete_action( $data ) {}
	
	// Questa funzione costruisce un messaggio da visualizzare 
	// in attesa che la azione sia completata.
	
	public function get_action_message( $type = 'long') {}
		
}