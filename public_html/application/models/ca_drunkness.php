<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Drunkness_Model extends Character_Action_Model
{

	protected $cancel_flag = false;
	protected $immediate_action = false;
	
	// Effettua tutti i controlli relativi al move, sia quelli condivisi
	// con tutte le action che quelli peculiari del move
	// @input: array di parametri	
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	// $messages contiene gli errori in caso di FALSE
		
	protected function check( $par, &$message ) {}
		
	protected function append_action( $par, &$message ) {}
	
	public function complete_action( $data ) {
	
		// togli il disease
		
		$char = ORM::factory('character', $data -> character_id );
		$obj = DiseaseFactory_Model::createDisease('drunkness');
		$obj -> cure_disease( $char );			
	
	}
		
	protected function execute_action() {}
	
	public function cancel_action() {}
		
	// Questa funzione costruisce un messaggio da visualizzare 
	// in attesa che la azione sia completata.
	
	public function get_action_message( $type = 'long') 
	{
		
		$pending_action = $this->get_pending_action();
		$message = "";
		
		if ( $pending_action -> loaded )
		{
				
			$now = date("F d, Y H:i:s", time() );
			if ( $type == 'long' )		
				$message = '__regionview.drunkness_longmessage';
			else
				$message = '__regionview.drunkness_shortmessage';	
		
		}
		
		return $message;
	
	}
	
}
?>