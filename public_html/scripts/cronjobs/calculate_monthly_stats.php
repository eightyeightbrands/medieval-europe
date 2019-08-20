<?php

/**
* Cronjobs che calcola le stats 
* per le classifiche
*/

define ( 'SYSPATH', 1 );
include dirname(__FILE__) . "/../libs/KLogger.php";
include dirname(__FILE__) . "/../../application/config/database.php";

ini_set('error_reporting', E_ERROR );
error_reporting(E_ERROR);

$log = new KLogger ( dirname(__FILE__) . "/calculate_monthly_stats.log" , KLogger::DEBUG );

mysql_connect( 'localhost', $config['default']['connection']['user'], $config['default']['connection']['pass'] ) or die('error: cannot connect to database');
mysql_select_db( $config['default']['connection']['database'] );


$period = time() - ( 14 * 24 * 3600 ); 

$log->LogDebug(" --- computing statistics for $period ---" );

// Statistiche Regni

$sql = "select 
	k.name kingdomname, r.name regionname, r.id region_id, k.id kingdom_id 
	from kingdoms k, regions r where r.kingdom_id = k.id 
	and k.name != 'kingdoms.kingdom-independent' and r.type != 'sea' ";

$rset = mysql_query( $sql );

$regionstats = array(); 
$kingdomstats = array(); 

while ( $r = mysql_fetch_assoc( $rset ) )
{
	$log->LogDebug(">>> Computing stats for kingdom: " . $r['kingdomname'] . ' region: ' . $r['regionname'] . '<<<' ); 
	
	//////////////////////////////////////////////
	// Trova il Re
	//////////////////////////////////////////////
	
	$sql = "select c.name, c.id from characters c, character_roles r where
		r.kingdom_id = " . $r['kingdom_id'] . "
		and r.character_id = c.id 
		and r.tag in ('king') 
		and current=1"; 
	$rset2 = mysql_query ( $sql ) or die ("Error: " . mysql_error() ); 
	
	if ( mysql_num_rows( $rset2 ) > 0 )
	{
		$row = mysql_fetch_assoc( $rset2 ); 
		$king = mysql_real_escape_string($row['name']) ; 
		$king_id = $row['id'] ; 
	}
	else
	{
		$king = '-';
		$king_id = 0;
	}
	
	$log->LogDebug("King is : $king");
	
	//////////////////////////////////////////////
	// Trova il Vassallo
	//////////////////////////////////////////////
			
	$sql = "select c.name from characters c, character_roles r where
		r.kingdom_id = " . $r['kingdom_id'] . "
		and r.region_id = " . $r['region_id']  . " 
		and r.character_id = c.id 
		and r.tag in ('vassal') 
		and current = 1"; 
		
	$rset2 = mysql_query ( $sql ) or die ("Error: " . mysql_error() ); 
	if ( mysql_num_rows( $rset2 ) > 0 )
	{
		$row = mysql_fetch_assoc( $rset2 ); $vassal = mysql_real_escape_string($row['name']) ; 
	}
	else
		$vassal = '-';
	
	$log->LogDebug("Vassal is : $vassal");
	
	////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Il heritage è dato dai soldi dei char + quelli immagazzinati nelle strutture 
	// e detenuti dai char del regno
	////////////////////////////////////////////////////////////////////////////////////////////////////////////

	$log->LogDebug("Computing Heritage..."); 
	
	$sql = " select ifnull( sum( quantity ), 0 ) value
		from items 
		where cfgitem_id = ( select id from cfgitems where tag = 'silvercoin' )
		and  character_id in ( select id from characters where region_id = " . $r['region_id'] . ")" ;
		
	$heritage_char = mysql_fetch_assoc( mysql_query( $sql ) ) or die ( mysql_error() ); 
	
	$sql = " select ifnull( sum( quantity ), 0 ) value
	from items 
	where structure_id in 
		( select  id from structures
			where structure_type_id in	( select id from structure_types where subtype = 'player' ) 
			and region_id = " . $r['region_id'] . "			
			and  character_id in ( select id from characters where region_id = " . $r['region_id'] . ") ) ";

	$heritage_structures =  mysql_fetch_assoc( mysql_query( $sql ) ) or die ( mysql_error() ); 
	
	$heritage  = $heritage_char['value'] + $heritage_structures['value'] ; 
	
	$regionstats[ $r['kingdomname'] ] [ $r[ 'regionname'] ] [ 'kingdom_id' ] = $r['kingdom_id'];
	$regionstats[ $r['kingdomname'] ] [ $r[ 'regionname'] ] [ 'region_id' ] = $r['region_id'];
	$regionstats[ $r['kingdomname'] ] [ $r[ 'regionname'] ] [ 'regionheritage' ] = $heritage;
	$regionstats[ $r['kingdomname'] ] [ $r[ 'regionname'] ] [ 'vassal' ] = $vassal;
	$regionstats[ $r['kingdomname'] ] [ $r[ 'regionname'] ] [ 'king' ] = $king;
	
	////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// n. abitanti
	////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	$log->LogDebug("Computing Citizens..."); 
	
	$sql = " select count( id ) value
		from characters 		
		where region_id = " . $r['region_id'] ; 
	
	//echo $sql; 
	
	$population =  mysql_fetch_assoc( mysql_query( $sql ) ) or die ( mysql_error() ); 
	
	$regionstats[ $r['kingdomname'] ] [ $r[ 'regionname'] ] [ 'regionpopulation' ] = $population ['value'];
	
	////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// patrimonio medio
	////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	$log->LogDebug("Computing Average heritage..."); 
	
	if ( $population['value'] == 0 )
		$regionstats[ $r['kingdomname'] ] [ $r[ 'regionname'] ] [ 'regionavgheritage' ] = 0;
	else
		$regionstats[ $r['kingdomname'] ] [ $r[ 'regionname'] ] [ 'regionavgheritage' ] = $heritage / $population['value'] ; 
	
	////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Statistiche Regno
	////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	$log->LogDebug("Counting Regions..." ); 
	
	$kingdomstats[ $r['kingdomname'] ] [ 'kingdom_id'] = $r['kingdom_id'];
	$kingdomstats[ $r['kingdomname'] ] [ 'region_id'] = null;
  $kingdomstats[ $r['kingdomname'] ] [ 'kingdomownedregions'] += 1; 
	$kingdomstats[ $r['kingdomname'] ] [ 'kingdomheritage'] += $regionstats[ $r['kingdomname'] ] [ $r[ 'regionname'] ] [ 'regionheritage' ];
	$kingdomstats[ $r['kingdomname'] ] [ 'kingdompopulation'] += $regionstats[ $r['kingdomname'] ] [ $r[ 'regionname'] ] [ 'regionpopulation' ];	
	if ( $kingdomstats[ $r['kingdomname'] ] [ 'kingdompopulation'] == 0 )
		$kingdomstats[ $r['kingdomname'] ] [ 'kingdomavgheritage'] = 0;
	else
		$kingdomstats[ $r['kingdomname'] ] [ 'kingdomavgheritage'] = $kingdomstats[ $r['kingdomname'] ] [ 'kingdomheritage'] / $kingdomstats[ $r['kingdomname'] ] [ 'kingdompopulation'] ; 
	$kingdomstats[ $r['kingdomname'] ] [ 'king' ] = $king;
	$kingdomstats[ $r['kingdomname'] ] [ 'king_id' ] = $king_id;
	
	
	////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Trova n. attacchi e n vincite
	////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	$log->LogDebug("Computing total battles..."); 
	$sql = " select id, source_region_id, dest_region_id, attacker_wins, defender_wins 
		from battles where (
		source_region_id = " . $r['region_id'] . " or dest_region_id = " . $r['region_id'] . ") 
		and status = 'completed' 
		and month(from_unixtime(timestamp)) = month( curdate() )
		and year(from_unixtime(timestamp)) = year( curdate() )
		and type in ( 'raid', 'conquer_ir', 'conquer_r' ) ";
	
	$rset2 = mysql_query ( $sql ) or die ("Error: " . mysql_error() ); 
	while ( $row = mysql_fetch_assoc( $rset2 ))
	{
		$kingdomstats[ $r['kingdomname'] ] [ 'kingdomtotalbattles']++;
		if ( $row['source_region_id'] == $r['region_id'] and $row['attacker_wins'] > $row['defender_wins'] )
			$kingdomstats[ $r['kingdomname'] ] [ 'kingdomtotalwonbattles'] ++;

		if ( $row['dest_region_id'] == $r['region_id'] and $row['defender_wins'] > $row['attacker_wins'] )
			$kingdomstats[ $r['kingdomname'] ] [ 'kingdomtotalwonbattles'] ++;

	}
	
}

