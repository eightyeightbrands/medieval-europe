<?php
define ( 'SYSPATH', 1 );
include dirname(__FILE__) . "/../../../application/config/database.php";

$melink = mysqli_connect( $config['default']['connection']['host'], $config['default']['connection']['user'], $config['default']['connection']['pass'] ) or die('error: cannot connect to database');
mysqli_select_db( $melink, $config['default']['connection']['database'] ) or die('error: cannot connect to database');

$rset = mysqli_query($melink,"select * from kingdomprojects where status in ( 'building', 'collectingmaterial' )") or die(mysqli_error());
while ( $row = mysqli_fetch_assoc( $rset ) ) 
{			
		
		
		$rs1 = mysqli_query($melink,"update structures set hourlywage = {$row['hourlywage']}	where id = {$row['structure_id']}") or die (mysqli_error());
		
		
}
?>