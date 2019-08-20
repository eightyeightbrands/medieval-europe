<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Game_Diceelite_Model extends Character_Action_Model
{

	const MONEYREQUIRED = 1;
	const DICESNUMBER = 5;
	const STARTJACKPOT = 50;
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
		// controllo che il char abbia sufficienti dobloni
		
		if ( $par[0] -> get_item_quantity( 'doubloon' ) < self::MONEYREQUIRED )
		{ $message = kohana::lang('bonus.error-notenoughdoubloons'); return FALSE; }		
		
		// il char ha almeno 30 giorni di età?
		
		if ( $par[0] -> is_newbie($par[0])==true )
		{ $message = kohana::lang('charactions.error-tooyoung'); return FALSE; }		
						
		return true;
		
	}

	
	// nessun controllo particolare
	protected function append_action( $par, &$message )	{	}

	public function execute_action ( $par, &$message) 	{
		
		$db = Database::instance();			
		$roll = array(); 
		$sum = 0;
		$game = ORM::factory('game' ) -> where ( array( 'name' => 'diceelite' ) ) -> find();
		
		srand( time() ); 
		for ($i = 0; $i < self::DICESNUMBER ; $i++)
		{			
			
			$roll[$i] = rand(1,6);
			$sum += $roll[$i];
		}
		
		//////////////////////////////////////////
		// Determiniamo se il char ha vinto
		//////////////////////////////////////////
		
		if (
		( $roll[0] == 1 and $roll[1] == 1 and $roll[2] == 1 and $roll[3] == 1 and $roll[4] == 1 ) or
		( $roll[0] == 2 and $roll[1] == 2 and $roll[2] == 2 and $roll[3] == 2 and $roll[4] == 2 ) 
		or
		( $roll[0] == 3 and $roll[1] == 3 and $roll[2] == 3 and $roll[3] == 3 and $roll[4] == 3 ) 
		or
		( $roll[0] == 4 and $roll[1] == 4 and $roll[2] == 4 and $roll[3] == 4 and $roll[4] == 4 )or
		( $roll[0] == 5 and $roll[1] == 5 and $roll[2] == 5 and $roll[3] == 5 and $roll[4] == 5 )or
		( $roll[0] == 6 and $roll[1] == 6 and $roll[2] == 6 and $roll[3] == 6 and $roll[4] == 6 )
		)
		{
		
			$message = kohana::lang( 'ca_gamedice1.rolled_win', implode ( $roll, ", ") );			
			$par[0] -> modify_doubloons( $game -> param1 - self::MONEYREQUIRED, 'game_diceelite' );			
			
			// eventi!			
			
			Character_Event_Model::addrecord( 
			null, 
			'announcement', 
			'__events.diceelitewin1' .
			';' . $par[0] -> name . 
			';' . $game -> param1 .
			';__' . $par[1] -> region -> name ,			
			'evidence' ); 
			
			Character_Event_Model::addrecord( 			
			'normal', 
			$par[0] -> id, 
			'__events.diceelitewin2' .			
			';' . $game-> param1 .
			';__' . $par[1] -> region -> name ,			
			'evidence' ); 
			
			Character_PermanentEvent_Model::add( 						
			$par[0] -> id, 
			'__events.diceelitewin3' .			
			';' . $game -> param1 .
			';__' . $par[1] -> region -> name ); 
			
			$gamewinner = new Gamewinner_Model();
			$gamewinner -> game = 'diceelite';
			$gamewinner -> winner = $par[0] -> name;
			$gamewinner -> amount = $game -> param1;
			$gamewinner -> region_id = $par[1] -> region -> id ;
			$gamewinner -> windate = time();
			$gamewinner -> save();			
			
			$db -> query("update games set param1 = 0 where name = 'diceelite' "); 		
		}
		else
		{
			$message = kohana::lang( 'ca_gamedice1.rolled_loss', implode ( $roll, ", ") ); 
			
			// lancio il dato per determinare se i soldi vanno ad incrementare il jackpot o meno			
			
			$par[0] -> modify_doubloons( - self::MONEYREQUIRED, 'game_diceelite' );
			
			mt_srand( time() ); 
			$x = mt_rand(1, 10); 			
			
			if ( $x < 8 )
			{
				$db -> query("update games set param1 = param1 + " . self::MONEYREQUIRED . " where name = 'diceelite' "); 			
			}
			else
			{				
				Trace_Sink_Model::add( 'doubloons', $par[0] -> id, - self::MONEYREQUIRED, 'game_diceelite');
			}
							
			
		}
				
		return true;

	}
		
}
