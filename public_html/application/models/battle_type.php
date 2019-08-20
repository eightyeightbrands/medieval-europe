<?php defined('SYSPATH') OR die('No direct access allowed.');

class Battle_Type_Model
{	
	protected $par = null;
	protected $attackers = array();
	protected $defenders = array();
	protected $defeated = array();
	protected $test = false;
	protected $battlereport = '';
	protected $attackingregion = null;
	protected $attackedregion = null;
	protected $bm = null;	
	protected $sourcechar = null;
	protected $destchar = null;
	protected $battletype = '';
	protected $fightstats = array(
		'totalround' => 0,
		'criticalhits' => 0,
	);
	
	/**
	* Torna una lista di chi partecipa alla battaglia con categorizzazione
	* @param int $battle_id ID Battaglia
	* @param string $faction attack|defense|null
	* @return array character name list
	*/

	public function get_joined_characters( $battle_id, $faction = null)
	{

		$a = array ( 
				'attack' => array( 
					'list' => array(), 
					'defenderorally' => 0,
					'attackerorally' => 0, 
					'mercenary' => 0,
					'native' => 0,
					'other' => 0 ),
				'defend' => array( 
					'list' => array(), 
					'defenderorally' => 0,
					'attackerorally' => 0, 
					'mercenary' => 0,
					'native' => 0,				
					'other' => 0 ),
			);		
		
		if ( is_null ( $faction ) )
			$sql = "select bp.faction, c.name, bp.categorization 	
				from battle_participants bp, characters c
				where battle_id = $battle_id
				and   bp.character_id = c.id 
				and   bp.status = 'alive'";
		else
			$sql = "select bp.faction, c.name, bp.categorization 	
			from battle_participants bp, characters c
			where battle_id = $battle_id
			and   bp.character_id = c.id 
			and   bp.faction = '$faction' 
			and   bp.status = 'alive'";
		
		$joinedsoldiers = Database::instance() -> query ( $sql ) -> as_array();
		
		foreach ( $joinedsoldiers as $joinedsoldier )
		{ 
			$a[$joinedsoldier -> faction]['list'][] = 
				Character_Model::create_publicprofilelink(null, $joinedsoldier -> name) ;
			$a[$joinedsoldier -> faction][$joinedsoldier -> categorization] += 1;	
		}
		
		return $a;
	}

	
	/**
	* Categorizza un combattente come attaccante, difensore, mercenario o altro
	* @param object char instance
	* @param object attackingregion region instance
	* @param object attackedregion region instance
	* @param string battletype 
	* @return string categorization attackerorally|defenderorally|mercenary
	*/

