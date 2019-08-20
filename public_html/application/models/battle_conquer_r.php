<?php defined('SYSPATH') OR die('No direct access allowed.');

class Battle_Conquer_R_Model extends Battle_Type_Model
{
	var $battletype = 'conquer_r';
	var $par = null;
	var $attackers = array();
	var $defenders = array();
	var $defeated = array();	
	var $test = false;
	var $bm = null;	
	var $be = null;
	var $attackingregion = null;
	var $attackedregion = null;
	var $battlefield = null;
	
	/** 
	* Esegue tutta la battaglia
	* 
	* @param par vettore di parametri	
	* par0: obj battle
	* par1: obj character action battleround
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
		
		$this -> battlereport[]['battleround'] = '__battle.conquerrintroduction' . 
			';'  . '__' . $this -> attackingregion -> name . 
			';' .  '__' . $this -> attackedregion -> name . 
			';' .  $this -> par[1] -> param1 . 
			';' . Utility_Model::format_datetime( time() );		
		$this -> compute_bonusmalus();
		$this -> battlereport[]['newline'] = '';		
		$this -> be -> runfight( 
			$this -> attackers, 
			$this -> defenders, 
			'conquer_r', 
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
			
	/** 
	* Aftermath della battaglia
	* 
	* @param none
	* @return none
	*/
	
