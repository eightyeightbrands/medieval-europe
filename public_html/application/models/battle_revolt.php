<?php defined('SYSPATH') OR die('No direct access allowed.');

class Battle_Revolt_Model extends Battle_Type_Model
{
	var $battletype = 'revolt';
	var $par = null;
	var $attackers = array();
	var $defenders = array();
	var $defeated = array();
	var $test = false;
	var $battlereport = '';
	var $bm = null;	
	var $be = null;
	var $attackingreion = null;
	var $attackedregion = null;
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
		$this -> attackingregion = ORM::factory('region', $this -> par[0] -> source_region_id ); 
		$this -> attackedregion = ORM::factory('region', $this -> par[0] -> dest_region_id ); 
		$this -> battlefield = $this -> attackedregion -> get_structure('battlefield'); 
		
		$this -> be = new Battle_Engine_Model();
		$this -> bm = $par[0];
		$this -> test = $test;		
		$this -> loadteams();						
		$this -> fight();						
		$battlereport = $this -> battlereport;		
		return;
	}	
	
	/** 
	* Combatte
	* 
	* @param none
	* @return none
	*/
	
	public function fight()
	{
		
		
		//kohana::log('debug', kohana::debug( $this -> attackers )); exit(); 
		$this -> battlereport[]['battleround'] = '__battle.revoltintroduction' . 
			';__' . $this -> attackingregion -> kingdom -> get_name()  . 
			';' .  Utility_Model::format_datetime( time() );		
		
		$this -> compute_bonusmalus( );
		
		$this -> be -> runfight( 
			$this -> attackers, 
			$this -> defenders, 
			'revolt', 
			$this -> defeated, 
			$winners, 
			$this -> battlereport, 
			$this -> fightstats,
			$this -> test );		
		
		$this -> handle_alive();
		$this -> handle_defeated( ); 
		$this -> do_aftermath( );		
	
	}
	
	/** 
	* Aftermath della battaglia
	* 
	* @param none
	* @return none
	*/
	
	function do_aftermath()
	{
		
		$db = Database::instance();
	
		///////////////////////////
		// stabilisco il vincitore
		///////////////////////////
		
		$attackerwins = $defenderwins = 0;
		$winners= 'none';		
		
		if ( count($this -> attackers) > count( $this -> defenders ) )
		{
			$attackerwins++;
			$winners='attackers';
		}
		elseif ( count($this -> defenders) > count( $this -> attackers ) )
		{
			$defenderwins++;
			$winners='defenders';
		}		
		
		if ( $winners == 'attackers' )
		{
				
			//////////////////////
			// Dethrone the King
			/////////////////////
			
			$this -> attackedregion -> kingdom -> dethrone_king();
			$newking = ORM::factory('character', $this -> bm -> source_character_id );
			
			$message='';					
			if ( $newking -> loaded and Character_Role_Model::check_eligibility( $newking, 'king', null, $message ) )					
			{
				$this -> attackedregion -> kingdom -> crown_king( $newking );									
				Character_Event_Model::addrecord( 
				null,
				'announcement', 
				'__events.revoltsuccessnewking'.';' . 					
					'__' . $this -> attackingregion -> kingdom -> get_name()  . ';' .					
					html::anchor( 'page/battlereport/' . $this ->  bm -> id, '[Report]'),
					'evidence');					
			}
			else
			{				
				Character_Event_Model::addrecord( 
				null,
				'announcement', 
				'__events.revoltsuccessnoking'.';' . 					
					'__' . $this -> attackingregion -> kingdom -> get_name()  . ';' .
					html::anchor( 'page/battlereport/' . $this ->  bm -> id, '[Report]'),
					'evidence');								
			}			
			
			

		}
		
		if  ($winners != 'attackers' )
		{
		
			Character_Event_Model::addrecord( 			
				null,
				'announcement', 
				'__events.revoltfailure'.';' . 
				'__' . $this -> attackingregion->kingdom -> get_name()  . ';' .
					html::anchor( 'page/battlereport/' . $this ->  bm -> id, '[Report]'),
				'evidence'
			);
		}
		
		//////////////////////////////////////////////////////////////
		// if the revolt leader didn't fight, lose 1 point charisma
		//////////////////////////////////////////////////////////////
	
		$leaderfought = false;

		foreach ( $this -> bm -> battle_participant as $battle_participant) 
			if ( $battle_participant -> character_id == $this -> bm -> source_character_id )
				$leaderfought = true;
				
		if ( $leaderfought == false )
		{
		
			$leader = ORM::factory('character', $this -> bm -> source_character_id );
			$leader -> car -= 1;
			
			Character_Event_Model::addrecord( 						
				$this -> bm -> source_character_id,
				'normal', 
				'__events.revoltleaderlostcharisma',
				'evidence');					
			$leader -> save();
		}

		
		// save data and finish
		$_tmp = $this -> be -> format_fightreport($this -> battlereport, 'internal' ); 

		$sql = "
			update battles set 
			status = 'completed', 
			timestamp = unix_timestamp(), 
			attacker_wins = " . $attackerwins . ",			
			defender_wins = " . $defenderwins . "
			where id = " . $this -> bm -> id ;
			
		Database::instance() -> query( $sql ); 	
		
		$sql = "update battle_reports set 			
			report1 = '" . addslashes($_tmp) . "'					
			where battle_id = " . $this -> bm -> id ;			
			
		Database::instance() -> query( $sql ); 		
	
		// schedule action for destroying the cdb
		
		$a = new Character_Action_Model();
		$a->character_id = $this -> bm -> source_character_id ;
		$a->param1 = $this -> battlefield -> id ;
		$a->blocking_flag = false;
		$a->action='destroycdb';
		$a->status='running';
		$a -> starttime = time() + kohana::config('medeur.battlefielddestroytime') * 3600;
		$a->endtime =  $a->starttime;
		$a->save();
		
	}
	
	/** 
	* Calcola i costi per la dichiarazione
	* ed il reclutamento
	* 
	* @param par vettore di parametri	
	* par0: regno che riceve la rivolta	
	* @return costo
	*/

	function compute_costs( $kingdom )
	{
	
		$kingdomregions = count( $kingdom -> regions ); 
		
		kohana::log('debug', 'attreg: ' . $kingdomregions ); 
		$cost = 100 * ($kingdomregions ); 
		
		return $cost;
	}
	
}