	public function categorize( $char, $attackingregion, $attackedregion, $battletype )
	{

	
		kohana::log('info', "------- Categorization of {$char -> name} -------");
		$kingdomchangeddate = $char -> get_stats( 'changedkingdom' );
				
		// Il char è cittadino del kingdom che attacca e non ha mai cambiato regno
		// oppure se ha cambiato regno verso questo regno, lo ha fatto almeno 30 
		// gg fa.
		
		kohana::log('info', "-> Checking if char is a citizen of attacking kingdom, attacker or ally...");
		
		// CITTADINO REGNO ATTACCANTE
		
		if ( 
			$char -> region -> kingdom_id == $attackingregion -> kingdom_id 
			and
			(
				is_null ( $kingdomchangeddate )			
				or
				(	
					!is_null($kingdomchangeddate) 
					and 
					$kingdomchangeddate[0] -> value < ( time() - 30 * 24 * 3600 ) 
				)
			)
		)
			return 'attackerorally';
		
		kohana::log('info', "-> Checking if is a citizen of defending kingdom, defender or ally...");
		
		// CITTADINO REGNO ATTACCATO
		
		if ( 
			$char -> region -> kingdom_id == $attackedregion -> kingdom_id
			and
			(
				is_null ( $kingdomchangeddate )			
				or
				(	
					!is_null($kingdomchangeddate) 
					and 
					$kingdomchangeddate[0] -> value < ( time() - 30 * 24 * 3600 ) 
				)
			)
		)
			return 'defenderorally';	


		/******************************************************
		* GESTIONE ALLEATI: ATTACCO
		******************************************************/		
		
		kohana::log('info', "-> Checking if the kingdom of the citizen is allied to the attacking kingdom and if the kingdom of the citizen is in war with the attacked kingdom.");
		
		// diplom. relationship between char and attackingregion
		
		$dr_1 = Diplomacy_Relation_Model::get_diplomacy_relation( 
			$char -> region -> kingdom_id, $attackingregion -> kingdom_id ) ;
		// diplom. relationship between char and defendingregion
		$dr_2 = Diplomacy_Relation_Model::get_diplomacy_relation( 
			$char -> region -> kingdom_id, $attackedregion -> kingdom_id ) ;

		kohana::log('info', '-> (Attack) Char: ' . $char -> name . ' Dipl. Rel. with Attacking Region: [' . $dr_1['type'] . '] Dipl. Rel. with Defending Region: [' . $dr_2['type'] . ']' );		
		
		// CONQUER_REGION, RAID ETC:
		// Se il mio ruolo è uguale a quello del regno che attacca allora sono un attaccante.

		
		// guerre del regno del char
		
		$runningwars = Kingdom_Model::get_kingdomwars( $char -> region -> kingdom_id, 'running');		
		
		// stabiliamo quale è la guerra corrente: è quella in cui sono coinvolti il regno del char e quello attaccato.
		
		$currentwar = null;
		
		foreach ($runningwars as $runningwar)
			if ( array_key_exists( $attackedregion -> kingdom_id, $runningwar['kingdoms'] ) )
				$currentwar = $runningwar;
				
		kohana::log('info', "-> (Attack): Kingdom of char has role: {$currentwar['kingdoms'][$char->region->kingdom_id]['role']}, Attacked Kingdom has role: {$currentwar['kingdoms'][$attackedregion -> kingdom_id]['role']}");
		
		if ( 
			in_array( $battletype, array('conquer_r', 'raid'))
			and
			!is_null($currentwar)
			and 
            $currentwar['kingdoms'][$char->region->kingdom_id]['role'] != $currentwar['kingdoms'][$attackedregion -> kingdom_id]['role']
			and
			( 
				is_null($kingdomchangeddate) 
				or 
				(	
					!is_null($kingdomchangeddate) and 
					$kingdomchangeddate[0] -> value < ( time() - 30 * 24 * 3600 ) 
				)
			)
			
		)		
			return 'attackerorally';			
		
		/******************************************************
		* GESTIONE ALLEATI: DIFESA
		******************************************************/

		kohana::log('info', "-> Checking if the kingdom of the citizen is allied to the attacked kingdom and if the kingdom of the citizen is in war with the attacking kingdom.");
		
		// Verifichiamo controlli DIFESA
		
		$dr_1 = Diplomacy_Relation_Model::get_diplomacy_relation( 
			$char -> region -> kingdom_id, $attackedregion -> kingdom_id ) ;
		$dr_2 = Diplomacy_Relation_Model::get_diplomacy_relation( 
			$char -> region -> kingdom_id, $attackingregion -> kingdom_id ) ;
		
		kohana::log('info', '-> (Defend) Char: ' . $char -> name . ' Dipl. Rel. with Defending Region: [' . $dr_1['type'] . '] Dipl. Rel. with Attacking Region: [' . $dr_2['type'] . ']');
		
		//if ( !is_null ( $kingdomchangeddate ) )
			//kohana::log('info', '-> Char: ' . $char -> name  . ' Changed kingdom on: ' . date('d-m-Y', $kingdomchangeddate[0] -> value ) );		
		// Se la relazione diplomatica tra regno del char e il regno che attaccato è ALLY
		// e il regno del char ed il regno della regione attaccante sono in guerra
		// allora la categoria è DEFENDERORALLY.
		
		// stabiliamo quale è la guerra corrente: è quella in cui sono coinvolti il regno del char e quello che attacca.
				
		foreach ($runningwars as $runningwar)
			if ( array_key_exists( $attackingregion -> kingdom_id, $runningwar['kingdoms'] ) )
				$currentwar = $runningwar;
		
		if ( 
			in_array( $battletype, array('conquer_r', 'raid'))
			and 			
			!is_null($currentwar)
			and
			$currentwar['kingdoms'][$char->region->kingdom_id]['role'] != $currentwar['kingdoms'][$attackingregion -> kingdom_id]['role']
			//Kingdom_Model::are_kingdoms_at_war( $char -> region -> kingdom_id, $attackingregion -> kingdom_id )
			and
			( 
				is_null($kingdomchangeddate) 
				or 
				(	
					!is_null($kingdomchangeddate) and 
					$kingdomchangeddate[0] -> value < ( time() - 30 * 24 * 3600 ) 
				)
			)
			
		)		
			return 'defenderorally';
		
		// Se la battaglia è nativerevolt, verifichiamo SOLO che il cittadino sia alleato
		// poichè in questo caso non è necessario che i 2 regni non siano in guerra
		// dato che i nativi sono del regno INDIPENDENTE.
		
		if (			
			$battletype == 'nativerevolt' 
			and 
			$dr_1['type'] == 'allied' 
			and 
			( is_null($kingdomchangeddate) or 
				(	
					!is_null($kingdomchangeddate) and 
					$kingdomchangeddate[0] -> value < ( time() - 30 * 24 * 3600 ) 
				)
			)
			
		)		
			return 'defenderorally';		
		
		/* 
		Commentata la parte di mercenario 
		// il char ha fondato un gruppo mercenario da almeno 30 giorni?
		
		$res = Database::instance() -> query 
		( 
			"select g.id 
			 from groups g
			 where g.character_id = " . $char -> id . " 
			 and   g.type = 'groups.mercenary' 
			 and   g.date < ( unix_timestamp() - ( 30 * 24 * 3600 ) )"
			 ) -> as_array();
		
		if ( count( $res ) > 0 )
			return 'mercenary' ;
			
		// char è membro di un gruppo mercenario?
		// da almeno 30 giorni?
		
		$res = Database::instance() -> query 
		( 
			"select g.id 
			 from group_characters gm, groups g
			 where gm.group_id = g.id
			 and   g.type = 'groups.mercenary' 
			 and   gm.joined = 1 
			 and   gm.date < ( unix_timestamp() - ( 30 * 24 * 3600 ) )
			 and   gm.character_id = " . $char -> id  ) -> as_array();
				
		if ( count( $res ) > 0 )
			return 'mercenary' ;
		*/
		
		return 'other' ;
		
	}
	
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
	{}
	
