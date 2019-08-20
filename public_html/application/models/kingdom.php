<?php defined('SYSPATH') OR die('No direct access allowed.');

class Kingdom_Model extends ORM
{
	protected $has_many = array
	(
		'regions',
		'kingdom_taxes',
		'kingdom_title',
		'kingdom_nobletitles'
	);	
    
	/**
	* Trova alleati del regno
	* @param int $kingdom_id ID Regno
	* @return array $allies vettore contenente gli ID dei regni alleati
	*/
	
	function get_allies( $kingdom_id )
	{
		$allies = array();
		$diplomacyrelations = Diplomacy_Relation_Model::get_diplomacy_relations( $kingdom_id );
		foreach ( $diplomacyrelations[$kingdom_id] as $targetkingdom_id => $relationdata )
		{
			
			if ( $relationdata['type'] == 'allied' )
				$allies[] = $targetkingdom_id;
		}
		return $allies;
	}
	
	/**
	* Trova l'ultima guerra combattuta
	* @param int $kingdom_id ID Regno
	* @return array $lastwar ultima guerra combattuta
	*/
	
	function get_last_war( $kingdom_id )
	{
		
		$lastwar = null;
		$kingdomwars = Kingdom_Model::get_kingdomwars( $kingdom_id );
		
		$maxdateendwar = 0;
		foreach ($kingdomwars as $war)
		{			
			if ( $war['war'] -> end > $maxdateendwar )
			{
				$maxdateendwar = $war['war']->end;			
				$lastwar = $war;
			}
		}
		
		return $lastwar;
				
	}
	
	/**
	* Determina se due regni sono in guerra
	* @param int $kingdom1_id ID Regno 1
	* @param int $kingdom2_id ID Regno 2
	* @return boolean
	*/
	
	function are_kingdoms_at_war( $kingdom1_id, $kingdom2_id )
	{
		$arekingdomsatwar = false;
		// prendo tutte le guerre running (in corso)
		$wars = Kingdom_Model::get_kingdomwars( $kingdom1_id, 'running' );
		// se almeno in una tutti e due i regni sono elencati vuol dire che sono in guerra.
		foreach ($wars as $war)
		{			
			if ( 
				array_key_exists( $kingdom1_id, $war['kingdoms']) 
				and 
				array_key_exists( $kingdom2_id, $war['kingdoms']) 
			)
			$arekingdomsatwar = true;
			break;
		}			
		
		return $arekingdomsatwar;
	}
	
	/**
	* Guerre in cui il regno è impegnato, sia come main attacker sia come alleato
	* @param int $kingdom_id ID Regno
	* @param str $status 'all' o 'running' 
	* @return array $kingdomwars Tutte le guerre in cui il regno è coinvolto
	*/
	
	function get_kingdomwars( $kingdom_id, $status = 'all' )
	{
		$allwars = Configuration_Model::get_kingdomswars();
		
		$kingdomwars = array();
		foreach ((array) $allwars as $war)
		{ 
		
			if ( $status == 'running' and $war['war'] -> status != 'running' )
				continue;
			
			if ( array_key_exists( $kingdom_id, $war['kingdoms']))
				$kingdomwars[] = $war;
		}
				
		return $kingdomwars;
		
	}	
	
	/**
	* Find out if kingdom has an achievement
	* @param
	* @return
	*/
	
