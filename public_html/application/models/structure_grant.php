<?php defined('SYSPATH') OR die('No direct access allowed.');

class Structure_Grant_Model extends ORM
{

protected $belongs_to = array('structure', 'character' );

/**
* Assigns a profile to a structure
* @param obj structure
* @param obj char that needs a grant
* @param obj job
* @param string grant
* @param expire date expire date
* @return none
*/	

function add( $structure, $granted, $job, $grant, $expiredate )
{

	$g = new Structure_Grant_Model();
	$g -> structure_id = $structure -> id;
	$g -> character_id = $granted -> id;
	if  ( !is_null($job) )
		$g -> job_id = $job -> id;	
	$g -> grant = $grant;
	$g -> expiredate = $expiredate;	
	$g -> save();
	
	$cachetag = '-charstructuregrant_' . $granted -> id . '_' . $structure -> id;
	My_Cache_Model::delete($cachetag);
	
	//kohana::log('debug', kohana::debug( $g ) ); 
	
	Structure_Event_Model::newadd( 
		$structure -> id, 
		'__events.structure_accessprofilegiven' . ';__' .
		'structures.grant_' . $grant . ';' .
		$granted -> name . ';' );			
		
	Character_Event_Model::addrecord( 
		$granted -> id, 
		'normal',
		'__events.target_accessprofilegiven' . ';' .
		$structure -> character -> name . ';__' .
		'structures.grant_' . $grant . ';__' .
		$structure -> structure_type -> name . ';__' . 
		$structure -> region -> name );
			
}		

/**
* Funzione che revoca il profilo per una certa struttura
* @param structure oggetto struttura
* @param granted oggetto char a cui è dato il permesso	
* @param job oggetto job a cui è legato il grant
* @return none
*/	
	
function revoke( $structure, $granted, $job = null, $profile )
{
	
	// trova grants legate al contratto
	// se questo esiste
	
	if ( is_null( $job ) )
		$grants = ORM::factory('structure_grant' ) -> where 
		( 
			array( 
			'structure_id' => $structure -> id, 
			'character_id' => $granted -> id,
			'grant' => $profile )
		) -> find_all(); 
	else
		$grants = ORM::factory('structure_grant' ) -> where 
		( 
			array( 
			'structure_id' => $structure -> id, 
			'character_id' => $granted -> id,
			'job_id' => $job -> id,
			'grant' => $profile )
		) -> find_all(); 
		
	if ( $grants -> count() == 0  )	
		return;
				
	Structure_Event_Model::newadd( 
		$structure -> id, 
		'__events.structure_accessprofilerevoked' . ';__' .
		'structures.grant_' . $profile . ';' .
		$granted -> name . ';' );			
	
	Character_Event_Model::addrecord( 
		$granted -> id, 
		'normal',
		'__events.target_accessprofilerevoked' . ';' .
		$structure -> character -> name . ';__' . 
		'structures.grant_' . $profile . ';__' .
		$structure -> structure_type -> name . ';__' . 
		$structure -> region -> name );
	
	$cachetag = '-charstructuregrant_' . $granted -> id . '_' . $structure -> id;
	My_Cache_Model::delete($cachetag);
	
	foreach ( $grants as $grant )
	{
		$grant -> delete();	
	}
	
}

/**
* Finds which grants have a certain character on the structure.
* @param obj $structure Struttura
* @param obj $granted Personaggio 
* @return array g$rants
*/	

function get_chargrants( $structure, $granted )
{
	
	$grants = array();	
	$relationtype = '';
	
	//kohana::log('debug', "-> ------- grants -------	");
	//kohana::log('debug', "-> finding grants on {$structure -> structure_type -> type} in {$structure->region->name} for char: {$granted->name}.");
	
	// find assigned grants 
	
	$cachetag = '-charstructuregrant_' . $granted -> id . '_' . $structure -> id;
	$grants = My_Cache_Model::get( $cachetag );
		
	if ( is_null( $grants ) )		
	{
		
		kohana::log('debug', "Tag: {$cachetag} not found in cache, getting from db.");
		
		// is the granted husband or wife of the structure owner?
	
		if ( Character_Model::is_marriedto( $granted -> id, $structure -> character_id, $relationtype ) )
			$grants[] = $relationtype;
	
		// in caso di struttura battlefield, ognuno è owner perchè 
		// l'accesso è filtrato a monte
		
		if ( 
				$structure -> structure_type -> supertype == 'battlefield' 
				or 
				$structure -> character_id == $granted -> id	)
		{
			$grants[] = 'owner' ;	
		}	
		
		foreach ( $structure -> structure_grant as $structure_grant )
			if ( $structure_grant -> character -> id == $granted -> id and $structure_grant -> expiredate > time() )
				$grants[] =  $structure_grant -> grant ;				
	
		if ( empty( $grants ) )
			$grants[] = 'none' ;
		
		My_Cache_Model::set( $cachetag, $grants, (24*3600) );
		kohana::log('debug', kohana::debug($grants));
	}
	
	return $grants;
	
}

/**
* Verifica se un char ha una determinata grant per la struttura
* @param obj $structure oggetto struttura
* @param obj $granted oggetto char controllato
* @param str $grant da controllers
* @return true or false
*/

function get_chargrant( $structure, $granted, $grant )
{

	$grants = Structure_Grant_Model::get_chargrants( $structure, $granted );	
	return in_array( $grant, $grants );
	
}

/**
* Verifica quanti char hanno un permesso sulla struttura
* @param structure oggetto struttura
* @param $grant grant da controllare
* @return numero di char con la grant
*/

function get_charswithprofile( $structure, $grant )
{
	$c = 0;
	
	foreach ( $structure -> structure_grant as $structure_grant )
		if ( $structure_grant -> grant == $grant and $structure_grant -> expiredate > time () )
			$c ++;
	
	return $c;

}



}
