<?php defined('SYSPATH') OR die('No direct access allowed.');

class Battle_Nativerevolt_Model extends Battle_Type_Model
{
	
	protected $battletype = 'nativerevolt';
	protected $attackersnumber = 0;
	protected $defendersnumber = 0;	
	var $par = null;
	var $attackers = array();
	var $defenders = array();
	var $defeated = array();
	var $test = false;
	var $localbattlereport = null;
	var $bm = null;	
	var $be = null;
	var $battlefield = null;
		
	
	/** 
	* Esegue tutta la battaglia
	* @param array $par vettori di parametri: par0: obj battle, par1: obj character action battleround
	* @param string $battlereport contiene il report della battaglia
	* @param boolean $test flag di test
	* @return none
	*/
	
	public function run( $par, &$battlereport, $test=false)
	{
					
		$this -> par = $par;			
		$this -> sourcechar = ORM::factory('character', $par[0] -> source_character_id );
		$this -> destchar = ORM::factory('character', $par[0] -> dest_character_id );
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
		$attackersnames = array();
		$defendersnames = array();
		
		// carico gli attaccanti (nativi)		
		
		$sql = "select id
		from battle_participants 
		where battle_id = " . $this -> par[0] . " 
		and faction = 'attack' 
		and status = 'alive' "  ; 
		
		$nativenumbers = Database::instance() -> query( $sql ) -> count();				
		kohana::log('debug', '-> Number of Loaded Natives: ' . $nativenumbers );
		$pointstodistribute = intval($this -> compute_native_stats ( $nativenumbers ) );
		kohana::log('debug', '-> Attribute Points to distribute: ' . $pointstodistribute ); 
		$cfgarmors = Configuration_Model::get_armorscfg();
		$cfgweapons = Configuration_Model::get_weaponscfg();
		
		/////////////////////////////////
		// Attaccanti creo nativi
		/////////////////////////////////
		
		for ( $i = 1; $i <= $nativenumbers; $i++)
		{
			//kohana::log('debug' , '-> *** Native: ' . $i . '***' );
			
			$points = $pointstodistribute;
			$native['char']['key'] = 'NPC-' . $i;
			$native['char']['type'] = 'npc';		
			$native['char']['npctag'] = 'native';		
			$native['char']['name'] = 'Native ' . $i; 
			$native['char']['health'] = 100;
			$native['char']['energy'] = 50;			
			$native['char']['transportedweight'] = 0;			
			$native['char']['ac'] = 0;			
			$native['char']['energymalus'] = 0;	
			$native['char']['stunnedround'] = 0;
			$native['char']['bleeddamage'] = 0;
			$native['char']['basetransportableweight'] = 0;
			$native['char']['encumbrance'] = 0;
			$native['char']['equippedweight'] = 0;
			$native['char']['fights'] = 0;
			$native['char']['fightmode'] = 'normal';
			$native['char']['faithlevel'] = 0;
			// Arma gli NPC
			
			$weapon = null;
			
			$r = mt_rand(0,6);
			switch ( $r )
			{
				case 0: $weapon = 'short_sword'; break;
				case 1: $weapon = 'knife'; break;
				case 2: $weapon = 'warhammer'; break;
				case 3: $weapon = 'greataxe'; break;
				case 4: $weapon = 'longsword'; break;
				case 5: $weapon = 'mace'; break;
				case 6: $weapon = 'scimitar'; break;
				default: break;
			}
			
			// add weapon
			
			$sql = "select c.* ,100 quality from cfgitems c where c.tag = '" . $weapon . "'" ; 		
			$resw = Database::instance() -> query( $sql ); 											
			$native['char']['weapons']['right_hand']['obj'] = $resw[0] ; 			
			
			// add shield
			
			$r = mt_rand(0,3);
			if ( $r == 2 )
			{							
				$sql = "select c.* ,100 quality from cfgitems c where c.tag = 'leather_armor_shield'";
				$resw = Database::instance() -> query( $sql ); 															
				$native['char']['armors']['left_hand']['leather_armor_shield']['obj'] = $resw[0];				
				$native['char']['equippedweight'] += $resw[0] -> weight;
			}
			
			// helmet
			$sql = "select c.* ,100 quality from cfgitems c where c.tag = 'leather_armor_head'";
			$resw = Database::instance() -> query( $sql ); 															
			$native['char']['armors']['head']['leather_armor_head']['obj'] = $resw[0];			
			$native['char']['equippedweight'] += $resw[0] -> weight;
			
			// add armor
			$sql = "select c.* ,100 quality from cfgitems c where c.tag = 'leather_armor_body'";
			$resw = Database::instance() -> query( $sql ); 																		
			$native['char']['armors']['torso']['leather_armor_body']['obj'] = $resw[0];			
			$native['char']['equippedweight'] += $resw[0] -> weight;
			
			$sql = "select c.* ,100 quality from cfgitems c where c.tag = 'leather_armor_legs'";
			$resw = Database::instance() -> query( $sql ); 																								
			$native['char']['armors']['legs']['leather_armor_legs']['obj'] = $resw[0];
			$native['char']['equippedweight'] += $resw[0] -> weight;

			// leather_armor_feet
			$sql = "select c.* ,100 quality from cfgitems c where c.tag = 'leather_armor_feet'";
			$resw = Database::instance() -> query( $sql ); 																								
			$native['char']['armors']['feet']['leather_armor_feet']['obj'] = $resw[0];
			$native['char']['equippedweight'] += $resw[0] -> weight;
						
			/////////////////////////////////
			// Distribuisci stats
			/////////////////////////////////
			
			$stats = Battle_Conquer_IR_Model::distribute_stats( $points ); 
			
			$native['char']['str'] = $stats['str'];
			$native['char']['dex'] = $stats['dex'];
			$native['char']['cost'] = $stats['cost'];
			$native['char']['intel'] = $stats['intel'];
			$native['char']['car'] = $stats['car'];
			
			$native['char']['basetransportableweight'] = 
				Character_Model::get_basetransportableweight( $native['char']['str'] );
						
			$native['char']['armorencumbrance'] = 
				Character_Model::get_armorencumbrance( $native['char']['basetransportableweight'], 
					$native['char']['equippedweight'] );
				
			//kohana::log('debug', kohana::debug($native));
			
			$attackers[$native['char']['key']] = $native;
			$attackersnames[] = $native['char']['name'];
		}
		
		/////////////////////////////////
		// Difensori
		/////////////////////////////////
		
		$sql = "
			select * from battle_participants bp, characters c 
			where bp.battle_id = " . $this -> par[0] . " 
			and   bp.character_id = c.id 
			and bp.faction = 'defend' 
			and bp.status = 'alive' "  ; 
		
		$participants = Database::instance() -> query( $sql );
		
		
		foreach ( $participants as $participant )
		{
			$defender = $this -> be -> loadcharbattlecopy( $participant -> character_id );
			$defenders[$defender['char']['key']] = $defender;
			$defendersnames[] = $defender['char']['name'];						
		}
		
		$this -> attackers = $attackers;
		$this -> defenders = $defenders;
		
		$attackersnamelist = implode( ", " , $attackersnames );
		$defendersnamelist = implode( ", " , $defendersnames );
						
		$this -> battlereport[]['participants'] = '__battle.participants'.
			';' . $attackersnamelist .
			';' . $defendersnamelist ;
		
		$this -> battlereport[]['newline'] = '';
		
		//kohana::log('debug', kohana::debug( $attackers )); 	exit();
		//kohana::log('debug', kohana::debug( $defenders )); 	exit();
		
	}
		
