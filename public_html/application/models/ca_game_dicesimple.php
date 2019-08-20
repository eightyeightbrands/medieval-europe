<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Game_Dicesimple_Model extends Character_Action_Model
{

	const MONEYREQUIRED = 5;
	const DICESNUMBER = 4;
	const STARTJACKPOT = 1000;
	
	protected $enabledifrestrained = true;	
	protected $immediate_action = true;

	// Effettua tutti i controlli relativi all' azione, sia quelli condivisi
	// con tutte le action che quelli peculiari 
	// @input: array di parametri
	// par[0]: oggetto char	
	// par[1]: oggetto struttura
	
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $message contiene il messaggio di ritorno	
	
	protected function check( $par, &$message )
	{ 
		
		if ( ! parent::check( $par, $message ) )					
		{ return false; }
		
		// il char ha i denari?
		if (! $par[0] -> check_money( self::MONEYREQUIRED ) )
		{ $message = kohana::lang('global.not_enough_money'); return FALSE; }
		
		// il char ha almeno 30 giorni di età?
		
		if ( $par[0] -> is_newbie($par[0])==true )
		{ $message = kohana::lang('charactions.error-tooyoung'); return FALSE; }	
		
		// al massimo, un click dopo ogni 5 secondi
		
		$res = Database::instance()->query( "select lastbettime from games where name = 'dicesimple'")-> as_array();
		if ( $res[0] -> lastbettime > time() - 5 )
		{ $message = kohana::lang('ca_gamedice1.clickedtoofast'); return FALSE; }
		
		
		return true;
		
	}

	
	// nessun controllo particolare
	protected function append_action( $par, &$message )	{	}

	public function execute_action ( $par, &$message) 	{
		
		$db = Database::instance();			
		$roll = array(); 
		$sum = 0;
		$game = ORM::factory('game' ) -> where ( array( 'name' => 'dicesimple' ) ) -> find();
		
		srand( time() ); 
		for ($i = 0; $i < self::DICESNUMBER ; $i++)
		{			
			
			$roll[$i] = rand(1,6);
			$sum += $roll[$i];
		}
		
		// settiamo il last bet click
		
		Database::instance() -> query ("update games set lastbettime = unix_timestamp() where name = 'dicesimple' ");
		
		//////////////////////////////////////////
		// Determiniamo se il char ha vinto
		//////////////////////////////////////////
		
		if ( $sum == 6 * self::DICESNUMBER )
		{
			
			$message = kohana::lang( 'ca_gamedice1.rolled_win', implode ( $roll, ", ") );
			
			$par[0] -> modify_coins( $game -> param1 - self::MONEYREQUIRED, 'game_dicesimple' );			
			
			// eventi!			
			
			Character_Event_Model::addrecord( 
			null, 
			'announcement', 
			'__events.dicesimplewin1' .
			';' . $par[0] -> name . 
			';' . $game -> param1 .
			';__' . $par[1] -> region -> name ,			
			'evidence' ); 
			
			Character_Event_Model::addrecord( 
			$par[0] -> id, 
			'normal', 
			'__events.dicesimplewin2' .			
			';' . $game -> param1 .
			';__' . $par[1] -> region -> name ,			
			'evidence' ); 
			
			Character_PermanentEvent_Model::add( 						
			$par[0] -> id, 
			'__events.dicesimplewin3' .			
			';' . $game -> param1 .
			';__' . $par[1] -> region -> name ); 
			
			$gamewinner = new Gamewinner_Model();
			$gamewinner -> game = 'dicesimple';
			$gamewinner -> winner = $par[0] -> name;
			$gamewinner -> amount = $game -> param1;
			$gamewinner -> region_id = $par[1] -> region -> id ;
			$gamewinner -> windate = time();
			$gamewinner -> save();
			
			$db -> query("update games set param1 = 0 
			where name = 'dicesimple' "); 		
		
		}
		else
		{
			$message = kohana::lang( 'ca_gamedice1.rolled_loss', implode ( $roll, ", ") ); 
			
			// lancio il dato per determinare se i soldi vanno ad incrementare il jackpot o meno
			// brucio il 50% dei soldi
			
			$par[0] -> modify_coins( - self::MONEYREQUIRED, 'game_dicesimple' );
			mt_srand( time() ); 
			$x = mt_rand(1, 10); 			
			
			if ( $x < 6 )
			{
				$db -> query("update games set param1 = param1 + " . self::MONEYREQUIRED . " where name = 'dicesimple' "); 
		
			}				
			
		}
				
		return true;

	}
		
}
