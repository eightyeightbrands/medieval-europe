<?php defined('SYSPATH') OR die('No direct access allowed.');

class Battle_Raid_Model extends Battle_Type_Model
{
	var $battletype = 'raid';
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
		$this -> battlereport[]['battleround'] = 
			'__battle.raidintroduction' . ';'  . '__' . $this -> attackingregion -> name . ';' . '__' . $this -> attackedregion -> name . ';' . Utility_Model::format_datetime( time() );		
		
		
		$this -> battlereport[]['newline'] = '';
		$this -> compute_bonusmalus();		
		$this -> be -> runfight( 
			$this -> attackers, 
			$this -> defenders, 
			'raid', 
			$this -> defeated, 
			$winners, 			
			$this -> battlereport, 
			$this -> fightstats,
			$this -> test );		
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
	
		$db = Database::instance();
		$totalraidedcoins = 0;
		
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
		
			$raidedcoins = 0;
			$totalraidedcoins=0;
			$totalraidedstructures= 0;	
			$totaldestroyedstructures= 0;	
			$totalraidedgoods = 0;
		
			// La percentuale di persone a cui 			
			// Strutture distrurre è direttamente proporzionale
			// A quanti attaccanti sono rimasti
			
			$aliveattackersleft = count( $this -> attackers );			
			$raidperc = intval(min( 50, intval( 15 + 1.25 * $aliveattackersleft )));
			$destroyperc = intval(min(50, intval( 1.1 * $aliveattackersleft )));
			
			// Calcoliamo il totale del peso trasportabile
			// di tutti gli attaccanti sopravvissuti
			
			$totalweight = 0;
			
			foreach ($this -> attackers as $attacker )
				$totalweight += $attacker['char']['obj'] -> get_transportableweight() ; 
						
			kohana::log('info', 
				'-> Attackers left: ' . $aliveattackersleft .
				'   Structure Raided Perc: ' .  $raidperc . 
				'   Structure Destroyed Perc: ' . $destroyperc . 
				'   Total Weight: ' . $totalweight );
						
			kohana::log( 'debug', '========== RAID START =============' ); 

			$this -> battlereport[]['raidresult'] = '__battle.separator' ;
			$this -> battlereport[]['raidresult'] = '__battle.raidparameters' . 
				';' . $aliveattackersleft . 
				';' . $raidperc . 
				';' . $destroyperc . 
				';' . ($totalweight / 1000); 
			
			$this -> battlereport[]['raidresult'] = '__battle.separator' ;

			/////////////////////////////////////////////
			// Comincia il looting
			/////////////////////////////////////////////			
			
			// select raidable structures
			
			$sql = "
			SELECT s.id  
			FROM structures s, structure_types st
			WHERE s.structure_type_id = st.id
			AND  
			(				
				st.subtype IN ('government', 'player', 'church', 'market') 				
			)
			AND s.region_id = {$this -> attackedregion -> id} 
			ORDER BY RAND();	  
			";
			
			// only for test: $sql = "SELECT 22308 AS id";
			
			$raidablestructures = Database::instance() -> query( $sql ) -> as_array();
			$totalraidablestructures = count($raidablestructures);
			$potentialraidablestructures = max(1,round(count($raidablestructures) * $raidperc/100, 0 ));
			$totalraidedstructures = 0;
			
			kohana::log('info', "-> Total Raidable Structures:{$totalraidablestructures}, Potential: {$potentialraidablestructures}" );
			
