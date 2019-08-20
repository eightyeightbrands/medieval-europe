<?php defined('SYSPATH') OR die('No direct access allowed.');

class Battle_Engine_Model
{
	protected $has_many = array('battle_participants');
	protected $test = false;
	private $battlereport = array();
	protected $cfgweapons;
	protected $cfgarmors;
	
	const MAXROUNDNUMBER = 24;
	const CYCLES = 500;
	
	
	/**
	* Gets hitpart defense, total defense and which equipment to consumed
	* @param string $part Hit part
	* @param array $charcopy Copy of Char
	* @return array $info
		- hitpart defense: defense of superior equipment on part
		- total defense: cumulative defense of equipment 
		- which equipment to consumed
	*/
	
	public function get_part_info($part, $charcopy)
	{		
		
		$cfgarmors = Configuration_Model::get_armorscfg();
		$cfgweapons = Configuration_Model::get_weaponscfg();

		$info  = array(
			'hitpartdefense' => 0,
			'totaldefense' => 0,
			'hitobj' => null,
			'hitpart' => $part);
				
		// Check type of defense on hit part.
		
		$totaldefense = 0;
		$hitobjdefense = 0;
		$hitobj = null;
		
		if ( isset($charcopy['char']['armors'][$part]) )
			foreach ($charcopy['char']['armors'][$part] as $tag => $data )			
			{
				// if the hit is on left_arm and a shield exist, the hit2
				// equipment is the shield only.
				
				if ( $part == 'left_hand' )
				{
					if ( $cfgarmors['armorlist'][$tag]['obj'] -> subcategory == 'shield' )
					{
						$hitobj = $data['obj'];								
						$totaldefense = $data['obj'] -> defense;
						$hitobjdefense = $data['obj'] -> defense;													
						break;
					}
					else
					{
						
						// if the hit is on left_arm and a shield DOES NOT exist,
						// the defense accumulates and the hit part is the upper armor.
				
						$hitobj = $data['obj'];								
						$totaldefense += $data['obj'] -> defense;
						$hitobjdefense = $data['obj'] -> defense;								
					
					}
				}
				else
				{
					
					// the defense accumulates and the hit part is the upper armor.
										
					$hitobj = $data['obj'];								
					$totaldefense += $data['obj'] -> defense;
					$hitobjdefense = $data['obj'] -> defense;								
				}
			}
								
		$info['hitobj'] = $hitobj;
		$info['hitobjdefense'] = $hitobjdefense;
		$info['totaldefense'] = $totaldefense;		
		
		return $info;
	}
	