	/** 
	* Combatte
	* 
	* @param none
	* @return none
	*/
	
	public function fight()
	{
		
		$this -> battlereport[]['battleround'] = '__battle.nativerevoltintroduction' . 
			';__' . $this -> attackedregion -> name . 
			';' . Utility_Model::format_datetime( time() );		

		$this -> compute_bonusmalus();
		$this -> battlereport[]['newline'] = '';
		
		$this -> be -> runfight( 
			$this -> attackers, 
			$this -> defenders, 
			'nativerevolt',
			$this -> defeated, 
			$winners, 
			$this -> battlereport,			
			$this -> fightstats,
			$this -> test );
		
		
		//kohana::log('debug', kohana::debug( $this -> battlereport)); exit(); 
		
		kohana::log('info', '-> Native Revolt: Handling alive players.' );
		$this -> handle_alive( );
		
		kohana::log('info', '-> Native Revolt: Handling defeated players.' );
		
		$this -> handle_defeated( ); 
		
		kohana::log('info', '-> Native Revolt: Doing aftermath.' );		
		
		$this -> do_aftermath( );		
		
		kohana::log('info', '-> Native Revolt: End.' );
		
	}

	/** 
	* Battle Aftermath
	* @param none
	* @return none
	*/
	
	function do_aftermath() {
	
		$attackerwins = $defenderwins = 0;
		
		// stabiliamo chi ha vinto
		
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
		
		// se vincono i nativi, la regione diventa indipendente.
		
		kohana::log('info', "-> Winners: {$winners}");
		
		if ( $winners == 'attackers' )
		{
				
			$independentkingdom = ORM::factory('kingdom') -> 
				where ( 
					array( 'image' => 'kingdom-independent' )) -> find();
			
			kohana::log('info', '-> Moving region...');
			
			$this -> attackedregion -> move( $independentkingdom );
			
			kohana::log('info', '-> Finishing moving region.');
			
			// A resource exists? if yes, destroy it.
			
			$sql = "
				SELECT distinct s.id, st.type 
				FROM structure_types st, structures s, structure_resources sr
				WHERE s.structure_type_id = st.id
				AND s.id = sr.structure_id
				AND s.region_id = {$this -> attackedregion -> id}";
			
			$rset = Database::instance() -> query($sql) -> as_array();

			if (count($rset)> 0)
			{
				
				kohana::log('info', "-> Resource is existing, destroying it...");
				$structure = StructureFactory_Model::create( null, $rset[0] -> id );
				
								
				Character_Event_Model::addrecord( 
					null,
					'announcement', 
					'__events.nativerevoltsuccessdestroyedresource' . ';' .	
					'__' . $this -> attackedregion -> name . ';' .
					'__structures.' . $structure -> structure_type -> type . ';' .					
					html::anchor( 'page/battlereport/' . $this ->  bm -> id, '[Report]'),
					'evidence'
				);	
				
				$structure -> destroy();
				
				// Cache
				My_Cache_Model::delete('-cfg-regions-resources');
				
			}
			else
			{
				Character_Event_Model::addrecord( 
					null,
					'announcement', 
					'__events.nativerevoltsuccess'.
					';' .	
					'__' . $this -> attackedregion -> name . ';' .
					html::anchor( 'page/battlereport/' . $this ->  bm -> id, '[Report]'),
					'evidence'
				);	
			}
		
		}
		else
		{
			Character_Event_Model::addrecord( 
			null,
			'announcement', 
			'__events.nativerevoltfailure'.';' .	
				'__' . $this -> attackedregion -> name . ';' .
				html::anchor( 'page/battlereport/' . $this ->  bm -> id, '[Report]'),
				'evidence'
			);	
		}
		
		$this -> completebattle( 1, $attackerwins, $defenderwins );
		
	}
	