	/** 
	* Loads battle participants
	* @return none
	*/
	
	public function loadteams( ) 
	{
		
		$attackers = array();
		$defenders = array();
		$attackersnames = array();	
		$defendersnames = array();

		$db = Database::instance();
		
		$sql = "select * from battle_participants where battle_id = " . $this -> par[0] . " and status = 'alive'" ; 
		$participants = $db -> query( $sql );
		
		foreach ( $participants as $participant )
		{						
			
			if ( $participant -> faction == 'attack' )
			{
				$attacker = $this -> be -> loadcharbattlecopy( $participant -> character_id );
				$attacker['fights'] = 0;
				//kohana::log('info', "-> Adding {$attacker['char']['name']} {$attacker['char']['key']} as attacker.");
				$attackers[$attacker['char']['key']] = $attacker;
				$attackersnames[] = $attacker['char']['name'];
			
			}
			
			if ( $participant -> faction == 'defend' )
			{
				$defender = $this -> be -> loadcharbattlecopy( $participant -> character_id );
				$defender['fights'] = 0;
				//kohana::log('info', "-> Adding {$defender['char']['name']} {$defender['char']['key']} as defender.");
				$defenders[$defender['char']['key']] = $defender;
				$defendersnames[] = $defender['char']['name'];
			}
			
		}
		
		$attackersnamelist = implode( ", " , $attackersnames );
		$defendersnamelist = implode( ", " , $defendersnames );		
		
		$this -> battlereport[]['participants'] = '__battle.participants'.
		';' . $attackersnamelist .
		';' . $defendersnamelist ;
		
		$this -> battlereport[]['newline'] = '';
		
		
		$this -> attackers = $attackers;
		$this -> defenders = $defenders;
		
		/*
		foreach ($this -> attackers as $key => $char)
			kohana::log('info', "-> ATT: {$key} {$char['char']['name']}");
		
		foreach ($this -> defenders as $key => $char)
			kohana::log('info', "-> DEF: {$key} {$char['char']['name']}");
		
		//kohana::log('debug', kohana::debug( $attackers )); 	
				
		kohana::log('debug', kohana::debug( $defenders )); 	exit();
		*/
		
	}
	
	/** 
	* Compute Bonuses and Maluses
	* @param none
	* @return none
	*/
	
