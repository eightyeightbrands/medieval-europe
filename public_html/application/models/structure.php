<?php defined('SYSPATH') OR die('No direct access allowed.');

class Structure_Model extends ORM
{
	protected $table_name = "structures";
	protected $belongs_to = array('structure_type', 'region', 'character');
	protected $has_many = array('item', 'structure_event', 'structure_grant', 'structure_resource', 'structure_stat',
	'structure_option');
	// extra parametri rispetto a quelli in tabella.
	protected $maxlevel = 1;
	protected $currentlevel = 1;
	protected $price;
	protected $baseprice;
	protected $storage;
	protected $restfactor;
	protected $isbuyable;
	protected $sellingprice;
	protected $issellable;
	protected $hoursfornextlevel = array();
	protected $neededmaterialfornextlevel= array();
	protected $supertype;
	protected $parenttype;
	protected $wikilink;
	protected $isupgradable = false;

	const DAMAGEPERCENTAGELIMIT = 30;

	/**
	* Inizializza la classe
	*/

	public function init() {}

	/** Funzione che istanzia la giusta struttura figlia
	* l' id comanda sul tipe. Se l' id � passato si
	* ritorner� un modello basato sul type dell' istanza
	* @param: str $structure_type Tipo struttura
	* @param: int $id ID struttura
	* @return obj $s Modello struttura o null
	*/


	public static function factory( $structure_type, $structure_id, $level = 1 )
	{
		$s = null;

		if ( !is_null($structure_id) )
		{
			$st = ORM::factory("structure", $structure_id );
			if ( $st->loaded )
			{
				$model = ("ST_".ucfirst( $st->structure_type->type ) . "_Model");
				$s = new $model;
				return $s;
			}
			else
				return null;
		}

		if ( !is_null ($structure_type) )
		{
			$model = ("ST_".ucfirst( $structure_type ) . "_Model");

			$structure_type = ORM::factory( 'structure_type' )
				->where ( array (
					'type' => $structure_type,
					'level' => $level )
					) -> find();

			if ( $structure_type -> loaded )
			{

				$s = new $model;
				$s -> structure_type_id = $structure_type->id;
				return $s;
			}
			else
				return null;
		}

	}

	// setta i link comuni a tutte le strutture
	// todo: leggere matrice azioni/link da db

	public function build_common_links( $structure, $workerbonus = false )
	{

		// Show game description in popup
		$links = kohana::lang('structures.' . $structure -> getSupertype() . '_desc' );

		$links .= "<br/><br/>";
		$links .= html::anchor(
			'https://wiki.medieval-europe.eu/index.php?title=' . $this -> getWikilink(),
			Kohana::lang('global.wikisection'),
			array(
				'class' => 'st_help_command',
				'target' => 'blank' )
		);
		$links .= "<br/><br/>";

		if ( !is_null($structure -> status) and $structure -> status == 'upgrading' )
		{
			$links .= Kohana::lang('global.status') . ': ' . kohana::lang('structures.prj_status_upgrading') . '<br/>';

			$upgradeinfo = $structure -> getUpgradeinfo();
			$workedhours = $this -> getUpgradeworkedhours();

			$links .= kohana::lang('structures.overallprogress') . ": " . $workedhours . "/" . $upgradeinfo['hours'] . " ("
				. Utility_Model::number_format($workedhours/$upgradeinfo['hours']*100,2). "%)<br/>";

			$links .= html::anchor(
				"/structure/upgrade/" . $structure -> id,
				kohana::lang('global.build'),
				array(
				'title' => Kohana::lang('global.build'),
				'class' => 'st_common_command')) . "<br/>";
		}

		return $links;
	}

	/**
	* Modifica denari all' interno di una struttura
	* @param float delta in silvercoins (es: -3.2)
	* @param string causale
	* @param boolean invia evento
	* @return none
	*/

	public function modify_coins( $delta, $reason = 'notspecified', $sendevent = true )
	{
		$db = Database::instance();

		kohana::log( 'debug', '-> Modifying coins for Structure: ' . $this -> id . ', reason: ' . $reason . ', delta: ' . $delta );

		Trace_Sink_Model::add( 'silvercoin', $this -> id, round($delta, 2), $reason, 'structure' );

		$orgdelta = $delta;
		$delta *= 100;

		$silvercoins = $this -> get_item_quantity( 'silvercoin' );
		$coppercoins = $this -> get_item_quantity( 'coppercoin' );

		kohana::log( 'debug', '-> (current coins) -> Structure has silvercoins: ' . $silvercoins . ' coppercoins: ' . $coppercoins );

		$this -> convertcoppercoins();

		$silvercoins = $this -> get_item_quantity( 'silvercoin' );
		$coppercoins = $this -> get_item_quantity( 'coppercoin' );

		kohana::log( 'debug', '-> (current coins a.c.) -> Structure has silvercoins: ' . $silvercoins . ' coppercoins: ' . $coppercoins );

		$deltasilvercoins = intval($delta/100);
		$deltacoppercoins = $delta - ($deltasilvercoins * 100);

		$silvercoin_item = Item_Model::factory( null, 'silvercoin' );
		$coppercoin_item = Item_Model::factory( null, 'coppercoin' );

		if ( $delta > 0 )
		{
			$silvercoin_item -> additem( "structure", $this -> id, abs($deltasilvercoins) );
			$coppercoin_item -> additem( "structure", $this -> id, abs($deltacoppercoins) );
		}
		else
		{

			if ( $coppercoins < abs($deltacoppercoins) )
			{
				$silvercoin_item -> removeitem( "structure", $this -> id, abs($deltasilvercoins) + 1);
				$coppercoin_item -> additem( "structure", $this -> id, 100 - abs( $deltacoppercoins ) );
			}
			else
			{
				$silvercoin_item -> removeitem( "structure", $this -> id, abs($deltasilvercoins) );
				$coppercoin_item -> removeitem( "structure", $this -> id, abs($deltacoppercoins) );
			}

		}

		$this -> convertcoppercoins();

		$silvercoins = $this -> get_item_quantity( 'silvercoin' );
		$coppercoins = $this -> get_item_quantity( 'coppercoin' );

		kohana::log( 'debug', '-> (recreated coins) -> Structure has silvercoins: ' . $silvercoins . ' coppercoins: ' . $coppercoins );

		if ( $sendevent)
			Structure_Event_Model::newadd(
				$this -> id,
				'__events.structurecoinsreceived' .
				';' . $orgdelta .
				';__charactions.reason_' . $reason );

		// nuovo metodo

		$this -> silvercoins = $silvercoins + $coppercoins / 100;
		kohana::log( 'debug', "-> Stored {$this -> silvercoins} on Structure Entity." );


		return;

	}

	/**
	* converte i copper coin in silvercoins
	* @param none
	* @return none
	*/

	public function convertcoppercoins( )
	{
		$amount = $this -> get_item_quantity( 'coppercoin' );

		$deltasilvercoins = intval( $amount / 100 );
		$deltacoppercoins = $amount - ($deltasilvercoins * 100 );
		$silvercoin_item = Item_Model::factory( null, 'silvercoin' );
		$coppercoin_item = Item_Model::factory( null, 'coppercoin' );
		$silvercoin_item -> additem( "structure", $this -> id, abs($deltasilvercoins) );
		$coppercoin_item -> removeitem( "structure", $this -> id, abs($deltasilvercoins*100) );
	}


	/**
	* Setta i link speciali, ma comuni a tutte le strutture
	* @param obj $structure Structure_Model Struttura
	* @return str $links HTML da visualizzare
	*/

	public function build_special_links( $structure, $workerbonus = false )
	{

		$links = "";
		kohana::log('debug', 'called structure bsl');
		// carica info della struttura
		$s = $structure;

		$links .= html::anchor( "/structure/manage/" .
			$structure -> id, Kohana::lang('global.manage'), array('class' => 'st_special_command')). "<br/>";

		///////////////////////////////////////
		// se il char � owner della directory,
		// aggiungi inventory
		///////////////////////////////////////

		if ( Session::instance() -> get('char_id') == $s -> character_id )
			$links .=
				html::anchor( "/structure/inventory/" . $structure -> id, Kohana::lang('global.inventory'),
					array('title' => Kohana::lang('global.inventory'), 'class' => 'st_special_command'))
				. "<br/>";

		return $links;
	}

	/**
	* Ritorna l' inventory della struttura
	* @param int $structure_Id ID struttura
	* @param boolean $ismarket identifica se la struttura � un market
	* @return array $items vettore
	*  items => elenco di oggetto items, itemsweight => peso totale, storableweight => peso sopportabile dalla struttura.
	*/