	/** 
	* La funzione fight mette a confronto due char e ne determina il vincitore. 
	* Ritornerà quindi le strutture attacker e defender con gli attributi del char (Energy/health) e gli item (duration ecc) aggiornati. 
	* Non è a carico della funzione eventuali ragionamenti sul vincitore o perdente, che dipendono dalle condizioni del combattimento a monte.    
	* Ritornerà anche l’ id del char che ha vinto e il report del combattimento nel campo report.
	* 
	* @param attacker struttura char di chi attacca in/out
	* @param defender struttura char di chi difende in/out
	* @param winner  id del giocatore che ha vinto out	
	* @param battlestats statistica battaglia
	* @param match numero match
	* @return report  array report della battaglia
	*/
	
	
	public function fight( &$attacker, &$defender, &$winner, &$battlestats, $match)
	{
	
		// controlliamo se hanno energia
		
		if ( $attacker['char']['energy'] <= 0 and $defender['char']['energy'] <= 0 ) 
		{
		
			$winner = 'none';
			return;		
		}
		
		$this -> battlereport[]['startduel'] = '__battle.startduel' .';' . 
			$match . ';' . $attacker['char']['name'] . ';' . $defender['char']['name'] ;
		
		////////////////////////////////////////////////////////////////////////////////////////////
		// stabiliamo le percentuali di quanti colpi riesce ad infliggere
		// in un turno l' attaccante ed il difensore.
		////////////////////////////////////////////////////////////////////////////////////////////
							
		$battleround = 1;
		$att_hits=0;
		$def_hits=0;
		$att_consumedenergy=0;
		
		//$att_energy = $attacker['char']['energy'] ;
		//$def_energy = $defender['char']['energy'] ;
		
		$attacker['char']['faction'] = 'attacker';
		$defender['char']['faction'] = 'defender';
		
		
		// determiniamo la percentale di hit e miss			
		$att_expectedchancetohit = intval((25 + ( $attacker['char']['dex'] - $defender['char']['dex'] )*1.1)/50 * 100 );
		$def_expectedchancetohit = intval((25 + ( $defender['char']['dex'] - $attacker['char']['dex'] )*1.1)/50 * 100 );
		
		$att_totalhits = $att_successfulhits = $def_totalhits = $def_successfulhits = 0;
		//Battle_Engine_Model::battledebug( $attacker['char']['name'] . " Hit rate: " . $att_expectedchancetohit . ", " .  $defender['char']['name'] . " Hit rate: " . $def_expectedchancetohit );
		
		// if attacker, defender is participating in a tournament,
		// display equipment.
		
		if (isset($attacker['char']['obj']))
			$tournstat = Character_Model::get_stat_d( $attacker['char']['obj'] -> id, 'tournamentparticipant');
		else
			$tournstat = null;
		
		if ( !is_null($tournstat) and $tournstat -> loaded == true )
		{
			
			// show weapon
			
			if ( isset( $attacker['char']['weapons']['right_hand']['obj'] ) )
				$this -> battlereport[]['equipmentinfo'] = 
					$attacker['char']['name'] .
					' Weapon: ' .  kohana::lang($attacker['char']['weapons']['right_hand']['obj'] -> name);
			else
			{
				$this -> battlereport[]['equipmentinfo'] = 	
					$attacker['char']['name'] . ' is not using any weapons. ';				
			}

			//armor
			
			if ( isset( $attacker['char']['armors']['head'] ) )
				foreach ($attacker['char']['armors']['head'] as $tag => $data )			
				$this -> battlereport[]['equipmentinfo'] = 
					$attacker['char']['name'] .
					' Head armor: ' .  kohana::lang($attacker['char']['armors']['head'][$tag]['obj'] -> name);
			else
				$this -> battlereport[]['equipmentinfo'] = 	$attacker['char']['name'] . ' Head: no armor';				
					
			if ( isset( $attacker['char']['armors']['body'] ) )
				foreach ($attacker['char']['armors']['body'] as $tag => $data )			
				$this -> battlereport[]['equipmentinfo'] = 
					$attacker['char']['name'] .
					' Body armor: ' .  kohana::lang($attacker['char']['armors']['body'][$tag]['obj'] -> name);		
			else
				$this -> battlereport[]['equipmentinfo'] = 	$attacker['char']['name'] . ' Body: no armor';									
					
			if ( isset( $attacker['char']['armors']['torso'] ) )
				foreach ($attacker['char']['armors']['torso'] as $tag => $data )			
				$this -> battlereport[]['equipmentinfo'] = 
					$attacker['char']['name'] .
					' Torso armor: ' .  kohana::lang($attacker['char']['armors']['torso'][$tag]['obj'] -> name);				
			else
				$this -> battlereport[]['equipmentinfo'] = 	$attacker['char']['name'] . ' Torso: no armor';							
			
			if ( isset( $attacker['char']['armors']['left_hand'] ) )
				foreach ($attacker['char']['armors']['left_hand'] as $tag => $data )			
				$this -> battlereport[]['equipmentinfo'] = 
					$attacker['char']['name'] .
					' Left Hand armor: ' .  kohana::lang($attacker['char']['armors']['left_hand'][$tag]['obj'] -> name);		
			else
				$this -> battlereport[]['equipmentinfo'] = 	$attacker['char']['name'] . ' Left Hand: no armor';				
					
					
			if ( isset( $attacker['char']['armors']['right_hand'] ) )
				foreach ($attacker['char']['armors']['right_hand'] as $tag => $data )			
				$this -> battlereport[]['equipmentinfo'] = 
					$attacker['char']['name'] .
					' Right Hand armor: ' .  kohana::lang($attacker['char']['armors']['right_hand'][$tag]['obj'] -> name);			
			else
				$this -> battlereport[]['equipmentinfo'] = 	$attacker['char']['name'] . ' Right Hand: no armor';				
			
			if ( isset( $attacker['char']['armors']['legs'] ) )
				foreach ($attacker['char']['armors']['legs'] as $tag => $data )			
				$this -> battlereport[]['equipmentinfo'] = 
					$attacker['char']['name'] .
					' Legs armor: ' .  kohana::lang($attacker['char']['armors']['legs'][$tag]['obj'] -> name);
			else
				$this -> battlereport[]['equipmentinfo'] = 	$attacker['char']['name'] . ' Legs: no armor';				
			
					
			if ( isset( $attacker['char']['armors']['feet'] ) )
				foreach ($attacker['char']['armors']['feet'] as $tag => $data )			
				$this -> battlereport[]['equipmentinfo'] = 
					$attacker['char']['name'] .
					' Feet armor: ' .  kohana::lang($attacker['char']['armors']['feet'][$tag]['obj'] -> name);								
			else
				$this -> battlereport[]['equipmentinfo'] = 	$attacker['char']['name'] . ' Feet: no armor';				
					
		}
		
		$this -> battlereport[]['newline'] = '' ; 			
		
		if (isset($defender['char']['obj']))
			$tournstat = Character_Model::get_stat_d( $defender['char']['obj'] -> id, 'tournamentparticipant');
		else
			$tournstat = null;
		
		if ( !is_null($tournstat) and $tournstat -> loaded == true )		
		{
			// show weapon
			
			if ( isset( $defender['char']['weapons']['right_hand']['obj'] ) )
				$this -> battlereport[]['equipmentinfo'] = 
					$defender['char']['name'] .
					' Weapon: ' .  kohana::lang($defender['char']['weapons']['right_hand']['obj'] -> name);
			else
			{
				$this -> battlereport[]['equipmentinfo'] = 	
					$defender['char']['name'] . ' is not using any weapons. ';				
			}

			//armor
			
			if ( isset( $defender['char']['armors']['head'] ) )
				foreach ($defender['char']['armors']['head'] as $tag => $data )			
				$this -> battlereport[]['equipmentinfo'] = 
					$defender['char']['name'] .
					' Head armor: ' .  kohana::lang($defender['char']['armors']['head'][$tag]['obj'] -> name);
			else
				$this -> battlereport[]['equipmentinfo'] = 	$defender['char']['name'] . ' Head: no armor';				
					
			if ( isset( $defender['char']['armors']['body'] ) )
				foreach ($defender['char']['armors']['body'] as $tag => $data )			
				$this -> battlereport[]['equipmentinfo'] = 
					$defender['char']['name'] .
					' Body armor: ' .  kohana::lang($defender['char']['armors']['body'][$tag]['obj'] -> name);		
			else
				$this -> battlereport[]['equipmentinfo'] = 	$defender['char']['name'] . ' Body: no armor';									
					
			if ( isset( $defender['char']['armors']['torso'] ) )
				foreach ($defender['char']['armors']['torso'] as $tag => $data )			
				$this -> battlereport[]['equipmentinfo'] = 
					$defender['char']['name'] .
					' Torso armor: ' .  kohana::lang($defender['char']['armors']['torso'][$tag]['obj'] -> name);				
			else
				$this -> battlereport[]['equipmentinfo'] = 	$defender['char']['name'] . ' Torso: no armor';							
			
			if ( isset( $defender['char']['armors']['left_hand'] ) )
				foreach ($defender['char']['armors']['left_hand'] as $tag => $data )			
				$this -> battlereport[]['equipmentinfo'] = 
					$defender['char']['name'] .
					' Left Hand armor: ' .  kohana::lang($defender['char']['armors']['left_hand'][$tag]['obj'] -> name);		
			else
				$this -> battlereport[]['equipmentinfo'] = 	$defender['char']['name'] . ' Left Hand: no armor';				
					
					
			if ( isset( $defender['char']['armors']['right_hand'] ) )
				foreach ($defender['char']['armors']['right_hand'] as $tag => $data )			
				$this -> battlereport[]['equipmentinfo'] = 
					$defender['char']['name'] .
					' Right Hand armor: ' .  kohana::lang($defender['char']['armors']['right_hand'][$tag]['obj'] -> name);			
			else
				$this -> battlereport[]['equipmentinfo'] = 	$defender['char']['name'] . ' Right Hand: no armor';				
			
			if ( isset( $defender['char']['armors']['legs'] ) )
				foreach ($defender['char']['armors']['legs'] as $tag => $data )			
				$this -> battlereport[]['equipmentinfo'] = 
					$defender['char']['name'] .
					' Legs armor: ' .  kohana::lang($defender['char']['armors']['legs'][$tag]['obj'] -> name);
			else
				$this -> battlereport[]['equipmentinfo'] = 	$defender['char']['name'] . ' Legs: no armor';				
			
					
			if ( isset( $defender['char']['armors']['feet'] ) )
				foreach ($defender['char']['armors']['feet'] as $tag => $data )			
				$this -> battlereport[]['equipmentinfo'] = 
					$defender['char']['name'] .
					' Feet armor: ' .  kohana::lang($defender['char']['armors']['feet'][$tag]['obj'] -> name);								
			else
				$this -> battlereport[]['equipmentinfo'] = 	$defender['char']['name'] . ' Feet: no armor';				
				
				
		}
		
		$this -> battlereport[]['newline'] = '' ; 
		
		while ( 
			$battleround < self::MAXROUNDNUMBER and 
			( $attacker['char']['health'] > 0 and $defender['char']['health'] > 0 ) and 
			( $attacker['char']['energy'] > 0 or $defender['char']['energy'] > 0 ) )
		
		{
			$initiative = '';			
			$initiative = 'attacker';
			
			// Calcoliamo le statistiche per il combattimento			
				
			$this -> get_fightstats( $attacker, $this->debug, $this -> cfgarmors, $this -> cfgweapons );
			$this -> get_fightstats( $defender, $this->debug, $this -> cfgarmors, $this -> cfgweapons );	
			
			Battle_Engine_Model::battledebug('<b> --- Attacker Info --- </b>', $this -> debug );
			
			if ( isset( $attacker['char']['weapons']['right_hand']['obj'] ) )
				Battle_Engine_Model::battledebug(
					$attacker['char']['name'] .
					' Weapon: ' .  kohana::lang($attacker['char']['weapons']['right_hand']['obj'] -> name) . 
					' Weapon weight: '  . ($attacker['char']['weapons']['right_hand']['obj'] -> weight / 1000) .
					' Weapon condition: '  . $attacker['char']['weapons']['right_hand']['obj'] -> quality . '%' .
					' Weapon Damage: ' . $attacker['char']['wpn_mindamage'] . '-' . $attacker['char']['wpn_maxdamage'] , $this -> debug );
			else
			{
				Battle_Engine_Model::battledebug( $attacker['char']['name'] . ' is not using weapons. ' .
				' Weapon Damage: ' . $attacker['char']['wpn_mindamage'] . '-' . $attacker['char']['wpn_maxdamage'] 
				, $this -> debug );
			}
			//var_dump($attacker['char']['armors']); exit;
			foreach ( array( 'head', 'torso', 'left_hand', 'right_hand', 'legs', 'feet' ) as $part )
				if ( isset( $attacker['char']['armors'][$part] ))
					foreach ($attacker['char']['armors'][$part] as $tag => $data )			
					Battle_Engine_Model::battledebug( 
						$attacker['char']['name'] .
						" has {$part} covered by : " .  kohana::lang($attacker['char']['armors'][$part][$tag]['obj'] -> name)
					, $this -> debug );
					
			
			Battle_Engine_Model::battledebug(
					$attacker['char']['name'] . ' - ' .
					' Health: ' .$attacker['char']['health'] .					
					' Energy: ' .$attacker['char']['energy'] .
					' Base Transp. Weight: ' . ($attacker['char']['basetransportableweight'] / 1000) . 
					' Encumberance: ' . $attacker['char']['armorencumbrance']. '%' . 
					' Stunned for attacks: ' . $attacker['char']['stunnedround'] . 					
					' Str: ' . $attacker['char']['str'] .
					' Dex: ' . $attacker['char']['dex'] .				
					' Int: ' . $attacker['char']['intel'] .
					' Car: ' . $attacker['char']['car'] .					
					' Const: ' . $attacker['char']['cost'] , $this -> debug );
			
			
			Battle_Engine_Model::battledebug('<b> --- Defender Info --- </b>', $this -> debug );
			
			// weapons
			
			if ( isset( $defender['char']['weapons']['right_hand']['obj'] ) )
				Battle_Engine_Model::battledebug(
					$defender['char']['name'] .
					' Weapon: ' .  kohana::lang($defender['char']['weapons']['right_hand']['obj'] -> name) . 
					' Weapon weight: '  . ($defender['char']['weapons']['right_hand']['obj'] -> weight / 1000) .
					' Weapon condition: '  . $defender['char']['weapons']['right_hand']['obj'] -> quality . '%' .
					' Weapon Damage: ' . $defender['char']['wpn_mindamage'] . '-' . $defender['char']['wpn_maxdamage'] , $this -> debug );
			else
			{
				Battle_Engine_Model::battledebug( $defender['char']['name'] . ' is not using weapons. ' .
				' Weapon Damage: ' . $defender['char']['wpn_mindamage'] . '-' . $defender['char']['wpn_maxdamage'] 
				, $this -> debug );
			}
			
			// armors
			
			foreach ( array( 'head', 'torso', 'left_hand', 'right_hand', 'legs', 'feet' ) as $part )
				if ( isset( $defender['char']['armors'][$part] ))
					foreach ($defender['char']['armors'][$part] as $tag => $data )			
					Battle_Engine_Model::battledebug( 
						$defender['char']['name'] .
						" has {$part} covered by : " .  kohana::lang($defender['char']['armors'][$part][$tag]['obj'] -> name)
					, $this -> debug );
			
			Battle_Engine_Model::battledebug( 
				$defender['char']['name'] . ' - ' .
					' Health: ' .$defender['char']['health'] .										
					' Energy: ' .$defender['char']['energy'] .					
					' Encumberance: ' . $defender['char']['armorencumbrance'] . '%' . 
					' Stunned for attacks: ' . $defender['char']['stunnedround'] . 
					' Str: ' . $defender['char']['str'] .
					' Dex: ' . $defender['char']['dex'] .				
					' Int: ' . $defender['char']['intel'] .
					' Car: ' . $defender['char']['car'] .				
					' Const: ' . $defender['char']['cost'] , $this -> debug );
			
						
			Battle_Engine_Model::battledebug('<b> --- Consecutive Attacks Info --- </b>', $this -> debug );
				
			// Determiniamo per questo turno quanti hit consecutivi hanno
			// a disposizione l' attaccante ed il difensore	
			
			$att_cons_hits = $this -> get_consecutiveattacks( $attacker );
			$def_cons_hits = $this -> get_consecutiveattacks( $defender );
			
			Battle_Engine_Model::battledebug( $attacker['char']['name'] . " Consecutive attacks: " . $att_cons_hits . 
				", " .  $defender['char']['name'] . " Consecutive attacks: " . $def_cons_hits , $this -> debug );
			
			Battle_Engine_Model::battledebug('<b> --- Initiative Info --- </b>', $this -> debug );
			
			// stabiliamo l'iniziativa in base alla caratteristica reach dell' arma						
			// se uno dei due char è stun, l' altro ha sempre l' iniziativa
			
			if ( $attacker['char']['stunnedround'] > 0 or $defender['char']['stunnedround'] > 0  )
			{
				
				if ($defender['char']['stunnedround'] > 0 )
				{
					Battle_Engine_Model::battledebug( $defender['char']['name'] . " is stunned, initiative given to " . 
						$attacker['char']['name'] , $this -> debug );
					$initiative = 'attacker' ;
				}
				elseif ($attacker['char']['stunnedround'] > 0 )
				{
					Battle_Engine_Model::battledebug( $attacker['char']['name'] . " is stunned, initiative given to  " . 
						$defender['char']['name'], $this -> debug );
					$initiative = 'defender' ;
				}
			}
			else
			{
				if ( !isset( $attacker['char']['weapons']['right_hand']['obj'] ) )
					$attackerweaponreach = 1;
				else
					list($attackerweaponreach) = explode( '-', $attacker['char']['weapons']['right_hand']['obj'] -> reach );
				
				if ( !isset( $defender['char']['weapons']['right_hand']['obj'] ) )
					$defenderweaponreach = 1;
				else
					list($defenderweaponreach) = explode( '-', $defender['char']['weapons']['right_hand']['obj'] -> reach );					
			
				$maxchance = $attackerweaponreach + $defenderweaponreach ;
				mt_srand();
				$r = mt_rand( 1, $maxchance );
				
				if ( $r <= $attackerweaponreach )
					$initiative = 'attacker';
				else
					$initiative = 'defender';			
				
				Battle_Engine_Model::battledebug( 'Initiative: Max chance: ' . $maxchance . 			                      
									  ', Attacker reach: ' . $attackerweaponreach . 
									  ', Defender reach: ' . $defenderweaponreach .
									  ', Roll: ' . $r .
									  ', Initiative: ' . $initiative , $this -> debug );
			}
			
			$attacker['char']['cons_hits'] = $att_cons_hits;
			$defender['char']['cons_hits'] = $def_cons_hits;
			
			// copia in variabili di comodo per usare lo stesso blocco di codice			
			
			if ( $initiative == 'attacker' )
			{
				$_attacker = $attacker;
				$_defender = $defender;				
			}
			else
			{
				$_attacker = $defender;
				$_defender = $attacker;				
			}
		
		
			// report
			
			$this -> battlereport[]['newline'] = '' ; 
			
			if ( $_attacker['char']['type'] == 'npc' )
				$this -> battlereport[]['nativeinfo'] = '__battle.playerinfo;' . 
					$_attacker['char']['name'] . ';' . 
					' Health: ' . round($_attacker['char']['health'], 2) .
					' Energy: ' . round($_attacker['char']['energy'],2) .
					' Str: ' . $_attacker['char']['str'] .
					' Dex: ' . $_attacker['char']['dex'] .				
					' Int: ' . $_attacker['char']['intel'] .					
					' Car: ' . $_attacker['char']['car'] .				
					' Const: ' . $_attacker['char']['cost'] . 					
					' Encumberance: ' . $_attacker['char']['armorencumbrance'] . '%';
			else
				$this -> battlereport[]['playerinfo'] = '__battle.playerinfo;' . 
					$_attacker['char']['name'] . ';' . 
					' Health: ' . round($_attacker['char']['health'], 2) .
					' Energy: ' . round($_attacker['char']['energy'], 2) .
					' Encumberance: ' . $_attacker['char']['armorencumbrance'] . '%';
			
			if ( $_defender['char']['type'] == 'npc' )
				$this -> battlereport[]['nativeinfo'] = '__battle.playerinfo;' . 
					$_defender['char']['name'] . ';' . 
					' Health: ' . round($_defender['char']['health'], 2) .
					' Energy: ' . round($_defender['char']['energy'],2) .
					' Str: ' . $_defender['char']['str'] .
					' Dex: ' . $_defender['char']['dex'] .				
					' Int: ' . $_defender['char']['intel'] .
					' Car: ' . $_defender['char']['car'] .				
					' Const: ' . $_defender['char']['cost'] . 					
					' Encumberance: ' . $_defender['char']['armorencumbrance'] . '%';
			else
				$this -> battlereport[]['playerinfo'] = '__battle.playerinfo;' . 
					$_defender['char']['name'] . ';' . 
					' Health: ' . round($_defender['char']['health'],2) .
					' Energy: ' . round($_defender['char']['energy'],2) .
					' Encumberance: ' . $_defender['char']['armorencumbrance'] . '%';
			
			$this -> battlereport[]['newline'] = '' ; 
				
			$this -> battlereport[]['startturn'] = '=== Round: ' . ( $battleround ) . ' ===';
			
			
			$internalturn = 1;
			$hits = 1;						
			
				
			// Bleed damage
			
			
			if ( $_attacker['char']['bleeddamage'] > 0 )
			{
				$_attacker['char']['health'] -= $_attacker['char']['bleeddamage'];
				$this -> battlereport[]['bleeddamage'] = '__battle.bleeddamage;' . $_attacker['char']['name'] . ';' . 
					round($_attacker['char']['bleeddamage'],2) . ';' . round($_attacker['char']['health'],2);
				
			}
			
			if ( $_defender['char']['bleeddamage'] > 0 )
			{
				$_defender['char']['health'] -= $_defender['char']['bleeddamage'];
				$this -> battlereport[]['bleeddamage'] = '__battle.bleeddamage;' . $_defender['char']['name'] . ';' . 
					round($_defender['char']['bleeddamage'],2) . ';' . round($_defender['char']['health'],2);
				
			}
			
			while ($internalturn <= 2 )
			{
				
				//Battle_Engine_Model::battledebug( '>>> Internalturn: ' . $internalturn ); 
				
				/*Battle_Engine_Model::battledebug('>>> Hits: ' . $hits .
					', Consecutive hits: ' .  $_attacker['char']['cons_hits'] . 					
					', Att. Health: ' .$_attacker['char']['health'] ); 
				*/
				
				while ( $hits <= $_attacker['char']['cons_hits'] and $_attacker['char']['health'] > 0 and $_defender['char']['health'] > 0 )
				{			
					
					Battle_Engine_Model::battledebug( 'Hit n. '. $hits . '/' . $_attacker['char']['cons_hits'] , $this -> debug );
					
					// Ricalcoliamo le statistiche per il combattimento
					
					$this -> get_fightstats( $_attacker, $this->debug, $this -> cfgarmors, $this -> cfgweapons );
					$this -> get_fightstats( $_defender, $this->debug, $this -> cfgarmors, $this -> cfgweapons );
					
					// stabiliamo se il colpo è inferto
					
					$miss = false;
					$hit = false;
					
					$battlestats['totalhits'][$_attacker['char']['name']]['total']++;
					
					// Logica di hit e miss
					
					// se l' attacker è stun, hit è falso sicuramente					
					// se il defender è stunned, hit è sicuro
					// altrimenti la probabilità dipende dalla differenza tra le dex					
					
					if ( $_attacker['char']['stunnedround'] > 0 )
					{ 
						Battle_Engine_Model::battledebug ( '*** ' . $_attacker['char']['name'] . ' is stunned, MISS.', $this -> debug );
						$hit = false; 
						$miss = true; 
					}
					elseif ( $_defender['char']['stunnedround'] > 0 )
					{ 
						$hit = true; 
						$miss = false; 
					}
					else
					{
						Battle_Engine_Model::battledebug('Battleround: ' . $battleround , $this -> debug );
						if ( $battleround == 1 )
						{
							if ( $_attacker['char']['name'] == $attacker['char']['name'] )		
							{
								$chancetohit = $att_expectedchancetohit;
								$att_totalhits++;
							}
							else
							{
								$chancetohit = $def_expectedchancetohit;
								$def_totalhits++;
							}
							
						}
						else
						{
							if ( $_attacker['char']['name'] == $attacker['char']['name'] )							
							{
								
								$actual_hitperc = ($att_successfulhits/max(1,$att_totalhits))*100;
								Battle_Engine_Model::battledebug($attacker['char']['name'] . ', Hits ' . $att_successfulhits.'/'. $att_totalhits, $this -> debug );
								Battle_Engine_Model::battledebug($attacker['char']['name'] . ' Actual Hitperc: ' . $actual_hitperc . '% (against ' . $att_expectedchancetohit . '%)', $this -> debug );
								$chancetohit = $att_expectedchancetohit + ( $att_expectedchancetohit - $actual_hitperc );
								Battle_Engine_Model::battledebug( 'Corrected chancetohit from: ' . $att_expectedchancetohit . ' to: ' . $chancetohit , $this -> debug );
								$att_totalhits++;
							}
							else
							{
								
								$actual_hitperc = ($def_successfulhits/max(1,$def_totalhits))*100;
								Battle_Engine_Model::battledebug($defender['char']['name'] . ', Hits ' . $def_successfulhits.'/'.$def_totalhits, $this -> debug );
								Battle_Engine_Model::battledebug($defender['char']['name'] . ' Actual Hitperc: ' . $actual_hitperc . '% (against ' . $def_expectedchancetohit . '%)', $this -> debug );
								$chancetohit = $def_expectedchancetohit + ( $def_expectedchancetohit - $actual_hitperc );
								Battle_Engine_Model::battledebug( 'Corrected chancetohit from: ' . $def_expectedchancetohit . ' to: ' . $chancetohit, $this -> debug );
								$def_totalhits++;
							}
						}
						
						// Lancia un dato per determinare se il colpo
						// è andato a buon fine o meno.
						
						mt_srand();
						$roll = mt_rand(1,100);
						Battle_Engine_Model::battledebug( 'Chancetohit :' . $chancetohit . '% Roll: ' . $roll , $this -> debug );
						
						if ( $roll <= $chancetohit )						
						{
							Battle_Engine_Model::battledebug( '***HIT!***', $this -> debug );
							$hit = true;							
							if ( $_attacker['char']['name'] == $attacker['char']['name'] )							
								$att_successfulhits ++;								
							else
								$def_successfulhits ++;
														
						}
						else
						{
							Battle_Engine_Model::battledebug( '***MISS!***', $this -> debug );
							$hit = false;
							$blockedwithparry = false;
						}
						
						// Se il char ha lo skill parry, teniamo conto dei tentativi riusciti e non riusciti
						// Lo skill si attiva se l' attaccante usa arma e il difensore ha scudo o arma impugnata.
						
						$blockedwithparry = false;
						
						if ( $hit == true )
						{
							// Non applicabile a NPC
							
							if ( $_defender['char']['type'] != 'npc' )
							{
								
								Battle_Engine_Model::battledebug( "Defender parry: " . isset( $_defender['char']['parry']) );
								Battle_Engine_Model::battledebug( "Attacker right hand: ". isset($_attacker['char']['weapons']['right_hand']['obj']));
								Battle_Engine_Model::battledebug( "Defender right hand: ". isset($_defender['char']['weapons']['right_hand']['obj']));
								
								if (
								
									isset( $_defender['char']['parry'] )
									// and	isset( $_attacker['char']['weapons']['right_hand']['obj'] )
									and
									(
										isset( $_defender['char']['weapons']['right_hand']['obj'])
										or
										isset( $_defender['char']['weapons']['left_hand']['obj'])
									)
								)
							
								{
									
									Battle_Engine_Model::battledebug( "Parry skill evaluation active.", $this -> debug );
										
									Battle_Engine_Model::battledebug( "Proficiency of parry skill: {$_defender['char']['parry']}, chance to parry a hit:" . ($_defender['char']['parry']*0.50) . "%", $this -> debug );
									
									mt_srand();
									
									$roll = mt_rand(1,100);
									
									if ( $roll <= ( max(0, $_defender['char']['parry'] * 0.50)) )
									{
										
										Battle_Engine_Model::battledebug( '***HIT BLOCKED BY PARRY SKILL!***', $this -> debug );
										$hit = false;
										$blockedwithparry = true;
										$_defender['char']['parrysuccess'] += 1;
										;										
									}
									else
									{
										$this -> battlereport[]['battleparryfail'] = '__battle.parryfail;' . $_defender['char']['name'];
										kohana::log('debug', "roll: {$roll}: ***PARRY TENTATIVE FAILED!***");
										Battle_Engine_Model::battledebug( '***PARRY TENTATIVE FAILED. ***', $this -> debug );
										
										// parata fallita, incrementiamo lo skill.
										$deltaproficiency = round((100 - $_defender['char']['parry'] ) / 130 + 0.2 , 2 );
										
										Battle_Engine_Model::battledebug( "-> Parry failed. current proficiency: {$_defender['char']['parry']}. Incrementing by {$deltaproficiency}.", $this -> debug );
										
										$_defender['char']['parry'] += $deltaproficiency;
										
										Battle_Engine_Model::battledebug( "-> New Parry proficiency: {$_defender['char']['parry']}", $this -> debug );																				
										$_defender['char']['parryfails'] += 1;
									}
								}
							}
						}
							
					}				
					// se c'è un miss e l' attaccante NON è stunned => ha proprio sbagliato.
					
					//kohana::log('debug', "blockedwithparry: {$blockedwithparry}");
					//kohana::log('debug', "hit: {$hit}");
					//kohana::log('debug', "stunned round: {$_attacker['char']['stunnedround']}");
					
					if ( !$hit and $_attacker['char']['stunnedround'] <= 0 and !$blockedwithparry)
					{
						$this -> battlereport[]['battlemiss'] = '__battle.miss;' . $_attacker['char']['name'];
						$battlestats['totalhits'][$_attacker['char']['name']]['missed']++;
					}					
					// se c'è un miss e l' attaccante NON è stunned ma il colpo è stato parato
					
					if ( !$hit and $_attacker['char']['stunnedround'] <= 0 and $blockedwithparry)
					{
						$this -> battlereport[]['battlemiss'] = '__battle.missbecausedparry;' . $_attacker['char']['name'] . ';' . $_defender['char']['name'];
						$battlestats['totalhits'][$_attacker['char']['name']]['blocked']++;
					}					
					else if ( !$hit and $_attacker['char']['stunnedround'] > 0 )
					{
						$this -> battlereport[]['battlemiss'] = '__battle.missbecausestun;' . $_attacker['char']['name'];
						//$battlestats['totalhits'][$_attacker['char']['name']]['missed']++
					}
					
					elseif ( $hit )
					{
						
						//$battlestats['totalhits'][$_attacker['char']['name']]['successful']++;
						
						///////////////////////////////////
						// stabiliamo il danno
						///////////////////////////////////
					
						if ( isset( $_attacker['char']['weapons']['right_hand']['obj'] ) )
							$wc = $_attacker['char']['weapons']['right_hand']['obj'] -> quality / 100;
						else
							$wc = 1;
						
						$skew_value = round( -10 + ($_attacker['char']['str'] * 0.35) + ($_attacker['char']['energy'] * 0.14)  + 
						($wc * 6) );
						
						$totalrolls = abs($skew_value) + 3;
						
						if ( $skew_value <= 0 ) 
							$wpndamage = Utility_Model::rollDice( $totalrolls, 0, abs($skew_value), 0, 
								$_attacker['char']['wpn_mindamage'], $_attacker['char']['wpn_maxdamage'] );
						else
							$wpndamage = Utility_Model::rollDice( $totalrolls, 0, 0, abs($skew_value),
								$_attacker['char']['wpn_mindamage'], $_attacker['char']['wpn_maxdamage'] );
						
						//////////////////////////////////////////////
						// damage: se si combatte 
						//////////////////////////////////////////////
						
						
						$damage = $wpndamage + ($_attacker['char']['str'] / 5);
						
						Battle_Engine_Model::battledebug( 
							' Wpn Damage: ' . $wpndamage .
							' Total rolls: ' . $totalrolls . 
							' Damage (including str bonus): ' . $damage , $this -> debug );
						
						
						$attackbonus = 0;
						
						if (							
							isset($_attacker['char']['dogmabonus']['meditateanddefend']) 
							and 
							$_attacker['char']['fightmode'] == 'defend')
						{
							$attackbonus = -50;
							$attackbonus *= (100 - ($_attacker['char']['faithlevel']*30/100))/100;
						}
						
						if (
							isset($_attacker['char']['dogmabonus']['killinfidels']) 
							and 
							$_attacker['char']['fightmode'] == 'attack' )
						{
							Battle_Engine_Model::battledebug ("Applying Kill Infidels Bonus.",$this->debug);	
							$attackbonus = 50;
							$attackbonus *= $_attacker['char']['faithlevel']/100;
						}
						Battle_Engine_Model::battledebug( "--- Attack Bonus (dogma ---");
						Battle_Engine_Model::battledebug( "Fight Mode: {$_attacker['char']['fightmode']}" , $this -> debug );
						Battle_Engine_Model::battledebug( "Attack Bonus: {$attackbonus}%" , $this -> debug );
						Battle_Engine_Model::battledebug( "Faith Level: {$_attacker['char']['faithlevel']}" , $this -> debug );
					
						Battle_Engine_Model::battledebug(" Damage Before: {$damage}", $this -> debug );
						$damage	*= ((100 + $attackbonus)/100);
						Battle_Engine_Model::battledebug("Damage After: {$damage}", $this -> debug );
						
						
						// se il difensore è stun, il damage è aumentato
						
						if ( $_defender['char']['stunnedround'] > 0 )
						{					
						
							$damage *= 1.5;
							Battle_Engine_Model::battledebug( 
							'Defender is stunned, damage multiplied. Damage now is: ' . $damage , $this -> debug );
						}
						
						/* 
							Colpo Critico
						*/
					
						if ( isset( $_attacker['char']['weapons']['right_hand']['obj'] ) )
						{
							if ($_attacker['char']['weapons']['right_hand']['obj'] -> critical == '')
								$critmin = 999;
							else
								list( $critmin, $critmult ) = explode( 'x', $_attacker['char']['weapons']['right_hand']['obj'] -> critical );
						}
						else
						{
							$critmin = 999;
							$critmult = 0;
						}
						
						$critical = false;
						mt_srand();
						$roll = mt_rand(1, 20);
						Battle_Engine_Model::battledebug( 'Minimum roll for critical for the weapon: ' . $critmin . '. Rolled: ' . $roll , $this -> debug );
						
						if ( $roll >= $critmin )
						{
							Battle_Engine_Model::battledebug( 'Attacker deals a Critic. Check if the Defender dodges it.', $this -> debug );
							
							$chancetocrit = intval( 
								10 + 
								(
									max( 0, $_attacker['char']['intel'] - $_defender['char']['intel'] ) 
									+
									max( 0, $_attacker['char']['dex'] - $_defender['char']['dex'] ) 
								) * 1.19);
								
							mt_srand();
							$roll = mt_rand(1,100);
							Battle_Engine_Model::battledebug( 'Critical chance: ' . $chancetocrit . '%' . ' roll: ' . $roll, $this -> debug );
							if ( $roll <= $chancetocrit )						
							{
								$critical = true;
								Battle_Engine_Model::battledebug( 'Defender did not dodge, CRITICAL HIT.', $this -> debug );
							}
						}	
						
								
						if ( $critical )
						{
							$battlestats['totalcriticalhits']['total']++;
							$battlestats['totalcriticalhits'][$_attacker['char']['name']]++;
							Battle_Engine_Model::battledebug("Damage before: $damage", $this -> debug );
							$damage *= $critmult; $damage += 10;
							Battle_Engine_Model::battledebug("Damage after Critical multiplier: $damage", $this -> debug );
							$this -> battlereport[]['battlecriticalhit'] = '__battle.battlecriticalhit' .';'  . $_attacker['char']['name'] ;
						}
						
						/*
						 Ripartizione danno tra bluntdamage e cutdamage
						*/
						
						if ( isset( $_attacker['char']['weapons']['right_hand']['obj'] ) )					
						{
							$bluntdamage = $damage * $_attacker['char']['weapons']['right_hand']['obj'] -> bluntperc / 100;
							$cuttingdamage = $damage * $_attacker['char']['weapons']['right_hand']['obj'] -> cutperc / 100;						
						}
						else
						{
							$bluntdamage = $damage;
							$cuttingdamage = $damage * 0;
						}
						
						Battle_Engine_Model::battledebug( $_attacker['char']['name'] . ' - ' .
							" Damage before defense: Bluntdamage: <b>" . round($bluntdamage,2) . "</b>, Cuttingdamage: <b>" . round($cuttingdamage,2)."</b>, Total Damage: <b>" . round($bluntdamage+$cuttingdamage,2) . "</b>"
						, $this -> debug );
						
						/*
						Determinazione parte del corpo colpita
						*/
					
						$part='none'; 
						
						mt_srand();
						if ( 
							isset( $_attacker['char']['weapons']['right_hand']['obj'] ) 
							and 
							$_attacker['char']['weapons']['right_hand']['obj'] -> reach >= 4 )
							$r = mt_rand( 1, 6 );
						else
							$r = mt_rand( 1, 5 );
												
						switch ( $r )
						{
							case 1: { $part = 'head'; break;}
							case 2: { $part = 'torso'; break; }							
							case 3: { $part = 'left_hand'; break; }
							case 4: { $part = 'right_hand'; break; }
							case 5: { $part = 'legs'; break; }
							case 6: { $part = 'feet'; break; }							
							default: break;
						}
						
						// let's verify defense of the hit part.
						
						$hitpart_info = array();
						
						// if there is some armors on the hit part...
						
						if ( 
							isset( $_defender['char']['armors'][$part]) and 
							count( $_defender['char']['armors'][$part]) > 0
							) 								
						{
							
							$hitpart_info = Battle_Engine_Model::get_part_info($part, $_defender);
							
							Battle_Engine_Model::battledebug( "Hit equipment is: <b>" . kohana::lang($hitpart_info['hitobj'] -> name) . "</b>, defense: <b>{$hitpart_info['hitobjdefense']}</b>, totaldefense: <b>{$hitpart_info['totaldefense']}</b>", $this -> debug );

							// if hit part is left hand and player has a shield, some damage is absorbed
							
							if ($hitpart_info['hitobj'] -> subcategory == 'shield')
							{								
								$bluntdamage_afterarmor = max(0, $bluntdamage - $hitpart_info['totaldefense'] * 40/100 );
								$cuttingdamage_afterarmor = 0;
							}														
							else
							{
								// subtract TOTAL armor defense from bluntdamage
								$bluntdamage_afterarmor = max(0, $bluntdamage - $hitpart_info['totaldefense']);					
								
								// if there's a weapon, subtract armor defense from cuttingdamage 
								if (isset( $_attacker['char']['weapons']['right_hand']['obj'] ) )
								{								
									$cuttingdamage_afterarmor = $cuttingdamage * (1 - ($hitpart_info['totaldefense'] * 3.5
										/ $_attacker['char']['weapons']['right_hand']['obj'] -> armorpenetration ));									
								}										
								else
									$cuttingdamage_afterarmor = 0;				
							}
							
							$cuttingdamage_afterarmor = max( 0, $cuttingdamage_afterarmor );
								
						}
						else
						{
							$hitpart_info = array(
								'hitobj' => null,
								'hitobjdefense' => 0,
								'totaldefense' => 0,
							);
							
							Battle_Engine_Model::battledebug(	'Hit landed on body part: ' . $part . 
								', Armor is: none' .
								', Defense is: 0 ', $this -> debug );
						
							$bluntdamage_afterarmor = $bluntdamage;
							$cuttingdamage_afterarmor = $cuttingdamage;
						}
						
						///////////////////////////////////////////////////////////////////
						// Consume Weapon: If there is a shield, we use shield defense.
						// A weapon consumes less than an armor (half)
						// if there is no shield we use armor defense and if not, we 
						// use chainmail defense
						///////////////////////////////////////////////////////////////////
						
						if ( isset( $_attacker['char']['weapons']['right_hand']['obj'] ) )
						{
							
							if (isset($hitpart_info['hitobj']))
								$consumefactor = (float) max( 1, $hitpart_info['hitobjdefense'] ) * 
									(1/max(1 , $_attacker['char']['weapons']['right_hand']['obj'] -> wearfactor)) ;
							else
								$consumefactor = (float) 1 * (1/max(1 , $_attacker['char']['weapons']['right_hand']['obj'] -> wearfactor)) ;
							
							$consumefactor *= 0.25;							
							
							Battle_Engine_Model::battledebug( $_attacker['char']['name'] . ' has a weapon. Its quality is: <b>' . round($_attacker['char']['weapons']['right_hand']['obj'] -> quality,2) . "%</b> and wearfactor is:" .$_attacker['char']['weapons']['right_hand']['obj'] -> wearfactor . " Will be consumed by a factor of <b>{$consumefactor}%</b>", $this -> debug );
							
							$_attacker['char']['weapons']['right_hand']['obj'] -> quality -= $consumefactor;
							
							Battle_Engine_Model::battledebug( $_attacker['char']['name'] . " has a weapon, Its quality is NOW: <b>" . round($_attacker['char']['weapons']['right_hand']['obj'] -> quality,2) .'%</b>' , $this -> debug );
							
							// recompute attack, defense
							//$this -> get_fightstats( $_attacker ) ;	
						}
						
						////////////////////////////////////////////////////////////
						// Consume armor
						////////////////////////////////////////////////////////////
						
						if ( !is_null ($hitpart_info['hitobj']) ) 								
						{							
							Battle_Engine_Model::battledebug( $_defender['char']['name'] . " has been hit equipment: <b>" . kohana::lang($hitpart_info['hitobj'] -> name) ." </b>. Its quality BEFORE HIT is: <b>" . round($hitpart_info['hitobj'] -> quality,2) . "%</b> and totaldefense is <b>" . round($hitpart_info['totaldefense'],2) ."</b>", $this -> debug );
													
							if ( $bluntdamage > 0 )
							{
								
								$consumefactor = max( $bluntdamage - $hitpart_info['hitobjdefense'], $hitpart_info['hitobjdefense']/4);
								$consumefactor /= (float) max(1, $hitpart_info['hitobj'] -> wearfactor);
								
								$_defender['char']['armors'][$hitpart_info['hitpart']][$hitpart_info['hitobj']->tag]['obj'] 
										-> quality -= $consumefactor;
								
								Battle_Engine_Model::battledebug( $_defender['char']['name'] . " has been hit equipment: <b>" . 
									kohana::lang($hitpart_info['hitobj'] -> name) ." </b>. Its quality AFTER HIT is: <b>" . round($_defender['char']['armors'][$hitpart_info['hitpart']][$hitpart_info['hitobj']->tag]['obj'] 
									-> quality,2) . "%</b>.", $this -> debug );
									
							}
								
						}
														
						// stun chance, Bleed damage
							
						$stunchance =  max( 0, round (( $bluntdamage_afterarmor / 150 ) * 100 ) );	
						$bleeddamage = $cuttingdamage_afterarmor * 0.05;
						
						Battle_Engine_Model::battledebug($_attacker['char']['name'] . 
							", Damage after armor defense: Bluntdamage: <b>" . round($bluntdamage_afterarmor,2). "</b>, Cuttingdamage: <b>" . round($cuttingdamage_afterarmor,2). "</b>, Total Damage: <b>" . round($bluntdamage_afterarmor + $cuttingdamage_afterarmor,2) . "</b>, Stun chance: " . ($stunchance) . "%, Bleed damage: <b>" . round($bleeddamage,2) .  "</b>", $this -> debug );
						
						$_defender['char']['bleeddamage'] += $bleeddamage;
			
						// if damage > 0 round up to 1.	
						
						if ( ($bluntdamage_afterarmor + $cuttingdamage_afterarmor) > 0)
							$totaldamage = max(1, $bluntdamage_afterarmor + $cuttingdamage_afterarmor);						
						else
							$totaldamage = max(0, $bluntdamage_afterarmor + $cuttingdamage_afterarmor);						
						
						$battlestats['totaldamage'][$_attacker['char']['name']] += (int) round($totaldamage,0);
						
						// Se il total damage > 0, e l' attaccante è un npc-ratto, c'è
						// una possibilità di attaccare la peste
						
						if ( ($totaldamage) > 0 )
						{
							
							$_defender['char']['health'] -= $totaldamage;
							
							if (
								$_attacker['char']['type'] == 'npc' 
								and 
								(
								$_attacker['char']['npctag'] == 'smallrat'
								or
								$_attacker['char']['npctag'] == 'largerat'
								)
								)
								{
									mt_srand();
									$r = mt_rand(1, 5000);									
									if ($r == 23 )
										$_defender['char']['plagueinjected'] = true;									
								}
								
						}
	
						
						if ( isset( $_attacker['char']['weapons']['right_hand']['obj'] ))
						{
												
							$this -> battlereport[]['battlehit'] = '__battle.hit2;' . $_attacker['char']['name'] . 
								';' . $_defender['char']['name'] . 
								';' . '__' . $_attacker['char']['weapons']['right_hand']['obj'] -> name . 
								';' . '__' . 'battle.part_' . $part .
								';' . round($totaldamage,2) . ';' . round($_defender['char']['health'],2) ;
						}
						else
						{							
							$this -> battlereport[]['battlehit'] = '__battle.hit' . $_attacker['char']['npctag'] .
								';' . $_attacker['char']['name'] .
								';' . $_defender['char']['name'] . 
								';' . '__' . 'battle.part_' . $part . 																
								';' . round($totaldamage,2) . 
								';' .  round($_defender['char']['health'],2) ;								
						}
						
						// roll per stun
						
						if ( $_defender['char']['stunnedround'] <= 0 )
						{
						
							mt_srand();
							$r = mt_rand( 1, 100 );
							
							Battle_Engine_Model::battledebug ( 'Roll for stun must be less or equal than: ' . $stunchance . ', Roll: ' . $r, $this -> debug );
							if ( $r <= $stunchance )
							{		
								
								$_defender['char']['stunnedround'] = 								
								max(1,
									round(( Character_Model::get_attributelimit() - mt_rand( 
									max( 1, $_defender['char']['cost'] - 5 ), 
									min( Character_Model::get_attributelimit(), $_defender['char']['cost'] + 6 )))/5));
																
								$battlestats['totalstungiven'][$_attacker['char']['name']] ++;
								$battlestats['totalstunreceived'][$_defender['char']['name']] ++;
								$battlestats['totalstunnedrounds'][$_defender['char']['name']] += $_defender['char']['stunnedround'];
								
								Battle_Engine_Model::battledebug ( 'Attacks stunned: ' . $_defender['char']['stunnedround'] , $this -> debug );
								
								// i turni sono aumentati di 1 perchè poi questo viene sottratto.
								
								//$_defender['char']['stunnedround'] += 1;
								
								if ( $_defender['char']['health'] > 0 ) 
									$this -> battlereport[]['stunnedchar'] = '__battle.stunnedchar;' . $_attacker['char']['name'] . ';' . $_defender['char']['name'] ; 
							}						
						}
										
					}
				
					///////////////////////////////////
					// Consumo Energia
					///////////////////////////////////				
										
					if ( isset( $char['char']['weapons']['right_hand']['obj'] ) )			
						list($weaponreach) = $char['char']['weapons']['right_hand']['obj'] -> reach ;
					else
						$weaponreach = 1;
						
					// calcoliamo il peso dell' arma. Se nessuna arma il peso di default è 0 g			
					
					if ( isset( $_attacker['char']['weapons']['right_hand']['obj'] ) )
						$att_rightweaponweight = ($_attacker['char']['weapons']['right_hand']['obj'] -> weight / 1000);
					else
						$att_rightweaponweight = 0/1000 ;				
		
			
					$weaponweight_normalized = ($att_rightweaponweight - 0)/($this-> cfgweapons['maxweight']/1000-0);
					$armorencumbrance_normalized = ( $_attacker['char']['armorencumbrance'] - 0 )/(107.8-0);
					$weaponreach_normalized = ( $weaponreach - 1 )/ (5 - 1);
					$constitution_normalized = ( $_attacker['char']['cost'] - 1 )/ (Character_Model::get_attributelimit() - 1);
					
					
					$drainrate = 0.150 + 
					( 
						1 + (
							+ $weaponweight_normalized
							+ $armorencumbrance_normalized
							+ $weaponreach_normalized 
							- $constitution_normalized * 3 )
							/3
					);
					
					Battle_Engine_Model::battledebug("Drainrate: {$drainrate}", $this -> debug );
					
					// Se il personaggio è impattato dal bonus meditate e il fightmode è defend,
					// il drainrate viene abbassato del 75%.
					
					$energybonus = 0;
					
					if (isset($_attacker['char']['dogmabonus']['meditateanddefend']) and $_attacker['char']['fightmode'] == 'defend')
					{
						Battle_Engine_Model::battledebug ("Applying Meditate And Defend Bonus.",$this->debug);							
						$energybonus = 75;					
						$energybonus *= $_attacker['char']['faithlevel']/100;					
					}
					
					// Se il personaggio è impattato dal bonus kill infidels e il fightmode è attack,
					// il drainrate viene aumentato del 30%
					
					if (isset($_attacker['char']['dogmabonus']['killinfidels']) and $_attacker['char']['fightmode'] == 'attack')
					{
						Battle_Engine_Model::battledebug ("Applying Kill Infidels Bonus.",$this->debug);							
						$energybonus = -30;					
						$energybonus *= (100 - ($_attacker['char']['faithlevel']*30	/100))/100;												
					}

					Battle_Engine_Model::battledebug("Fight Mode: {$_attacker['char']['fightmode']}", $this -> debug );
					Battle_Engine_Model::battledebug("Faith Level: {$_attacker['char']['faithlevel']}", $this -> debug );
					Battle_Engine_Model::battledebug("Energy Bonus: {$energybonus}", $this -> debug );
					
					$drainrate *= (100 - $energybonus)/100;					
					Battle_Engine_Model::battledebug("Drainrate After: {$drainrate}", $this -> debug );
				
					// se l' attaccante è stun, nno colpisce per default, ma 
					// l' energia non deve essere tolta.
					
					if ( $_attacker['char']['stunnedround'] > 0 ) 
						$newenergy = $_attacker['char']['energy'];
					else
						if ( isset($_attacker['char']['staminaboost']) )					
							$newenergy = max( 5, $_attacker['char']['energy'] - $drainrate * 0.5);
						else
							$newenergy = max( 5, $_attacker['char']['energy'] - $drainrate) ;
					
					Battle_Engine_Model::battledebug( '*** ENERGY REPORT of ' . $_attacker['char']['name'] . '*** <br/>' .
							' >>>>>> Current Energy: ' . $_attacker['char']['energy'] . '<br/>' .
							' >>>>>> Weapon Weight: ' . $att_rightweaponweight . ' Kg ' . '<br/>' .
							' >>>>>> Weapon Weight Normalized: ' .$weaponweight_normalized . '<br/>' .
							' >>>>>> Armor Encumberance : ' . $_attacker['char']['armorencumbrance'] . '<br/>' .
							' >>>>>> Armor Encumberance Normalized: ' .$armorencumbrance_normalized . '<br/>' .
							' >>>>>> Weapon Reach : ' . $weaponreach . '<br/>' .
							' >>>>>> Weapon Reach Normalized: ' .$weaponreach_normalized . '<br/>' .
							' >>>>>> Stamina Boost: ' . isset($_attacker['char']['staminaboost']) . '<br/>' .
							' >>>>>> Constitution: ' . $_attacker['char']['cost'] . '<br/>' .
							' >>>>>> Constitution Normalized: ' . $constitution_normalized . '<br/>' .
							' >>>>>> Energy Depletion: ' . $drainrate . '<br/>' .							
							' >>>>>> New energy: ' . $newenergy , $this -> debug );
					
					$_attacker['char']['energy'] = $newenergy;
					
					$hits++;
					
					// siccome swappiamo, è sempre l' attacker che è stunned quindi
					// è nel turno attaccante che diminuiamo il counter
					
					if ( $_attacker['char']['stunnedround'] >= 0 )
						$_attacker['char']['stunnedround']--;
					if ( $_attacker['char']['stunnedround'] == 0 )
						$this -> battlereport[]['stunnedrecoverchar'] = '__battle.stunnedrecoverchar;' . $_attacker['char']['name'] ;								
				
				}				
				
				// swappo attacker e defender
				
				Battle_Engine_Model::battledebug ('-> Swapping chars.' , $this -> debug );
				$_x = $_attacker;
				$_attacker = $_defender;
				$_defender = $_x;
				$hits = 1;
				$internalturn++;
				
					
				
				/*
				if ( $_defender['char']['stunnedround'] >= 0 and $_defender['char']['health'] > 0 )
				{
					Battle_Engine_Model::battledebug ('-> Stunnedround: ' . ($_defender['char']['stunnedround']));					
					Battle_Engine_Model::battledebug ('-> *** Now should be the turn for DEFENDER but he is STUNNED so Round is ENDED.');
					$_defender['char']['stunnedround']--;
					if ( $_defender['char']['stunnedround'] == 0 )
						$this -> battlereport[]['stunnedrecoverchar'] = '__battle.stunnedrecoverchar;' . $_defender['char']['name'] ;
					
					$internalturn = 3;
					continue;
				}
				else
				{
					Battle_Engine_Model::battledebug ('-> Swapping chars.' );
					$_x = $_attacker;
					$_attacker = $_defender;
					$_defender = $_x;				
				}
				
				$hits = 1;
				$internalturn++;
				*/
			}
			
			// I contendenti si sono scambiati i colpi, passiamo al prossimo turno
			// e ricopiamo i valori nelle variabili master				
			
			if ( $_attacker['char']['faction'] == 'attacker' )
			{
				$attacker = $_attacker;
				$defender = $_defender;
			}
			else
			{
				$attacker = $_defender;
				$defender = $_attacker;
			}
			
			$battleround++;
			
		}	
		
		$battlestats['battlerounds'] = $battleround;
		$winner = 'none';
		Battle_Engine_Model::battledebug(	$attacker['char']['name'] . ' Health: ' . $attacker['char']['health'] .
			' Energy: ' . $attacker['char']['energy'] .
			' - ' . $defender['char']['name'] . ' Health: ' . $defender['char']['health'] .
			' Energy: ' . $defender['char']['energy'], $this -> debug );
		
		if ( $attacker['char']['health'] <= 0 and $defender['char']['health'] > 0 ) 
		{
			$winner = $defender['char']['key'];
			$winner_name = $defender['char']['name'];
			$this -> battlereport[]['endduel'] = '__battle.fight_win'.';'.$defender['char']['name'];
		}
		
		if ( $defender['char']['health'] <= 0 and $attacker['char']['health'] > 0 ) 
		{
			$winner = $attacker['char']['key'];
			$winner_name = $attacker['char']['name'];

			$this -> battlereport[]['endduel'] = '__battle.fight_win'.';'.$attacker['char']['name'];
		}
		
		if ( ( $defender['char']['health'] > 0 and $attacker['char']['health'] > 0 )
		or ( $defender['char']['health'] <= 0 and $attacker['char']['health'] <= 0 ) ) 
		{			
			$winner_name = 'none';
			$this -> battlereport[]['endduel'] = '__battle.fight_tie';
		}
				
		kohana::log('debug', "-> Winner is: {$winner}, {$winner_name}");
		
		//$attacker['char']['energy'] = $att_energy;
		//$defender['char']['energy'] = $def_energy;
		
		Battle_Engine_Model::battledebug( $attacker['char']['name'] . ' health: ' . $attacker['char']['health'], $this -> debug );
		Battle_Engine_Model::battledebug( $defender['char']['name'] . ' health: ' . $defender['char']['health'], $this -> debug );
		Battle_Engine_Model::battledebug( $attacker['char']['name'] . ' energy: ' . $attacker['char']['energy'], $this -> debug );
		Battle_Engine_Model::battledebug( $defender['char']['name'] . ' energy: ' . $defender['char']['energy'], $this -> debug );
		
		return;
	}
			
