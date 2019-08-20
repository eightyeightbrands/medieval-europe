<?php defined('SYSPATH') OR die('No direct access allowed.');

class Stats_Model
{

protected $limit = 10000;
protected $yest_ranking = array();

/**
* Load character titles
* @param none
* @return none
*/

static function get_charactertitles()
{
	
	$cachetag = '-charactertitles' ;		
	$cfg = My_Cache_Model::get( $cachetag );
			
	if ( is_null( $cfg ) )		
	{
		
		$db = Database::instance();			
		$sql = "select * 
		from character_titles cr, cfgachievements ca
		where cr.cfgachievement_id = ca.id ";
		$res = $db -> query( $sql ) -> as_array();								
		foreach ($res as $row)
			$cfg[$row -> character_id ][$row -> name][$row -> stars] = $row ;
		
		My_Cache_Model::set( $cachetag, $cfg );
	}
	
	return $cfg;
	
}
	
/**
* Compute all stats
* @param none
* @return none
*/

function compute_all_stats()
{
	
	kohana::log('info', '-> Computing all stats...');
	var_dump("-> Computing all data...");
	$rset = Database::instance() -> query ( "select * from stats_globals where date(from_unixtime(extractiontime)) = date_sub( current_date, interval 1 day )");		
	
	foreach ( $rset as $row )
		$this -> yest_rankings[$row -> type][$row -> stats_id] = $row;
	
	// cancello i dati di 2 gg fa e di oggi

	kohana::log('info', '-> Removing old data...');	
	var_dump("-> Removing old data...");
	
	Database::instance() -> query( "delete from stats_globals where date(from_unixtime(extractiontime)) = date_sub( current_date, interval 0 day)");	
	Database::instance() -> query("delete from stats_globals where date(from_unixtime(extractiontime)) <= date_sub( current_date, interval 2 day)");
		
	var_dump("-> Computing statistics: BEGIN");
		
	$this -> compute_activekingdoms();
	$this -> compute_populatedkingdoms();
	$this -> compute_richestkingdoms();
	$this -> compute_bestdonors();
	$this -> compute_richestchars();	
	$this -> compute_bestduelists();
	$this -> compute_oldestchars();
	$this -> compute_fightstats();
	$this -> compute_battlechampion();
	$this -> compute_manhunters();
	$this -> compute_mostreligious();
	$this -> compute_mostcharitable();
	$this -> compute_raiderskingdoms();
	$this -> compute_raidedkingdoms();
	$this -> compute_richestcities();
	$this -> compute_populatedcities();
	$this -> compute_mostfollowedchurch();
	$this -> compute_mosthonorable();	
	$this -> compute_gamescore();	
	
	var_dump("-> Computing statistics: END");
	
}


function save_stats( $data, $computetitle = false )
{
	
	foreach ($data as $d )
	{
		
		Database::instance() -> query( 
			"insert into stats_globals ( 
			stats_id, 
			stats_label, 
			target,
			prevposition,
			position,
			type, 
			value,
			entity,
			param1,
			param2,
			extractiontime )
			values ( " . 
				$d['stats_id'] . "," . 
				$d['stats_label'] .", '" .
				$d['target'] ."', " .
				$d['prevposition'] . ", " . 
				$d['position'] . ", '" .
				$d['type'] . "', " . 
				$d['value'] . ", '" .
				$d['entity'] . "', '" .
				$d['param1'] . "', '" .
				$d['param2'] . "', " . 
				" unix_timestamp()  )" ) or die ( mysql_error() );
	
		if ( $computetitle  and $d['target'] == 'player' )
		{			
			Achievement_Model::compute_achievement(
				'stat_' . $d['type'],
				$d['valuefortitle'],
				$d['stats_id'],
				$d['param1fortitle']
			);
		}
		
		if ( $computetitle  and $d['target'] == 'kingdom' )
		{			
			if ($d['position'] <= 3 )
			{
				Database::instance() -> query ("
				update kingdom_titles 
				set current = 'N' 
				where kingdom_id = {$d['stats_id']}
				and   cfgachievement_id in 
				(select id from cfgachievements where type = 'kingdom' and tag = 'stat_{$d['type']}')");
				
				Database::instance() -> query ("
				replace into kingdom_titles
				( cfgachievement_id, kingdom_id, stars, position, timestamp, current )
				values 
				( (select id from cfgachievements where type = 'kingdom' and tag = 'stat_{$d['type']}' and level = {$d['position']}), {$d['stats_id']}, {$d['position']}, 1,now(), 'Y')");
			}
		}
			
		My_Cache_Model::delete('-allrankings');		
		
	}
}

function compute_gamescore()
{
	kohana::log('info', '-> Computing Game Scores');		
	$data = array();
	$type = 'gamescore';
	
	$sql = "select c.id character_id, c.name character_name, c.score
	from characters c
	where c.type = 'pc' 
	order by c.score desc limit " . $this -> limit ;
	
	$rset = Database::instance() -> query( $sql );
	$totalitems = $rset -> count();
	$i = 1;
	
	foreach ( $rset as $row )
	{
		if (  ! key_exists( $type, (array) $this -> yest_rankings ) )
			$prevposition = 999999;	
		else
		$prevposition = key_exists( $row -> character_id, (array) $this -> yest_rankings[$type]) ? $this -> yest_rankings[$type][$row -> character_id] -> position : '999999' ;
		
		
		$data[$i]['stats_id'] = $row -> character_id ;
		$data[$i]['stats_label'] = Database::instance() -> escape( $row -> character_name );
		$data[$i]['target'] = 'player';
		$data[$i]['prevposition'] = $prevposition;		
		$data[$i]['position'] = $i;		
		$data[$i]['type'] = $type;
		$data[$i]['value'] = $row -> score;
		$data[$i]['entity'] = 'rankings.ranking_gamescorepoints';
		$data[$i]['param1'] = '';
		$data[$i]['param2'] = '';
		$data[$i]['valuefortitle'] = '';
		
		$i++;
	}
		
	$this -> save_stats( $data, false );
	
}

function compute_richestchars()
{
	kohana::log('info', '-> Computing Game Richest Chars...');		
	$data = array();
	$type = 'richestchars';
	
	$sql = "select sum(quantity) coins, 
		c.name character_name, c.id character_id, round(( unix_timestamp() - 	c.birthdate ) / (24 * 3600 )) age
	from items i, characters c
	where cfgitem_id = ( select id from cfgitems c where tag='silvercoin' )
	and c.type = 'pc' 
	and 
	(
	c.id = i.character_id 
	or i.structure_id in ( select id from structures where character_id = c.id  and 
	structure_type_id in ( select id from structure_types where subtype in ( 'player' ) ) )
	)
	group by c.name
	order by coins desc limit 0, " . $this -> limit ;

	$rset = Database::instance() -> query( $sql );
	
	$i = 1;
	foreach ( $rset as $row )
	{
		if (  ! key_exists( $type, (array) $this -> yest_rankings ) )
			$prevposition = 999999;	
		else
			$prevposition = key_exists( $row -> character_id, (array) $this -> yest_rankings[$type]) ? $this -> yest_rankings[$type][$row -> character_id] -> position : '999999' ;
		
		$data[$i]['stats_id'] = $row -> character_id ;
		$data[$i]['stats_label'] = Database::instance() -> escape( $row -> character_name );
		$data[$i]['target'] = 'player';
		$data[$i]['prevposition'] = $prevposition;		
		$data[$i]['position'] = $i;		
		$data[$i]['type'] = $type;
		$data[$i]['value'] = $row -> coins;
		$data[$i]['entity'] = 'global.coins';
		$data[$i]['param1'] = $row -> age;
		$data[$i]['param2'] = '';
		$data[$i]['valuefortitle'] = '';
		
		$i++;
	}
		
	$this -> save_stats( $data, false );
	
}

function compute_bestduelists()
{
	kohana::log('info', '-> Computing Best Duelist');			
	$data = array();
	$type = 'bestduelist';
	
	$sql = "select c.name character_name, cs.* 
	from character_stats cs, characters c 
	where c.id = cs.character_id 
	and c.type = 'pc' 
	and cs.name = 'duelscore' order by cs.value desc limit " . $this -> limit ;
	
	$rset = Database::instance() -> query( $sql );
	$totalitems = $rset -> count();
	$i = 1;
	foreach ( $rset as $row )
	{
		if (  ! key_exists( $type, (array) $this -> yest_rankings ) )
			$prevposition = 999999;	
		else
		$prevposition = key_exists( $row -> character_id, (array) $this -> yest_rankings[$type]) ? $this -> yest_rankings[$type][$row -> character_id] -> position : '999999' ;
		
		$data[$i]['stats_id'] = $row -> character_id ;
		$data[$i]['stats_label'] = Database::instance() -> escape( $row -> character_name );
		$data[$i]['target'] = 'player';
		$data[$i]['prevposition'] = $prevposition;		
		$data[$i]['position'] = $i;		
		$data[$i]['type'] = $type;
		$data[$i]['value'] = $row -> value;
		$data[$i]['entity'] = 'rankings.ranking_duelscorepoints';
		$data[$i]['param1'] = $row -> stat1;
		$data[$i]['param2'] = $row -> stat2;
		$data[$i]['valuefortitle'] = round(($totalitems - $i + 1 ) * 100/$totalitems,0);
		
		$i++;
	}
	
	kohana::log('info', '-> Computing Best Duelist: Saving stats...');			
	
	$this -> save_stats( $data, true );
}

function compute_oldestchars()
{
	kohana::log('info', '-> Computing Oldest Chars');			
	$data = array();
	$type = 'oldestchars';
	
	$sql = "
			SELECT c.birthdate age , c.name character_name, c.id character_id
			FROM characters c, users u			
			WHERE u.id = c.user_id
			and c.type = 'pc' 
			ORDER BY age asc limit " . $this -> limit ;
	
	$rset = Database::instance() -> query( $sql );
	
	$i = 1;
	foreach ( $rset as $row )
	{
		if (  ! key_exists( $type, (array) $this -> yest_rankings ) )
			$prevposition = 999999;	
		else
		$prevposition = key_exists( $row -> character_id, (array) $this -> yest_rankings[$type]) ? $this -> yest_rankings[$type][$row -> character_id] -> position : '999999' ;
		
		
		$data[$i]['stats_id'] = $row -> character_id ;
		$data[$i]['stats_label'] = Database::instance() -> escape( $row -> character_name );
		$data[$i]['target'] = 'player';
		$data[$i]['prevposition'] = $prevposition;		
		$data[$i]['position'] = $i;		
		$data[$i]['type'] = $type;
		$data[$i]['value'] = $row -> age;
		$data[$i]['entity'] = 'global.age';
		$data[$i]['param1'] = '';
		$data[$i]['param2'] = '';
		$data[$i]['valuefortitle'] = '';
		
		$i++;
	}
		
	$this -> save_stats( $data, false );
}

function compute_fightstats()
{
	kohana::log('info', '-> Computing Fight Stats');			
	$data = array();
	$type = 'fightstats';
	
	$sql = "
	select 
		c.id character_id, floor(cs.value/cs.stat1 * 100) percentage, cs.value, cs.stat1, 
		c.name character_name from character_stats cs, characters c 
	where cs.character_id = c.id 
	and c.type = 'pc' 
	and cs.name  in ( 'fightstats' ) and cs.stat1 > 0 order by cast(stat1 as unsigned) desc, cast(percentage as unsigned) desc";
	
	$rset = Database::instance() -> query( $sql );
	
	$i = 1;
	foreach ( $rset as $row )
	{
		if (  ! key_exists( $type, (array) $this -> yest_rankings ) )
			$prevposition = 999999;	
		else
		$prevposition = key_exists( $row -> character_id, (array) $this -> yest_rankings[$type]) ? $this -> yest_rankings[$type][$row -> character_id] -> position : '999999' ;
		
		
		$data[$i]['stats_id'] = $row -> character_id ;
		$data[$i]['stats_label'] = Database::instance() -> escape( $row -> character_name );
		$data[$i]['target'] = 'player';
		$data[$i]['prevposition'] = $prevposition;		
		$data[$i]['position'] = $i;		
		$data[$i]['type'] = $type;
		$data[$i]['value'] = $row -> percentage;
		$data[$i]['entity'] = 'rankings.ranking_wonbattles';
		$data[$i]['param1'] = $row -> value;
		$data[$i]['param2'] = $row -> stat1;
		$data[$i]['valuefortitle'] = $row -> stat1;
		$data[$i]['param1fortitle'] = $row -> percentage;
		
		$i++;
	}
		
	$this -> save_stats( $data, true );
	
	
}

function compute_battlechampion()
{
	kohana::log('info', '-> Computing Battle Champions');			
	$data = array();
	$type = 'battlechampion';
	
	$sql = "
		select c.id character_id, cs.value, cs.stat1, 
		c.name character_name 
		from character_stats cs, characters c 
		where cs.character_id = c.id 
		and c.type = 'pc' 
		and cs.name  in ( 'battlechampion' ) 
		order by value desc";
	
	$rset = Database::instance() -> query( $sql );
	
	$i = 1;
	foreach ( $rset as $row )
	{
		if (  ! key_exists( $type, (array) $this -> yest_rankings ) )
			$prevposition = 999999;	
		else
		$prevposition = key_exists( $row -> character_id, (array) $this -> yest_rankings[$type]) ? $this -> yest_rankings[$type][$row -> character_id] -> position : '999999' ;
		
		
		$data[$i]['stats_id'] = $row -> character_id ;
		$data[$i]['stats_label'] = Database::instance() -> escape( $row -> character_name );
		$data[$i]['target'] = 'player';
		$data[$i]['prevposition'] = $prevposition;		
		$data[$i]['position'] = $i;		
		$data[$i]['type'] = $type;
		$data[$i]['value'] = $row -> value;
		$data[$i]['entity'] = 'rankings.ranking_battlechampion';
		$data[$i]['param1'] = '';
		$data[$i]['param2'] = '';
		$data[$i]['valuefortitle'] = $row -> value;
		$data[$i]['param1fortitle'] = '';
		
		$i++;
	}
		
	$this -> save_stats( $data, false );
}

function compute_manhunters()
{
	kohana::log('info', '-> Computing Bounty Hunters');			
	$data = array();
	$type = 'arrests';
	
	$sql = "
	select c.id character_id, cs.value, c.name character_name 
	from character_stats cs, characters c 
	where cs.character_id = c.id 
	and c.type = 'pc' 
	and cs.name  in ( 'arrests' ) and cs.value > 0 order by cs.value desc";
	
	$rset = Database::instance() -> query( $sql );
	
	$i = 1;
	foreach ( $rset as $row )
	{
		if (  ! key_exists( $type, (array) $this -> yest_rankings ) )
			$prevposition = 999999;	
		else
		$prevposition = key_exists( $row -> character_id, (array) $this -> yest_rankings[$type]) ? $this -> yest_rankings[$type][$row -> character_id] -> position : '999999' ;
		
		
		$data[$i]['stats_id'] = $row -> character_id ;
		$data[$i]['stats_label'] = Database::instance() -> escape( $row -> character_name );
		$data[$i]['target'] = 'player';
		$data[$i]['prevposition'] = $prevposition;		
		$data[$i]['position'] = $i;		
		$data[$i]['type'] = $type;
		$data[$i]['value'] = $row -> value;
		$data[$i]['entity'] = 'rankings.ranking_arrestsdone';
		$data[$i]['param1'] = '';
		$data[$i]['param2'] = '';
		$data[$i]['valuefortitle'] = $row -> value;
		$data[$i]['param1fortitle'] = '';
		
		$i++;
	}
		
	$this -> save_stats( $data, false );
}

function compute_bestdonors()
{
	kohana::log('info', '-> Computing Best Donors');			
	$data = array();
	$type = 'boughtdoubloons';
	
	$sql = "
	select c.id character_id, c.name charname, s.value doubloons 
	from character_stats s, characters c
	where s.name = 'boughtdoubloons'
	and c.type = 'pc' 
	and   c.id = s.character_id
	order by 3 desc ";
	
	$rset = Database::instance() -> query( $sql );
	
	$i = 1;
	foreach ( $rset as $row )
	{
		if (  ! key_exists( $type, (array) $this -> yest_rankings ) )
			$prevposition = 999999;	
		else
			$prevposition = key_exists( $row -> character_id, (array) $this -> yest_rankings[$type]) ? $this -> yest_rankings[$type][$row -> character_id] -> position : '999999' ;
		
		$data[$i]['stats_id'] = $row -> character_id ;
		$data[$i]['stats_label'] = Database::instance() -> escape( $row -> charname );
		$data[$i]['target'] = 'player';
		$data[$i]['prevposition'] = $prevposition;		
		$data[$i]['position'] = $i;		
		$data[$i]['type'] = $type;
		$data[$i]['value'] = $row -> doubloons;
		$data[$i]['entity'] = 'global.doubloons';
		$data[$i]['param1'] = $row -> doubloons;
		$data[$i]['param2'] = '';
		$data[$i]['valuefortitle'] = $row -> doubloons;
		$data[$i]['param1fortitle'] = '';
		
		$i++;
	}
	
	$this -> save_stats( $data, false );
}

function compute_mostreligious()
{
	kohana::log('info', '-> Computing Most Religious');			
	$data = array();
	$type = 'fpcontribution';
	
	$sql = "select c.id character_id , c.name character_name, cs.value fp, ch.name church_name 
	from characters c, character_stats cs, churches ch
	where cs.character_id = c.id 
	and   cs.name = 'fpcontribution' 
	and   cs.param1 = c.church_id
	and c.type = 'pc' 
	and   c.church_id = ch.id 
	order by fp desc";
	
	$rset = Database::instance() -> query( $sql );
	
	$i = 1;
	foreach ( $rset as $row )
	{
		if (  ! key_exists( $type, (array) $this -> yest_rankings ) )
			$prevposition = 999999;	
		else
		$prevposition = key_exists( $row -> character_id, (array) $this -> yest_rankings[$type]) ? $this -> yest_rankings[$type][$row -> character_id] -> position : '999999' ;		
		
		$data[$i]['stats_id'] = $row -> character_id ;
		$data[$i]['stats_label'] = Database::instance() -> escape( $row -> character_name );
		$data[$i]['target'] = 'player';
		$data[$i]['prevposition'] = $prevposition;		
		$data[$i]['position'] = $i;		
		$data[$i]['type'] = $type;
		$data[$i]['value'] = $row -> fp;
		$data[$i]['entity'] = 'rankings.ranking_afp';
		$data[$i]['param1'] = 'religion.church-' . $row -> church_name;
		$data[$i]['param2'] = '';
		$data[$i]['valuefortitle'] = $row -> fp;
		$data[$i]['param1fortitle'] = '';
		
		$i++;
	}
		
	$this -> save_stats( $data, false );
}

function compute_mostcharitable()
{
	kohana::log('info', '-> Computing Most Charitable');		
	$data = array();
	$type = 'mostcharitable';
	
	$sql = "
		select c.id character_id, cs.value, c.name character_name, ch.name church_name 
		from character_stats cs, characters c, churches ch 
		where cs.character_id = c.id 
		and cs.name in ( 'alms' ) 
		and c.church_id = ch.id 
		and c.type = 'pc' 
		and cs.param1 = c.church_id 
		and cs.value > 0 order by cs.value desc ";
	
	$rset = Database::instance() -> query( $sql );
	
	$i = 1;
	foreach ( $rset as $row )
	{
		if (  ! key_exists( $type, (array) $this -> yest_rankings ) )
			$prevposition = 999999;	
		else
		$prevposition = key_exists( $row -> character_id, (array) $this -> yest_rankings[$type]) ? $this -> yest_rankings[$type][$row -> character_id] -> position : '999999' ;		
		
		$data[$i]['stats_id'] = $row -> character_id ;
		$data[$i]['stats_label'] = Database::instance() -> escape( $row -> character_name );
		$data[$i]['target'] = 'player';
		$data[$i]['prevposition'] = $prevposition;		
		$data[$i]['position'] = $i;		
		$data[$i]['type'] = $type;
		$data[$i]['value'] = $row -> value;
		$data[$i]['entity'] = 'rankings.ranking_givenalms';
		$data[$i]['param1'] = 'religion.church-' . $row -> church_name;
		$data[$i]['param2'] = '';
		$data[$i]['valuefortitle'] = '';
		$data[$i]['param1fortitle'] = '';

		
		$i++;
	}
		
	$this -> save_stats( $data, false );
}

function compute_richestkingdoms()
{
	kohana::log('info', '-> Computing Richest Kingdoms');		
	$data = array();
	$type = 'richestkingdoms';
	
	$sql1 = "SELECT sum( quantity ) coins, k.id kingdom_id, k.name kingdom_name
		FROM items i, kingdoms_v k, structures s, regions n, structure_types st, cfgitems ci
		WHERE i.structure_id = s.id
		AND s.structure_type_id = st.id 
		AND s.region_id = n.id
		AND n.kingdom_id = k.id
		AND i.cfgitem_id = ci.id 
		AND ci.tag = 'silvercoin' 		
		GROUP by k.name  
		ORDER BY coins desc
		limit 0," . $this -> limit;

	$sql2 = "SELECT sum( quantity ) coins, k.id kingdom_id, k.name kingdom_name
		FROM items i, kingdoms_v k, cfgitems ci, characters c, regions r
		WHERE c.region_id = r.id
		AND   r.kingdom_id = k.id
		AND   i.character_id = c.id
		AND   i.cfgitem_id = ci.id 
		AND   ci.tag = 'silvercoin' 
		AND c.type = 'pc' 
		GROUP BY k.name  
		ORDER BY coins desc
		LIMIT 0," . $this -> limit;
	
	$rset1 = Database::instance() -> query( $sql1 ) -> as_array();
	$rset2 = Database::instance() -> query( $sql2 ) -> as_array();
		
	$a = array();
	
	foreach ( $rset1 as $row1 )
	{
		
		$a[$row1 -> kingdom_id]['coins'] = $row1 -> coins;
		$a[$row1 -> kingdom_id]['kingdom_name'] = $row1 -> kingdom_name;
	}
	
	foreach ( $rset2 as $row2 )
	{
		$a[$row2 -> kingdom_id]['coins'] = $row2 -> coins;
		$a[$row2 -> kingdom_id]['kingdom_name'] = $row2 -> kingdom_name;
	}
	
	$i = 1;
	foreach ( $a as $key => $value )
	{
		if (  ! key_exists( $type, (array) $this -> yest_rankings ) )
			$prevposition = 999999;	
		else
			$prevposition = key_exists( $key, (array) $this -> yest_rankings[$type]) ? $this -> yest_rankings[$type][$key] -> position : '999999' ;		
		
		$data[$i]['stats_id'] = $key ;
		$data[$i]['stats_label'] = Database::instance() -> escape( $value['kingdom_name'] );
		$data[$i]['target'] = 'kingdom';
		$data[$i]['prevposition'] = $prevposition;		
		$data[$i]['position'] = $i;		
		$data[$i]['type'] = $type;
		$data[$i]['value'] = $value['coins'];
		$data[$i]['entity'] = null;
		$data[$i]['param1'] = '';
		$data[$i]['param2'] = '';
		$data[$i]['valuefortitle'] = '';
		$data[$i]['param1fortitle'] = '';
		
		$i++;
	}
		
	$this -> save_stats( $data, true );
}

function compute_activekingdoms()
{
	kohana::log('info', '-> Computing Most Active Kingdoms');		
	$data = array();
	$type = 'activekingdoms';
	
	$sql1 = "
	SELECT name kingdom_name, activityscore, id kingdom_id
	FROM kingdoms_v 
	WHERE name != 'kingdoms.kingdom-independent' 
	ORDER BY activityscore DESC LIMIT 0," . $this -> limit;
	$rset1 = Database::instance() -> query( $sql1 ) -> as_array();	
	
	$a = array();
	
	foreach ( $rset1 as $row1 )
	{
		
		$a[$row1 -> kingdom_id]['activityscore'] = $row1 -> activityscore;
		$a[$row1 -> kingdom_id]['kingdom_name'] = $row1 -> kingdom_name;
	}
	
	$i = 1;
	foreach ( $a as $key => $value )
	{
		if (  ! key_exists( $type, (array) $this -> yest_rankings ) )
			$prevposition = 999999;	
		else
			$prevposition = key_exists( $key, (array) $this -> yest_rankings[$type]) ? $this -> yest_rankings[$type][$key] -> position : '999999' ;		
		
		$data[$i]['stats_id'] = $key ;
		$data[$i]['stats_label'] = Database::instance() -> escape( $value['kingdom_name'] );
		$data[$i]['target'] = 'kingdom';
		$data[$i]['prevposition'] = $prevposition;		
		$data[$i]['position'] = $i;		
		$data[$i]['type'] = $type;
		$data[$i]['value'] = $value['activityscore'];
		$data[$i]['entity'] = null;
		$data[$i]['param1'] = '';
		$data[$i]['param2'] = '';
		$data[$i]['valuefortitle'] = '';
		$data[$i]['param1fortitle'] = '';
		
		$i++;
	}
		
	$this -> save_stats( $data, false );
}

function compute_populatedkingdoms()
{
	kohana::log('info', '-> Computing Most Populated Kingdoms');		
	$data = array();
	$type = 'populatedkingdoms';
	
	$sql = "SELECT count(c.id) residents, k.id kingdom_id, k.name kingdom_name
		FROM characters c, kingdoms_v k, regions r
		WHERE c.region_id = r.id 
		and   r.kingdom_id = k.id 
		and c.type = 'pc' 
		group by k.id 
		order by residents desc
		limit 0, " . $this -> limit;
	
	$rset = Database::instance() -> query( $sql );
	
	$i = 1;
	foreach ( $rset as $row )
	{
		if (  ! key_exists( $type, (array) $this -> yest_rankings ) )
			$prevposition = 999999;	
		else
		$prevposition = key_exists( $row -> character_id, (array) $this -> yest_rankings[$type]) ? $this -> yest_rankings[$type][$row -> character_id] -> position : '999999' ;
		
		
		$data[$i]['stats_id'] = $row -> kingdom_id ;
		$data[$i]['stats_label'] = Database::instance() -> escape( $row -> kingdom_name );
		$data[$i]['target'] = 'kingdom';
		$data[$i]['prevposition'] = $prevposition;		
		$data[$i]['position'] = $i;		
		$data[$i]['type'] = $type;
		$data[$i]['value'] = $row -> residents;
		$data[$i]['entity'] = 'rankings.ranking_residents';
		$data[$i]['param1'] = '';
		$data[$i]['param2'] = '';
		$data[$i]['valuefortitle'] = '';
		$data[$i]['param1fortitle'] = '';

		$i++;
	}
		
	$this -> save_stats( $data, true );
}

function compute_raiderskingdoms()
{
	kohana::log('info', '-> Computing Most Raiders Kingdoms');		
	$data = array();
	$type = 'raiderskingdoms';
	
	$sql = "SELECT sum(raidedcoins) raided_coins, k.id kingdom_id, k.name kingdom_name
		FROM battles b, regions n, kingdoms_v k
		WHERE b.source_region_id = n.id 
		and n.kingdom_id = k.id
		and raidedcoins > 0 
		and b.status = 'completed' 
		group by k.id
		order by raided_coins desc
		limit 0, " . $this -> limit;
	
	$rset = Database::instance() -> query( $sql );
	
	$i = 1;
	foreach ( $rset as $row )
	{
		if (  ! key_exists( $type, (array) $this -> yest_rankings ) )
			$prevposition = 999999;	
		else
		$prevposition = key_exists( $row -> character_id, (array) $this -> yest_rankings[$type]) ? $this -> yest_rankings[$type][$row -> character_id] -> position : '999999' ;
		
		
		$data[$i]['stats_id'] = $row -> kingdom_id ;
		$data[$i]['stats_label'] = Database::instance() -> escape( $row -> kingdom_name );
		$data[$i]['target'] = 'kingdom';
		$data[$i]['prevposition'] = $prevposition;		
		$data[$i]['position'] = $i;		
		$data[$i]['type'] = $type;
		$data[$i]['value'] = $row -> raided_coins;
		$data[$i]['entity'] = 'rankings.ranking_raidedcoins';
		$data[$i]['param1'] = '';
		$data[$i]['param2'] = '';
		$data[$i]['valuefortitle'] = '';
		$data[$i]['param1fortitle'] = '';

		$i++;
	}
		
	$this -> save_stats( $data, false );
}

function compute_richestcities()
{
	kohana::log('info', '-> Computing Richest Regions');		
	$data = array();
	$type = 'richestcities';
	
	$sql = "SELECT sum( quantity ) coins, n.id region_id, n.name region_name
		FROM items i, structures s, regions n
		WHERE i.structure_id = s.id
		and s.structure_type_id in 
		( select id from structure_types where type in 
			( 'royalpalace', 'castle', 'court', 'barracks' ) )
		AND s.region_id = n.id
		AND i.cfgitem_id = ( 
		SELECT id
		FROM cfgitems
		WHERE tag = 'silvercoin' )
		GROUP BY n.name
		order by coins desc
		limit 0," . $this -> limit;
	
	$rset = Database::instance() -> query( $sql );
	
	$i = 1;
	foreach ( $rset as $row )
	{
		if (  ! key_exists( $type, (array) $this -> yest_rankings ) )
			$prevposition = 999999;	
		else
		$prevposition = key_exists( $row -> character_id, (array) $this -> yest_rankings[$type]) ? $this -> yest_rankings[$type][$row -> character_id] -> position : '999999' ;
		
		
		$data[$i]['stats_id'] = $row -> region_id ;
		$data[$i]['stats_label'] = Database::instance() -> escape( $row -> region_name );
		$data[$i]['target'] = 'region';
		$data[$i]['prevposition'] = $prevposition;		
		$data[$i]['position'] = $i;		
		$data[$i]['type'] = $type;
		$data[$i]['value'] = $row -> coins;
		$data[$i]['entity'] = null;
		$data[$i]['param1'] = '';
		$data[$i]['param2'] = '';
		$data[$i]['valuefortitle'] = '';
		$data[$i]['param1fortitle'] = '';

		$i++;
	}
		
	$this -> save_stats( $data, false );
}

function compute_raidedkingdoms()
{
	kohana::log('info', '-> Computing More Raided Kingdoms');		
	$data = array();
	$type = 'raidedkingdoms';
	
	$sql = "SELECT sum(raidedcoins) raided_coins, k.id kingdom_id, k.name kingdom_name
	FROM battles b, regions n, kingdoms_v k
	WHERE b.dest_region_id = n.id 
	and n.kingdom_id = k.id
	and raidedcoins > 0 
	and b.status = 'completed' 
	group by k.id
	order by raided_coins desc
	limit 0, " . $this -> limit;
	
	$rset = Database::instance() -> query( $sql );
	
	$i = 1;
	foreach ( $rset as $row )
	{
		if (  ! key_exists( $type, (array) $this -> yest_rankings ) )
			$prevposition = 999999;	
		else
		$prevposition = key_exists( $row -> character_id, (array) $this -> yest_rankings[$type]) ? $this -> yest_rankings[$type][$row -> character_id] -> position : '999999' ;
		
		
		$data[$i]['stats_id'] = $row -> kingdom_id ;
		$data[$i]['stats_label'] = Database::instance() -> escape( $row -> kingdom_name );
		$data[$i]['target'] = 'kingdom';
		$data[$i]['prevposition'] = $prevposition;		
		$data[$i]['position'] = $i;		
		$data[$i]['type'] = $type;
		$data[$i]['value'] = $row -> raided_coins;
		$data[$i]['entity'] = 'rankings.ranking_raidedcoins';
		$data[$i]['param1'] = '';
		$data[$i]['param2'] = '';
		$data[$i]['valuefortitle'] = '';
		$data[$i]['param1fortitle'] = '';

		$i++;
	}
		
	$this -> save_stats( $data, false );
}

function compute_populatedcities()
{
	kohana::log('info', '-> Computing Most Populated Regions');		
	$data = array();
	$type = 'populatedcities';
	
	$sql = "SELECT count(c.id) residents, n.id region_id, n.name region_name
	FROM characters c, regions n
	WHERE c.region_id = n.id 
	AND c.type = 'pc' 
	group by n.id 
	order by residents desc
	limit 0, " . $this -> limit;
	
	$rset = Database::instance() -> query( $sql );
	
	$i = 1;
	foreach ( $rset as $row )
	{
		if (  ! key_exists( $type, (array) $this -> yest_rankings ) )
			$prevposition = 999999;	
		else
		$prevposition = key_exists( $row -> character_id, (array) $this -> yest_rankings[$type]) ? $this -> yest_rankings[$type][$row -> character_id] -> position : '999999' ;
		
		
		$data[$i]['stats_id'] = $row -> region_id ;
		$data[$i]['stats_label'] = Database::instance() -> escape( $row -> region_name );
		$data[$i]['target'] = 'region';
		$data[$i]['prevposition'] = $prevposition;		
		$data[$i]['position'] = $i;		
		$data[$i]['type'] = $type;
		$data[$i]['value'] = $row -> residents;
		$data[$i]['entity'] = 'rankings.ranking_residents';
		$data[$i]['param1'] = '';
		$data[$i]['param2'] = '';
		$data[$i]['valuefortitle'] = '';
		$data[$i]['param1fortitle'] = '';

		$i++;
	}
		
	$this -> save_stats( $data, false );
}

function compute_mostfollowedchurch()
{
	kohana::log('info', '-> Computing Most Followed Church');		
	$data = array();
	$type = 'mostfollowedchurch';
	
	
	$sql = "SELECT count(c.id) followers, ch.name church_name, ch.id church_id, r.name religion_name 
	FROM churches ch, characters c, religions r 
	WHERE c.church_id = ch.id 
	AND   c.type = 'pc' 
	AND   r.id = ch.religion_id 
	GROUP BY ch.name 
	ORDER BY followers desc ";
	
	$rset = Database::instance() -> query( $sql );
	
	$i = 1;
	foreach ( $rset as $row )
	{
		if (  ! key_exists( $type, (array) $this -> yest_rankings ) )
			$prevposition = 999999;	
		else
		$prevposition = key_exists( $row -> character_id, (array) $this -> yest_rankings[$type]) ? $this -> yest_rankings[$type][$row -> character_id] -> position : '999999' ;
		
		
		$data[$i]['stats_id'] = $row -> church_id ;
		$data[$i]['stats_label'] = Database::instance() -> escape( 'religion.church-' . $row -> church_name ) ;
		$data[$i]['target'] = 'religion';
		$data[$i]['prevposition'] = $prevposition;		
		$data[$i]['position'] = $i;		
		$data[$i]['type'] = $type;
		$data[$i]['value'] = $row -> followers;
		$data[$i]['entity'] = 'rankings.ranking_followers';
		$data[$i]['param1'] = 'religion.religion-' . $row -> religion_name;
		$data[$i]['param2'] = '';
		$data[$i]['valuefortitle'] = '';
		$data[$i]['param1fortitle'] = '';

		$i++;
	}
		
	$this -> save_stats( $data, false );
}

function compute_mosthonorable()
{
	kohana::log('info', '-> Computing Most Honorable');		
	$data = array();
	$type = 'honorpoints';
	
	$sql = "select character_id, c.name character_name, cs.value honor from 
	character_stats cs, characters c 
	where cs.character_id = c.id
	and   cs.name = 'honorpoints'
	and   c.type = 'pc' 
	order by honor desc limit 0, " . $this -> limit ;	
	
	$rset = Database::instance() -> query( $sql );
	
	$i = 1;
	foreach ( $rset as $row )
	{
		if (  ! key_exists( $type, (array) $this -> yest_rankings ) )
			$prevposition = 999999;	
		else
			$prevposition = key_exists( $row -> character_id, (array) $this -> yest_rankings[$type]) ? $this -> yest_rankings[$type][$row -> character_id] -> position : '999999' ;
		
		$data[$i]['stats_id'] = $row -> character_id ;
		$data[$i]['stats_label'] = Database::instance() -> escape( $row -> character_name );
		$data[$i]['target'] = 'player';
		$data[$i]['prevposition'] = $prevposition;		
		$data[$i]['position'] = $i;		
		$data[$i]['type'] = $type;
		$data[$i]['value'] = $row -> honor;
		$data[$i]['entity'] = 'rankings.ranking_honorpoints';
		$data[$i]['param1'] = '';
		$data[$i]['param2'] = '';
		$data[$i]['valuefortitle'] = $row -> honor;
		
		$i++;
	}
		
	$this -> save_stats( $data, false );
	
}

}
?>