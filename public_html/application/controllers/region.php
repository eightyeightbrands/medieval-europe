<?php defined('SYSPATH') OR die('No direct access allowed.');
class Region_Controller extends Template_Controller
{
	// Dimensioni della porzione di mappa da renderizzare
	const WIDTH_MAP  = 600;
	const HEIGHT_MAP = 600;
	// Dimensioni della mappa originale di gioco
	const WIDTH_IMAGE = 1380;
	const HEIGHT_IMAGE = 1583;
	
	// Imposto il nome del template da usare
	public $template = 'template/gamelayout';
	
	public function __construct()
	{
		parent::__construct();    
	}

	public function index()
	{
		// Se l'utente digita /region allora viene rediretto
		// alla view della corrente posizione del char
	}
	
		
	/**
	* Carica i dati e visualizza le 
	* strutture presenti nel nodo
	* @param none
	* @return none
	*/	
	
	public function view( $version = '' )
	{
		
		$view = new view("region/view");
		$sheets  = array('gamelayout'=>'screen');		
		$char = Character_Model::get_info( Session::instance()->get('char_id') );		
		$structures_arr = array();
		$list_i = array();
		
		// se il char sta viaggiando, lo ridirigo alla 
		// pagina di viaggio
		
		if ( Character_Model::is_traveling( $char -> id ) )							
			url::redirect ( 'map/view');
		
		$currentregion = ORM::factory('region', $char -> position_id );							
		
		// carico le strutture
		
		$sql = "
				select s.id, st.image, st.supertype, st.attribute1 st_attribute1, 
				s.attribute1 s_attribute1, st.type, st.name, 
				s.character_id, st.subtype, st.level 
				from structures s, structure_types st 
				where  s.structure_type_id = st.id
				and    
				(
					st.subtype in ( 'government', 'church', 'other' )
					or
					(st.subtype in ('player') and s.character_id = " . $char -> id . ") 
				)
				and s.region_id = " . $char -> position_id  . 
				" order by st.sortorder asc, s.id asc ";
		
		$structures = Database::instance() -> query($sql);
		$i = 0;
				
		foreach ( $structures as $structure )
		{	
			$structureinstance = ORM::factory ('structure', $structure -> id );
			$churchname = $structureinstance->structure_type->church->name;
			$structure -> cannotmanage = Structure_Grant_Model::get_chargrant( $structureinstance, $char, 'none' ); 
			$structures_arr[ $structure -> subtype ][$i] = $structure;			
			$i ++;
		}
		
		//var_dump($structures_arr); exit;
		
		$list_i = ORM::factory('item')
			-> where( 
				array('region_id' => $char -> position_id )) -> find_all() -> as_array();
		$list_c = Region_Model::get_chars( $char -> position_id, $char -> region -> kingdom_id, 'regionpresentchars' );
		
		$structures2 = Region_Model::get_structures_d( $char -> position_id );		
		$view -> isadjacenttosea = Region_Model::isadjacenttosea( $char -> position_id );		
		$view -> structures = $structures_arr; 	
		$view -> char = $char;
		$view -> list_c = $list_c;			
		$view -> list_i = $list_i;
		$view -> currentregion = $currentregion;
		$view -> viewingchar = $char;		
		// questo è per la vista nuova non rimuovere.
		//$view -> structures2 = $structures;
		$this -> template -> structures = $structures_arr;
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;

	}
	
	public function find_paths()
	{
		$this -> auto_render = false;
		Region_Model::find_paths();
	
	}
	
	/**
	* Visualizzazione informazioni diplomazia
	* @param region_id id regione
	* @return none
	*/
	
	public function info_diplomacy( $region_id ) 
	{
		
		$view = new view('region/info_diplomacy');
		$sheets  = array('gamelayout'=>'screen',  'submenu'=>'screen');
		$subm    = new View ('template/submenu');		
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$region = ORM::factory('region', $region_id );
		
		$kingdoms = Database::instance() -> 
			query ( "select * from kingdoms_v where name != 'kingdoms.kingdom-independent'") -> as_array();
				
		$relations = Configuration_Model::get_cfg_diplomacyrelations();
		
		$lnkmenu = array(
		'region/info/' . $region -> id => kohana::lang('regionview.submenu_generalinfo'), 
		'region/info_laws/' . $region -> id => kohana::lang('regionview.submenu_laws'),
		'region/info_diplomacy/' . $region -> id => 
			array( 'name' => kohana::lang('regionview.submenu_diplomacy'),'htmlparams' => array( 'class' => 'selected' )), 
		);

		$view -> kingdoms = $kingdoms;
		$view -> relations = $relations;
		$subm -> submenu = $lnkmenu;
		$view -> region = $region;
		$view -> submenu = $subm;		
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;		

	}

	/** 
	* Visualizzazione delle informazioni sulla regione (e regno)
	* @param int $region_id id regione
	* @return none
	*/
	
