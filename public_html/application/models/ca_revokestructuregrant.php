<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Revokestructuregrant_Model extends Character_Action_Model
{
	protected $immediate_action = true;
	
	// check
	// @input: parametri
	//  - par[0]: oggetto struttura	
	//  - par[1]: oggetto char a cui togliere il permesso	
	//  - par[2]: profilo
	
	protected function check( $par, &$message )
	{ 
		if ( ! parent::check( $par, $message ) )					
			return false;		
		// check input				
		
		if ( !$par[0] -> loaded or !$par[1] -> loaded )
		{ $message = kohana::lang( 'global.operation_not_allowed'); return false; }

		return true;
	}
	
	protected function append_action( $par, &$message ){	}

	public function execute_action ( $par, &$message ) 
	{
		
		Structure_Grant_Model::revoke( $par[0], $par[1], null, $par[2] );
		$message = kohana::lang('ca_revokestructuregrant.grantrevoked_ok');
					
		return true;		
	}
	
}