<?php defined('SYSPATH') OR die('No direct access allowed.');

class Character_Model extends ORM
{
	// Costanti

	const LIMIT_GLUT   = 50;
	const LIMIT_ENERGY = 50;
	const LIMIT_HEALTH = 100;	
	const CART_1_STORAGE = 3000000;
	const CART_1_WEIGHT = 300000;
	const CART_2_STORAGE = 300000;
	const CART_2_WEIGHT = 100000;	
	const CART_3_STORAGE = 10000000;
	const CART_3_WEIGHT = 600000;	
	const LIMIT_ATTRIBUTES = 23;

	protected $has_many = array(
		'character_actions', 
		'item', 
		'structure', 
		'character_roles', 
		'character_sentences', 
		'character_stats', 
		'groups', 
		'character_titles', 
		'character_permanentevents');
		
	protected $belongs_to = array( 'kingdom', 'user', 'region', 'church' );		

	static function get_attributelimit()
	{
		return self::LIMIT_ATTRIBUTES;
	}
	
	/**
	* Determina se un char è online
	* @param int $char_id ID Character
	* @return bool 
	*/
	
	function is_online($char_id)
	{
		$lastactiontime = Character_Model::get_lastactiontime_d ( $char_id ); 	
		return ( time() - $lastactiontime > (Kohana::config('medeur.maxidletime')) ) ?  false : true ;
	}
	
	/**
	* Calcola se un char è newborn o meno
	* @param Character_Model $char Personaggio
	* @return boolean
	*/
	
	static function is_newbie( $char )
	{
		if ($char -> get_age() <= kohana::config('medeur.newbiedays', 30) )
			return true;
		else
			return false;
	}
	
	/**
	* Wrapper per save. Cacha l' oggetto
	* @param none
	* @return none
	*/
	
	function save()
	{		
		
		kohana::log( 'debug', '-> Saving char data.' );
		//var_dump($this);exit;
		parent::save();
		
		Character_Model::invalidate_char_cache( $this -> id );
		
		return true;
		
	}
	
	/** 
	* Modifica honor points del char
	* @param delta delta da aggiungere o sottrarre	
	* @param reason causale
	* @return none
	*/
	
	public function modify_honorpoints( $delta, $reason ) 
	{
	
		if ( $reason == '' )
			$reason = 'notspecified';
		Character_Model::modify_stat_d( $this -> id, 'honorpoints', $delta, null, null, false );
		Character_Event_Model::addrecord(
			$this -> id,
			'normal',
			'__events.honorpointsupdated;' . 
			$delta . ';__' . 
			'charactions.reason_' . $reason );		
		kohana::log('debug', '-> CID: ' . $this -> id . ', Modifying honor: ' . $delta . ', reason: ' . $reason );		
	}
	
	/** 
	* Modifica i dobloni del char
	* @param int $delta delta da aggiungere o sottrarre	
	* @param string $category Categoria 
	* @param string $reason Causale
	* @return none
	*/
	
	public function modify_doubloons( $delta, $category, $reason = 'Not Specified' ) 
	{
		
		Character_Event_Model::addrecord(
			$this -> id,
			'normal',
			'__events.doubloonupdated;' . 
			$delta . ';__' . 
			'charactions.reason_' . $category );
		
		$doubloons = Item_Model::factory( null, 'doubloon' );						
		
		if ( $delta > 0 )
		{
			$doubloons -> additem( "character", $this -> id, $delta );
			$this -> doubloons += $delta;
		}
		else
		{
			$doubloons -> removeitem( "character", $this -> id, -$delta );		
			$this -> doubloons += $delta;
		}
		
		Trace_Sink_Model::add( 'doubloon', $this -> id, $delta, $category) ; 
	
	}
	
	/** 
	* Modifica la posizione del char
	* @param id location (region_id)
	* @return none
	*/
	
	public function modify_location ( $location_id )
	{
		
		$this -> position_id = $location_id ;			
		My_Cache_Model::delete( '-charinfo_' . $this -> id . '_currentposition');		
	}
	
	/** 
	* Modifica i denari di un char
	* @param int $delta delta
	* @param str $reason causa modifica
	* @return none
	*/
	
	public function modify_coins( $delta, $reason = null )	
	{
		
		kohana::log( 'debug', '------ MODIFY COINS ------');
		
		$db = Database::instance();				
		
		kohana::log( 'debug', '-> Modifying coins for Char: ' . $this -> name . ', reason: ' . $reason . ', delta: ' . $delta );
		
		Trace_Sink_Model::add( 'silvercoin', $this -> id, $delta, $reason );
			
		$delta *= 100;
		
		$silvercoins = Character_Model::get_item_quantity_d( $this -> id, 'silvercoin' );
		$coppercoins = Character_Model::get_item_quantity_d( $this -> id, 'coppercoin' );		
		
		kohana::log( 'debug', '->  Char has NOW silvercoins: ' . $silvercoins . ' coppercoins: ' . $coppercoins );
		
		$this -> convertcoppercoins();
		
		$silvercoins = Character_Model::get_item_quantity_d( $this -> id, 'silvercoin' );
		$coppercoins = Character_Model::get_item_quantity_d( $this -> id, 'coppercoin' );
		
		$deltasilvercoins = intval($delta/100);
		$deltacoppercoins = $delta - ($deltasilvercoins * 100);
				
		$silvercoin_item = Item_Model::factory( null, 'silvercoin' );
		$coppercoin_item = Item_Model::factory( null, 'coppercoin' );			

		if ( $delta > 0 )
		{
			$silvercoin_item -> additem( "character", $this -> id, abs($deltasilvercoins) );		
			$coppercoin_item -> additem( "character", $this -> id, abs($deltacoppercoins) );							
		}
		else
		{			
			kohana::log( 'debug', 'Delta is negative.' );
			if ( $coppercoins < abs($deltacoppercoins) )
			{
				$silvercoin_item -> removeitem( "character", $this -> id, abs($deltasilvercoins) + 1);		
				$coppercoin_item -> additem( "character", $this -> id, 100 - abs( $deltacoppercoins ) );
			}
			else
			{				
				$silvercoin_item -> removeitem( "character", $this -> id, abs($deltasilvercoins) );		
				$coppercoin_item -> removeitem( "character", $this -> id, abs($deltacoppercoins) );				
				
			}
				
		}
		
		$this -> convertcoppercoins();
		
		$silvercoins = Character_Model::get_item_quantity_d( $this -> id, 'silvercoin' );
		$coppercoins = Character_Model::get_item_quantity_d( $this -> id, 'coppercoin' );		
		
		kohana::log( 'debug', '-> (recreated coins) -> Char has silvercoins: ' . $silvercoins . ' coppercoins: ' . $coppercoins );
		
		// nuovo metodo
		
		$this -> silvercoins = $silvercoins + $coppercoins / 100;
		
		kohana::log( 'debug', "-> Stored {$this -> silvercoins} on Character Entity." );
		
		return;
		
	}
	
	/** 
	* Modified Score
	* @param int delta
	* @return none
	*/
	
	public function modify_score( $delta )
	{
		$this -> score += $delta;						
	}
	
	public function convertcoppercoins( )
	{
		$amount = Character_Model::get_item_quantity_d( $this -> id, 'coppercoin' );		
		
		$deltasilvercoins = intval( $amount / 100 );
		$deltacoppercoins = $amount - ($deltasilvercoins * 100 );
		$silvercoin_item = Item_Model::factory( null, 'silvercoin' );
		$coppercoin_item = Item_Model::factory( null, 'coppercoin' );			
		$silvercoin_item -> additem( "character", $this -> id, abs($deltasilvercoins) );		
		$coppercoin_item -> removeitem( "character", $this -> id, abs($deltasilvercoins*100) );				
	}
	
	/** 
	* Controlla se il char ha la quantità  di soldi f
	* @return  boolean
	*/
	
	public function check_money( $price )
	{
		kohana::log('debug', '------ CHECK MONEY ------');
		kohana::log('debug', '-> Checking money. Price: ' . $price ); 
		
		$silvercoins = intval( $price ); 
		$coppercoins = round(( $price - (int)  $price) * 100, 0);
		$totalcoins = $price * 100;
		
		kohana::log('debug', '-> Checking if character ' .  $this -> name . ' has at least ' . $silvercoins . ' silvercoins and ' .  $coppercoins . ' copper coins.' ); 
		
		$silvercoins = Character_Model::get_item_quantity_d( $this -> id, 'silvercoin' );
		$coppercoins = Character_Model::get_item_quantity_d( $this -> id, 'coppercoin' );
			
		$totalownedcoins = $silvercoins * 100 + $coppercoins;
		
		kohana::log('debug', '-> Total owned coins: ' . $totalownedcoins . ', Total due coins:' . $totalcoins ); 
		
		if ( $totalownedcoins >= $totalcoins )
			return true ;
		else
			return false ;
	}					

	
	/**
	* Controlla se il char ha un oggetto
	* @param int $id id Character
	* @param string $tag che identifica l' oggetto
	* @param int $quantity numero di oggetti
	* @param boolean $fullqualitycheck se true, applica il controllo qualità  = 100%
	* return false o true
	*/
	
	public function has_item( $character_id, $tag, $quantity=1, $fullqualitycheck=false)
	{
		
		if ( $fullqualitycheck )
			$quality = 100.00 ;
		else
			$quality = 0;
	
		$db = Database::instance();
		$sql = "select sum(i.quantity) quantity 
		from items i, cfgitems ci
		where i.cfgitem_id = ci.id
		and   i.character_id = " . $character_id  . "
		and   i.structure_id is null
		and   i.quality >= " . $quality . " 
		and   ci.tag = '$tag' "; 
		
		$res = $db -> query( $sql );		
		$q = intval($res[0] -> quantity); 
		
		if ( $q >= $quantity )
		{
			//kohana::log('debug', '--> Has Item: returning true' ); 			
			return true;
		}
			
		//kohana::log('debug', '--> Has Item: returning false' ); 		
		return false;
		
	}

	/** 
	* Ritorna una lista degli item posseduti dal char.
	* Associa ad ogni entry il peso dell' item e calcola il peso totale
	* @param int character_id id del char	
	* @return array $items ( 
	*	items => lista di item, 
	* totalitemsweight => peso totale oggetti)
	*/
	
	public function inventory( $character_id )
	{
		
		$items = array( 
			'items' => null, 
			'totalitemsweight' => 0
		);				
		
		$sql = "
		SELECT i.id item_id, i.*, ci.* 
		FROM items i, cfgitems ci
		WHERE i.cfgitem_id = ci.id 
		AND i.character_id = {$character_id} 
		ORDER BY parentcategory asc, ci.tag asc";
		
		$res = Database::instance() -> query( $sql ) -> as_array();		
		$itemsweight = 0;
		
		foreach ( $res as $item )		
		{			
			
			$item -> totalweight = $item -> weight * $item -> quantity;
			$item -> actions = Item_Model::get_actions( $item );
			$items['items'][$item->parentcategory][] = $item;	
			$items['items']['all'][] = $item;
			$items['totalitemsweight'] += $item -> totalweight;			
		}
		
		return $items;
	
	}	
	
	
	/**
	* Unequip all items
	* @param int $character_id ID Character
	* @param str $message 
	* @return none
	*/
	
	public function unequip_all( $character_id, &$message )	
	{
		$equippeditems = Character_Model::get_equipment( $character_id );
		foreach ((array) $equippeditems as $equippeditem)
		{			
			$ca_undress = Character_Action_Model::factory("undress");		
			$par[0] = $equippeditem->id;
			$par[1] = $character_id;		
			$rc = $ca_undress -> do_action( $par,  $message);
			if ($rc == false)
				return $rc;
		}		
		
		return true;
		
	}
	
	/**
	* Torna cosa il char ha equipaggiato
	* @param: int $character_id Id personaggio
	* @return: array $equipment Array con equipaggiamento:
	*	 array ( 'torso' => array( dati ))
	*/
	
	public function get_equipment( $character_id )
	{
		
		$equipment = array();
		
		$db = Database::instance();
		
		$sql = "
		select 
		i.id, i.equipped, i.color, ci.defense, 
		ci.category, ci.subcategory, ci.parentcategory, ci.tag, ci.car_modifier, ci.critical, 
		ci.mindmg, ci.maxdmg, ci.name, ci.weight, ci.reach, 
		ci.armorpenetration, ci.bluntperc, ci.cutperc, ci.wearfactor, i.quality 	 
		from items i, cfgitems ci 
		where i.character_id = {$character_id}
		and   i.cfgitem_id = ci.id 
		and equipped != 'unequipped' " ;
		
		$items = $db -> query( $sql );
		
		foreach ( $items as $item )
			$equipment[$item-> equipped] = $item ; 
		
		return $equipment; 
		
	}
		
	
	/** 
	* Ritorna l' id dell' item che il char indossa in una determinata
	* parte del corpo
	* @param bodypart parte del corpo (head, legs, right hand, left hand, torso)
	* @return obj $i Item_Model o null
	*/
	
	public function get_bodypart_item( $bodypart )
	{
		
		//kohana::log('debug', '-> Checking Equipped Item on bodypart: ' . $bodypart ); 
			
		$i = ORM::factory('item') -> where (
			array ( 
				'character_id' => $this -> id,
				'equipped' => $bodypart ) )-> find(); 
		
		if ( $i -> loaded )
			return $i;
		
		return null;
		
	}
	
	
	/**
	* Calcola la Base Transportale weight
	* @param str forza
	* @return peso base trasportabile in grammi
	*/
	
	function get_basetransportableweight( $str )
	{		
		$btw = (100 - round(pow( abs ( $str - Character_Model::get_attributelimit() ), 1.3 ), 0)) * 1000 ;		
		return $btw;
	}
	
	/** 
	* Returns max transportable weight for char. 	
	* @param boolean $excludecart: don't include cart in computation
	* @return int $btw transportable weight in grams
	*/
	
	/**
	* Ritorna la prima occorrenza di un certo tipo di item
	* @param int $character_id ID Personaggio
	* @param str $tag Tag Item
	* @param int $id ID item (prima occorrenza)
	* @return mixed NULL (item non trovato) o id item
	*/
	
	function find_item( $character_id, $tag )
	{
		$sql = "
		SELECT i.id 
		FROM items i, cfgitems ci
		WHERE i.cfgitem_id = ci.id
		AND   i.character_id = {$character_id} 
		AND   i.structure_id is null
		AND   ci.tag = '{$tag}'
		LIMIT 1 "; 
		
		$item = Database::instance() -> query ( $sql );
		
		if ( count($item) == 0 )
			return null;
		else 
			return $item[0] ->  id;		
	}
	
