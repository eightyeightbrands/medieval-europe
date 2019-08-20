<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Glance_Model extends Character_Action_Model
{
	
	protected $cancel_flag = false;
	protected $immediate_action = true;
	protected $enabledifrestrained = true;
	const DELTA_GLUT = 2;
	const DELTA_ENERGY = 5;
	const COOLDOWN = 300;

	/**
	* Controlla le condizioni per l' azione
	* @param: array $par parametri per controllo
	*  par[0] = char che ispeziona
	*  par[1] = char ispezionato
	* @return: boolean esito
	*
	*/
	
	protected function check( $par, &$message )
	{ 
		
		// disabilitata.
		return false;
		
		$message = "";
		
		if ( ! parent::check( $par, $message ))					
			return false;
			
		// controllo dati
		
		if ( 
			! $par[0] -> loaded or 
			! $par[1] -> loaded 
		)
		{ $message = kohana::lang('global.operation_not_allowed'); return FALSE; }

		// i char devono essere nella stessa regione
		
		if ( $par[0] -> position_id != $par[1] -> position_id )
		{ $message = kohana::lang('charactions.error-notinsameregion'); return FALSE; }	

		// il char target non può essere in prigione
		if ( Character_Model::is_imprisoned( $par[1] -> id ) )
		{ $message = kohana::lang('charactions.error-charisimprisoned'); return FALSE; }		
			
	
		// energia, glut
				
		if (
			$par[0] -> energy < self::DELTA_ENERGY or
			$par[0] -> glut < self::DELTA_GLUT )
		{
			$message = Kohana::lang("charactions.notenoughenergyglut");
			return false;
		}
		
		// cooldown
		
		$cooldown = Character_Model::get_stat_d(
			$par[0] -> id,
			'lastglancedate'
		);
		
		if ($cooldown -> loaded == false )
			$timeuntilnextglance = time() + 0;
		else
			$timeuntilnextglance = time() + max( 0, self::COOLDOWN - ( time() - $cooldown -> stat1 ));
		//var_dump(time() - $cooldown -> stat1);exit;
		if ( time() < $timeuntilnextglance )
		{
			$message = Kohana::lang("ca_glance.error-cooldownnotexpired", Utility_Model::countdown($timeuntilnextglance));
			return false;
		}
		
		
		return true;
		
	}

	protected function append_action( $par, &$message )  {}
	
	public function complete_action( $data ) 	{	}
	
	protected function execute_action( $par, &$message ) 
	{	
		// toglie energia
		
		$par[0] -> modify_energy ( - self::DELTA_ENERGY, false, 'glance' );
		$par[0] -> modify_glut ( - self::DELTA_GLUT );
		$par[0] -> save();

		// imposta cooldown
		
		$par[0] -> modify_stat(			
			'lastglancedate',
			0,
			null,
			null,
			true,
			time()
		);
		
		// verifica se l' glance ha successo
		
		$chancetoglance = min(1,
			($par[0] -> get_attribute('dex')+$par[0] -> get_attribute('intel'))
			/
			(
				($par[1] -> get_attribute('dex')+$par[1] -> get_attribute('intel')) * 2
			)
		) * 100;
		
		kohana::log('debug', '-> Chance to Inspect succesfully: ' . $chancetoglance );
		
		// rolliamo per successo o failure
		
		mt_srand();
		$roll = mt_rand(1,100);
		kohana::log('debug', '-> Roll: ' . $roll );
		if ($roll > $chancetoglance)
		{
			$message = Kohana::lang("ca_glance.error-glancefailed");			
			return false;
		}
		
		// rolliamo per vedere se il target si accorge	
		
		$chancetobefound = max(0,
			(
				1 - ($par[0] -> get_attribute('dex')+$par[0] -> get_attribute('intel'))
				/
				(
					($par[1] -> get_attribute('dex')+$par[1] -> get_attribute('intel')) * 2
				)
			)
		) * 100;
		
		kohana::log('debug', '-> Chance to be found: ' . $chancetobefound );
		
		$roll = mt_rand(1,100);
		kohana::log('debug', '-> Roll: ' . $roll );
		if ($roll <= $chancetobefound)
		{
			// TODO: invio evento			
		
			Character_Event_Model::addrecord( 
				$par[1]->id,
				'normal', 
				'__events.foundcharacterglanceing'.
				';' . $par[0] -> name,
				'evidence'
				);
			
			Character_Event_Model::addrecord( 
				$par[0]->id,
				'normal', 
				'__events.foundbycharacterglanceed'.
				';' . $par[1] -> name,
				'evidence'
				);
			
			Utility_Model::send_notification(
				$par[1] -> user_id,
				'Notification from Medieval Europe',
				"You spotted {$par[0] -> name} glanceing your inventory." 
			);
			
			Utility_Model::send_notification(
				$par[0] -> user_id,
				'Notification from Medieval Europe',
				"You have been spotted by {$par[1] -> name} while glanceing his inventory." 
			);
			
		}
		
		
		
		return true; 	
	}
	
	public function cancel_action( ) {}
		
	
}