	public function info( $region_id = null )	
	{
		
		$view = new view('region/info');
		$sheets  = array('gamelayout'=>'screen',  'submenu'=>'screen');
		$subm    = new View ('template/submenu');		
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$db = Database::instance();		
		
		if ( is_null ( $region_id ) ) 
			$region_id = $character -> position_id ;
		
		$region = ORM::factory( "region", $region_id );
		
		// la regione esiste? (manipolazione parametri)
		
		if ( !$region -> loaded )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">" . kohana::lang('global.operation_not_allowed') . "</div>");		
			url::redirect( 'map/view' );
		}
		
		// si sta cercando di vedere le informazioni di una regione
		// non conquistata?
		
		if ( $region -> kingdom -> get_name() == 'kingdoms.kingdom-independent' )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">" . kohana::lang('regioninfo.error-infonotexisting') . "</div>");
			url::redirect( 'map/view' );
		}
		
		// carico le relazioni diplomatiche
		
		if ( $character -> region -> kingdom_id != $region -> kingdom_id )
		{			
			
			$relations = Diplomacy_Relation_Model::get_diplomacy_relations ( $character -> region -> kingdom_id );
			
			$view -> diplomacyrelationsourcedest = $relations[ $character -> region -> kingdom_id ][$region -> kingdom_id];		
			
		}
				
		// carico le informazioni sul Regno
		
		$view -> kingdom_info = $region -> kingdom -> get_info();		
		$view -> constables = $region -> get_charinrolegdr('constable');
		$view -> chancellors = $region -> get_charinrolegdr('chancellor');
		$view -> seneschals = $region -> get_charinrolegdr('seneschal');
		$view -> chamberlains = $region -> get_charinrolegdr('chamberlain');
		$view -> treasurers = $region -> get_charinrolegdr('treasurer');
		$view -> ambassadors = $region -> get_charinrolegdr('ambassador');
		
		// Conteggio dei players residenti nel Regno
		
		$rset = $db -> query ("
			SELECT count(id) total
			FROM characters c
			WHERE c.type = 'pc'
			AND region_id in ( 
				SELECT id 
				FROM regions 
				WHERE kingdom_id = {$region -> kingdom_id})
			" )
			-> as_array();		
			
		$view -> tot_kingdom_residents = $rset['0'] -> total;
		
		// Conteggio dei players residenti nella regione
		
		$view -> tot_region_residents = 
			ORM::factory( "character" )-> where ( 
				array( 
					"region_id" => $region_id,
					"type" => 'pc'
				)
			) -> count_all ();

		// Conteggio dei players attualmente presenti nel nodo
		
		$view -> tot_players_present = ORM::factory( "character" )
			-> where ( 
				array( 
					"position_id" => $region_id,
					"type" => 'pc'
				)
			) -> count_all ();

		// Query per la selezione di tutti i tipi di casa
		
		$houses = ORM::factory("structure_type")->where ( 'supertype', 'house' ) -> find_all ();		
		
		// Cariche		
		
		
		// Scorro i tipi di casa ed effettuo le query per vedere quante
		// ce ne sono in quel nodo. Contemporaneamente conteggio il totale parziale
		
		$i=0; $toth=0;
		foreach ($houses as $house )
		{
			$houselist[$i]['house'] = $house;
			$houselist[$i]['tot'] = ORM::factory("structure")->where (array("structure_type_id" => $house->id, 'region_id' => $region->id)) -> count_all ();
			$toth +=$houselist[$i]['tot']; $i++;
		}
		
		// Calcolo le informazioni relative ai terreni
		
		$view -> terrains_info = Region_Model::get_terrains_info( $region );
		
		// Estraggo le tasse relative al nodo
		
		$view -> vat = Region_Model::get_tax( $region -> id, 'valueaddedtax');
		$view -> propertyprice = Region_Model::get_tax( $region, 'propertyprice');		
		
		$lnkmenu = array(
		'region/info/' . $region -> id => 
			array( 'name' => kohana::lang('regionview.submenu_generalinfo'), 'htmlparams' => array( 'class' => 'selected' )), 
		'region/info_laws/' . $region -> id => kohana::lang('regionview.submenu_laws'),
		'region/info_diplomacy/' . $region -> id => kohana::lang('regionview.submenu_diplomacy'),
		);

		$view -> king = $region->get_charinrole('king');
		$view -> vassal = $region->get_charinrole('vassal');
		$view -> judge = $region->get_charinrole('judge');
		$view -> sheriff = $region->get_charinrole('sheriff');		
		$view -> academydirector = $region -> get_charinrole('academydirector');
		$view -> drillmaster = $region->get_charinrole('drillmaster');
		
		$view -> toth = $toth;
		$view -> houselist = $houselist;

		$view -> char = $character;		
		$subm -> submenu = $lnkmenu;
		
		$view -> region = $region;
		$view -> submenu = $subm;		
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;		
	
	}
	
	/**
  * Restituisce la lista dei chars residenti o che sono di passaggio
	*/

	public function regionpresentchars( )
	{
		
		$view = new view( 'region/regionpresentchars');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		
		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		$region_id = $char -> position_id ;
		
		$currentregion = ORM::factory('region', $region_id);
		
		if ($currentregion -> loaded == false )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">" . kohana::lang('global.error-regionunknown') . "</div>");
			url::redirect( 'region/info' );
			
		}
		
		$kingdomcount = Database::instance() -> query("
		select count(*) c, k.name kingdom_name 
		from characters c, regions r, kingdoms_v k
		where c.region_id = r.id
		and   r.kingdom_id = k.id
		and   c.type != 'npc' 
		and   c.position_id = {$currentregion -> id}
		group by k.name
		order by c desc") -> as_array();
		
		$religioncount = Database::instance() -> query("
		select count(*) c, ch.name church_name 
		from characters c, regions r, churches ch
		where c.region_id = r.id
		and   c.type != 'npc' 
		and   c.church_id = ch.id 		
		and   c.position_id = {$currentregion -> id}
		group by ch.name
		order by c desc") -> as_array();		
		
		$presentchars = Database::instance() -> query ( "
			select from_unixtime( u.last_login, '%d-%m-%y') last_login, 
			c.id, c.type, c.status, c.name char_name, r.name region_name, ch.name church_name, k.name kingdom_name 
			from characters c, users u, regions r, churches ch, kingdoms_v k
			where c.user_id = u.id 
			and c.region_id = r.id 			
			and c.church_id = ch.id 
			and c.type != 'npc'
			and r.kingdom_id = k.id 
			and c.position_id = {$currentregion -> id}
			order by u.last_login desc" );
		
		$this -> pagination = new Pagination(array(
			'base_url'=>'region/regionpresentchars/',
			'uri_segment'=> 'regionpresentchars',
			'style' => "extended",
			'total_items'=> $presentchars  -> count(),
			'items_per_page' => 20));				
		
		$presentchars = Database::instance() -> query ( "
			select from_unixtime( u.last_login, '%d-%m-%y') last_login, 
			c.id, c.type, c.status, c.name char_name, r.name region_name, ch.name church_name, k.name kingdom_name  
			from characters c, users u, regions r, churches ch, kingdoms_v k
			where c.user_id = u.id 
			and c.region_id = r.id 			
			and c.church_id = ch.id 
			and r.kingdom_id = k.id 
			and c.type != 'npc'
			and c.position_id = {$currentregion -> id}
			order by u.last_login desc 
			limit 20 offset " . $this -> pagination -> sql_offset );
		
		$subm = new View ('template/submenu');
		
		$lnkmenu = array(
		'region/info/' . $region_id => 
			array( 'name' => kohana::lang('regionview.submenu_generalinfo'), 'htmlparams' => array( 'class' => 'selected' )), 
		'region/info_laws/' . $region_id => kohana::lang('regionview.submenu_laws')); 		
		
		$view -> kingdomcount = $kingdomcount;
		$view -> religioncount = $religioncount;
		$subm -> submenu = $lnkmenu;
		$view -> submenu = $subm;
		$view -> pagination = $this -> pagination;
		$view -> presentchars = $presentchars ;
		$view -> currentregion = $currentregion;
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;		
	}
	
	/**
  * Restituisce la lista dei chars residenti o che sono di passaggio
	* @param string $kind tipologia di query (residenti o di passaggio)
	* @param int $region_id id regione
	* @return none
	*/

	public function kingdomcitizens( $region_id )
	{
		$view = new view( 'region/kingdomcitizens');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		
		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		if ( is_null ( $region_id ) )
			$region_id = $char -> position_id ;
		
		$currentregion = ORM::factory('region', $region_id);
		
		if ($currentregion -> loaded == false )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">" . kohana::lang('global.error-regionunknown') . "</div>");
			url::redirect( 'region/info' );
			
		}
		
		$citizens = Database::instance() -> query ( "
			select from_unixtime( u.last_login, '%d-%m-%y') last_login, c.id, c.type, c.status, c.name char_name, r.name region_name
			from characters c, users u, regions r
			where c.user_id = u.id 
			and c.region_id = r.id 			
			and c.type != 'npc'
			and c.region_id in (select id from regions where kingdom_id = {$currentregion->kingdom_id}) order by u.last_login desc " );

		
		$this -> pagination = new Pagination(array(
			'base_url'=>'region/kingdomcitizens/',
			'uri_segment'=> $region_id,
			'style' => "extended",
			'total_items'=> $citizens -> count(),
			'items_per_page' => 20));				
		
		$citizens = Database::instance() -> query ( "
			select from_unixtime( u.last_login, '%d-%m-%y') last_login, c.id, c.type, c.status, c.name char_name, r.name region_name
			from characters c, users u, regions r
			where c.user_id = u.id 
			and c.region_id = r.id 			
			and c.type != 'npc'
			and c.region_id in (select id from regions where kingdom_id = {$currentregion->kingdom_id}) order by u.last_login desc 
			limit 20 offset " . $this -> pagination -> sql_offset );
		
		$subm = new View ('template/submenu');
		$lnkmenu = array(
		'region/info/' . $region_id => 
			array( 'name' => kohana::lang('regionview.submenu_generalinfo'), 'htmlparams' => array( 'class' => 'selected' )), 
		'region/info_laws/' . $region_id => kohana::lang('regionview.submenu_laws')); 		
		
		$subm -> submenu = $lnkmenu;
		$view -> submenu = $subm;
		$view -> pagination = $this -> pagination;
		$view -> citizens = $citizens;
		$view -> currentregion = $currentregion;
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;		
	}
	
	public function listchars( $kind = 'regionpresentchars', $region_id = null )
	{
	
		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		$role = $char -> get_current_role();
		
		if ( is_null ( $region_id ) )
			$region_id = $char -> position_id ;
			
		$region = ORM::factory("region", $region_id );
		
		if ( ! $region -> loaded )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">" . kohana::lang('global.operation_not_allowed') . "</div>");					
			url::redirect( 'map/view' );
		}	
		
		$sheets  = array('gamelayout'=>'screen',  'submenu'=>'screen');		
		$subm = new View ('template/submenu');
		$lnkmenu = array(
		'region/info/' . $region -> id => 
			array( 'name' => kohana::lang('regionview.submenu_generalinfo'), 'htmlparams' => array( 'class' => 'selected' )), 
		'region/info_laws/' . $region -> id => kohana::lang('regionview.submenu_laws')); 		
		
		//////////////////////////////////////////////////////
		// Se si è nel mare, non si può vedere la lista 		
		//////////////////////////////////////////////////////
		
		$currentcharposition = ORM::factory('region', $char -> position_id );
		if ( $currentcharposition -> type == 'sea' )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">" . kohana::lang('global.operation_not_allowed') . "</div>");					
			url::redirect( 'region/view' );		
		}
		
		// E' possibile vedere la lista dei char presenti
		// in regione solo se si è in quella regione
		
		if ( $char -> position_id != $region -> id and $kind == 'regionpresentchars' )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">" . kohana::lang('global.operation_not_allowed') . "</div>");					
			url::redirect( 'region/view' );						
		}
		else
		{
			$list_c = $region -> get_chars( $char -> region_id, $char -> region -> kingdom_id, $kind );			
		}
		
		// Istanzia la view corretta.	
		
		if ($kind == 'regionpresentchars') 
			$view = new view( 'region/view');						
		else
			$view = new view( 'region/' . 'list_' . $kind );
		
		$view -> currentregion = $region;		
		$subm -> submenu = $lnkmenu;
		$view -> submenu = $subm;
		$view -> char = $char;
		$view -> role = $role;
		$view -> list_c = $list_c;
		$view -> kind = $kind;
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;
	
	}

	/**
	* Visualizza le strutture private
	* @param type tipo struttura privata
	* @param region_id id regione
	* @return none
	*/
	
	public function privatestructures( $type = 'shop' )
	{
		
		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		$view = new View( 'region/privatestructures' );
		$sheets = array('gamelayout'=>'screen', );
		$region_id =$char -> position_id;
			
			$privatestructures = 
			Database::instance() -> query ( 
			"select s.id, st.image, st.name structure_name, c.name owner, c.id owner_id, st.type, st.supertype, st.parenttype, s.attribute1 
 			 from structures s, structure_types st,characters c
			 where s.structure_type_id = st.id
			 and   s.character_id = c.id 
			 and   st.subtype = 'player' 
			 and   st.parenttype = '" . $type . "' 
			 and   s.region_id = " . $region_id )  -> as_array();
			 
		foreach ( $privatestructures as $privatestructure )
		{
			$structure = StructureFactory_Model::create( null, $privatestructure -> id );
			$privatestructure -> cannotmanage = Structure_Grant_Model::get_chargrant( $structure, $char, 'none' ); 
		}		
		
		$submenu = new View("region/privatestructuressubmenu");
		$submenu -> region_id = $region_id;
		$submenu -> action = $type;		
		$view -> submenu = $submenu;		
		$view -> region_id = $region_id; 
		$view -> type = $type;
		$view -> privatestructures = $privatestructures;
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;
		
	}
	
	function kingdomboards( $kingdom_id = null )
	{
		
		$view = new View('region/kingdomboards');
		$sheets  = array('gamelayout' => 'screen',  'pagination'=>'screen', 'submenu'=>'screen');
		$char = Character_Model::get_info( Session::instance() -> get('char_id') );
		
		if ( is_null($kingdom_id) or $char -> region -> kingdom_id != $kingdom_id )
		{			
			Session::set_flash('user_message', "<div class=\"error_msg\">" . kohana::lang('global.operation_not_allowed') . "</div>");					
			url::redirect( 'region/view' );				
		}
		
		$kingdom = ORM::factory('kingdom', $kingdom_id);
		if ( $kingdom -> loaded == false )
		{
			
			Session::set_flash('user_message', "<div class=\"error_msg\">" . 
				kohana::lang('global.operation_not_allowed') . "</div>");					
			url::redirect( 'region/view' );				
		}
		
		$rows = ORM::factory('kingdom_forum_board') -> where (
			array(
				'kingdom_id' => $kingdom_id,
				'status' => 'new'
			)) -> find_all();
		
		
		$this -> pagination = new Pagination(array(
			'base_url' => 'region/kingdomboards/' . $kingdom_id,
			'uri_segment' => $kingdom_id,			
			'query_string' => 'page',
			'total_items'=> $rows -> count(),
			'items_per_page'=> 20));		
		//var_dump($this -> pagination);
		
		$rows = ORM::factory('kingdom_forum_board') -> where (
			array(
					'kingdom_id' => $kingdom_id,
					'status' => 'new'
				)) -> find_all( 20, $this -> pagination -> sql_offset );
		
		$view -> rows = $rows;	
		$view -> kingdom = $kingdom;
		$view -> pagination = $this -> pagination;
		$view -> char = $char;				
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;
		
	}
	
	function kingdomtopics( $kingdom_id = null, $board_id = null )
	{
		
		kohana::log('debug', $kingdom_id.'-'.$board_id);
		
		$view = new View('region/kingdomtopics');
		$sheets  = array('gamelayout' => 'screen',  'pagination'=>'screen', 'submenu'=>'screen');
		$char = Character_Model::get_info( Session::instance() -> get('char_id') );
		
		if ( is_null($kingdom_id) or is_null($board_id) or $char -> region -> kingdom_id != $kingdom_id )
		{
			
			Session::set_flash('user_message', "<div class=\"error_msg\">" . 
				kohana::lang('global.operation_not_allowed') . "</div>");					
			url::redirect( 'region/view' );				
		}
		
		$kingdom = ORM::factory('kingdom', $kingdom_id);
		if ( $kingdom -> loaded == false )
		{
			
			Session::set_flash('user_message', "<div class=\"error_msg\">" . 
				kohana::lang('global.operation_not_allowed') . "</div>");					
			url::redirect( 'region/view' );				
		}
		
		$currentboard = ORM::factory('kingdom_forum_board', $board_id );
		if ( $currentboard -> loaded == false )
		{
			
			Session::set_flash('user_message', "<div class=\"error_msg\">" . 
				kohana::lang('global.operation_not_allowed') . "</div>");					
			url::redirect( 'region/view' );				
		}
		
		$rows = ORM::factory('kingdom_forum_topic') -> where (
			array(
				'kingdom_forum_board_id' => $board_id,
				'status' => 'new'
				)) -> find_all();			
		
		//var_dump($rows);exit;
		$this -> pagination = new Pagination(array(
			'base_url' => 'region/kingdomtopics/' . $board_id,
			'uri_segment' => $board_id,			
			'query_string' => 'page',
			'total_items'=> $rows -> count(),
			'items_per_page'=> 20));		
		//var_dump($this -> pagination);
		
		$rows = ORM::factory('kingdom_forum_topic') -> where (
		array(
				'kingdom_forum_board_id' => $board_id,
				'status' => 'new'
				)) ->  find_all( 20, $this -> pagination -> sql_offset );
				
		//var_dump($c);exit;
		$view -> currentboard = $currentboard;
		$view -> kingdom = $kingdom;
		$view -> rows = $rows;			
		$view -> pagination = $this -> pagination;
		$view -> char = $char;				
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;
		
	}
	
	function kingdomreplies( $kingdom_id = null, $topic_id = null )
	{
		kohana::log('debug', 'kid: ' . $kingdom_id . 't:' . $topic_id);	
		$view = new View('region/kingdomreplies');
		$sheets  = array(
			'character' => 'screen', 'gamelayout' => 'screen',  'pagination'=>'screen', 'submenu'=>'screen');
		$char = Character_Model::get_info( Session::instance() -> get('char_id') );
		
		if ( is_null($kingdom_id) or is_null($topic_id) or $char -> region -> kingdom_id != $kingdom_id )
		{
			
			Session::set_flash('user_message', "<div class=\"error_msg\">" . 
				kohana::lang('global.operation_not_allowed') . "</div>");					
			url::redirect( 'region/view' );				
		}
		
		$kingdom = ORM::factory('kingdom', $kingdom_id);
		if ( $kingdom -> loaded == false )
		{
			
			Session::set_flash('user_message', "<div class=\"error_msg\">" . 
				kohana::lang('global.operation_not_allowed') . "</div>");					
			url::redirect( 'region/view' );				
		}
		
		$currenttopic = ORM::factory('kingdom_forum_topic', $topic_id );
		
		if ( $currenttopic -> loaded == false )
		{
			
			Session::set_flash('user_message', "<div class=\"error_msg\">" . 
				kohana::lang('global.operation_not_allowed') . "</div>");					
			url::redirect( 'region/view' );				
		}
		
		$sql = "SELECT id, title, body, created, updated, author 
				FROM kingdom_forum_topics kt WHERE id = {$topic_id}
				UNION
				SELECT id, '' as title, body, created, updated, author  
				FROM kingdom_forum_replies kr WHERE kingdom_forum_topic_id = {$topic_id}";
				
		$rows = Database::instance() -> query($sql);
		
		//var_dump($rows);exit;
		$this -> pagination = new Pagination(array(
			'base_url' => 'region/kingdomreplies/' . $topic_id,
			'uri_segment' => $topic_id,			
			'query_string' => 'page',
			'total_items'=> $rows -> count(),
			'items_per_page'=> 20));		
		
		
		$sql = "SELECT id, title, body, created, updated, author  
				FROM kingdom_forum_topics kt WHERE id = {$topic_id}
				UNION
				SELECT id, '' as title, body, created, updated, author 
				FROM kingdom_forum_replies kr WHERE kingdom_forum_topic_id = {$topic_id} 
				LIMIT 20 OFFSET {$this -> pagination -> sql_offset}";
		
		$rows = Database::instance() -> query($sql);
				
		
		$view -> currenttopic = $currenttopic;
		$view -> currentboard = $currenttopic -> kingdom_forum_board;
		$view -> kingdom = $kingdom;
		$view -> rows = $rows;			
		$view -> pagination = $this -> pagination;
		$view -> char = $char;				
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;
		
	}
	
	function addkingdomboard( $kingdom_id = null )
	{
		
		$view = new View('region/addkingdomboard');
		$sheets  = array('gamelayout' => 'screen',  'pagination'=>'screen', 'submenu'=>'screen');
		$char = Character_Model::get_info( Session::instance() -> get('char_id') );
		$message = '';
		
		$form = array(
			'name' => null,
			'boarddescription' => null);
		
		if ($_POST)
		{
			
			//var_dump($_POST);exit;
			$post = Validation::factory($this->input->post())
				-> pre_filter('trim', TRUE)
				-> add_rules('name','required', 'length[3,50]')
				-> add_rules('boarddescription','required', 'length[5,255]');
			
			$kingdom = Kingdom_Model::load( $this -> input -> post('kingdom_id') );	
			if ($kingdom -> loaded == false)
			{
				
				Session::set_flash('user_message', "<div class=\"error_msg\">" . 
				kohana::lang('global.operation_not_allowed') . "</div>");					
				url::redirect( 'region/kingdomboards/' . $kingdom_id );
			
			}
			
			if ($post -> validate() )
			{	

				
				// add board
				
				$c = Board_Factory_Model::create('kingdom');
				//var_dump($post);exit;
				$rc = $c -> add( $char, $this -> input -> post(), $message );
				if ($rc == true )
				{
					Session::set_flash('user_message', 
					"<div class=\"info_msg\">".kohana::lang('kingdomforum.info-boardadded')."</div>"); 
					url::redirect('/region/kingdomboards/' . $kingdom_id );
				}
				else
				{
					Session::set_flash('user_message', 
					"<div class=\"error_msg\">".kohana::lang($message)."</div>"); 
					$form = arr::overwrite($form, $post -> as_array());	
				}
			}
			else
			{
				$errors = $post -> errors('form_errors'); 
				$view -> bind('errors', $errors);				
				$form = arr::overwrite($form, $post -> as_array());				
			}
		}
		else
		{
			$kingdom = Kingdom_Model::load( $kingdom_id );	
			
			if ($kingdom -> loaded == false)
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">" . 
				kohana::lang('global.operation_not_allowed') . "</div>");					
				url::redirect( 'region/kingdomboards/' . $kingdom_id );
				
			}
		}
		
		$view -> kingdom = $kingdom;
		$view -> form = $form;
		$view -> char = $char;		
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;
		
	}
	
	function editkingdomboard( $board_id = null )
	{
		
		$view = new View('region/editkingdomboard');
		$sheets  = array('gamelayout' => 'screen',  'pagination'=>'screen', 'submenu'=>'screen');
		$char = Character_Model::get_info( Session::instance() -> get('char_id') );
		$data = null; 
		$message = '';
		
		$form = array(
			'name' => null,
			'boarddescription' => null,
			);
		
		if ($_POST)
		{
			//var_dump($_POST);exit;
			$post = Validation::factory($this->input->post())
				-> pre_filter('trim', TRUE)
				-> add_rules('name','required', 'length[3,50]')
				-> add_rules('boarddescription','required', 'length[5,255]');
			
			$board_id = $this -> input -> post('board_id');
			$currentboard = ORM::factory('kingdom_forum_board', $board_id);
			
			if ($post->validate() )
			{	
				
				
				$c = Board_Factory_Model::create('kingdom');
				$rc = $c -> edit( $char, $currentboard, $this -> input -> post(), $message );
			
				if ($rc == true )
				{					
					Session::set_flash('user_message', 
					"<div class=\"info_msg\">".kohana::lang('kingdomforum.info-boardedited')."</div>"); 				
					url::redirect('region/kingdomboards/' . $currentboard -> kingdom_id);					
				}
				else
				{
					Session::set_flash('user_message', 
					"<div class=\"error_msg\">".kohana::lang('global.operation_not_allowed')."</div>");
					$form = arr::overwrite($form, $post -> as_array());				
				}
				
			}
			else
			{
				$errors = $post -> errors('form_errors'); 
				$view -> bind('errors', $errors);								
				$form = arr::overwrite($form, $post -> as_array());				
				
			}
		}
		else
		{
			$c = Board_Factory_Model::create('kingdom');
			$rc = $c -> read( $char, $board_id, $data );
			
			if ($rc == true )
			{
				$form['name'] = $data -> name;
				$form['boarddescription'] = $data -> description;
				
			}
			else
			{
				Session::set_flash('user_message', 
				"<div class=\"error_msg\">".kohana::lang('global.operation_not_allowed')."</div>"); 		
				url::redirect('region/kingdomboards/' . $data -> kingdom_id );
			}
		}
		
		$view -> form = $form;
		$view -> board_id = $board_id;
		$view -> char = $char;		
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;
		
	}	
	
	function deletekingdomboard( $board_id )
	{
		
		$char = Character_Model::get_info( Session::instance() -> get('char_id') );		
		$board = ORM::factory('kingdom_forum_board', $board_id);
		
		$c = Board_Factory_Model::create('kingdom');
		$rc = $c -> delete( $char, $board, $message );
		
		if ($rc == true )
		{
			Session::set_flash('user_message', 
			"<div class=\"info_msg\">".kohana::lang('kingdomforum.info-boarddeleted')."</div>"); 		
			url::redirect('region/kingdomboards/' . $board -> kingdom_id );
			
		}
		else
		{
			Session::set_flash('user_message', 
			"<div class=\"error_msg\">".kohana::lang('global.operation_not_allowed')."</div>"); 		
			url::redirect('region/kingdomboards/' . $board -> kingdom_id );
		}
	}
	
	
	function addkingdomtopic( $board_id = null )
	{
		
		$view = new View('region/addkingdomtopic');
		$sheets  = array('gamelayout' => 'screen',  'pagination'=>'screen', 'submenu'=>'screen');
		$char = Character_Model::get_info( Session::instance() -> get('char_id') );
		
		$form = array(
			'title' => '',
			'body' => '');
		
		if ($_POST)
		{
			
			$post = Validation::factory($this->input->post())
				-> pre_filter('trim', TRUE)
				-> add_rules('body', 'required')
				-> add_rules('title','required', 'length[3,50]');			
			
			$currentboard = ORM::factory('kingdom_forum_board', $this -> input -> post('board_id'));
			
			if ($post->validate() )
			{	
				$c = Topic_Factory_Model::create('kingdom');
				$rc = $c -> add( $char, $this -> input -> post(), $message );
				
				if ($rc == true )
				{					
					Session::set_flash('user_message', 
					"<div class=\"info_msg\">".kohana::lang('kingdomforum.info-topicadded')."</div>"); 				
					url::redirect('region/kingdomboards/' . $currentboard -> kingdom_id);					
				}
				else
				{
					Session::set_flash('user_message', 
					"<div class=\"error_msg\">".kohana::lang('global.operation_not_allowed')."</div>");
					$form = arr::overwrite($form, $post -> as_array());				
				}				
				
			}
			else
			{
				$errors = $post -> errors('form_errors'); 
				$view -> bind('errors', $errors);				
				$form = arr::overwrite($form, $post -> as_array());				
			}
			
		}
		else
		{
			$currentboard = ORM::factory('kingdom_forum_board', $board_id );
		}
		
		$view -> currentboard = $currentboard;		
		$view -> form = $form;
		$view -> char = $char;		
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;
		
	}
	
	function editkingdomtopic( $topic_id = null)
	{
		
		$view = new View('region/editkingdomtopic');
		$sheets  = array('gamelayout' => 'screen',  'pagination'=>'screen', 'submenu'=>'screen');
		$char = Character_Model::get_info( Session::instance() -> get('char_id') );
		$message = '';
		$data = null;
		
		$form = array(
			'title' => '',
			'body' => ''
		);
		
		if ($_POST)
		{
			
			$currenttopic = ORM::factory('kingdom_forum_topic', $this -> input -> post('topic_id'));			
			$post = Validation::factory($this->input->post())
				-> pre_filter('trim', TRUE)
				-> add_rules('body', 'required')
				-> add_rules('title','required', 'length[3,50]');				

			if ($post->validate() )
			{	
				$c = Topic_Factory_Model::create('kingdom');
				$rc = $c -> edit( $char, $currenttopic, $this -> input -> post(), $message );
				
				if ($rc == true )
				{					
					Session::set_flash('user_message', 
					"<div class=\"info_msg\">".kohana::lang('kingdomforum.info-topicedited')."</div>"); 				
					url::redirect('region/kingdomtopics/' . $currenttopic -> kingdom_forum_board -> kingdom_id . '/' . 
						$currenttopic -> kingdom_forum_board_id);			
				}
				else
				{
					Session::set_flash('user_message', 
					"<div class=\"error_msg\">".kohana::lang('global.operation_not_allowed')."</div>");
					$form = arr::overwrite($form, $post -> as_array());				
				}				
			}
			else
			{
				$errors = $post -> errors('form_errors'); 				
				$view -> bind('errors', $errors);				
				$form = arr::overwrite($form, $post -> as_array());				
			}
		}
		else
		{
			
			
			$c = Topic_Factory_Model::create('kingdom');
			$rc = $c -> read( $char, $topic_id, $data );
			$currenttopic = ORM::factory('kingdom_forum_topic', $topic_id);			
			//var_dump($data);exit;
			if ($rc == true )
			{
				$form['title'] = $data -> title;
				$form['body'] = $data -> body;
				
			}	
		}
				
		$view -> currenttopic = $currenttopic;
		$view -> form = $form;
		$view -> char = $char;		
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;
		
	}
	
	function deletekingdomtopic( $topic_id )
	{
		$char = Character_Model::get_info( Session::instance() -> get('char_id') );		
		$topic = ORM::factory('kingdom_forum_topic', $topic_id);			
		if ( Kingdom_Forum_Topic_Model::haswriterights( $char, 
			$topic -> kingdom_forum_board -> kingdom ) == false )
		{
			Session::set_flash('user_message', 
				"<div class=\"error_msg\">".kohana::lang('global.operation_not_allowed')."</div>"); 								
			url::redirect('/region/kingdomreplies/' . $topic -> kingdom_forum_board -> kingdom_id . '/' . 
				$topic -> id);
		}
		
		$c = Topic_Factory_Model::create('kingdom');
		$rc = $c -> delete( $char, $topic, $message );
		kohana::log('debug', 'rc: ' . $rc);
		if ($rc == true )
		{
			Session::set_flash('user_message', 
			"<div class=\"info_msg\">".kohana::lang('kingdomforum.info-topicdeleted')."</div>"); 		
			
			url::redirect('region/kingdomtopics/' . $topic -> kingdom_forum_board -> kingdom_id . '/' .
				$topic -> kingdom_forum_board -> id );			
		}
		else
		{
			kohana::log('debug', 'rc: ' . $rc);
			Session::set_flash('user_message', 
			"<div class=\"error_msg\">".kohana::lang('global.operation_not_allowed')."</div>"); 		
			
			url::redirect('region/kingdomtopics/' . $topic -> kingdom_forum_board -> kingdom_id . '/' . 
				$topic -> kingdom_forum_board -> id );			
		}
	}
	
