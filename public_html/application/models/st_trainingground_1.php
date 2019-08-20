<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Trainingground_1_Model extends Structure_Model
{

	
	const LEVEL = 0;
	const DUMMIES_LESSONHOURS = 3;
	protected $basecourses = array
	( 'battlepower',
		'battleagility',
		'battleconst'		
	);

	protected $installablecourses = array
	( 
		'defensetechniques_1',		
	);		
	
	// Funzione che costruisce i links relativi
	// @output: stringa contenente i links relativi a questa struttura
	
	public function init()
	{	
		$this -> setCurrentLevel(1);
		$this -> setParenttype('trainingground');
		$this -> setSupertype('trainingground');
		$this -> setMaxlevel(2);
		$this -> setIsbuyable(false);
		$this -> setIssellable(false);				
		$this -> setIsupgradable(true);
		$this -> setHoursfornextlevel(1900);			
		$this -> setNeededmaterialfornextlevel(
			array(
				'iron_piece' => 1000,
				'wood_piece' => 1300,
				'stone_piece' => 1300,
			)
		);;
		$this -> setStorage(10000000);	
		$this -> setWikilink('En_US_TheTraining_Grounds');				
	}
	
	public function build_common_links( $structure )
	{
	
		
		$links = parent::build_common_links( $structure );						
		$links .= html::anchor( "/structure/donate/" . $structure -> id, Kohana::lang('structures_actions.global_deposit'), array('class' => 'st_common_command')) . "<br/>" ;
		$links .= html::anchor( "/structure/info/" . $structure -> id, Kohana::lang('structures_actions.global_info'), array('class' => 'st_common_command')) . "<br/>" ;				
		$links .= html::anchor( "/trainingground/train/" . $structure -> id, Kohana::lang('structures_actions.global_train'), array('class' => 'st_common_command')) . "<br/>" ;
		$links .= html::anchor( "/trainingground/trainwithsparring/" . $structure -> id, Kohana::lang('structures_trainingground.trainwithsparring'), array('class' => 'st_common_command')) . "<br/>" ;
		
		return $links;
	}
	
	public function build_special_links( $structure )
	{
			
		$links = parent::build_special_links( $structure );				
		
		return $links;
	}
	
	function get_level()
	{
		return 1;
	}
	
	/**
	* Train with Sparring Partner
	* @param boolean includere informazioni di debug?
	* @return array $data
	*/
	
	public function trainwithsparring($debug = false, $post)
	{
		
		//var_dump($post);exit;
		
		$startingquality = 100;
		$defeated = array();
		$localbattlereport = array();
		$winners = null;
		$be = new Battle_Engine_Model();
		$fighter1 = $post[ 'fighter1' ]; 
		$fighter2 = $post[ 'fighter2' ]; 
		
		$cfgarmors = Configuration_Model::get_armorscfg();		
		
		$data = array ( 
			'repeats' => $post[ 'repeats' ],			
			'totalrounds' => 0,
			'totalcycles' => 0,
			'totalhits' => 
				array( 
					$fighter1 => 
						array( 
							'total' => 0,
							'missed' => 0,
							'blocked' => 0
						),
					$fighter2 => 
						array( 						
							'total' => 0,
							'missed' => 0,
							'blocked' => 0							
						),	
					),				
			'totalcriticalhits' => 
				array( 
					'total' => 0,
					$fighter1 => 0, 
					$fighter2 => 0 ), 
			'totalstungiven' => 
				array( 
						$fighter1 => 0,
						$fighter2 => 0,
				),
			'totalstunreceived' => 
				array( 
						$fighter1 => 0,
						$fighter2 => 0,
				),
			'totalstunnedrounds' => 
				array( 
						$fighter1 => 0,
						$fighter2 => 0,
				),
			'totaldamage' => 
				array( 
					$fighter1 => 
						array( 
							'total' => 0,							
						),
					$fighter2 => 
						array( 
							'total' => 0,							
						),	
					),					
			'fighter1' => $fighter1,
			'fighter2' => $fighter2,
			'report' => '',
			'wins' => null,			
		);
		
		
		$wins = array(
			$fighter1 => array( 'wins' => 0, 'ties' => 0 ),
			$fighter2 => array( 'wins' => 0, 'ties' => 0 ),
		);
		
		$wins[$fighter1]['wins'] = 0;
		$wins[$fighter2]['wins'] = 0;
		
		// set up attacker
		
		$attacker['char']['key'] = 'NPC-ATTACKER';
		$attacker['char']['fightmode'] = $post['fightmode1'];
		
		if ($attacker['char']['fightmode'] == 'attack')
			$attacker['char']['dogmabonus']['killinfidels'] = true;
		if ($attacker['char']['fightmode'] == 'defend')
			$attacker['char']['dogmabonus']['meditateanddefend'] = true;
		
		$attacker['char']['type'] = 'benpc';		
		$attacker['char']['faithlevel'] = $post['faithlevel1'];
		$attacker['char']['name'] = $fighter1;
		$attacker['char']['transportedweight'] = 0;			
		$attacker['char']['ac'] = 0;			
		$attacker['char']['npctag'] = '';		
		$attacker['char']['energymalus'] = 0;	
		$attacker['char']['stunnedround'] = 0;
		$attacker['char']['bleeddamage'] = 0;
		$attacker['char']['basetransportableweight'] = 0;
		$attacker['char']['encumbrance'] = 0;
		$attacker['char']['equippedweight'] = 0;
		$attacker['char']['fights'] = 0;
		$attacker['char']['staminaboost'] = $post['staminaboost1'];
		$attacker['char']['parry'] = $post['parry1'];
		$attacker['char']['health'] = $post['health1'];		
		$attacker['char']['energy'] = $post['energy1'];
		$attacker['char']['str'] = $post['str1'];
		$attacker['char']['dex'] = $post['dex1'];
		$attacker['char']['intel'] = $post['intel1'];
		$attacker['char']['cost'] = $post['cost1'];
		$attacker['char']['car'] = 1;
		$attacker['char']['parrysuccess'] = 0;
		$attacker['char']['parryfails'] = 0;
		
		$sql = "select c.* , $startingquality quality from cfgitems c where c.id in ( " . 
			$post['weapon1'] . "," .
			$post['armorhead1'] . "," .
			$post['armortorso1'] . "," .
			$post['armorlegs1'] . "," .
			$post['armorfeet1'] . "," .
			$post['armorshield1'] . ")" ;
			
		$res = Database::instance() -> query( $sql ) -> as_array();
		
		
		$equippedweight = 0;		
		foreach ( $res as $row )
		{
			
			if ( $row -> category == 'weapon' and $row -> id != 0 )
			{
				$attacker['char']['weapons'][$row -> part]['obj'] = $row;				
			}
			
			elseif ( $row -> category == 'armor' and $row -> id != 0 )
			{
				foreach ( $cfgarmors['armorlist'][$row -> tag]['coverage'] as $coveredpart )
					$attacker['char']['armors'][$coveredpart][$row->tag]['obj'] = $row;				
			}
			
		}		
		
		//var_dump($attacker['char']['armors']);exit;
		$attacker_equipmentinfo = Battle_Engine_Model::get_fightstats( $attacker, $debug );		
		
		//var_dump($attacker_equipmentinfo);exit;
		// set up defender
		
		$defender['char']['key'] = 'NPC-DEFENDER';
		$defender['char']['type'] = 'benpc';
		$defender['char']['fightmode'] = $post['fightmode2'];
		
		if ($defender['char']['fightmode'] == 'attack')
			$defender['char']['dogmabonus']['killinfidels'] = true;
		if ($defender['char']['fightmode'] == 'defend')
			$defender['char']['dogmabonus']['meditateanddefend'] = true;
		
		$defender['char']['name'] =  $post['fighter2'];
		$defender['char']['faithlevel'] = $post['faithlevel2'];
		$defender['char']['transportedweight'] = 0;			
		$defender['char']['ac'] = 0;			
		$defender['char']['npctag'] = '';
		$defender['char']['energymalus'] = 0;	
		$defender['char']['stunnedround'] = 0;
		$defender['char']['bleeddamage'] = 0;
		$defender['char']['basetransportableweight'] = 0;
		$defender['char']['encumbrance'] = 0;
		$defender['char']['equippedweight'] = 0;
		$defender['char']['fights'] = 0;
		$defender['char']['staminaboost'] = $post['staminaboost2'];
		$defender['char']['parry'] = $post['parry2'];
		$defender['char']['health'] = $post['health2'];
		$defender['char']['energy'] = $post['energy2'];
		$defender['char']['str'] = $post['str2'];
		$defender['char']['dex'] = $post['dex2'];
		$defender['char']['intel'] = $post['intel2'];
		$defender['char']['cost'] = $post['cost2'];
		$defender['char']['car'] = 1;
		$defender['char']['parrysuccess'] = 0;
		$defender['char']['parryfails'] = 0;
		
		$sql = "select c.* ,$startingquality quality from cfgitems c where c.id in ( " . 
			$post['weapon2'] . "," .
			$post['armorhead2'] . "," .
			$post['armortorso2'] . "," .
			$post['armorlegs2'] . "," .
			$post['armorfeet2'] . "," .
			$post['armorshield2'] . ")" ;
		
		// giving equipment
		
		$res = Database::instance() -> query( $sql ) -> as_array();
		
		$equippedweight = 0;		
		foreach ( $res as $row )
		{
			
			if ( $row -> category == 'weapon' and $row -> id != 0 )
			{
				$defender['char']['weapons'][$row -> part]['obj'] = $row;				
			}
			
			elseif ( $row -> category == 'armor' and $row -> id != 0 )
			{
				foreach ( $cfgarmors['armorlist'][$row -> tag]['coverage'] as $coveredpart )
					$defender['char']['armors'][$coveredpart][$row->tag]['obj'] = $row;				
			}
			
		}
		
		//var_dump($defender['char']['armors']);exit;
		
		$defender_equipmentinfo = Battle_Engine_Model::get_fightstats( $defender, $debug );		
		
		//var_dump($attacker);
		//var_dump($defender);exit;
			
		//var_dump( $attackers[0]['char']['dex'] ); 
		//$attackers[0]['char']['dex'] *= 110/100; 
		$repeats = $post['repeats'];
		$starttime = time();
		for( $k = 1 ; $k <= $repeats ; $k ++ )
		{
						
			//reset each time quality of equipment
			
			if ( isset( $attacker['char']['armors'] ) )
			{
				foreach ( $attacker['char']['armors'] as $part => $equipment )
				{					
					foreach ($equipment as $equipmenttag => $obj )				
					{					
						
						$obj['obj'] -> quality = $startingquality;
						$obj['obj'] -> defense = $attacker_equipmentinfo[$part][$equipmenttag];
					}								
				}
			}
			
			if ( isset( $attacker['char']['weapons'] ) )
				$attacker['char']['weapons']['right_hand']['obj'] -> quality = $startingquality;
			
			if ( isset( $defender['char']['armors'] ) )
			{
				foreach ( $defender['char']['armors'] as $part => $equipment )
					foreach ($equipment as $equipmenttag => $obj )				
					{					
						$obj['obj'] -> quality = $startingquality;
						$obj['obj'] -> defense = $defender_equipmentinfo[$part][$equipmenttag];
					}								
			}
			
			if ( isset( $defender['char']['weapons'] ) )
				$defender['char']['weapons']['right_hand']['obj'] -> quality = $startingquality;
						
			$localbattlereport = array();			
			
			//var_dump('-> BETT: Running fight n. ' . $k );
			
			$attackers[$attacker['char']['key']] = $attacker;
			$defenders[$defender['char']['key']] = $defender;
			
			$be -> runfight( $attackers, $defenders, 'duel', $defeated, $winners, $localbattlereport, $battlestats, true, $debug);			
			
			$localbattlereport = Battle_Engine_Model::format_fightreport ( $localbattlereport, 'internal');	
			$data['report'] = Battle_Engine_Model::format_fightreport ( $localbattlereport, 'html');
			
			if ( count($winners) != 1 )
			{
				$wins[$fighter1]['ties'] += 1;
				$wins[$fighter2]['ties'] += 1;
			}			
			else
			{
				$winner = array_shift($winners);			
				$wins[$winner['char']['name']]['wins'] +=1;
			}
			
			//var_dump($battlestats);exit;
			
			$data['totalrounds'] += $battlestats['battlerounds'];
			$data['totalcycles'] += $battlestats['totalcycles'];
			$data['totalcriticalhits']['total'] += $battlestats['totalcriticalhits']['total'];
			
			$data['totalcriticalhits'][$attacker['char']['name']] += $battlestats['totalcriticalhits'][$attacker['char']['name']];
			$data['totalcriticalhits'][$defender['char']['name']] += $battlestats['totalcriticalhits'][$defender['char']['name']];
			
			$data['totalhits'][$attacker['char']['name']]['total'] += $battlestats['totalhits'][$attacker['char']['name']]['total'];
			$data['totalhits'][$attacker['char']['name']]['missed'] += $battlestats['totalhits'][$attacker['char']['name']]['missed'];
			$data['totalhits'][$attacker['char']['name']]['blocked'] += $battlestats['totalhits'][$attacker['char']['name']]['blocked'];
			//$data['totalhits'][$attacker['char']['name']]['successful'] += $battlestats['totalhits'][$attacker['char']['name']]['successful'];			
			
			$data['totalhits'][$defender['char']['name']]['total'] += $battlestats['totalhits'][$defender['char']['name']]['total'];
			$data['totalhits'][$defender['char']['name']]['missed'] += $battlestats['totalhits'][$defender['char']['name']]['missed'];
			$data['totalhits'][$defender['char']['name']]['blocked'] += $battlestats['totalhits'][$defender['char']['name']]['blocked'];
			
			//$data['totalhits'][$defender['char']['name']]['successful'] += $battlestats['totalhits'][$defender['char']['name']]['successful'];
			
			$data['totaldamage'][$attacker['char']['name']]['total'] += $battlestats['totaldamage'][$attacker['char']['name']];
			$data['totaldamage'][$defender['char']['name']]['total'] += $battlestats['totaldamage'][$defender['char']['name']];
			
			$data['totalstungiven'][$attacker['char']['name']] += $battlestats['totalstungiven'][$attacker['char']['name']];
			$data['totalstungiven'][$defender['char']['name']] += $battlestats['totalstungiven'][$defender['char']['name']];
			
			$data['totalstunreceived'][$attacker['char']['name']] += $battlestats['totalstunreceived'][$attacker['char']['name']];
			$data['totalstunreceived'][$defender['char']['name']] += $battlestats['totalstunreceived'][$defender['char']['name']];
			
			$data['totalstunnedrounds'][$attacker['char']['name']] += $battlestats['totalstunnedrounds'][$attacker['char']['name']];
			$data['totalstunnedrounds'][$defender['char']['name']] += $battlestats['totalstunnedrounds'][$defender['char']['name']];
			
			
			
			
			$attackers = null;
			$defenders = null;
		}
		
		$endtime = time();
		
		//kohana::log('debug', kohana::debug($data));
		
		$data['wins'] = $wins;
		$data['elapsed'] = ($endtime - $starttime) ;
		$data['elapsedperround'] = ($endtime - $starttime)/$data['totalrounds'] ;
		
		
		return $data;
	}

}