	function get_maxtransportableweight( $excludecart = FALSE )
	{
		$btw = Character_Model::get_basetransportableweight( $this -> get_attribute( 'str', true ) );
		
		if ( ! $excludecart )
		{
		
			// controlliamo, se ha il cart3 se è scaduto. Se lo è, distruzione.
			
			if ( 
				Character_Model::has_item( $this->id, 'cart_3', 1 ) 
				and 
				Character_Model::get_premiumbonus( $this -> id, 'supercart' ) === false 
			)
			{
			
				$procart = Database::instance() -> query("
				select i.id 
				from items i, cfgitems ci 
				where i.cfgitem_id = ci.id
				and   ci.tag = 'cart_3'
				and   i.character_id = " . $this -> id );
				
				if ( $procart -> count() > 0 )
				{
					$item = ORM::factory('item', $procart[0] -> id );
					if ( $item -> loaded )
						$item -> destroy();
						
					Character_Event_Model::addrecord( 
					$this -> id,
					'normal', 
					'__events.procartbreaks',
					'evidence' );		
				}	
			}
			
			// se il giocatore ha pià¹ cart, viene calcolata la massima 
			// capacità , ma non si cumula
			
			if ( Character_Model::has_item( $this->id, 'cart_3', 1 ) )
			{ $btw += self::CART_3_STORAGE; }
			elseif ( Character_Model::has_item( $this->id, 'cart_1', 1 ) )
			{ $btw += self::CART_1_STORAGE; }
			elseif ( Character_Model::has_item( $this->id, 'cart_2', 1 ) )
			{ $btw += self::CART_2_STORAGE; }			
			else
				;		
		}
		
		//kohana::log('debug', '-> Maximum transportable weight for char: '  . $this -> name . ' is : '  . $btw/1000 . ' Kg');
		
		return $btw;				
	}
	
	
	function get_transportableweight()
	{
		return ( $this -> get_maxtransportableweight() - $this -> get_transportedweight() );
	}
	
	/** 
	* Ritorna il peso in grammi che il char  trasporta.	
	* @param none
	* @return  integer: grammi che il char sta trasportando.
	*/
	
	function get_transportedweight()
	{
	
		$items = Character_Model::inventory( $this -> id ); 
		$transportedweight = $items['totalitemsweight'];
		
		$db = Database::instance();
		
		// sottraggo dal peso trasportato solo il peso del carretto pià¹ grande
		
		kohana::log('debug', '-> Transportedweight before cart computation: ' . 
			$transportedweight/1000 . ' Kg' );
		
		if ( Character_Model::has_item( $this->id, 'cart_3', 1 ) )
			{ $transportedweight -= self::CART_3_WEIGHT; }
		elseif ( Character_Model::has_item( $this->id, 'cart_1', 1 ) )
			{ $transportedweight -= self::CART_1_WEIGHT; }
		elseif ( Character_Model::has_item( $this->id, 'cart_2', 1 ) )
			{ $transportedweight -= self::CART_2_WEIGHT; }			
		else
			;	
		
		kohana::log('debug', '-> Transportedweight after cart computation: ' . 
			$transportedweight/1000 . ' Kg'  );
		
		return $transportedweight;
	
	}
	
	/** 
	* Ritorna il peso in grammi in eccesso
	* @param none
	* @return  integer: grammi in eccesso
	*/
	
	function get_weightinexcess( $excludecart = false )
	{
		$weightinexcess = $this -> get_transportedweight() - $this -> get_maxtransportableweight( $excludecart ) ;
		
		if ( $weightinexcess < 0 )
			$weightinexcess = 0;
		
		return $weightinexcess;
	
	}
	
	/** 
	* Ritorna il fattore di riposo, legato alla casa posseduta dal char.
	* La taverna ha invece un fattore di riposo fisso.
	* @param structure oggetto struttura
	* @param freerest true/false indica se il rest è gratis
	* @param cartrest true/false indica se il rest è nel cart
	* @return info vettore
	*   restfactor: fattore di recupero (punti energia recuperati per ora)
	*   timeforfullenergy: tempo (in secondi) per recuperare piena energia
	*/
	
	function get_restfactor( $structure, $freerest = false, $cartrest = false )
	{
		
		$info = array( 'restfactor' => 0, 'timeforfullenergy' => 0 );		
		$base_rf = 5;
		
		// caso in cui si riposa in una struttura
		
		if ( $cartrest == false )
		{
			// se il rest è gratis o è c/o il villaggio dei nativi, il RF è basso
			
			$age = $this -> get_age();
			
			if ( $freerest === true and $this -> get_age() > 90) 
				$base_rf = 1.8;		
			
			// RF dipende dal livello della struttura, dalla costituzione e dalla sazietà 				
			
			$base_rf = $base_rf * pow( $structure -> structure_type -> restlevel , 0.2 );
			
			kohana::log('info', '-> ------- REST REST REST -------' );
			kohana::log('info', '-> Computing RF for Structure:' . $structure -> structure_type -> type . 
			' and character: ' . $this -> name);
			kohana::log('info', '-> age: [' . $age. ']' );
			kohana::log('info', '-> Free Rest: [' . (bool) $freerest. ']' );
			kohana::log('info', '-> Cart Rest: [' . (bool) $cartrest . ']' );
			kohana::log('info', '-> Base Rest: [' . ($base_rf) . ']' );
			kohana::log('info', '-> Structure Rest Level: [' . $structure-> structure_type -> restlevel . ']');
			
		}
		// nel caso del cart il RF rimane 5.
		else
			;
				

		
		// calcolo RF
		$info['restfactor'] =  $base_rf * 
		( 100 - (( 10 - $this -> get_attribute( 'cost' ) )  * 1.9 ) )/100 * 
		( 100 - (( 25 - $this -> glut ) / 1.2 ))/100 ;
		
		kohana::log('info', "-> Constitution: [{$this -> get_attribute( 'cost' )}]" );
		kohana::log('info', "-> Current Energy: [{$this -> energy}]" );
		kohana::log('info', "-> Glut: [" . ($this -> glut/ 50) * 100 . "%]");		
		kohana::log('info', "-> Restfactor (after cost. and glut check): [{$info['restfactor']}" );		
		
		$info['timeforfullenergy'] = round (( ( 50 - $this -> energy ) / $info['restfactor'] ) * 3600, 0 );
		
		kohana::log('info', '-> Timeforfullenergy: [' . $info['timeforfullenergy'] . '] - ' . 
			'[' .Utility_Model::secs2hmstostring( $info['timeforfullenergy'] ) .']' );
		
		/////////////////////////////////////////		
		// bonus se il char ha lo stato nobile
		/////////////////////////////////////////
		
		if ( Character_Model::get_premiumbonus( $this -> id, 'basicpackage' ) !== false )
			$info['restfactor']  = 	$info['restfactor']  * 2;
		
		kohana::log('info', '-> Restfactor (after basic package bonus): [' . $info['restfactor'] .']');				
		
		/////////////////////////////////////////		
		// applico eventuali malus religiosi
		/////////////////////////////////////////
		// Verifico che non ci siano dei malus contro la chiesa del char
		$char_church_id = $this->church_id;
		// Conto quanti malus ci sono verso la chiesa del char
		$num_malus = Church_Model::get_num_malus_against_my_church($char_church_id, 'curseinfidels');
		// Calcolo l'energia residua dopo applicazione di tutti i malus trovati
		$malus_to_apply = (1 - $num_malus * 0.25);
		$info['restfactor'] = ($info['restfactor'] * $malus_to_apply);
		kohana::log('info', '-> Restfactor (after religious malus): [' . $info['restfactor'] .']' );
		
		kohana::log('info', '-> Current char energy: [' . $this -> energy  .']');
		kohana::log('info', '-> Applying server speed...');
		kohana::log('info', '-> Server speed is: ' . Kohana::config('medeur.serverspeed'). ']');
		
		$info['restfactor'] *=  Kohana::config('medeur.serverspeed');
		kohana::log('info', '-> Restfactor (after server speed): [' . $info['restfactor'] .']' );
		
		// Apply speed bonus
		$speedbonus = Character_Model::get_stat_from_cache($this -> id, 'speedbonus');
		if ($speedbonus -> loaded and $speedbonus -> stat1 > time() )		
			$info['restfactor'] *= $speedbonus -> value;
		
		kohana::log('info', '-> Restfactor FINAL (% points per hour): ' . $info['restfactor']/2 .']' );
		kohana::log('info', '-> Restfactor FINAL (% points per minute): ' . $info['restfactor']/2/60 .']' );
		
		// Calcolo il tempo residuo per recuperare tutta l'energia
		$info['timeforfullenergy'] = round (( ( 50 - $this -> energy ) / $info['restfactor'] ) * 3600, 0 ) ;
				
		kohana::log('info', '-> Timeforfullenergy: [' . $info['timeforfullenergy'] . '] - [' . Utility_Model::secs2hmstostring( $info['timeforfullenergy'], 'hours' ) . ']' );
		
		return $info ;
		
	}

	
	/** 
	* Modifica la sazietà 
	* @param delta delta da aggiungere o sottrarre	
	* @param deltaflag se true, assegna direttamente il valore passato
	* @return none
	*/

	function modify_glut( $delta, $deltaflag=false )
	{
			
		if ( $deltaflag == false )
			$this -> glut = $this -> glut + $delta;
		else
			$this -> glut = $delta;

		if ( $this -> glut > self::LIMIT_GLUT )
		{ $this -> glut = self::LIMIT_GLUT; }
		
		if ( $this-> glut < 0 )
		{ $this -> glut = 0; }

		kohana::log( 'debug', 'modifying glut: ' . $delta . ' to char: ' . $this -> name . ' ' . $this -> id );
		
		// Non invalido la cache perchè l' invalidazione è sul save.
		
	}

	/** 
	* Modifica l' energia
	* @param int $delta delta da aggiungere o sottrarre
	* @param boolean $assigndirectly se true, assegna direttamente il valore passato
	* @param str $reason Reason
	* @return none
	*/
	
	function modify_energy( $delta, $assigndirectly = false, $reason = 'notspecified' )
	{
		kohana::log('debug', "Delta: {$delta}");
		kohana::log('debug', "Current Energy: {$this -> energy}");
		
		if ( $assigndirectly == false )
			$this -> energy = $this -> energy + round($delta,0);
		else
			$this -> energy = $delta;				
		
		if ( $this -> energy < 0 )
		{ $this -> energy = 0; }

		if ( $this -> energy > self::LIMIT_ENERGY )
		{ $this -> energy = self::LIMIT_ENERGY; }
		
		kohana::log('debug', "Current Energy After delta: {$this -> energy}");
		
		Character_Event_Model::addrecord ( 
			$this -> id, 
			'normal', 
			'__events.energymodified' . 
			';' . (Utility_Model::number_format($delta/50,2)*100) . 
			';__charactions.reason_' . $reason );
				
	}

	/** 
	* Modifies health
	* @param int $delta delta to add
	* @param boolean $replace if true, replaces value
	* @return none
	*/
	
	function modify_health( $delta, $replace = false )
	{
				
		if ( $replace == false )
			$this -> health = $this -> health + $delta;
		else
			$this -> health = $delta;
		
		if ( $this -> health < 0 )
		{ $this -> health = -1; }

		if ( $this -> health > self::LIMIT_HEALTH )
		{ $this -> health = self::LIMIT_HEALTH; }		
		
		kohana::log( 'debug', '-> modifying health: ' . $delta . ' to char: ' . $this->name . ' ' . $this -> id );
				
		// Non invalido la cache perchè l' invalidazione è sul save.		

	}
	
	/**
	* funzione che ritorna la quantità  di un certo item
	* param tag tag dell' oggetto
	* return q quantità  posseduta
	*/
	
	function get_item_quantity_d( $char_id, $tag )
	{
				
		$cachetag = '-charinfo_' . $char_id . '_' . $tag; 				
		$quantity = My_Cache_Model::get( $cachetag );
				
		if ( is_null( $quantity ) )		
		{
						
			
			$db = Database::instance();
			
			$sql = "select ifnull( sum( quantity ), 0) q from items i, cfgitems ci
			where i.cfgitem_id = ci.id 
			and   i.character_id = " . $char_id . "
			and   ci.tag = '$tag'";
			
			$res = $db -> query( $sql ) -> as_array();		
			
			$quantity = $res[0] -> q;
			
			My_Cache_Model::set( $cachetag, $quantity );
			
		}
		
		return $quantity;
	
	}
	
	
	/**
	* funzione che ritorna la quantità  di un certo item
	* param tag tag dell' oggetto
	* return q quantità  posseduta
	*/
	
	function get_item_quantity( $tag )
	{
		

		$cachetag = '-charinfo_' . $this -> id . '_' . $tag ; 
		//kohana::log('debug', "-> Getting $cachetag from CACHE.");		
		
		$quantity = null;		
		$quantity = My_Cache_Model::get( $cachetag );		
		
		if ( is_null( $quantity ) )		
		{
			//kohana::log('debug', "-> Getting $cachetag from DB.");
			$db = Database::instance();
			$sql = "select sum( quantity ) quantity from items i, cfgitems ci
			where i.cfgitem_id = ci.id 
			and   i.character_id = " . $this -> id . "
			and   ci.tag = '$tag'";
			$res = $db -> query( $sql ) -> as_array();		
			$quantity = $res[0] -> quantity;
			My_Cache_Model::set( $cachetag, $quantity );
		}							
		
		return $quantity;
	
	}

	/**
	* torna il ruolo corrente del char, metodo statico
	* @param none
	* @return oggetto Character_Role o null
	*/
	
	function get_current_role_s( $character_id )
	{
		
		$role = ORM::factory('character_role')
			-> where ( 
					array( 
					'character_id' => $character_id,
					'gdr' => false,
					'current' => true ) 
				) -> find();
		
		if ($role -> loaded == false )
			return null;
		else
			return $role;
		
	}
	
	/**
	* torna il ruolo corrente del char
	* @param none
	* @return oggetto Character_Role o null
	*/
	
	function get_current_role()
	{
		$currentrole = null;
		
		foreach ( $this -> character_roles as $role )
			if ( $role -> current and ! $role -> gdr )
				$currentrole = $role;
		
		return $currentrole;
	}
	
	// functione che torna il character che è il riporto
	// gerarchico.
	// @output: struttura character_role se esiste altrimenti null
	//
	
	function get_upperhierarchicallevel()
	{
		$role = $this -> get_current_role();
		
		//print kohana::debug( $role ); exit();
		
		$upper = null;
		
		if ( !$role )
			;
		else
			switch ( $role->tag )
			{
				case 'king': 
				case 'duke':
				case 'seigneur':				
				case 'doge':
				case 'count':
				case 'church_level_1':
					return null ; break;
				case 'vassal' : 					
					$upper =  ORM::factory("character_role")
						->where (
							array(
								'current' => true,
								'region_id' => Kingdom_Model::get_capitalregion( $this -> region -> kingdom_id )))
						->in( 'tag'	, array( 'king') )->find();								
					break;
				case 'sheriff':
				case 'judge':
				case 'academydirector':
				case 'drillmaster':
					$upper =  ORM::factory("character_role")
						->where (
							array(
								'current' => true,
								'kingdom_id' => $this -> region -> kingdom -> id,
								'region_id' => $this->region_id,
								'tag' => 'vassal') )->find();
						break;
				case 'priest': 
					$prieststruct = ORM::factory("structure_type") ->where ( array('associated_role_tag' => 'priest') ) ->find();
					$mystructure = ORM::factory("structure") ->where ( array('character_id' => $this->id, 'structure_type_id' => $prieststruct->id) ) ->find();
					$parentstructure = ORM::factory("structure", $mystructure->parent_structure_id);

					$upper =  ORM::factory("character_role")
						->where (
							array(
								'current' => true,
								'character_id' => $parentstructure->character_id) )->find();
				break;
				case 'bishop':
					$bishopstruct = ORM::factory("structure_type") ->where ( array('associated_role_tag' => 'bishop') ) ->find();
					$mystructure = ORM::factory("structure") ->where ( array('character_id' => $this->id, 'structure_type_id' => $bishopstruct->id) ) ->find();					
					$parentstructure = ORM::factory("structure", $mystructure->parent_structure_id);
					$upper = ORM::factory("character_role")
						->where (
							array(
								'current' => true,
								'character_id' => $parentstructure->character_id) )->find();
					
				break;
				case 'cardinal':
					$cardinalstruct = ORM::factory("structure_type") ->where ( array('associated_role_tag' => 'cardinal') ) ->find();
					$mystructure = ORM::factory("structure") ->where ( array('character_id' => $this->id, 'structure_type_id' => $cardinalstruct->id) ) ->find();
					
					$parentstructure = ORM::factory("structure", $mystructure->parent_structure_id);

					$upper = ORM::factory("character_role")
						->where (
							array(
								'current' => true,
								'character_id' => $parentstructure->character_id) )->find();
				break;
				default: break;
			}
			
			if ( !is_null( $upper ) and $upper->loaded )
				return $upper;
			else
				return null;
	}
		
	/**
	* Get info sui giocatori (chiamata da home)
	*/
	
	function getplayersinfo()
	{
		
		$info = array( 
			'active' => 0, 
			'online' => 0, 
			'registeredtoday' => 0 );
		
		$db = Database::instance();
		$res = $db -> query('select count(*) count from characters'); 		
		$info['active']  = $res[0]->count;
		
		$yesterday = mktime(0, 0, 0, date("m") , date("d") - 1, date("Y"));
		$today = mktime(0, 0, 0, date("m") , date("d"), date("Y"));
		$tomorrow = mktime(0, 0, 0, date("m") , date("d") + 1, date("Y"));
		
		$info['registeredtoday'] = 
		ORM::factory('user') -> where( 
			array ( 
				'created>'=> $today,
				'created<'=>$tomorrow				
			) )->count_all();
			
		
		$res = $db -> query( "select count(*) count from characters where  (unix_timestamp() - lastactiontime) < " . Kohana::config('medeur.maxidletime') );
		$info['online']  = $res[0] -> count;
		
		return $info;
		
	}
	
	/**
	* Funzione che elimina fisicamente i dati di un char morto
	* sono mantenuti solo gli annunci del nodo
	* @param none
	* @return none
	**/
	
	function deletecharfromdb()
	{
	
		if ( kohana::config( 'medeur.death_enabled' ) == false )
			return;
		
		$roles = $this -> user -> roles;
		foreach ( $roles as $r )
			if ( $r -> name == 'admin' or $r -> name == 'staff'  )
				return;
				
		kohana::log('alert', '*** Canceling from db character id: ' . $this->id );

		$db = Database::instance();		
		
		////////////////////////////////////////////////
		// memorizzo la data di morte
		////////////////////////////////////////////////
		
		$db -> query("update characters set deathdate = unix_timestamp(),
			death_region_id = " . $this -> position_id . " where id = " . $this->id);
		
		// permanent event
		
		$region = ORM::factory('region', $this -> position_id );		
		Character_Permanentevent_Model::add(
					$this -> id, 
					'__permanentevents.death' . ';' . 
					'__' . $region -> name . ';' .
					'__global.deathreasonfamine'); 
				
		////////////////////////////////////////////////
		// traccio perdita silver coins
		////////////////////////////////////////////////
		
		$silvercoins = Character_Model::get_item_quantity_d( $this -> id, 'silvercoin' );	
		Trace_Sink_Model::add( 'silvercoin', $this -> id, -( $silvercoins ), 'chardeath'); 
		
		////////////////////////////////////////////////
		// Cancella payments
		////////////////////////////////////////////////
		
		$db -> query("delete from electronicpayments where user_id = {$this -> user_id}");		
		
		////////////////////////////////////////////////
		// Archivio e Cancello bonus
		////////////////////////////////////////////////
		
		$db -> query( 'replace into ar_character_premiumbonuses ( select * from character_premiumbonuses where character_id = ' . $this -> id . ')' );
		
		$db -> query("delete from character_premiumbonuses where character_id = {$this -> id}");
		
		////////////////////////////////////////////////
		// cancella mail ricevute e marca per cancellazione 
		// quelle inviate			
		////////////////////////////////////////////////
		
		$db -> query( 'replace into ar_messages ( select * from messages where char_id = ' . $this -> id . ')' );		
		$db -> query( 'delete from messages where char_id = ' . $this -> id);		
			
		////////////////////////////////////////////////	
		// Cancella tutte le actions
		////////////////////////////////////////////////
		
		$db -> query( 'delete from character_actions where character_id = ' . $this -> id);
		
		////////////////////////////////////////////////
		// Cancello i linguaggi
		////////////////////////////////////////////////
		
		$db -> query( 'delete from user_languages where user_id = ' . $this -> user_id);
		
		////////////////////////////////////////////////
		// Cancello tutte le grants
		////////////////////////////////////////////////
		
		$db -> query ( 'delete from structure_grants where character_id = ' . $this -> id ); 
		$db -> query ( 'delete from jobs where ( character_id = ' . $this -> id . ' or employer_id = ' . $this -> id . ')' ); 
		
		////////////////////////////////////////////////
		// cancello eventuali partecipazioni a battaglie
		////////////////////////////////////////////////
		
		$db -> query ( 'delete from battle_participants where character_id = ' . $this -> id ); 		
		
		////////////////////////////////////////////////
		// Cancella tutti gli eventi
		////////////////////////////////////////////////
		
		$db -> query( "replace into ar_character_events ( select * from character_events where type = 'normal' and character_id = " . $this->id . ')' );
		$db -> query( "delete from character_events where type = 'normal' and character_id = " . $this -> id);
			
		$role = $this -> get_current_role();
		
		////////////////////////////////////////////////
		// Cancella le relazioni parentali (wedding)
		////////////////////////////////////////////////
		
		$db -> query( "
		delete from character_relationships 
		where ( sourcechar_id = " . $this -> id . " or targetchar_id = " . $this -> id . " ) 
		and type in ( 'husband', 'wife' )" );
				
		////////////////////////////////////////////////
		// pubblica annuncio solo per char che hanno 
		// ruoli
		////////////////////////////////////////////////
		
		if ( !is_null( $role) )
		{					
			
			Character_Event_Model::addrecord( 
				null, 
				'announcement', 
				'__events.charinroledied_announcement'.
				';'. $this -> name . $this -> get_rolename()				
				);
		}		
		
		////////////////////////////////////////////////
		// Salvo Cancello item del char	
		////////////////////////////////////////////////
		
		$db -> query( 'replace into ar_items ( select * from items where character_id = ' . $this -> id . ' or 
		seller_id = ' . $this -> id . ' )' );
		
		$db -> query( "
			replace into ar_items 
			( 
				select * from items i where structure_id in
				(
					select s.id from structures s
					where character_id = " . $this -> id . "
				)
			)"
		);
		
		////////////////////////////////////////////////
		// Cancello armature prestate
		////////////////////////////////////////////////
		
		$db -> query( "delete from structure_lentitems where target_id = {$this -> id}");		
		
		foreach ( $this -> item as $item )
			$item -> destroy();
		
		////////////////////////////////////////////////
		// Cancello item nel market	venduti dal char
		////////////////////////////////////////////////
		
		$items = ORM::factory('item') -> where( 'seller_id', $this -> id ) -> find_all() ; 
		foreach ( $items as $item ) 
			$item -> destroy();
		
		////////////////////////////////////////////////
		// cancello tutte le strutture del char, di tipo 
		// player.
		////////////////////////////////////////////////
		
		$db -> query( "replace into ar_structures ( select * from structures where character_id = " . $this -> id . "	 and structure_type_id in ( select id from structure_types where subtype = 'player')) " );				
		$res = $db -> query ("select id from structures where character_id = " . $this -> id . 
			" and structure_type_id in ( select id from structure_types where subtype = 'player') " );

		foreach ( $res as $row )
		{
			$structure = StructureFactory_Model::create( null, $row -> id );
			if ( $structure -> loaded )
				$structure -> destroy();
		}
		
		////////////////////////////////////////////////
		// rimuovo annunci
		////////////////////////////////////////////////
		
		ORM::factory('boardmessage') -> where ( 'character_id', $this -> id ) -> delete_all();
		
		////////////////////////////////////////////////
		// rimuovo il controllo del char da strutture 
		// che hanno un ruolo		
		////////////////////////////////////////////////
		
		$db -> query( 'update structures set character_id = null where character_id = ' . $this -> id ); 			
		$db -> query( 'update character_roles set end = unix_timestamp(), current = 0 where character_id = ' . $this -> id . ' and current = 1' ); 
				
		////////////////////////////////////////////////
		// cancella sentenze
		////////////////////////////////////////////////
		
		$db -> query( "update character_sentences set status = 'canceled', cancelreason = 'Death of Character' where character_id = " . $this->id );		
	
		////////////////////////////////////////////////
		// cancella i gruppi creati dall' utente
		////////////////////////////////////////////////
				
		$db -> query( 'delete from group_characters where group_id in ( select id from groups where character_id = ' . $this -> id . ')' ); 
		$db -> query( 'delete from groups where character_id = ' . $this -> id ); 
		
		////////////////////////////////////////////////
		// cancella l' utente dai gruppi a cui era 
		// iscritto
		////////////////////////////////////////////////
		
		$db -> query( 'delete from group_characters where character_id = ' . $this -> id ); 
		
		////////////////////////////////////////////////
		// cancella le statistiche, prima faccio
		// una copia dei dati
		////////////////////////////////////////////////
		
		$db -> query('replace into ar_character_stats ( select * from character_stats where character_id = ' . $this->id . ')' );
		$db -> query('delete from character_stats where character_id = ' . $this->id  );
		
		////////////////////////////////////////////////
		// cancella i titoli
		////////////////////////////////////////////////
		
		$db -> query('replace into ar_character_titles ( select * from character_titles where character_id = ' . $this->id . ')' );
		$db -> query('delete from character_titles where character_id = ' . $this -> id  );
		
		////////////////////////////////////////////////
		// cancella dalla tabella char, prima faccio
		// 		una copia dei dati
		////////////////////////////////////////////////
		
		$db -> query('replace into ar_characters ( select * from characters where id = ' . $this->id . ')' );		
		$db -> query('delete from characters where id = ' . $this->id );
		
		////////////////////////////////////////////////
		// disabilitazione utente forum
		////////////////////////////////////////////////
		
		if ( kohana::config('medeur.deleteforumaccount') )
			ForumBridge_Model::delete_account( $this, 'forum' ); 
		
		////////////////////////////////////////////////
		// manda una email all' utente		
		////////////////////////////////////////////////
		
		$subject = Kohana::lang('user.characterdead_email_subject');
		$body = kohana::lang('user.characterdead_email_body', $this -> user -> username, $this -> name );
		$result = Utility_Model::send_notification( $this -> user -> id, $subject, $body );
		
	}

	public function get_changeregion_price ($origin, $dest)
	{
		if ( Character_Model::get_premiumbonus( $this -> id, 'basicpackage') !== false )					
		{ return 25; }
		else
		{ return 50; }

	}
	
	/**
	* Returns character premium bonuses	
	* @param int $char_id ID Character
	* @param string bonus name
	* @return false or array
	*/
	
	function get_premiumbonus( $char_id, $name )
	{
		//kohana::log( 'info', "-> get_premiumbonus: checking bonus $name for char: $char_id");
		
		$bonuses = Character_Model::get_premiumbonuses( $char_id );		
		//kohana::log('info', kohana::debug( $bonuses )); 
		
		if ( !is_null($bonuses) and array_key_exists( $name, $bonuses ) )
		{
			//kohana::log('info', 'Count: ' . count( $bonuses[$name] ));
			//kohana::log('info', kohana::debug( $bonuses[$name] )); 
			if ( count( $bonuses[$name] ) > 1 )
			{				
				$bonus = $bonuses[$name];
			}
			else
				$bonus =  $bonuses[$name][0];
		}
		else
		{
			//kohana::log('debug', '-> get_premiumbonus: key NOT found: ' . $name );
			return false;
		}
		
		//kohana::log('debug', kohana::debug( $bonus )); 
		
		return $bonus ;
	}
	
	
	/** 
	* funzione che ritorna il nome di un char comprensivo
	* di titolo nobiliare
	* esempio: Lord Ildebrando Malatesta
	* @input: nessuno
	* @output: stringa i18n
	*/
	
	public function get_name( $translate = true, $twolines = false )
	{
		// controlliamo se ha lo stato nobile. Se ce l'ha, va messo anche il titolo scelto.
		
		$name = '';				
			
		$title = Character_Model::get_basicpackagetitle( $this -> id );		
		if ( !empty($title) )
			if ( $translate )
				$name = kohana::lang( $title ) . ' ' ;
			else
				$name = $title .';';		
				
		// Controllo se voglio che il titolo e il nome
		// siano visualizzati su due linee distinte
	
		if ($twolines)
				$name .='<br/>'. $this->name;
			else
				$name .= $this->name;
		
		return $name;
	}
	
	/** 
	* funzione che ritorna il ruolo di un char 	
	* esempio: Reggente - Ducato di Milano
	* @param: translate se true, traduce direttamente
	* @output: stringa i18n
	*/
	
	public function get_rolename( $translate = false )
	{
		// controlliamo se ha un ruolo
		
		$role = $this -> get_current_role();
		if ( is_null( $role ) )
			return null;
		else						
			return $role -> get_title( $translate );
	}
	
	/** 
	* funzione che ritorna la firma da apportare
	* a editti ecc.
	* esempio: Marchese Ildeprando Malatesta - Reggente del Ducato di Milano
	* @input: nessuno
	* @output: stringa i18n
	*/
	
	public function get_signature( $translate = false )
	{
		
		if ( $translate )
			$signature = kohana::lang('global.signature', $this -> name, $this -> get_rolename( $translate )); 
		else
			$signature = '__global.signature;' . 
			$this -> name . 
			$this -> get_rolename( $translate );
			
		//kohana::log( 'debug', 'signature: ' . $signature );
		
		return $signature;
	}
	
	/** 
	* Funzione che ritorna se l' utente è morto o meno
	* @input: nessuno
	* @output: true o false
	*/
	
	public function is_dead( )
	{
		$character = ORM::factory('character', $this -> id );
		return ( !$character -> loaded );	
	}
	
	/** 
	* Funzione che ritorna il ruolo amministrativo dell' utente
	* @param obj $character Character_Model
	* @param str $name role name
	* @return string $role [admin|newbornrole]
	*/
	
	public function has_merole( $character, $name )
	{
		foreach ( $character -> user -> roles as $role )
			if ($role -> name == $name )
				return true;
		return false;
	}	
	
	/** 
	* Modifica lo stato del char, ed invalida la cache.	
	* @param data stato
	* @return niente
	*/
	
	function modify_status( $status )
	{	
		$this -> status = $status;		
		
		// Non invalido la cache perchè l' invalidazione è sul save.		

	}
	
	/**
	* Invalida la cache del char
	* @param char_id ID del char a cui invalidare la cache del char
	*/
	
	function invalidate_char_cache( $char_id )
	{
		kohana::log( 'debug', 'Invalidatingng char cache of char: ' . $char_id );
		My_Cache_Model::delete( '-charinfo_' . $char_id . '_charobj' );
		My_Cache_Model::delete( '-charinfo_' . $char_id . '_chararr' );	
	}
	
	/** 
	* Funzione che gestisce i redirect
	* @param none
	* @return none
	*/
	
	function handle_char_specialstatus( )
	{
		
		$uri = new URI();
		
		$controller = Router::$controller;
		$method = Router::$method;
		$parameters = implode ('/', Router::$arguments );
		$char_id = Session::instance()->get('char_id'); 

		// se il gioco è in modalità  admin
		// e il char non è admin, si butta fuori.
		
		kohana::log('debug', '*** Handle Character Status ***');
		
		if ( 
			! Auth::instance() -> logged_in('admin') and 
			! Auth::instance() -> logged_in('staff') and 
			kohana::config( 'medeur.loginonlyadmin' ) )
		{
			Auth::instance() -> logout();
			url::redirect('/');
		}
		
		if ( 
			$controller == 'page' and $method == 'display' or
			$controller == 'boardmessage' and $method == 'index' )
			$action = $controller . '/' . $method . '/' . $parameters;
		else
			$action = $controller . '/' . $method ;
			
	kohana::log( 'debug', "-> Handling action: [{$action}] for char: {$char_id}" );
		
		// array di azioni sempre permesse
				
		$commonurls = array( 
			'admin/console',
			'admin/read_adminmessage', 
			'admin/list_allmessages', 
			'admin/givedoubloons',
			'admin/emptycharcache',
			'banner/display',
			'boardmessage/index/',
			'boardmessage/index/europecrier/ALL',
			'boardmessage/index/job',
			'boardmessage/index/europecrier',
			'boardmessage/index/suggestion',
			'boardmessage/index/other',
			'boardmessage/like',
			'boardmessage/dislike',
			'boardmessage/add',
			'boardmessage/edit',
			'boardmessage/give_globalvisibility',
			'boardmessage/bump_up',
			'boardmessage/index/suggestion/fundable',
			'boardmessage/index/suggestion/funded',
			'boardmessage/index/suggestion/ALL',
			'boardmessage/delete',
			'boardmessage/report',
			'boardmessage/view',			
			'boardmessage/view_sponsorlist',
			'boardmessage/sponsorise',
			'bonus/getdoubloons',
			'bonus/getdoubloons_bitcoin',
			'bonus/buy',
			'buildingsite/info',
			'character/accessrpforum',
			'character/cancel_action',
			'character/change_avatar',
			'character/change_description',
			'character/change_slogan',
			'character/change_history',
			'character/change_signature',
			'character/delallevents',
			'character/details',
			'character/complete_action',
			'character/history',
			'character/inventory',
			'character/list_avatar',
			'character/listall',
			'character/myproperties',
			'character/publicprofile',
			'character/rankings',
			'character/role',
			'character/unequip_all',
			'event/deleteselected',
			'event/show',
			'group/delete/',
			'group/edit',
			'group/mygroups',
			'group/message',
			'group/transfer_leadership',
			'group/upload_image',
			'group/view',
			'item/eat',
			'item/undress',
			'item/wear',
			'item/senddoubloons',
			'jqcallback/callbbcodeparser',
			'jqcallback/listallchars',
			'jqcallback/get_servertime',			
			'jqcallback/bbcodepreview', 
			'jqcallback/loadstructureinfo',
			'jqcallback/getinfo',
			'jqcallback/loadcharacterinfo',
			'language/change_language',
			'market/buy',
			'message/delete',
			'message/deleteselectedmessages',
			'message/received',
			'message/sent',
			'message/view',
			'message/write',
			'page/announcements',
			'page/battlereport',
			'page/display/credits',
			'page/display/help',
			'page/display/getbitcoins',
			'page/display/toplist',
			'page/rankings',
			'page/shop',
			'page/shop_superrewards',
			'page/shop_matomymoney',
			'page/shop_sponsorpay',
			'page/serverinfo',
			'region/info',
			'region/listchars',
			'region/listshops',
			'region/regionpresentchars',
			'map/view',			
			'religion_1/manage/',
			'religion_2/manage/',
			'religion_3/manage/',
			'religion_4/manage/',
			'shop/manage/',
			'region/view_announcements',
			'region/info_laws',
			'region/info_diplomacy',
			'shop/listcraftableitems',
			'shop/manage',
			'structure/info',
			'structure/events',
			'structure/inventory',
			'toplist/vote',
			'user/bonuspurchases',
			'user/logout',
			'user/referrals',
			'user/configure',
			'wardrobe/configureequipment',
			);
		
			// l' azione fa parte degli URL comuni, quindi non ridireziono
			
			if ( in_array( $action, $commonurls) )
			{
				kohana::log('debug', '-> action is in commonurl, returning.' );
				return;		
			}
			
			// Meditazione
			
			if ( Character_Model::is_meditating( Session::instance()->get('char_id') ) )
			{
				kohana::log('debug', '--> char is meditating.' ); 				
				$allowedurls = array_merge( $commonurls, array( 'page/retire'));			
					if ( !in_array( $action, $allowedurls) )
				{
					kohana::log( 'debug', '*** handle_char_specialstatus: redirecting...');
					url::redirect('page/retire');			
				}
				else
				{				
					return;
				}
			}
			
			// In prigione
			
			if ( Character_Model::is_imprisoned( Session::instance()->get('char_id') ) )
			{
				
				kohana::log('debug', '--> char is imprisoned.' ); 				
				
				$allowedurls = array_merge( $commonurls, array( 
					'page/jail',
					'structure/donate',
				));	
				
				if ( !in_array( $action, $allowedurls) )

				{
					kohana::log( 'debug', '*** handle_char_specialstatus: redirecting...');
					url::redirect('page/jail');			
				}
				else
				{				
					return;
				}
			}
			
			// Sta combattendo
			
			if ( Character_Model::is_fighting( Session::instance() -> get('char_id') ) )
			{
				
				kohana::log('debug', '--> Char is fighting.' ); 				
				
				$allowedurls = array_merge( 
					$commonurls, 
					array ( 
					'battlefield/raidloot',
					'structure/inventory', 
					'structure/events', 
					'bonus/getdoubloons',
					'bonus/getdoubloons_bitcoin',
					'bonus/index',
					'bonus/buy',
					'battlefield/joinfaction', 
					'battlefield/enter',
					'battlefield/entercity',
					'battlefield/retire', 
					'battlefield/rest',
					'newchat/init',
					'bonus/buy',
					'character/change_language',
					'character/cure',
					'item/apply', 
					'structure/massitemtransfer',
					'royalpalace/chooserevoltfaction',
					'character/move',
					'character/sail',
					'structure/take',
					'structure/drop',	
					'user/profile',
					'map/view' )); 	
				
				//var_dump( $allowedurls ); exit; 
				
				if ( !in_array( $action, $allowedurls) )
				{					
					kohana::log( 'debug', '*** handle_char_specialstatus: redirecting...');					
					
					url::redirect('battlefield/enter');
				}
				else
				{				
					return;
				}
			}			
			
			// Sta viaggiando
			
			if ( Character_Model::is_traveling( Session::instance()->get('char_id') ) )
			{
					kohana::log('debug', '--> Char is traveling.' ); 				
					$nonallowedurls = array (						
						'region/info',
						'region/listchars',						
						'map/view',
					);
						
					if ( in_array( $action, $nonallowedurls) )
					{						
						kohana::log( 'debug', '*** handle_char_specialstatus: redirecting...');
						url::redirect('map/view');			
					}
					else
					{				
						kohana::log('debug', 'Action is allowed'); 
						return;
					}
			}		
			
					
			
	}	
	
	/*
	* Returns active character bonuses
	* @param int $char_id ID character
	* @return $array vettore con bonuses
	*/
	
	function get_premiumbonuses( $char_id )
	{
	
		$cachetag = '-charinfo_' . $char_id . '_premiumbonuses' ; 
		$bonuses = null;
		$bonuses = My_Cache_Model::get( $cachetag ); 			
		
		if ( is_null( $bonuses ) )
		{

                       $character = Character_Model::get_info( $char_id );

			
			$res = Database::instance() -> query("
			SELECT cp.id, c.name, c.cutunit, cp.user_id, 
			cp.targetuser_id, cp.targetcharname, cp.character_id, cp.cfgpremiumbonus_id, 
			cp.structure_id, 
			cp.starttime, cp.endtime, cp.param1, cp.param2, cp.doubloons 
			FROM character_premiumbonuses cp, cfgpremiumbonuses c
			WHERE cp.targetuser_id = {$character -> user_id} 
			AND   cp.cfgpremiumbonus_id != 0 
			AND   cp.cfgpremiumbonus_id = c.id 
			AND   endtime > unix_timestamp()
			ORDER BY cp.id DESC");
			
			$i = 0;					
			
			foreach ( $res as $bonus ) 
			{	
			
				$a = array();
				
				$a['id'] = $bonus -> id;
				$a['user_id'] = $bonus -> user_id;
				$a['targetuser_id'] = $bonus -> targetuser_id;
				$a['structure_id'] = $bonus -> structure_id;
				$a['targetcharname'] = $bonus -> targetcharname;
				$a['character_id'] = $bonus -> character_id;				
				$a['cutunit'] = $bonus -> cutunit;				
				$a['cfgpremiumbonus_id'] = $bonus -> cfgpremiumbonus_id;
				//$a['cfgpremiumbonuses_cut_id'] = $bonus -> cfgpremiumbonuses_cut_id;				
				$a['name'] = $bonus -> name;				
				$a['starttime'] = $bonus -> starttime;
				$a['endtime'] = $bonus -> endtime;
				$a['param1'] = $bonus -> param1;				
				$a['param2'] = $bonus -> param2;
				$a['doubloons'] = $bonus -> doubloons;	
				
				$bonuses[$bonus -> name][] = $a;
			}				
			
			My_Cache_Model::set( $cachetag, $bonuses ); 
		
		}
		
		return $bonuses ;
	
	}
	
	
	/**
	* Verifica se il char ha l' azione pending passata in corso
	* @param char_id ID char
	* @param action nome azione
	* @return false o true
	*/
	
	static function get_pending_action_d( $char_id, $action )
	{
		//kohana::log( 'info', '-> Checking if character ' . $char_id . ' has a pending action: ' . $action );
		$pendingaction = Character_Model::get_currentpendingaction( $char_id ); 				
		//kohana::log( 'info', "-> Character {$char_id} has a pending action: [{$pendingaction}]");
		if ( 
			$pendingaction != 'NOACTION' and $pendingaction['action'] == $action and $pendingaction['status'] == 'running' ) 
		{			
			//kohana::log( 'info', "-> Character {$char_id} has a pending action: [{$action}]");
			return true;
		}
		else
			return false;		
	
	}
	
	/**
	* torna il rank
	* @param type tipo rank
	* @return posizione o NULL
	*/
	
	function get_rank( $type )
	{
	
		$rank = ORM::factory('stats_global') -> where 
			( array( 
				'type' => $type, 
				'stats_id' => $this -> id )  ) -> orderby ('id', 'DESC') -> find();
			
		//kohana::log('debug', kohana::debug( $rank ));
		
		if ( $rank->loaded)
			return $rank ;
		else
			return null;
	}
	
	
	/**
	* Render char picture 
	* @param: array $equippeditems arrays of equipment items
	* @param string $mode preview mode
	* @return none
	*/
	
	function render_char ( $equippeditems, $mode = 'wardrobe', $size = 'large' )
	{		
		
		/////////////////////////////////////////////////////////////////
		// Visualizzo il profilo male/female in base al sesso del char
		// comprensivi di volto e capelli (per ora statici)	
		/////////////////////////////////////////////////////////////////
		
		$skincolorstat = $this -> get_stat( 'skincolorset' ); 
		
		if ( $skincolorstat -> loaded == false )
			$skincolorset = 'default';
		else
			$skincolorset = $skincolorstat -> stat1;
		
		//var_dump( $skincolorset ); exit; 
		$class = "characterimage {$size}";
		
		
		echo "<div class='{$class}'>";
		
		/////////////////////////////////////////////////////////////////
		// Visualizzo l'eventuale item indossato sul capo (head)
		/////////////////////////////////////////////////////////////////
		
		// frame
		
		if (true)
		{			
			echo html::image ( array( 'src' => 'media/images/characters/aspect/frame.png'), array('class' => 'item_frame') );
		}
		
		// BACKGROUND
		
		if (true)
		{
			$image = Wardrobe_Model::get_correctimage( $this, 'background', $mode, 'background'	);
			echo html::image ( array( 'src' => $image), array('class' => 'item_background') );
		}
		
		// PROFILE
		
		if (true)
		{
			$image = Wardrobe_Model::get_correctimage( $this, 'profile', $mode, 'profile'	);
			echo html::image ( array( 'src' => $image), array('class' => 'item_profile') );
		}
		
		// HAIR
		
		if (true)
		{
			$hidehairsunderclothes = Character_Model::get_stat_d( $this -> id, 'hidehairsunderclothes');
			$image = Wardrobe_Model::get_correctimage( $this, 'hair', $mode, 'hair'	);
			if ( $hidehairsunderclothes -> loaded and $hidehairsunderclothes -> value == true )
				echo html::image ( array( 'src' => $image), array('class' => 'item_hair_hidden') );
			else
				echo html::image ( array( 'src' => $image), array('class' => 'item_hair') );
		}		
					
		// FACE
		
		if (true)
		{
			$image = Wardrobe_Model::get_correctimage( $this, 'face', $mode, 'face'	);
			echo html::image ( array( 'src' => $image), array('class' => 'item_face') );
		}
		
		// HEAD
				
		if (isset($equippeditems['head']))
		{
			$image = Wardrobe_Model::get_correctimage( $this, $equippeditems['head'], $mode);
			echo html::image ( array( 'src' => $image), array('class' => 'item_head') );
		}
		
		// NECK				
		
		if (isset($equippeditems['neck']))
		{
			$image = Wardrobe_Model::get_correctimage( $this, $equippeditems['neck'], $mode);
			echo html::image ( array( 'src' => $image), array('class' => 'item_neck') );
		}	
		
		// BODY
		if (isset($equippeditems['body']))
		{
			$image = Wardrobe_Model::get_correctimage( $this, $equippeditems['body'], $mode);
			echo html::image ( array( 'src' => $image), array('class' => 'item_body') );
		}
		else
		{
			
			if (isset($equippeditems['torso']))
			{
				$image = Wardrobe_Model::get_correctimage( $this, $equippeditems['torso'], $mode);
				echo html::image ( array( 'src' => $image), array('class' => 'item_torso') );
			}
			
			if (isset($equippeditems['legs']))
			{
				$image = Wardrobe_Model::get_correctimage( $this, $equippeditems['legs'], $mode);
				echo html::image ( array( 'src' => $image), array('class' => 'item_legs') );
			}
		}
		
		if (isset($equippeditems['torso']))
		{
			$image = Wardrobe_Model::get_correctimage( $this, $equippeditems['torso'], $mode);
			echo html::image ( array( 'src' => $image), array('class' => 'item_armor') );
		}

		if (isset($equippeditems['feet']))
		{
			$image = Wardrobe_Model::get_correctimage( $this, $equippeditems['feet'], $mode);
			echo html::image ( array( 'src' => $image), array('class' => 'item_feet') );
		}
		
		if (isset($equippeditems['shoulder']))
		{
			$image = Wardrobe_Model::get_correctimage( $this, $equippeditems['shoulder'], $mode);
			echo html::image ( array( 'src' => $image), array('class' => 'item_shoulder') );
		}		
		
		if (isset($equippeditems['left_wrist']))
		{
			$image = Wardrobe_Model::get_correctimage( $this, $equippeditems['left_wrist'], $mode);
			echo html::image ( array( 'src' => $image), array('class' => 'item_left_wrist') );
		}
		
		if (isset($equippeditems['left_hand']))
		{
			$image = Wardrobe_Model::get_correctimage( $this, $equippeditems['left_hand'], $mode);
			echo html::image ( array( 'src' => $image), array('class' => 'item_left_hand') );
		}
		
		if (isset($equippeditems['right_wrist']))
		{
			$image = Wardrobe_Model::get_correctimage( $this, $equippeditems['right_wrist'], $mode);
			echo html::image ( array( 'src' => $image), array('class' => 'item_right_wrist') );
		}
		
		if (isset($equippeditems['right_hand']))
		{
			$image = Wardrobe_Model::get_correctimage( $this, $equippeditems['right_hand'], $mode);
			echo html::image ( array( 'src' => $image), array('class' => 'item_right_hand') );
		}		
		
		
		
		if (isset($equippeditems['ring']))
		{
			$hideringunderclothes = Character_Model::get_stat_d( $this -> id, 'hideringunderclothes');
			$image = Wardrobe_Model::get_correctimage( $this, $equippeditems['ring'], $mode);
			if ( $hideringunderclothes -> loaded and $hideringunderclothes -> value == true )
				echo html::image ( array( 'src' => $image), array('class' => 'item_ring_hidden') );
			else
				echo html::image ( array( 'src' => $image), array('class' => 'item_ring') );
		}		
	
		// Chiusura del DIV dell'immagine profilo
		
		echo "</div>";	
	
	}
	
	/**
	* Calcola l' età  del char in giorni
	* @param mode: days ritorna l' età  in giorni altrimenti in secondi
	* @return età  in secondi o giorni
	*/	
	
	function get_age( $mode = 'days' )
	{
		
		if ( $mode == 'days' )
			return floor( ( time() - $this -> birthdate ) / ( 24*3600) );
		else
			return ( time() - $this -> birthdate );
	}
	
	/**
	* Calcola l' età  del char in giorni
	* Modello Statico
	* @param mode: days ritorna l' età  in giorni altrimenti in secondi
	* @return età  in secondi o giorni
	*/	
	
	static function get_age_s( $character_id, $mode = 'days' )
	{
		$character = ORM::factory('character', $character_id );		
		if ( $mode == 'days' )
			return floor( ( time() - $character -> birthdate ) / ( 24*3600) );
		else
			return ( time() - $character -> birthdate );
		
	}


	/**
	* Calcola i giorni passati dall' ultimo cambio di regno
	* @param none
	* @return int giorni passati o null
	*/

	function get_timesincelastresidencechange()
	{
		
		$changedkingdom = $this -> get_stats( 'changedkingdom' );		
		
		if ( is_null( $changedkingdom	) )
			return null;
		
		$days = intval( ( time() - $changedkingdom[0] -> value ) / ( 24 * 3600 ) );		 
		return $days;
	}
	
	/**
	* Modifica una statistica del char, ma 
	* è possibile specificare il charid destinatario
	* @param int id char
	* @param string statname
	* @param int value
	* @param string searchparam1
	* @param string searchparam2
	* @param boolean replacevalues value, stat1, stat2 otherwise are summed
	* @param string stat1 statistica 1
	* @param string stat2 statistica 2
	* @param string sparefield 1	
	* @param string sparefield 2	
	* @param string sparefield 3
	* @param string sparefield 4		
	* @return none
	*/
	
	static function modify_stat_d( 
		$character_id, 
		$name, 
		$delta=0, 
		$searchparam1 = null, 
		$searchparam2 = null, 
		$replace=false,
		$stat1=null, 
		$stat2=null, 
		$spare1=null, 
		$spare2=null, 
		$spare3=null, 
		$spare4=null
		)
	{
		
		kohana::log('debug', '------------ MODIFY STAT ------------');
		
		kohana::log('debug', 
			"-> Modifiying stat for char {$character_id}. Parameters: 
			statname: [{$name}] delta: [{$delta}] searchparam1: [{$searchparam1}] 
			searchparam2: [{$searchparam2}]
			stat1: [{$stat1}]
			stat2: [{$stat2}]
			spare1: [{$spare1}]
			spare2: [{$spare2}]
			spare3: [{$spare3}]
			spare4: [{$spare4}]
			replace: 	[{$replace}]");
			
		
		$stat = Character_Model::get_stat_d( $character_id, $name, $searchparam1, $searchparam2 );
							
		if ( !$stat -> loaded )		
		{
			
			kohana::log('debug', 'Stat is not existing, initializing it');
			
			$stat -> character_id = $character_id;
			$stat -> name = $name ;
			$stat -> param1 = $searchparam1 ;
			$stat -> param2 = $searchparam2 ;
			$stat -> value = 0;
			$stat -> stat1 = 0;
			$stat -> stat2 = 0;
			$stat -> spare1 = $spare1 ;
			$stat -> spare2 = $spare2 ;
			$stat -> spare3 = $spare3 ;
			$stat -> spare4 = $spare4 ;
		}
		else
		{
			kohana::log('debug', 'Stat is already existing: ' .
				' Pre:  statname: ' . $name . ' value: ' . $stat -> value . 
				' stat1: ' . $stat -> stat1 . ' stat2: ' . $stat -> stat2 . 
				' spare1: ' . $stat -> spare1 .
				' spare2: ' . $stat -> spare2 . 
				' spare3: ' . $stat -> spare3 .
				' spare4: ' . $stat -> spare4
				);		
		}
		
		if ( $replace )
		{
			kohana::log('debug', '-> Replacing values...');
			$stat -> value = $delta;
			$stat -> stat1 = $stat1;
			$stat -> stat2 = $stat2;
			$stat -> spare1 = $spare1;
			$stat -> spare2 = $spare2;
			$stat -> spare3 = $spare3;
			$stat -> spare4 = $spare4;
		}
		else
		{
			kohana::log('debug', '-> Adding values...');
			$stat -> value += $delta ;		
			$stat -> stat1 += $stat1;
			$stat -> stat2 += $stat2;		
			$stat -> spare1 += $spare1;
			$stat -> spare2 += $spare2;
			$stat -> spare3 += $spare3;
			$stat -> spare4 += $spare4;
		}
		
		kohana::log('debug', 
			"-> Modified stat for char {$character_id}. Parameters: 
			statname: [{$name}] 
			value: [{$stat -> value}] 
			searchparam1: [{$stat -> param1}] 
			searchparam2: [{$stat -> param2}]
			stat1: [{$stat -> stat1}]
			stat2: [{$stat -> stat2}]
			spare1: [{$stat -> spare1}]
			spare2: [{$stat -> spare2}]
			spare3: [{$stat -> spare3}]
			spare4: [{$stat -> spare4}]");
						
		Achievement_Model::compute_achievement( 'stat_' . $name, $stat -> value, $character_id, $stat -> stat1 ); 
		
		My_Cache_Model::delete ( '-charinfo_' . $character_id . '_stat_' . $stat -> name 
			.'-'.$searchparam1.'-'.$searchparam2); 
		
		$stat -> save();		
		
		kohana::log('debug', '------------ MODIFY STAT END ------------');
		
	}
	
	/**
	* Modifica una statistica del char
	* @param string nome statistica
	* @param int delta da applicare al valore statistica
	* @param string param1 parametro di ricerca
	* @param stringp param2 ulteriore parametro di ricerca
	* @replace boolean se true sovrascrive i campi value, stat* e spare* invece che sommarli
	* @param string stat1 statistica 1
	* @param string stat2 statistica 2
	* @spare1 string sparefield 1	
	* @spare1 string sparefield 2	
	* @spare1 string sparefield 3	
	* @spare1 string sparefield 4			
	* @return none
	*/
	
	function modify_stat( 
		$name, 
		$delta = 0, 
		$searchparam1 = null, 
		$searchparam2 = null, 
		$replace = false,
		$stat1 = null, 
		$stat2 = null, 
		$spare1 = null, 
		$spare2 = null, 
		$spare3 = null, 
		$spare4 = null
		)
	{
		
		kohana::log('debug', 'Modifiying stat for ' . $this -> name . 
			' statname: ' . $name . 
			' delta: ' . $delta  . 
			' searchparam1: ' . $searchparam1 . 
			' searchparam2: ' . $searchparam2 . 
			' stat1: ' . $stat1 . 
			' stat2: ' . $stat2 . 
			' spare1: ' . $spare1 . 
			' spare2: ' . $spare2 . 
			' spare3: ' . $spare3 . 
			' spare4: ' . $spare4 . 
			' replace: ' . $replace ); 
		
		$stat = Character_Model::get_stat_d( $this -> id, $name, $searchparam1, $searchparam2 );
							
		if ( !$stat -> loaded )		
		{
			
			kohana::log('debug', 'Stat is not existing, initializing it');
			
			$stat -> character_id = $this -> id;
			$stat -> name = $name ;
			$stat -> param1 = $searchparam1 ;
			$stat -> param2 = $searchparam2 ;
			$stat -> value = 0;
			$stat -> stat1 = 0;
			$stat -> stat2 = 0;
			$stat -> spare1 = $spare1 ;
			$stat -> spare2 = $spare2 ;
			$stat -> spare3 = $spare3 ;
			$stat -> spare4 = $spare4 ;
		}
		else
		{
			kohana::log('debug', 'Stat is already existing: ' . $this -> name . 
				' Pre:  statname: ' . $name . ' value: ' . $stat -> value . 
				' stat1: ' . $stat -> stat1 . ' stat2: ' . $stat -> stat2 . 
				' spare1: ' . $stat -> spare1 .
				' spare2: ' . $stat -> spare2 . 
				' spare3: ' . $stat -> spare3 .
				' spare4: ' . $stat -> spare4
				);		
		}
		
		if ( $replace )
		{
			$stat -> value = $delta;
			$stat -> stat1 = $stat1;
			$stat -> stat2 = $stat2;
			$stat -> spare1 = $spare1;
			$stat -> spare2 = $spare2;
			$stat -> spare3 = $spare3;
			$stat -> spare4 = $spare4;
		}
		else
		{
			$stat -> value += $delta ;		
			$stat -> stat1 += $stat1;
			$stat -> stat2 += $stat2;		
			$stat -> spare1 = $spare1;
			$stat -> spare2 = $spare2;
			$stat -> spare3 = $spare3;
			$stat -> spare4 = $spare4;
		}
				
		kohana::log('debug', $this -> name . ' Post: statname: ' . $name . ' value: ' . $stat -> value . 
			' stat1: ' . $stat -> stat1 . 
			' stat2: ' . $stat -> stat2 . 
			' spare1: ' . $stat -> spare1 .
			' spare2: ' . $stat -> spare2 .
			' spare3: ' . $stat -> spare3 .
			' spare4: ' . $stat -> spare4
			
		);

		Achievement_Model::compute_achievement( 'stat_' . $name, $stat -> value, $this -> id, $stat -> stat1 ); 
		
		My_Cache_Model::delete ( '-charinfo_' . $stat -> character_id . '_stat_' . $stat -> name 
			.'-'.$searchparam1.'-'.$searchparam2); 
		
		$stat -> save();		
		
	}
	
	/**
	* Prende statistica dalla cache
	* @param int $character_id ID Char
	* @param str $name Nome della Statistica
	* @param str $searchparam Parametro Ricerca
	* @param str $searchaparam2 Parametro Ricerca
	* @return Character_Stat_Model (se non esiste, la proprietà  loaded è false)
	*/

	
	function get_stat_from_cache($character_id, $name, $searchparam1 = null, $searchparam2 = null)
	{
		
		$cachetag = '-charinfo_' . $character_id . '_stat_' . $name .'-'.$searchparam1.'-'.$searchparam2 ;			
		
		//kohana::log('debug', "--- get_stat_from_cache ---");		
		
		$stat = My_Cache_Model::get( $cachetag );
		
		if ( is_null( $stat ) )
		{			
			$stat = Character_Model::get_stat_d( $character_id, $name, $searchparam1, $searchparam2 );
			My_Cache_Model::set($cachetag, $stat);
		}
	
		return $stat;
		
	}

	
	/**
	* Carica una statistica del personaggio
	* @param  int $char_id ID Character
	* @param  string $name stat name
	* @param  string $param1 searchparam1
	* @param  string $param2 searchparam2
	* @return Character_Stat_Model (se non esiste, la proprietà  loaded è false)
	*/
	
	static function get_stat_d( $char_id, $name, $param1 = null, $param2 = null )
	{
		//kohana::log('debug', "--- get_stat_d {$name} for char: {$char_id} ---");
		//var_dump( $name, $char_id, $param1); exit; 
		if ( is_null( $param1 ) and is_null( $param2)  )
			$stat = ORM::factory( 'character_stat' ) 
			->where( array( 
				'character_id' => $char_id, 
				'name' => $name )) -> find();					
		
		if ( !is_null( $param1 ) and is_null( $param2)  )
			$stat = ORM::factory( 'character_stat' ) 
			->where( array( 
				'character_id' => $char_id,
				'param1' => $param1, 
				'name' => $name )) -> find();		
		
		if ( is_null( $param1 ) and !is_null( $param2)  )
			$stat = ORM::factory( 'character_stat' ) 
			->where( array( 
				'character_id' => $char_id,
				'param2' => $param2, 
				'name' => $name )) -> find();		
				
		if ( !is_null( $param1 ) and !is_null( $param2)  )
			$stat = ORM::factory( 'character_stat' ) 
			->where( array( 
				'character_id' => $char_id,
				'param1' => $param1, 
				'param2' => $param2, 
				'name' => $name )) -> find();		
		
		//var_dump( $stat ); exit; 
		
		return $stat; 
	
	}
	
	public function get_stat( $name, $param1 = null, $param2 = null )
	{
		
		if ( is_null( $param1 ) and is_null( $param2)  )
			$stat = ORM::factory( 'character_stat' ) 
			->where( array( 
				'character_id' => $this ->id, 
				'name' => $name )) -> find();			
		
		if ( !is_null( $param1 ) and is_null( $param2)  )
			$stat = ORM::factory( 'character_stat' ) 
			->where( array( 
				'character_id' => $this ->id,
				'param1' => $param1, 
				'name' => $name )) -> find();		
		
		if ( is_null( $param1 ) and !is_null( $param2)  )
			$stat = ORM::factory( 'character_stat' ) 
			->where( array( 
				'character_id' => $char_id,
				'param2' => $param2, 
				'name' => $name )) -> find();		
				
		if ( !is_null( $param1 ) and !is_null( $param2)  )
			$stat = ORM::factory( 'character_stat' ) 
			->where( array( 
				'character_id' => $this ->id,
				'param1' => $param1, 
				'param2' => $param2, 
				'name' => $name )) -> find();		
		
		return $stat;
	
	}
	
	/**
	* Carica il timestamp dell' ultima azione
	* @param id char_id
	* @return int time
	*/
	
	function get_lastactiontime_d( $char_id )
	{
		$db = Database::instance();
		
		$cachetag = '-charinfo_' . $char_id . '_lastactiontime' ;
		//kohana::log('debug', "-> Getting $cachetag from CACHE..." ); 
		$lastactiontime = null;
		$lastactiontime = My_Cache_Model::get( $cachetag );
		
		if ( is_null( $lastactiontime ) )
		{
			//kohana::log('debug', "-> Getting $cachetag from DB."); 
			$res = $db -> query( "select lastactiontime from characters where id = " . $char_id );
			$lastactiontime = $res[0] -> lastactiontime; 
			My_Cache_Model::set( $cachetag, $lastactiontime );
		}
		
		return $lastactiontime;
	
	}
	
	/**
	* Ritorna le stat di un char
	* @param str $name nome statistica
	* @param str $param1 se passato, è un criterio di ricerca
	* @param str $param2 se passato, è un criterio di ricerca
	* @return array $stats, null se non trovata
	*/
	
	function get_stats( $name, $param1 = null, $param2 = null ) 
	{
		
		//kohana::log('debug','Getting stats for char: ' . $this -> id );
		//kohana::log('debug',"name = $name, param1 = $param1, param2 = $param2" );
		
		if ( is_null( $param1) and is_null( $param2 )  )
			$stats = ORM::factory( 'character_stat' ) 
				->where( array ( 
					'character_id' => $this->id, 
					'name' => $name)) -> find_all ()  ;
					
		if ( !is_null( $param1) and is_null( $param2 )  )
			$stats = ORM::factory( 'character_stat' ) 
				->where( array ( 
					'character_id' => $this->id, 
					'name' => $name,
					'param1' => $param1
					)) -> find_all () ;
					
		if ( is_null( $param1) and !is_null( $param2 )  )
			$stats = ORM::factory( 'character_stat' ) 
				->where( array ( 
					'character_id' => $this->id, 
					'name' => $name,
					'param2' => $param2
					)) -> find_all () ;
		if ( !is_null( $param1) and !is_null( $param2 )  )
			$stats = ORM::factory( 'character_stat' ) 
				->where( array ( 
					'character_id' => $this->id, 
					'name' => $name,
					'param1' => $param1,
					'param2' => $param2
					)) -> find_all () ;			
					
					
		if ( $stats -> count() > 0 ) 
			return $stats -> as_array();
			
		if ( $stats -> count() == 0 ) 
			return null;
		
	}
	
	/**
	* Ritorna le stat di un char
	* @param int $character_id ID Personaggio
	* @param str $name nome statistica
	* @param str $param1 se passato, è un criterio di ricerca
	* @param str $param2 se passato, è un criterio di ricerca
	* @return array $stats, null se non trovata
	*/
	
	function get_stats_d( $character_id, $name, $param1 = null, $param2 = null ) 
	{
		
		//kohana::log('debug','Getting stats for char: ' . $this -> id );
		//kohana::log('debug',"name = $name, param1 = $param1, param2 = $param2" );
		
		if ( is_null( $param1) and is_null( $param2 )  )
			$stats = ORM::factory( 'character_stat' ) 
				->where( array ( 
					'character_id' => $character_id, 
					'name' => $name)) -> find_all ()  ;
					
		if ( !is_null( $param1) and is_null( $param2 )  )
			$stats = ORM::factory( 'character_stat' ) 
				->where( array ( 
					'character_id' => $character_id, 
					'name' => $name,
					'param1' => $param1
					)) -> find_all () ;
					
		if ( is_null( $param1) and !is_null( $param2 )  )
			$stats = ORM::factory( 'character_stat' ) 
				->where( array ( 
					'character_id' => $character_id, 
					'name' => $name,
					'param2' => $param2
					)) -> find_all () ;
					
		if ( !is_null( $param1) and !is_null( $param2 )  )
			$stats = ORM::factory( 'character_stat' ) 
				->where( array ( 
					'character_id' => $character_id, 
					'name' => $name,
					'param1' => $param1,
					'param2' => $param2
					)) -> find_all () ;			

		if ( $stats -> count() > 0 ) 
			return $stats -> as_array();
			
		if ( $stats -> count() == 0 ) 
			return null;
		
	}
	
	/**
	* Returns attribute modifier value
	* @param string $attribute attribute name (ex: dex)
	* @param boolean $bonus_flag consider bonuses?
	* @return int $modifier modifier value (ex: -3 or +3)
	*/
	
	function get_attribute_modifier( $attribute, $bonus_flag = true ) 
	{
	
		$modifiers[0] = array( 
			'value' => 0, 
			'reason' => '' 
		);
		
		$i=0;
		$diseases = $this -> get_diseases();
		
		switch ( $attribute )
		{
			case 'dex':
				
				// check bonuses
				
				$stat = Character_Model::get_stat_d($this -> id, 'dexboost');
				
				if ( $stat -> loaded and $stat -> stat1 > time() )
				{
					$modifiers[$i]['value'] =  + 3;
					$modifiers[$i]['reason'][] = kohana::lang('character.modifierelixirofdex');
					$i++;
				}
				
				// check diseases
				
				foreach ( (array) $diseases as $disease )
				{
					
					$class = "Disease_" . ucfirst( $disease -> param1 ) . "_Model";
					$diseaseclass = new $class;
					$modifiers[$i]['value'] = $diseaseclass -> get_dexmalus();
					$modifiers[$i]['reason'] = kohana::lang('character.disease_' . $disease -> param1 );
					$i++;
				}
				
				break;
				
			case 'str':
				
				// check bonuses
				
				$stat = Character_Model::get_stat_d($this -> id, 'strboost');
				
				if ( $stat -> loaded and $stat -> stat1 > time() )
				{
					$modifiers[$i]['value'] = +3;
					$modifiers[$i]['reason'] = kohana::lang('character.modifierelixirofstr');
					$i++;
				}
				
				// check diseases
				
				foreach ( (array) $diseases as $disease )
				{
					$class = "Disease_" . ucfirst( $disease -> param1 ) . "_Model";
					$diseaseclass = new $class;
					$modifiers[$i]['value'] = $diseaseclass -> get_strmalus();
					$modifiers[$i]['reason'] = kohana::lang('character.disease_' . $disease -> param1 );
					$i++;
				}
				
				break;
			case 'intel':
				
				// check bonuses
				
				$stat = Character_Model::get_stat_d($this -> id, 'intelboost');
				
				if ( $stat -> loaded and $stat -> stat1 > time() )
				{
					$modifiers[$i]['value'] =  +3;
					$modifiers[$i]['reason'] = kohana::lang('character.modifierelixirofintel');
					$i++;
				}
				
				// check diseases
				
				foreach ( (array) $diseases as $disease )
				{
					$class = "Disease_" . ucfirst( $disease -> param1 ) . "_Model";
					$diseaseclass = new $class;
					$modifiers[$i]['value'] = $diseaseclass -> get_intelmalus();
					$modifiers[$i]['reason'] = kohana::lang('character.disease_' . $disease -> param1 );
					$i++;
				}
				break;
			case 'car':
				
				/////////////////////////////////////////
				// Controllo modificatore vestiti
				/////////////////////////////////////////
				
				// Imposto il malus a -6 come se il char fosse completamente nudo
				
				$modifiers[0]['value'] = -6;
				
				// Query per il conteggio del bonus carisma in base ai vestiti indossati				
				
				$equipment = Character_Model::get_equipment( $this -> id );			
										
				foreach ($equipment as $e )
				{
					//kohana::log('debug', "Adding modifier: " . $e -> car_modifier . " because of item: " . $e -> tag ); 
					$modifiers[0]['value'] += $e -> car_modifier;
				
				}
				
				$modifiers[0]['reason'] = kohana::lang('character.modifierclothes');
				$i++;
				
				/////////////////////////////////////////
				// Controllo modificatore malattie
				/////////////////////////////////////////
				
				foreach ( (array) $diseases as $disease )
				{
					$class = "Disease_" . ucfirst( $disease -> param1 ) . "_Model";
					$diseaseclass = new $class;
					$modifiers[$i]['value'] = $diseaseclass -> get_carmalus();
					$modifiers[$i]['reason'] = kohana::lang('character.disease_' . $disease -> param1 );
					$i++;
				}
				
				
				break;
			case 'cost':
				
				// check bonuses
				
				$stat = Character_Model::get_stat_d($this -> id, 'costboost');
				
				if ( $stat -> loaded and $stat -> stat1 > time() )
				{
					$modifiers[$i]['value'] =  + 3;
					$modifiers[$i]['reason'] = kohana::lang('character.modifierelixirofcost');
					$i++;
				}
			
				// check disease

				foreach ( (array) $diseases as $disease )
				{
					$class = "Disease_" . ucfirst( $disease -> param1 ) . "_Model";
					$diseaseclass = new $class;
					$modifiers[$i]['value'] = $diseaseclass -> get_costmalus();
					$modifiers[$i]['reason'] = kohana::lang('character.disease_' . $disease -> param1 );
					$i++;
				}
				break;
			default: break;	
		}
		
		return $modifiers ;
	}
	
	
	/**
	* Ritorna il valore dell' attributo del char
	* @param string nome attributo
	* @param boolean tenere conto dei bonus?
	* @return valore
	*/
		
	function get_attribute( $attribute, $bonus_flag = true) 
	{
	
		switch ( $attribute )
		{
			case 'str': $value = $this -> str; break;
			case 'dex':	$value = $this -> dex ; break;
			case 'intel': $value = $this -> intel ; break;
			case 'car':	$value = $this -> car; break; 
			case 'cost': $value = $this -> cost ; break;
			default: $value = null;  break;
		}
		
		//kohana::log( 'debug', '-> Get Attribute: before modifiers: attribute ' . $attribute . ' is: ' . $value ); 
		
		if ( $bonus_flag )
		{
			$modifiers = $this -> get_attribute_modifier ( $attribute );
			
			foreach ( $modifiers as $modifier )
				$value += $modifier['value'];
		}
		
		//kohana::log( 'debug',  '-> Get Attribute: after modifiers: attribute ' . $attribute . ' is: ' . $value ); 
		
		if ( $value > Character_Model::get_attributelimit() )
			$value = Character_Model::get_attributelimit();
		if ( $value < 1 )
			$value = 1;
			
		return $value;
	}

	/**
	* Setta il valore dell' attributo del char
	* @param string nome attributo    
	* @param int delta
	* @return none
	*/
	
	function set_attribute( $name, $delta )
	{
	
		$endvalue = $this -> get_attribute( $name, false ) + $delta ; 
		
		if ( $endvalue < 1 ) $endvalue = 1;
		if ( $endvalue > 20 ) $endvalue = 20;
		
		/*
		if ( $endvalue > Character_Model::get_attributelimit() ) 
			$endvalue = Character_Model::get_attributelimit();
		*/
		
		kohana::log('debug', "-> Setting attribute {$name} to: {$endvalue}");							
		
		switch ( $name )
		{
			case 'str': $this->str = $endvalue ;break;
			case 'dex': $this->dex = $endvalue ;break;
			case 'intel': $this->intel = $endvalue ;break;
			case 'car': $this->car = $endvalue ;break;
			case 'cost': $this->cost = $endvalue ;break;
			default: $value = null;  break;
		}
		
		return;
	}	
	
	/**
	* Aggiunge un bonus o un malus
	* @param bonus tag bonus
	* @param param1,2 parametri
	* @param durata del bonus in secondi
	* @return valore
	*/
	
	function add_bonus( $name, $param1=null, $param2=null, $duration )
	{	
			$bonus = new character_premiumbonus_Model();
			$bonus -> user_id	 = $this->user->id;
			$bonus -> character_id = $this->id;
			$bonus -> bonus = $name;
			$bonus -> type = 'normal'; // non premium
			$bonus -> param1 = $param1;
			$bonus -> param2 = $param2;
			$bonus -> starttime = time();
			$bonus -> endtime = $bonus->starttime + $duration;
			$bonus -> doubloons = 0;	
			$bonus -> save();
			
			
			switch ( $name ) 
			{
				case 'strength_boost' :	Character_Event_Model::addrecord ( $this -> id, 'normal', '__events.' . $name . '_event;', 'evidence' ) ; break;
				default: break;
			}	
			
			return;
	}

	// Cerca la lista dei gruppi a cui appartiene il char
	public function get_my_groups()
	{
		$db = Database::instance();
		
		// Selezioni i gruppi di cui il char è il proprietario
		// o a cui appartiene come membro approvato (non pendente)
		$sql = "( (select groups.* 
		          from groups left join group_characters 
		          on groups.id = group_characters.group_id 
		          where (groups.character_id = " . $this -> id .")))
		          UNION
		          (select groups.* 
		          from groups left join group_characters 
		          on groups.id = group_characters.group_id 
		          where (group_characters.character_id = " . $this->id ." and group_characters.joined = 1))"; 				
		$mygroups = $db -> query( $sql );

		return $mygroups;
	}
		
	/**
	* Trova le regioni controllate dal char
	* @param none
	* @return obj $regions ORM_Collection di regioni o null
	*/
	
	public function get_controlledregions()
	{
		
		$db = Database::instance();		
		$role = $this -> get_current_role();		
		
		if ( is_null ( $role ) ) 
			return null;
		
		// se è un re le controlla tutte.
		
		if ( $role -> tag == 'king' )			
			$regions = ORM::factory('region') -> where ( 'kingdom_id', $this -> region -> kingdom -> id ) -> find_all(); 		
		
		// se è un vassallo controlla solo le regioni con native village
		// figli del castello
		
		elseif ( $role -> tag == 'vassal' )
		{				
			$castle = $this -> region -> get_structure('castle'); 			
			kohana::log('debug', "-> This char controls the castle: {$castle -> id}.");
			
			$res = $db -> query ( 
				"select s.region_id, r.name
				from structures s, structure_types st, regions r 
				where s.structure_type_id = st.id
				and   s.region_id = r.id 
				and   st.parenttype = 'nativevillage' 
				and   s.parent_structure_id = " . $castle -> id ) -> as_array ();
			
			foreach ( (array) $res as $r ) 
			{
				kohana::log('debug', "-> Controlled regions: adding {$r -> name}");
				$x[] = $r -> region_id;						
			}
			
			// Plus, add role region.			
			kohana::log('debug', "-> Controlled regions: adding OWN Region: {$role -> region -> name}");
			
			$x[] = $role -> region_id;
			$regions = ORM::factory('region') -> in ( 'id', $x ) -> find_all();			
		}			
		else
		{
			$regions = ORM::factory('region' ) -> where( 'id' , $this -> region -> id ) -> find_all();
		}
		
		return $regions;
		
	}	
		
	/*
	* torna la regione di nascita
	*/
	
	public function get_birth_region()
	{
		$birthregion = ORM::factory('region', $this -> birth_region_id); 
		if ( ! $birthregion -> loaded )
			$birthregionname = kohana::lang('global.unknown' ); 
		else
			$birthregionname = kohana::lang( $birthregion -> name ); 
		return $birthregionname;
	}
	
	/*
	* torna la regione di morte
	*/
	
	public function get_death_region()
	{
		$deathregion = ORM::factory('region', $this -> death_region_id); 
		if ( ! $deathregion -> loaded )
			$deathregionname = kohana::lang('global.unknown' ); 
		else
			$deathregionname = kohana::lang( $deathregion -> name ); 
		return $deathregionname;
	}
	
	/**
	* determina se il char è in cura (o sta curando)
	* @param char_id id char
	* @return true o false
	*/
	
	function is_beingcured( $char_id )
	{	
		return Character_Model::get_pending_action_d ( $char_id, 'cure' ); 		
	}
	
	/**
	* Determina se il char è malato
	* @param none
	* @return true o false
	*/
	
	function is_sick( )
	{	
		$count = 0;
		$diseases = $this -> get_diseases();
		
		return ( count($diseases) > 0 ) ? true : false ; 
	}
	
	/**
	* determina se il char sta controllando le areee
	* @param char_id id char
	* @return true o false
	*/
	
	function is_watchingarea( $char_id )
	{
		return Character_Model::get_pending_action_d ( $char_id, 'watcharea' ); 		
	}
	
	
	/**
	* determina se il char è imprigionato
	* @param char_id id char
	* @return true o false
	*/
	
	function is_imprisoned( $char_id )
	{	
		kohana::log('debug', '--- is_imprisoned ---');
		$stat = Character_Model::get_stat_from_cache( $char_id, 'servejailtime');
		//kohana::log('debug', kohana::debug($stat));
		if (!is_null($stat) and $stat -> stat2 > time())
		{
			kohana::log('debug', '-> Char should be imprisoned until: ' . date("d-m-Y H:i:s", $stat -> stat2));	
			return true;
		}
		
		return false;
				
	}
	
	/**
	* Torna se il char sta combattendo
	* Questo è uno stato speciale e va settato specificatamente in char.status
	* dalle singole azioni
	*/
	
	function is_fighting( $char_id )
	{
		
		$fighting = Character_Model::get_stat_from_cache( $char_id, 'fighting' ); 
		
		if ( $fighting -> loaded and $fighting -> value == true )
			return true;
		else
			return false;
		
	}

	function is_resting( $char_id )
	{
		return 
		(
			Character_Model::get_pending_action_d( $char_id, 'rest' ) 
				or
			Character_Model::get_pending_action_d( $char_id, 'resttavern' )
		);
	}
	
	/**
	* Determina se il char sta meditando
	* @param char_id id char
	* @return true o false
	*/
	
	static function is_meditating( $char_id )
	{	
		return Character_Model::get_pending_action_d( $char_id, 'retire' ); 	
	}
				
	/**
	* torna se il char sta viaggiando, versione CACHABILE
	* @param char_id ID del char
	* ritorna true o false
	*/	
	
	function is_traveling( $char_id )
	{
		
		return ( 
			Character_Model::get_pending_action_d ( $char_id, 'move' ) or 
			Character_Model::get_pending_action_d ( $char_id, 'sail' ) or 
			Character_Model::get_pending_action_d ( $char_id, 'arrest' ) or
			Character_Model::get_pending_action_d ( $char_id, 'imprison' )  
			);
	}
	
	/**
	* torna se il char sta recuperando
	*/
	
	function is_recovering( $char_id )
	{		
		return Character_Model::get_pending_action_d( $char_id, 'recovering' );
	}
		
	
	function is_restrained( $char_id )
	{
		$rset = ORM::factory('character_action' ) -> 
			where ( 
				array( 
					'action' => 'restrain',
					'status' => 'running',
					'character_id' => $char_id )) -> find();
		
		return ($rset -> loaded);
	}
		
	/**
	* Controlla se il char è nel regno
	* @param kingdom oggetto kingdom
	* @return boolean
	*/
	
	function is_inkingdom( $kingdom )
	{
		if ( Character_Model::is_traveling( $this -> id ) )
		{
			$action = Character_Model::get_pending_action_d( $this -> id, 'move' );			
			$position_id = $action -> param2;
		}
		else
			$position_id = $this -> position_id ;
		
		foreach ( $kingdom -> regions as $region )
		{
			if ( $region -> id == $this -> position_id )
				return true;
		}
		
		return false;
		
	}
	
	/**
	* torna lo stato del char
	* @param nessuno
	* @return string stato o null
	**/
	
	function get_status ( $character_id )
	{
			
		if ( Character_Model::is_resting( $this -> id ) )	return 'resting'; 
		if ( Character_Model::is_fighting( $this -> id ) )	return 'fighting'; 
		if ( Character_Model::is_recovering( $character_id ) )	return 'recovering';
		if ( Character_Model::is_meditating( $character_id ) )	return 'meditating';		
		if ( Character_Model::is_imprisoned( $character_id ) )	return 'imprisoned';		
		if ( Character_Model::is_restrained( $character_id ) )	return 'restrained';				
		if ( Character_Model::is_traveling( $this -> id ) )	return 'traveling'; 		

		return null;
	}	
	
	/**
	* Verifica se il char è nudo o meno. 
	* Nudo: o non ha le scarpe o ha il torso o le gambe nude.
	* @param int $char_id ID Character
	* @return false o true
	**/
	
	public function is_naked( $char_id )
	{
		
		$equipment = Character_Model::get_equipment( $char_id );
		
		// ha qualche parte nuda?
		
		if ( 
				( 
					!isset($equipment['torso']) and 
					!isset($equipment['body'])
				) 
				or ( !isset($equipment['body']) and !isset($equipment['legs']) ) 				
			)
			return true;
			
		return false;
	}
	
	/**
	* modifica il faithlevel	
	* @param delta delta
	* @return none
	**/
	
	
	function modify_faithlevel( $delta, $replace = false )
	{
		
		$stat = $this -> get_stat( 'faithlevel' );
		
		
		if ( ! $stat -> loaded )
			$currentfl = 0;
		else
			$currentfl = $stat -> value ;
		
		kohana::log('debug', '-> Actual FL: ' . $currentfl ); 
		kohana::log('debug', '-> Delta: ' . $delta ); 

		if ( $replace )
			$newfl = $delta ;
		else
			$newfl = $currentfl + $delta;		
		
		if ( $newfl >= 100 ) $newfl = 100;
		
		if ( $newfl < 0 )	$newfl = 0;
		
		kohana::log('debug', '-> New FL: ' . $newfl ); 
				
		$this -> modify_stat( 
			'faithlevel', 
			$newfl, 
			null, 
			null,
			true,
			null,
			null,
			null,
			null,
			null,
			null
		);
			
	}
	
	/**
	* Load Char Rankings
	* @param int character_id
	* @return array rankings
	*/
	
	static function get_allrankings()
	{	
		
		$cachetag = '-allrankings';	
		$data = My_Cache_Model::get( $cachetag );		
	
		if ( is_null ( $data ) )
		{	

			$res = Database::instance() -> query ("
			select * from stats_globals
			where   target = 'player'
			and extractiontime > " . (time() - ( 24 * 3600 )) . "
			order by target, stats_id, position asc" ) -> as_array();
			
			foreach ( $res as $row )
				$data[$row->target][$row -> stats_id][$row -> type] = $row;
			
			My_Cache_Model::set( $cachetag, $data );
			//var_dump($data); exit;
		}
		
		return $data;
		
	}
	
	static function get_rankings($character_id)
	{	
		$allrankings = Character_Model::get_allrankings();
		if(isset($allrankings['player'][$character_id]))
			return $allrankings['player'][$character_id];
		else	
			return array();
	}
		
	/**
	* Restituisce il numero delle strutture possedute dal char
	* @param parenttype parenttype struttura
	* @return   int      numero di strutture
	**/
	function count_my_structures ( $parenttype )
	{
		$tot = 0;
		
		foreach ( $this -> structure as $st )
		{
			if ( $st -> structure_type -> parenttype == $parenttype and $st -> locked == false )
			$tot++;
		}
		
		return $tot;
	}
	
	/**
	* crea public profile link
	* @param character_id id char
	* @param character_name name char
	* @return link che rimanda al profilo pubblico oppure NULL
	*/
	
	function create_publicprofilelink( $character_id = null, $character_name = null )
	{
		
		if ( !is_null( $character_id ) and !is_null( $character_name) )
			return html::anchor('character/publicprofile/' . $character_id, $character_name, 
				array( 'target' => '_blank' ) ); 
		else
		{
			if ( 
				(!is_null( $character_id ) and is_null( $character_name )) or
				(!is_null( $character_id ) and !is_null( $character_name ))
			)
				$character = ORM::factory('character', $character_id );
			
			if ( !is_null( $character_name ) and is_null( $character_id ) )
				$character = ORM::factory('character') 
					-> where ( 'name', $character_name ) -> find();
			
			if( is_null( $character_name ) and is_null ( $character_id ))
				return null;
			
			// char is dead?
			
			if ( !$character -> loaded )
			{
				
				if ( !is_null( $character_id ) and is_null( $character_name ) )
					$character = ORM::factory('ar_character', $character_id );
				
				if ( !is_null( $character_name ) and is_null( $character_id ) )
					$character = ORM::factory('ar_character') -> 
						where ( 'name', $character_name ) -> find();
				
				// char not existing in archive...
								
				
				if (!is_null($character_name))
					return $character_name;
				else
					return "Unknown";
				
			}
		
			return html::anchor('character/publicprofile/' . $character -> id, $character -> name, 
				array( 'target' => '_blank' ) ); 
		}
		
	}
	
	/**
	* Crea codice html per avatar
	* @param character_id id char
	* @param size dimensione ('s', 'l')
	* @param  class eventuale classe css
	* @return codice html
	*/
	
	function display_avatar( $character_id, $size = 'l', $class = '' )
	{
		$imagelarge = $character_id . '_l.jpg';
		$imagesmall = $character_id . '_s.jpg';
		$path = 'media/images/characters/';
		
		if ( $size == 'l' )
			$file = 'media/images/characters/' . $imagelarge;
		else
			$file = 'media/images/characters/' . $imagesmall;
		
		if (file_exists($file))
			return html::image( 
				$file . "?r=" . time(), 
				array(
				'class' => $class,
				), 
				false);
		else 
			return html::image('media/images/characters/aspect/noimage_' . $size . ".jpg", array('class' => $class), false);
	
	}
	
	/**
	* Torna numero di messaggi email non letti
	* @param none
	* @return numero di messaggi non letti
	*/
	
	public function get_unreadmessages_d( $char_id )
	{
		
		
		$cachetag = '-charinfo_' . $char_id . '_unreadmessages' ;
		//kohana::log('debug', "-> Getting $cachetag from CACHE..." ); 
		$unreadmessages = My_Cache_Model::get( $cachetag );		
	
		if ( is_null ( $unreadmessages ) )
		{			
			//kohana::log('debug', "-> Getting $cachetag from CACHE..." ); 
			
			$unreadmessages = count(Message_Model::get_unreadmessages( $char_id ) );
			My_Cache_Model::set( $cachetag, $unreadmessages );
			
		}
		
		return $unreadmessages;
	
	}
	
	public function get_unreadmessages()
	{
		
		
		$cachetag = '-charinfo_' . $this -> id . '_unreadmessages' ;
		//kohana::log('debug', "-> Getting $cachetag from CACHE..." ); 
		$unreadmessages = My_Cache_Model::get( $cachetag );		
	
		if ( is_null ( $unreadmessages ) )
		{			
			//kohana::log('debug', "-> Getting $cachetag from CACHE..." ); 
			$unreadmessages =  ORM::factory( "message" ) -> 
				where ( array ( 
					'tochar_id' =>  $this -> id,
					'isread' =>  false )) -> count_all();
			
			My_Cache_Model::set( $cachetag, $unreadmessages );
			
		}
		
		return $unreadmessages;
	
	}
	
	/**
	* Torna numero di eventi personali non letti
	* @param none
	* @return numero di eventi non letti
	*/
	
	public function get_unreadevents_d( $char_id )
	{
	
		
		$cachetag = '-charinfo_' . $char_id . '_unreadevents' ;
		//kohana::log('debug', "-> Getting $cachetag from CACHE..." ); 
		$unreadevents = My_Cache_Model::get( $cachetag );		
		
		if ( is_null ( $unreadevents ) )
		{
			//kohana::log('debug', "-> Getting $cachetag from DB..." ); 
			$data = Character_Model::get_stat_d( $char_id, 'lastreadevent' ); 
			
			if ( is_null( $data ) )
				$date = 0;
			else
				$date = $data -> value;

			$unreadevents =  ORM::factory( "character_event" ) -> 
				where ( array ( 
					'type' => 'normal', 
					'character_id' =>  $char_id, 
					'timestamp>' => $date) ) -> count_all();							
		
			My_Cache_Model::set( $cachetag, $unreadevents );
			
		}
		
		return $unreadevents;
		
	}
	
	public function get_unreadevents()
	{
	
		
		$cachetag = '-charinfo_' . $this -> id . '_unreadevents' ;
		//kohana::log('debug', "-> Getting $cachetag from CACHE..." ); 
		$unreadevents = My_Cache_Model::get( $cachetag );		
		
		if ( is_null ( $unreadevents ) )
		{
			//kohana::log('debug', "-> Getting $cachetag from CACHE..." ); 
			$data = $this -> get_stat('lastreadevent'); 
			
			if ( is_null( $data ) )
				$date = 0;
			else
				$date = $data -> value;

		
			$unreadevents =  ORM::factory( "character_event" ) -> 
				where ( array ( 
					'type' => 'normal', 
					'character_id' =>  $this -> id,
					'timestamp>' => $date) ) -> count_all();							
		
			My_Cache_Model::set( $cachetag, $unreadevents );
			
		}
		
		return $unreadevents;
		
	}
	
	/**
	* Returns title chosen with Basic Package
	* @param int $char_id ID Character
	* @return string title
	*/
	
	public function get_basicpackagetitle( $char_id )
	{		
	
		if (Character_Model::get_premiumbonus( $char_id, 'basicpackage') )
		{
			
			$stat = Character_Model::get_stat_d( 
				$char_id,
				'basicpackage',
				'title'
			);
			
			if ($stat -> loaded and $stat -> stat1 != '')
				return 'global.title_' . $stat -> stat1;
			else
				return '';
		}
		else
			return '';
				
	}
	
	/**
	* Returns info of current position
	* @param int $char_id ID character
	* @return array or null
	*/
	
	function get_currentposition_d( $char_id ) 
	{
		
		$cachetag = '-charinfo_' . $char_id . '_currentposition' ;		
		$currentposition = My_Cache_Model::get( $cachetag ); 
		
		if ( is_null( $currentposition ) )
		{
						
			$db = Database::instance();			
			
			$sql = "
			select r.*, k.name kname, k.image kimage, k.id kid  
			from regions r, kingdoms_v k, characters c			
			where r.id = c.position_id 			
			and   c.id = " . $char_id . "
			and   k.id = r.kingdom_id "; 
			
			$res = $db -> query( $sql ) -> as_array();
			if ( count( $res ) == 0 ) 
				$currentposition = false ;
			else
			{
				$currentposition = $res[0];
				$currentposition -> backgroundclass = $currentposition -> type;
				if ($currentposition -> type == 'land' )
					if ( Region_Model::isadjacenttosea($currentposition -> id) )
						$currentposition -> backgroundclass = 'landsea';
				
			}
			
			My_Cache_Model::set( $cachetag, $currentposition );
	
		}
		
		return $currentposition;		
		
	}
	
	/*
	* Torna la corrente posizione del personaggio
	* @param none
	* @return array $currentposition o false;
	*/
	
	function get_currentposition() 
	{
		
		$cachetag = '-charinfo_' . $this -> id . '_currentposition' ;
		$currentposition = My_Cache_Model::get( $cachetag ); 
		
		if ( is_null( $currentposition ) )
		{
			
			if ( $this -> position_id == 0 )
				$currentposition = false;
			else
			{
				$db = Database::instance();			
				$sql = "select regions.*, kingdoms_v.name as kname, kingdoms_v.image as kimage 				
				from regions join kingdoms_v
				on regions.kingdom_id = kingdoms_v.id
				where (regions.id = ". $this -> position_id .")"; 				
				$res = $db -> query( $sql ) -> as_array(); 			
				$currentposition = $res[0] ;
			}
			
			My_Cache_Model::set( $cachetag, $currentposition );
	
		}
		
		return $currentposition;		
		
	}
	
	
	/**
	* Torna i messaggi non letti dalle board
	* ogni 15 minuti ricontrolla.
	* @param character oggetto char
	* @category categoria della board
	* @return numero di messaggi non letti
	*/
	
	function get_unreadboardmessages_d( $char_id )	
	{
	
		
		$db = Database::instance();					
		
		// leggo la data di ultimo POST nelle board
		// e la setto nella cache. Se la setto 
		// invalido la cache del numero di annunci non letti.
		
		$cachetag = '-global-boardmessagelastpost' ;
		$boardmessagelastpost = My_Cache_Model::get( $cachetag );
		
		//kohana::log('debug', "-> Getting $cachetag from CACHE..." ); 
		
		if ( is_null ( $boardmessagelastpost ) )
		{
			$boardmessagelastpost = $db -> query( "select max( created ) created, 
				max( updated ) updated from boardmessages 
				where category in ( 'job', 'other', 'suggestion' )" ) -> as_array();			
				
			$boardmessagelastpost  = max( $boardmessagelastpost[0] -> created, $boardmessagelastpost[0] -> updated );
			
			My_Cache_Model::set	( $cachetag, $boardmessagelastpost );			
		}
		
		kohana::log('debug', 'lastpost: ' . date( $boardmessagelastpost ) ); 
		
		// ricavo la minima data in cui ho letto le board
		$cachetag = '-charinfo_' . $char_id . '_boardmessagelastread' ;		
		$boardmessagelastread = My_Cache_Model::get( $cachetag );
		kohana::log('debug', 'boardmessagelastread: ' . date( $boardmessagelastread ) ); 
		
		
		if ( is_null ( $boardmessagelastread ) )
		{			
			
			//kohana::log('debug', "-> Getting $cachetag from DB."); 
			
			$data = Character_Model::get_stat_d( $char_id, 'boardlastread', 'job' );
			if ( is_null( $data ) )	$date_job = 0; else	$date_job = $data -> value;
			
			$data = Character_Model::get_stat_d( $char_id, 'boardlastread', 'other' );
			if ( is_null( $data ) )	$date_other = 0; else	$date_other= $data -> value;
			
			$data = Character_Model::get_stat_d( $char_id, 'boardlastread', 'suggestion' );
			if ( is_null( $data ) )	$date_suggestion = 0; else	$date_suggestion = $data -> value;
			
			kohana::log('debug', $date_job );
			kohana::log('debug', $date_other );
			kohana::log('debug', $date_suggestion );
			
			$boardmessagelastread  = min( $date_job, $date_other, $date_suggestion );
			My_Cache_Model::set( $cachetag, $boardmessagelastread );
			
		}
		
		if ( $boardmessagelastread < $boardmessagelastpost)
			return true;
		else
			return false;					
		
	}
	
	function get_unreadboardmessages()	
	{
	
		
		$cachetag = '-charinfo_' . $this -> id . '_unreadboardmessage' ;
		
		//kohana::log('debug', "-> Getting $cachetag from CACHE..." ); 
		$unreadboardmessages = My_Cache_Model::get( $cachetag );
		
		if ( is_null ( $unreadboardmessages ) )
		{
			
			//kohana::log('debug', "-> Getting $cachetag from DB."); 
			
			$data = $this -> get_stat('boardlastread', 'job' );
			if ( is_null( $data ) )	$date_job = 0; else	$date_job = $data -> value;
			
			$data = $this -> get_stat('boardlastread', 'other' );
			if ( is_null( $data ) )	$date_other = 0; else	$date_other= $data -> value;
			
			$data = $this -> get_stat('boardlastread', 'suggestion' );
			if ( is_null( $data ) )	$date_suggestion = 0; else	$date_suggestion = $data -> value;
			
			$date = min( $date_job, $date_other, $date_suggestion );
		
			$db = Database::instance();					
			
			$sql = "select b.id from boardmessages b
				where (b. created + b.validity * 24 * 3600 ) > unix_timestamp() 			
				and   b.category in ( 'other', 'jobs', 'suggestion' ) 
				and 	b.status = 'published' 
				and		(b.kingdom_id = " . $this -> region -> kingdom -> id  . " or b.visibility = 'global') 
				and   (b.created > " . $date . " or b.updated > " . $date . ")"; 									
			
			$res = $db -> query ( $sql );		
			$unreadboardmessages = count( $res ); 
			My_Cache_Model::set( $cachetag, $unreadboardmessage ); 
		}
		
		return count( $unreadboardmessages );		
		
	}
	
	/**
	* Ricarica il char
	* @param int $char_id id ID personaggio
	* @return array $char Informazioni
	*/
	
	function get_data( $char_id )
	{
		
		if (empty($char_id))
			return null;
		
		kohana::log('debug', "--- get_data for ---");
		$cachetag = '-charinfo_' . $char_id . '_chararr' ;
		$char = My_Cache_Model::get( $cachetag );
		
		if ( is_null( $char ) )
		{
			
			$rset = Database::instance() -> query ("
			select c.* 
			from characters c, users u
			where u.id = c.user_id
			and c.id = {$char_id}") -> as_array();
			
			if (empty($rset))
				$char = null;
			else
			{
				$char = $rset[0];
			My_Cache_Model::set( $cachetag, $char );
		}
		}
		//var_dump($char);exit;
		return $char;
	
	}
	
	/**
	* Ricarica il char
	* @param char_id id char
	* @return oggetto char
	*/
	
	function get_info( $char_id )
	{
		
		$cachetag = '-charinfo_' . $char_id . '_charobj' ;
			//kohana::log('debug', "-> Getting $cachetag from CACHE..." ); 
			$char = null;
			$char = My_Cache_Model::get( $cachetag );
			
			if ( is_null( $char ) )
			{
			
				$char = ORM::factory('character', $char_id );
				
				if ( ! $char -> loaded )
					$char = null;
				
				My_Cache_Model::set( $cachetag, $char );
				
			}
		
		return $char;
	
	}
	
	
	/**
	* Trova la pending action "blocking" per il char passato
	* @param: character_id
	* @return: oggetto character_action pending o 'NOACTION'
	*/
	
	public static function get_currentpendingaction( $char_id )
	{					
		$cachetag = '-charinfo_' . $char_id . '_currentpendingaction' ;				
		//kohana::log('debug', "-> Get current pending action for char: [" . $char_id . "]");
		
		$currentpendingaction = My_Cache_Model::get( $cachetag );
		
		//kohana::log('info', kohana::debug( $currentpendingaction ));
		
		if ( is_null( $currentpendingaction ) or (is_array( $currentpendingaction) and $currentpendingaction['id'] == 0 ) )
		{		
			//kohana::log('info', "-> Getting Pending action from DB.");			
			$currentpendingaction = ORM::factory('character_action')
					-> where(	array( 
						'character_id' => $char_id,
						'status' => 'running',						
						'blocking_flag' => true )) -> find() -> as_array();
			
			//kohana::log('info', '-> Currentpendingaction : ' . 	$currentpendingaction['id'] );
			//kohana::log('info', kohana::debug( $currentpendingaction ));
			if ( $currentpendingaction['id'] !== 0 )
			{
				//kohana::log('info', '-> Setting currentpendingaction in cache.');
				My_Cache_Model::set( $cachetag, $currentpendingaction );
			}
			else
			{		
				$currentpendingaction = 'NOACTION';
				My_Cache_Model::set( $cachetag, $currentpendingaction );
				
			}			
		
		}
		
		/*
		if ( is_array( $currentpendingaction) )
			kohana::log('info', '-> Currentpendingaction: ' . 	$currentpendingaction['action'] );
		else
			kohana::log('info', '-> Currentpendingaction: ' . 	$currentpendingaction );
		*/
		
		return $currentpendingaction;
		
	}		
	
	/** 
	* Returns data for an achievement
	* If character does not have an achievement, $achievement['id'] is 0 
	* @param int $char_id ID Character
	* @param string $name Achievement Name
	* @return array $achievement or null se non esiste
	*/
	
	function get_achievement( $char_id, $name )
	{
	
		$cachetag = '-achievement_' . $char_id . '_' . $name; 				
		$achievement = My_Cache_Model::get( $cachetag );
				
		if ( is_null( $achievement ) )		
		{
			
			$rset = ORM::factory('character_title') -> where
			( array( 
				'character_id' => $char_id,
				'name' => $name,
				'current' => 'Y' )) -> find();
			
			if ($rset -> loaded )
				$achievement = $rset -> as_array();
			else
				$achievement = null;
			
			My_Cache_Model::set( $cachetag, $achievement );
			
		}
	
		return $achievement;
	
	}
	
	
	/** 
	* Verifica se un char ha conseguito un achievement
	* @param char_id ID char
	* @param nome dell' achievement
	* @return true o false
	*/
	
	
	function has_achievement( $char_id, $name )
	{
		$achievement = Character_Model::get_achievement( $char_id, $name );
		
		if ( is_null($achievement))
			return false;
		else
			return true;
	}
	
		
	/**
	* Costruisce il menu orizzontale per la scheda profilo pubblico
	* @param dead flag che dice se il char è morto oppure no
	* @param action azione corrente
	* @return html
	*/
	
	function get_publicprofile_submenu( $action )
	{
	
		$submenu = array( 
			'character/publicprofile/' . $this -> id => 
				array(
				'name' => kohana::lang('global.main'), 
				'htmlparams' => array( 'class' => ( $action == 'publicprofile' ) ? 'selected' : '' )),				
			'character/history/' . $this -> id => 
				array(
				'name' => kohana::lang('character.char_history'), 
				'htmlparams' => array( 'class' =>( $action == 'history' ) ? 'selected' : '' )));
		
		
		return $submenu;
	}
	
	/**
	* Costruisce il menu orizzontale per la scheda dettaglio
	* @param none
	* @return html
	*/

	function get_details_submenu()
	{
		
		return 	array( 
			'/character/details/' . $this -> id =>
				array( 'name' =>  kohana::lang('character.submenu_details'),    
				'htmlparams' => array( 'class' =>( uri::segment(2) == 'details' ) ? 'selected' : '' )),
			'/character/inventory' =>
				array( 'name' =>  kohana::lang('global.inventory'),    
				'htmlparams' => array( 'class' =>( uri::segment(2) == 'inventory' ) ? 'selected' : '' )),
			'/character/role/' . $this -> id =>
				array( 'name' =>  kohana::lang('character.submenu_role'),    
				'htmlparams' => array( 'class' =>( uri::segment(2) == 'role' ) ? 'selected' : '' )),
			'/character/myproperties/' . $this -> id =>
				array( 'name' =>  kohana::lang('character.submenu_myproperties'),    
				'htmlparams' => array( 'class' =>( uri::segment(2) == 'myproperties' ) ? 'selected' : '' )),
			'/character/myjobs' =>
				array( 'name' =>  kohana::lang('character.submenu_myjobs'),    
				'htmlparams' => array( 'class' =>( uri::segment(2) == 'myjobs' ) ? 'selected' : '' )),			
			'/character/myquests/' =>
				array( 'name' =>  kohana::lang('character.submenu_myquests'),    
				'htmlparams' => array( 'class' =>( uri::segment(2) == 'myquests' ) ? 'selected' : '' )),			
			'/event/show/' =>
				array( 'name' =>  kohana::lang('character.submenu_events'),    
				'htmlparams' => array( 'class' =>( uri::segment(2) == 'show' ) ? 'selected' : '' )),
			'/user/referrals/' =>
				array( 'name' =>  kohana::lang('character.submenu_referrals'),    
				'htmlparams' => array( 'class' =>( uri::segment(2) == 'referrals' ) ? 'selected' : '' )),			
			'/group/mygroups/' =>
				array( 'name' =>  kohana::lang('character.mygroups'),    
				'htmlparams' => array( 'class' =>( uri::segment(1) == 'group' ) ? 'selected' : '' )));
				
	}

	
	/** 
	* Torna tutti i ruoli attivi del giocatore
	* @param none
	* @return allroles:  array con tutti i ruoli
	*/
	
	function get_allrptitles()
	{
		// Carico tutti i ruoli del character
		$allroles = ORM::factory('character_role')
			->where( array( 
				"character_id" => $this->id, 
				'current' => true,		
				'gdr' => true,
				))
			->orderby('gdr', 'asc')
			->find_all();

		return $allroles;	
	}
	
	/** 
	* Torna tutti i ruoli attivi del giocatore
	* @param none
	* @return allroles:  array con tutti i ruoli
	*/
	
	function get_alltitles()
	{
		// Carico tutti i ruoli del character
		$allroles = ORM::factory('character_role')
			->where( array( 
				"character_id" => $this->id, 
				'current' => true,		
				))
			->orderby('gdr', 'asc')
			->find_all();

		return $allroles;	
	}
	
	/** 
	* Verifica se il char ha un certo ruolo RP
	* @param tag ruolo
	* @return true o false
	*/
	
	function has_rprole( $roletag )
	{
		$found = false;
		$allroles = $this -> get_allrptitles();
		foreach ( $allroles as $role )
			if ( $role -> tag == $roletag )
			{
				$found = true ;
				break;
			}
		return $found;
		
	}
	
	/**
	* Calcola l' encumbrance
	* @param none
	* @return int $encumbrance Percentuale >= 0
	*/
	
	public function get_encumbrance( )
	{	
		$encumbrance =  round($this -> get_weightinexcess() / $this -> get_maxtransportableweight(), 3) * 100;			
		return $encumbrance;
	}
	
	/**
	* Calcola l' encumbrance di guerra
	* @param $basetransportableweight in kg
	* @param $equippedweight
	* @return encumbrance
	*/
	
	public function get_armorencumbrance( $basetransportableweight, $equippedweight )
	{		
		$armorencumbrance = round( $equippedweight / $basetransportableweight, 2) * 100;		
		return $armorencumbrance;		
	}

	/**
	* ritorna il quest attivo
	* @param int $char_id
	* @return array info o null
	*/
	
	public function get_active_quest( $char_id )
	{
		kohana::log('debug', '--- get_active_quest ---');
		$cachetag = "-activequest-{$char_id}" ;		
		$cfg = My_Cache_Model::get( $cachetag );		
		
		if ( is_null( $cfg ) )		
		{
			kohana::log('debug', '-> Loading data from DB');

		$activequest = Character_Model::get_stat_d( 
				$char_id, 
			'quest', 
			null, 
			'active' );		
	
			if ($activequest -> loaded)
			{
				$quest = QuestFactory_Model::createQuest($activequest -> param1);		
				$cfg = $quest -> get_info($char_id);			
	}
			My_Cache_Model::set( $cachetag, $cfg );
		}

		return $cfg;
	
	}

	/**
	* Ritorna le malattie del char. Scarta quelle inattive (curate)
	* o che sono attive ma scadute
	* @param none
	* @return array di stats
	*/
	
	public function get_diseases()	
	{
		$diseases = $this -> get_stats( 'disease', null, null );
		
		foreach ( (array) $diseases as $key => $value )
			if ( $value -> spare1 != 'active' )
				unset ($diseases[$key]);
		
		return $diseases;
	}
	
	/**
	* Torna se il char ha una malattia specifica
	* @param nome della malattia
	* @return true o false
	*/
	
	public function has_disease( $name )
	{
		$diseases = $this -> get_diseases();
		
		foreach ( (array) $diseases as $disease )
			if ( $disease -> param1 == $name )
				return true; 
		return false;
		
	}

	/**
	* Mette il personaggio a riposare in modo automatico. 	
	* @param int $char_id ID character
	* @return boolean true or false in case of error.
	*/
	
	function makecharsleep( $char_id )
	{
		
		$character = ORM::factory('character', $char_id );
		kohana::log( 'info', "-> --- AUTOMATIC SLEEP ---" );
		kohana::log( 'info', "-> Checking if char {$character -> name} has sleepafteraction set." );
		kohana::log( 'info', "-> Character sleepafteraction flag: " . $character -> user -> sleepafteraction );
		$message = '';
		
		if ( $character -> type == 'npc' )
		{
			kohana::log( 'info', "-> Character is a NPC.");
			return false;
		}
		
		if ( Character_Model::get_premiumbonus( $character -> id , 'automatedsleep' ) === false )
		{
			kohana::log( 'info', "-> Character does not have the bonus.");
			return false;
		}
		
		if ( $character -> user -> sleepafteraction  == 'Y'	)
		{		
			kohana::log( 'info', "-> Finding pending actions..." );
			$pendingaction = Character_Model::get_currentpendingaction( $character -> id );
				
			// solo se non c'è pending action mettiamo a dormire il char.

			//kohana::log('info', kohana::debug($pendingaction) );
			
			if ( $pendingaction != 'NOACTION' )
			{
				kohana::log('info', "-> There is a pendingaction: {$pendingaction['action']}, exiting.");
			}
			else
			{
				kohana::log( 'info', "-> Eating..." );
				
				// trova il cibo trasportato e ordiniamolo per capacità  di 
				// sazietà 
				
				$fooditems = Database::instance() -> query("
					select i.id, i.quantity, ci.tag, ci.spare1, ci.spare3, ci.category, ci.subcategory 
					from items i, cfgitems ci
					where i.character_id = " . $char_id . "					
					and   i.cfgitem_id = ci.id 
					and   ci.parentcategory = 'consumables' 					
					and   ci.subcategory in ( 'cookedfood' ) "
					 ) -> as_array();
				
				// se ha cibo, mangia...
				
				if ( count( $fooditems ) > 0 )
				{
					kohana::log('info', '-> Char has food, eating it.');
				
					$k = 0;
					foreach ( $fooditems as $food )
					{
						$a_food[$k]['id'] = $food -> id;
						$a_food[$k]['quantity'] = $food -> quantity;
						$a_food[$k]['tag'] = $food -> tag;
						if ( $food -> subcategory == 'cookedfood' )	
						{
							$a_food[$k]['glut'] = $food -> spare1;
							$a_foodglut[$food -> id]['glut'] = $food -> spare1;
						}
						$k++;
					}				
				
					array_multisort( $a_foodglut, SORT_DESC, $a_food );
				
					//var_dump($a_food); exit;
					
					kohana::log('debug', '-> Char has glut: ' . $character -> glut );
					
					$maxglut = $character -> user -> maxglut ;
					
					foreach ( $a_food as $food )
					{
						kohana::log('debug', '-> Max point glut: ' . $maxglut );
						kohana::log('debug', '-> Checking food: ' . $food['tag'] );
						
						if ( $food['glut'] > ( $maxglut - $character -> glut ) )			
						{
							kohana::log('debug', '-> Food: ' . $food['tag'] . 
								' has too many calories (' . $food['glut'] . ')... skipping to the next food category. ');
							continue;
						}
							
						for ($i = 1; $i <= $food['quantity']; $i ++ )
						{
							
							if ( $food['glut'] > ( $maxglut - $character -> glut ) )
							{
								kohana::log('debug', '-> Food: ' . $food['tag'] . ' has too many calories 
								(' . $food['glut'] . ')... skipping to the next. '	);
								break;
							}
							
							kohana::log('info', '-> Eating 1 ' . $food['tag'] );
							$a = Character_Action_Model::factory("apply");		
							$par[0] = $food['id'];
							$par[1] = $character;
							$par[2] = 1;
							$par[3] = $character -> id;
							$rc = $a -> do_action( $par,  $message );
							if ( $rc == false )	
							{
								Character_Event_Model::addrecord(
									$character -> id,
									'normal',
									'__events.error-automaticsleep;__' . 
									$message );									
							}
							else
							{
								//$character -> glut += $food['glut'];
								kohana::log('debug', '-> Char has now glut: ' . $character -> glut );
							}
							
						}
					}
				}
				
				// sleep				
				$restfactors = array();
				
				kohana::log( 'info', "-> Putting char to sleep..." );
				$message = 'ca_rest.noplacetosleep';
				
				kohana::log( 'info', "-> Finding character role..." );
				$role = $character -> get_current_role();				
				$currentregion = ORM::factory('region', $character -> position_id );
				
				// se ha un ruolo e la struttura è presente
				// nella regione, provo a dormire nella struttura.
				
				$i = 0;
				kohana::log('info', '-> Checking restfactor of government or religious structure...');
				
				if ( !is_null($role) and $role -> gdr == false )
				{
					
					kohana::log( 'info', "-> Char has role: " . $role -> tag . ', checking rest factor of controlled structure...');
					
					$structure = $role -> get_controlledstructure();
					
					if ($structure -> loaded == false )
					{
						kohana::log('info', '-> Could not find structure controlled. Check data.');						
					}
					else
						kohana::log( 'info', "-> Char position_id: " . $character -> position_id . " structure location: " . $structure -> region_id );
					
					if ( $structure -> loaded and $structure -> region_id == $character -> position_id )
					{
						kohana::log('info', '-> Adding restfactor of owned structure...');
						$restfactors[$i]['structure'] = $structure;
						$data = $character -> get_restfactor( $structure, false, false );
						$restfactors[$i]['restfactor'] = $data['restfactor'];
						$i++;						
					}
				}
				
				// caso speciale: sposato
				
				$relation = Character_Model::is_married( $character -> id );
				
				if (!is_null($relation))				
				{
					
					// c'è una struttura governativa dello sposato?
					// se sà¬, prendi il RestFactor.
					
				}
					
				// caso speciale: watchtower
				
				kohana::log('info', '-> Checking restfactor of watchtower...');
				$watchtower = $currentregion -> get_structure( 'watchtower' );
				if ( !is_null ( $watchtower ) and  $watchtower -> allowedaccess( 
					$character, 'watchtower', $message, 'private', 'rest' ) )
				{
					kohana::log('info', '-> Adding restfactor of watchtower...');
					$restfactors[$i]['structure'] = $watchtower;
					$data = $character -> get_restfactor( $watchtower, false, false );
					$restfactors[$i]['restfactor'] = $data['restfactor'];
					$i++;					
				}
				
				// Casa: il char ha una casa nella presente locazione?
				
				$house = Database::instance() -> query( 
					"select s.id
					  from structures s, structure_types st
					  where s.structure_type_id = st.id 
					  and s.region_id = " . $character -> position_id . "						  
					  and s.character_id = " . $character -> id . " 
					  and st.parenttype = 'house' " ) -> as_array();
				
				kohana::log('info', '-> Checking restfactor of house...');	
				if ( count( $house ) > 0 ) 
				{
					
					$structure = StructureFactory_Model::create( null, $house[0] -> id );
					$restfactors[$i]['structure'] = $structure;
					kohana::log('info', '-> Adding restfactor of house...');
					$data = $character -> get_restfactor( $structure, false, false );
					$restfactors[$i]['restfactor'] = $data['restfactor'];
					$i++;					
				}
						
				// cart

				kohana::log('info', '-> Checking restfactor of cart...');	
				if ( Character_Model::has_item( $character->id, 'cart_3', 1 ) )
				{
					kohana::log('info', '-> Adding restfactor of cart...');
					$restfactors[$i]['structure'] = null;
					$data = $character -> get_restfactor( null, false, true );
					$restfactors[$i]['restfactor'] = $data['restfactor'];
					$i++;
				}				
								
				// tavern
				kohana::log('info', '-> Checking restfactor of tavern...');	
				$tavern = $currentregion -> get_structure( 'tavern' );
				
				if ( !is_null ( $tavern ) )
				{
					
					kohana::log('info', '-> Adding restfactor of tavern...');
					$restfactors[$i]['structure'] = $tavern;
					kohana::log('info', '-> age of char is : ' . $character -> get_age());
					$data = $character -> get_restfactor( $tavern, true, false );					
					
					if ($character -> get_age() <= 90 )
					{
						$freerest = false;
						$data = $character -> get_restfactor( $tavern, $freerest, false );
					}
					else
					{
						$freerest = true;
						$data = $character -> get_restfactor( $tavern, $freerest, false );
					}
					
					$restfactors[$i]['restfactor'] = $data['restfactor'];
					
					$i++;					
				}
				
				//village				
				
				kohana::log('info', '-> Checking restfactor of village...');	
				if ( $currentregion -> is_independent() )				
				{
					$village = $currentregion -> get_structure( 'nativevillage' );
					if ( !is_null ( $village ) )
					{						
						kohana::log('info', '-> Adding restfactor of native village...');					
						$restfactors[$i]['structure'] = $village;
						$data = $character -> get_restfactor( $village, false, false );
						$restfactors[$i]['restfactor'] = $data['restfactor'];
						$i++;						
					}
				}
				
				//kohana::log('info', kohana::debug($restfactors));				
				
				if (count($restfactors) == 0 )
				{
					Character_Event_Model::addrecord(
							$character -> id,
							'normal',
							'__events.error-automaticsleep;__' . $message );
					
					kohana::log('info', '-> No places found where the char can sleep.');
							
					return false;
					
				}
				
				foreach ( $restfactors as $key => $row )
				{										
					$_restfactor[$key] = $row['restfactor'];
				}
				
				array_multisort($_restfactor, SORT_DESC, $restfactors);		
				$wheretorest = array_shift(array_values($restfactors));	
				
				if ( is_null ($wheretorest['structure']) )
				{
					kohana::log('info', '-> Resting in cart.');
					$a = Character_Action_Model::factory("rest");
					$par[0] = $character;
					$par[1] = null;
					$par[2] = true;			
					$rc = $a -> do_action( $par, $message );	
					return $rc;			
				}
				else
					kohana::log('info', '-> **** Resting in: ' . $wheretorest['structure'] -> structure_type -> type . "****");

				// tavern
				
				if	( $wheretorest['structure'] -> getSupertype() == 'tavern' )
				{
										
					$a = Character_Action_Model::factory("resttavern");		
					$par[0] = $character;
					$par[1] = 0; // non usato
					$par[2] = $wheretorest['structure'];
					$par[3] = $freerest;
					$par[4] = 0;
					$par[5] = 100;
					$par[6] = 0;
					
					$rc = $a -> do_action( $par, $message );	
					if ( $rc == false )
					{
						Character_Event_Model::addrecord(
							$character -> id,
							'normal',
							'__events.error-automaticsleep;__' . $message );
					}
					
					return $rc;
					
				}				
				else
				{
					$a = Character_Action_Model::factory("rest");
					$par[0] = $character;
					$par[1] = $wheretorest['structure'];
					$par[2] = false;			
					$rc = $a -> do_action( $par, $message );	
					if ( $rc == false )
					{
						Character_Event_Model::addrecord(
							$character -> id,
							'normal',
							'__events.error-automaticsleep;__' . $message );
					}
					
					return $rc;
				}
				
			}
		}
		
		return false;
	
	}	
	
	/**
	* Finds if two characters are married
	* @param int sourcecharid
	* @param int targetcharid	
	* @return boolean result
	*/
	
	public function is_marriedto( $source_id, $target_id, &$relationtype )
	{
		
		$result = false;		
		$kinrelations = Character_Relationship_Model::get_kinrelations( $source_id );  		
		
		if (isset( $kinrelations['outgoingrelations']['husband'])
			and $kinrelations['outgoingrelations']['husband']['id'] == $target_id )
			{
				$relationtype = 'husband';
				$result = true;
			}
		
		if (isset( $kinrelations['outgoingrelations']['wife'])
			and $kinrelations['outgoingrelations']['wife']['id'] == $target_id )
			{
				$relationtype = 'wife';
				$result = true;
			}
		
		return $result;
	}
	
	/**
	* Torna se un personaggio è sposato
	* @param int $char_id ID Character
	* @return array $data Informazioni sulla relazione o NULL se non trovata
	*/
	
	public function is_married( $char_id )
	{
	
		$data = null;
		
		$kinrelations = Character_Relationship_Model::get_kinrelations( $char_id );
		
		if ( isset( $kinrelations['outgoingrelations']['wife'] ))
			$data = $kinrelations['outgoingrelations']['wife'];
		
		if ( isset( $kinrelations['outgoingrelations']['husband'] ))
			$data = $kinrelations['outgoingrelations']['husband'];
			
		return $data;
		
	}	
	
	/*
	* Calcola se un char ha un ruolo religioso
	* @param   none
	* @return  boolean  $has_religious_role
	*/
	public function has_religious_role()
	{
		$has_religious_role = false;
		
		// Cerco tra tutti i ruoli del char
		foreach ($this->character_roles as $role)
		{
			if
			(
				// Il rouolo è di tipo religioso
				in_array ( $role -> tag, array ('church_level_1', 'church_level_2', 'church_level_3', 'church_level_4' ) )
				// Il ruolo è attuale
				and $role -> current
			)
			$has_religious_role = true;
		}
		
		return $has_religious_role;
		
	}
	
	/**
	* Assegna un tutor
	* @param int $user_id ID Utente
	* @param string $primarylanguage Linguaggio Primario
	* @return none
	*/
	
	public function assign_tutor( $character, $primarylanguage )
	{
	
		// trova tutti i tutors e li ordina per data 
		// in modo che si segua un algoritmo round-robin.

		kohana::log('info', "-> Assigning tutor for char: [{$character -> name}]");
		
		$sql = "
		SELECT u.id user_id, c.id character_id, c.name character_name, ul.language
		FROM users u, user_languages ul, roles_users ru, roles r, characters c, character_stats cs
		WHERE u.id = ul.user_id
		AND  u.id = ru.user_id
		AND  ru.role_id = r.id
		AND  u.id = c.user_id 
		AND  cs.character_id = c.id
		AND  cs.name = 'lastnewbornassigned'
		AND  r.name = 'newborntutor'
		AND  ul.language != '' 
		ORDER BY cs.stat1 asc";
		
		$choosentutor = null;
		$firsttutor = null;

		$tutors = Database::instance() -> query ( $sql );

		if ($tutors -> count() > 0 )
		{
	
			// ciclo 1: cerchiamo di assegnare il tutor
			// che conosce il linguaggio primario
				
			$count = 0;
			
			foreach ($tutors as $tutor)
			{
				
				if ($count == 0)
					$firsttutor = $tutor;
				
				if ($tutor -> language == $primarylanguage)
				{
					$choosentutor = $tutor;
					break;
				}
				
				$count++;
			}

			if (is_null($choosentutor))	
				$choosentutor = $firsttutor;		

			//kohana::log('info', "-> Assigned tutor:" . kohana::debug($choosentutor));
			
			Database::instance() -> query("
				UPDATE users set tutor_id = {$choosentutor -> user_id}
				WHERE id = {$character->user_id}");
			
			// trova il gruppo tutor corretto
			$tutorgroup = ORM::factory('group')
				-> where('name', "Tutor " . $choosentutor -> character_name) -> find();
			
			Group_Model::add_member ($tutorgroup -> id, $character -> id, true);
			
			// mail
			
			$choosentutorchar = ORM::factory('character', $choosentutor -> character_id);
			Message_Model::send(
				$choosentutorchar,
				$character,
				'Need Help?',			
				'Hello, I would like to welcome you to Medieval Europe. I am your personal tutor and I will answer your questions. Much of the game is explained in the tutorial quest, which I recommend to complete as soon as possible also to get money and useful items. If you have any questions about the game or need help feel free to contact me.',
				false,
				false
			);
			
			Character_Model::modify_stat_d(
				$choosentutor -> character_id,
				'lastnewbornassigned',
				0,
				null,
				null,
				true,
				time()
			);
			
			Character_Event_Model::addrecord(
				$choosentutor -> character_id,
				'normal',  
				'__events.newbornassigned'.
				';'. $character -> name			
			);
			
			Character_Event_Model::addrecord(
				$character -> id,
				'normal',  
				'__events.tutorassigned'.
				';'. $choosentutor -> character_name
			);
		}
		
		return;	
		
	}
	
}
?>
