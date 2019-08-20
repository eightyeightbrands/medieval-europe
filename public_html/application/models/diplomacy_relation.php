<?php defined('SYSPATH') OR die('No direct access allowed.');

class Diplomacy_Relation_Model extends ORM
{

/**
* Ricava le info di una relazione diplomatica
* @param int $id Id record stato diplomatico
* @return info array oppure null
*/

public function get_info( $id )
{

	$diplomacystatus = Database::instance() -> query( 
		"select 
		dr.id, dr.sourcekingdom_id, dr.targetkingdom_id, 
		k1.name sourcekingdom_name, k2.name targetkingdom_name, dr.type, dr.timestamp, 
		dr.signedby 
		from    kingdoms_v k1, kingdoms_v k2, diplomacy_relations dr
		where   k1.id = dr.sourcekingdom_id
		and     k2.id = dr.targetkingdom_id 
		and     dr.id = " . $id ) -> as_array();
	
	if ( count($diplomacystatus) == 0 )
		return null;
	
	return $diplomacystatus[0];
}

/**
* Torna le relazioni diplomatiche di un regno
* @param int $kingdom_id Regno sorgente
* @return array
*/

public function get_diplomacy_relations( $kingdom_id )
{
	
	$relations = Configuration_Model::get_cfg_diplomacyrelations();
	$r = array();
	
	foreach ( $relations as $key1 => $subarray  )
		foreach ( $subarray as $key2 => $value )
			if ( $key1 == $kingdom_id )
				$a[$key1][$key2] = $value ;	
	
	return $a;	
}

/**
* Torna la relazione tra un regno ed un altro
* dalla release 2.9.4.4 le relazioni sono bidirezionali quindi
* è sufficiente cercarne una
* @param sourcekingdom_id regno sorgente
* @param targetkingdom_id regno destinatario
* @return array $relation
*  - id: id
*  - sourcekingdom_id: id kingdom sorgente
*  - targetkingdom_id: id kingdom target
*  - type: tipo relazione (neutral ecc)
*  - description: descrizione (non usata)
*  - timestamp: data 
*  - signed by: Id Reggente che ha firmato 
*/


function get_diplomacy_relation( $sourcekingdom_id, $targetkingdom_id )
{
	
	$relation = ORM::factory('diplomacy_relation') -> where 
		( array( 
			'sourcekingdom_id' => $sourcekingdom_id, 
			'targetkingdom_id' => $targetkingdom_id ) ) -> find();
	if ( !$relation -> loaded ) 
		return null;
		
	return $relation -> as_array();
	
}

function get_allies ( $sourcekingdom_id )
{
	$allies = array();

	$relations = Configuration_Model::get_cfg_diplomacyrelations();
	$relationswithsourcekingdom = $relations[ $sourcekingdom_id ];
	
	foreach ( $relationswithsourcekingdom as $tarketkingdom_id => $data )
	{
		if ( $data['type'] == 'allied' )
			$allies[] = $tarketkingdom_id ;
		
	}
	
	return $allies;
		
}


}