	public function inventory( $structure_id, $ismarket = false )
	{

		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		$items = array( 'items' => null, 'totalitemsweight' => 0, 'storableweight' => 0 );

		if ($ismarket)
			$sql = "
				select i.id item_id, ci.*, i.*, c.name seller_name
				from items i, cfgitems ci, characters c
				where i.cfgitem_id = ci.id
				and   i.seller_id = c.id
				and   i.structure_id ={$structure_id}
				order by ci.tag asc, i.price asc, i.salepostdate asc ";
		else
			$sql = "
				select i.id item_id, i.*, ci.*
				from items i, cfgitems ci
				where i.cfgitem_id = ci.id
				and i.structure_id = {$structure_id}
				order by ci.tag asc, i.price asc, i.salepostdate asc ";

		$res = Database::instance() -> query($sql);

		$itemsweight = 0;
		$k = 0;

		foreach ( $res as $item )
		{

			$item -> totalweight = $item -> weight * $item -> quantity;
			$item -> actions = Item_Model::get_actions( $item );
			$items['items'][$item->parentcategory][] = $item;
			$items['items']['all'][] = $item;
			$items['totalitemsweight'] += $item -> totalweight;

			/*
			$item -> totalweight = $item -> weight * $item -> quantity;
			$items['items'][$k] = $item;
			$items['totalitemsweight'] += $item -> totalweight;
			*/

			$k++;
		}

		//var_dump( $items );exit;

		return $items;

	}


	/**
	 * Calcola la capacit� rimanente di stoccaggio
	 * della struttura.
	 * Di default per tutte le strutture � 100000 Kg.
	 * Il metodo va overridato nelle singole classi
	 * in caso di necessit�
	 * @param  none
	 * @return int $sw;
	*/

	public function get_storableweight( )
	{
		//print kohana::debug( $structure );exit();
		$itemsweight = 0;

		foreach ( $this -> item as $item )
			$itemsweight += $item -> cfgitem -> weight * $item->quantity ;

		$sw = $this -> getStorage() -  $itemsweight ;

		kohana::log('debug', 'Storable weight for ' . $this -> id . ': ' . $sw );

		return $sw;

	}


	/**
	*	Torna il nome della struttura con il giusto articolo
	* @param nessuno
	* @return stringa con l' articolo
	*/

	public function get_structurearticle()
	{

		switch ( $this -> structure_type -> type )
		{
			case 'terrain' :
			case 'breeding_cow' :
			case 'breeding_sheep' :
			case 'breeding_pig' :
			case 'breeding_silkworm' :
			case 'trainingground_1' :
				return 'global.al_tuo'; break;
			case 'house' :
			case 'shop':
			case 'academy_1':
			case 'court':
				return 'global.alla_tua'; break;
			case 'royalpalace':
			case 'castle' :
				return 'global.al' ; break;
			case 'barracks' :
				return 'global.alle ' ; break ;
			default: return 'global.al' ; break;
		}

	}

	/**
	* Controlla se una struttura ha almeno un certo numero di oggetti
	* @param string $itemtag Tag item
	* @param int $quantity Quantit� item
	* @return boolean true if found, false otherwise
	*/

	function contains_item( $tag, $quantity = 1 )
	{
		kohana::log('debug', '-> Item to search: ' . $tag . ',quantity: ' . $quantity );

		$sql = "select sum(quantity) quantity
			from structures s, items i, cfgitems ci
			where i.structure_id = s.id
			and   i.cfgitem_id = ci.id
			and   ci.tag = '{$tag}'
			and   s.id = {$this -> id}" ;

		$res = Database::instance() -> query ( $sql ) -> as_array();
		if ( $res[0] -> quantity < $quantity )
			return false;
		else
			return true;

	}


/**
* Verifies if the character can access the structure
* @param: obj $char Char_Model character that wants to access the structure
* @param string $parenttype parenttype of structure that is accessed
* @param string $message error message to retugn
* @param string $accesstype: Access type�(private o public)
* @param string $action: Action that the char wants to execute
* @return boolean esito (false/true)
*/

	function allowedaccess( $char, $parenttype = 'notset', &$message, $accesstype = 'private', $action = null )
	{

		kohana::log('info', '----- CHECKING ALLOWED ACCESS -----' );

		kohana::log(
			'info',
			"-> Checking access. Structure: {$this -> id}, Region_id: {$this -> region_id}, Position_id: {$char -> position_id}, parenttype: {$parenttype}, action: {$action}, char: {$char -> name}"
		);

		$message = kohana::lang( 'global.operation_not_allowed');

		/////////////////////////////////////////////////////////
		// controllo che il char esista
		/////////////////////////////////////////////////////////

		if ( !$char -> loaded )
		{
			kohana::log('info', '-> Char was not loaded. ' );
			return false;
		}

		/////////////////////////////////////////////////////////
		// controllo che la struttura esista
		/////////////////////////////////////////////////////////

		$role = $char -> get_current_role();

		if ( !$this -> loaded )
		{
			kohana::log('info', ' -> Structure was not loaded. ' );
			return false;
		}

		/////////////////////////////////////////////////////////
		// se la condizione della struttura non � < 30% solo alcu
		// ne funzioni sono abilitate
		/////////////////////////////////////////////////////////

		if ( $this -> state < self::DAMAGEPERCENTAGELIMIT and !in_array( $action, array( 'damage', 'repair', 'donate' ) ) )
		{
			kohana::log('info', '-> Structure is too much damaged. ' );
			$message = kohana::lang( 'structures.structureistoodamaged');
			return false;
		}

		/////////////////////////////////////////////////////////
		// il char deve essere nello stesso nodo della struttura.
		/////////////////////////////////////////////////////////

		if ( $this -> region_id != $char -> position_id )
		{
			kohana::log('info', '-> Char is not in the same location of the structure. ' . $this -> region_id . ' - ' . $char -> position_id );
			return false;
		}

		/////////////////////////////////////////////////////////
		// Se la struttura � in una regione indipendente non
		// si pu� accedere
		/////////////////////////////////////////////////////////

		if ( $this -> region -> kingdom -> image == 'kingdom-independent' and $this -> getParenttype() != 'fish_shoal' )
		{
			kohana::log('info', '-> Region is independent!' );
			return false;
		}

		// Azione deposit vietata per mercato

		if ( $action == 'donate' and $this -> structure_type -> type == 'market')
		{
			kohana::log('info', '-> Deposit not allowed on market.' );
			return false;

		}

		/////////////////////////////////////////////////////////
		// Controlli per funzioni private
		/////////////////////////////////////////////////////////

		kohana::log('info', '-> Checking Private access...');

		if ( $accesstype == 'private' )
		{

			/////////////////////////////////////////////////////////
			// la azione � disponibile nella struttura?
			/////////////////////////////////////////////////////////
			kohana::log('info', '-> Checking if function is available in structure.');
			if ( $this -> check_allowedfunction( $action ) == false )
			{
				kohana::log('info', "-> Char is not enabled to the function: {$action}." );
				return false;
			}

			/////////////////////////////////////////////////////////
			// il char ha il corretto profilo per l' azione sulla
			// struttura?
			/////////////////////////////////////////////////////////
			kohana::log('info', '-> Checking if char has correct grants.');
			$grants = Structure_Grant_Model::get_chargrants( $this, $char );
			if ( $this -> get_accessallowed( $action, $grants ) == false )
			{
				kohana::log('info', '-> Char has not the right grant/profile.' );
				return false;
			}

			////////////////////////////////////////////////////////////////////
			// se il char riveste un ruolo, deve essere vestito dignitosamente
			// e deve essere elegibile, ossia avere i requisiti di nomina
			////////////////////////////////////////////////////////////////////
			kohana::log('info', '-> Checking if char is dressed properly.');
			if ( !is_null( $role ))
			{
				$equipment = Character_Model::get_equipment( $char -> id );

				if ( $this -> character_id == $char -> id and !is_null ($role) and $this -> structure_type -> subtype != 'player' )
				{
					if
						( Character_Model::is_naked( $char -> id ) == true or
							(
								(isset($equipment['torso']) and $equipment['torso'] -> tag == 'rags_shirt') or
								(isset($equipment['legs']) and $equipment['legs'] -> tag == 'rags_trousers') or
								(isset($equipment['body']) and $equipment['body'] -> tag == 'rags_robe')
							)
					)
					{
						$message = kohana::lang('structures.charhasrole_notproperlydressed');
						kohana::log('info', '-> Char is not properly dressed.' );
						return false;
					}
				}

				kohana::log('info', '-> Checking if char is eligible for operating the structure.');

				if (
					$this -> structure_type -> subtype != 'player'
					and $this -> character_id == $char -> id
					and $role -> check_eligibility( $char, $role -> tag, $char -> church, $message )  == false )
				{
					kohana::log('info', '-> Char is not eligible to address.' );
					return false;
				}

			}


			/////////////////////////////////////////////////////////
			// se il char possiede pi� di un negozio non pu�
			// accedere a nessuno di essi
			/////////////////////////////////////////////////////////

			kohana::log('info', '-> Checking if char has too many shops.');

			if (
				!in_array( $action, array( 'sell', 'inventory', 'take' ))
				and
				$this -> structure_type -> parenttype == 'shop'
			)
			{
				$ownedshops = $char -> count_my_structures( 'shop' );

				// se ha  un numero di negozi >= a quelli permessi errore
				// a meno che la chiesa non abbia il bonus ecc.

				if ($ownedshops > Kohana::config('medeur.maxshops'))
				{

					$churchhasoraetlaborabonus = Church_Model::has_dogma_bonus( $char -> church_id, 'craftblessing');
					$charhasfpcontribution = Character_Model::get_achievement( $char -> id, 'stat_fpcontribution');

					if (
						$churchhasoraetlaborabonus == false
						or
						( is_null($charhasfpcontribution) or $charhasfpcontribution['stars'] < 3 )
					)
					{
						$message = kohana::lang('structures.error-toomanystructures');
						kohana::log('info', '-> Char has too many shops.' );
						return false;
					}
				}
			}

			/////////////////////////////////////////////////////////
			// se il char possiede pi� di due terreni non pu�
			// accedere a nessuno di essi
			/////////////////////////////////////////////////////////
			kohana::log('info', '-> Checking if char has too many terrains.');
			if ( $this -> structure_type -> parenttype == 'terrain' and $char -> count_my_structures("terrain") > Kohana::config('medeur.maxterrains'))
			{
				$message = kohana::lang('structures.error-toomanystructures');
				kohana::log('info', '-> Char has too many terrains.' );
				return false;
			}

		}

		// controlli per edifici religiosi
		kohana::log('info', '-> Checking CHURCH permissions');
		if ( $this -> structure_type -> subtype == 'church' )
		{

			////////////////////////////////////////////////////
			// Un giocatore di relig. diversa pu� solo accedere
			// alla funzione initiate
			////////////////////////////////////////////////////

			kohana::log('info', '-> Checking if char is of the right religion.');

			if (
			$this -> structure_type -> church_id != $char -> church_id
			and
			! in_array(
					$action,
					array (
					'info',
					'initiate',
					'buyitems',
					'donate'
					)
				)
			)
			{
				kohana::log('info', $char -> name . ' church: ' . $char -> church_id . ' structure church: ' .
					$this -> structure_type -> church_id  );
				$message = kohana::lang('structures.charofdifferentchurch');
				return false;
			}

		}

		kohana::log('info', '-> *** Char can access the structure. ***');
		return true;
	}

