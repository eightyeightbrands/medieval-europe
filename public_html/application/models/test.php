<?php defined('SYSPATH') OR die('No direct access allowed.');

class Test_Model
{
	
	function patchpaths()
	{
	
		kohana::log('debug', '--- start ---');
		
		$fastlands = Database::instance() -> query("
		select rp.id fastland_id, rp.region_id, rp.destination, r1.id, r1.name source, r2.id, r2.name dest
		from regions_paths rp, regions r1, regions r2
		where rp.type = 'fastland'
		and rp.region_id = r1.id 
		and rp.destination = r2.id 
		");
		
		foreach ($fastlands as $fastland)
		{
			
			kohana::log('debug', "-> Processing fastland n: {$fastland -> fastland_id}");
		
			$existnormalpath = Database::instance() -> query("
				select count(*) c
				from regions_paths 
				where type = 'land' 
				and   region_id = {$fastland -> region_id}
				and   destination = {$fastland -> destination}");			
			
			if ( $existnormalpath[0] -> c > 0 )
			{
				kohana::log('debug', "A normal path exists for fastland ID: {$fastland -> fastland_id} - {$fastland -> source} - {$fastland -> dest}, deleting fastland");
				
				Database::instance() -> query("delete from regions_paths_fasttracksroutes where regions_path_id = {$fastland->fastland_id}");
				Database::instance() -> query("delete from regions_paths where id = {$fastland -> fastland_id}");				
				
			}
		}
		
		$doubleentries = Database::instance() -> query("
		select count(*) c, region_id, destination, type
		from regions_paths
		group by region_id, destination, type
		having count(*) > 1;
		");
		
		foreach ($doubleentries as $entry)
		{
			kohana::log('debug', "-> Deleting double entry {$entry -> region_id} {$entry -> destination} {$entry->c}");
			$todelete = $entry->c - 1;
			
			$doubles = Database::instance() -> query
			("select * from regions_paths where region_id = {$entry->region_id} and destination = {$entry -> destination}");
			
			/*foreach ($doubles as $double)
				echo $double->id.";".$double -> region_id .";".$double -> destination .";".$double -> type."\r\n";
			*/
			Database::instance() -> query("delete from regions_paths
			where region_id = {$entry -> region_id}
			and   destination = {$entry -> destination}
			and   type = '{$entry -> type}'
			limit {$todelete}");
			
		}
		
		kohana::log('debug', '--- end ---');
		
	}
	/**
	* Assegna un ruolo forzatamente ad un personaggio
	*/
	
	function destroystructure( $id )
	{
		$structure = StructureFactory_Model::create( null, $id);		
		
		if ($structure -> loaded)
			$structure -> destroy();
		else
			die("Structure id {$id} not found.");
	}
	
	function assignrole( $char_id, $roletag, $structure_id, $church_id = null )
	{
		kohana::log('debug', '--- ASSIGN ROLE ---');
		$char = ORM::factory('character', $char_id);
		if (!$char -> loaded)
			die('Char non trovato.');
		
		$structure = StructureFactory_Model::create( null, $structure_id );
		
		if (is_null($structure) or !$structure -> loaded)
			die('Struttura non trovata.');
		
		if (!is_null($church_id))
		{
			$church = ORM::factory('church', $church_id);
			if (!$church -> loaded)
				die('Chiesa non trovata.');
		}
		
		$cr = new Character_Role_Model();
		$type = $cr -> get_roletype($roletag);		
		
		if( $type == 'religious' and $char -> church_id != $church_id )
			die("Il char non è battezzato per la chiesa: {$church_id}");
		
		Database::instance() -> query("
		update character_roles set end = unix_timestamp(), current = false 
		where tag = '{$roletag}' and kingdom_id = {$structure -> region -> kingdom_id} and
		region_id = {$structure -> region_id}");
		
		Database::instance() -> query("
		update structures set character_id = null where character_id = 
		( select id from characters where name = '{$char -> name}' )
		and structure_type_id in 
		(select id from structure_types where subtype in( 'church', 'government' ))");
		
		Database::instance() -> query("
		update character_roles  cr
		set current = false, 
		end = unix_timestamp()
		where current = true 
		and cr.gdr = false 
		and character_id = {$char -> id}");		
		
		Database::instance() -> query("
		update structures set character_id = 
		( select id from characters where name = '{$char -> name }' )
		where id = {$structure_id}");
		
		Database::instance() -> query("
		update characters set region_id = {$structure -> region_id } 
		where id = {$char -> id}
		");
		
		Database::instance() -> query("
		update characters set position_id = {$structure -> region_id } 
		where id = {$char -> id}
		");
		
		if ($type == 'government')
		{
			Database::instance() -> query("		
			insert into character_roles values
			(
				null, 
				{$char -> id},
				'{$roletag}',
				unix_timestamp(), 
				null, 
				{$structure -> region -> kingdom_id},
				{$structure -> region_id},
				{$structure -> id},
				true,
				null,
				null,
				false
			)");
		}
		else
		{
			
			
			Database::instance() -> query("
			insert into character_roles 
			(
				id, 
				character_id, 
				tag, 
				begin, 
				end, 
				kingdom_id, 
				region_id, 
				structure_id, 
				current, church_id, place, gdr )
				values
				(null, 
				{$char->id},
				'{$roletag}',
				unix_timestamp(), 
				null, 
				{$structure -> region -> kingdom_id},
				{$structure -> region_id},
				{$structure_id},
				true,
				{$church_id},
				null,
				false
				);
			");

			Character_Model::invalidate_char_cache( $char_id );
			
			$cachetag = '-charstructuregrant_' . $char_id . '_' . $structure_id;			
			My_Cache_Model::delete( $cachetag );
			var_dump("done");
		}	
			
	}
	
	function checkcurrencydealign()
	{
		
		$rset = Database::instance() -> query( "
		select s.id, st.type
		from structures s, structure_types st
		where s.structure_type_id = st.id
		and   st.type != 'market'");

		foreach ($rset as $row )
		{
			
			var_dump("-> Checking Structure ID: {$row -> id}");		
			
			$res_sc = Database::instance() -> query("
			select coalesce(sum(quantity),0) sc
			from items i, cfgitems ci
			where i.cfgitem_id = ci.id 
			and i.structure_id = {$row -> id} 
			and ci.tag = 'silvercoin' ") -> as_array();			
				
			$res_cc = Database::instance() -> query("
			select coalesce(sum(quantity),0) cc
			from items i, cfgitems ci
			where i.cfgitem_id = ci.id 
			and i.structure_id = {$row -> id} 
			and ci.tag = 'coppercoin' ") -> as_array();
			
			$coins = $res_sc[0]-> sc + $res_cc[0]->cc/100;			
			$res_scflat = Database::instance() -> query("select silvercoins from structures where id = {$row->id}") -> as_array();			
			
			
			if ($coins != $res_scflat[0] -> silvercoins)
			{
				var_dump("Structure: {$row->id} {$row->type} silvercoins items: [{$coins}], flat: [{$res_scflat[0] -> silvercoins}].");			
				var_dump("Fixing...");
				Database::instance() -> query("update structures set silvercoins = {$coins} where id = {$row->id}");
			}

			
		}
	}

}