////////////////////////////////////////////////////////////
// Inserimento statistiche
////////////////////////////////////////////////////////////

$log->LogDebug(">>> Updating statistics <<<");
$log->LogDebug("Updating Kingdom Statistics...");

foreach ( $kingdomstats as $key => $value )
{
	
	$statistics = array ( 'kingdomownedregions', 
		'kingdomheritage', 'kingdompopulation', 'kingdomavgheritage',
		'kingdomtotalbattles', 'kingdomtotalwonbattles' ); 
	
	foreach ( $statistics as $statistic )
	{
	
		if ( !isset ($value[$statistic]) )
			$value[$statistic] = 0; 
			
		$log->LogDebug("Updating Kingdom Statistic: $statistic...");
	
		$sql =  "select id from stats_historical where name = '$statistic' and kingdom = '$key' and period = '$period' ";
		$log->LogDebug( $sql ); 
		$res = mysql_query ( $sql ) or die ( mysql_error() );
	
		if ( mysql_num_rows( $res ) == 0 )
		{
			$log -> logDebug ('--> Adding statistic...'); 
			$sql = "insert into stats_historical ( name, period, kingdom, region, kingdom_id, region_id, param1, param2, param3 ) values 
			( '$statistic', '$period', '"		
			. $key. "', null, "
			. $value['kingdom_id'] . ", null, " 
			. $value[$statistic] . ", '" 
			. $value['king'] . "', " . $value['king_id'] .")"; 		
		}
		else
		{
			$log -> logDebug ('--> Updating statistic...'); 
			$row = mysql_fetch_assoc( $res ); 
			$sql = "update stats_historical set param1 = " . $value[$statistic] . " 
					where id = " . $row['id'] ; 
		}
		
		$log -> LogDebug( $sql ); 
		mysql_query ( $sql ) or die ( mysql_error() );
		
	}
}
?>