	/**
	* Calcola il numero di punti attributi da distribuire
	* @param $nativenumbers numero nativi
	* @return numero di punti da distribuire
	*/
	
	private function compute_native_stats( $nativenumbers )
	{
	
		//return max(50, 100 - $nativenumbers );	
		return mt_rand(60,90);
	
	}
	
	/**
	* Effettua il break down degli schierati in battaglia
	* @param battle_id id battaglia
	* @param faction attack|defense|null. Se null conta comunque il totale.
	* @return array
	*/

	public function get_joined_characters( $battle_id, $faction = null)
	{

		$data = array ( 
				'attack' => array( 
					'list' => array(), 
					'attackerorally' => 0, 
					'defenderorally' => 0, 
					'mercenary' => 0,
					'native' => 0,
					'other' => 0 ),
				'defend' => array( 
					'list' => array(), 
					'attackerorally' => 0, 
					'defenderorally' => 0, 
					'mercenary' => 0,
					'native' => 0,				
					'other' => 0 ),
			);
		
		$sql['attack'] = "
			select 
				bp.faction, 					
				bp.categorization,
				'Native_' name
			from battle_participants bp 
			where battle_id = $battle_id
			and   faction = 'attack' 				
			and   bp.status = 'alive' ";
		
		$sql['defend'] = "
			select 
				bp.faction, 					
				bp.categorization, 
				c.name 
			from battle_participants bp, characters c
			where battle_id = $battle_id
			and   faction = 'defend' 
			and   bp.character_id = c.id 
			and   bp.status = 'alive' ";
				
		if ( is_null ( $faction ) )
		{	
			$attack_soldiers = Database::instance() -> query ( $sql['attack'] ) -> as_array();
			$defend_soldiers = Database::instance() -> query ( $sql['defend'] ) -> as_array();
			$allsoldiers = array_merge( $attack_soldiers, $defend_soldiers );
			
		}				
		else
			$allsoldiers = Database::instance() -> query ( $sql[$faction] ) -> as_array();
		
		
		
		$i = 1;
		foreach ( $allsoldiers as $soldier )
		{			
			if ( $soldier -> faction == 'attack' )
				$data[$soldier -> faction]['list'][] = $soldier -> name . $i ;
			else
				$data[$soldier -> faction]['list'][] = Character_Model::create_publicprofilelink(null, $soldier -> name) ;
				
			$data[$soldier -> faction][$soldier -> categorization] += 1;	
			
			$i++;
		}
		
		//var_dump($data); exit;
		
		return $data;
	}
	
}
