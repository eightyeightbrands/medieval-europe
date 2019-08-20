<?php defined('SYSPATH') OR die('No direct access allowed.');

class Battle_PcvsNpc_Model extends Battle_Type_Model
{
	protected $battletype = 'pcvsnpc';
	protected $attackersnumber = 0;
	protected $defendersnumber = 0;
	var $battlefield = null;
	
	/** 
	* Esegue tutta la battaglia
	* 
	* @param par vettore di parametri	
	* par0: obj battle
	* @param test flag di test
	* @return boolean
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
		
		if ( $this -> loadteams() )
		{
			$this -> fight();						
			$battlereport = $this -> battlereport;		
		}
		else
			return false;
		
		return true;
		
	}

	/** 
	* Carica i due team
	* 
	* @param par vettore di parametri
	* @param test flag di test
	* @return boolean
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
			$attacker['fights'] = 0;
			$attackers[$attacker['char']['key']] = $attacker;			
		}
		else
		{
			kohana::log('info', '-> Char: ' . $this -> sourcechar -> name . ' not loaded, either is not in the duel region or it has a blocking action' );
			return false;
		}
		
		if ( $defenderpendingaction == 'NOACTION' and $this -> destchar -> position_id == $this -> bm -> source_region_id )
		{
			$defender = $this -> be -> loadcharbattlecopy( $this -> destchar -> id );	
			$defender['fights'] = 0;
			$defenders[$defender['char']['key']] = $defender;			
		}		
		else
		{
			kohana::log('info', '-> Char: ' . $this -> destchar -> name . ' not loaded, either is not in the duel region or it has a blocking action' );
			return false;
		}
			
		$this -> attackers = $attackers;
		$this -> defenders = $defenders;
		
		$this -> attackersnumber = count($attackers);
		$this -> defendersnumber = count($defenders);
		
		return true;
		
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
			$attackerwins = 0;
			$defenderwins = 1;
			
			Character_Event_Model::addrecord(
				$this -> sourcechar -> id, 
				'normal',
				'__events.pcvsnpclost;' .
				$this -> destchar -> name . ';' . 
				html::anchor( 'page/battlereport/' . $this ->  bm -> id, '[Report]'),
				'evidence'
			);
			
			// Stat
			
			Character_Model::modify_stat_d(
				$this -> sourcechar -> id,
				'killednpc',
				0,
				$this -> destchar -> npctag,
				null,
				false,
				+1
			);			
		}
		
		if ( count($this -> attackers) > 0 and count($this -> defenders) == 0 )
		{	
			$winner = $this -> sourcechar;
			$loser  = $this -> destchar;
			$attackerwins = 1;
			$defenderwins = 0;
			
			Character_Event_Model::addrecord(
				$this -> sourcechar -> id, 
				'normal',
				'__events.pcvsnpcwon;' .
				$this -> destchar -> name .
				';' . html::anchor( 'page/battlereport/' . $this ->  bm -> id, '[Report]'),
				'evidence'
			);
			
			// Stat
			
			Character_Model::modify_stat_d(
				$this -> sourcechar -> id,
				'killednpc',
				+1,
				$this -> destchar -> npctag,
				null,
				false,
				+1
			);
		}
		
		if ( count($this -> attackers) == 0 and count($this -> defenders) == 0 )
		{	
			$winner = null;
			$loser  = null;
			$attackerwins = 0;
			$defenderwins = 0;
			Character_Event_Model::addrecord(
				$this -> sourcechar -> id, 
				'normal',
				'__events.pcvsnpctie;' . 
				$this -> destchar -> name .
				';' . html::anchor( 'page/battlereport/' . $this ->  bm -> id, '[Report]'),
				'evidence'
			);
		}
		
		//////////////////////
		// save battle entry
		//////////////////////
		
		$this -> completebattle( 1, $attackerwins, $defenderwins );
		
	}
	
}