<?php defined('SYSPATH') OR die('No direct access allowed.');
class CA_Disease_Model extends Character_Action_Model
{
	
	protected $immediate_action = true;
	protected $cancel_flag = false;
	
	
	/** 
	* Effettua tutti i controlli 
	* @param: par: array di parametri
	* par[0]: oggetto char
	* par[1]: nome disease
	*/
	
	
	protected function check( $par, &$message )
	{ 
		
		if ( ! parent::check( $par, $message ) )					
		{ return false; }
		
		/////////////////////////////////////////////////////
		// controllo dati
		/////////////////////////////////////////////////////
		
		if ( !$par[0]->loaded )
		{ $message = kohana::lang('global.operation_not_allowed'); return FALSE; }
		
		
		return true;
	}

	protected function append_action( $par, &$message ) {}	

	public function complete_action( $data )
	{ 
		
		$char = ORM::factory('character', $data -> character_id );
		
		// istanzia la classe corretta
		
		$disease = "Disease_" . ucfirst( $data -> param1 ) . "_Model";
		$class = new $disease();
		$class -> apply( $char );
		
		$a = ORM::factory('character_action', $data -> id );
		$nexttime = $class -> get_nextapplytime();
		$a -> starttime = $nexttime;
		$a -> endtime = $nexttime;
		$a -> save();
		
	}
	
}
