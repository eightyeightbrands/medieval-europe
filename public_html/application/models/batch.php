<?php defined('SYSPATH') OR die('No direct access allowed.');
class Batch_Model
{
	const SECURITYKEY = '';
	const ILREDUCTIONFACTOR = 3;

	public function test_email() {
		Utility_Model::mail( kohana::config('medeur.adminemail') , "Medieval Europe [Server {$config['servername']}] - Testing.", 'this is a test');
		echo 'sent';
	}

	/*
	* Watchdog
	*/

	public function watchdog($securitykey = null)
	{
		if ( $securitykey != self::SECURITYKEY )
		{
			kohana::log('error', "securitykey[$securitykey] is not correct.");
			die( 'Security Key is wrong.');
		}

		$sql = "
		select id, action, from_unixtime( starttime ) starttime
		from character_actions
		where keylock is not null
		and unix_timestamp() > (endtime + 1800)
		and status = 'running' ";

		$rset = Database::instance() -> query( $sql );
		$count = 0;
		$body = "";
		foreach ($rset as $row)
		{
			$body .= "ID:" . $row -> id . ", Action: " . $row -> action . ", Scheduled for: " . $row -> starttime . "<br/>";
			$count ++;
		}

		if ( $count  > 0 )
			Utility_Model::mail( kohana::config('medeur.adminemail') , "Medieval Europe [Server {$config['servername']}] - Found {$count} blocked actions.", $body);
	}

	/*
	* Delete a char avatar
	*/

	public function deleteavatar( $securitykey = null, $character_id = null )
	{
		if ( $securitykey != self::SECURITYKEY )
		{
			kohana::log('error', "securitykey[$securitykey] is not correct.");
			die( 'Security Key is wrong.');
		}

		unlink( DOCROOT . 'media/images/characters/' . $character_id . '_s.jpg');
		unlink( DOCROOT . 'media/images/characters/' . $character_id . '_l.jpg');

		return;
	}


/**
* calcola il bonus giornaliero in base
* ai giocatori vivi nelle ultime 24h
* @param str $securitykey Security Key
* @return none
**/

	function give_daily_revenues( $securitykey = null)
	{


		if ( $securitykey != self::SECURITYKEY )
		{
			kohana::log('error', "securitykey[$securitykey] is not correct.");
			die( 'Security Key is wrong.');
		}

		kohana::log('info', '>>> Giving Daily bonus <<<');

		$totalcoins = 0;
		$db = Database::instance();

		///////////////////////////////////
		// Daily revenue per Regno
		///////////////////////////////////

		$sql = "select k.id, k.name, count(c.name) active_residents
		from   characters c, kingdoms_v k, regions r, users u
		where  c.region_id = r.id
		and    r.kingdom_id = k.id
		and    c.user_id = u.id
		and    c.type = 'pc'
		and    u.last_login > ( unix_timestamp() - (2 * 24 * 3600) )
		group by k.name
		order by count(c.name) desc";

		$res = $db -> query ( $sql );

		foreach ( $res as $kingdom )
		{

			$sql = "select count(*) castles
				from structures s, structure_types st
				where s.structure_type_id = st.id
				and   st.type = 'castle_1'
				and   s.region_id in ( select id from regions where kingdom_id = " . $kingdom -> id .  " ) ";

			$res2 = $db -> query ( $sql ) -> as_array();
			$coppercoins = $res2[0] -> castles * $kingdom -> active_residents * 60 ;

			kohana::log('info', '-> Kingdom ' . $kingdom -> name . ' has ' . $kingdom -> active_residents . ' Active Residents and ' .$res2[0] -> castles  . ' castles. ' );

			$silvercoins = intval ( $coppercoins / 100 );
			$coppercoins = intval ( $coppercoins % 100 );

			kohana::log('info', '-> Giving silver coins: ' . $silvercoins . ', copper coins: ' . $coppercoins . ' to royalpalace.'  );
			// give coins

			$ccoins = Item_Model::factory( null, 'coppercoin' );
			$scoins = Item_Model::factory( null, 'silvercoin' );

			$kingdom = ORM::factory('kingdom', $kingdom -> id );
			$royalpalace = $kingdom -> get_structure('royalpalace');

			$scoins -> additem( "structure", $royalpalace -> id, $silvercoins );
			$ccoins -> additem( "structure", $royalpalace -> id, $coppercoins );

			// event

			$text = '__events.structurecastledailyrevenue;' . $silvercoins . ';' . $coppercoins ;
			Structure_Event_Model::newadd( $royalpalace -> id, $text );

			$totalcoins += $silvercoins;

		}

		kohana::log('info', "-> Total Silver coins given: {$totalcoins}");

		Trace_Sink_Model::add( 'silvercoin', -1, $totalcoins, 'dailyrevenue');

	}


	/**
	* Clean up and archive data
	*/

