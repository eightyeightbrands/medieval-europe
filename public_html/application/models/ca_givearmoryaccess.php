<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Givearmoryaccess_Model extends Character_Action_Model
{
	protected $immediate_action = true;
	
	
	// check
	// @input: parametri
	//  - par[0]: oggetto char che da l' accesso
	//  - par[1]: oggetto struttura armory
	//  - par[2]: oggetto char a cui si dà l' accesso	
	
	protected function check( $par, &$message )
	{ 
		if ( ! parent::check( $par, $message, $par[0] -> id, $par[2] -> id ) )					
			return false;		
		
		// check input				
		if ( !$par[0] -> loaded or !$par[1] -> loaded or !$par[2] -> loaded) 
		{ $message = kohana::lang( 'global.operation_not_allowed'); return false; }

		// il char deve essere autorizzato; solo l' owner può dare deleghe		
		if ( $par[0] -> id != $par[1] -> character_id )
		{ $message = kohana::lang( 'global.operation_not_allowed'); return false; }
		
		// per dare la delega, l' armory deve avere il bonus valido
		$bonus = $par[1] -> get_premiumbonus('armory');
		if ( is_null( $bonus ) )
		{ $message = kohana::lang( 'global.operationnotallowed'); return false; }	

		if ( Structure_Grant_Model::get_chargrant( $par[1], $par[2], 'captain_assistant' ) == true )		
		{ $message = kohana::lang( 'ca_givearmoryaccess.charhasalreadyaccess', $par[2] -> name ); return false; }	
		
		if ( Structure_Grant_Model::get_chargrant( $par[1], $par[2], 'owner' ) == true )
		{ $message = kohana::lang( 'global.operationnotallowed'); return false; }	
		
		
		if ( count( $par[1]-> structure_grant ) == 5 )
		{ $message = kohana::lang( 'ca_givearmoryaccess.max5delegated'); return false; }	
		
		return true;
	}
	
	protected function append_action( $par, &$message ){	}
	
	public function execute_action ( $par, &$message ) 
	{
	
		Structure_Grant_Model::add( $par[1], $par[2], null, 'captain_assistant', null); 
		$message = kohana::lang('ca_givearmoryaccess.grantgiven_ok', $par[2] -> name ); 
					
		return true;		
	}
	
}