	public function compute_bonusmalus()
	{		
		
		// Applichiamo i vantaggi dati dalla geografia solo per alcuni tipi di battaglia
		
		if ( in_array ( $this -> par[0] -> type, 
			array( 
				'conquer_ir',
				'conquer_r', 
				'raid',
				'revolt',
				'nativerevolt',
			)))		
		{
			
			$this -> battlereport[]['bonusesheader'] = '__battle.malusbonusapplication'; 
			
			/////////////////////////////////////////
			// malus su energia dovuta a terreno
			////////////////////////////////////////
			
			kohana::log('info', '-> --- Applying Geography Maluses --- ');
			
			kohana::log('info', '-> Region Geography is: ' . $this -> attackedregion -> geography );
			
			switch ( $this -> attackedregion -> geography )
			{
				case 'plains': $malus = 0; break;
				case 'hills': $malus = 0.05; break;
				case 'mountains': $malus = 0.10; break;
				default: $malus = 0; break;
			}
			
			kohana::log('info', '-> Malus for geography is: ' . $malus );
			
			$this -> battlereport[]['bonuses'] = 
				'__battle.terrainmalus' . ';' . 
				($malus * 100) . ';__' . 
				'regioninfo.' . $this -> attackedregion -> geography; 
							
			
			////////////////////////////////////////
			// Malus a energia attaccanti
			// a seconda della presenza di un castello
			// o di un palazzo reale
			////////////////////////////////////////		
			
			kohana::log('info', '-> --- Applying Castle/RoyalPalace Malus ---');
			
			$castle = $this -> attackedregion -> get_structure('castle' );
			$royalpalace = $this -> attackedregion -> get_structure('royalpalace' );			
			
			if ( !is_null ( $castle ) )
			{
				
				$controlledregions = ST_Castle_1_Model::get_controlled_regions( $castle -> id, $castle -> region_id );
				$malus += min( 0.15, 0.3 * count( $controlledregions ));
				$this -> battlereport[]['bonuses'] = '__battle.castlepresencemalus' . ';' . $malus * 100 ;
				kohana::log('info', '-> Controlled regions: ' . count( $controlledregions ) . ' - adding malus: +' . $malus . '%' );
			}
			
			if ( !is_null ( $royalpalace ))
			{
				kohana::log('info', '-> Royalpalace found, adding +.1% malus.' );
				$this -> battlereport[]['bonuses'] = '__battle.royalpalacepresencemalus' . ';' . 10 ;
				$malus += 0.1;
			}
			
			kohana::log('info', '-> Final Royal Palace/Energy Malus is: ' . $malus . '---' );
			
			if ( $malus > 0 )
			{											
				foreach ( $this -> attackers as $key => &$attacker )
				{
					//kohana::log('info', $attacker['char']['name'] . '  - Energy before: ' . $attacker['char']['energy'] ); 
					$attacker ['char']['energy'] = max( 1, round( $attacker['char']['energy'] * ( 100 - $malus * 100 ) / 100, 0 ) );
					//kohana::log('info', $attacker['char']['name'] . '  - Energy after: ' . $attacker['char']['energy'] ); 
				}
			}
			
			////////////////////////////////////////////////////
			// Stamina boost
			////////////////////////////////////////////////////
			
			foreach ( $this -> attackers as $key => &$attacker )
			{
				kohana::log('info', "Evaluating {$key} - {$attacker['char']['name']}");
				if ($attacker['char']['type'] != 'npc')
				{
					
					$stat = Character_Model::get_stat_d( $attacker['char']['obj']->id, 'staminaboost' );
					if ( $stat -> loaded == true and time() < $stat -> stat1  )
					{
							kohana::log('info', $attacker['char']['name'] . ' has stamina BOOST');
						$attacker['char']['staminaboost'] = true;
					}
				}
			}
			
			foreach ($this -> defenders as $key => &$defender)
			{
				kohana::log('info', "Evaluating {$key} - {$defender['char']['name']}");
				$stat = Character_Model::get_stat_d( $defender['char']['obj'] -> id, 'staminaboost' );
				if ( $stat -> loaded == true and time() < $stat -> stat1  )
				{
					kohana::log('info', $defender['char']['name'] . ' has stamina BOOST');
					$defender['char']['staminaboost'] = true;
				}
			}
			
			/*
			kohana::log('info', 'afterstamina');			
			foreach ($this -> defenders as $key => $char)
				kohana::log('info', "-> DEF: {$key} {$char['char']['name']}");
			*/
			
			////////////////////////////////////////////////////
			// Per ogni jar tar danno ad un numero variabile di 
			// attaccanti in dipendenza della costituzione
			// solo nel caso di conquista regione, rivolta	
			// raid ed in presenza di un castello.
			////////////////////////////////////////////////////			
		
			if ( !is_null ( $castle ) )		
			{
				
				kohana::log('debug', '-> Applying Jar tar Malus...' );			
			
				$jartarlimit = 3;
				$usedjartar = 0;
				
				foreach ( $this -> defenders as $key => &$defender )
				{
					kohana::log('info', '-> Checking if ' . $defender['char']['name'] . ' has a jartar...');
					
					if ( $usedjartar < $jartarlimit 
					and 
						$defender['char']['obj'] -> has_item( $defender['char']['obj']->id, 'jartar', 1 ) == true )
					{					
											
						kohana::log('debug', '-> defender ' . $defender['char']['name'] . ' has a jartar. ');
						
						$item = Item_Model::factory( null, 'jartar' ); 
						$item -> removeitem( 'character', $defender['char']['obj'] -> id, 1 );											
						$this -> battlereport[]['bonuses'] = '__battle.jartarused' ;
											
						mt_srand();
						foreach ( $this -> attackers as $key => &$attacker )
						{
							
							$x = mt_rand ( 1, 100 );
							if ( $x <= 20 )
							{
								$dealtdamage = max(1, 
									round (
										90 / 
										pow(											
											$attacker['char']['cost'],
											0.5
										),2
									)
								);
								
								kohana::log('debug', '-> Applying jartar damage ' . $dealtdamage . ' to: ' . $attacker['char']['name']  );		
								$attacker['char']['health'] -= $dealtdamage;
								$this -> battlereport[]['bonuses'] = '__battle.jartardamage' . 
									';' . $attacker['char']['name'] .
									';' . $dealtdamage ;
							}
						}				
						
						$usedjartar ++;
					}
				
				}
				/*
				kohana::log('info', 'after jartar');			
				foreach ($this -> defenders as $key => $char)
					kohana::log('info', "-> DEF: {$key} {$char['char']['name']}");
				*/
			}
			
		}
	}	
		
