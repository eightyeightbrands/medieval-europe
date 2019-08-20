<?php defined('SYSPATH') OR die('No direct access allowed.');


class CA_Cancelrestrain_Model extends Character_Action_Model
{
	
	protected $cancel_flag = false;
	protected $immediate_action = true;
	
	public function __construct()
	{		
		parent::__construct();
		// questa azione non é bloccante per altre azioni del char.
		$this->blocking_flag = false;		
		return $this;
	}
	
	/**
  *	Effettua tutti i controlli relativi al move, sia quelli condivisi
	* con tutte le action che quelli peculiari del dig
	* @param: par
	*  par[0] = char che sblocca
	*  par[1] = char da sbloccare
	*  par[2] = azione di restrain
	*  par[3] = motivo
	* @return: TRUE = azione disponibile, FALSE = azione non disponibile
	*
	*/
	
	protected function check( $par, &$message )
	{ 
		$message = "";
		
		if ( ! parent::check( $par, $message ) )					
			return false;

		// controllo che il char abbia il ruolo adatto	
		$role = $par[0]->get_current_role();
		if ( $role->tag != 'sheriff' ) 
		{ $message = kohana::lang('global.operation_not_allowed'); return FALSE; }
		
		//kohana::log('debug', kohana::debug( $par[2] ) ); 
		
		// controllo dati
		if ( 
			! $par[1] -> loaded or 
			! $par[2] -> loaded or 
			$par[2] -> param1 != $par[0] -> region_id or
			$par[2] -> character_id != $par[1] -> id )
		{ $message = kohana::lang('global.operation_not_allowed'); return FALSE; }

		// la motivazione è mandatoria.
		
		if ( strlen( $par[3] ) == 0 )
		{ $message = kohana::lang('ca_cancelrestrain.cancelreasonismissing' ) ; return FALSE; }				
		
		return true;
	}

	protected function append_action( $par, &$message )  {}
	
	public function complete_action( $data ) 	{	}
	
	protected function execute_action( $par, &$message ) {
	
		// cancella l' azione
		
		$par[2] -> status = 'canceled' ; 
		$par[2] -> save();
		
		// evento al char sbloccato
		
		Character_Event_Model::addrecord(
			$par[1]->id, 
			'normal',
			'__events.restraincanceled_targetinfo' . 
			';' . $par[0]->name . 			
			';' . $par[3]
			);
			
		// evento al char che ha sbloccato
		
		Character_Event_Model::addrecord(
			$par[0]->id, 
			'normal',
			'__events.restraincanceled_sourceinfo' . 
			';' . $par[1]->name .
			';' . $par[3]			
			);			
		
		// marco la fine del restrain.
		
		$par[1] -> modify_stat(
			'lastrestrain',
			0,
			$par[2] -> param3,
			null,
			true,
			time());
			
		$message = kohana::lang('ca_cancelrestrain.cancelrestrain_ok'); 
		return true; 
	
	}
	
	public function cancel_action( ) {}
	
	
	
}