	/**
	* Ritorna info sulla struttura
	* @param none
	* @return array $info
	*     obj: contiene l oggetto struttura
	*     structurename: nome struttura
	*     kingdomproject: info sul progetto regno
	*     structureoptions: opzioni struttura
	*     resource: informazioni su risorse
	*     faithpoints: informazioni su FP
	*  oppure null (se la struttura non � stata costruita)
	*/

	function get_info()
	{

		$character = Character_Model::get_info( Session::instance() -> get('char_id') );

		$info = array(
			'obj' => $this,
			'structurename' => '',
			'kingdomproject' => null,
			'structureoptions' => null,
			'resources' => null,
			'faithpoints' => 0
		);

		// recupero i faith points

		$faithpoints = Structure_Model::get_stat_d( $this -> id, 'faithpoints' );
		if ($faithpoints -> loaded)
			$info['faithpoints'] = $faithpoints -> value;
		else
			;

		// compilo le info per i progetti

		$project = ORM::factory('kingdomproject')
			-> where ( array(
				'structure_id' => $this -> id )
				)
			-> find();

		if ( $project -> loaded )
		{

			$info['kingdomproject'] = $project -> get_info();

			if ( is_null ( $this -> attribute6 ) )
				$info['structurename'] = kohana::lang($info['kingdomproject']['builtstructure'] -> name);
			else
				$info['structurename'] = kohana::lang($info['kingdomproject']['builtstructure'] -> name) . ' (' . $this -> attribute6 . ')' ;
		}
		else
		{
			$info['structurename'] = $this -> getName();
		}

		// compilo le info per le opzioni

		foreach ( $this -> structure_option as $option )
			$info['structureoptions'][$option -> name] = $option -> value ;

		// compilo le info per lo stato delle risorse, se il char ha int > 18
		if ( $character -> get_attribute( 'intel' ) >=  18)
		{

			$info['resources']['structuresize'] = $this -> size;
			foreach ( $this -> structure_resource as $resource )
			{
				$availability = round( $resource -> current / $resource -> max * 100, 0);
				$info['resources']['availability'][ $resource -> resource ] = $availability;
			}
		}

		return $info;
	}


	/**
	* ritorna la quantit� di un certo item in una struttura.
	* @param string tag dell' item
	* @return int numero di item trovati nella struttura
	*/

