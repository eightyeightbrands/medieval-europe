<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Giveaccesspermit_Model extends Character_Action_Model
{
	protected $immediate_action = true;
	protected $targetchar = null;
	
	// check
	// @input: parametri
	//  - par[0]: nome target char
	//  - par[1]: oggetto struttura da cui si impartisce il comando.
	
	protected function check( $par, &$message )
	{ 
		if ( ! parent::check( $par, $message ) )					
			return false;		
		// check input				
		
		$this -> targetchar = ORM::factory('character') -> 
			where( 'name', $par[0] ) -> find() ; 
		
		if ( ! $this -> targetchar -> loaded or !$this -> targetchar -> loaded )
		{ $message = kohana::lang( 'global.operation_not_allowed'); return false; }		
		
		// è cittadino?
		
		if ( $this -> targetchar -> region -> kingdom_id == $par[1] -> region -> kingdom_id )
		{ $message = kohana::lang( 'ca_giveaccesspermit.error-charisacitizen', $this -> targetchar -> name ); return false; }
		
		// controllo: foglio di carta e sigillo ???
		
		if ( ! Character_Model::has_item( $par[1] -> character_id, 'paper_piece', 1 ) 
			or ! Character_Model::has_item( $par[1] -> character_id, 'waxseal', 1 ) ) 
		{ $message = kohana::lang('charactions.paperpieceandwaxsealneeded'); return FALSE; }	
		
		// ha giÃ  un permesso?
		
		$stat = Character_Model::get_stat_d( $this -> targetchar -> id, 'accesspermit',
			$par[1] -> region -> kingdom_id );
		if ( $stat -> value > time() )
		{ $message = kohana::lang('ca_giveaccesspermit.error-charhasalreadyapermit',
			$this -> targetchar -> name ); return FALSE; }	

		
		
		return true;
	}
	
	protected function append_action( $par, &$message ){	}

	public function execute_action ( $par, &$message ) 
	{
		
		$expiredate = time() + ( 24 * 3600 );
		$this -> targetchar -> modify_stat( 
			'accesspermit', 
			$expiredate, 
			$par[1] -> region -> kingdom_id, 
			null, 
			true );					
		
		$paper_piece = Item_Model::factory( null, 'paper_piece' );
		$paper_piece -> removeitem( 'character', $par[1] -> character_id, 1 );
		
		$waxseal = Item_Model::factory( null, 'waxseal' );
		$waxseal -> removeitem( 'character', $par[1] -> character_id, 1 );

		// evento al Re.
		Character_Event_Model::addrecord( 
			$par[1] -> character_id,
			'normal', 
			'__events.accesspermitassignedsource' . 			
			';' . $this -> targetchar -> name .
			';' . Utility_Model::format_datetime($expiredate),
			'normal' );
			
		// evento al target char
		
		Character_Event_Model::addrecord( 
			$this -> targetchar -> id,
			'normal', 
			'__events.accesspermitassignedtarget' . 
			';' . $par[1] -> character -> name .
			';__' . $par[1] -> region -> kingdom -> get_name()  .			
			';' . Utility_Model::format_datetime($expiredate),
			'normal' );
		
		
		$message = kohana::lang('ca_giveaccesspermit.info-permitassigned_ok', $this -> targetchar -> name, 
			Utility_Model::format_datetime( time() + (24 * 3600) ) );
					
		return true;		
	}
	
}