	public static function cleanupdatabase( $securitykey = null)
	{

		if ( $securitykey != self::SECURITYKEY )
		{
			kohana::log('error', "securitykey [$securitykey] is not correct.");
			return;
		}

		kohana::log('info', '-> Cleaning up database...');

		// cancella righe dalle tabelle online


		// structure events -- 1 mese
		kohana::log('info', '-> Cleaning up structure_events...');
		Database::instance() -> query( "delete from structure_events where timestamp < ( unix_timestamp() - (1*30*24*3600))" );
		// character events -- 15 gg
		kohana::log('info', '-> Cleaning up character_events...');
		Database::instance() -> query( "delete from character_events where timestamp < ( unix_timestamp() - (15*24*3600))" );
		// character actions -- 15 gg
		kohana::log('info', '-> Cleaning up character_actions...');
		Database::instance() -> query( "delete from character_actions where (status = 'completed' or status = 'canceled') and endtime < ( unix_timestamp() - (15*24*3600)) and character_id <> -1");
		// character messages -- 1 mese
		kohana::log('info', '-> Cleaning up messages...');
		Database::instance() -> query( "delete from messages where date < ( unix_timestamp() - (1*30*24*3600)) and archived = 'N' ") ;
		kohana::log('info', '-> Cleaning up toplistvotes...');
		Database::instance() -> query( 'delete from toplistvotes where timestamp < ( unix_timestamp() - (7*24*3600))');

		kohana::log('info', '-> Cleaning up trace_sinks...');
		Database::instance() -> query( "delete from trace_sinks where type = 'silvercoin' and  timestamp < DATE_SUB(NOW(), INTERVAL 1 MONTH)");
		kohana::log('info', '-> Cleaning up trace_user_logins...');
		Database::instance() -> query( "delete from trace_user_logins where logintime < ( unix_timestamp() - (1*30*24*3600))" );
		// Clean up old battles (duels and npcvspc
		kohana::log('info', '-> Cleaning up battle reports...');
		Database::instance() -> query( "delete from battle_reports where battle_id in (
			select id from battles where timestamp < ( unix_timestamp() - (1*30*24*3600))
			and type in ('pcvsnpc', 'duel'))" );
		Database::instance() -> query( "delete from battles where timestamp < ( unix_timestamp() - (30*24*3600)) and type in ('pcvsnpc', 'duel')" );
		kohana::log('info', '-> Cleaning up archives...');
		Database::instance() -> query( "update ar_characters set status = 'erased' where deathdate < ( unix_timestamp() - (6*30*24*3600))" );
		Database::instance() -> query( "delete from ar_character_events where character_id in (select id from ar_characters where status = 'erased' )");
		Database::instance() -> query( "delete from ar_character_stats where character_id in (select id from ar_characters where status = 'erased' )");
		Database::instance() -> query( "delete from ar_items where character_id in (select id from ar_characters where status = 'erased' )");
		Database::instance() -> query( "delete from ar_messages where char_id in (select id from ar_characters where status = 'erased' )");
		Database::instance() -> query( "delete from ar_structures where character_id in (select id from ar_characters where status = 'erased' )");
		Database::instance() -> query ("update users set doubloons = 0 where doubloons > 0 and last_login < (unix_timestamp() - (3*30*24*3600));");

		kohana::log('info', '-> Finished Cleaning up database.');

	}

	/**
	* calcola le statistiche
	* @param $securitykey security key
	* @return none
	*/

	function computestats($securitykey=null)
	{

		if ( $securitykey != self::SECURITYKEY )
		{
			kohana::log('error', "securitykey[$securitykey] is not correct.");
			return;
		}

		$s = new Stats_Model();
		$s -> compute_all_stats();

	}


	function consumeitems($securitykey=null)
	{

		if ( $securitykey != self::SECURITYKEY )
		{
			kohana::log('error', "securitykey $securitykey is not correct.");
			return;
		}

		// Gioielli

		$sql = "
		select i.id, i.character_id char_id, ci.tag, ci.name from items i, cfgitems ci
		where i.cfgitem_id = ci.id
		and i.equipped != 'unequipped'
		and ci.category in ( 'jewel' )
		and ci.tag not in ('crown_king_1', 'ringdiamond' )";

		$rset = Database::instance() -> query( $sql );
		mt_srand( time() );
		if ( count ( $rset ) > 0 )
			foreach ( $rset as $item )
			{
				$i = ORM::factory('item', $item -> id );

				$chance = mt_rand( 1, 1000 );
				kohana::log('info', '-> Trying to destroying jewel: ' . $i -> cfgitem -> tag . ' roll: ' . $chance . ' char id: ' . $item -> char_id );
				if ( $chance == 23 )
				{
					kohana::log('info', '-> Destroying jewel: ' . $i -> cfgitem -> tag );
					if ( $i -> loaded )
					{
						Character_Event_Model::addrecord(
							$i -> character_id,
							'normal',
							'__events.jewellost'. ';__' . $i -> cfgitem -> name,
							'evidence'
						);
						$i -> destroy();
						kohana::log('info', '-> Destroyed item' );
					}
				}

				My_Cache_Model::delete( '-charinfo_' . $i -> character_id . '_' . $i -> cfgitem -> tag );

			}

	}

	/*
	* Send starving email
	* @param string $securitykey key
	* @return none
	*/