	function do_aftermath()
	{
	
		
		$currentround = $this -> par[1] -> param1; 
		
		///////////////////////////
		// stabilisco il vincitore
		// per questo round
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
		
		$this -> bm -> attacker_wins += $attackerwins;
		$this -> bm -> defender_wins += $defenderwins;
		
		// Se la regione attaccata non ha il castello, 1 round.
		// Se la regione attaccata ha il castello, 3 round
		// Se la regione attaccata ha il p. reale, 5 round
		
		$castle = $this -> attackedregion -> get_structure('castle');
		$royalpalace = $this -> attackedregion -> get_structure('royalpalace');
		
		$rounds = 1;
		
		if ( !is_null($castle))
			$rounds = 3;
		
		if ( !is_null($royalpalace))
			$rounds = 5;
		
		kohana::log('info', "This battle will be fought on $rounds rounds." );
		kohana::log('info', "The round n. " . $currentround . ' has been fought.' ); 
			
		if ( $currentround >= $rounds )
		{
			// Stabilisce il vincitore di tutta la battaglia 
			
			$battlewinners = 'none'; 			
			
			kohana::log('info', "Attacker wins: " . $this -> bm -> attacker_wins ); 
			kohana::log('info', "Defender wins: " . $this -> bm -> defender_wins ); 
			
			if ( $this -> bm -> attacker_wins > $this -> bm -> defender_wins )
				$battlewinners = 'attackers' ;
			if ( $this -> bm -> attacker_wins < $this -> bm -> defender_wins )
				$battlewinners = 'defenders' ;
			
			kohana::log('info', "-> Winner of the battle: " . $battlewinners );
			

			if ( $battlewinners == 'attackers' ) 
			{
			
				////////////////////////////////////////////////////
				// Caso: la regione non ha nè castello nè p. reale
				////////////////////////////////////////////////////				
							
				if ( is_null( $castle ) and is_null( $royalpalace ) )
				{
					kohana::log('info', "-> Case: no castle, no royal palace." );

					// Annuncio prima che cambino le relazioni.		
					
					Character_Event_Model::addrecord( 
					null,
					'announcement', 
					'__events.conquerrsuccess'.';' .	
						'__' . $this -> attackingregion -> kingdom -> get_name()  . ';' .
						'__' . $this -> attackedregion -> name . ';' .
						'__' . $this -> attackedregion -> kingdom -> get_name()  . ';' .					
						html::anchor( 'page/battlereport/' . $this ->  bm -> id, '[Report]'),
						'evidence'
						);					
					
					kohana::log('info', "Event written, moving region." );					
					$this -> attackedregion -> move( $this -> attackingregion -> kingdom );
					
					
				}
				
				////////////////////////////////////////////////////
				// Caso: la regione ha solo il castello
				////////////////////////////////////////////////////				
				
				
				elseif ( !is_null( $castle ) and is_null( $royalpalace ) ) 
				{
				
					// Annuncio prima che cambino le relazioni.		
					
					kohana::log('info', "-> Case: Castle, no royal palace." );
					Character_Event_Model::addrecord( 
						null,
						'announcement', 
						'__events.conquerrsuccess'.';' .	
						'__' . $this -> attackingregion -> kingdom -> get_name()  . ';' .
						'__' . $this -> attackedregion -> name . ';' .
						'__' . $this -> attackedregion -> kingdom -> get_name()  . ';' .					
						html::anchor( 'page/battlereport/' . $this ->  bm -> id, '[Report]'),
						'evidence'
					);					
					
					$c = new ST_Castle_1_Model();					
					$controlledregions = $c -> get_controlled_regions( $castle -> id, $castle -> region -> id ); 
					foreach	($controlledregions as $controlledregion )
						$controlledregion -> move( $this -> attackingregion -> kingdom ); 					
					
				}
				
				////////////////////////////////////////////////////
				// Caso: la regione ha il palazzo reale ed il 
				// castello (Capitale)
				////////////////////////////////////////////////////			
				
				elseif ( !is_null( $castle ) and !is_null( $royalpalace )) 
				{
					
					kohana::log('info', "Case: Castle, Royal palace." );
					
					// Detronizza il corrente Re, e metti il sostituto 
					// se ha le caratteristiche corrette, è vivo ecc.
					
					kohana::log('info', "Dethroning King..." );					
					$this -> attackedregion -> kingdom -> dethrone_king();
					kohana::log('info', "Dethroned." );					
					
					$newking = ORM::factory('character', $this -> bm -> kingcandidate );
					kohana::log('info', '-> Trying to chrown : ' . $newking -> name );
					kohana::log('info', '-> Position of tobeking: ' . $newking -> position_id . ', region: ' . $this -> attackedregion -> id );
					
					$message='';
					
					if ( 
						$newking -> loaded and 						
						$newking -> position_id == $this -> attackedregion -> id and 
						Character_Role_Model::check_eligibility( $newking, 'king', null, $message ) )					
					{
						$this -> attackedregion -> kingdom -> crown_king( $newking );									
						Character_Event_Model::addrecord( 
							null,
							'announcement', 
							'__events.conquerrsuccessnewking'.';' . 					
							'__' . $this -> attackingregion -> kingdom -> get_name()  . ';' .
							'__' . $this -> attackedregion -> kingdom -> get_name()  . ';' .					
							$newking -> name . ';' .
							html::anchor( 'page/battlereport/' . $this ->  bm -> id, '[Report]'),
							'evidence'
						);					
					}
					else
					{
						Character_Event_Model::addrecord( 
							null,
							'announcement', 
							'__events.conquerrsuccessnoking'.';' . 					
								'__' . $this -> attackingregion -> kingdom -> get_name() . ';' .
								'__' . $this -> attackedregion -> kingdom -> get_name() . ';' .					
								html::anchor( 'page/battlereport/' . $this ->  bm -> id, '[Report]'),
								'evidence');										
					}
					
				}
				else				
					kohana::log('error', "Conquer Region: business case not expected!" ); 
				
			}
			else if ( $battlewinners == 'defenders' or $battlewinners == 'none' ) 
			{
			
				Character_Event_Model::addrecord( 
					null,
					'announcement', 
					'__events.conquerrfailure'.';' . 
						'__' . $this -> attackingregion -> kingdom -> get_name() . ';' .
						'__' . $this -> attackedregion -> kingdom-> get_name() . ';' .					
						html::anchor( 'page/battlereport/' . $this ->  bm -> id, '[Report]')
				);

			}
			
			//////////////////////
			// save battle entry
			//////////////////////
			
			$this -> completebattle( $currentround, $attackerwins, $defenderwins );
			
		}
		else
		{
				
			//////////////////////
			// save battle entry
			//////////////////////
			
			$_tmp = $this -> be -> format_fightreport( $this -> battlereport, 'internal' ); 	
			
			$sql = "update battles set
			attacker_wins = " . $this -> bm -> attacker_wins . ",			
			defender_wins = " . $this -> bm -> defender_wins . "			
			where id = " . $this -> bm -> id ;			
			Database::instance() -> query( $sql ); 	
			
			$sql = "update battle_reports set 			
			report" . $currentround . " = '" . addslashes($_tmp) . "'
			where battle_id = " . $this -> bm -> id ;			
			Database::instance() -> query( $sql ); 				
			
			// Aggiungi un evento @ town crier
		
			$e = Character_Event_Model::addrecord( 
			null,
			'announcement', 
			'__events.roundended'.';' . 
			'__' . $this -> attackingregion -> kingdom -> get_name()  . ';' .
			'__' . $this -> attackedregion -> kingdom -> get_name()  . ';' .
			html::anchor( 'page/battlereport/' . $this -> bm -> id , '[Report]')
			);

			// schedula un altro round
						
			kohana::log('debug', 'Scheduling another round.' ); 
			
			$a = new Character_Action_Model();
			$a->character_id = $this -> par[0] -> source_character_id ;
			$a->param1 = $currentround + 1; 
			$a->param2 = $this -> par[1] -> param2;
			$a->blocking_flag = false;
			$a->action = 'battleround';
			$a->status = 'running';
			$a->starttime = $this -> par[1] -> starttime + (kohana::config('medeur.nextroundtime') * 3600);
			$a->endtime =  $a->starttime;
			$a->save();
		
		}
	
	}	
}