			foreach ($raidablestructures as $raidablestructure)			
			{
								
				if ( $totalraidedstructures >= $potentialraidablestructures )
					break;	
				
				$totalraidedstructures++;
				
				//var_dump($raidablestructure);exit;
				
				$structure = StructureFactory_Model::create( null, $raidablestructure -> id );				
				
				kohana::log( 'info', '===== Structure: ' . $structure -> structure_type -> type . 	' owner: ' . $structure -> character -> name . '=====' ); 
						
				///////////////////////////////////////////
				// Stabiliamo se la struttura corrente 
				// è razziata
				///////////////////////////////////////////
				
				foreach ( $structure -> item as $i )
				{							
				
					kohana::log( 'info', '-> Total transportable weight: ' . $totalweight  ); 
										
					$raidedquantity = max(1, intval($i -> quantity * $raidperc / 100));
					$itemtotalweight = $i -> get_totalweight( $raidedquantity );
					
					kohana::log( 'info', 
						'-> Trying to raid: {' . $raidedquantity . '] [' . $i -> cfgitem -> tag . ']'); 								
					kohana::log( 'info', 
						'-> Item total weight: ' . $itemtotalweight  ); 								
					
					if ($i -> cfgitem -> stealable == false )
					{
						kohana::log( 'info', 
						"-> Bypassing $i -> cfgitem -> tag because is not stealable.");	continue;
					}
					
					if ( $totalweight > $itemtotalweight )
					{
						
						$totalweight -= $itemtotalweight ;
						
						// Remove item. If it is a relic, move it directly into 
						// HQ of King Religion. If King is atheist, into his Royal Palace
						
						if ( !is_null( $this->bm->param1 ) and $i -> cfgitem -> tag == $this -> bm -> param1 )
						{
							
							$king = ORM::factory('character', $this -> bm -> source_character_id);
							
							if ($king -> church -> name == 'nochurch' )
								$targetstructure = $king -> region -> get_controllingroyalpalace();
							else
							{
								$info = Church_Model::get_info($king -> church_id);								
								$headquarter = current($info['structures']['religion_1']);
								reset($info['structures']['religion_1']);
								$targetstructure = ORM::factory('structure', $headquarter -> id );
							}
														
							$i -> removeitem( 'structure', $structure->id, $raidedquantity );
							$i -> additem( 'structure', $targetstructure -> id, $raidedquantity );	
							
							Character_Event_Model::addrecord(			
								null,
								'announcement', 
								'__events.raidedreliq'.';' .
									'__' . $this -> attackingregion->kingdom -> get_name()  . ';' .
									'__items.' . $this -> bm -> param1 . '_name' . ';'.
									'__' . $targetstructure -> structure_type -> name . ';' .
									'__' . $targetstructure -> region -> name,
									'evidence'
								);
							
						}
						else
						{
							$i -> removeitem( 'structure', $structure -> id, $raidedquantity );
							$i -> additem( 'structure', $this -> battlefield -> id, $raidedquantity );	
						}
						
						kohana::log('info', '==> Raided item: ' . $i -> cfgitem -> tag . ' quantity: ' . $raidedquantity );								
						
						if ( is_null( $structure -> character_id ) )
							$structureowner = '-';
						else
							$structureowner = $structure -> character -> name;
						
						$totalraidedgoods += $raidedquantity;
						
						// evento al proprietario della struttura
						
						if ( !is_null( $structure -> character_id ) )
						{
							
							Character_Event_Model::addrecord( 
							$structure->character_id,
							'normal', 
							'__events.structureraideditems'.';__' . $structure->get_structurearticle() . ';__' . $structure->structure_type->name . ';' . $i -> quantity  . ';' .
							'__' . $i -> cfgitem -> name 
							);					
						}
						
						// se la struttura è mercato, evento al seller
						
						if ( $structure -> structure_type -> type == 'market' )
						{
							
							Character_Event_Model::addrecord( 
							$i -> seller_id,
							'normal', 
							'__events.raidedmarketitem' .
								';' . $raidedquantity . 
								';__' . $i -> cfgitem -> name . 
								';__'   . $structure -> region -> name 
							);										
						}
						
						// evento struttura battlefield
							
						Structure_Event_Model::newadd( 
							$this -> battlefield -> id, 
							'__events.itemraided;' .  $raidedquantity . 
							';__' . $i -> cfgitem -> name .
							';__' . $structure -> structure_type -> name . 
							';'   . $structureowner );
						
					}
					else
						kohana::log('debug', 'Could not raid item: ' . $i -> cfgitem -> tag . ', finished carrying capacity or item too heavy.' ); 							
				}
				
				
			} // end loop on structures
				
			// Destroy player structures...
			
			$sql = "SELECT s.id 
			FROM structures s, structure_types st
			WHERE s.structure_type_id = st.id
			AND s.region_id = {$this -> attackedregion -> id}
			AND   st.subtype IN ('player') 					
			ORDER BY RAND();	  
			";
			
			$destroyablestructures = Database::instance() -> query( $sql ) -> as_array();
			$totaldestroyablestructures = count($destroyablestructures);
			$potentialdestroyablestructures = round(count($destroyablestructures) * $destroyperc/100, 0 );
			$totaldestroyedstrucures = 0;
			
			kohana::log('debug', "-> Total Destroyable Structures:{$totaldestroyablestructures}, Potential: {$potentialdestroyablestructures}" );
			
			foreach ($destroyablestructures as $destroyablestructure)			
			{	
				
				
				
				if ($totaldestroyedstructures >= $potentialdestroyablestructures )
					break;
				
				$totaldestroyedstructures ++;
								
				$structure = StructureFactory_Model::create( null, $destroyablestructure -> id );

			 
				Character_Event_Model::addrecord( 
					$structure -> character_id,
					'normal', 
					'__events.structuredestroyed'.';__' . $structure->get_structurearticle() . ';__' . $structure->structure_type->name, 'evidence' );
						
				kohana::log('debug', '==> Destroyed structure: ' . 
					$structure->structure_type->name . ' owner: ' . $structure -> character -> name );
			
				$structure -> destroy(); 
											
				
			
			}	
			
			//////////////////////////////////////////
			// Razzia dei presenti in regione
			// Viene razziato chiunque si trovi 
			// nella regione e che non abbia partecipato
			// alla battaglia			
			//////////////////////////////////////////
			
			kohana::log('debug', 'Raiding characters...' );
			