	function sendstarvingemail($securitykey=null)
	{

		if ( $securitykey != self::SECURITYKEY )
		{
			kohana::log('error', "-> securitykey $securitykey is not correct.");
			return;
		}

		$sql = "select
		u.id, u.email, u.username, u.language, c.name name, c.glut glut, c.health health from characters c, users u
		where c.user_id = u.id
		and c.type != 'npc'
		and u.email not like '%nowhere.com%'
		and glut < 1 and health < 50";
		$rset = Database::instance() -> query ( $sql );

		foreach ( $rset as $row )
		{
			kohana::log('info', "-> Sending starving email to: " . $row -> name );
			$to = $row -> email;
			$subject = sprintf( 'Medieval Europe: Your char %s is very hungry and his health is low!', $row -> name );
			$body = sprintf( 'Current glut level: %s, Current health level: %s.<br/><br/>
			Hello %s, if you don\'t eat something, in the next few days your character will die. Your character and your possessions will be deleted and if you want to play again, a new character would need to be created.<br/>
			Please login as soon with your username <b>%s</b> as soon as possible and feed your char.',	$row -> glut, $row -> health, $row -> name, $row -> username );

			Utility_Model::send_notification( $row -> id, $subject, $body );

		}

	}

	/*
	* Abbassa il livello di intossicazione
	* @param securitykey Security key per i batch
	* @return none
	*/

	public static function reduceintoxicationlevel($securitykey=null)
	{

		if ( $securitykey != self::SECURITYKEY )
		{
			kohana::log('error', "-> securitykey $securitykey is not correct.");
			return;
		}

		$sql = "
			select cs.id csid, c.id cid, c.name from characters c, character_stats cs
			where cs.character_id = c.id
			and   cs.name = 'intoxicationlevel'
			and   cs.value > 0 ";

		$rset = Database::instance() -> query( $sql );

		foreach ( $rset as $row )
		{
			$current_il_stat = Character_Model::get_stat_d(
				$row -> cid,
				'intoxicationlevel');

			$new_il = max( $current_il_stat -> value - self::ILREDUCTIONFACTOR, 0 );
			kohana::log('info', '-> drink: char: ' . $row -> name . ', current_il: ' . ($current_il_stat -> value) . ', new_il: ' . $new_il );

			// se il < 50, tolgo lo stato tipsy

			$char = ORM::factory('character', $row -> cid );

			if ( $new_il < 50 and
				(
					$char -> has_disease( 'tipsyness' )
					or
					$char -> has_disease( 'drunkness' )
				)
			)
			{
				kohana::log('info', '-> reduceil: char: ' . $row -> name . ' curing  tipsyness... ' );
				$obj = DiseaseFactory_Model::createDisease('tipsyness');
				$obj -> cure_disease( $char );
				kohana::log('info', '-> reduceil: char: ' . $row -> name . ' curing  drunkness... ' );
				$obj = DiseaseFactory_Model::createDisease('drunkness');
				$obj -> cure_disease( $char );
			}

			Character_Model::modify_stat_d(
				$row -> cid,
				'intoxicationlevel',
				$new_il,
				null,
				null,
				true );

		}

	}

	/**
	* Creates a new kingdom
	* @param string $securitykey key
	* @param string $kingdomname (es: duchy-germany)
	* @param string $capitalname (es: region.lombardia)
	* @param string $king_id Character ID
	* @param string $title Regent Title (es: king)
	* @param string $color Color on the map (es: 0xf5f500)
	*/

	function splitkingdoms( $securitykey, $newkingdomname, $capitalname, $king_id, $title, $color )
	{

		if ( $securitykey != self::SECURITYKEY )
		{
			kohana::log('error', "securitykey $securitykey is not correct.");
			return;
		}

		Database::instance() -> query("set autocommit = 0");
		Database::instance() -> query("start transaction");
		Database::instance() -> query("begin");

		try {

			$kingdom = ORM::factory('kingdom') ->
				where ( 'name','kingdoms.' . $kingdomname ) -> find();

			if ($kingdom -> loaded )
				die("-> Kingdom {$newkingdomname} is already existing.");

			$capital = ORM::factory('region') -> where ( 'name', $capitalname ) -> find();

			if (!$capital -> loaded )
				die("-> Region {$capitalname} does not exist.");

			$castle = $capital -> get_structure('castle');
			if (is_null($castle) )
				die("-> Region {$capitalname} does not have a castle.");

			$kingchar = ORM::factory('character') -> where ('id', $king_id) -> find();

			if (!$kingchar -> loaded )
				die("-> Char {$king_id} does not exist.");

			// create new kingdom

			$kingdom = new Kingdom_Model();
			$kingdom -> name = 'kingdoms.' . $newkingdomname;
			$kingdom -> image = $newkingdomname;
			$kingdom -> title = 'global.title_' . $title;
			$kingdom -> color = $color;
			$kingdom -> language1 = 'English';
			$kingdom -> language2 = 'English';
			$kingdom -> activityscore = 0;
			$kingdom -> save();

			$kingdomhistory = new Kingdom_history_Model();
			$kingdomhistory -> id = $kingdom -> id;
			$kingdomhistory -> name = 'kingdoms.' . $newkingdomname;
			$kingdomhistory -> kingdom_id = $kingdom -> id;
			$kingdomhistory -> image = $newkingdomname;
			$kingdomhistory -> begin = time();
			$kingdomhistory -> end = 1956438000;
			$kingdomhistory -> save();

			// Aggiornamento capitale.

			$sql = "
			UPDATE `regions`
			SET `kingdom_id` = ( select id from kingdoms where name = 'kingdoms.{$newkingdomname}'),
			`capital` = '1',
			`updatemap` = '1'
			WHERE `regions`.`name` = '{$capitalname}'";

			Database::instance() -> query ($sql);

			// aggiungo palazzo reale

			$sql = "INSERT INTO `structures` (`id`, `parent_structure_id`, `structure_type_id`, `region_id`, `character_id`, `state`, `start`, `end`, `history`, `customstorage`, `locked`, `attribute1`, `attribute2`, `attribute3`, `attribute4`, `attribute5`, `attribute6`, `size`, `description`, `image`, `message`) VALUES (NULL, NULL, 7, (select id from regions where name = '{$capitalname}'), NULL, 100.00, NULL, NULL, NULL, NULL, 0, '', NULL, NULL, NULL, NULL, NULL, '', '', '', '')";

			// linka il castello al palazzo reale

			Database::instance() -> query ($sql);

			$royalpalace = $capital -> get_structure('royalpalace');

			$castle -> parent_structure_id = $royalpalace -> id;
			$castle -> save();

			// configurazione tasse

			$sql = "
			INSERT INTO `taxes` (`id` ,`tag` ,`type` ,`region_id` ,`kingdom_id` ,`name` ,`description` ,`value`)
			VALUES (NULL , 'kingdom_property', 'kingdom', NULL , ( select id from kingdoms where name = 'kingdoms.{$newkingdomname}'), 'taxes.kingdom_property', 'taxes.kingdom_property_desc', '75')";

			Database::instance() -> query ($sql);

			$sql = "INSERT INTO `taxes` (`id` ,`tag` ,`type` ,`region_id` ,`kingdom_id` ,`name` ,`description` ,`value`)
			VALUES (NULL , 'kingdom_selling', 'kingdom', NULL , ( select id from kingdoms where name = 'kingdoms.{$newkingdomname}'), 'taxes.kingdom_selling', 'taxes.kingdom_selling_desc', '75')";

			Database::instance() -> query ($sql);

			$sql = "INSERT INTO `kingdom_taxes` (`kingdom_id`, `name`, `hostile`, `neutral`, `friendly`, `allied`, `citizen`) VALUES (( select id from kingdoms where name = 'kingdoms.{$newkingdomname}'), 'distributiontax', 50, 50, 50, 50, 100)";

			Database::instance() -> query ($sql);

			// Crown King

			$kingdom = ORM::factory('kingdom') -> where
				('name', 'kingdoms.' . $newkingdomname ) -> find();

			// configurazione diplomacy_relations

			$sql = "SELECT *
			FROM kingdoms_v
			WHERE name != 'kingdoms.{$newkingdomname}'";

			$res = Database::instance() -> query($sql);

			foreach ($res as $r)
			{

				Database::instance() -> query("
				INSERT INTO diplomacy_relations VALUES
				( null, {$kingdom -> id}, {$r -> id}, 'neutral', NULL, unix_timestamp(), 1)");

				Database::instance() -> query("
				INSERT INTO diplomacy_relations VALUES
				( null, {$r -> id}, {$kingdom -> id}, 'neutral', NULL, unix_timestamp(), 1)");

			}

			Character_Event_Model::addrecord(
				1,
				'announcement',
				'__kingdoms.newkingdom' .
				';__' . 'kingdoms.' . $newkingdomname,
				'evidence'
			);

			$kingdom -> crown_king($kingchar);


		} catch (Kohana_Database_Exception $e)
		{
			kohana::log('error', kohana::debug( $e -> getMessage() ));
			kohana::log('error', 'An error occurred during Kingdom Split, rollbacking everything.');
			Database::instance() -> query("rollback");
			return false;
		}

		Database::instance()->query("set autocommit = 1");
	}

	/**
	* Manda avvisi per scadenza basic package
	* @param string $securitykey SecurityKEY
	* @return none
	*/

	function checkpremiumexpiration( $securitykey )
	{

		if ( $securitykey != self::SECURITYKEY )
		{
			kohana::log('error', "-> securitykey $securitykey is not correct.");
			return;
		}

		$sql = "
		select c.id character_id, u.id user_id,
			c.name character_name,
			(cb.endtime - unix_timestamp()) secs,
			round((cb.endtime - unix_timestamp())/(24*3600)) days
		from character_premiumbonuses cb, characters c, cfgpremiumbonuses cp, users u
		where cb.targetuser_id = u.id
		and   c.user_id = u.id
		and   c.type = 'pc'
		and   cb.cfgpremiumbonus_id = cp.id
		and   cp.name = 'ipcheckshield'
		and
		round((cb.endtime - unix_timestamp())/(24*3600),0 ) <= 5
    and
		round((cb.endtime - unix_timestamp())/(24*3600),0 ) > 0";

		$rset = Database::instance() -> query ( $sql );
		$subject = 'Package: IP Check Shield is expiring';

		if ( count ( $rset ) > 0 )
			foreach ( $rset as $r )
			{

				$body = "Dear {$r->character_name}, the IP Check Shield Package will expire in: "
				. Utility_Model::secs2hmstostring( $r -> secs, 'hours' )
				. ". Please extend it in the bonus page before it expires, otherwise the IP shared protection and the other related bonuses will be no more valid.";

				kohana::log('info', "-> Sending Premium Package Warning Email to: " . $r -> character_name );

				Character_Event_Model::addrecord(
					$r -> character_id,
					'normal',
					'__events.basicpackageexpires;' . Utility_Model::secs2hmstostring( $r -> secs ),
					'evidence' );


				Utility_Model::send_notification( $r -> user_id, $subject, $body );

			}

	}

	/**
	* Mergia 2 regni
	* @params string $securitykey key
	* @param string $kingdomsourcename Kingdom Source Name
	* @param string $kingdomtargetname Kingdom Target Name
	* @param string $newkingdomname New Kingdom Name
	* @param string $newkingdomimage New Kingdom heraldry Image
	* @return none
	**/

	public static function mergekingdoms($securitykey=null, $kingdomsourcename, $kingdomtargetname, $newkingdomname = null, $newkingdomimage = null)
	{

		if ( $securitykey != self::SECURITYKEY )
		{
			kohana::log('error', "securitykey $securitykey is not correct.");
			return;
		}

		kohana::log('info', "attempting to merge kingdoms, params: " . $securitykey . " | " . $kingdomsourcename . " | " . $kingdomtargetname . " | " . $newkingdomname . " | " . $newkingdomimage);


		if ( is_null( $newkingdomname) )
			$newkingdomname = $kingdomtargetname;

		Database::instance() -> query("set autocommit = 0");
		Database::instance() -> query("start transaction");
		Database::instance() -> query("begin");

		try {

			$kingdomsource = ORM::factory( 'kingdom' ) -> where ( 'name', $kingdomsourcename ) -> find();
			$kingdomtarget = ORM::factory( 'kingdom' ) -> where ( 'name', $kingdomtargetname ) -> find();

			kohana::log('info', '-> MergeKingdoms - Kingdom Source: ' . $kingdomsource -> name );
			kohana::log('info', '-> MergeKingdoms - Kingdom Target: ' . $kingdomtarget -> name );

			$royalpalacesource = $kingdomsource -> get_structure('royalpalace');
			$royalpalacetarget = $kingdomtarget -> get_structure('royalpalace');

			kohana::log('info', '-> MergeKingdoms- Royalpalacesource: ' . $royalpalacesource -> id );
			kohana::log('info', '-> MergeKingdoms- Royalpalacetarget: ' . $royalpalacetarget -> id );

			// sposta le strutture che puntavano al palazzo reale source su target

			kohana::log('info', '-> MergeKingdoms: Linking structures to new Royal Palace...');

			Database::instance() -> query (
				"update structures set parent_structure_id = " .
				$royalpalacetarget -> id . "
				where parent_structure_id =  " . $royalpalacesource -> id );

			// rimuovi il Re.

			kohana::log('info', '-> MergeKingdoms: Dethroning King');

			$kingdomsource -> dethrone_king();

			// distruggi il palazzo reale

			kohana::log('info', '-> MergeKingdoms: Destroying Royal Palace...');

			$royalpalacesource -> destroy();

			// rimuovi tutte le relazioni diplomatiche del regno rimosso

			kohana::log('info', '-> MergeKingdoms: Removing All Diplomatic Relations...');

			Database::instance() -> query("delete from diplomacy_relations where sourcekingdom_id = " . $kingdomsource -> id . " or
			targetkingdom_id = " . $kingdomsource -> id );

			// sposta le

			Database::instance() -> query ("update kingdom_forum_boards set kingdom_id = {$kingdomtarget -> id} where kingdom_id = {$kingdomsource -> id}");

			// rimuovi il regno

			kohana::log('info', '-> MergeKingdoms: Cleaning up db...');
			kohana::log('info', '-> MergeKingdoms: Cleaning up kingdom taxes...');
			Database::instance() -> query("delete from kingdom_taxes where kingdom_id = " . $kingdomsource -> id );
			kohana::log('info', '-> MergeKingdoms: Cleaning up laws...');
			Database::instance() -> query("delete from laws where kingdom_id = " . $kingdomsource -> id );
			kohana::log('info', '-> MergeKingdoms: Cleaning up boardmessages...');
			Database::instance() -> query("delete from boardmessages where kingdom_id = " . $kingdomsource -> id );
			kohana::log('info', '-> MergeKingdoms: Cleaning up taxes...');
			Database::instance() -> query("delete from taxes where type = 'kingdom' and kingdom_id = " . $kingdomsource -> id );
			kohana::log('info', '-> MergeKingdoms: Cleaning up kingdom...');
			//Database::instance() -> query("update kingdoms_history set end = unix_timestamp() where kingdom_id = " . $kingdomsource -> id );

			// TODO: se nuovo regno, il vecchio finisce e inserire una riga per il nuovo nome

			if ( $newkingdomname != $kingdomtargetname )
			{
				kohana::log('info', '-> MergeKingdoms: 	 name...');

				$sql = "update kingdoms set status = 'deleted' where name = {$kingdomtargetname}";
				kohana::log('info', '-> sql: ' . $sql);
				Database::instance() -> query($sql);
			}

			// Muovi regioni

			kohana::log('info', '-> MergeKingdoms: Moving regions...');

			foreach ( $kingdomsource -> regions as $region )
			{
				$region -> kingdom_id = $kingdomtarget -> id;
				$region -> updatemap = true;
				$region -> capital = false;
				$region -> save();
			}

			// Evento

			kohana::log('info', '-> MergeKingdoms: Adding event...');

			Database::instance() -> query("INSERT INTO `boardmessages` VALUES (null, 0, 1,
				'europecrier', 'published', 180, 0, 'global', '',
				NULL, unix_timestamp(),
				'__events.kingdomsmerge;__" . $kingdomsourcename . ";__" . $kingdomtargetname . ";__" . $newkingdomname . "',
				'evidence', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL)");

			// refresh cache

			$cachetag = '-diplomacyrelations';
			My_Cache_Model::delete( $cachetag );
			$cachetag = '-cfg-regions';
			My_Cache_Model::delete( $cachetag );


		} catch (Kohana_Database_Exception $e)
		{
			kohana::log('error', kohana::debug( $e->getMessage() ));
			kohana::log('error', 'An error occurred during Kingdom Merge, rollbacking everything.');
			Database::instance() -> query("rollback");
			return false;
		}

		Database::instance()->query("set autocommit = 1");
	}

	/**
	* Computes Kingdom Activity
	* @params string $securitykey key
	* @returns none
	*/

	public static function computekingdomsactivity($securitykey=null)
	{

		if ( $securitykey != self::SECURITYKEY )
		{
			kohana::log('error', "securitykey $securitykey is not correct.");
			return;
		}

		$data = array();
		$kingdoms = Database::instance() -> query("
		select count(c.id) citizens, k.name, k.id
		from kingdoms k, characters c, regions r
		where k.id = r.kingdom_id
		and   r.id = c.region_id
		and   c.type = 'pc'
		and   c.birthdate < unix_timestamp() - ( 30 * 24 * 3600 )
		group by k.name,k.id");


		foreach ( $kingdoms as $kingdom )
		{
			//var_dump("-> Analyzing Kingdom: " . $kingdom -> name );

			$data[$kingdom->id]['name'] = $kingdom -> name;
			$data[$kingdom->id]['citizens'] = $kingdom -> citizens;

			// sent messages

			$messages = Database::instance() -> query("
			select count(m.id) c
			from messages m, characters c, regions r, kingdoms k
			where m.char_id = c.id
			and   c.region_id = r.id
			and   c.type = 'pc'
			and   c.birthdate < unix_timestamp() - ( 30 * 24 * 3600 )
			and   k.id = r.kingdom_id
			and   k.id = {$kingdom -> id} ");

			$data[$kingdom -> id]['messages'] = $messages[0] -> c ;
			$data[$kingdom -> id]['score_messages'] = $messages[0] -> c / $data[$kingdom -> id]['citizens'];

			// actions done
			$actions = Database::instance() -> query(
			"select count(ca.id) c
			from character_actions ca, characters c, regions r, kingdoms k
			where ca.character_id = c.id
			and   c.region_id = r.id
			and   c.type = 'pc'
			and   ca.status = 'completed'
			and   k.id = r.kingdom_id
			and   k.id = {$kingdom -> id}");

			$data[$kingdom -> id]['actions'] = $actions[0] -> c ;
			$data[$kingdom -> id]['score_actions'] = $actions[0] -> c / $data[$kingdom -> id]['citizens'] ;

			// logins

			$logins = Database::instance() -> query(
			"select count(tu.id) c
			from  trace_user_logins tu, users u, characters c, regions r, kingdoms k
			where tu.user_id = u.id
			and   u.id = c.user_id
			and   c.type = 'pc'
			and   c.birthdate < unix_timestamp() - ( 30 * 24 * 3600 )
			and   c.region_id = r.id
			and   r.kingdom_id = k.id
			and   k.id = {$kingdom -> id}");

			$data[$kingdom -> id]['logins'] = $logins[0] -> c ;
			$data[$kingdom -> id]['score_logins'] = $logins[0] -> c / $data[$kingdom -> id]['citizens'] ;


		}
		//var_dump( date("h:i:s", time()) . "-> Computing Max and Mins.");
		$_a = $data;

		$maxmin['score_messagesmin'] = 9999;
		$maxmin['score_messagesmax'] = -9999;
		$maxmin['score_actionsmin'] = 9999;
		$maxmin['score_actionsmax'] = -9999;
		$maxmin['score_loginsmin'] = 9999;
		$maxmin['score_loginsmax'] = -9999;

		foreach ( $_a as $key => $row )
		{
			if ( $row['score_messages'] >= $maxmin['score_messagesmax'] )
					$maxmin['score_messagesmax'] = $row['score_messages'];
			if ( $row['score_messages'] <= $maxmin['score_messagesmin'] )
					$maxmin['score_messagesmin'] = $row['score_messages'];
			if ( $row['score_actions'] >= $maxmin['score_actionsmax'] )
					$maxmin['score_actionsmax'] = $row['score_actions'];
			if ( $row['score_actions'] <= $maxmin['score_actionsmin'] )
					$maxmin['score_actionsmin'] = $row['score_actions'];
			if ( $row['score_logins'] >= $maxmin['score_loginsmax'] )
					$maxmin['score_loginsmax'] = $row['score_logins'];
			if ( $row['score_logins'] <= $maxmin['score_loginsmin'] )
					$maxmin['score_loginsmin'] = $row['score_logins'];
		}
		//var_dump( date("h:i:s", time()) . "-> Normalizing.");

		foreach ( $_a as $key => &$row )
		{
			//var_dump('-> Normalizing ' . $row['name'] );
			if ( $maxmin['score_messagesmax'] == $maxmin['score_messagesmin'] )
				$row['score_messages_normalized'] = 0.5;
			else
				$row['score_messages_normalized'] =
					  ($row['score_messages'] - $maxmin['score_messagesmin']) / ($maxmin['score_messagesmax'] - $maxmin['score_messagesmin']);

			if ( $maxmin['score_actionsmax'] == $maxmin['score_actionsmin'] )
				$row['score_actions_normalized'] = 0.5;
			else
				$row['score_actions_normalized'] =
					  ($row['score_actions'] - $maxmin['score_actionsmin']) / ($maxmin['score_actionsmax'] - $maxmin['score_actionsmin']);

			if ( $maxmin['score_loginsmax'] == $maxmin['score_loginsmin'] )
				$row['score_logins_normalized'] = 0.5;
			else
				$row['score_logins_normalized'] =
					  ($row['score_logins'] - $maxmin['score_loginsmin']) / ($maxmin['score_loginsmax'] - $maxmin['score_loginsmin']);

			if ( $maxmin['score_activitypointsmax'] == $maxmin['score_activitypointsmin'] )
				$row['score_activitypoints_normalized'] = 0.5;
			else
				$row['score_activitypoints_normalized'] =
					  ($row['score_activitypoints'] - $maxmin['score_activitypointsmin']) / ($maxmin['score_activitypointsmax'] - $maxmin['score_activitypointsmin']);

			$row['score_total'] = (string) $row['score_messages_normalized'] + $row['score_actions_normalized'] + $row['score_logins_normalized'] +
				$row['score_activitypoints'];

			//var_dump("-> Total Score: " . $row['score_total']);

			$score_total = str_replace(",",".", $row['score_total']);

			Database::instance() -> query("update kingdoms set activityscore = {$score_total} where id =
			{$key}");

		}

	}

	/**
	* Give Referral Coins
	* @params string $securitykey key
	* @returns none
	*/

	static public function givereferralcoins($securitykey=null)
	{

		if ( $securitykey != self::SECURITYKEY )
		{
			kohana::log('error', "securitykey $securitykey is not correct.");
			return;
		}

		$referrals = array();
		$referralsstats = array();

		$sql = "
			select u.user_id referrer_user_id, u.referred_id referred_user_id, c1.id referrer_character_id, c1.name referrer_name, c2.id referred_character_id, c2.name referred_name
			from user_referrals u, characters c1, characters c2
			where c1.user_id = u.user_id
			and   c2.user_id = u.referred_id
			order by u.user_id asc
			";

		$rset = Database::instance() -> query( $sql );

		if ( count ( $rset ) > 0 )
		{
			foreach ( $rset as $referralrow )
			{

				kohana::log( 'debug', '-> givereferralcoins-Referrer: ' . $referralrow -> referrer_name  );
				$referred = ORM::factory('character', $referralrow -> referred_character_id );
				$referredage = $referred -> get_age();
				kohana::log( 'debug', '-> Referral: ' . $referralrow -> referred_name . ', age: ' . $referredage );

				if ( $referred -> loaded == false )
				{
					kohana::log( 'debug', '-> givereferralcoins-Referred: ' . $referralrow -> referred_name . ' is not existing skipping.' );
					continue;
				}

				if ( $referred -> is_meditating( $referred -> id ) == true )
				{
					kohana::log( 'debug', '-> givereferralcoins-Referred: ' . $referralrow -> referred_name . ' is in meditation, skipping.' );
					continue;
				}

				if ( $referredage  < 30 )
				{
					kohana::log( 'debug', '-> givereferralcoins-Referred: ' . $referralrow -> referred_name . ' is too young, skipping.' );
					continue;
				}

				if ( $referredage  >= 30  and $referredage  < 60)
				{
					kohana::log( 'debug', '-> givereferralcoins-Adding 0.25 for Referral: ' . $referralrow -> referred_name  );
					$referralsstats[$referralrow -> referrer_user_id][$referralrow -> referred_user_id] += 1;
					$referrals[$referralrow -> referrer_character_id]['total'] += 1;
				}

				if ( $referredage  >= 60  and $referredage  < 360)
				{
					kohana::log( 'debug', '-> givereferralcoins-Adding 0.5 for Referral: ' . $referralrow -> referred_name  );
					$referralsstats[$referralrow -> referrer_user_id][$referralrow -> referred_user_id] += 2.5;
					$referrals[$referralrow -> referrer_character_id]['total'] += 2.5;
				}

				if ( $referredage  >= 360 )
				{
					kohana::log( 'debug', '-> givereferralcoins-Adding 1 for Referral: ' . $referralrow -> referred_name  );
					$referralsstats[$referralrow -> referrer_user_id][$referralrow -> referred_user_id] += 5;
					$referrals[$referralrow -> referrer_character_id]['total'] += 5;
				}

			}

			// diamo i soldi.

			foreach ( $referrals as $key => $value )
			{
				if ( $value['total'] > 0 )
				{
					// giving coins
					$referral = ORM::factory('character', $key );
					kohana::log( 'info', ">>>> givereferralcoins-Giving a total of {$value['total']} s.c. to:" . $referral -> name );

					$referral -> modify_coins( $value['total'], 'referralbonus' );



					// evento

					Character_Event_Model::addrecord(
						$referral -> id, 'normal', '__events.referralcoinsgiven;' . $value['total'] );
				}
			}

			// stats

			foreach ( $referralsstats as $referrer_id => $referred )
			{
				foreach ( $referred as $referred_id => $contribute )
				{
					if ( $contribute > 0 )
					{
						kohana::log('debug', "givereferralcoins-Saving statistic: $contribute for referrer: $referrer_id  and referred: $referred_id. ");
						$stat = ORM::factory('user_referral') ->
							where( array(
								'user_id' => $referrer_id,
								'referred_id' => $referred_id ) ) -> find();

						if ( !$stat -> loaded )
						{
							$stat -> user_id = $referrer_id ;
							$stat -> referred_id = $referred_id ;
							$stat -> coins = $contribute ;
							$stat -> save;
						}
						else
						{
							$stat -> coins += $contribute;
							$stat -> save();

						}
					}
				}
			}
		}
	}

	/**
	* Replenish Basic Resources
	* @params string $securitykey key
	* @returns none
	*/

	static function rechargebasicresources($securitykey=null)
	{

		if ( $securitykey != self::SECURITYKEY )
		{
			kohana::log('error', "securitykey [$securitykey] is not correct.");
			return;
		}

		kohana::log('info', '-> ----- RECHARGING RESOURCES -----');

		// seleziono tutte le strutture in un regno conquistato che hanno
		// qualche risorsa da ricaricare

		$sql = "
		select s.id, r.id region_id, sr.resource, sr.current, sr.max, r.kingdom_id, k.name kingdom_name, r.name region_name, from_unixtime(sr.next_recharge)
		from structures s, structure_types st, structure_resources sr, regions r, kingdoms k
		where s.structure_type_id = st.id
		and sr.structure_id = s.id
		and s.region_id = r.id
		and r.kingdom_id = k.id
		and sr.current < sr.max
		and sr.next_recharge < unix_timestamp()
		and st.parenttype in (
			'forest',
			'breeding_region',
			'mine',
			'fish_shoal'
		)
		";

		// trovo tutte le risorse che sono in una regione con un castello che ha il trofro dump.

		$resources = Database::instance() -> query( $sql );

		$sql2 = "
		select s.id
		from structures s, structure_types st
		where s.structure_type_id = st.id
		and st.parenttype in
		(
			'forest',
			'breeding_region',
			'mine',
			'fish_shoal'
		)
		and  s.region_id in
		(
			select s.region_id
			from items i, cfgitems ci, structures s, structure_types st
			where s.structure_type_id = st.id
 			and st.parenttype = 'castle'
			and i.structure_id = s.id
			and i.cfgitem_id = ci.id
			and ci.tag = 'dumpscavengertrophy'
		)";

		$rset = Database::instance() -> query( $sql2 );

		$resourcestotopup = array();
		foreach( $rset as $row)
		{
			$resourcestotopup[ $row -> id ] = $row -> id;
		}


		// Find active characters

		$sqlchars = "
		select u.id
		from characters c, users u
		where c.user_id = u.id
		and  c.type = 'pc'
		and (unix_timestamp() - u.last_login ) < (2*24*3600)
		";

		$rsetchars = Database::instance() -> query( $sqlchars ) -> as_array();
		$activechars = count($rsetchars);
		kohana::log('info', "-> Active chars in last two days: {$activechars}");
		$recharge = min(50, max(5, $activechars / 110));
		kohana::log('info', "-> Recharging perc.: {$recharge}%");

		foreach ( $resources as $resource )
		{

			$next_recharge = mktime( mt_rand(0,23),mt_rand(0,59),mt_rand(0,59), date('m'), date('d')+1, date('y') );

			if ( in_array( $resource -> id, $resourcestotopup ) )
			{

				Database::instance() -> query( "update structure_resources
				set current = max,
				next_recharge = {$next_recharge}
				where structure_id = " . $resource -> id );
				kohana::log('info', "-> Topped up Resource {$resource -> resource} of Region: {$resource -> region_name}");

			}
			else
			{


				$quantity = round($resource -> max * $recharge / 100, 0);

				if ( $resource -> current + $quantity > $resource -> max )
					$quantity = ( $resource -> max - $resource -> current );

				Database::instance() -> query (
				"	update structure_resources
					set current = current + {$quantity},
					next_recharge = {$next_recharge}
					where structure_id = {$resource -> id}
					and resource = '{$resource -> resource}'");

				kohana::log('info', "-> + {$quantity} for Resource {$resource -> resource} of Region: {$resource -> region_name}");

			}
		}

		// Cache
		My_Cache_Model::delete('-cfg-regions-resources');

		kohana::log('info', '-> ------ END RECHARGING RESOURCES ------');

	}

	function multi($daysago=1)
	{


		$sql = "select count( distinct c.id) c, tul.logincookie
			from characters c, trace_user_logins tul
			where tul.user_id = c.user_id
			and   logincookie is not null
			and   logintime > SUBDATE(CURDATE(),{$daysago})
			group by  tul.logincookie
			having count(distinct c.id) > 3";

		$sharedcookies = Database::instance() -> query($sql);
		foreach ($sharedcookies as $sharedcookie)
		{

			$multies = Database::instance() -> query (
				"select c.id, c.name, u.username, u.email, tul.ipaddress, tul.logincookie, from_unixtime( tul.logintime ) logintime
				 from characters c, users u, trace_user_logins tul
				 where c.user_id = u.id
				 and u.id = tul.user_id
				 and tul.logincookie = '{$sharedcookie -> logincookie}'");

			print "charid;charname;username;email;ipaddress;logincookie;logintime\r\n";
			foreach ($multies as $multi)
				print $multi -> id.";".$multi -> name.";".$multi ->username.";".$multi->email.
					";".$multi->ipaddress.";".$multi->	logincookie.";".$multi->logintime."\r\n";
		}
	}
}