	/** 
	* Combatte
	* 
	* @param none
	* @return none
	*/	

	public function fight()
	{}
		
	/** 
	* Gestisce gli sconfitti
	* li mette in convalescenza (solo se sono PC (Player controlled)
	* @param none
	* @return none
	*/
	
	public function handle_defeated( )
	{
	
		kohana::log('info', '------ Handling defeated ------' ); 
					
		$db = Database::instance();
		
		foreach ( $this -> defeated as $defeated )
		{
			kohana::log('info', '- Handling defeat for char ' . $defeated['char']['name'] . ' -' ); 
			
			if ( $defeated['char']['type'] == 'pc' )
			{				
				// aggiorna skill parry
				
				if ( Skill_Model::character_has_skill( $defeated['char']['obj'] -> id, 'parry' ) )				
				{
					kohana::log('info', "-> Char {$defeated['char']['obj']->name} failed parry: {$defeated['char']['parryfails']}");
					if ( $defeated['char']['parryfails'] > 0 )
					{
						$skillinstance = SkillFactory_Model::create('parry'); 
						$skillinstance -> increaseproficiency( 
							$defeated['char']['obj'] -> id, 
							round( 
								(100 - $skillinstance -> getProficiency($defeated['char']['obj'] -> id))/130 + $skillinstance -> getIncreasefactor() * $defeated['char']['parryfails'], 
								2
							)
						);						
					}																
				}
				
				// Controlla se il char ha contratto la peste
				
				if (isset($defeated['char']['plagueinjected']))
				{
					kohana::log('info', 'Injecting plague.');
					$plague = DiseaseFactory_Model::createDisease('plague');
					$plague -> injectdisease($defeated['char']['obj'] -> id );
				}
			
				// Metti il char in convalescenza
				
				kohana::log('info', '-> Putting in convalescence char: ' . $defeated['char']['name']); 
				$this -> be -> put_in_convalescence( $defeated, $this -> battletype ); 				
				kohana::log('info', '-> Finished putting in convalescence char: ' . $defeated['char']['name']); 
			
				// togli dallo schieramento
				
				kohana::log('info', 'setting char as DEAD in battle participants...');
				
				$sql = "update battle_participants set status = 'dead' where battle_id = " . $this -> par[0] . "
					and character_id = " . $defeated['char']['obj'] -> id ;
				$db -> query ( $sql ); 
					
				kohana::log('info', 'Destroying items...' ); 				
				Battle_Type_Model::destroyitems($defeated);
				kohana::log('info', 'Updating items...');
				Battle_Type_Model::updateitems($defeated);
			
			}
			
			// i npc native non hanno un reale char, quindi
			// non possono essere salvati.
			
			if ( $defeated['char']['type'] == 'npc' and $defeated['char']['npctag'] != 'native' )	
			{
				
				$c = ORM::factory('character_npc_' . $defeated['char']['obj']->npctag, $defeated['char']['obj'] -> id);				
				$c -> die_aftermath();
				$c -> death();
				
			}
			
		}
		
		return;
	}
	
