<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Assignstructuregrant_Model extends Character_Action_Model
{
	protected $immediate_action = true;
	
	// check
	// @input: parametri
	//  - par[0]: oggetto struttura	
	//  - par[1]: oggetto char a cui assegnare il permesso	
	//  - par[2]: profilo
	
	protected function check( $par, &$message )
	{ 
		if ( ! parent::check( $par, $message , null, $par[1] -> id) )					
			return false;		

		// Check dati
		if ( $par[1] -> loaded == false )
		{ $message = kohana::lang( 'global.operation_not_allowed'); return false; }
		
		// verifica se il char ha giÃ  il permesso
		if ( Structure_Grant_Model::get_chargrant( $par[0], $par[1], $par[2] ) == true )
		{ $message = kohana::lang( 'ca_assignstructuregrant.error-grantsalreadyassigned'); return false; }
		
		// Non è possibile assergnare grant a sè stessi
		if ( $par[1] -> id == $par[0] -> character_id )
		{ $message = kohana::lang( 'ca_assignstructuregrant.error-cantgranttoself'); return false; }	
						
		if ( in_array( $par[2], array( 'guard_assistant' ) ) and
			Structure_Grant_Model::get_charswithprofile( $par[0], $par[2] ) >= 5 )
		{ $message = kohana::lang( 'ca_assignstructuregrant.error-grantlimitreached'); return false; }	
		
		// se la struttura è di tipo government, il char target deve essere del regno		
		if ( 
			$par[0] -> structure_type -> subtype == 'government' and 
			$par[1] -> region -> kingdom_id != $par[0] -> region -> kingdom_id )
		{ $message = kohana::lang( 'ca_assignstructuregrant.error-charisnotofthesamekingdom', $par[1] -> name); return false; }	
		
		
		return true;
	}
	
	protected function append_action( $par, &$message ){	}

	public function execute_action ( $par, &$message ) 
	{
	
		if ( in_array( $par[2], array (
			'captain_assistant',
			'guard_assistant',
			'chancellor' ) ) )
			Structure_Grant_Model::add( $par[0], $par[1], null, $par[2], (time() + 3 * 365 * 24 * 3600) );
		else
			Structure_Grant_Model::add( $par[0], $par[1], null, $par[2], (time() + 7 * 24 * 3600) );
		
		$message = kohana::lang('ca_assignstructuregrant.grantassigned_ok');					
		return true;		
	}
	
}
