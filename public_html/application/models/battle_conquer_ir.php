<?php defined('SYSPATH') OR die('No direct access allowed.');

class Battle_Conquer_IR_Model extends Battle_Type_Model
{
	var $battletype = 'conquer_ir';
	var $par = null;
	var $attackers = array();
	var $defenders = array();
	var $defeated = array();
	var $test = false;
	var $battlefield = null;	
	var $bm = null;	
	var $be = null;
	
	/** 
	* Esegue tutta la battaglia
	* 
	* @param par vettore di parametri	
	* par[0]: obj gruppo che attacca
	* par[1]: obj region da attaccare
	* par[2]: obj battaglia
	* @param test flag di test
	* @return 
	*/
	
	public function run( $par, &$battlereport, $test=false)
	{
		
		// la parte transazionale è omessa perchè questa è l' unica battaglia
		// che accade tramite azione.
		
		$this -> be = new Battle_Engine_Model();		
		kohana::log('debug', '-> Starting Conquer IR battle.' );
		$this -> bm = $par[2];
		$this -> test = $test;
		$this -> par = $par;
		$this -> loadteams();				
		$this -> fight();
		$battlereport = $this -> battlereport;
		
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
		
		// aggiungo i membri del gruppo che sono 
		// nella regione da attaccare (meno quelli in convalescenza...)
		
		$group_members = $this -> par[0] -> get_all_members( 'joined', $this -> par[0] -> id ); 		
		foreach ( $group_members as $group_member )
		{			
			
			if ( 
				$group_member -> character -> position_id == $this -> par[0] -> character -> position_id 
				and Character_Model::is_recovering( $group_member -> character -> id ) === false
				and Character_Model::is_resting( $group_member -> character -> id ) === false 
				and $group_member -> character -> get_age() > kohana::config('medeur.mindaystofight', 30)
				)
			{
				kohana::log('info', '-> Loading fighter: ' .  $group_member -> character -> name ); 
				$attacker = $this -> be -> loadcharbattlecopy( $group_member -> character_id );
				$attacker['fights'] = 0;
				$attackers[$attacker['char']['key']] = $attacker;				
				
			}
			
		}		
		
		// metti il capitano di default		
		$captain = $this -> be -> loadcharbattlecopy( $this -> par[0] -> character -> id );
		$attackers[$captain['char']['key']] = $captain;
		
		// creo nativi...
		// cerco quante regioni ha il regno dell' organizzatore del gruppo
		
		$db = Database::instance();
		$sql = "select count(*) regions 
		from regions where kingdom_id = " .  $this -> par[0] -> character -> region -> kingdom -> id ; 
		
		$res = $db -> query( $sql ); 
		$nregions = $res[0] -> regions ;		
		
		kohana::log('debug', 'regions: ' . $nregions ); 
		$nativenumbers = $this -> compute_native_numbers ( $nregions ) ;
		kohana::log('debug', 'natives: ' . $nativenumbers ); 
		$pointstodistribute = intval($this -> compute_native_stats ( $nregions 	) );
		kohana::log('debug', 'points to distribute: ' . $pointstodistribute ); 
		
		// creo nativi
		
		for ($i=1;$i<=$nativenumbers;$i++)
		{
			kohana::log('debug' , '-> *** Native: ' . $i . '***' );
			
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
			$native['char']['weapons']['right_hand']['obj'] = null ; 
			$native['char']['fightmode'] = 'normal';
			$native['char']['faithlevel'] = 0;
		
			/////////////////////////////////
			// Arma gli NPC
			/////////////////////////////////
			
			$weapon = null;
			
			$r = mt_rand(0,8);
			switch ( $r )
			{
				case 0: $weapon = 'knife'; break;
				case 1: $weapon = 'knife'; break;
				case 2: $weapon = 'knife'; break;
				case 3: $weapon = 'knife'; break;
				case 4: $weapon = 'knife'; break;
				case 5: $weapon = 'knife'; break;
				default: break;
			}
			
			if ( !is_null( $weapon ) )
			{
				$db = Database::instance();
				$sql = "select c.* ,100 quality from cfgitems c where c.tag = '" . $weapon . "'" ; 		
				$resw = $db -> query( $sql ); 							
				kohana::log('debug' , '-> Weight of weapon is: ' . $resw[0] -> weight );
				$native['char']['weapons']['right_hand']['obj'] = $resw[0] ; 
				kohana::log('debug' , '-> Weight Before Weapon: ' . $native['char']['equippedweight'] ); 
				$native['char']['equippedweight'] += ($resw[0] -> weight);
				kohana::log('debug' , '-> Weight After Weapon: ' . $native['char']['equippedweight'] ); 
			}
			
			
			/////////////////////////////////
			// Distribuisci stats
			/////////////////////////////////
			
			$stats = $this -> distribute_stats( $points ); 
			
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
			
			kohana::log('debug' , '-> Native equippedweight: ' . $native['char']['equippedweight'] );
			kohana::log('debug' , '-> Native btw: ' . $native['char']['basetransportableweight'] ); 
			kohana::log('debug' , '-> Native armorencumbrance: ' . $native['char']['armorencumbrance'] ); 
					
			//exit;
			$native['fights'] = 0;
			$defenders[$native['char']['key']] = $native;
			
		}
		
		
		$this -> attackers = $attackers;
		$this -> defenders = $defenders;
		
		//kohana::log('debug', kohana::debug( $attackers )); 	exit();
		//kohana::log('debug', kohana::debug( $defenders )); 	exit();
		
	}
	