	/**
	* Remove items destroyed in battle
	* @param array $character character
	* @return none
	*/
	
	static function destroyitems( &$character )
	{
		if (isset($character['char']['destroyeditems']))
			foreach ( $character['char']['destroyeditems'] as $destroyeditem )
			{
				
				$item = ORM::factory('item', $destroyeditem['obj'] -> id );
				if ($item -> loaded )
				{
					kohana::log('info', "Destroying item: {$destroyeditem['obj']->tag} of player: {$character['char']['obj']->name}");
					$item -> destroy();					
				}
				
				// invalida la cache.
					
				My_Cache_Model::delete(  '-charinfo_' . $character['char']['obj'] -> id . '_' . $destroyeditem['obj'] -> tag );
			}
							
		return;
	}
	
	/**
	* Update items used in battle
	* @param array $character 
	* @return none
	*/
	
	static function updateitems( &$character )
	{
		// weapons
		
		if ( isset( $character['char']['weapons']['right_hand']['obj'] ) )
		{					
		
			
			kohana::log('debug', "-> Char: {$character['char']['name']}, updating Weapons...");
			
			$equippedweapon = $character['char']['weapons']['right_hand']['obj'];
			
			$item = ORM::factory('item', $equippedweapon -> id );
			if ($item -> loaded )					
				if ( $equippedweapon -> quality <= 0 )
				{
					kohana::log('info', "Destroying item: {$equippedweapon->tag} of player: {$character['char']['obj']->name}");
					$item -> destroy();
				}
				else
				{
					kohana::log('info', "Updating item: {$equippedweapon->tag} of player: {$character['char']['obj']->name} to quality:{$equippedweapon->quality}");
					$item -> quality = $equippedweapon -> quality;
					$item -> save();
				}			
			
			// invalida la cache.
							
			My_Cache_Model::delete(  '-charinfo_' . $character['char']['obj'] -> id . '_' . 
				$equippedweapon-> tag );							
				
		}
		
				
		if ( isset( $character['char']['armors'] ) )
		{
			
			kohana::log('debug', "-> Char: {$character['char']['name']}, updating Armors...");
			$processed = array();
			foreach ( (array) $character['char']['armors'] as $part => $equipmentitems )
			{				
				kohana::log('debug', "-> Armors: Examining part: {$part}");
				
				foreach ( (array) $equipmentitems as $tag => $data )
				{				
						//process only once.
						if (!isset($processed[$tag]))
						{	
					
							$item = ORM::factory('item', $data['obj'] -> id );
							if ($item -> loaded )					
								if ( $data['obj'] -> quality <= 0 )
								{
									kohana::log('info', "Destroying item: {$item->cfgitem->tag} {$item->id} of player: {$character['char']['name']}");
									$item -> destroy();
								}
								else
								{
									kohana::log('info', 
										"Updating item: {$item->cfgitem->tag} {$item->id} of player: {$character['char']['name']} to quality:{$data['obj'] -> quality}");
									$item -> quality = $data['obj'] -> quality;
									$item -> save();
								}			
										
							// invalida la cache.
							
							My_Cache_Model::delete(  '-charinfo_' . $character['char']['obj'] -> id . '_' .  $data['obj'] -> tag );	
							$processed[$data['obj'] -> tag] = true;
							
						}
				}						
			}
		}
		return;
	}
	
