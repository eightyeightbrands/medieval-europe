<?php defined('SYSPATH') OR die('No direct access allowed.');
class CA_Imprison_Model extends Character_Action_Model
{
	
	protected $immediate_action = false;
	protected $cancel_flag = false;
	
	const ENERGY_FOR_30_MIN = 2;
	const GLUT_FOR_30_MIN = 1;
	const MAXIMPRISONMENTDAYS = 5;
	const IMPRISON_COOLDOWN = 86400; // 1 giorno
	
	/**
	* @param: array di parametri
	* par[0]: oggetto char giudice
	* par[1]: procedura di incriminazione
	* par[2]: ore di imprigionamento
	* par[3]: oggetto prigione scelta per l' imprigionamento
	* par[4]: oggetto struttura corte
	* @param: TRUE = azione disponibile, FALSE = azione non disponibile
	*          $messages contiene gli errori in caso di FALSE
	*/
	
	protected function check( $par, &$message )
	{ 
		
		if ( ! parent::check( $par, $message ) )					
		{ return false; }
		
		/////////////////////////////////////////////////////
		// controllo dati
		/////////////////////////////////////////////////////
		
		if ( !$par[0]->loaded or !$par[1]->loaded or !$par[3]->loaded)
		{ $message = kohana::lang('global.operation_not_allowed'); return FALSE; }
		
		/////////////////////////////////////////////////////
		// controlliamo che le ore sia positivo
		/////////////////////////////////////////////////////
		
		if ( $par[2] <= 0 or $par[2] > ( self::MAXIMPRISONMENTDAYS * 24 )   )
		{ $message = kohana::lang('ca_imprison.wrongperiod'); return FALSE; }				
		
		/////////////////////////////////////////////////////
		// controlliamo che la procedura sia ancora valida
		/////////////////////////////////////////////////////
		
		if ( $par[1] -> status != 'new' )
		{ $message = kohana::lang('ca_imprison.invalidcrimeprocedure'); return FALSE; }				
		
		/////////////////////////////////////////////////////
		// controllo che il char sia un giudice
		/////////////////////////////////////////////////////
		
		$role = $par[0] -> get_current_role();		
		if ( !is_null( $role ) and $role -> tag != 'judge' )		
		{ $message = kohana::lang('global.operation_not_allowed'); return FALSE; }

		/////////////////////////////////////////////////////
		// controllo che il personaggio da imprigionare:
		// sia nel regno
		/////////////////////////////////////////////////////
		
		$offenderlocation = ORM::factory('region',  $par[1] -> character -> position_id );
	  	if ( $par[4] -> region -> kingdom -> id != $offenderlocation -> kingdom -> id )
		{ $message = sprintf(kohana::lang('ca_imprison.notinsameregion', $par[1] -> character -> name)); return FALSE; }
		
		//////////////////////////////////////////////////////////////////////
		// Cooldown di 24 ore
		//////////////////////////////////////////////////////////////////////
		
		$stat = Character_Model::get_stat_d( $par[1] -> character_id, 'lastimprisonment', $par[0] -> region -> kingdom_id );
		
		//var_dump($stat);exit;
		if ( $stat -> loaded and time() - self::IMPRISON_COOLDOWN < $stat -> stat1 )
		{ $message = kohana::lang('ca_imprison.cooldownnotexpired', $par[1] -> character -> name); return FALSE; }				
		
		/////////////////////////////////////////////////////
		// Non si può imprigionare un Re o un Leader Religioso
		/////////////////////////////////////////////////////
		
		$offenderrole = $par[1] -> character -> get_current_role();		
		if ( 
			!is_null ( $offenderrole ) and 
			($offenderrole -> tag == 'king' or $offenderrole == 'church_level_1' ) 
			)
		{ 
			$message = sprintf(kohana::lang('ca_imprison.notenoughautority', $par[1] -> name)); return FALSE; 
		}
		
		//////////////////////////////////////////////////////////////////////
		// Il regno del giocatore è in guerra con il regno di chi tenta di
		// imprigionare?
		//////////////////////////////////////////////////////////////////////
		
		$judgekingdomrunningwars = Kingdom_Model::get_kingdomwars( $par[0] -> region -> kingdom_id, 'running');
		$criminalkingdomrunningwars = Kingdom_Model::get_kingdomwars(  $par[1] -> character -> region -> kingdom_id, 'running');
		
		if (
			count($judgekingdomrunningwars) > 0 
			and  
			count($criminalkingdomrunningwars) > 0 
			and 
			$judgekingdomrunningwars[0]['war'] -> id == $criminalkingdomrunningwars[0]['war'] -> id )
		{ $message = kohana::lang( 'charactions.error-characterisofenemykingdom'); return false;}					
		
		//////////////////////////////////////////////////////////////////////
		// Se il regno è attaccato non si può nè restrainare nè
		// imprigionare nel regno attaccato
		//////////////////////////////////////////////////////////////////////		
		
		$runningbattles = Kingdom_Model::get_runningbattles( $par[0] -> region -> kingdom_id );		
		
		foreach( (array) $runningbattles as $runningbattle )
		{ 			
			
			if ($runningbattle -> type == 'conquer_r'
				or
				$runningbattle -> type == 'raid'
				or
				$runningbattle -> type == 'revolt'
			)
			{
				// troviamo se il regno è attaccato
				$attackedregion = ORM::factory('region', $runningbattle -> dest_region_id);
				
				if ( $attackedregion -> kingdom_id == $par[0] -> region -> kingdom_id)
				{
					$message = kohana::lang('ca_imprison.error-kingdomisattacked'); 
					return FALSE; 
				}
			}
		}			
		
		/////////////////////////////////////////////////////
		// controllo che il prigioniero non stia già 
		// scontando qualche altra pena
		/////////////////////////////////////////////////////
		
		if ( Character_Model::is_imprisoned( $par[1] -> character -> id ) )
		{ $message = sprintf(kohana::lang('ca_imprison.alreadyimprisoned', $par[1]->name)); return FALSE; }
	
		/////////////////////////////////////////////////////
		// Controllo che il char non sia in uno stato che 
		// impedisca l' imprigionamento
		/////////////////////////////////////////////////////			
	
		if ( $par[1] -> character -> is_meditating( $par[1] -> character -> id ) )
		{ $message = sprintf(kohana::lang('ca_imprison.charismeditating', $par[1] -> character -> name)); return FALSE; }		
	
		if ( Character_Model::is_fighting( $par[1] -> id ) == true ) 		
		{ $message = sprintf(kohana::lang('ca_imprison.charisfighting', $par[1] -> character -> name)); return FALSE;}		
		
		return true;
	}

	
	protected function append_action( $par, &$message )
	{	
	
	
		//////////////////////////////////////////////////////
		// interrompo qualsiasi azione sta facendo il char
		//////////////////////////////////////////////////////
		
		Kohana::log('info', '-> Imprison: Canceling pending actions...');
		
		$pendingaction = Character_Action_Model::get_pending_action( $par[1] -> character_id ); 
		if ( !is_null( $pendingaction ) )		
		{
			$pendingaction -> cancel_pending_action( $par[1] -> character_id, true, $message);
			Kohana::log('info', '-> Imprison: return message: ' . $message);
		}
		
		// distanza tra la regione della corte e la prigione
		
		$distance = Region_Path_Model::compute_distance( 
			$par[4] -> region -> name, 
			$par[3] -> region -> name);				
			
		// 40 km/ora (cavallo), max 24 ore
		
		$time = min ( 24 * 3600, max( 15 * 60, $distance * ( 3600 / 40 ) ) );
		
		$starttime = time();
		$endtime = $starttime + $time;
		
		//var_dump( $distance . '-' . $time ); exit; 
		
		// Decrementa celle libere
		
		$par[3] -> attribute1 ++;
		$par[3] -> save();
		
		// azione di trasferimento alla prigione
		
		$a =  Character_Action_Model::factory("imprison");		
		$a -> starttime = $starttime;
		$a -> endtime = $endtime;
		$a -> status = 'running';
		$a -> param1 = $par[4] -> region -> id ;
		$a -> param2 = $par[3] -> region -> id ;				
		$a -> param3 = $par[2];
		$a -> param4 = $par[1] -> id;		
		$a -> param5 = $par[3] -> id;		
		$a -> character_id = $par[1] -> character -> id;
		$a -> save();
		
		$par[1] -> character -> modify_location(0);
		$par[1] -> character -> save();
	
		// cambia lo stato della procedura di incriminazione
		
		$par[1] -> status = 'executing' ;
		$par[1] -> prison_id = $par[3] -> id; 
		$par[1] -> imprisonment_hours_given = $par[2];
		$par[1] -> save();
		
		// eventi
		
		Character_Event_Model::addrecord(
			$par[1]-> character -> id, 
			'normal',  
			'__events.imprisoned' . ';' .
			'__' . $par[3] -> region -> name . ';' . 
			Utility_Model::format_datetime( time() + $par[2] * 3600 ) 
			);		
		
		Character_Event_Model::addrecord(		
			$par[0]-> id, 
			'normal',  
			'__events.imprisonedjudge' . ';' .
			$par[1] -> character -> name . ';' . 
			'__' . $par[3] -> region -> name . ';' . 
			Utility_Model::format_datetime( time() + $par[2] * 3600 ) 
			);						
		
		$message = kohana::lang('ca_imprison.imprison_ok', $par[1] -> character -> name, kohana::lang($par[3] -> region -> name) ); 
		
		return true;
	
	}