	/** 
	* calcola i numeri di nativi
	* 
	* @param n. regioni	
	* @return n. nativi
	*/
	
	public function compute_native_numbers( $nregions )
	{		
		
		kohana::log('debug', 'computing... called function with parameter: ' . $nregions ); 			
		
		if ( $nregions == 1 )
			return 10; 
			
		kohana::log('debug', 'computing ... calling function with parameter: ' . ($nregions - 1) ); 
		
		$n = round( (Battle_Conquer_IR_Model::compute_native_numbers( $nregions - 1 ) + pow( $nregions , 0.3 )), 0 );
		
		kohana::log('debug', 'computed ... natives: ' . $n ); 
		
		return $n;
	}
	
	/** 
	* Combatte
	* 
	* @param none
	* @return none
	*/
	
	public function fight()
	{
		
		$this -> battlereport[]['battleround'] = '__battle.conqueririntroduction' . ';'  . 
			'__' . $this -> par[1] -> name . ';' . 
			Utility_Model::format_datetime( time() );		
		
		
		$this -> compute_bonusmalus();				
		
		kohana::log('info', '-> Conquer IR: Fight.' );
		
		$this -> be -> runfight( 
			$this -> attackers, 
			$this -> defenders, 
			'conquer_ir', 
			$this -> defeated, 
			$winners, 
			$this -> battlereport, 
			$this -> fightstats,
			$this -> test );
		
		kohana::log('info', '-> Conquer IR: Handling alive players.' );
		
		$this -> handle_alive( );
		
		kohana::log('info', '-> Conquer IR: Handling defeated players.' );
		
		$this -> handle_defeated( ); 
		
		kohana::log('info', '-> Conquer IR: Doing aftermath.' );		
		
		$this -> do_aftermath( );		
		
		kohana::log('info', '-> Conquer IR: End.' );
	
	}
	
	/** 
	* Calcola il totale statistiche da distribuire
	* 
	* @param n. regioni	
	* @return n. nativi
	*/
	
	public function compute_native_stats( $nregions )
	{		
		
		if ( $nregions == 1 )
			return 35; 
		
		$n = intval($this -> compute_native_stats( $nregions - 1 ) + pow( $nregions , 0.3 )); 
		return $n;
	}
	
	/** 
	* Verifica se il capitano può attaccare
	* 
	* @param $group
	* @return none
	*/
	
