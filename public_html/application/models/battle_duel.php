<?php defined('SYSPATH') OR die('No direct access allowed.');

class Battle_Duel_Model extends Battle_Type_Model
{
	protected $battletype = 'duel';
	protected $attackersnumber = 0;
	protected $defendersnumber = 0;
	var $battlefield = null;
	
	/** 
	* Esegue tutta la battaglia
	* 
	* @param par vettore di parametri	
	* par0: obj battle
	* @param test flag di test
	* @return 
	*/
	
	public function run( $par, &$battlereport, $test=false)
	{
		$this -> par = $par;			
		$this -> sourcechar = ORM::factory('character', $par[0] -> source_character_id );
		$this -> destchar = ORM::factory('character', $par[0] -> dest_character_id );
		$this -> attackingregion = ORM::factory('region', $this -> par[0] -> source_region_id ); 
		$this -> attackedregion = ORM::factory('region', $this -> par[0] -> dest_region_id ); 			
		$this -> be = new Battle_Engine_Model();
		$this -> bm = $par[0];
		$this -> test = $test;		
		$this -> loadteams();						
		$this -> fight();						
		$battlereport = $this -> battlereport;		
		return;
	}

	/** 
	* Carica i due team
	* 
	* @param par vettore di parametri
	* @param test flag di test
	* @return 
	*/
	
	public function loadteams( ) 
	{
		
		$attackers = array();
		$defenders = array();
		
		kohana::log('info', '-> *** Loadteams *** ' );
		
		// Carichiamo i char solo se sono nella regione prevista del duello 
		// e non stanno facendo nessuna azione bloccante
		
		$attackerpendingaction = $this -> sourcechar -> get_currentpendingaction( $this -> sourcechar -> id );
		$defenderpendingaction = $this -> destchar -> get_currentpendingaction( $this -> destchar -> id );
		
		if ( $attackerpendingaction == 'NOACTION' and $this -> sourcechar -> position_id == $this -> bm -> source_region_id )
		{
			$attacker = $this -> be -> loadcharbattlecopy( $this -> sourcechar -> id );	
			$attackers[$attacker['char']['key']] = $attacker;			
		}
		else
			kohana::log('info', '-> Char: ' . $this -> sourcechar -> name . ' not loaded, either is not in the duel region or it has a blocking action' );
		
		if ( $defenderpendingaction == 'NOACTION' and $this -> destchar -> position_id == $this -> bm -> source_region_id )
		{
			$defender = $this -> be -> loadcharbattlecopy( $this -> destchar -> id );	
			$defenders[$defender['char']['key']] = $defender;			
		}		
		else
			kohana::log('info', '-> Char: ' . $this -> destchar -> name . ' not loaded, either is not in the duel region or it has a blocking action' );
			
		$this -> attackers = $attackers;
		$this -> defenders = $defenders;
		
		$this -> attackersnumber = count($attackers);
		$this -> defendersnumber = count($defenders);
		
		
	}
	
	/** 
	* Combatte
	* 
	* @param none
	* @return none
	*/
	
	public function fight()
	{
		
		$this -> battlereport[]['battleround'] = '__battle.duelintroduction' . 
			';' . $this -> sourcechar -> name . 
			';' . $this -> destchar -> name . 
			';__' . $this -> attackedregion -> name . 
			';' . Utility_Model::format_datetime( time() );		

		$this -> compute_bonusmalus();		
		$this -> battlereport[]['newline'] = '';
		
		$this -> be -> runfight( 
			$this -> attackers, 
			$this -> defenders, 
			'duel', 
			$this -> defeated, 
			$winners, 
			$this -> battlereport,			
			$this -> fightstats,
			$this -> test );
		
		//kohana::log('debug', kohana::debug( $this -> battlereport)); exit(); 
		
		$this -> handle_alive( );
		$this -> handle_defeated( ); 
		$this -> do_aftermath( );		
	
	}
	
	function compute_bonusmalue()
	{
		return;
	}
	
	/** 
	* Aftermath della battaglia
	* 
	* @param none
	* @return none
	*/
	
