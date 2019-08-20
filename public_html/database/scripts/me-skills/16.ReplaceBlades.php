<?php
define ( 'SYSPATH', 1 );
include dirname(__FILE__) . "/../../../application/config/database.php";

$melink = mysqli_connect( $config['default']['connection']['host'], $config['default']['connection']['user'], $config['default']['connection']['pass'] ) or die('error: cannot connect to database');
mysqli_select_db( $melink, $config['default']['connection']['database'] ) or die('error: cannot connect to database');

$rset = mysqli_query($melink,"
select i.quantity, i.structure_id, i.price, i.character_id, i.seller_id, i.id, ci.tag from items i, cfgitems ci
where i.cfgitem_id = ci.id
and ci.tag in ('knife_blade', 'hoe_blade', 'handaxe_blade', 'pickaxe_hardhead' )") or die(mysqli_error($melink));
while ( $row = mysqli_fetch_assoc( $rset ) ) 
{					
		$structure_type = '';
		if (!is_null($row['character_id']) and $row['character_id'] == -1 )
		{
			//var_dump("Skipping.");
			continue;
		}
		
		if (!is_null($row['structure_id']))
		{
			$res = mysqli_query($melink, "
			select type from structure_types st, structures s 
			where s.structure_type_id = st.id
			and s.id = {$row['structure_id']}");
			
			$row2 = mysqli_fetch_assoc($res);
			$structure_type = $row2['type'];
		}
		if ($row['tag'] == 'knife_blade' )
			$quantity = 4 * ($row['quantity']-1);
		if ($row['tag'] == 'hoe_blade' )
			$quantity = 2 * ($row['quantity']-1);	
		if ($row['tag'] == 'pickaxe_hardhead' )
			$quantity = 8 * ($row['quantity']-1);
		if ($row['tag'] == 'handaxe_blade' )
			$quantity = 4 * ($row['quantity']-1);		
				
		$structure_id = !empty($row['structure_id']) ? $row['structure_id'] : "NULL";
		$character_id = !empty($row['character_id']) ? $row['character_id'] : "NULL";
		$seller_id = !empty($row['seller_id']) ? $row['seller_id'] : "NULL";
		$price = !empty($row['price']) ? $row['price'] : "NULL";
		
		
		if ($quantity > 0)
		{
			//var_dump("Replacing item :{$row['quantity']} {$row['tag']} owner: {$row['character_id']} in structure: [{$structure_type}], seller: {$row['seller_id']} with {$quantity} Iron pieces at price: {$row['price']}.");
		
			$sql = "
			insert into items
			(id, cfgitem_id, character_id, structure_id, seller_id, quantity, quality, price)
			values
			(
				NULL,			
				(select id from cfgitems where tag = 'iron_piece'),
				{$character_id},
				{$structure_id},
				{$seller_id},
				{$quantity},
				100,
				{$price}
			);";
						
			echo $sql;
			//mysqli_query($melink, $sql) or die(mysqli_error($melink));
		}

		
}

mysqli_query($melink, "delete from items where cfgitem_id not in (select id from cfgitems)");
		



?>