	/*
	*	Funzione che formatta un fight report.	
	*	@param text: report 
	*	@return testo html
	*/
	
	function format_fightreport	( $report, $format )
	{
	
		$debug = Kohana::config( 'medeur.debugbe'); 
		
		$output="";
		
		if ( $format == 'internal' )
			for ($r=0; $r<count($report); $r++)
			{									
				$output .= key($report[$r]) . '#' . $report[$r][key($report[$r])] . '@' ; 				
			}
		
		if ( $format == 'html' )		
		{
			
			//kohana::log( 'debug', $report); 
			$rows = explode( "@", $report, -1 );
			//echo kohana::log( 'debug', kohana::debug( $rows) ); exit(); 
			//echo kohana::debug( $rows ); exit();
				
			foreach ( $rows as $row )
			{
				$row = explode( "#", $row );				
				$output .= "<div class='" . $row[0] . "'>" . My_i18n_Model::translate($row[1]) . "</div>";
				
			}
		}
		
		return $output;
		
	}
	
	/*
	* Funzione che carica in maniera efficiente
	* le informazioni per la battaglia	
	* @param int $char_id ID Personaggio
	* @return $obj Copia del char
	*/
	
	function loadcharbattlecopy( $char_id )
	{
		
		$charcopy = array();
		$char = ORM::factory('character', $char_id );		
		
		// calcoliamo gli attributi
		
		$charcopy['char']['obj'] = $char;
		$charcopy['char']['type'] = $char -> type;
		$charcopy['char']['npctag'] = $char -> npctag;
		$charcopy['char']['key'] = $char -> type . '-' . $char -> id; 
		$charcopy['char']['name'] = $char -> name;
		$charcopy['char']['energy'] = $char -> energy;
		$charcopy['char']['health'] = $char -> health;		
		$charcopy['char']['str'] = $char -> get_attribute( 'str' ); 
		$charcopy['char']['dex'] = $char -> get_attribute( 'dex' ); 
		$charcopy['char']['intel'] = $char -> get_attribute( 'intel' ); 
		$charcopy['char']['cost'] = $char -> get_attribute( 'cost' ); 
		$charcopy['char']['car'] = $char -> get_attribute( 'car' ); 
		
		if ( Skill_Model::character_has_skill( $char -> id, 'parry') )
		{
			$parry = SkillFactory_Model::create('parry'); 
			$charcopy['char']['parry'] = $parry -> getProficiency($char->id);
		}
		
		$charcopy['char']['basetransportableweight'] = Character_Model::get_basetransportableweight( $charcopy['char']['str']);
		$charcopy['char']['encumbrance'] = $char -> get_encumbrance();		
		$charcopy['char']['armorencumbrance'] = 0;
		$charcopy['char']['stunnedround'] = 0;
		$charcopy['char']['parrysuccess'] = 0;
		$charcopy['char']['parryfails'] = 0;
		$charcopy['char']['bleeddamage'] = 0;
		$charcopy['char']['fights'] = 0;
		$charcopy['char']['destroyeditems'] = array();
		$charcopy['char']['faithlevel'] = 0;
		$charcopy['char']['fightmode'] = 'normal';		
		
		
		// load equipped objects
		
		$equippedarmorweight = 0;
		$wornequipment = Character_Model::get_equipment( $char -> id );
		//var_dump($wornequipment);exit;
		$cfgarmors = Configuration_Model::get_armorscfg();		
		
		//var_dump($equipment);exit;
		
		foreach ( $wornequipment as $part => $equipment )
		{
			
			if ( $equipment -> category == 'weapon' )			
			{
				$charcopy['char']['weapons'][$part]['obj'] = $equipment;				
			}
			
			elseif ( $equipment -> category == 'armor' )
			{
				foreach ( $cfgarmors['armorlist'][$equipment -> tag]['coverage'] as $coveredpart )
				{
					$charcopy['char']['armors'][$coveredpart][$equipment -> tag]['obj'] = $equipment;				
				
				}					
			}			
		}
		
		// Carica i bonus relativi alla chiesa
		
		if ( Church_Model::has_dogma_bonus($char -> church_id,'meditateanddefend') )
			$charcopy['char']['dogmabonus']['meditateanddefend'] = true;
		
		if ( Church_Model::has_dogma_bonus($char -> church_id,'killinfidels') )
			$charcopy['char']['dogmabonus']['killinfidels'] = true;		
		
		// faith level
		
		$stat = Character_Model::get_stat_d( $char -> id, 'faithlevel' );
		if ( $stat -> loaded )		
			$charcopy['char']['faithlevel'] = $stat -> value;

		return $charcopy;
	
	}
	
