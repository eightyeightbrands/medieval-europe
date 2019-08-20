<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Freeprisoner_Model extends Character_Action_Model
{
	protected $immediate_action = true;
	
	// Effettua tutti i controlli relativi alla wear, sia quelli condivisi
	// con tutte le action che quelli peculiari della wear
	// @input: array di parametri (par[0] memorizza l'id dell'oggetto che indosso)
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	// par[0]: oggetto char dello sceriffo 
	// par[1]: oggetto char dell imprigionato
	// par[2]: motivazione scarcerazione
	// par[3]: oggetto struttura della prigione
	// par[4]: oggetto sentenza
	
	protected function check( $par, &$message )
	{ 
		if ( ! parent::check( $par, $message ) )					
		{ return false; }
		
		// controllo dati "manipolabili"
		
		if ( !$par[0]->loaded or !$par[1]->loaded  or !$par[3]->loaded or !$par[4]->loaded)
		{ $message = kohana::lang('global.operation_not_allowed'); return FALSE; }
		
		// controllo che il personaggio da liberare
		// 1) sia nello stesso nodo dello sceriffo
		// 2) non abbia una carica 

		if ( $par[0] -> position_id != $par[1] -> position_id )
		{ $message = kohana::lang('charactions.imprison_notinsameregion', $par[1]->name ); return FALSE; }
	
		// controllo che la scarcerazione sia motivata
		
		if ( strlen($par[2]) <= 0 )
		{ $message = kohana::lang('charactions.free_motivationempty'); return FALSE; }
		
		return true;
	}
		
	protected function append_action( $par, &$message )	{}
	
	public function execute_action ( $par, &$message ) 
	{
		
		// modifico la stat
		
		$stat = Character_Model::get_stat_d(
			$par[1] -> id,
			'servejailtime');
		
		if (!is_null($stat))
		{			
			Character_Model::modify_stat_d(
				$par[1] -> id,
				'servejailtime',
				0,
				null,
				null,
				true,
				$stat -> stat1,
				time()
			);			
		}		
		
		/////////////////////////////////////////////
		// Aggiorno la sentenza
		/////////////////////////////////////////////			
		
		$par[4] -> imprisonment_end = time();
		$par[4] -> free_reason = $par[2];
		$par[4] -> status = 'executed';
		$par[4] -> save();
				
		/////////////////////////////////////////////
		// Invia evento all' imprigionato
		/////////////////////////////////////////////

		Character_Event_Model::addrecord( 
			$par[1]->id,
			'normal', 
			'__events.freeprisoner_prisoner'.
				';'.$par[0]->name	.
				';'.$par[2] 
		);
		
		/////////////////////////////////////////////
		// Invia evento al giudice
		/////////////////////////////////////////////
		
		Character_Event_Model::addrecord( 
			$par[4] -> issued_by,
			'normal', 
			'__events.freeprisoner_judge'.								
				';'.$par[1]->name	.
				';'.$par[0]->name	.
				';'.$par[2] 
		);
			
		$message = kohana::lang( 'charactions.freeprisoner_ok',  $par[1] -> name );
		
		return true;
	}
}
