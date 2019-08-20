<?php
define ( 'SYSPATH', 1 );
include dirname(__FILE__) . "/../../../application/config/database.php";

$melink = mysqli_connect( $config['default']['connection']['host'], $config['default']['connection']['user'], $config['default']['connection']['pass'] ) or die('error: cannot connect to database');
mysqli_select_db( $melink, $config['default']['connection']['database'] ) or die('error: cannot connect to database');

$rset = mysqli_query($melink,"
select i.id, ci.tag from items i, cfgitems ci
where i.cfgitem_id = ci.id
and   ci.tag in ('handaxe_blade' )") or die(mysqli_error());
while ( $row = mysqli_fetch_assoc( $rset ) ) 
{					
		var_dump("Updating {$row['id']} {$row['tag']}");
		
		if ($row['tag'] == 'pickaxe_hardhead' )
			$quantity = 8;
		else
			$quantity = 4;
		
		$rs1 = mysqli_query($melink,"
		update items
		set    cfgitem_id = (select id from cfgitems where tag = 'iron_piece'),
		quantity = {$quantity},
		quality = 100 
		where id = {$row['id']}") or die (mysqli_error());
		
}
?>