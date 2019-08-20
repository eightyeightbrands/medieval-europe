<?php defined('SYSPATH') OR die('No direct access allowed.');

class Structure_Type_Model extends ORM
{
	protected $table_name = "structure_types";	
	const TERRAIN_BASE_VALUE = 100; // Valore monetario di base del terreno in game

	// Relazioni con gli altri modelli
	protected $has_many =  array('structure');
	protected $belongs_to = array('church');
	
	static function factory( $type )
	{				
		$model = 'ST_' . $type . '_Model'; 		
		return new $model;
	}
		
	/**
	* Trova le potenziali regioni
	* dove è possibile costruire questo tipo di struttura
	* @param: $structure: istanza struttura da cui si lancia la costruzione
	* @return array id: region_id, name: region_name
	*/
	
	public function getpotentialbuildinglocations( $structure )
	{
		$db = Database::instance();
		$sql = '';
		if ( $this -> subtype == 'church' )
		{
			// sostituire con dijkstra
			$sql = "select r.id, r.name from regions r, kingdoms_v k 
			where r.kingdom_id = k.id 			
			and   k.name != 'kingdoms.kingdom-independent' 
			and   not exists ( select id from structures where structure_type_id = " . $this -> id . ")";			
		}
		else
		{			
			$sql = "select r.id, r.name from regions r, kingdoms_v k 
			where r.kingdom_id = k.id 			
			and   k.id = " . $structure -> region -> kingdom -> id . "
			and   k.name != 'kingdoms.kingdom-independent' 
			and   not exists ( select id from structures where structure_type_id = " . $this -> id . "
			and   region_id = r.id ) ";			
		}
		//var_dump( $sql ); exit; 		
		$regions = $db->query( $sql ) -> as_array();			
    //var_dump( $regions ); exit; 		
		return $regions ;
	}


	
}