	/*
	* Recomputes damage and defense related to items condition
	* Removes equipment if condition <= 0
	* @param array $charcopy Character Data
	* @param boolean $debug stampare i messaggi di debug?
	* @return none
	*/
	
	function get_fightstats( &$charcopy, $debug = false, $cfgarmors = null, $cfgweapons = null )
	{
		
		if (is_null($cfgarmors))
			$cfgarmors = Configuration_Model::get_armorscfg();		
		
		if (is_null($cfgweapons))
			$cfgweapons = Configuration_Model::get_weaponscfg();		
			
		Battle_Engine_Model::battledebug( "<b>--- Computing defense and damage of char: {$charcopy['char']['name']} ---</b>", $debug);
		
		//kohana::log('debug', kohana::debug($charcopy));
		
		Battle_Engine_Model::battledebug ("--- Evaluating Weapon ---",$debug);
		
		$equippedarmorweight = 0;		
		
		// fixed valuess for no weapon. In case of non human npc it's function of strength.
		if ( $charcopy['char']['type'] == 'npc' )
		{
			$wpn_mindamage = max(1, $charcopy['char']['str'] * 0.25);
			$wpn_maxdamage = max(1, $charcopy['char']['str'] * 0.5);
		}
		else
		{
			$wpn_mindamage = max(1, $charcopy['char']['str'] * 0.1,0);
			$wpn_maxdamage = max(1, $charcopy['char']['str'] * 0.2,0);
		}
		
		// recompute weapon damage based on condition		
		
		if ( isset ($charcopy['char']['weapons']['right_hand']['obj'] ) )
		{
			// if weapon is broken, unset it
			
			$weapon = $charcopy['char']['weapons']['right_hand']['obj'];			
			
			if (intval($weapon -> quality) <= 0 )
			{				
				$charcopy['char']['destroyeditems'][] = $charcopy['char']['weapons']['right_hand'];
				
				if (isset($this -> battlereport))
					$this -> battlereport[]['objectbreak'] = '__battle.weaponshatters' . ';' . $charcopy['char']['name'] . ';__' . $weapon -> name;
				unset ($charcopy['char']['weapons']['right_hand']);
				
			}
			else
			{
				$originalmindamage = $cfgweapons['weaponlist'][$weapon->tag]['obj'] -> mindmg;
				$originalmaxdamage = $cfgweapons['weaponlist'][$weapon->tag]['obj'] -> maxdmg;
				
				Battle_Engine_Model::battledebug ("CURRENT {$weapon -> tag} Quality: {$weapon -> quality}%,
					Min damage: {$weapon -> mindmg}, Max damage: {$weapon -> maxdmg} ", $debug);
					
				$wpn_mindamage = max(1,round($charcopy['char']['weapons']['right_hand']['obj'] -> mindmg 
					* $weapon -> quality / 100,2));
				$wpn_maxdamage = max(1,round($charcopy['char']['weapons']['right_hand']['obj'] -> maxdmg 
					* $weapon -> quality / 100,2));				
					
			}
		}
		
		$charcopy['char']['wpn_mindamage'] = $wpn_mindamage;
		$charcopy['char']['wpn_maxdamage'] = $wpn_maxdamage;
		
		Battle_Engine_Model::battledebug("NEW Weapon Min-Max Damage: {$wpn_mindamage} - {$wpn_maxdamage}", $debug);
		
		// armors
		
		Battle_Engine_Model::battledebug("---- Evalutating Armor ----", $debug);
		
		if ( isset ($charcopy['char']['armors'] ) )
		{
			$processed = array();			
		
			foreach ( $charcopy['char']['armors'] as $part => $equipment )
			{
				Battle_Engine_Model::battledebug("---- Evaluating Part: [{$part}] ----", $debug);
				//kohana::log('debug', "-> Evaluating part {$part}");
				
				foreach ($equipment as $tag => $data )
				{						
					//Battle_Engine_Model::battledebug("Evaluating {$tag} {$data['obj'] -> tag} {$data['obj'] -> quality}");
					
					//if quality < 0, destroy item
					
					if (intval($data['obj'] -> quality) <= 0)
					{
						
						// write and destroy item only once
						if (!isset($processed[$tag]))
						{
							
							$charcopy['char']['destroyeditems'][] = $charcopy['char']['armors'][$part][$tag];
							if (isset($this -> battlereport))
								$this -> battlereport[]['objectbreak'] = '__battle.armorshatters' . ';' . $charcopy['char']['name'] . ';__' . $data['obj'] -> name ;
							$processed[$tag] = true;
						}
						unset( $charcopy['char']['armors'][$part][$tag]);
					}
					else
					{
						$originaldefense = $cfgarmors['armorlist'][$tag]['obj'] -> defense;
					
						/*
						Battle_Engine_Model::battledebug ("Part: {$part}, old {$tag} Quality: {$data['obj'] -> quality}, Defense: {$data['obj'] -> defense}");							
						*/
						
						$data['obj'] -> defense = round( $originaldefense * $data['obj'] -> quality / 100,2);
						
						Battle_Engine_Model::battledebug("Defense of obj [{$data['obj']->tag}]: {$data['obj'] -> defense}", $debug);
						
						$defensebonus = 0;
						
						// check if the character church has the dogma Meditate and Defend
						
						if (isset($charcopy['char']['dogmabonus']['meditateanddefend']) and $charcopy['char']['fightmode'] == 'defend')
						{
							Battle_Engine_Model::battledebug("Applying meditateanddefend bonus", $debug);							
							Battle_Engine_Model::battledebug("Faith Level: {$charcopy['char']['faithlevel']}", $debug);
							$defensebonus = 100;
							$defensebonus *= $charcopy['char']['faithlevel']/100;
						
						}
						
						if (isset($charcopy['char']['dogmabonus']['killinfidels']) and $charcopy['char']['fightmode'] == 'attack')
						{							
							
							Battle_Engine_Model::battledebug("Applying killinfidels bonus", $debug);							
							Battle_Engine_Model::battledebug("Faith Level: {$charcopy['char']['faithlevel']}", $debug);
							$defensebonus = -50;
							$defensebonus *= (100 - ($charcopy['char']['faithlevel']*30/100))/100;							
							
						}
						
						Battle_Engine_Model::battledebug( "Fight Mode: [{$charcopy['char']['fightmode']}]", $debug);						
						Battle_Engine_Model::battledebug( "Defense Bonus: [{$defensebonus}%]", $debug);												
						
						Battle_Engine_Model::battledebug("Defense of obj BEFORE bonus/malus: {$data['obj']->tag}: {$data['obj'] -> defense}", $debug);
						$data['obj'] -> defense	*= ((100 + $defensebonus)/100);						
						Battle_Engine_Model::battledebug("Defense of obj AFTER bonus/malus: {$data['obj']->tag}: {$data['obj'] -> defense}", $debug);
						
						/*
						Battle_Engine_Model::battledebug ("Part: {$part}, new {$tag} Quality: {$data['obj'] -> quality}, Defense: 	{$data['obj'] -> defense}");							
						*/
						
						if (!isset($processed[$tag]))
						{
							$equippedarmorweight += $data['obj'] -> weight;
							$processed[$tag] = true;
						}
						
						
					}
				}
			}
		}
			
		$btw = Character_Model::get_basetransportableweight	( $charcopy['char']['str'] ) ;
		
		$charcopy['char']['armorencumbrance'] = Character_Model::get_armorencumbrance( $btw, $equippedarmorweight ); 		
		Battle_Engine_Model::battledebug("New Armor Encumberance: {$charcopy['char']['armorencumbrance']}%", $debug);
		
	}
	
	/**
	* Seleziona un combattente
	* @param array $fighters Lista fighters
	* @return str key array che identifica il fighter
	*/
	
	function pickfighter( $fighters ) 
	{
		
		$minfights = 999;
		$candidatefighters = array();
		
		// find min fights.
		
		foreach( $fighters as $key => $fighter )
		{			
			if ( $fighter['char']['fights'] <= $minfights )
			{
				$minfights = $fighter['char']['fights'];
			}			
		}		
		
		// put in candidate fighters all the fighter with min fightstats
		
		foreach( $fighters as $key => $fighter )
		{			
			if ( $fighter['char']['fights'] == $minfights )
			{
				$candidatefighters[$key] = $fighter;
			}			
		}				
		
		return array_rand($candidatefighters);
		
	}
	
	/**
	* Lancia la battaglia
	* @param attackers vettore di attaccanti
	* @param defensers vettore di difensori
	* @param type  tipo battaglia
	* @param beaten	vettore che conterrà gli sconfitti
	* @param winners vettore che conterrà quelli rimasti vivi
	* @param report contiene report della battaglia	
	* @param battlestats contiene statistiche
	* @param test indica se è un test
	* @param test indica se è il debug mode è on
	* @return none
	*/
	
	
	function runfight( &$attackers, &$defenders, $type, &$beaten, &$winners, &$report, &$battlestats, $test=false, $debug = false)
	{
		
		$this -> test = $test;
		$this -> debug = $debug;
		$start = microtime(true); 	
		$this -> battlereport = $report; 
		$totalrounds = 0;
		$cycle = 1;
		$tied = 0;
		$energy = $avg_energy = 0; 
		$health = $avg_health = 0 ;
		$this -> cfgarmors = Configuration_Model::get_armorscfg();		
		$this -> cfgweapons = Configuration_Model::get_weaponscfg();				
		
		$a = null;
		$b = null;
		
		$totalfighters = count( $attackers ) + count( $defenders ); 	
		$totalattackers = count( $attackers ); 
		$totaldefenders = count( $defenders ); 
		
		// computa la totale energia e salute per statistiche.
		
		foreach ( $attackers as $key => $attacker )
		{
			
			$energy += $attacker['char']['energy'] ; 
			$health += $attacker['char']['health'] ; 	
			
		}
		
		foreach ( $defenders as $key => $defender )
		{
			$energy += $defender['char']['energy'] ; 
			$health += $defender['char']['health'] ; 			
		}
			
		if ( $totalfighters > 0 )
		{
			$avg_health = $health / $totalfighters;
			$avg_energy = $energy / $totalfighters;
		}
		// FInchè ci sono attaccanti o difensori e non abbiamo superato il massimo dei cicli, loop.
		
		while ( count ($defenders) > 0 and count ($attackers) > 0 and $cycle < self::CYCLES )
		{
			
			kohana::log('info' , '-> Picking next fighters...'); 
			
			//$a_key = array_rand($attackers);
			//$d_key = array_rand($defenders);			
			
			$a_key = $this -> pickfighter($attackers);
			$d_key = $this -> pickfighter($defenders);
			
			$a = $attackers[$a_key];
			$d = $defenders[$d_key];				
			
			kohana::log('info', '====> > > > FIGHT < < < < <=====' ); 
			//kohana::log('info', '-> Cycle: ' . $cycle ); 				
			
			$totalrounds = 0;
			
			if ( !is_null($a) and !is_null($d) )
			{
				
				// check: se tutti i char hanno energy < 0, finiamo la battaglia
				
				$defenderswithenergy=0;
				foreach ( $defenders as $defender )
					if ( $defender['char']['energy'] > 0 )
						$defenderswithenergy++;
				
				$attackerswithenergy=0;
				foreach ( $attackers as $attacker )
					if ( $attacker['char']['energy'] > 0 )
						$attackerswithenergy++;
				
				if ( $defenderswithenergy == 0 and $attackerswithenergy == 0 )
				{
					kohana::log('info', '-> No soldiers with energy, exiting.');
					break;
				}
				
				$winner = 'none';
					
				/*****************************
				* Esegui il combattimento
				*****************************/				
				
				kohana::log('info', "-> {$a['char']['name']}, {$a_key} VS {$d['char']['name']}, {$d_key}");
				
				$a['char']['fights']++;
				$d['char']['fights']++;
						
				$battlestats = array( 
					'battlerounds' => 0,
					'totalhits' => 
						array( 
							$a['char']['name'] => 
								array( 
									'total' => 0,
									'missed' => 0,
									'blocked' => 0,
								),
							$d['char']['name'] => 
								array( 
									'total' => 0,
									'missed' => 0,
									'blocked' => 0,
								),	
							),		
					'totalcriticalhits' => 
						array( 
							'total' => 0,
							$a['char']['name'] => 0,
							$d['char']['name'] => 0,
					),
					'totalstungiven' => 
						array( 
							$a['char']['name'] => 0,
							$d['char']['name'] => 0,
					),
					'totalstunreceived' => 
						array( 
							$a['char']['name'] => 0,
							$d['char']['name'] => 0,
					),
					'totalstunnedrounds' => 
						array( 
							$a['char']['name'] => 0,
							$d['char']['name'] => 0,
					),	
					'totaldamage' => 
						array( 							
							$a['char']['name'] => 0,
							$d['char']['name'] => 0,
					),										
				);
		
				
				$this -> fight( $a, $d, $winner, $battlestats, $cycle);				
				$totalrounds += $battlestats['battlerounds'];
				
				/*****************************
				* Detemina la squadra vincente
				* e quella perdente
				*****************************/     
				
				if ( $winner != 'none' )
				{				

					if ( $winner == $a_key )
					{
						$loserparty = 'defenders';
					}
					else
					{
						$loserparty = 'attackers' ;
					}					
					
					kohana::log('info', '-> Winner is: [' . $winner .']' );						
					kohana::log('info', '-> Loserparty was: [' . $loserparty .']' );
				
								
					/*****************************
					 * Popup il prossimo avversario
					 * del vincente				 
					 *****************************/
						
					if ( $loserparty == 'attackers' )
					{
					
						//kohana::log('debug' , 'att fighter type is: ' . $a['char']['type'] ); 
						//kohana::log('debug' , 'def fighter type is: ' . $d['char']['type'] ); 
						
						// aggiorna statistiche
						
						if ($a['char']['type'] == 'pc' )
						{
							kohana::log('info', "-> modifying stat for: " . $a['char']['obj'] -> name  . ' ( 0, + 1 )' );
							$a['char']['obj'] -> modify_stat( 
								'fightstats', 
								0, 
								null, 
								null, 
								false,
								+1);					
						}
						
						if ($d['char']['type'] == 'pc' )
						{
							kohana::log('info', "-> modifying stat for: " . $d['char']['obj'] -> name  . ' ( +1, + 1 )' );
							$d['char']['obj'] -> modify_stat( 
								'fightstats', 
								+1,  
								null, 
								null, 
								false,
								+1);
						}
						
						kohana::log('info', '-> Removing ' . $a['char']['name'] . ' ' . $a_key . ' from attackers array' );
						
						$beaten[$a_key] = $a;					
						unset ( $attackers [ $a_key ] );
						
						// updating defender array
						kohana::log('info', "-> Replacing data of key {$d_key} - {$d['char']['name']}");
						$defenders[$d_key] = $d;
						
					}

					if ( $loserparty == 'defenders' )
					{
												
						if ($a['char']['type'] == 'pc' )
						{
							kohana::log('info', "-> modifying stat for: " . $a['char']['obj'] -> name  . ' ( +1, + 1 )' );
							$a['char']['obj'] -> modify_stat( 
								'fightstats', 
								+1,  
								null, 
								null, 
								false,
								+1);
							
						}
						
						if ($d['char']['type'] == 'pc' )
						{
							kohana::log('info', "-> modifying stat for: " . $d['char']['obj'] -> name  . ' ( 0, + 1 ) ' );
							$d['char']['obj'] -> modify_stat( 
								'fightstats', 
								0,  
								null, 
								null, 
								false,
								+1);
						}

						kohana::log('info', '-> Removing ' . $d['char']['name'] . ' ' . $d_key . ' from defenders array' );
						$beaten[$d_key] = $d; 
						unset ( $defenders [ $d_key ] );						
						// updating attackers array
						kohana::log('info', "-> Replacing data of key {$a_key} - {$a['char']['name']}");
						
						$attackers[$a_key] = $a;						
						
					}					
				}
				else		
				{
					$tied++;
					
					kohana::log('info', '-> Fight ended tie.'); 										
					
					
					if ( $d['char']['health'] > 0 )
					{
						kohana::log('info', "-> Health: {$d['char']['health']}, putting back soldier into array, replacing key [{$d_key}],[{$d['char']['name']}]");
						$defenders[$d_key] = $d;				
					}
					else
					{
						$beaten[$d_key] = $d;
						kohana::log('info', '-> (health < 0), Removing ' . $d['char']['name'] 
							. ' ' . $d_key . ' from defenders array' );
						unset ( $defenders [ $d_key ] );						
					}
					
					if ( $a['char']['health'] > 0 )
					{
						$attackers[$a_key] = $a;				
						kohana::log('info', "-> Health: {$a['char']['health']}, putting back soldier into array, replacing key [{$a_key}],[{$a['char']['name']}]");
					}
					else
					{
						$beaten[$a_key] = $a;
						kohana::log('info', '-> (health < 0), Removing ' . $a['char']['name'] 
							. ' ' . $a_key . ' from attackers array' );
						unset ( $attackers [ $a_key ] );						
					}
					
					
				}				
								
			}
			
			kohana::log('info' , '-> Defenders Alive: ' . count($defenders) . ' Attackers: Alive ' . count($attackers) );
			
			$cycle++;
			
		}
		
		
		if ( count($defenders ) == 0 ) 
			$report[]['endround'] = '__battle.endrounddefendernotexist';
		
		if ( count($attackers ) == 0 ) 
			$report[]['endround'] = '__battle.endroundattackernotexist';
		
		if ( count($defenders ) == 0 and count($attackers) == 0) 
			$report[]['endround'] = '__battle.endroundnooneexist';
		
			
		// determiniamo i vincitori
		
		/*
		var_dump( " -> Attackers " );
		var_dump( $attackers ); 
		var_dump( " -> Defenders " );
		var_dump( $defenders ); 
		*/
	
		if ( count ($attackers) > count ($defenders) )
		{
			$winners = $attackers;
			$this -> battlereport[]['newline'] = '';
			$this -> battlereport[]['roundresult'] = '__battle.roundresult_attackers_win';		
		}
		
		if ( count ($defenders) > count ($attackers) )
		{
			$winners = $defenders;
			$this -> battlereport[]['newline'] = '';
			$this -> battlereport[]['roundresult'] = '__battle.roundresult_defenders_win';
		}
		
		if ( count ($attackers) == count ($defenders) )
		{
		
			$winners = array_merge($attackers, $defenders);
			$this -> battlereport[]['newline'] = '';
			$this -> battlereport[]['roundresult'] = '__battle.roundresult_tie';
		}
		
		$this -> battlereport[]['newline'] = '';
		$this -> battlereport[]['closeround'] = '------------------------------------------------------------------------' ;
		
		//kohana::log('debug', kohana::debug( $this -> battlereport)); exit(); 
		
		//kohana::log('debug', kohana::debug($this -> report)); exit(); 
		
		//var_dump ($defenders); exit; 
		
		
		$elapsed = ( microtime(true) - $start );
		
		//$fightstats = $battlestats;
		
		$stats = 			
			"<center><h3>Battle Statistics</h3></center>
			<br/>Total fighters: " . $totalfighters . " <br/>" . 
			"Total attackers: " . $totalattackers . " <br/>" . 
			"Total defenders: " . $totaldefenders . " <br/>" . 
			"Total rounds: " . $totalrounds . "<br/>" .			
			"Average rounds: " . $totalrounds / $cycle . "<br/>" .
			"Alive attackers: " . count($attackers) . " <br/>" . 
			"Alive defenders: " . count($defenders) . " <br/>" . 
			"Average Energy: " . $avg_energy . " <br/>" . 
			"Average Health: " . $avg_health . " <br/>" . 
			"Cycles: " . $cycle . " <br/>" .  			
			"Tied: " . $tied . " <br/>" .  
			"Battle Engine elapsed: " . $elapsed . " secs. " ;

			
		// battle champion solo per alcuni tipi di battaglia
				
		if ( in_array( $type, array( 'conquer_r', 'conquer_ir', 'revolt', 'nativerevolt', 'raid' ) ) )
		{
			
			$allsoldiers = array_merge ( $beaten, $winners );
			
			foreach ( $beaten as $beatenc )		
				kohana::log('info', '-> beaten: ' . $beatenc['char']['name']);
				
			foreach ( $winners as $winnerc )		
				kohana::log('info', '-> winner: ' . $winnerc['char']['name']);
			
			if (count($allsoldiers) > 0 )
			{
				foreach ( $allsoldiers as $key => &$record )
				{
				
					
					
					if ( $record['char']['health'] > 0 )
					{
						$record['char']['status'] = 'Alive';
						$allsoldiersstatuses[$key] = 'Alive';
					}
					else
					{
						$record['char']['status'] = 'Dead';
						$allsoldiersstatuses[$key] = 'Dead';
					}
					$allsoldiersfights[$key] = $record['char']['fights'];			
					$allsoldiersnames[$key] = $record['char']['name'];
				}
				
				$maxfights = max( $allsoldiersfights ); 
				kohana::log('info', '-> Maxfights: ' . $maxfights );
				
				array_multisort( 
					$allsoldiersstatuses, SORT_ASC,
					$allsoldiersfights, SORT_DESC, 
					$allsoldiersnames, SORT_ASC, $allsoldiers );
				
				$stats .= "<br/><br/><center><h3>Fought Battles and Battle Champion</h3></center><br/><br/>";		
				$stats .= "<table>";
				$stats .= "<tr><td><b>Name</b></td><td><b>Alive/Dead</b></td><td><b>Fights</b></td></tr>";
			
				$champions = 0;
				$championslist = array();
				foreach ( $allsoldiers as $soldier )
				{
					
					kohana::log('info', "-> Considering soldier: {$soldier['char']['name']}");
					
					if ( 
						$soldier['char']['status'] == 'Alive' and 
						$soldier['char']['fights'] == $maxfights and
						$maxfights > 0 
					)
					{
						kohana::log('info', "-> Setting soldier: {$soldier['char']['name']} as Battle Champion.");	
						$championslist[$soldier['char']['name']] = true;
						$champions ++;
					}
				}
				
				//var_dump($allsoldiers);
				//var_dump($championslist); exit;
				//Only one champion can exist
				
				foreach ( $allsoldiers as $soldier )
				{

					$stats .= '<tr>';					
					$stats .= '<td>' .
					$soldier['char']['name'] . 
					'</td><td>'.
					$soldier['char']['status'] . 
					'</td><td>' .
					$soldier['char']['fights'] . '</b> Fight(s) ';					
						
					if ( array_key_exists($soldier['char']['name'], $championslist)
						and $soldier['char']['type'] != 'npc' 
						and $champions == 1 )
					{
						$stats .= "*** Battle Champion! ***";
						$soldier['char']['obj'] -> modify_stat( 'battlechampion', 1, null, null, false );
					}
					else
						$stats .= '';
				
					$stats .= '</td>
					</tr>';
				}
				
			}
			

			$stats .= '</table>';
			$this -> battlereport[]['simplemsg'] = $stats;
		}
		
		$battlestats['totalcycles'] = $cycle;
		
		
		$report = $this -> battlereport; 

		return;
	
	}	
	
	
	
	/*
	* Mette il char in convalescenza
	* @param obj $defeated (oggetto charclone)
	* @param obj $battle_type (tipo battaglia)
	* @return none
	*/
	
	function put_in_convalescence( $defeated, $battle_type )
	{
		
		if ($defeated['char']['obj']->type == 'npc' )
		{
			$defeated['char']['obj'] -> status  = 'dead';			
			$defeated['char']['obj'] -> modify_health ( 0, true );							
			$defeated['char']['obj'] -> modify_energy ( 0, true, 'recovering' );
			$defeated['char']['obj'] -> save();	
		}
		else
		{
			$defeated['char']['obj'] -> modify_health ( 10, true );							
			$defeated['char']['obj'] -> modify_energy ( 0, true, 'recovering' );
			$defeated['char']['obj'] -> save();	
			$par[0] = $defeated['char']['obj'];			
			$par[1] = $defeated['char']['cost'];					
			$par[2] = $battle_type;
			
			if (isset($defeated['char']['bleeddamage']))
				$par[3] = true;
			else
				$par[3] = false;
			
			$c = Character_Action_Model::factory('recovering');								
			$c -> append_action( $par, $message );									
			
			My_Cache_Model::delete(  '-charinfo_' . $defeated['char']['obj'] -> id . '_currentpendingaction');
		
		}
		
		
		
		
		return;
	
	}
	
	/**
	* Messaggio di debug
	* @input: msg messaggio da stampare
	* @return: none
	*/
	
	function battledebug( $msg, $debug = false) 
	{		
		
		if ( kohana::config('medeur.debugbe') == true or $debug == true )				
		{
			
			kohana::log( 'debug', $msg );			
			
			if ( isset($this -> battlereport ) )
				$this -> battlereport[]['debug'] = '__battle.debug' . ';' . $msg ;		
			
		}
	}
	
	/**
	* Establish how many 	 hits a char can deal
	* @param obj $char Character
	* @return int attacks
	*/
	
	public function get_consecutiveattacks( $char )
	{
		
		//maxencumberance with current armors
		
		
		$maxarmorencumbrance = 93.2;
		$maxweaponweight = $this -> cfgweapons['maxweight']/10000;
		
		// get max weapon weight
		
		
		if ( $char['char']['energy'] <= 0 )
		{
			Battle_Engine_Model::battledebug( $char['char']['name'] . ' has no more energy, 0 consecutive hits.', $this -> debug );
			return 0;
		}
			
		if ( isset( $char['char']['weapons']['right_hand']['obj'] ) )
		{
			Battle_Engine_Model::battledebug( "Weapon Weight:{$char['char']['weapons']['right_hand']['obj'] -> weight}", $this -> debug );
			$weaponreach_normalized = ( $char['char']['weapons']['right_hand']['obj'] -> reach  - 1 )/ (5 - 1); 
			$weaponweight_normalized = ($char['char']['weapons']['right_hand']['obj'] -> weight/1000 - 0)/($this-> cfgweapons['maxweight']/1000-0);
		}
		else
		{
			$weaponreach_normalized = ( 1 - 1 )/ (5 - 1);
			$weaponweight_normalized = (0/1000 - 0)/($maxweaponweight/1000-0);
		}
		Battle_Engine_Model::battledebug( "Armor Encumbrance:{$char['char']['armorencumbrance']}", $this -> debug );
		$armorencumbrance_normalized = ( $char['char']['armorencumbrance'] - 0 )/($maxarmorencumbrance-0);		
		$energy_normalized = ( $char['char']['energy'] - 1 )/ (50 - 1);
		$constitution_normalized = ( $char['char']['cost'] - 1 )/ (Character_Model::get_attributelimit() - 1);
		
		
		// formula attacchi consecutivi nel caso si indossi una arma o si è a mani nudi	
		
		$negativefactors = $weaponweight_normalized + $armorencumbrance_normalized + $weaponreach_normalized;
		$positivefactors = 1.5 * $energy_normalized + 1.5 * $constitution_normalized;
				
		Battle_Engine_Model::battledebug( 
		"{$char['char']['name']} " . 
		" Energy normalized: {$energy_normalized} " . 
		" Const normalized: {$constitution_normalized}" .
		" Total Positive Factors: <b>{$positivefactors}</b>" . 
		" Weapon Weight normalized: {$weaponweight_normalized}" . 
		" Armor Encumbrance normalized: {$armorencumbrance_normalized}" . 
		" Weapon Reach normalized: {$weaponreach_normalized}" .
		" Total Negative Factors: <b>{$negativefactors}</b>", $this -> debug );
		
		
		$attacks = min
		(3, 
			max( 1, 
				round (
					3/max(1, $negativefactors + (3 - $positivefactors * 1.34)), 0
				)
			)
		);
		
		return $attacks;
	}

}
