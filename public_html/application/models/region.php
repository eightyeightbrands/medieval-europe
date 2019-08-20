<?php defined('SYSPATH') OR die('No direct access allowed.');

class Region_Model extends ORM
{
	protected $belongs_to = array('kingdom');
	protected $has_many = array('region_paths', 'structures', 'region_taxes', 'region_prices', 'laws', 'character_roles', 'characters', );

	const MAX_FIELD_PER_REGION = 100; // Numero field max per citt�
	const TERRAIN_BASE_VALUE   = 100; // Valore monetario di base del terreno in game

	const MAXCITIZENS_PLAINS = 50;
	const MAXCITIZENS_HILLS = 40;
	const MAXCITIZENS_MOUNTAINS = 30;
	const MAXCITIZENS_CAPITAL = 50;


	/**
	* Torna informazioni sui terreni disponibili in una regiona.
	* @param obj $region regione dove si compra il terreno
	* @return array $terrains_info
	*   - max_terrains_x_region
	*   - terrains_taken
	*   - terrains_free
	*/

	public function get_terrains_info( $region )
	{

		$terrains_info = array();
		$terrains_info['max_terrains_x_region'] = ST_Terrain_Model::get_maxfield_x_region ( $region );
		$takenterrains = Database::instance() -> query ( "
			select * from structures s, structure_types st
			where s.structure_type_id = st.id
			and   s.locked = false
			and   s.region_id = " . $region -> id . "
			and   st.parenttype = 'terrain' " );

		$terrains_info['terrains_taken'] = $takenterrains -> count();
		$terrains_info['terrains_free'] = $terrains_info['max_terrains_x_region'] - $terrains_info['terrains_taken'];

		if ($terrains_info['terrains_free'] < 0)
			$terrains_info['terrains_free'] = 0;

		return $terrains_info;
	}

	/*
	* Carica lista dei char nella regione
	* @param int id regione
	*/

	function get_characteractivity( $region_id )
	{

		$data = array();

		$rset = Database::instance() -> query( "
		select c.id, c.name character_name, k.name kingdom_name, c.type character_type, c.status character_status
		from characters c, regions r, kingdoms_v k
		where c.region_id = r.id
		and   k.id = r.kingdom_id
		and   position_id = $region_id");
		$i = 0;

		foreach ($rset as $presentchar )
		{

			if ($presentchar -> character_type == 'npc' and $presentchar -> character_status == 'dead' )
				continue;
			$data[$i]['character_id'] = $presentchar -> id;
			$data[$i]['character_name'] = $presentchar -> character_name;
			$data[$i]['kingdom_name'] = $presentchar -> kingdom_name;
			$data[$i]['activity'] = Character_Model::get_currentpendingaction( $presentchar -> id );
			$i++;
		}
		//var_dump($data); exit;
		return $data;

	}

	/**
	* Load region configuration from cache
	* @param none
	* @return array with region info (id, name, type, image, kingdom_name, kingdom_id, geography, clima)
	*/

	public function get_regions_info()
	{
		$cachetag = '-regionsinfo';
		$regionsinfo = My_Cache_Model::get( $cachetag );

		if ( is_null( $regionsinfo ) )
		{

			$sql = "select
				r.id, r.name, r.type, k.image, k.name kingdom_name, k.id kingdom_id, k.color kingdom_color, r.geography, r.clima
				from 	regions r, kingdoms k
				where r.kingdom_id = k.id
			";
			$regionsinfo = Database::instance() -> query( $sql ) -> as_array();
			My_Cache_Model::set( $cachetag, $regionsinfo );

		}

		return $regionsinfo ;

	}


	/**
	* Returns available terrains for the region
	* @param none
	* @return Int available terrains for the region
	*/

	public function get_free_terrains()
	{
		$terrains_taken = $this -> get_region_terrains();
		$max_terrains = $this -> get_maxfield_x_region();
		return ($max_terrains - $terrains_taken);
	}

	/**
	* Torna lista di personaggi presenti o residenti
	* @param: int $region_id id Regione
	* @param  int $kingdom_id id Regno
	* #param: str tipo di query: regionpresentchar|regionresidentchars
	* @return: array $data
	*/

	public function get_chars( $region_id, $kingdom_id, $kind = 'regionpresentchars' )
	{

		$data = array(
			'list' => null,
			'npc' => 0,
			'pc' => 0);

		$db = Database::instance();

		if ( $kind == 'regionpresentchars' )
		{
			$chars = $db->query ( "
			select *
			from characters c
			where c.position_id = {$region_id} and c.status is null
			");
		}

		if ( $kind == 'regionresidentchars' )
		{
			$chars = $db->query ( "
			select *
			from characters c
			where c.region_id = {$region_id} and c.status is null
			and type != 'npc'
			");
		}

		kohana::log('debug', $chars -> count() . " chars found in region: {$region_id}. ");

		// build data array
		$npc = $pc = 0;

		if ( $chars -> count() > 0 )
		{
			$i=0;
			foreach( $chars as $char )
			{
				if ($char -> status != 'dead' )
				{
					if ($char -> type == 'npc' )
					{
							$npc++;
							$data['list'][$i]['online'] = true;
					}
					else
					{
						$pc++;
						$data['list'][$i]['online'] = Character_Model::is_online($char->id);
					}

					$data['list'][$i]['char'] = $char;

					$i++;
				}
			}
		}

		$data['npc'] = $npc;
		$data['pc'] = $pc;

		return $data;

	}


	/**
    * Loads region taxes info
	* @param region id
	* @return Object result set
	*/

	public function get_taxes( $region_id )
	{

		$taxes = ORM::factory('region_tax') -> where ( 'region_id' , $region_id ) -> find_all();
		return $taxes ;
	}


	/**
	* Torna la struttura in una regione
	* @param string $supertype Structure Super Type
	* @return: Structure_Model $structure o null se non trovata
	*/

	public function get_structure( $supertype )
	{

		$structure = Database::instance()
		-> query(
			"SELECT s.id
			 FROM  structures s, structure_types st
			 WHERE s.structure_type_id = st.id
			 AND   s.region_id = {$this -> id}
			 AND   st.supertype = '{$supertype}'")
		-> as_array();

		if ( count($structure) == 0 )
			return null;

		$structure = StructureFactory_Model::create( null, $structure[0] -> id );

		return $structure ;

	}


	/**
	* Torna il personaggio nel ruolo
	* @param string $roletag TAG ruolo (king, ecc)
	* @return Character_Role_Model personaggio nel ruolo o NULL se non trovato.
	*/

	public function get_roledetails( $roletag )
	{
		// ruoli gestiti dal re

		$rolematrix = array
			(
				'kingdom' => array (
					'king',
					'chancellor',
					'seneschal',
					'constable',
					'chamberlain',
					'treasurer',
					'ambassador',
					'chaplain' ),
			);

		if ( in_array( $roletag, $rolematrix['kingdom'] ) )
			$role = ORM::factory ('character_role' ) ->
				where( array(
					'tag' => $roletag,
					'kingdom_id' => $this -> kingdom -> id,
					'current' => 1)) -> find();
		else
			$role = ORM::factory ('character_role' ) -> where(
				array(
					'tag' => $roletag,
					'region_id' => $this -> id,
					'current' => 1)) -> find();

		if ( ! $role->loaded )
			return null;

		return $role;
	}
	/**
	* Torna il vassallo che controlla la regione
	* @param nessuno
	* @return obj character_Modelchar del vassallo
	*/

	public function get_controllingvassal()
	{
		$controllingcastle = $this -> get_controllingcastle();
		return $controllingcastle -> character;
	}


	/**
	* Torna la regione che controlla
	* @param none
	* @return obj struttura region
	*/

	public function get_controllingregion()
	{

		$controllingcastle = $this -> get_controllingcastle();
		return $controllingcastle -> region;

	}

	/**
	* Torna la struttura castello che controlla la regione
	* @param nessuno
	* @return struttura o null se non trovata
	*/

	public function get_controllingcastle()
	{
		// se nella regione c'� una struttura castle, allora �
		// controllata da essa. Altrimenti, � la parent structure id
		// del native village.

		$controllingcastle = $this -> get_structure('castle');

		if ( !is_null( $controllingcastle ) )
			return $controllingcastle;

		$nativevillage = $this -> get_structure('nativevillage');

		$controllingcastle = ORM::factory('structure',
			$nativevillage -> parent_structure_id );

		return $controllingcastle;

	}

	/**
	* Torna la struttura del palazzo reale che controlla la regione
	* @param nessuno
	* @return struttura
	*/

	public function get_controllingroyalpalace()
	{
		$royalpalace = $this -> get_structure('royalpalace');

		if ( !is_null( $royalpalace ) )
			return $royalpalace;

		$capitalregion = Kingdom_Model::get_capitalregion($this -> kingdom_id);
		$controllingroyalpalace = $capitalregion -> get_structure('royalpalace');

		return $controllingroyalpalace;

	}

	/**
	* Returns char with the specified RP role
	* @param $roletag  role tag (chancellor|...)
	* @return $chars array of Character
	*/

	public function get_charinrolegdr( $roletag )
	{
		$chars = array();

		$roles = ORM::factory('character_role') -> where
		( array(
			'tag' => $roletag,
			'current' => 1,
			'gdr' => true,
			'kingdom_id' => $this -> kingdom_id ) ) -> find_all();

		foreach ( $roles as $role )
			$chars[] = $role -> character ;

		return $chars;
	}

	/**
	* Returns char with the specified role
	* @param $roletag  role tag (king|vassal|...)
	* @return mixed
	* $obj Character
	* null if no character has the role;
	*/

	public function get_charinrole( $roletag )
	{
		$char = null;

		if ( $roletag == 'vassal' )
		{
			$char = $this -> get_controllingvassal() ;
		}

		if ( $roletag == 'king' )
		{
				$royalpalace = $this -> get_controllingroyalpalace();
				if (!is_null($royalpalace))
					$char = $royalpalace -> character;
		}
		else
		{
			$controlledstructures =
				Character_Role_Model::get_controlledstructurestags( $roletag );

			$structure = $this -> get_structure ( $controlledstructures[0] );

			if ( !is_null( $structure ) )
			{
				$char = $structure -> character;
			}
		}

		return $char;

	}


	/**
	* Trova la tassa
	* @param region_id id regione
	* @param name nome della tassa
	* @param param1 parametro di ricerca
	* @return oggetto tax
	*/

	public function get_tax( $region_id, $name, $param1 = null )
	{
		if ( is_null( $param1 ) )
			$tax = ORM::factory('region_tax') -> where
				( array( 'region_id' => $region_id, 'name' => $name)) -> find();
		else
			$tax = ORM::factory('region_tax') -> where
				( array(
					'region_id' => $region_id,
					'name' => $name,
					'param1' => $param1,
					)) -> find();

		return $tax;

	}

	/**
	* Trova la tassa applicabile per
	* il char
	* @param region oggetto regione
	* @param name nome della tassa
	* @param character oggetto char
	* @return int % da applicare
	*/

	public function get_appliable_tax( $region, $name, $character )
	{

		$tax = $region -> get_tax( $region -> id, $name );

		// � cittadino del regno?
		if ( $character -> region -> kingdom_id == $tax -> region -> kingdom_id )
			return $tax -> citizen;
		//var_dump( $tax ); exit;
		// trovo la relazione diplomatica
		$dr = Diplomacy_Relation_Model::get_diplomacy_relation(
			$region -> kingdom_id,
			$character -> region -> kingdom_id
			);



		if ( $dr['type'] == 'hostile' ) return $tax -> hostile;
		if ( $dr['type'] == 'neutral' ) return $tax -> neutral;
		if ( $dr['type'] == 'friendly' ) return $tax -> friendly;
		if ( $dr['type'] == 'allied' ) return $tax -> allied;

	}

	/**
	* trova i progetti comunitari startabili per la regione
	* @param none
	* @return array di oggetti cfg_kingdomproject startabili per il nodo.
	*/

	function get_startableprojects()
	{



		$allprojects = ORM::factory('cfgkingdomproject')-> find_all();
		$startableprojects = array();

		foreach ( $allprojects as $project )
		{
			kohana::log('debug', 'checking project: ' . $project -> name);

			if ( $project -> is_startable( $this  ) )
			{
				kohana::log('debug', 'adding project: ' . $project -> name);
				$startableprojects[] = $project;
			}
		}

		return $startableprojects;

	}

	/**
	* Verifies if a region is at full capacity
	* @param: none
	* @return: boolean true: is full
	*/

	function is_full()
	{


		$citizens = $this -> get_chars( $this -> id, $this -> kingdom_id, 'regionresidentchars');


		if ($this -> capital)
		{
			if ($citizens['pc'] > self::MAXCITIZENS_CAPITAL)
				$status = true;
			else
				$status = false;
		}
		else
		{
			switch ( $this -> geography )
			{

				case 'hills' : if ( $citizens['pc'] >= self::MAXCITIZENS_HILLS ) $status = true; else $status = false ; break;
				case 'mountains' : if ( $citizens['pc'] >= self::MAXCITIZENS_MOUNTAINS ) $status = true; else $status = false ; break;
				case 'plains' : if ( $citizens['pc'] >= self::MAXCITIZENS_PLAINS ) $status = true; else $status = false ; break;
				default: return false; break;
			}
		}

		kohana::log('debug', '-> region: ' . $this -> name . ' full: ' . $status ) ;

		return $status;

	}

	/**
	* Verifica se la regione � indipendente
	* @param: none
	* @return: False o true
	*/

	function is_independent()
	{
		if ( $this -> kingdom -> image == 'kingdom-independent')
			return true;
		else
			return false;
	}

	/**
	* trova i progetti comunitari che sono in corso
	* @param $role role
	* @return array di oggetti kingdomproject che sono in corso
	*/

	function get_runningprojects( $role )
	{
		$db = Database::instance();

		if ( $role == 'church_level_1' )
			$condition = " owner in ('church_level_1', 'church_level_2', 'church_level_3' ) ";
		elseif ( $role == 'king' )
			$condition = " owner in ('king', 'vassal' )";
		else
			$condition = " owner = '" . $role . "' ";

		$res = $db -> query ( "
		select ck.name, ck.image, ck.id, ck.description,  k.* from kingdomprojects k, cfgkingdomprojects ck
		where k.cfgkingdomproject_id = ck.id
		and   k.region_id = " . $this -> id . "
		and region_id in
		( select id from regions where kingdom_id =  " . $this -> kingdom -> id . ")" );

		return $res;
	}

	/*
	* Torna le strutture della regione dalla cache
	* @param Int id regione
	* @return array
	*/

	function get_structures_d( $region_id )
	{

		$cachetag = '-regionstructures_' . $region_id;
		$data = My_Cache_Model::get( $cachetag );

		if ( is_null( $data ) )
		{
			//kohana::log('debug', "-> get_item_quantity_d: Getting $cachetag from DB.");

			$db = Database::instance();

				$sql = "select s.id, st.image, st.subtype, st.supertype, st.associated_role_tag, st.attribute1 st_attribute1, s.attribute1 s_attribute1, st.type, st.name, s.character_id, st.subtype, st.level,
				st.church_id
				from structures s, structure_types st
				where  s.structure_type_id = st.id
				and
				(
					st.subtype in ( 'government', 'church', 'other', 'player' )
				)
				and s.region_id = " . $region_id ;

			$res = $db -> query( $sql ) -> as_array();

			foreach ( (array) $res as $structure )
				$data[$structure -> subtype][$structure->type][] = $structure;

			My_Cache_Model::set( $cachetag, $data );

		}

		//var_dump($data); exit;

		return $data;

	}


	/**
	* torna le strutture della regione
	* @param str $supertype Struttura Super Type
	* @return array di oggetti struttura
	*/

	public function get_structures( $supertype )
	{

		$db = Database::instance();

		$sql = "select s.id from structures s, structure_types st
		where s.structure_type_id = st.id
		and    st.supertype = '{$supertype}'
		and    s.region_id = " . $this -> id ;

		//var_dump( $sql ); exit;

		$structures = $db -> query( $sql ) ;

		if ( $structures -> count() == 0 )
			return null;

		foreach ( $structures as $structure )
			$structures_a[] = StructureFactory_Model::create( null, $structure -> id );

		return $structures_a;

	}

	/**
	* torna le potenziali regioni
	* dove � possibile costruire la struttura passata
	* @param  $type livello struttura religiosa
	* @return regions lista di regioni
	*/

	public function findpotentialregions( $type )
	{
		$db = Database::instance();

		// religion structure livello 2
		// distanza almeno a

		if ( $type == 'religion_2' )
			$sql = "select id, name from regions where id != " . $this -> id ;

		$regions = $db -> query( $sql ) -> as_array();

		return $regions ;

	}

	public function get_allpaths()
	{

		$cachetag = '-allpaths' ;
		$allpaths = My_Cache_Model::get( $cachetag );

		if ( is_null( $allpaths ) )
		{

			$sql = "select r1.name region_1, r2.name region_2, rp.type
			from regions r1, regions r2, regions_paths rp
			where r1.id = rp.region_id
			and   rp.type not in ( 'fastsea', 'fastland' )
			and   r2.id = rp.destination" ;

			$allpaths = Database::instance() -> query ( $sql ) -> as_array();
			My_Cache_Model::set( $cachetag, $allpaths );

		}

		return $allpaths;

	}


	/*
	* Torna se la regione ha il mare vicino
	* @param int id region
	* @return boolean
	*/

	public function isadjacenttosea( $region_id )
	{
		$sql = "select rp.id
				from regions_paths rp
				where rp.region_id = " . $region_id . "
				and   rp.type in ( 'sea', 'mixed' ) ";

		$paths = Database::instance() -> query ( $sql ) -> as_array();

		if ( count($paths) > 0 )
			return true;
		else
			return false;

	}

	/**
	* COmputes min and max distance from a source region
	* and an array of target regions.
	* @param  obj $region Region_Model Source Region
	* @param  array $regions array with regions objects
	* @return array
	*           regionmin Region that is more near
	*           regionmax Region that is more far
	*           mindistance Minimum distance from source region to dest region
	*           maxdistance Maximum distance from source region to dest region
	*/

	public function findminmaxdistance( $region, $regions )
	{

		$info = array ( 'regionmin' => null, 'regionmax' => null, 'mindistance' => 0, 'maxdistance' => 0 );

                require_once( dirname(dirname(__FILE__)) . "/libraries/vendors/Dijkstra/Dijkstra.php");
		$db = Database::instance();
		$g = new Graph();

		$sql = "select r1.name region_1, r2.name region_2, rp.type
		from regions r1, regions r2, regions_paths rp
		where r1.id = rp.region_id
		and   rp.type not in ( 'fastsea', 'fastland' )
		and   r2.id = rp.destination" ;

		$res = $db -> query( $sql );
		foreach ( $res as $row )
		{
			if ( $row -> type == 'sea' or $row -> type == 'mixed' )
				$weight = 999;
			else
				$weight = 1;
			$g -> addedge( $row -> region_1 , $row -> region_2 , $weight );
		}

		//kohana::log('debug', kohana::debug( $regions ) );

		$mindistance = 999;
		$maxdistance = 0;

		foreach ( $regions as $key => $value )
		{

			kohana::log('debug', '-> Computing distance between Region: [' . $region -> name . '] and region: [' . $value . ']') ;
			list($distances, $prev) = $g -> paths_from( $region -> name );
			$path = $g -> paths_to( $prev, $value );
			//kohana::log('debug', kohana::debug( $path ) );
			if ( count( $path ) == 0 )
				$distance = 0 ;
			else
				$distance = max(array_keys($path));
			kohana::log('debug', '-> Computing distance between Region [' . $region -> name . '] and  region: [' . $value . '],  distance is: ' .  $distance .
			'mindistance: ' . $mindistance ) ;

			if ( $distance < $mindistance )
			{
				$mindistance = $distance;
				$info['regionmin'] = $value;
				$info['mindistance'] = $mindistance ;
			}

			if ( $distance > $maxdistance )
			{
				$maxdistance = $distance;
				$info['regionmax'] = $value;
				$info['maxdistance'] = $maxdistance ;
			}
		}

		//kohana::log('debug', kohana::debug( $info ) );

		return $info ;

	}

	/**
	* Find adjacent regions
	* @param Region_Model $sourceregion Regione da cui parte il char.
	* @return array key=region name, value = region id or NULL
	*/

	function find_adjacentregions( $sourceregion )
	{

		if ( ! $sourceregion -> loaded )
			return null;

		require_once( dirname(dirname(__FILE__)) . "/libraries/vendors/Dijkstra/Dijkstra.php");
		$g = new Graph();
		$regionpaths = Configuration_Model::get_cfg_regions_paths();
		$regions = Configuration_model::get_cfg_regions();

		//var_dump($regionpaths); exit;
		//var_dump($regions); exit;

		foreach ( $regionpaths as $regionpath )
			if ( !in_array( $regionpath -> type, array( 'fastland', 'fastsea' ) ) )
			{
				//kohana::log('debug', "-> Adding edge {$regionpath -> name1} -> {$regionpath -> name2}");
				$g -> addedge( $regionpath -> name1 , $regionpath -> name2 , 1 );
			}

		list($distances, $prev) = $g -> paths_from( $sourceregion -> name );
		//var_dump($g->nodes['regions.trapani']); exit;

		foreach ( $distances as $region => $distance )
			if ( $distance == 1 )
				$adjacentregions[$regions[$region] -> id] = kohana::lang($region);

		//var_dump($adjacentregions); exit;
		//list($distances, $prev) = $g -> paths_from( $region -> name );

		return $adjacentregions;
	}


	/**
	* Assegna la regione ad un Regno
	* @param object $targetkingdom Oggetto Regno a cui assegnare la Regione
	* @return none
	*/

	function move( $targetkingdom )
	{

		// Trovo tutte le strutture in questa regione e per ognuna
		// setto i palazzi parent.

		kohana::log('info', '-> Moving Region: ' . $this -> name . ' to Kingdom: ' . $targetkingdom -> name );

		$allstructures = Region_Model::get_structures_d( $this -> id );
		$royalpalace = $targetkingdom -> get_structure('royalpalace');
		$castle = $targetkingdom -> get_structure('castle');

		foreach ( $allstructures['government'] as $key => $structures )
			foreach ( $structures as $structure )
			{
				kohana::log('info', '-> Processing structure: ' . $structure -> type . ', ID: ' .
					$structure -> id);

				$sobj = ORM::factory('structure', $structure -> id );

				// Chiudo tutti i ruoli...

				kohana::log('info', '-> Processing Roles...' );

				if ( !is_null($structure -> associated_role_tag ) )
				{
					$char = ORM::factory( 'character', $structure -> character_id );
					if ( $char -> loaded )
					{
						$role = $char -> get_current_role();
						if ( !is_null ( $role ) )
						{
							kohana::log('info', '-> Closing role: ' . $role -> tag . ' for char: ' . $char -> name );
							$role -> end();
						}
					}
				}

				// Sposto le strutture legate al Palazzo Reale
				// al castello reale della nuova regione oppure
				// blanka il campo

				if ( in_array( $structure -> supertype, array(
					'castle',
					'academy',
					'trainingground',
					'buildingsite' ) ) )
				{
					kohana::log('info', '-> Assigning parent structure for structure: ' .
						$structure -> supertype );

					// Setta le parent structure al palazzo reale. Gestisci
					// il caso speciale building site e rivolta nativi.

					if ( $targetkingdom -> name == 'kingdoms.kingdom-independent' )
					{
						$sobj -> parent_structure_id = NULL;
						$sobj -> character_id = NULL;
					}
					else
					{
						if ( 0 and $structure -> supertype == 'buildingsite' )
						{
							$buildingstructure = ST_Buildingsite_1_Model::get_info( $structure -> id );

							kohana::log('info', '-> buildingsite type: ' . $buildingstructure[0] -> supertype );

							if ( in_array(
								$buildingstructure[0] -> supertype,
								array( 'academy', 'trainingground', 'castle' ) ) )
							{
								kohana::log('info', '-> assigning parent structure: royalpalace.');
								$sobj -> parent_structure_id = $royalpalace -> id;
								$sobj -> character_id = $royalpalace -> character_id;
							}
							else
							{
								kohana::log('info', '-> assigning parent structure: castle.');
								$sobj -> parent_structure_id = $castle -> id;
								$sobj -> character_id = $castle -> character_id ;
							}
						}
						else
							$sobj -> parent_structure_id = $royalpalace -> id;
					}
				}

				// Sposto le strutture legate al Castello
				// al Castello della nuova regione oppure
				// blanka il campo

				if ( in_array( $structure -> supertype, array(
					'court',
					'barracks',
					'market',
					'nativevillage',
					'tavern',
					'well',
					'harbor',
					) ) )
				{

					kohana::log('info', '-> Assigning parent structure for structure: ' . $structure -> supertype . ' (capital castle)' );

					// linka struttura a castello della capitale.

					if ( !is_null( $castle ) )
						$sobj -> parent_structure_id = $castle -> id;

					// caso Regno Indipendente... blanka la parent

					else if ( $targetkingdom -> name == 'kingdoms.kingdom-independent' )
						$sobj -> parent_structure_id = NULL;
				}
				kohana::log('info', '-> saving object...');
				$sobj -> save();

			}


		kohana::log('info', '-> *** Changing Citizens Residence...');

		// Tutti i cittadini con residenza nella regione diventano
		// residenti della capitale del regno che ha perso.

		$citizens = ORM::factory('character') -> where  ( 'region_id', $this -> id ) -> find_all();
		$capitalregion = $this -> kingdom -> get_capitalregion( $this -> kingdom_id );

		if ( count($citizens) > 0 )
			foreach ( $citizens as $citizen )
			{
				kohana::log('info', '-> Changing residence for char: ' . $citizen -> name . ' to region: ' . $capitalregion -> name );
				$citizen -> region_id = $capitalregion -> id;
				$citizen -> save();
			}

		// Nuova data di attacco in caso rivolta nativi
		if ( $targetkingdom -> name == 'kingdoms.kingdom-independent' )
			$this -> canbeconquered = time() + ( mt_rand(7, 10) * 24 * 3600 );

		// Setto il nuovo Regno

		$this -> kingdom_id = $targetkingdom -> id;
		$this -> updatemap = true;
		$this -> save();

		// Invalidazione CACHE

		$cachetag = '-cfg-regions';
		My_Cache_Model::delete ( $cachetag );

		$cachetag = '-regionstructures_' . $this -> id;
		My_Cache_Model::delete( $cachetag );

		// Invalido cache per tutti i char che sono nella regione.

		$chars = Database::instance() -> query ("
			select * from characters where
			( region_id = " .   $this -> id . " or
			  position_id = " . $this -> id . " ) " );

		foreach ( $chars as $char )
		{
			$cachetag = '-charinfo_' . $char -> id . '_charobj';
			My_Cache_Model::delete(  $cachetag );
			$cachetag = '-charinfo_' . $char -> id . '_currentposition';
			My_Cache_Model::delete(  $cachetag );
		}

	}

	/*
	* Verifica se il char si pu� muovere da una regione all' altra
	* analizzando le relazioni diplomatiche
	* @param obj $char Character_Model che si deve muovere
	* @param obj $targetregtion Region_Model regione di destinazione
	* @param obj $currentregion Region_Model regione di partenza
	* @param boolean $movetobattlefield Specifica se ci si sta muovendo in un battlefield
	* @param str $message Messaggio di ritorno
	* @return none
	*/

	function canmoveto( $char, $targetregion, $currentregion, $movetobattlefield, &$message )
	{

		// troviamo la diplomatic relation esistente tra la
		// regione target ed il regno del char.

		kohana::log('info', '-> canmoveto: Moving from: ' . kohana::lang($currentregion -> name) . ' to: ' . kohana::lang($targetregion -> name ));

		$dr = Diplomacy_Relation_Model::get_diplomacy_relation(
			$targetregion -> kingdom_id,
			$char -> region -> kingdom_id );

		kohana::log('info', '-> canmoveto: Diplom. Relation with Kingdom: ' . kohana::lang($targetregion -> kingdom -> name) . ' is: ' . $dr['type'] );

		// controlliamo se il char ha un permesso di accesso per la regione.

		$stat = $char -> get_stat_d( $char -> id, 'accesspermit', $targetregion -> kingdom_id );

		// se:
		// 1. la relazione diplomatica � ostile;
		// 2. il char non si sta muovendo verso un battlefield;
		// 3. il char non ha un permesso di accesso o questo � scaduto;
		// 4. il char si sta muovendo in un regno diverso o all' interno
		// 5. del regno ma sta combattendo
		// 6. allora non si pu� muovere.

		if ( !is_null( $dr )
			and $dr['type'] == 'hostile'
			and $movetobattlefield == false
			and ( !$stat -> loaded or $stat -> value < time() )
			and (
				$currentregion -> kingdom_id != $targetregion -> kingdom_id
			 or
				(
				$currentregion -> kingdom_id == $targetregion -> kingdom_id
				and
				Character_Model::is_fighting($char->id) == true )
			)
		)
		{
			kohana::log('info', '-> canmoveto: Char cannot move because of hostile access.');
			$message = kohana::lang('ca_move.error-hostileaccessdenied');
			return false;
		}


		$data = null;

		// is the target kingdom on war?

		kohana::log('info', "-> canmoveto: Checking if Kingdom {$targetregion -> kingdom -> name } is on war...");
		$isonwar = $targetregion -> kingdom -> is_fighting( $targetregion -> kingdom_id, $data);

		if ( $isonwar !== false )
		{

			kohana::log('info', "-> canmoveto: Kingdom: " . $targetregion -> kingdom -> name . " is on war.");

			// go through all the battles, and see if the char is moving
			// into a kingdom that is attacked.

			foreach ( $data['battles'] as $battle )
			{

				$attackingregion = ORM::factory('region', $battle -> source_region_id );
				$attackedregion = ORM::factory('region', $battle -> dest_region_id );

				kohana::log('info', '-> canmoveto: Attacking Kingdom: ' . $attackingregion -> kingdom -> name . ", Attacked kingdom: " . $attackedregion -> kingdom -> name . " Region attacked: " . $attackedregion -> name);

				// Is the char moving into the attacked kingdom?

				if ( $attackedregion -> kingdom_id  == $targetregion -> kingdom_id )
				{
					// is the char allied with the attacking region?

					$dr = Diplomacy_Relation_Model::get_diplomacy_relation( $char -> region -> kingdom_id,
					$attackingregion -> kingdom_id);

					// if
					// 1. the char kingdom is allied with the attacking region
					// 2. the char is moving into the attacked kingdom (is not already there)
					// 3. the char is not going into battlefield
					// => char can't move.

					if (
						!is_null( $dr )
						and $dr['type'] == 'allied'
						and $movetobattlefield == false
						and $char -> region -> kingdom_id != $attackedregion -> kingdom_id
						and ( !$stat -> loaded or $stat -> value < time() )
						and ( $currentregion -> kingdom_id != $targetregion -> kingdom_id ) )
						{
							$message = kohana::lang('ca_move.error-diplomacyrelationhostileorallied');
							kohana::log('info', "-> canmoveto: Can't move: Char Kingdom: " . $char -> region -> kingdom -> name. " is allied with " . $attackingregion -> kingdom -> name);

							return false;
						}
				}
			}

		}
		kohana::log('info', "-> canmoveto: Can move.");
		return true;
	}

}