/**
* Mostra le leggi della regione
* @param region_id id regione
* @return none
*/

function info_laws( $region_id  )
{
	
	$char = Character_Model::get_info( Session::instance()->get('char_id') );	
	$region = ORM::factory('region', $region_id );
	$view = new View ( 'region/info_laws');
	$sheets  = array('gamelayout'=>'screen',  'submenu'=>'screen');
	$subm    = new View ('template/submenu');
	$lnkmenu = array(
		'region/info/' . $region -> id => kohana::lang('regionview.submenu_generalinfo'),		
		'region/info_laws/' . $region -> id => array( 'name' => kohana::lang('regionview.submenu_laws'), 'htmlparams' => array( 'class' => 'selected' )),
		'region/info_diplomacy/' . $region -> id => kohana::lang('regionview.submenu_diplomacy'),
		); 
	
	$limit = 5	;
	
	////////////////////////////////////////////
  // le leggi valide sono quelle della regione
  // che controlla (vassallo)
  //////////////////////////////////////////////			
		
		
		$laws = ORM::factory( 'law' )	-> where( 'kingdom_id', $region -> kingdom -> id ) -> find_all();			
		
		$this -> pagination = new Pagination(array(
			'base_url'=>'region/info_laws/',
			'uri_segment'=> $region_id,
			'style' => "extended",
			'total_items'=> $laws-> count(),
			'items_per_page' => $limit));				
		
		$laws = ORM::factory( 'law' )	-> where( 'kingdom_id', $region -> kingdom -> id ) -> find_all($limit, $this -> pagination->sql_offset);
		
		$subm->submenu = $lnkmenu;
		
		$view->pagination = $this->pagination;
		$view->laws = $laws;
		$view->region = $region;
		$view->submenu = $subm;
		
		$this->template->content = $view;
		$this->template->sheets = $sheets;	

}

	function retire()
	{
		$view    = new view( 'region/retire' );
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		
		$this->template->content = $view;
		$this->template->sheets = $sheets;
	
	}

	function confirm_retire()
	{
		$ca = Character_Action_Model::factory("retire");		
		$par[0] = Character_Model::get_info( Session::instance()->get('char_id') );
		$par[1] = $this->input->post('days');
		
		if ( $ca->do_action( $par,  $message ) )
		{
		 	Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
			url::redirect('region/view/');
		}
		else			
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");					
			url::redirect( 'region/retire');
		}
	}

	function mapgetinfo()
	{
		if ( request::is_ajax() )
		{
			kohana::log('debug', 'Received an ajax call.'); 
			kohana::log('debug', kohana::debug($this -> input -> post())); 
			$this->auto_render = false;
			//echo json_encode( $a );
			return;
		}
	}

	/**
	* Verifica se è possibile costruire la struttura
	* specificata nella regione specificata	
	* @param: none
	* @return: costo in FP
	*/
	
	public function checkprojectfeasibility()
	{
		
		if ( request::is_ajax() )
		{			
			
			$this -> auto_render = false;
			
			//kohana::log('debug', kohana::debug( $this -> input -> post() )); 
			
			$sourceregion = ORM::factory('region', $this -> input -> post('sourceregion_id'));			
			$destregion = ORM::factory('region',   $this -> input -> post('destregion_id') );			
			$cfgkingdomproject = ORM::factory('cfgkingdomproject', $this -> input -> post('cfgkingdomproject_id') );			
			$structure_type  = ORM::factory('structure_type', $this -> input -> post('structure_type_id') );
			$sourcestructure = ORM::factory('structure', $this ->input -> post( 'structure_id') );
			
			$result = CfgKingdomproject_Model::checkprojectfeasibility( 
				$cfgkingdomproject, 
				$structure_type, 
				$sourceregion, 
				$destregion, 
				$sourcestructure );			
			
			//kohana::log('debug', kohana::debug( $result )); 
			
			$result['position'] = $this -> input -> post('position');
			
			echo json_encode( $result ); 
		}
		
	}
	
}