	function get_item_quantity( $tag )
	{

		$db = Database::instance();

		$res = $db -> query( "
		select ifnull(sum(quantity),0) q from items i, cfgitems ci
		where i.cfgitem_id = ci.id
		and   ci.tag = '$tag'
		and structure_id = " . $this -> id ) -> as_array();
		//var_dump($res);exit;
		return $res[0] -> q ;

	}

	/**
	* Torna il numero di item in una struttura (funzione statica)
	* @param id ID struttura
	* @return int quantit�
	*/

	public function get_item_quantity_s( $structure_id, $tag)
	{
		$res = Database::instance() -> query(
			"
			SELECT COALESCE(SUM(i.quantity), 0) quantity
			FROM items i, cfgitems ci
			WHERE i.structure_id = {$structure_id}
			AND   i.cfgitem_id = ci.id
			AND   ci.tag = '{$tag}'") -> as_array();

		return $res[0] -> quantity;

	}

	/**
	* Torna gli item in una struttura
	* @param none
	* @return array vettore con items
	*/

	function get_items()
	{
		$db = Database::instance();
		$items = $db -> query ( "
			select i.*, c.name, c.description, c.tag, c.weight, c.category from items i, cfgitems c
			where i.cfgitem_id = c.id
			and structure_id = " . $this -> id) -> as_array();

		return $items;

	}

	/**
	* ritorna se c'� una pending action sulla struttura.
	* @param character_id : id char
	* @return false o true
	*/

	function get_pending_actions( $character_id )
	{

		$actions = ORM::factory('character_action') ->
			where( array (
				'status' => 'running',
				'structure_id' => $this -> id ) ) -> find_all() ;

		//kohana::log('debug', kohana::debug( $actions ) ) ;

		if ( count( $actions) > 0 )
			return true;
		else
			return false;

	}

	/**
	* Trasferisce la propriet� di una struttura
	* @param Character_Model $seller: Acquirente
	* @param Character_Model $buyer: Venditore
	* @return none
	*/

	function transfer_ownership( $seller, $buyer )
	{

		$this -> character_id = $buyer -> id ;
		$this -> save();

		// invalida cache

		$cachetag = '-charstructuregrant_' . $seller -> id . '_' . $this -> id;
		My_Cache_Model::delete($cachetag);
		$cachetag = '-charstructuregrant_' . $buyer -> id . '_' . $this -> id;
		My_Cache_Model::delete($cachetag);

		// evento a vassallo
		$vassal = $this -> region -> get_roledetails ( 'vassal' ) ;
		if ( ! is_null( $vassal ) )
		{

			Character_Event_Model::addrecord( $vassal->id,
				'normal', '__events.market_transferedpropertyownership' .
				';' . $seller -> name  .
				';__' . $this -> structure_type -> name .
				';' . $buyer -> name, 'evidence' ) ;


		}
	}

	/**
	* Destroy a structure
	* @param none
	* @return none
	*/

	public function destroy()
	{

		// if it's a shop destroy the license scroll

		kohana::log('debug', "-> Destroying structure {$this->id}");
		if ( $this -> structure_type -> parenttype == 'shop' )
		{

			$cfgitem = ORM::factory('cfgitem') -> where ( 'tag', 'scroll_propertylicense') -> find();
			$propertylicense = ORM::factory('item') -> where (
				array(
					'cfgitem_id' => $cfgitem -> id,
					'param2' => $this -> id
					) ) -> find();
			$propertylicense -> destroy();

			// remove craftingitem perc statistics

			$itemprogress = ORM::factory( 'character_stat' )
			-> where ( array (
				'character_id' => $this -> character_id,
				'name' => 'craftingitemperc',
				'param2' => $this -> id ) ) -> delete_all();

		}


		// remove character_actions

		ORM::factory('character_action') -> 	where( array ( 'structure_id' => $this -> id) ) -> delete_all();

		// character_premiumbonuses

		ORM::factory('character_premiumbonus') -> 	where( array ( 'structure_id' => $this -> id) ) -> delete_all();

		// roles

		ORM::factory('character_role') -> 	where( array ( 'structure_id' => $this -> id) ) -> delete_all();

		// character_sentences

		ORM::factory('character_sentence') -> 	where( array ( 'structure_id' => $this -> id) ) -> delete_all();

		// jobs

		ORM::factory('job') -> 	where( array ( 'structure_id' => $this -> id) ) -> delete_all();

		// kingdomprojects

		ORM::factory('kingdomproject') -> 	where( array ( 'structure_id' => $this -> id) ) -> delete_all();

		// structure_events

		ORM::factory('structure_event') -> 	where( array ( 'structure_id' => $this -> id) ) -> delete_all();

		// structure_grants

		ORM::factory('structure_grant') -> 	where( array ( 'structure_id' => $this -> id) ) -> delete_all();

		// structure_options

		ORM::factory('structure_option') -> 	where( array ( 'structure_id' => $this -> id) ) -> delete_all();

		// structure_resources

		ORM::factory('structure_resource') -> 	where( array ( 'structure_id' => $this -> id) ) -> delete_all();

		// structure_stats

		ORM::factory('structure_stat') -> 	where( array ( 'structure_id' => $this -> id) ) -> delete_all();

		// remove all items from structure

		$items = ORM::factory('item') -> 	where( array ( 'structure_id' => $this -> id) ) -> find_all();

		foreach ( $items as $item )
			$item -> destroy();

		// blank lent itemid
		Database::instance() -> query("update items set lend_id = null where lend_id in
			(select id from structure_lentitems where structure_id = {$this -> id})");

		Database::instance() -> query("delete from structure_lentitems where structure_id = {$this -> id}");

		$items = ORM::factory('item') -> 	where(
			array (
				'structure_id' => $this -> id) ) -> find_all();
		foreach ( $items as $item )
			$item -> destroy();

		$region_id = $this -> region_id;
		$this -> delete();

		// aggiorna cache

		$cachetag = '-regionstructures_' . $region_id;
		My_Cache_Model::delete($cachetag);

	}

	/**
	* controlla lo stato della risorsa principale
	* @param type  tipo risorsa
	* @param quantity quantit� da controllare
	* @return true o false
	*/

	public function check_resource_status( $type, $quantity )
	{
		foreach ( $this -> structure_resource as $resource )
			if ( $resource -> resource == $type and $resource -> current < $quantity )
			{
				return false;
			}

		return true;
	}

	/**
	* Trova i progetti in costruzione
	* Legati alla struttura
	* @param none
	* @return array $runningprojects Running Projects
	*/

	public function getlinkedrunningprojects()
	{

		$role = $this -> character -> get_current_role();
		$db = Database::instance();

		if ( $this -> structure_type -> subtype != 'church' )
			if ( $this -> structure_type -> type == 'royalpalace' )
				$sql = "select k.*
					from kingdomprojects k, cfgkingdomprojects ck, structures s
					where  k.cfgkingdomproject_id = ck.id
					and    ck.owner in ( 'king', 'vassal' )
					and    k.status != 'completed'
					and    k.structure_id = s.id
					and    s.region_id in (
						select id from regions where kingdom_id = " . $this -> region -> kingdom_id . ")
					order by k.region_id asc";
			else
				$sql = "select k.*
					from kingdomprojects k, cfgkingdomprojects ck, structures s
					where  k.cfgkingdomproject_id = ck.id
					and    ck.owner = '" . $role -> tag . "'
					and    k.status != 'completed'
					and    k.structure_id = s.id
					and    s.parent_structure_id = " . $this -> id . "
					and    s.region_id in (
						select id from regions where kingdom_id = " . $this -> region -> kingdom_id . ")
					order by k.region_id asc";

		if ( $this -> structure_type -> subtype == 'church' )
		{
			if ( $this -> structure_type -> type == 'religion_1' )
				$sql = "
					select k.*
					from kingdomprojects k, cfgkingdomprojects ck, structures s, structure_types st
					where  k.cfgkingdomproject_id = ck.id
					and    ck.owner in ( 'church_level_1', 'church_level_2', 'church_level_3' )
					and    k.status != 'completed'
					and    s.attribute1 = st.id
					and    st.church_id = {$this -> structure_type -> church_id}
					and    k.structure_id = s.id";
			else
				$sql = "select k.*
					from kingdomprojects k, cfgkingdomprojects ck, structures s
					where  k.cfgkingdomproject_id = ck.id
					and    ck.owner = '" . $role -> tag . "'
					and    k.status != 'completed'
					and    k.structure_id = s.id
					and    s.parent_structure_id = " . $this -> id . "
					order by k.region_id asc";
		}

		kohana::log('debug', $sql );

		$runningprojects = $db -> query( $sql ) -> as_array();
		return $runningprojects;

	}

	/**
	* Trova i progetti completati
	* Legati alla struttura
	* @param none
	* @return array $completedprojects Progetti completati
	*/

	public function getlinkedcompletedprojects()
	{

		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		$role = $char -> get_current_role();
		$db = Database::instance();

		if ( $this -> structure_type -> subtype != 'church' )
			if ( $this -> structure_type -> type == 'royalpalace' )
				$sql = "select k.*
					from kingdomprojects k, cfgkingdomprojects ck, structures s
					where  k.cfgkingdomproject_id = ck.id
					and    ck.owner in ( 'king', 'vassal' )
					and    k.status = 'completed'
					and    k.structure_id = s.id
					and    s.region_id in (
						select id from regions where kingdom_id = " . $this -> region -> kingdom_id . ")
					order by k.region_id asc " ;
			else
				$sql = "select k.*
					from kingdomprojects k, cfgkingdomprojects ck, structures s
					where  k.cfgkingdomproject_id = ck.id
					and    ck.owner = '" . $role -> tag . "'
					and    k.status = 'completed'
					and    k.structure_id = s.id
					and    s.parent_structure_id = " . $this -> id . "
					and    s.region_id in (
						select id from regions where kingdom_id = " . $this -> region -> kingdom_id . ")
					order by k.region_id asc " ;

		if ( $this -> structure_type -> subtype == 'church' )
			if ( $this -> structure_type -> type == 'religion_1' )
				$sql = "
					select k.*
					from kingdomprojects k, cfgkingdomprojects ck, structures s, structure_types st
					where  k.cfgkingdomproject_id = ck.id
					and    ck.owner in ( 'church_level_1', 'church_level_2', 'church_level_3' )
					and    k.status = 'completed'
					and    s.attribute1 = st.id
					and    st.church_id = {$this -> structure_type -> church_id}
					and    k.structure_id = s.id";
			else
				$sql = "select k.*
					from kingdomprojects k, cfgkingdomprojects ck, structures s
					where  k.cfgkingdomproject_id = ck.id
					and    ck.owner = '" . $role -> tag . "'
					and    k.status = 'completed'
					and    k.structure_id = s.id
					and    s.parent_structure_id = " . $this -> id . "
					order by k.region_id asc " ;

		//var_dump( $sql ); exit;

		$projects = $db -> query( $sql ) -> as_array();
		return $projects;

	}

	/**
	* Trova i progetti che l' owner della struttura
	* pu� lanciare.
	* @return
	*    array
	*      'struttura' => elenco di regioni dove si pu� costruire
	*/

	public function get_startableprojects()
	{

		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		$role = $char -> get_current_role();
		$db = Database::instance();
		$result = array();

		if ( $this -> structure_type -> subtype != 'church' )
			$sql = "select ck.*, st.id st_id from cfgkingdomprojects ck, structure_types st
			where owner = '" . $role -> tag . "'" .
			" and  ck.tag = st.type " ;
		else
			$sql = "select ck.*, st.id st_id from cfgkingdomprojects ck, structure_types st
			where owner = '" . $role -> tag . "'" .
			" and  ck.tag = st.type
			  and  st.church_id = " . $this -> structure_type -> church_id;

		$startableprojects = $db -> query( $sql ) -> as_array();

		//var_dump( $sql ); exit;

		$i=0;
		foreach ( $startableprojects as $sp )
		{
			$combo = null;
			$result[$i]['structure_type'] = ORM::factory('structure_type', $sp -> st_id );
			$result[$i]['dependingstructure_type'] = ORM::factory('structure_type', $sp -> required_structure_type_id );
			//$regions = $result[$i]['structure_type'] -> getpotentialbuildinglocations($this);
			//foreach ( $regions as $r )
				//$combo[$r -> id] = kohana::lang($r -> name );
			//$result[$i]['regions'] = $combo;
			$result[$i]['cfgkingdomproject']['obj'] = ORM::factory('cfgkingdomproject', $sp -> id );
			$result[$i]['cfgkingdomproject']['info'] = $result[$i]['cfgkingdomproject']['obj'] -> get_info();
			$i++;
		}

		return $result;

	}

	/**
	* Aggiorna i dobloni
	* @param delta delta
	* @param reason causale
	* @return none
	*/

	public function modify_doubloons( $delta, $reason = 'notspecified' )
	{

		Structure_Event_Model::newadd(
				$this -> id,
				'__events.doubloonupdated' .
				';' . $delta .
				';__charactions.reason_' . $reason );

		$doubloons = Item_Model::factory( null, 'doubloon' );

		if ( $delta > 0 )
		{
			$doubloons -> additem( "structure", $this -> id, $delta );
			$this -> doubloons += $delta;
		}
		else
		{
			$doubloons -> removeitem( "structure", $this -> id, -$delta );
			$this -> doubloons += $delta;
		}

	}

	/**
	* aggiorna i Faith points
	* @param delta delta
	* @param reason causale
	* @return none
	*/

	public function modify_fp( $delta, $reason = '' )
	{

		$fp = Structure_Model::get_stat_d( $this -> id, 'faithpoints' );

		if ( ! $fp -> loaded )
			$currentfp = 0 ;
		else
			$currentfp = $fp -> value;

		kohana::log('debug', '-> pre: current FP: ' . $currentfp );

		if ( $currentfp + $delta < 0 )
			$currentfp = 0;
		else
			$currentfp += $delta;

		kohana::log('debug', '-> post: current FP: ' . $currentfp );

		$this -> modify_stat(
			'faithpoints',
			$currentfp,
			null,
			null,
			null,
			null,
			true );

	}

	/**
	* aggiorna le stat di una struttura
	* @param  name: nome della statistica
	* @param delta: delta value
	* @param searchparam1: parametro di ricerca 1
	* @param searchparam2: parametro di ricerca 2
	* @param spare1: valore campo spare1
	* @param spare1: valore campo spare2
	* @param criteria: eventuale array di criteri aggiuntivi
  * @param replace: se true rimpiazza il valore (value) e non lo somma/sottrae
	* @return none
	*/

	public function modify_stat(
		$name,
		$delta,
		$searchparam1 = null,
		$searchparam2 = null,
		$spare1 = null,
		$spare2 = null,
		$replace = false )
	{

		kohana::log('debug', 'Modifiying Structrure stat. name: ' . $name .
			' delta: ' . $delta  .
			' searchparam1: ' . $searchparam1 .
			' searchparam2: ' . $searchparam2 .
			' spare1: ' . $spare1 .
			' spare2: ' . $spare2 .
			' replace: ' . $replace );

		$stat = Structure_Model::get_stat_d( $this -> id, $name, $searchparam1, $searchparam2 );

		kohana::log('debug',
			' Pre:  statname: ' . $name .
			' value: ' . $stat -> value .
			' spare1: ' . $stat -> spare1 .
			' spare2: ' . $stat -> spare2
		);

		//var_dump( $stat ); exit;

		if ( ! $stat -> loaded )
		{
			$stat = new Structure_Stat_Model();
			$stat -> structure_id = $this->id;
			$stat -> name = $name ;
			$stat -> searchparam1 = $searchparam1 ;
			$stat -> searchparam2 = $searchparam2 ;
			$stat -> spare1 = $spare1;
			$stat -> spare2 = $spare2;
			$stat -> value = 0;
		}

		if ( $replace )
		{
			$stat -> value = $delta;
			$stat -> spare1 = $spare1;
			$stat -> spare2 = $spare2;
		}
		else
		{
			$stat -> value += $delta;
			$stat -> spare1 += $spare1;
			$stat -> spare2 += $spare2;
		}

		$stat -> searchparam1 = $searchparam1 ;
		$stat -> searchparam2 = $searchparam2 ;
		$stat -> spare1 = $spare1;
		$stat -> spare2 = $spare2;
		$stat -> save();

		kohana::log('debug', ' Post:  statname: ' . $name . ' value: ' . $stat -> value .
			' spare1: ' . $stat -> spare1 .
			' spare2: ' . $stat -> spare2
		);

	}

	/**
	* aggiorna le stat di una struttura
	* @patam int $structure_id ID Struttura
	* @param  name: nome della statistica
	* @param delta: delta value
	* @param searchparam1: parametro di ricerca 1
	* @param searchparam2: parametro di ricerca 2
	* @param spare1: valore campo spare1
	* @param spare1: valore campo spare2
	* @param criteria: eventuale array di criteri aggiuntivi
  * @param replace: se true rimpiazza il valore (value) e non lo somma/sottrae
	* @return none
	*/

	public function modify_stat_d(
		$structure_id,
		$name,
		$delta,
		$searchparam1 = null,
		$searchparam2 = null,
		$spare1 = null,
		$spare2 = null,
		$replace = false )
	{

		kohana::log('debug', 'Modifiying Structrure stat. name: ' . $name .
			' delta: ' . $delta  .
			' searchparam1: ' . $searchparam1 .
			' searchparam2: ' . $searchparam2 .
			' spare1: ' . $spare1 .
			' spare2: ' . $spare2 .
			' replace: ' . $replace );

		$stat = Structure_Model::get_stat_d( $structure_id, $name, $searchparam1, $searchparam2 );

		kohana::log('debug', 'Pre:  statname: ' . $name . ' value: ' . $stat -> value .
			' spare1: ' . $stat -> spare1 .
			' spare2: ' . $stat -> spare2
		);

		//var_dump( $stat ); exit;

		if ( ! $stat -> loaded )
		{
			$stat = new Structure_Stat_Model();
			$stat -> structure_id = $structure_id;
			$stat -> name = $name ;
			$stat -> searchparam1 = $searchparam1 ;
			$stat -> searchparam2 = $searchparam2 ;
			$stat -> spare1 = $spare1;
			$stat -> spare2 = $spare2;
			$stat -> value = 0;
		}

		if ( $replace )
		{
			$stat -> value = $delta;
			$stat -> spare1 = $spare1;
			$stat -> spare2 = $spare2;
		}
		else
		{
			$stat -> value += $delta;
			$stat -> spare1 += $spare1;
			$stat -> spare2 += $spare2;
		}

		$stat -> searchparam1 = $searchparam1 ;
		$stat -> searchparam2 = $searchparam2 ;
		$stat -> save();

		kohana::log('debug', ' Post:  statname: ' . $name . ' value: ' . $stat -> value .
			' spare1: ' . $stat -> spare1 .
			' spare2: ' . $stat -> spare2
		);

	}

	/**
	* carica le stat di una struttura
	* @param  name: nome della statistica
	* @param  param1 param 1 di ricerca
	* @param  param2 param 2 di ricerca
	* @return oggetto stat
	*/

	public function get_stat_d( $structure_id, $name, $searchparam1 = null, $searchparam2 = null )
	{

		kohana::log( 'debug', "Searching for structure stat: $name, searchparam1: $searchparam1, searchparam2: $searchparam2" );

		if ( is_null( $searchparam1 ) and is_null( $searchparam2)  )
			$stat = ORM::factory( 'structure_stat' )
			->where( array(
				'structure_id' => $structure_id,
				'name' => $name )) -> find();

		if ( !is_null( $searchparam1 ) and is_null( $searchparam2)  )
			$stat = ORM::factory( 'structure_stat' )
			->where( array(
				'structure_id' => $structure_id,
				'searchparam1' => $searchparam1,
				'name' => $name )) -> find();

		if ( !is_null( $searchparam1 ) and !is_null( $searchparam2)  )
			$stat = ORM::factory( 'structure_stat' )
			->where( array(
				'structure_id' => $structure_id,
				'searchparam1' => $searchparam1,
				'searchparam2' => $searchparam2,
				'name' => $name )) -> find();

		return $stat;

	}

	/**
	* carica le stats (se ce ne sono pi� d'una) di una struttura
	* @param  name: nome della statistica
	* @return collection di oggetti stats o null
	*/

	public function get_stats( $name )
	{

		$stats = ORM::factory( 'structure_stat' )
		-> where( array(
			'structure_id' => $this -> id,
			'name' => $name )) ->  orderby ( 'value' , 'DESC') -> find_all() ;

		if ( $stats -> count () > 0 )
			return $stats;
		else
			return null;

	}

	/**
	* carica una opzione
	* @param  name: nome dell' opzione
	* @param  value: valore dell' opzione
	* @return value o false
	*/

	public function get_option( $name )
	{

		$option = ORM::factory( 'structure_option' )
		->where( array(
			'structure_id' => $this->id,
			'name' => $name )) -> find();

		if ( $option -> loaded )
			return $option -> value ;
		else
			return false;

	}

	/**
	* setta un opzione della struttura
	* @param  name: nome dell' opzione
	* @param  value: valore dell' opzione
	* @return none ;
	*/

	public function set_option( $name, $value )
	{
		$found = false;

		foreach ( $this -> structure_option as $option )
			if ( $option -> name == $name )
			{
				$found = true;
				$option -> value = $value ;
				$option -> save();
			}

		if ( $found == false )
		{
			$option = new Structure_Option_Model();
			$option -> name = $name;
			$option -> structure_id = $this -> id;
			$option -> value = $value;
			$option -> save();
		}

		return;
	}

	/**
	* Setta il menu orizzontale a seconda della struttura.
	* @param string $selected voce da selezionare
	* @return array $lnkmenu codice html;
	*/

	public function get_horizontalmenu( $selected )
	{

		$lnkmenu = '';

		///////////////////////////////////////////////
		// mercato
		///////////////////////////////////////////////

		if ( $this -> structure_type -> supertype == 'market' )
			$lnkmenu = array(
				'/market/buy/' . $this->id =>
						array( 'name' => 	kohana::lang('structures_actions.market_buy'),	'htmlparams' => array( 'class' =>
										( $selected == 'buy' ) ? 'selected' : '' )),
				'/market/sell/' . $this->id =>
						array( 'name' => 	kohana::lang('structures_actions.global_sell'),	'htmlparams' => array( 'class' =>
						( $selected == 'sell' ) ? 'selected' : '' )),
			);

		///////////////////////////////////////////////
		// terreno
		///////////////////////////////////////////////

		if ( $this -> structure_type -> supertype == 'terrain' )
		{
			$lnkmenu = array(
				'/terrain/manage/' . $this->id =>
					array( 'name' => 	kohana::lang('global.manage'),	'htmlparams' => array( 'class' =>
									( $selected == 'manage' ) ? 'selected' : '' )),
				'/terrain/seed/' . $this -> id =>
						array( 'name' => 	kohana::lang('structures_terrain.seed'),	'htmlparams' => array( 'class' =>
						( $selected == 'seed' ) ? 'selected' : '' )),
				'/terrain/harvest/' . $this -> id =>
						array( 'name' => 	kohana::lang('structures_terrain.harvest'),	'htmlparams' => array( 'class' =>
						( $selected == 'harvest' ) ? 'selected' : '' )),
				'/structure/upgradelevel/' . $this->id =>
						array( 'name' => 	kohana::lang('structures.upgradelevel'),	'htmlparams' => array( 'class' =>
						( $selected == 'upgradelevel' ) ? 'selected' : '' )),
				'/structure/inventory/' . $this->id =>
						array( 'name' => 	kohana::lang('global.inventory'),	'htmlparams' => array( 'class' =>
						( $selected == 'inventory' ) ? 'selected' : '' )),

			);

			if ( $this -> structure_type -> level > 1 )
				$lnkmenu[ '/structure/manageaccess/' . $this -> id ] =
						array( 'name' => 	kohana::lang('structures.manageaccess'),	'htmlparams' => array( 'class' =>
						( $selected == 'manageaccess' ) ? 'selected' : '' ));
		}


		///////////////////////////////////////////////
		// breeding
		///////////////////////////////////////////////

		if ( $this -> structure_type -> parenttype  == 'breeding' )
			$lnkmenu = array(
				'/structure/manage/' . $this -> id =>
					array( 'name' => 	kohana::lang('global.manage'),	'htmlparams' => array( 'class' =>
					( $selected == 'manage' ) ? 'selected' : '' )),
				'/breeding/feed/' . $this->id =>
					array( 'name' => 	kohana::lang('structures.breeding_feed'),	'htmlparams' => array( 'class' =>
					( $selected == 'feed' ) ? 'selected' : '' )),
				'/breeding/gather/' . $this->id =>
					array( 'name' => 	kohana::lang('structures.breeding_gather'),	'htmlparams' => array( 'class' =>
					( $selected == 'gather' ) ? 'gather' : '' )),
				'/breeding/butcher/' . $this->id =>
					array( 'name' => 	kohana::lang('structures.breeding_butcher'),	'htmlparams' => array( 'class' =>
					( $selected == 'inventory' ) ? 'selected' : '' )),
				'/structure/manageaccess/' . $this -> id =>
						array( 'name' => 	kohana::lang('structures.manageaccess'),	'htmlparams' => array( 'class' =>
						( $selected == 'manageaccess' ) ? 'selected' : '' )),
				'/structure/inventory/' . $this ->id =>
					array( 'name' => 	kohana::lang('global.inventory'),	'htmlparams' => array( 'class' =>
					( $selected == 'inventory' ) ? 'selected' : '' )),

			);

			// Check: l'allevamento � di bachi da seta
				if
				(
					$this -> structure_type -> parenttype  == 'breeding' AND
					$this -> structure_type -> type  == 'breeding_silkworm'
				)
				{
					// Rimuovo il link gather
					unset ( $lnkmenu['/breeding/gather/' . $this->id] );
				}

		///////////////////////////////////////////////
		// native village
		///////////////////////////////////////////////

		if ( $this -> structure_type -> supertype == 'nativevillage' )
		{
			$lnkmenu = array(
			'/structure/rest/' . $this -> id =>
				array( 'name' =>
				 kohana::lang('global.rest'),    'htmlparams' => array( 'class' =>
 				( $selected == 'rest' ) ? 'selected' : '' )) );
		}

		///////////////////////////////////////////////
		// breedingregion
		///////////////////////////////////////////////

		if ( $this -> structure_type -> supertype == 'breeding_region' )
		{
			$lnkmenu = array(
				'/structure/buyanimals/' . $this -> id =>
					array( 'name' => kohana::lang('structures_actions.breeding_buyanimals'),
					'htmlparams' => array( 'class' =>
						( $selected == 'manage' ) ? 'selected' : '' )));
		}

		return $lnkmenu;

	}
	/*
	* Ritorna lo stato della risorsa legata alla struttura
	* @param % di risorse disponibili
	* @return array $status
	*/

	public function get_descriptiveresourcestatus ( $percentage )
	{
		$status = array( 'desc', 'color' );

		if ( $percentage >= 95 )
		{
			$status['desc'] = 'structures.fullresources';
			$status['color'] =  '#080';
		}
		if ( $percentage >= 70 and $percentage < 95 )
		{
			$status['desc'] = 'structures.almostfullresources';
			$status['color'] =  '#080';
		}
		if ( $percentage >= 40 and $percentage < 70 )
		{
			$status['desc'] = 'structures.halvedresources';
			$status['color'] =  '#eb9100';
		}
		if ( $percentage >= 15 and $percentage < 40 )
		{
			$status['desc'] = 'structures.almostdepletedresources';
			$status['color'] =  '#eb9100';
		}
		if ( $percentage < 15 )
		{
			$status['desc'] = 'structures.depletedresources';
			$status['color'] =  '#f00';
		}

		return $status;
	}

	/**
	* trova le strutture figlie
	* @param nessuno
	* @return collection di oggetti structure
	*/

	function get_childstructures()
	{
		return ORM::factory('structure')
			-> where( 'parent_structure_id', $this -> id ) -> find_all();
	}


	/**
	* Returns Premium Bonuses linked to structures.
	* @param  string $name Bonus Name
	* @return obj Character_Premiumbonus or null
	*/

	function get_premiumbonuses( )
	{
		$data = null;
		$res = Database::instance() -> query(
			"SELECT c.name, cp.*
			FROM character_premiumbonuses cp, cfgpremiumbonuses c
			WHERE cp.cfgpremiumbonus_id = c.id
			AND   structure_id = {$this -> id}
			AND   endtime > unix_timestamp()");

		if ( count($res) > 0 )
			foreach ($res as $bonus)
				$data[$bonus -> name] = $bonus;
		//var_dump($data);exit;
		return $data;

	}

	function get_premiumbonus ($name)
	{

		$bonuses = $this -> get_premiumbonuses();
		//var_dump($bonuses);exit;
		if (!is_null($bonuses))
			return $bonuses[$name];
		else
			return null;
	}

	/**
	* Returns assignable grants for
	* a certain structure type.
	* @param none
	* @return none
	*/

	function get_assignable_grants()
	{

		$grants = array(
			'shop' => array(),
			'house' => array(),
			'terrain' => array(),
			'breeding' => array(),
			'barracks' => array(
				'captain_assistant' => kohana::lang('structures.grant_captain_assistant'),
			),
			'watchtower' => array(
				'guard_assistant' => kohana::lang('structures.grant_guard_assistant')
			)
		);

		return $grants[ $this -> structure_type -> parenttype ] ;
	}

	/**
	* Controlla se la struttura contiene la funzione
	* @param function nome funzione da controllare
	* @return false o true
	*/

	function check_allowedfunction( $function )
	{
		$rc = false;

		// funzioni che sono disponibili per tutti i tipi di struttura
		$catchall = array( 'inventory',
			'drop', 'take', 'events',
			'manage', 'sell', 'change_description', 'change_infomessage', 'change_image' );

		// funzioni disponibili per struttura e livello

		$aclist = array(
			'house' => array(
				'1' => array( 'rest' ),
				'2' => array( 'manageaccess', 'assigngrant', 'revokegrant', )
			),
			'shop' => array(
				'1' => array( 'craft', 'listcraftableitems', 'upgradelevel', 'upgradeinventory' ),
				'2' => array( 'manageaccess', 'assigngrant', 'revokegrant' ),
			),
			'terrain' => array(
				'1' => array( 'seed', 'harvest', 'upgradelevel' ),
				'2' => array( 'manageaccess', 'assigngrant', 'revokegrant' ),
			),
			'breeding' => array(
				'1' => array( 'feed', 'gather', 'butcher', 'manageaccess', 'assigngrant', 'revokegrant' ),
			),
			'royalpalace' => array(
				'1' => array( 'rest', 'declarehostileaction', 'viewlaws', 'appoint', 'addlaw', 'editlaw', 'deletelaw', 'assign_roles', 'list_roles',
					'taxes', 'announcements', 'buildproject', 'basicresourcereport',	'runningprojects', 'completedprojects', 'resourcereport',
					'revoke_role', 'propertyreport', 'raid', 'conquer_r', 'conquer_ir', 'assign_region', 'assign_rolerp', 'add_announcement',
					'diplomacy', 'modifydiplomacystatus', 'viewdiplomacystatus', 'giveaccesspermit', 'list_roletitles', 'customizenobletitles',
					'declarewar', 'revokerolerp'),
			),
			'castle' => array(
				'1' => array( 'rest', 'propertyreport', 'basicresourcereport',
					'valueaddedtax', 'add_announcement', 'list_subordinates', 'assignrole',
					'buildproject', 'runningprojects', 'completedprojects', 'assign_rolerp', 'propertyprice', 'list_roletitles', 'revoke_role')
			),
			'court' => array(
				'1' => array( 'rest', 'opencrimeprocedure', 'listcrimeprocedure', 'viewcrimeprocedure',
					'editcrimeprocedure', 'cancelcrimeprocedure', 'writearrestwarrant', 'imprison', 'assign_rolerp', 'list_roletitles')
			),
			'barracks' => array(
				'1' => array(
					'rest',
					'manageprisoners',
					'freeprisoner',
					'managerestrained',
					'assign_rolerp',
					'list_roletitles',
					'restrain',
					'assign_rolerp',
					'list_roletitles',
					'upgradelevel',

					),
				'2' => array( 'armory', 'viewlends', 'manageaccess', 'assigngrant', 'revokegrant', 'lend')
			),
			'watchtower' => array(
				'1' => array( 'rest', 'manageaccess', 'assigngrant', 'revokegrant', 'watch')
			),
			'trainingground' => array(
				'1' => array( 'rest', 'sethourlycost', 'assign_rolerp', 'list_roletitles', 'upgradelevel'),
				'2' => array( 'managecourses'),
			),
			'academy' => array(
				'1' => array( 'rest', 'sethourlycost', 'assign_rolerp', 'list_roletitles', 'upgradelevel'),
				'2' => array( 'managecourses'),
			),
			'religion_4' => array(
				'1' => array( 'rest', 'listcraftableitems', 'craft', 'configureitemprices', 'cure', 'celebratemarriage', 'assign_rolerp', 'list_roletitles' ),
			),
			'religion_3' => array(
				'1' => array( 'rest', 'transferpoints', 'managehierarchy', 'buildproject', 'runningprojects', 'completedprojects', 'assign_rolerp',
				'list_roletitles', 'celebratemarriage'),
			),
			'religion_2' => array(
				'1' => array( 'rest', 'listcraftableitems', 'craft', 'configureitemprices', 'transferpoints', 'managehierarchy', 'buildproject', 'runningprojects', 'completedprojects' , 'assign_rolerp', 'list_roletitles', 'celebratemarriage'),
			),
			'religion_1' => array(
				'1' => array( 'rest', 'transferpoints', 'managehierarchy', 'buildproject', 'runningprojects', 'completedprojects' , 'assign_rolerp',
					'list_roletitles', 'celebratemarriage', 'managedogmas', 'removedogma','resourcereport'),
			),
			'buildingsite' => array(
				'1' => array( 'sethourlywage', 'changename'),
			),
			'tavern' =>
			array('1' => array( 'rest'), ),
		);


		if ( in_array( $function, $catchall ) )
			return true;

		// scorriamo la matrice e confrontiamo il livello della struttura.
		// se la funzione � presente nelle funzioni disponibili dei livelli
		// inferiori o uguali => la funzione esiste

		foreach ( $aclist as $strtype => $level ) {
			//kohana::log('debug', '-> Structure Type: ' . $strtype . ', parent type: ' . $this -> structure_type -> parenttype );
			if ( $this -> structure_type -> parenttype == $strtype )
				foreach ( $level as $l => $allowedfunctions )
				{
					//var_dump( $l );
					if ( $this -> structure_type -> level >= $l )
						if ( in_array( $function, $allowedfunctions ))
							$rc = true;
				}
		}

		// caso particolare, se la struttura � barracks, la funzione �
		// manageaccess e la struttura non � powered up, ritorna false

		if (
				(
				$function == 'manageaccess'
				)
			and
				$this -> structure_type -> type == 'barracks_2'
			and
				is_null( $this -> get_premiumbonus('armory') )
			)
				$rc = false;


		return $rc;

	}

	/**
	* Funzione che determina se un profilo pu� accedere ad una data funzione
	* @param string $function funzione
	* @param array $grants array di grants
	* @return boolean esito;
	*/

	function get_accessallowed( $function, $grants )
	{

		kohana::log('debug', '----- ACCESS ALLOWED -----');
		kohana::log('debug', "Function: {$function}");
		kohana::log('debug', kohana::debug($grants));

		if ( in_array( 'none', $grants ) )
			return false;

		if ( in_array( 'owner', $grants ) )
			return true;

		$aclist = array(
			'worker' =>
				array(
			'manage', 'seed', 'harvest', 'listcraftableitems', 'craft', 'upgradeinventory', 'feed','gather', 'butcher'),
			'captain_assistant' =>
				array('armory', 'viewlends', 'lend'	),
			'guard_assistant' =>
				array('rest', 'watch'),
			'chancellor' =>
				array('diplomacy', 'modifydiplomacystatus', 'viewdiplomacystatus', 'giveaccesspermit' ),
			'customsofficer' =>
				array('valueaddedtax', 'propertyprice'),
			'wife' =>
				array('manage','rest'),
			'husband' =>
				array('manage','rest'),
		);

		foreach ( $grants as $key => $value )
			if ( in_array( $function, $aclist[ $value ] ) )
				return true;

		return false;

	}

	/**
	* Computes number of role given by a certain structure.
	* @param   string $role
	* @return  int number of roles
	*/

	function count_rproles_assigned_by_structure( $role )
	{

		$c = ORM::factory('character_role') -> where (
			array(
				'tag' => $role,
				'current' => true,
				'structure_id' => $this -> id )) -> count_all();

		return $c;
	}

	/**
	* Computes how many shild structures exists of a certain type
	* @param string structuretype
	* @return int number of structures
	*/

	function get_childstructures_tot( $structuretype )
	{
		$db = Database::instance();

		$res = $db -> query( "
		select count(s.id) q, st.id, st.parenttype from structures s, structure_types st
		where st.id = s.structure_type_id
		and st.parenttype = '$structuretype'
		and s.parent_structure_id = " . $this -> id ) -> as_array();

		return $res[0] -> q ;
	}

	/**
	* Cambia la descrizione della struttura
	* @param none
	* @return none
	*/

	public function change_description()
	{

		$structure = StructureFactory_Model::create( null, $this -> input -> post( 'structure_id' ) );
		if ( ! $structure->allowedaccess( $character, $structure -> structure_type -> parenttype ,
			$message, 'private', 'change_description' ) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect( 'region/view/' );
		}

		$structure -> description = substr($this -> input -> post ('description' ), 0, 1023);
		$structure -> save();

		Session::set_flash('user_message', "<div class=\"info_msg\">". kohana::lang('structures.configuration_ok') . "</div>");

		url::redirect('/structure/manage');


	}

	/**
	* Permette di cambiare il messaggio informativo
	* @param structure_id id struttura
	* @return none
	*/

	function change_infomessage ()
	{

		$structure = StructureFactory_Model::create( null, $this -> input -> post( 'structure_id' ) );

		if ( ! $structure -> allowedaccess( $character, $structure -> structure_type -> parenttype , $message, 'private', 'change_infomessage' ) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect( 'region/view/' );
		}

		$structure -> message = substr($this -> input -> post ('message' ), 0, 1023);
		$structure -> save();

		Session::set_flash('user_message', "<div class=\"info_msg\">". kohana::lang('structures.configuration_ok') . "</div>");

		url::redirect('/structure/manage');

	}

	/**
	* Permette di cambiare l' immagine del negozio
    * @param structure_id id struttura
	* @return none
	*/

	function change_image( )
	{

		$structure = StructureFactory_Model::create( null, $this -> input -> post( 'structure_id' ) );

		if ( ! $structure -> allowedaccess( $character, $structure -> structure_type -> parenttype , $message, 'private', 'change_image' ) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect( 'region/view/' );
		}

		$structure -> image = $this -> input -> post ('image' );
		$structure -> save();

		Session::set_flash('user_message', "<div class=\"info_msg\">". kohana::lang('structures.configuration_ok') . "</div>");

		url::redirect('/structure/manage');

	}

	/**
	* Stampa la condizione di una struttura
	* @param none
	* @return none
	*/

	function display_condition(  )
	{

		if ( $this -> state >= 60 )
			$color = '#228b49';

		if ( $this -> state < 60 and $this -> state >= 30 )
			$color = '#f2ec41';

		if ( $this -> state < 30  )
			$color = '#dd0000';

		$link = "<span style='color:"
			. $color . ";font-weight:bold;'>" .  $this -> state . "%</span>";

		return $link;
	}

	/**
	* Torna la percentuale di energia recuperata per ora.
	* per ora
	* @param none
	* @return float $restfactor % Recuperata per ora.
	*/

	function getRestfactor() {
		return $this->restfactor * 2;
	}
	function setMaxlevel($maxlevel) { $this->maxlevel = $maxlevel; }
	function getMaxlevel() { return $this->maxlevel; }
	function setCurrentlevel($currentlevel) { $this->currentlevel = $currentlevel; }
	function getCurrentlevel() { return $this->currentlevel; }
	function setBaseprice($baseprice) { $this -> baseprice = $baseprice; }
	function getBaseprice() { return $this->baseprice; }
	function setPrice($price) { $this->price = $price; }

	/**
	* Return price of structure (with tax)
	* @param obj $char Character_Model Personaggio
	* @param obj $regio Region_Model Regione dove la struttura � comprata
	* @return int $price Prezzo di acquisto
	*/

	public function getPrice( $char, $region )
	{
		$propertypricemodifier = $region -> get_appliable_tax( $region, 'valueaddedtax', $char );
		return round( $this -> getBaseprice() * ( 100 + $propertypricemodifier ) / 100, 0) ;
	}

	function getTag()
	{
		if ( $this -> structure_type -> subtype == 'church')
			return 'structures.' . $this -> getSuperType() . '_' . $this -> structure_type -> church -> name;
		else
			return 'structures.' . $this -> getSuperType() . '_' . $this -> getCurrentLevel();
	}

	function getName()
	{
		if ( $this -> structure_type -> subtype == 'church')
			return kohana::lang('structures.' . $this -> getSuperType() . '_' . $this -> structure_type -> church -> name);
		else
			return kohana::lang('structures.' . $this -> getSuperType() . '_' . $this -> getCurrentLevel());
	}

	function getDescription()
	{
		if( empty($this -> description) )
			return kohana::lang('structures.' . $this -> getSupertype() . '_desc' );
		else
			return Utility_Model::bbcode($this -> description);
	}

	function setStorage($storage) { $this->storage = $storage; }
	function getStorage() {

		if ( is_null( $this -> customstorage ) )
			return $this -> storage;
		else
			return $this -> getCustomstorage();
	}

	function setRestfactor($restfactor) { $this->restfactor = $restfactor; }
	function setIsbuyable($isbuyable) { $this->isbuyable = $isbuyable; }
	function getIsbuyable() { return $this->isbuyable; }
	function setSellingprice($sellingprice) { $this->sellingprice = $sellingprice; }

	/**
	* Calcola il prezzo di vendita della casa basato
	* @param obj $char Character_Model Personaggio
	* @param obj $regio Region_Model Regione dove la struttura � comprata
	* @return int $price Prezzo di vendita
	*/

	function getSellingprice( $char, $region )
	{
		return 0.8 * $this -> getBaseprice();
	}

	function setIssellable($issellable) { $this->issellable = $issellable; }
	function getIssellable() { return $this->issellable; }
	function setHoursfornextlevel($hoursfornextlevel) { $this->hoursfornextlevel = $hoursfornextlevel; }
	function getHoursfornextlevel() { return $this->hoursfornextlevel; }
	function setNeededmaterialfornextlevel($neededmaterialfornextlevel) { $this->neededmaterialfornextlevel = $neededmaterialfornextlevel; }
	function getNeededmaterialfornextlevel() { return $this->neededmaterialfornextlevel; }
	function setSubmenu($submenu) { $this->submenu = $submenu; }
	function getSubmenu() {
		return 'submenu_' . $this -> getParenttype(). '_' . $this -> getCurrentlevel();
	}

	function setSupertype($supertype) { $this->supertype = $supertype; }
	function getSupertype() { return $this->supertype; }
	function setParenttype($parenttype) { $this->parenttype = $parenttype; }
	function getParenttype() { return $this->parenttype; }
	function setWikilink($wikilink) { $this->wikilink = $wikilink; }
	function getWikilink() { return $this->wikilink; }
	function setIsupgradable($isupgradable) { $this->isupgradable = $isupgradable; }
	function getIsupgradable() { return $this->isupgradable; }
	function setCustomstorage($customstorage) { $this->customstorage = $customstorage; }
	function getCustomstorage() { return $this->customstorage; }

	/**
	* Ritorna le info per l' upgrade di livello
	* @param none
	* @return array $info
	*/

	public function getUpgradeinfo()
	{
		$info = array(
			'currentlevel' => 0,
			'maxlevel' => 0,
			'hours' => 0,
			'neededmaterialfornextlevel' => array(),
			'supertype' => ''
		);

		$info['currentlevel'] = $this -> getCurrentlevel();
		$info['maxlevel'] = $this -> getMaxLevel();
		$info['hours'] = $this -> getHoursfornextlevel();
		$info['neededmaterialfornextlevel'] = $this -> getNeededmaterialfornextlevel();
		$info['supertype'] = $this -> getSuperType();

		return $info;

	}


	/*
	* Ritorna le ore lavorate per l' upgrade
	* @param none
	* @return int ore lavorate
	*/

	public function getUpgradeworkedhours()
	{

		$stat = Structure_Model::get_stat_d(  $this -> id, 'upgradeworkedhours' );
		if ($stat -> loaded )
				return $stat -> value;
		else
				return 0;

	}

	/**
	* Ritorna informazioni sui corsi installabili nella struttura
	* @param none
	* @return array corsi che si possono installare
	*/

	function getAvailablecourses()
	{

		$availablecourses = $this -> basecourses;

		// Load installed courses

		$installedcourses = Structure_Model::get_stats('course');
		if (!is_null($installedcourses))
			foreach ($installedcourses as $installedcourse)
				$availablecourses[] = $installedcourse -> searchparam1;

		return array_unique($availablecourses);

	}

	/**
	* Ritorna informazioni sui corsi installabili nella struttura
	* @param none
	* @return array corsi che si possono installare
	*/

	function getAllcourses()
	{
		return array_merge( $this -> basecourses, $this -> installablecourses );
	}

	/**
	* Installa un corso
	* @param str $course Corso da installare
	* @return none
	*/

	function add_course( $course )
	{

		Structure_Model::modify_stat_d
		(
			$this -> id,
			'course',
			0,
			$course,
			null,
			null,
			null,
			true
		);

		Character_Event_Model::addrecord
		(
				null,
				'announcement',
				'__events.courseinstallation' .
				';' . $this -> character -> name .
				';__structures.course_' . $course . '_name' .
				';__' . 'structures.' . $this -> structure_type -> type .
				';__' . $this -> region -> name,
				'evidence'	);
	}

}