	function iscaptainallowedtoattack( $group )
	{
		// controlliamo se il capitano ha con sè un ordine del re per 
		// attaccare questa regione e se è ancora valido.				
		
		$db = Database::instance();
		$sql = "select i.* from items i, characters c, cfgitems ci
		where i.character_id = " . $group -> character -> id . " 
		and   i.cfgitem_id = ci.id
		and   ci.tag = 'scroll_conquerirorder'";
		
		$conquerorders = $db -> query( $sql ); 
		$allowed = false;

		
		foreach ( $conquerorders as $conquerorder )
		{
			list( $king_id, $captain_id, $captain_name , $region_id, $region_name, $expiry_date ) = explode( ';' , $conquerorder -> param1 );

			kohana::log('debug', '-> Conquer Order Expiry_date: ' . date( 'd-m-y', $expiry_date ) . ' Region_name ' . $region_name ); 
			kohana::log('debug', '-> region_id: ' . $region_id . ' captain position id: ' . $group -> character -> position_id ); 
			
			if ( $region_id == $group -> character -> position_id and $expiry_date > time() )
			{ $allowed = true; break; }
		}
		return $allowed;
	}
	
	/** 
	* Aftermath della battaglia
	* 
	* @param none
	* @return none
	*/
	
	function do_aftermath()
	{
	
		///////////////////////////
		// stabilisco il vincitore
		///////////////////////////
		
		$attackerwins = $defenderwins = 0;		
		$winners = 'none';
		
		if ( count( $this -> attackers) > count( $this -> defenders ) )
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
		
			$this -> par[1] -> move( $this -> par[0] -> character -> region -> kingdom );
		
			Character_Event_Model::addrecord( 
				null,
				'announcement', 
				'__events.conquerirsuccess' . ';' .
				'__' . $this -> par[0] -> character -> region -> kingdom -> name . ';' . 
				'__' . $this -> par[1] -> name . ';' . 
				html::anchor('page/battlereport/' . $this -> bm -> id, '[report]'),
				'evidence'
				);
		}
		
		if  ($winners == 'defenders' or $winners == 'none' )
		{
			Character_Event_Model::addrecord( 
				null,
				'announcement', 
				'__events.conquerirfailure' . ';' .
				'__' . $this -> par[0] -> character -> region -> kingdom -> name . ';' . 					
				'__' . $this -> par[1] -> name . ';' . 
				html::anchor('page/battlereport/' . $this -> bm -> id, '[report]'),
				'evidence' ); 
		}		
		
		//////////////////////
		// save battle entry
		//////////////////////
		
		$this -> completebattle( 1, $attackerwins, $defenderwins );
		
	}
	
	/**
	* Distribuisce il totale dei punti tra
	* le principali stat
	* @param $total totale punti da distribuire
	* @return array con stat distribuite
	*/
	
	function distribute_stats ( $total )
	{
	
		// sistemo la strength
		$r = rand(1,20);
		$a['str'] = $r;
		$total -= $r;

		while ( $total - ($r = rand( 1, 20 )) < 3 ) ; 	
		$a['dex'] = $r; 
		$total -= $r;

		while ( $total - ($r = rand( 1, 20 )) < 2 ) ; 	
		$a['cost'] = $r; 
		$total -= $r;

		while ( $total - ($r = rand( 1, 20 )) < 1 ) ; 	
		$a['intel'] = $r; 
		$total -= $r;

		$a['car'] = min($total, 20);
		
		$total -= $a['car'];
		
		if ( $total > 0 and 20 - $a['str'] > $total )
		{
			$a['str'] += $total ;
			$total = 0; 
		}

		if ( $total > 0 and 20 - $a['dex'] > $total )
		{
			$a['dex'] += $total ;
			$total = 0; 
		}

		if ( $total > 0 and 20 - $a['cost'] > $total )
		{
			$a['cost'] += $total ;
			$total = 0; 
		}

		if ( $total > 0 and 20 - $a['intel'] > $total )
		{
			$a['intel'] += $total ;
			$total = 0; 
		}
		
		
		//echo $a['str'].'-'.$a['dex'].'-'.$a['const'].'-'.$a['intel'].'-'.$a['car'] . ' = ' . ($a['str']+$a['dex']+$a['const']+$a['intel']+$a['car']) . "\r\n";
		return $a; 
	}

}
