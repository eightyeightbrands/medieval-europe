<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Startnativerevolt_Model extends Character_Action_Model
{
	// Azione ad esecuzione immediata
	protected $immediate_action = true;
	
	// Cooldown protezione di un regno:
	// Numero di giorni minimi che devono intercorrere prima
	// che i nativi possano rivoltarsi nuovamente nello stesso regno
	const COOLDOWN = 90;
	
	// Effettua tutti i controlli relativi alla Startnativerevolt, sia quelli condivisi
	// con tutte le action che quelli peculiari della Startnativerevolt
	// @input:   $par        array di parametri
	// @output:  boolean     TRUE = azione disponibile, FALSE = azione non disponibile
	//           $messages   contiene gli errori in caso di FALSE (per riferimento)
	protected function check( $par, &$message )
	{ 
		return true;	
	}

	// Viene eseguita nel caso in cui l'azione non si di tipo immediato
	// La char_action viene collegata al character fino alla sua scadenza
	// In questo caso l'azione è di tipo immediato e quindi la funzione non viene evocata
	protected function append_action( $par, &$message )
	{}
	
	// Funzione che esegue tutte le azioni collegate al completamento della char_action nativerevolt.
	// In questo caso, essendo di tipo immediato, viene richiamata subito
	// @input:   $data       array di parametri
	// @output:  boolean     TRUE / FALSE = azione andata a buon fine
	public function complete_action ( $data ) 
	{
		// Data e ora inizio elaborazione
		$start=time();
		// Numero di query
		$q = 0;
		
		// LOG
		kohana::log('debug','-> Computing Native Attacks: START');

		// Init array
		$candidatedregions = array();
		$kingdoms = array();		
		
		// Seleziono tutte le regioni. Escludo le capitali (che contengono sicuramente
		// un castello) e le regioni indipendenti
        $sql = "
		select    k.name kingdom_name, 
		          k.id kingdom_id, 
		          r.name region_name, 
		          r.id region_id, 
		          k.lastattacked 
		from      regions r, kingdoms_v k
		where     r.kingdom_id = k.id
		and       r.capital = false
		and       k.image != 'kingdom-independent' 			
		and       r.type = 'land'				
		order by  rand()
		";
		
		$regions = Database::instance() -> query ($sql);
		
		// Loop delle regioni selezionate
		foreach ( $regions as $region )
		{
			// LOG
			kohana::log('info', "-> ***** Kingdom: {$region -> kingdom_name} Region: {$region -> region_name } ******");
			kohana::log('info', "-> Kingdom: {$region -> kingdom_name} was attacked last on: " . date( "d-m-Y" , $region -> lastattacked) );
			
			/*****************************************
			CONTROLLI ESCLUSIONE DELLA REGIONE
			******************************************/
			
			// Se il regno a cui appartiene la regione è stato attaccato
			// recentemente, allora la regione viene saltata
			if ( $region -> lastattacked > ( time() - ( self::COOLDOWN * 24 * 3600 ) ) )
			{
				// LOG
				kohana::log('info', '-> Kingdom ' . $region -> kingdom_name . " has been attacked by natives less than " . self::COOLDOWN . " days ago, skipping it.");
				continue;					
			}
						
			// Se il regno non ha un Re, skippa			
			$_region = ORM::factory('region', $region -> region_id);
			$king = $_region -> get_charinrole( 'king' );
			if (is_null($king))
			{
				kohana::log('info', '-> kingdom: ' . $region -> kingdom_name . 'does not have a king, skipping.');
				continue;
			}
			
			// Se la regione possiede un castello allora viene saltata
			
			$castle = $_region -> get_structure('castle', 'type');
			
			if ( !is_null( $castle ) )
			{
				// LOG
				kohana::log('info', '-> Region ' . $region -> region_name . " contains a castle, skipping.");
				continue;
			}
			
			// Se vengono superati tutti i controlli esclusivi, aggiungo la
			// regione all'array dei candidati
			$candidatedregions[$region -> region_name]['region_name'] = $region -> region_name ;
			$candidatedregions[$region -> region_name]['kingdom_name'] = $region -> kingdom_name ;
			$candidatedregions[$region -> region_name]['region_id'] = $region -> region_id ;
			$candidatedregions[$region -> region_name]['score_war'] = 0;
			$candidatedregions[$region -> region_name]['score_distance'] = 0;
			$candidatedregions[$region -> region_name]['score_residents'] = 0;
			$candidatedregions[$region -> region_name]['score_structures'] = 0;
			$candidatedregions[$region -> region_name]['score_total'] = 0;
			
			/*****************************************
			CALCOLO SCORE DELLA REGIONE
			******************************************/
			
			// LOG
			kohana::log('info', '-> Computing Native Attacks for region: ' . $region -> region_name );			
		
			// Determiniamo se il regno è in guerra.
			// LOG
			kohana::log('info', '-> Computing Native Attacks, is kingdom of region: ' . $region -> region_name . ' on war?');
			
			if ( isset( $kingdoms[$region -> kingdom_id] ) )
			{
				$iskingdomfighting = $kingdoms[$region -> kingdom_id]['isonwar'];
			}
			else
			{				
				$data = array();
				$iskingdomfighting = Kingdom_Model::is_fighting( $region -> kingdom_id, $data );
				
				// solo se è in guerra, non valutare piÃ¹ le altre regioni.
				if ( $iskingdomfighting == true )
				{
					$kingdoms[$region -> kingdom_id]['isonwar'] = $iskingdomfighting;
					$kingdoms[$region -> kingdom_id]['data'] = $data;
				}				
				$q += 1;
			}
			// LOG
			kohana::log('info', '-> Is Kingdom on war? [' . $iskingdomfighting . ']' );
			
			// Se il regno è in guerra e si sta difendendo, score negativo
			if ( $iskingdomfighting == true and $kingdoms[$region -> kingdom_id]['data']['defending'] == true )
			{
				//LOG
				kohana::log('info', '-> Kingdom is being attacked, giving negative score: -9999');
				$candidatedregions[$region -> region_name]['score_war'] = -9999;
			}						
			
			// Se per la regione c'è qualche attacco in corso, score negativo
			foreach ( (array) $data['battles'] as $battle )
			{
				if ( $battle -> dest_region_id == $candidatedregions[$region -> region_name]['region_id'] )
				{
					// LOG
					kohana::log('info', '-> Region is being attacked, giving negative score: -9999');
					$candidatedregions[$region -> region_name]['score_war'] += -9999;
				}
			}
			// LOG
			kohana::log('info', '-> Computing Native Attacks, War Score: ' . $candidatedregions[$region -> region_name]['score_war'] );
						
			// Calcolo della distanza dalla capitale in km
			// Trova la capitale per il regno (75-2000)
			if ( isset( $capitals[$region -> kingdom_name] ) )
				;
			else			
				$capitals[$region -> kingdom_name] = Kingdom_Model::get_capitalregion($region -> kingdom_id);
			
			$capital = $capitals[$region -> kingdom_name];				
			$distance = Region_Path_Model::compute_distance( $capital -> name, $region -> region_name);
			// LOG
			kohana::log('info', '-> Computing Native Attacks, Distance score: ' . round($distance * 0.5, 0) );
			
			$candidatedregions[$region -> region_name]['score_distance'] += round($distance * 0.5, 0);
			
			// Score: numero di residenti nel regno
			if ( isset( $_totalkingdomresidents[$region -> kingdom_name] ) )
				;
			else			
				$_totalkingdomresidents[$region -> kingdom_name] = Kingdom_Model::get_citizens_count( $region -> kingdom_id );
						
			$totalkingdomresidents = $_totalkingdomresidents[$region -> kingdom_name];
			
			// Score: numero di residenti nel regno che sono nel regno (0-100)
			if ( isset( $_totalresidentsinkingdom[$region -> kingdom_name] ) )
				;
			else			
				$_totalresidentsinkingdom[$region -> kingdom_name] = Kingdom_Model::get_citizensinkingdom_count( $region -> kingdom_id );
						
			$totalresidentsinkingdom = $_totalresidentsinkingdom[$region -> kingdom_name];
			
			// FIX: Se il regno non ha residenti manda in crash lo script "divide by zero"
			$totalkingdomresidents = max($totalkingdomresidents, 1);
			$candidatedregions[$region -> region_name]['score_residents'] += (100 - round($totalresidentsinkingdom/$totalkingdomresidents*100, 0));
			//kohana::log('debug', '-> Computing Native Attacks, Residents in Kingdom ' . $region -> kingdom_name . ' now: ' . $totalresidentsinkingdom . ', Total Residents in Kingdom: ' . $totalkingdomresidents );

			kohana::log('info', '-> Computing Native Attacks, Residents in Kingdom now score: ' . (100 - round($totalresidentsinkingdom/$totalkingdomresidents*100)));			
			
			$structures = Region_Model::get_structures_d( $region -> region_id );
			foreach ( $structures['government'] as $structures )
				foreach ( $structures as $structure )
				{
					if ( $structure-> supertype == 'court' )
						$candidatedregions[$region -> region_name]['score_structures'] -= 4;
					elseif ( $structure-> supertype == 'barracks' )
						$candidatedregions[$region -> region_name]['score_structures'] -= 3;	
					else					
						$candidatedregions[$region -> region_name]['score_structures'] -= 1;
				}
				
				kohana::log('info', '-> Computing Native Attacks, Structures score: ' . $candidatedregions[$region -> region_name]['score_structures']);
		}
		
		// compute all mins and max
		// and normalize score between 0 and 1.
		
		$_a = $candidatedregions;
		
		$max['score_warmax'] = -9999;
		$max['score_warmin'] = 9999;
		$max['score_distancemax'] = -9999;
		$max['score_distancemin'] = 9999;		
		$max['score_residentsmax'] = -9999;
		$max['score_residentsmin'] = 9999;
		$max['score_structuresmax'] = -9999;
		$max['score_structuresmin'] = 9999;
		
		foreach ( $_a as $key => $row )
		{
			if ( $row['score_war'] >= $max['score_warmax'] )
				$max['score_warmax'] = $row['score_war'];
				
			if ( $row['score_war'] <= $max['score_warmin'] )
				$max['score_warmin'] = $row['score_war'];
							
			if ( $row['score_distance'] >= $max['score_distancemax'] )
				$max['score_distancemax'] = $row['score_distance'];
			if ( $row['score_distance'] <= $max['score_distancemin'] )
				$max['score_distancemin'] = $row['score_distance'];

			if ( $row['score_residents'] >= $max['score_residentsmax'] )
				$max['score_residentsmax'] = $row['score_residents'];
			if ( $row['score_residents'] <= $max['score_residentsmin'] )
				$max['score_residentsmin'] = $row['score_residents'];
			
			if ( $row['score_structures'] >= $max['score_structuresmax'] )
				$max['score_structuresmax'] = $row['score_structures'];
			if ( $row['score_structures'] <= $max['score_structuresmin'] )
				$max['score_structuresmin'] = $row['score_structures'];		
			
		}
		
		//var_dump($_a['regions.vastergotland']);
		
		foreach ( $_a as $key => &$row )
		{
			if ( $max['score_warmax'] == $max['score_warmin'] )
				$row['score_war_normalized'] = 	0.5;
			else
				$row['score_war_normalized'] = 
					($row['score_war'] - $max['score_warmin']) / ($max['score_warmax'] - $max['score_warmin']);		
					
			if ( $max['score_distancemax'] == $max['score_distancemin'] )
				$row['score_distance_normalized'] = 0.5;
			else
				$row['score_distance_normalized'] = 
					($row['score_distance'] - $max['score_distancemin']) / ($max['score_distancemax'] - $max['score_distancemin']);		
			
			if ( $max['score_residentsmax'] == $max['score_residentsmin'] )
				$row['score_residents_normalized'] = 0.5;
			else
				$row['score_residents_normalized'] = 
					($row['score_residents'] - $max['score_residentsmin']) / ($max['score_residentsmax'] - $max['score_residentsmin']);				
					
			if ( $max['score_structuresmax'] == $max['score_structuresmin'] )
				$row['score_structures_normalized'] = 0.5;
			else
				$row['score_structures_normalized'] = 
					($row['score_structures'] - $max['score_structuresmin']) / ($max['score_structuresmax'] - $max['score_structuresmin']);				
						
			$row['score_total'] = 	
				3 * $row['score_war_normalized'] + 
				2 * $row['score_distance_normalized'] +
				1 * $row['score_residents_normalized'] + 
				1 * $row['score_structures_normalized']; 
				
		}
		
		/*
		foreach ($_a as $region)
			kohana::log('debug', kohana::debug($region));
		*/
		
		kohana::log('debug', '-> Start Native Revolt, sorting results...');
		
		foreach ( $_a as $key => $row )
		{	$_regions[$key] = $row['region_name'];
			$_score[$key] = $row['score_total'];
		}
		
		array_multisort($_score, SORT_NUMERIC, SORT_DESC, $_a);		
		$i=0;
		foreach ( $_a as $key => $row )
				{	
			if ( $i > 50 ) 
				break;
			kohana::log('debug', '-> Start Native Revolt, Region: ' . 
				$row['region_name'] . ', score: ' . $row['score_total'] );
			$i++;
		}
		
		//var_dump($candidatedregions['regions.vastergotland']);
		//var_dump($_a['regions.vastergotland']);
		
		/*****************************************
		AVVIO DELLA RIVOLTA
		******************************************/
		//var_dump($_a); exit;
		// Selezione delle regioni da attaccare
		$apocalypse = array();
		// Prima regione da attaccare
		$apocalypse[0] = array_shift($_a);
	
		// Finchè la seconda regione appartiene al regno delle prima, scorro l'array
		$second_region = array();
		$second_region = array_shift($_a);
		//var_dump($second_region['kingdom_name']); exit;
		while ($apocalypse[0]['kingdom_name'] == $second_region['kingdom_name'] )
		{
			$second_region = array_shift($_a);
		}
		$apocalypse[1] = $second_region;
		//var_dump($apocalypse[0]['kingdom_name']); var_dump($apocalypse[1]['kingdom_name']); exit;
		
		kohana::log( 'info', 'First region to attack: '.kohana::debug($apocalypse[0]) );
		kohana::log( 'info', 'Second region to attack: '.kohana::debug($apocalypse[1]) );
		
		$c=0;
		foreach ($apocalypse as $regiontoattack)
		{

			// Carico la regione da attaccare
			$region = ORM::factory('region', $regiontoattack['region_id']);			
			
			// Prelevo il Re del regno
			
			$king = $region -> get_charinrole( 'king' );
			
			if ( is_null( $king) )
				continue;
			
			kohana::log('info','-> Total queries: ' . $q );
			kohana::log('info','-> Attacking Region: ' . $region -> name );
		
			// Gestione notifiche e messaggi
			// ******************************************
		
			// Prelevo il Re del regno
			
			$king = $region -> get_charinrole( 'king' );
			
			// Se è presente invio un evento di notifica al personaggio
			if ( is_null( $king ) )
			{
				kohana::log('debug', '-> computenativeattack: King not found for Region: ' . $region -> name . ', message to the king not sent.' );
			}
			else
			{
				Character_Event_Model::addrecord
				( 
					$king -> id, 
					'normal',
					'__events.nativeattackdeclaration;' . 		
					'__' . $region -> name . ';' . 
					'__' . $region -> kingdom -> name,
					'evidence'
				); 
			}
		
			// Creazione evento nel town crier
			Character_Event_Model::addrecord
			( 
				null, 
				'announcement',
				'__events.nativeattackdeclaration;' . 
				'__' . $region -> name . ';' .
				'__' . $region -> kingdom -> name,
				'evidence'
			);
		
			// Gestione campo di battaglia e NPC
			// ******************************************			
			// Creo il campo di battaglia
			
			$wd = new Battle_Model();
			$wd -> source_character_id = -1;
			$wd -> dest_character_id = $king -> id;
			$wd -> dest_region_id = $region -> id;
			$wd -> source_region_id = -1;
			$wd -> type = 'nativerevolt';
			$wd -> maxattackers = 0;	
			$wd -> status = 'running';			
			$wd -> timestamp = time();
			$wd -> save ();
						
			$br = new Battle_Report_Model();
			$br -> battle_id = $wd -> id;
			$br -> save();
			
			// Creo i ribelli NPC e li assegno al campo di battaglia
			$natives = Battle_Conquer_IR_Model::compute_native_numbers(	count( $region -> kingdom -> regions ) );		
			// LOG
			kohana::log('debug', '-> Regions number: ' . count($region -> kingdom -> regions) ); 
			kohana::log('debug', '-> Natives: ' . $natives );
			
			for ($i = 1; $i <= $natives; $i++ )
			{
				$bp = new Battle_Participant_Model();
				$bp -> id = null;
				$bp -> battle_id = $wd -> id;
				$bp -> character_id = -1;
				$bp -> faction = 'attack';
				$bp -> status = 'alive';
				$bp -> categorization = 'native';
				$bp -> save();
			}	

			// Schedula azione per creare il campo di battaglia
			
			$a = new Character_Action_Model();
			$a -> character_id = -1;
			$a -> action = 'createcdb';
			$a -> blocking_flag = false;
			$a -> cycle_flag = false;
			$a -> status = 'running';
			kohana::log('debug', 'Time: ' . (time() + $c * 600) );
			// separa le battaglie di 10 minuti per
			// distribuire il carico
			$a -> starttime = (time() + $c * 600);
			$a -> endtime = $a -> starttime;
			$a -> param1 = $wd -> id;
			$a -> save ();
			
			// Aggiorno il campo di 'ultima rivolta/attacco' del regno
			$region -> kingdom -> lastattacked = time();
			$region -> kingdom -> save();
			$c++;
		}
		
		/*****************************************
		RISCHEDULA PROSSIMO CHECK DELLA RIVOLTA 62685619200 62008934400 676684800
		******************************************/
		
		$action = ORM::factory('character_action') -> 
			where( array( 
				'character_id' => -1, 
				'action' => 'startnativerevolt' ) ) -> find();
		
		mt_srand();
		$nextdate = ( time() + (((kohana::config('medeur.nativerevoltinterval', 10)) + mt_rand(1,3)) *(24*3600)));	
		$action -> starttime = $nextdate;
		$action -> endtime = $nextdate;
		$action -> save();
		
		// Data e ora fine elaborazione
		$end = time();
		// LOG
		kohana::log('debug', '-> Start Native Revolt, elapsed: ' . (($end - $start)/(3600)) . 'secs');
		
		// Azione completata correttamente
		return true;
	}
}
