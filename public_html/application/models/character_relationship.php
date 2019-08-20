<?php defined('SYSPATH') OR die('No direct access allowed.');

class Character_Relationship_Model extends ORM
{	

	/**
	* Add a relationship
	* @param int sourcechar_id
	* @param int destchar_id
	* @param string relationship type
	* @return none
	*/
	
	public function add( $sourcechar_id, $targetchar_id, $type )
	{
		kohana::log('debug', '-> Adding relationship: ' . $sourcechar_id . ' - ' . $targetchar_id . ' - ' . $type );
		$rec = ORM::factory('character_relationship');
		$rec -> sourcechar_id = $sourcechar_id ;
		$rec -> targetchar_id = $targetchar_id ;
		$rec -> type = $type;
		$rec -> timestamp = time();
		$rec -> save();
		My_Cache_Model::delete(  '-charinfo_' . $sourcechar_id . '_relationships' );	
		
	}
	
	/**
	* Remove a relationship
	* @param int sourcechar_id
	* @param int destchar_id
	* @param string relationship type
	* @return none
	*/
	
	public function remove( $sourcechar_id, $targetchar_id, $type )
	{
	
		kohana::log('debug', '-> Removing relationship: ' . $sourcechar_id . ' - ' . $targetchar_id . ' - ' . $type );
		
		$rec = ORM::factory('character_relationship') 
			-> where
			( array (
				'sourcechar_id' => $sourcechar_id,
				'targetchar_id' => $targetchar_id,
				'type' => $type ) ) -> find() ;
		
		if ( $rec -> loaded ) 
		{
			kohana::log('debug', '-> Removing relationship: ' . $rec -> id) ;
			$rec -> delete($rec -> id);			
		}
		
		My_Cache_Model::delete(  '-charinfo_' . $sourcechar_id . '_relationships');	
		My_Cache_Model::delete(  '-charinfo_' . $targetchar_id . '_relationships');	
		
	}
	
	
	/**
	* Get all kin relations of a char
	* @param int targetchar id
	* @return array $data
	*/
	
	public function get_kinrelations( $char_id )
	{
		
		//kohana::log('debug', '--- get_kinrelations ---');
		//kohana::log('debug', "--- get_kinrelations char: {$char_id} ---");
	
		$cachetag = '-kinrelations_' . $char_id;
		$data = My_Cache_Model::get( $cachetag );
		//kohana::log('debug', kohana::debug($data));
		if ( is_null( $data ) )
		{
			
			$data = array(
				'id' => $char_id, 
				'name' => '',
				'outgoingrelations' => null,
				'incomingrelations' => null
			);
			
			
			$res = Database::instance() -> query("
				select  c1.id source_id, c1.name source_name, c2.id target_id, 
				c2.name target_name, cr.type 
				from character_relationships cr, 
						characters c1, characters c2 
				where   cr.sourcechar_id = {$char_id} 
				and     cr.targetchar_id = c2.id 
				and     cr.sourcechar_id = c1.id ");			
			
			foreach ( $res as $row )
			{
				
				$data['name'] = $row -> source_name;
				$data['outgoingrelations'][$row -> type]['id'] = $row -> target_id;
				$data['outgoingrelations'][$row -> type]['name'] = $row -> target_name;
			}
			
			$res = Database::instance() -> query("
				select  c1.id source_id, c1.name source_name, c2.id target_id, 
				c2.name target_name, cr.type 
				from character_relationships cr, 
						characters c1, characters c2 
				where   cr.targetchar_id = {$char_id} 
				and     cr.targetchar_id = c2.id 
				and     cr.sourcechar_id = c1.id ");
			
			foreach ( $res as $row )
			{				
				$data['incomingrelations'][$row -> type]['id'] = $row -> source_id;
				$data['incomingrelations'][$row -> type]['name'] = $row -> source_name;
			}
						
			My_Cache_Model::set( $cachetag, $data, (8*3600) );
			
			
		}
		
		return $data;
	
	}
}
?>