	function do_aftermath() 
	{
		
		$attackerwins = $defenderwins = 0; 		
		
		// determino chi ha vinto e chi ha perso
		
		if ( count($this -> attackers) == 0 and count($this -> defenders) > 0 )
		{	
			$winner = $this -> destchar;
			$loser  = $this -> sourcechar;
		}
		
		if ( count($this -> attackers) > 0 and count($this -> defenders) == 0 )
		{	
			$winner = $this -> sourcechar;
			$loser  = $this -> destchar;
		}
		
		if ( count($this -> attackers) == 0 and count($this -> defenders) == 0 )
		{	
			$winner = null;
			$loser  = null;
		}
		
		// controllo chi c'era e no, gestione honor
		
		kohana::log('info', '-> Attackers: ' . $this -> attackersnumber . ' Defenders: ' . $this -> defendersnumber );
		
		// update stats
		
		$duellocation = ORM::factory('region', $this -> bm -> source_region_id );
					
		// caso: uno dei due o tutti e due non si sono presentati.
		
		if ( $this -> attackersnumber == 0 or $this -> defendersnumber == 0 )
		{
			if ( $this -> attackersnumber == 0 and $this -> defendersnumber > 0) 		
			{
				$this -> sourcechar -> modify_honorpoints( -1, 'duelabsence');
				$this -> destchar -> modify_honorpoints( +1, 'duelpresence');
				
				Character_Event_Model::addrecord(
				$this -> destchar -> id, 
				'normal',
				'__events.duelopponentdidntshow;' . $this -> sourcechar -> name,
				'evidence'				
				);
			
				Character_Event_Model::addrecord(	
				null, 
				'announcement', 
				'__events.duelfinishedtowncriernoshow;' . 
				$this -> sourcechar -> name . ';' . 
				$this -> destchar -> name . ';__' . 
				$duellocation -> name . ';' . 
				$this -> sourcechar -> name,			
				'duel');
			}
			
			if ( $this -> attackersnumber > 0 and $this -> defendersnumber == 0) 		
			{
				$this -> destchar -> modify_honorpoints( -1, 'duelabsence');
				$this -> sourcechar -> modify_honorpoints( +1, 'duelpresence');
					
				Character_Event_Model::addrecord(
				$this -> sourcechar -> id, 
				'normal',
				'__events.duelopponentdidntshow;' . $this -> destchar -> name,
				'evidence'				
				);
							
				Character_Event_Model::addrecord(	
				null, 
				'announcement', 
				'__events.duelfinishedtowncriernoshow;' . 
				$this -> sourcechar -> name . ';' . 
				$this -> destchar -> name . ';__' . 
				$duellocation -> name . ';' . 
				$this -> destchar -> name,				
				'duel');
			
			}
			
			if ( $this -> attackersnumber == 0 and $this -> defendersnumber == 0) 		
			{
				$this -> destchar -> modify_honorpoints( -1, 'duelabsence');
				$this -> sourcechar -> modify_honorpoints( -1, 'duelabsence');
				
				Character_Event_Model::addrecord(
				$this -> destchar -> id, 
				'normal',
				'__events.duelopponentdidntshow;' . $this -> sourcechar -> name,
				'evidence'				
				);
				
				Character_Event_Model::addrecord(
				$this -> sourcechar -> id, 
				'normal',
				'__events.duelopponentdidntshow;' . $this -> destchar -> name,
				'evidence'				
				);
				
				Character_Event_Model::addrecord(	
				null, 
				'announcement', 
				'__events.duelfinishedtowncrierbothnoshow;' . 
				$this -> sourcechar -> name . ';' . 
				$this -> destchar -> name . ';__' . 
				$duellocation -> name . ';',							
				'duel');
			}
		}
		else
		{
			
			// il duello si è svolto e c'è un perdente ed un vincitore.
			
			$this -> destchar -> modify_honorpoints( +1, 'duelpresence');
			$this -> sourcechar -> modify_honorpoints( +1, 'duelpresence');
			
			if ( !is_null( $winner ) and !is_null( $loser ) )
			{
				$winnerscore = 1;
				$loserscore = -1;
		
				$winner -> modify_stat(
					'duelscore',
					$winnerscore, 
					null,
					null,
					false,
					+1,
					+1,
					null,
					null,
					null,
					null
				);
		
				$loser -> modify_stat(
					'duelscore',
					$loserscore, 
					null,
					null,
					false,
					0,
					+1,
					null,
					null,
					null,
					null
				);
		
				// eventi
			
				Character_Event_Model::addrecord(
					$winner -> id, 
					'normal',
					'__events.duelwinner;' . $loser -> name,
					'evidence'				
				);
			
				Character_Event_Model::addrecord(
					$loser -> id, 
					'normal',
					'__events.duellooser;' . $winner -> name,
					'evidence'				
				);
			
				Character_Event_Model::addrecord(	
				null, 
				'announcement', 
				'__events.duelfinishedtowncrier;' . 
				$this -> sourcechar -> name . ';' . 
				$this -> destchar -> name . ';__' . 
				$duellocation -> name . ';' . 
				$winner -> name . ';' .
				html::anchor( 'page/battlereport/' . $this ->  bm -> id, '[Report]'),			
				'duel');
			}
			else
			{
				
				Character_Event_Model::addrecord(
					$this -> sourcechar -> id, 
					'normal',
					'__events.dueltie;' . $this -> destchar -> name,
					'evidence'				
				);
			
				Character_Event_Model::addrecord(
					$this -> destchar -> id, 
					'normal',
					'__events.dueltie;' . $this -> sourcechar -> name,
					'evidence'				
				);
			
				Character_Event_Model::addrecord(	
				null, 
				'announcement', 
				'__events.duelfinishedtietowncrier;' . 
				$this -> sourcechar -> name . ';' . 
				$this -> destchar -> name . ';__' . 
				$duellocation -> name . ';' . 
				html::anchor( 'page/battlereport/' . $this ->  bm -> id, '[Report]'),			
				'duel');			
			}
		
		}
		
		//////////////////////
		// save battle entry
		//////////////////////
		
		$this -> completebattle( 1, $attackerwins, $defenderwins );
		
	}
	
}
