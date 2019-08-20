<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Resignfromrole_Model extends Character_Action_Model
{

	protected $immediate_action = true;

	// Effettua tutti i controlli relativi all' azione, sia quelli condivisi
	// con tutte le action che quelli peculiari 
	// @input: array di parametri
	// par[0]: oggetto char	
	// par[1]: oggetto character_role
	
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $message contiene il messaggio di ritorno	
	
	protected function check( $par, &$message )
	{ 
		
		if ( ! parent::check( $par, $message ) )					
		{ return false; }
		
		// il chiamante deve avere un ruolo		
		$role = $par[0] -> get_current_role();
		if ( is_null( $role ) )
		{
			$message = kohana::lang( 'global.operation_not_allowed');
			return false;
		}
		
		// Se il regno è in guerra, nessuno si può dimettere
		$data = null;
		$iskingdomfighting = Kingdom_Model::is_fighting( $par[0] -> region -> kingdom_id, $data ) ;		
		if ( $iskingdomfighting == true and $role -> get_roletype() != 'religious' )
		{	$message = kohana::lang( 'ca_resignfromrole.cantresignwhileinwar');	return false;	}
		
		return true;
	}

	
	// nessun controllo particolare
	protected function append_action( $par, &$message )
	{	}

	public function execute_action ( $par, &$message) 
	{
		
		// carica religiosa
		if ( in_array( $par[1] -> tag,
			array( 
				'church_level_1', 
				'church_level_2', 
				'church_level_3', 
				'church_level_4' 
				) ) )
		{		
			Character_Event_Model::addrecord( 
				null, 
				'announcement', 
				'__events.churchresign_announcement' .
				';' .   $par[0] -> name . 	
				$par[0] -> get_rolename(),				
				'evidence'	
				);
		}
		// carica governo
		else
		{
			if ( $par[1] -> tag == 'king' )
			{
				
				Character_Event_Model::addrecord( 
					null, 
					'announcement', 
					'__events.kingresign_announcement' .
					';' .   $par[0] -> name . 				
					';__' . $par[0] -> region -> kingdom -> get_name() , 
					'evidence'
					);
			}
			else
			{
				Character_Event_Model::addrecord( 
					null, 
					'announcement', 
					'__events.otherresign_announcement' .
					';' .   $par[0] -> name . 
					';__global.' . $par[1] -> tag . 
					';__' . $par[1] -> region -> name, 
					'evidence'
					);
			}
		}
		
		$par[1] -> end( $par[0] ); 
		$message = kohana::lang( 'charactions.resign_from_role_ok');

		return true;
	
	}
}