	public static function get_achievement( $kingdom_id, $name )
	{
		
		
		$cachetag = '-achievement_kingdom_' . $kingdom_id . '_' . $name; 				
		$achievement = My_Cache_Model::get( $cachetag );
				
		if ( is_null( $achievement ) )		
		{
			
			
			$achievement = Database::instance() -> query( 
			"select ca.tag, ca.level, ca.score 
			from  cfgachievements ca, kingdom_titles kt
			where kt.kingdom_id = {$kingdom_id}
			and   kt.cfgachievement_id = ca.id
			and   ca.tag = '{$name}'") -> as_array();
			
			My_Cache_Model::set( $cachetag, $achievement );
		}
	
		return $achievement;
				
	}
	
	/**
	* Get Kingdom Image
	* @param string $dimension image dimension (small|large)
	* @return string image name
	*/
	
	public function get_image ( $dimension = 'small' )
	{
	
		$rec = Database::instance() -> query( 
			"select image from kingdoms_v where id = " . $this -> id ) -> as_array();
		return $rec[0] -> image .'-'.$dimension.'.png';		
	
	}
	
	/**
	* Functions that returns Kingdom name
	* @param int $kingdom_id Id Kingdom
	* @return string Kingdom name	
	*/
	
	public function get_name( $timestamp = null )
	{
		$rec = Database::instance() -> query( 
			"select name from kingdoms_v where id = " . $this -> id ) -> as_array();			
		
		return $rec[0] -> name ;				
	}
	
	
	
	/**
	* Funzione che recupera il nome storico del regno
	* @param none
	* @return nome regno
	*/

	public function get_name2( $kingdom_id )
	{
		$rec = Database::instance() -> query( 
			"select name from kingdoms_v where id = " . $kingdom_id ) -> as_array();			
		
		return $rec[0] -> name ;	
	}
	
	/**
	* Returns Kingdom Tax
	* @param string $name tax name
	* @param int $kingdom_id id Kingdom
	* @return obj Kingdom_Tax_Model
	*/
	
	public function get_tax( $name, $kingdom_id )
	{
	
		$tax = ORM::factory( 'kingdom_tax' ) -> 
			where ( array( 
				'kingdom_id' => $kingdom_id, 
				'name' => $name )) -> find();
		
		if ( !$tax -> loaded )
			return null;
		
		return $tax;

	}
	
	/**
	* Returns correct article for a Kingdom
	* @param none
	* @return string correct article
	* @todo: check if it is used...
	*/
	
	public function get_article()
	{
	
		if ( strstr( $this -> image, 'republic' ) != false ) 
			return 'global.la';
		else
			return 'global.il';
	
	}
	
	/**
	* Return position for a certain stat
	* @param int $kingdom_id ID Kingdom
	* @param string $statname statname
	* @return int position or null if it doesn't exist
	*/
	
	public static function get_position_for_stat( $kingdom_id, $statname )
	{
		
		$res = Database::instance() -> query ( 
			"select position
			from stats_globals
			where type = '{$statname}' 
			and stats_id = {$kingdom_id}") -> as_array();
		
		$cfgkingdoms = Configuration_Model::getcfg_kingdoms();
		
		if ( count($res) > 0 )
		{
			return $res[0] -> position;
		}
		else
			return null;
		
	}
	
	/**
	* Returns Kingdom name with correct article
	* Example of Kingdom of Sicily => del Regno di Sicilia
	* @param none
	* @return string correct article
	* @todo: find out if it is used or duplicate of new_get_article2
	*/
	
	public function get_article2()
	{
		$char = Character_Model::get_info( Session::instance()->get('char_id') ); 
		if ( $char -> user -> language == 'en_US' )
			return 'of ';
		//print strstr( $this -> image, 'stato' ); exit();
		if ( $char->user->language ==	'it_IT' )
		{
			if ( strstr	( $this -> image, 'regno' ) != false )
				return 'del ';
			if ( strstr	( $this -> image, 'ducato' ) != false )
				return 'del ';
			if ( strstr	( $this -> image, 'repubblica' ) != false ) 
				return 'della ';
			if ( strstr	( $this -> image, 'stato' ) != false )
				return 'dello ';
		}
	}
	
	/**
	* Returns Kingdom name with correct article
	* Example of Kingdom of Sicily => del Regno di Sicilia
	* @param boolean $translate translate string or return it raw?
	* @return string correct article
	* @todo: find out if it is used or duplicate of get_article2
	*/
	
	public function new_get_article2( $translate = false)
	{
				
		$article = null;
		
		//var_dump ( $this -> image ); exit; 
		
		if ( strstr	( $this -> image, 'kingdom' ) != false )
			if ( $translate )
				$article = kohana::lang('global.del');
			else
				$article =  '__global.del';				
					
		if ( strstr	( $this -> image, 'duchy' ) != false )			
			if ( $translate )
				$article = kohana::lang('global.del') . ' ' ;
			else
				$article =  '__global.del';		
		if ( strstr	( $this -> image, 'republic' ) != false ) 
			if ( $translate )
				$article = kohana::lang('global.della') . ' ' ;
			else
				$article =  '__global.della';		
		
		return $article;
						
	}
	
	/**
	* Compute the cost for becoming King
	* @param none
	* @return int silver coins needed to become King
	*/
	
	public function get_regent_cost()
	{		
		$cost = $this -> regions -> count() * kohana::config('medeur.kingcostxregion') + kohana::config('medeur.kingcostfixed') ;
		return $cost;
	}
	
	/**
	* Returns correct article
	* Example of to Kingdom of Sicily => al Regno di Sicilia
	* @param none
	* @return string correct article
	* @todo: find out if it is used
	*/
	
	public function get_article3()
	{
		if ( strstr	( $this -> image, 'kingdom' ) != false )
			return 'global.al';
		if ( strstr	( $this -> image, 'duchy' ) != false )
			return 'global.al';
		if ( strstr	( $this -> image, 'principality' ) != false )
			return 'global.al';
		if ( strstr	( $this -> image, 'republic' ) != false ) 
			return 'global.alla';
		if ( strstr	( $this -> image, 'county' ) != false ) 
			return 'global.alla';
		if ( strstr	( $this -> image, 'stato' ) != false )
			return 'global.allo';
		if ( strstr	( $this -> image, 'empire' ) != false )
			return 'global.all';
		if ( strstr	( $this -> image, 'voivodship' ) != false )
			return 'global.al';
	
	}
		
	/**
	* Return data about a structure in the capital.
	* @param string $supertype structure supertype (castle|royalpalace ... )
	* @return obj Structure or null if the structure doesn't exist.
	*/
	
	public function get_structure( $supertype )
	{
		
		// trovo il nodo capitale
		
		$capital = self::get_capitalregion( $this -> id );		
		return $capital -> get_structure( $supertype );
		
	}
	
	/**
	* Return the Regent char
	* @param none
	* @return obj $king Character or null if there is no Regent
	*/
	
	function get_king()
	{
	
		$royalpalace = $this -> get_structure( 'royalpalace' );
		
		$king = ORM::factory( 'character', $royalpalace -> character_id );
		if ( $king -> loaded )
			return $king;
		else 
			return null;
	}
	
	/**
	* Return all the structures of a certain type in a Kingdom
	* @param str $supertype structure supertype (castle|royalpalace ... )	
	* @return Resultset $structures ResultSet list of structures	
	*/
		
	public function  get_structures( $supertype )
	{
	
		$db = Database::instance();
		$structures = $db -> query ( "
		select s.* from structures s, structure_types st 
		where s.structure_type_id = st.id 
		and   st.supertype = '{$supertype}'
		and   s.region_id in 
		( select id from regions where kingdom_id = " . $this -> id . ")" ); 
				
		return $structures;
	}
	
	/**
	* Return the capital region of a Kingdom
	* @param  int $kingdom_id id Kingdom
	* @return obj Region Capital Region
	* @todo: check if it is used
	*/	
	
	function get_capitalregion( $kingdom_id )
	{		
		return ORM::factory( 'region' )-> where ( array( 
			'kingdom_id' => $kingdom_id, 
			'capital' => true )) -> find();
	}
	
	/**
	* Dethrones a King
	* @param none
	* @return none
	*/	
	
	function dethrone_king( )
	{
		$royalpalace = $this->get_structure( 'royalpalace' );
		kohana::log('debug', '- Royalpalace: ' . $royalpalace -> id );
		
		$king = ORM::factory('character', $royalpalace->character_id );
		
		// se non c'è il Re, non c'è niente da fare.
		if ( ! $king -> loaded )
			return;
		
		// termina il ruolo
		$role = $king-> get_current_role();
		if ( !is_null( $role) ) 
			$role -> end ( $king, $royalpalace ) ;	
			
		// evento per town crier
		
		Character_Event_Model::addrecord( 
			null, 
			'announcement',			
			'__events.kingdethroned' . 
			';' . $king->name.						
			';__global.of' .
			';__'.$this->name,
			'evidence'
			);
			
		// evento per il detronizzato
		
		
		Character_Event_Model::addrecord( 
			$king->id, 
			'normal',			
			'__events.kingdethronedkingmessage',
			'evidence'
			);
			
	}
	
	/**
	* Chrown a King
	* @param obj $char Character to be chrowned
	* @return none
	*/	
	
	function crown_king( $char )
	{
		// toglie tutti i ruoli precedenti (governativi e del regno)
		//var_dump($char -> character_roles);exit;
		
		foreach ( $char -> character_roles as $role )
			if ( $role -> gdr == false and $role -> current == true)
				$role -> end();
		
		
		
		$royalpalace = $this -> get_structure( 'royalpalace' );		
		
		// assegna residenza di default		
		
		$char -> region_id = $royalpalace -> region -> id;		
		$char -> save();		
		
		Character_Role_Model::start( $char, 'king', $royalpalace -> region, $royalpalace -> id, null, false );
		
		// eventof per town crier
		
		Character_Event_Model::addrecord( 
			null,
			'announcement',			
			'__events.kingchrowned' . 
			';' . $char -> name.						
			';__global.of' .
			';__'.$this -> name,
			'evidence'
		);
					
		Character_Event_Model::addrecord( 
			$char -> id, 
			'normal',			
			'__events.kingchrownedkingmessage' . 
			';__'. $royalpalace -> region -> kingdom -> get_name() , 					
			'evidence'
			);
	}
	
	/**
	* Returns running battles for the Kingdom
	* @param int $kingdom_id id Kingdom
	* @return array $attacks
	*/
	
	function get_runningbattles( $kingdom_id )
	{
	
		$sql = "select * from battles 
			where ( 
				dest_region_id in ( select id from regions where kingdom_id = " . $kingdom_id  . " ) 
			or
				source_region_id in ( select id from regions where kingdom_id = " . $kingdom_id  . " ) 
			)
			and status in ( 'new', 'running' )
			and type not in ('duel', 'pcvsnpc')";	
			
		$attacks = Database::instance() -> query( $sql ) -> as_array();
		return $attacks;
	
	}
	
	/**
	* load a Kingdom
	* @param int $kingdom_id id Kingdom
	* @return obj $kingdom Kingdom_Model
	*/
	
	public function load( $kingdom_id )
	{
		
		return ORM::factory('kingdom') -> 
			where ( array( 
				'id' => $kingdom_id,
				'status <>' => 'deleted' )) -> find();			
		
	}
	
	/**
	* Controlla se il regno sta combattendo
	* @param int $kingdom_id id Kingdom
	* @param array $data contiene dati addizionali
	* @return boolean
	*/
	
	public function is_fighting( $kingdom_id, &$data = null )	
	{	
		$isonwar = false;
		kohana::log('debug', '-> Checking if kingdom: ' . $kingdom_id . ' is on war.');
		
		$data = array(			
			'reason' => null,
			'battles' => null,
			'attacking' => false,
			'defending' => false
		);
			
		// controllo se ci sono delle dichiarazioni di guerra in atto		
				
		$runningbattles = self::get_runningbattles( $kingdom_id );
		
		if ( count( $runningbattles ) > 0 )
		{			
			$data['battles'] = $runningbattles;
			$data['reason'] = 'runningbattles';						
			foreach ( (array) $data['battles'] as $battle )
			{				
				$attackedregion  = ORM::factory('region', $battle -> dest_region_id );			
				$attackingregion = ORM::factory('region', $battle -> source_region_id );
				if ( $attackedregion -> kingdom_id == $kingdom_id )
					$data['defending'] = true;
				if ( $attackingregion -> kingdom_id == $kingdom_id )
					$data['attacking'] = true;
			}
			$isonwar = true;
		}
		
		kohana::log('debug', '-> Kingdom ' . $kingdom_id . ' is on war: '. $isonwar);
		return $isonwar;

	}
	
	/**
	* Returns if the Kingdom is full 
	* @param none
	* @return boolean Is the Kingdom Full?
	* @todo: check if it is used
	*/
	
	function is_full()
	{
	
		if ( $this -> image == 'kingdom-independent' ) 
			return true; 
			
		$full = true; 
		foreach ( $this -> regions as $region )
		{
			kohana::log('debug', "checking kingdom " . $this -> name . " - " . $region ); 
			if ( $region -> is_full() == false ) 
					$full = false ; 
		}
		kohana::log('debug', "kingdom " . $this -> name . " full: $full " ) ; 
		return $full;		
	}

	/**
	* Returns a region_id with less residents, provided they have a castle and the vassal 
	* @param int $choosenkingdom_id of the kingdom chosen
	* @return int region_id or region capital id if the kingdom don't have region with castle and vassal.
	*/
	
	public static function  get_destination_region( $choosenkingdom_id )
	{
				
		
		$regions = Database::instance() -> query ( "
			select r.id, count(c.id) chars
			from regions r left outer join characters c  on c.region_id=r.id
			where r.kingdom_id = ?
			and r.id  in (
			select r.id from regions r
			left outer join structures s on r.id=s.region_id      
			left outer join structure_types st on st.id = s.structure_type_id 
			where r.kingdom_id = ? and lower(st.type) = 'castle'  and s.character_id is not null)
			group by r.id
			order by chars asc;", $choosenkingdom_id, $choosenkingdom_id ) -> as_array();		
		
		$region_without = Database::instance() -> query ( "
		   	select r.id, count(c.id) chars
			from regions r left outer join characters c  on c.region_id=r.id
			where r.kingdom_id = ?
			and r.id  in (
			select r.id from regions r
			left outer join structures s on r.id=s.region_id      
			left outer join structure_types st on st.id=s.structure_type_id 
			where r.kingdom_id= ?  and lower(st.type) = 'castle'  and s.character_id is null)
			group by r.id
			order by chars asc;", $choosenkingdom_id, $choosenkingdom_id) -> as_array();
			
		$region_nocastle = Database::instance() -> query ( "
		    select r.id, count(c.id) as chars
			from regions r left outer join characters c  on c.region_id=r.id
			where r.kingdom_id = ?
			and r.id not in (
			select r.id from regions r
			left outer join structures s on r.id=s.region_id
			left outer join structure_types st on st.id=s.structure_type_id 
			where r.kingdom_id = ?  and lower(st.type) = 'castle' )
			group by r.id
			order by chars asc;", $choosenkingdom_id, $choosenkingdom_id) -> as_array();	
			
		// if capital is not full, return capital
		
		$capital = Kingdom_Model::get_capitalregion( $choosenkingdom_id );	
		
		kohana::log('debug', '-> Check: assign to capital...');
		
		if ( ! $capital -> is_full() )
			return $capital -> id;		
		
		kohana::log('info', '-> Check: assign to regions with castle and with vassal...');
		foreach ( (array) $regions as $region )
		{
			$_region = ORM::factory('region', $region -> id);
			kohana::log('info', "-> Checking if {$_region -> name} is full...");
			if ( !$_region -> is_full() )
				return $_region -> id;
		}

		// we cycle then on the other regions with castle but not vassal
		kohana::log('info', '-> Check: assign to regions with castle and NO vassal...');
			foreach ((array) $region_without as $region_without )
		{
			$_region_without = ORM::factory('region', $region_without->id);
			kohana::log('info', "-> Checking if {$_region_without -> name} is full...");
			if ( !$_region_without -> is_full() )
				return $_region_without -> id;
		}
		
		// we cycle then on the other regions without castle 
		kohana::log('info', '-> Check: assign to regions without castle...');
		foreach ((array) $region_nocastle as $region_nocastle )
		{
			$_region_nocastle = ORM::factory('region', $region_nocastle->id);
			kohana::log('info', "-> Checking if {$_region_nocastle -> name} is full...");
			if ( !$_region_nocastle -> is_full() )
				return $_region_nocastle -> id;
		}
		
		kohana::log('info', "-> Catch all, sending char to capital...");
		
		// catch all
		
		return $capital -> id;

	}

	
	/**
	* Returns a list of subscribable kingdoms
	* @param none
	* @return array $data list of kingdoms
	* id: Kingdom_Model ID
	* name: Kingdom_Model name
	* image: heraldry image
	*/
	
	function get_subscribable_kingdoms()
	{
		$kingdoms = Database::instance() -> query ( "
			select k.id, k.name, k.image, k.activityscore 
			from kingdoms_v k
			where k.name != 'kingdoms.kingdom-independent' 
			order by k.activityscore desc" ) -> as_array();
		
		$p = 0;
		foreach ($kingdoms as $kingdom )		
		{
			$data['kingdoms'][$kingdom -> id]['id'] = $kingdom -> id;
			$data['kingdoms'][$kingdom -> id]['name'] = $kingdom -> name;
			$data['kingdoms'][$kingdom -> id]['image'] = $kingdom -> image;
			$data['kingdoms'][$kingdom -> id]['activityscore'] = $kingdom -> activityscore;
			$data['kingdoms'][$kingdom -> id]['position'] = $p++;
		}
		$data['totalkingdoms'] = $p-1;
		
		return $data;
			
	}
	
	function compute_avg_heritage ($kingdom_id)
	{
		
	   $db = Database::instance();
	   // Seleziono tutte le regioni del regno
	   $sql = "select k.name kingdomname,
	                  r.name regionname,
	                  r.id region_id,
	                  k.id kingdom_id 
	           from   kingdoms_v k, regions r 
	           where  r.kingdom_id = k.id
	                  and k.id = ".$kingdom_id."
                      and k.name != 'kingdoms.kingdom-independent' 
                      and r.type != 'sea' ";
	   $regions = $db -> query($sql) -> as_array();
	   $citizens = self::get_citizens_count( $this -> id );
	   
	   $totcoins = 0;
	   foreach ($regions as $region)
	   {
	      	$sql = " select ifnull( sum( quantity ), 0 ) value
		             from items 
		             where cfgitem_id = ( select id from cfgitems where tag = 'silvercoin' )
		                   and character_id in ( select id from characters where region_id = " . $region -> region_id . ")";
		    
				$charcoins = $db -> query( $sql ) -> as_array();
		    
		    $sql = " select ifnull( sum( quantity ), 0 ) value
			         from items 
	                 where structure_id in 
		                   ( select id from structures
			                 where structure_type_id in ( select id from structure_types where subtype = 'player' ) 
			                 and region_id = " . $region -> region_id . "			
			                 and character_id in ( select id from characters where region_id = " . $region -> region_id. ") ) ";
		    
				$structcoins = $db->query($sql)->as_array();
		    
		    $totcoins = $totcoins + intval($charcoins[0]->value) + intval($structcoins[0]->value);
	   }
	   return round($totcoins / max($citizens, 1));
	}
	
	/**
	* Torna se il regno ha un re o meno
	* @param kingdom_id id regno
	* @return true o false
	*/
	
	function has_king( $kingdom_id )
	{
	   $db = Database::instance();
	   $sql = "select *
               from character_roles
               where kingdom_id = ".$kingdom_id."
               and tag = 'king'
               and current = 1";
	   $rs = $db->query($sql)->as_array();
	   if ( count( $rs ) > 0 )
		return true;
	   else
		return false;
	}	
	
	/**
	* Torna l'elenco dei cittadini
	* @param int $kingdom_id ID regno
	* @return array elenco cittadini del regno
	*/
	
	function get_citizens( $kingdom_id )
	{
		
		$res = Database::instance() -> query(
		"	select c.id 
			from characters c, regions r
			where c.region_id = r.id 
			and   c.type != 'npc' 
			and   r.kingdom_id = " . $kingdom_id );
	
		return $res -> as_array();
		
	}
	
	/**
	* Torna il numero di residenti di un regno
	* @param int $kingdom_id ID regno
	* @return int numero di abitanti del regno	
	*/
	
	function get_citizens_count( $kingdom_id )
	{
	
		$res = Database::instance() -> query(
		"	select c.id 
			from characters c, regions r
			where  c.region_id = r.id 
			and   c.type != 'npc' 
			and   r.kingdom_id = " . $kingdom_id );
	
		return count($res);		
		
	}
	
	/**
	* Torna il numero di residenti di un regno
	* presenti nel regno
	* @param int $kingdom_id ID regno
	* @return int numero di abitanti del regno	
	*/
	
	function get_citizensinkingdom_count( $kingdom_id )
	{
	
		$res = Database::instance() -> query(
		"	select c.id from characters c
			where region_id in (select id from regions where kingdom_id = " . $kingdom_id . ")  
			and   c.type != 'npc' 
			and   position_id in (select id from regions where kingdom_id = " . $kingdom_id . ")" );  
	
		return count($res);		
		
	}
		
	/**
	* Regioni assegnabili 
	* @param none
	* @return array
	*/
	
	function get_assignableregions()
	{
		$i=0;
		foreach ( $this -> regions as $region )
		{					
			
			$castle = $region -> get_structure('castle'); 
			
			$assignableregions[ $i ]['region'] = $region;
			$assignableregions[ $i ]['responsiblevassal'] = $region -> get_controllingvassal();
			// se il castello è presente la regione non è assegnabile.
			if ( !is_null( $castle ) )
				$assignableregions[ $i ]['assignable'] = false;
			else
				$assignableregions[ $i ]['assignable'] = true;			
			$i++;
		
		}
		return $assignableregions;
	}
	
	
	/**
	* Returns informations about the Kingdom
	* @param none
	* @return array 
	*  - averageage > average age of citizens
	*  - citizens > list of citizens
	*  - citizenscount > number of citizens
	*  - nationalities > nationalities breakdown
	*  - kingdommessage > message of the King
	*  - kingdommessagetitle > title of king message
	*  - kingdomheraldry > path to kingdom heraldry image
	*  - spokenlanguages > spoken languages breakdown
	*  - name > Kingdom name (raw)
	*  - translatedname -> Kingdom name (translated)
	*  - religion > Religion breakdown
	*/
	
	function get_info()
	{
		$db = Database::instance();
		$a = array();
		$info = array(
			'averageage' => 0,
			'citizens' => null,
			'citizenscount' => 0,			
			'nationalities' => null,
			'kingdommessage' => '',
			'kingdommessagetitle' => '',
			'kingdomheraldry' => '',
			'spokenlanguages' => null,
			'name' => '',
			'slogan' => '',
			'translatedname' => '',
			'kingdomheraldry' => '',
			'richestkingdomposition' => 0,
			'populatedkingdomposition' => 0,
			'activekingdomposition' => 0,
			'kingdomscount' => 0,
			'religion' => 
				array ( 
					'atheism' => 
						array( 'nochurch' => 
							array ( 'id' => null, 'total' => 0, 'percentage' => 0 ) ),
					'pagan' => 
						array ( 'turnu' => 
							array ( 'id' => null, 'total' => 0, 'percentage' => 0 ) ),
					'teological' =>
						array ( 'rome' => 
							array ( 'id' => null, 'total' => 0, 'percentage' => 0 ) ),
					'mystical' =>
						array ( 'cairo' => 
							array ( 'id' => null, 'total' => 0, 'percentage' => 0 ) ),
					'patriarchal' =>
						array ( 'kiev' => 
							array ( 'id' => null, 'total' => 0, 'percentage' => 0 ) ),
					'norse' =>
						array ( 'norse' => 
							array ( 'id' => null, 'total' => 0, 'percentage' => 0 ) ),
							) );
			
		// regioni controllate
		
		$res = $db -> query ( "
			select id from regions 
			where kingdom_id = " . $this -> id );
		
		$info['controlledregions'] = count($res);
		
		// trova tutti i cittadini
		
		$sql = "
			select c.*, ch.name church, re.name religion 
			from characters c, churches ch, religions re, regions r, kingdoms_v k 
			where c.region_id = r.id
			and   r.kingdom_id = k.id
			and   c.church_id = ch.id
      and   c.type != 'npc' 			
			and   ch.religion_id = re.id
			and   k.id = " . $this -> id ;		
			
		$res =  $db -> query( $sql );		
		
		$info['citizens'] = $res;		
		$info['citizenscount'] = count($res);
		$king = $this -> get_king();
		$info['name'] = $this -> get_name();
		$info['translatedname'] = kohana::lang($info['name']);
		
		if ( is_null( $king ) )
			$info['kingname'] = '-' ;
		else
			$info['kingname'] = $king -> name;
		
		$info['slogan'] = $this -> slogan;
		
		$totalage = 0;
		foreach ( $res as $row )
		{
			$totalage +=(time() - $row->birthdate) / ( 24 * 3600 );			
			$info['religion'][$row->religion][$row->church]['total']++;			
			$info['religion'][$row->religion][$row->church]['percentage'] = 
			round($info['religion'][$row->religion][$row->church]['total'] / $info['citizenscount'] * 100, 2);
		}
		
		if ( $info['citizenscount'] > 0 )
			$info['averageage'] = round( $totalage / $info['citizenscount'], 0);
		else
			$info['averageage'] = 'N/A';
		
		// setto gli ID della chiesa
		
		$churches = ORM::factory('church') -> find_all();
		foreach ( $churches as $church )
			$info['religion'][$church -> religion -> name ][ $church -> name]['id'] = 
			$church -> id ;
		
		// stats_globals
		$info['richestkingdomposition'] = is_null(Kingdom_Model::get_position_for_stat( $this -> id, 'richestkingdoms')) ? kohana::lang('global.notclassified') : Kingdom_Model::get_position_for_stat( $this -> id, 'richestkingdoms') ;
		$info['populatedkingdomposition'] = is_null(Kingdom_Model::get_position_for_stat( $this -> id, 'populatedkingdoms')) ? kohana::lang('global.notclassified') : Kingdom_Model::get_position_for_stat( $this -> id, 'populatedkingdoms') ;
		$info['activekingdomposition'] = is_null(Kingdom_Model::get_position_for_stat( $this -> id, 'activekingdoms')) ? kohana::lang('global.notclassified') : Kingdom_Model::get_position_for_stat( $this -> id, 'activekingdoms') ;
		$cfgkingdoms = Configuration_Model::getcfg_kingdoms();
		$info['totalkingdoms'] = count($cfgkingdoms);
		
		// cerco la religione prominente
		
		$max = 0;
		
		foreach ( $info['religion'] as $religion => $church )
		{
			
			foreach ( $church as $branch => $value )
			{
							
				if ( $branch != 'nochurch' and $value['total'] >= $max )
				{
					$dominant = $branch;
					$max = $value['total'] ;
				}
			}
		}
		
		$info['dominant'] = $dominant;
		$info['religioninfo'] = 
			kohana::lang('religion.religion-atheism') . 
				': ' . 	$info['religion']['atheism']['nochurch']['percentage'] . '%, ' . 
			kohana::lang('religion.religion-pagan') . 
				': ' . 	$info['religion']['pagan']['turnu']['percentage'] . '%, ' . 
			kohana::lang('religion.religion-mystical') . 
				': ' . 	$info['religion']['mystical']['cairo']['percentage'] . '%, ' . 
			kohana::lang('religion.religion-teological') . 
				': ' .  $info['religion']['teological']['rome']['percentage'] . '%, ' .
			kohana::lang('religion.religion-patriarchal') . 
				': ' . $info['religion']['patriarchal']['kiev']['percentage'] . '%, ' .
			kohana::lang('religion.religion-norse') . 
				': ' . $info['religion']['norse']['norse']['percentage'] . '%';
			
		
		// messaggio informativo
		
		$message = ORM::factory('region_announcement' ) -> where ( array (
			'region_id' => $this -> id,
			'subtype' => 'infomessage' ) ) -> find();
		
		$info['kingmessage'] = $message -> text;
		$info['kingmessagetitle'] = $message -> title;
		
		// Linguaggi Parlati
		
		$info['spokenlanguages'] = 'English' ;
		if ( !is_null( $this -> language1 ) )
			$info['spokenlanguages'] .= ', ' . $this -> language1;
		if ( !is_null( $this -> language2 ) )
			$info['spokenlanguages'] .= ', ' . $this -> language2;
				
		$info['kingdomheraldry'] = url::base() . 'media/images/heraldry/' . $this -> get_image('large');
		// Nazionalità
		
		$res = $db -> query ( "select count(u.id) num , cc.country, cc.code 
		from users u, characters c, regions r, cfgcountrycodes cc
		where u.id = c.user_id
		and   c.region_id = r.id
		and   c.type != 'npc' 
		and   u.nationality = cc.code 
		and   r.kingdom_id = " . $this -> id . "
		group by cc.country 
		order by num desc ") -> as_array();
		kohana::log('debug', "Citizens count: {$info['citizenscount']}");
		
		$a = array();
		foreach ( $res as $r )
		{
			kohana::log('debug', "Citizens count: {$r->num} for country: {$r->country}");
			if ( $info['citizenscount'] == 0  )
			 ;
			else
			 $a[] = $r -> country .': ' . (round($r -> num/$info['citizenscount'],2) *100 ). '%' ;
		}
		if ( $info['citizenscount'] == 0  )
			$info['nationalities'] = '';
		else
			$info['nationalities'] = implode( ', ', $a );
		
		kohana::log('debug', kohana::debug($a));
		
		return $info;
	
	}
	
	
}