	/** 
	* Gestisce i char rimasti
	* Se PC (player controlled)
	* 1) Salva la salute e l' energia
	* 2) Elimina item che si sono distrutti durante la battaglia
	* @param none
	* @return none
	*/
	
	public function handle_alive ( )
	{
		
		kohana::log('info', '------ Handling alive ------' ); 
		
		$start = microtime();
		$db = Database::instance();
		$alivesoldiers = array_merge ( $this -> attackers, $this -> defenders );
		
		//kohana::log('debug', kohana::debug( $alivesoldiers )); exit(); 
		
		foreach ( $alivesoldiers as $alivesoldier )
		{
			
			if ( $alivesoldier['char']['type'] == 'pc' )
			{
				
				if ( Skill_Model::character_has_skill( $alivesoldier['char']['obj'] -> id, 'parry' ) )				
				{
					kohana::log('info', "-> Char {$alivesoldier['char']['obj'] -> name} failed parry: {$alivesoldier['char']['parryfails']}");
					if ( $alivesoldier['char']['parryfails'] > 0 )
					{
						$skillinstance = SkillFactory_Model::create('parry'); 
						$skillinstance -> increaseproficiency( 
							$alivesoldier['char']['obj'] -> id, 
							round( 
								(100 - $skillinstance -> getProficiency($alivesoldier['char']['obj'] -> id))/130 + $skillinstance -> getIncreasefactor() * $alivesoldier['char']['parryfails'], 
								2
							)
						);						
					}
				}
				
				if (isset($alivesoldier['char']['plagueinjected']))
				{
					kohana::log('info', 'Injecting plague...');
					$plague = DiseaseFactory_Model::createDisease('plague');
					$plague -> injectdisease($alivesoldier['char']['obj'] -> id );
				}
			
				// aggiorno la salute
			
				$alivesoldier['char']['obj'] -> health = $alivesoldier['char']['health'];
				$alivesoldier['char']['obj'] -> energy = $alivesoldier['char']['energy'];				
				$alivesoldier['char']['obj'] -> save();				
				
				kohana::log('info', 'Destroying items...' );
				Battle_Type_Model::destroyitems( $alivesoldier );
				
				kohana::log('info', 'Updating items...' ); 
				Battle_Type_Model::updateitems( $alivesoldier );
				
			}
		}
		
		$elapsed = microtime() - $start;
				
		return;
	}
	
	/** 
	* Aftermath della battaglia
	* 
	* @param none
	* @return none
	*/
	
	function do_aftermath()
	{}
	
	/**
	* Completa la battaglia
	* @param $currentround round corrente
	* @return none
	*/
	
	function completebattle( $currentround = 1, $attackerwins, $defenderwins, $raidedcoins = 0 )
	{			
		
		$_tmp = $this -> be -> format_fightreport( $this -> battlereport, 'internal' ); 
		
		$sql = "
			update battles set 
			status = 'completed', 
			timestamp = unix_timestamp(), 
			attacker_wins = " . $attackerwins . ",			
			defender_wins = " . $defenderwins . ",
			raidedcoins = " . $raidedcoins . " 
			where id = " . $this -> bm -> id ;
			
		Database::instance() -> query( $sql ); 	
		
		$sql = "update battle_reports set 			
			report" . $currentround . " = '" . addslashes($_tmp) . "'					
			where battle_id = " . $this -> bm -> id ;			
			
		Database::instance() -> query( $sql ); 		
			
		// refresh cache
			
		$cachetag = '-cfg-regions'; 				
		My_Cache_Model::delete ( $cachetag );			
		
		// schedula un destroy cdb 
		
		if (!is_null( $this -> battlefield ) )
		{
			$a = new Character_Action_Model();
			$a -> character_id = $this -> bm -> source_character_id ;
			$a -> param1 = $this -> battlefield -> id ;
			$a -> blocking_flag = false;
			$a -> action = 'destroycdb';
			$a -> status = 'running';
			$a -> starttime = time() + kohana::config('medeur.battlefielddestroytime') * 3600;
			$a -> endtime = $a -> starttime;
			$a -> save();	
		}
		
		return;

	}
	
	
	
	/** 
	* Calcola i costi per la dichiarazione
	* ed il reclutamento
	* 
	* @param none
	* @return costo
	*/

	function compute_costs()
	{
		return 1000;
	}		

}
