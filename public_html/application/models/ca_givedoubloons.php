<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Givedoubloons_Model extends Character_Action_Model
{

	protected $immediate_action = true;

	// Effettua tutti i controlli relativi all' azione, sia quelli condivisi
	// con tutte le action che quelli peculiari 
	// @input: array di parametri
	// par[0]: oggetto character Recipient
	// par[1]: quantità
	// par[2]: categoria
	// par[3]: Reason
	// par[4]: Sender (name)
	// par[5]: oggetto character sender (o null)
	
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $message contiene il messaggio di ritorno	
	
	protected function check( $par, &$message )
	{ 				
		
		// l' utente deve esistere		
		
		if ( ! $par[0] -> loaded )
		{
			$message = kohana::lang( 'global.error-characterunknown');
			return false;
		}
						
		return true;
	}
	
	// nessun controllo particolare
	protected function append_action( $par, &$message )
	{}

	public function execute_action ( $par, &$message) 
	{
		
		$message = kohana::lang('charactions.senditem_ok');
		
		// Assegna o togli dobloni.
		
		$par[0] -> modify_doubloons( $par[1], $par[2], $par[3] );
		
		// Evento amministrator
		
		Character_Event_Model::addrecord( 
			1,
			'normal',  
			'__events.doubloons_sent'.
			';'.$par[1]. ';' . $par[0] -> name . ';' . $par[4] );		
		
		// se la causale è purchase aumenta la stat
		// e dai la quota al referrer solo se il char che INVIA non è un ADR.
				
		if ( $par[2] == 'purchase' )			
		{	
			
			kohana::log('info', '-> Updating boughtdoubloon stats...');			
			
			Character_Model::modify_stat_d( $par[0] -> id, 'boughtdoubloons', $par[1], null, null, false );
			
			kohana::log('info', '-> Updated boughtdoubloon stats.');
					 
			// Dai quota al referrer.
			
			kohana::log('info', '-> Getting Char referrer...');
			
			$referrer = User_referral_Model::get_referrer( $par[0] );
			$quantity = max(1, round($par[1]*5/100,0));
			
			if ( !is_null( $referrer ) )							
			{
				
				kohana::log( 'debug', $referrer -> name . ' is referrer?' . User_Model::has_role( $referrer -> user, 'doubloonreseller' ));
				// Se il Referrer è un ADR, non dare dobloni.				
				
				if ( User_Model::has_role( $referrer -> user, 'doubloonreseller' ) == true )
				{
					kohana::log('info', "-> NOT Giving doubloons to {$referrer -> name} because is an ADR.");										
				}
				else
				{
					kohana::log('info', "-> Giving doubloons to [{$referrer -> name}]");
					
					$referrer -> modify_doubloons( $quantity, 'referral', 'Referral' );
					
					// Event per amministratore
					
					Character_Event_Model::addrecord( 
					1,
					'normal',  
					'__events.doubloonsreferral_sent' .	';' . $quantity . ';' . $referrer -> name );
				
					// aggiorna referrer stats
				
					$userreferral = ORM::factory('user_referral')	
					-> where(
							array(
							'user_id' =>  $referrer -> user_id,
							'referred_id' => $par[0] -> user_id
							)
					) -> find();
					
					if ($userreferral -> loaded)	
					{
						kohana::log('info', "-> Updating referrer stats for user {$referrer -> user_id}");
						$userreferral -> doubloons += $quantity;
						$userreferral -> save();
					}
				}
  			
			}
			
			// informa il sender
			if (!is_null($par[5]))
				Character_Event_Model::addrecord(
					$par[5]->id, 
					'normal', 
					'__events.itemsent_event'.
					';' . $par[1].
					';__items.doubloon_name'.
					';' . $par[0]-> name .
					';' . Utility_Model::format_datetime( time() )
				);
				
			// informa il receiver

			if (!is_null($par[5]))
				Character_Event_Model::addrecord(
					$par[0]->id, 
					'normal', 
					'__events.itemreceived_event'.
					';' . $par[5]->name .
					';' . $par[1] . 
					';__items.doubloon_name'					
				);	
		}
		
		return true;
	}
}