			$sql = 
			"select c.id, c.name 
			 from characters c
			 where	c.position_id = " . $this -> attackedregion -> id . "
			 and c.id not in ( select character_id from battle_participants where battle_id = " . $this-> bm -> id . " and faction = 'attack' )";
			
			$raidablechars = $db -> query( $sql );
			$totalraidablechars = count($raidablechars);
			$potentialraidablechars = round( count($raidablechars) * $raidperc/100, 0 );
			$totalraidedchars = 0;
			
			kohana::log('debug', "-> Raided Chars Total:{$totalraidablechars}, Potential: {$potentialraidablechars}" );
			
			foreach ( $raidablechars as $c )
			{
				
				

				if ( $totalraidedchars >= $potentialraidablechars )
					break;
				
				$totalraidedchars++;
				
				$character = ORM::factory('character', $c->id );					
				
				// raiding items...
				
				foreach ( $character -> item as $i )
				{					
			
					if ( $i -> cfgitem -> tag == 'silvercoin' )
					{
						
						$raidedquantity = max(1, intval($i -> quantity * $raidperc / 100));
						$itemtotalweight = $i -> get_totalweight( $raidedquantity );

						kohana::log( 'debug', 'Raiding: ' . $raidedquantity . ' ' . $i -> cfgitem -> tag ); 								
						kohana::log( 'debug', 'Item total weight: ' . $itemtotalweight  ); 			
						
						// only if itemsweight that can be raided is less or equal than the total weight that can be raided, 
						// raid.
				
						if ( $totalweight >= $itemtotalweight )
						{
							
							// decrease total items that can be raided weight
							$totalweight -= $itemtotalweight ;								
						
							kohana::log( 'debug', 'Raided: ' . $raidedquantity . ' coins from  ' . $character->name);			
							
							$i -> removeitem( 'character', $character->id, $raidedquantity );
							$i -> additem( 'structure', $this -> battlefield -> id, $raidedquantity );	
																
							// evento struttura battlefield
							
							$e = new Structure_Event_Model();																
							$e -> add( 
								$this -> battlefield -> id, 									
								'__events.itemmugged;' . $raidedquantity . 
								';__' . $i -> cfgitem -> name  .
								';' . $character -> name ); 									
						
							// evento al razziato
							
							Character_Event_Model::addrecord( 
							$c -> id,
							'normal', 
							'__events.charraided' . 
							';__'. $this -> attackedregion -> name .
							';'. $raidedquantity
							);
					
							$totalraidedcoins += $raidedquantity;
						}
							
					}
					
					
				}
				
				
			} // end loop raided chars.
			
			$this -> battlereport[]['raidresult'] = '__battle.raidresult' . 			
				';' . $totalraidedchars . '/' . $potentialraidablechars . '/' . $totalraidablechars .
				';' . $totalraidedgoods . 
				';' . $totalraidedstructures  . '/' . $potentialraidablestructures . '/' . $totalraidablestructures .				
				';' . $totaldestroyedstructures . '/' . $potentialdestroyablestructures .  '/' . $totaldestroyedstructures . 
				';' . $totalraidedcoins ;				
					
			//////////////////////
			// crier's event
			/////////////////////
				
			Character_Event_Model::addrecord(			
			null,
			'announcement', 
			'__events.battleendedwinner'.';' .
				'__battle.' . $this -> bm -> type . ';' .
				'__' . $this -> attackingregion->kingdom -> get_name()  . ';' .
				'__' . $this -> attackedregion->kingdom -> get_name()  . ';' .
				'__' . $this -> attackingregion->kingdom -> get_name()  . ';' .
				html::anchor( 'page/battlereport/' . $this ->  bm -> id, '[Report]')
			);
	
		}
		
		if  ($winners == 'defenders' )
		{
		
			Character_Event_Model::addrecord( 
			null,
			'announcement', 
			'__events.battleendedwinner'.';' . 
				'__battle.' . $this -> bm -> type . ';' .
				'__' . $this -> attackedregion->kingdom -> get_name()  . ';' .
				'__' . $this -> attackingregion->kingdom -> get_name()  . ';' .
				'__' . $this -> attackedregion->kingdom -> get_name()  . ';' .
				html::anchor( 'page/battlereport/' . $this ->  bm -> id, '[Report]')
			);
		}
		
		if ( $winners =='none' )
		{
			Character_Event_Model::addrecord( 
			null,
			'announcement', 
			'__events.battleendedtie'.';' . 
			'__battle.' . $this -> bm -> type . ';' .
			'__'. $this -> attackingregion->kingdom -> get_name()  . ';' .
			'__'. $this -> attackedregion->kingdom -> get_name()  . ';' .
			html::anchor( 'page/battlereport/' . $this -> bm -> id , '[Report]')
			);			
		}
		
		//////////////////////
		// save battle entry
		//////////////////////
			
		$this -> completebattle( 1, $attackerwins, $defenderwins, $totalraidedcoins );
		
		
		
	}
	
}