	public function complete_action( $data )
	{ 
	
		$char = ORM::factory('character', $data -> character_id ); 		
		
		if ( $char -> loaded )
		{
		
			// tolgo energia e glut in base alla distanza
			
			$sourceregion = ORM::factory('region', $data -> param1 );
			$targetregion = ORM::factory('region', $data -> param2 );
			
			$distance = Region_Path_Model::compute_distance( $sourceregion -> name, $targetregion -> name);			$glut = min( 50, round( self::GLUT_FOR_30_MIN * ( $distance / 30 ) ) ) ;
			$energy = min( 50, round( self::ENERGY_FOR_30_MIN * ( $distance / 30 ) ) );

			$char -> modify_energy ( - $energy, false, 'imprison' );
			$char -> modify_glut ( - $glut );		
			$char -> modify_location( $data -> param2 );		
			$char -> save();
			
			// modifico stat per settare che � in prigione.
			
			Character_Model::modify_stat_d(
				$char -> id,
				'servejailtime',
				0,
				null,
				null,
				true,
				time(),
				time()+ $data -> param3 * 3600
			);
								
			// Modifica procedura
			
			$crimeprocedure = ORM::factory('character_sentence', $data -> param4 );
			$crimeprocedure -> imprisonment_start = time();
			$crimeprocedure -> imprisonment_end = time()+ $data -> param3 * 3600;
			$crimeprocedure -> save(); 			
		
		}
	
	}
	
	// Questa funzione costruisce un messaggio da visualizzare 
	// in attesa che la azione sia completata.
	
	public function get_action_message( $type = 'long') 
	{
		$pending_action = $this -> get_pending_action();
		$message = "";				
		
		$startregion = ORM::factory('region', $pending_action -> param1);
		$destregion =  ORM::factory('region', $pending_action -> param2);		
		
		if ( $pending_action -> loaded )
		{
			if ( $type == 'long' )		
				$message = '__regionview.imprison_longmessage;__'. $startregion -> name . ';__' . $destregion -> name ;
			else
				$message = '__regionview.imprison_shortmessage';
		}
		
		return $message;
	
	}

	
}
