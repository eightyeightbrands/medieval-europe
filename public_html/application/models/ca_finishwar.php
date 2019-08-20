<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Finishwar_Model extends Character_Action_Model
{
	
	protected $immediate_action = true;
	protected $war = null;
	
	// Effettua tutti i controlli relativi all' azione, sia quelli condivisi
	// con tutte le action che quelli peculiari 
	// @input: array di parametri
	// par[0]: oggetto char
	// par[1]: id guerra
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $message contiene il messaggio di ritorno	
	
	protected function check( $par, &$message )
	{ 	
		
		$allwars = Configuration_Model::get_kingdomswars();
		
		// guerra deve esistere e in corso
		if (!isset($allwars[$par[1]]) or $allwars[$par[1]]['war'] -> status != 'running')
		{
			$message = kohana::lang( 'ca_finishwar.error-warnotfound');
			return false;
		}						
		// può terminare la guerra solo chi l' ha lanciata
		$sourcekingdom = ORM::factory('kingdom', $allwars[$par[1]]['war'] -> source_kingdom_id);
		$sourceking = $sourcekingdom -> get_king();
		if (is_null($sourceking) or $sourceking -> id != $par[0] -> id )
		{
			$message = kohana::lang( 'ca_finishwar.error-youdidnotdeclarewar');
			return false;
			
		}
		
		$this -> war = $allwars[$par[1]];
		
		return true;				
	}
		
	protected function append_action( $par, &$message ) {}

	function complete_action( $data ) {}
	
	public function execute_action ( $par, &$message) 
	{
		$war = ORM::factory('kingdom_war', $this -> war['war'] -> id);
		$war -> finish( 'terminatedbysourceking' );
		
		$message = kohana::lang( 'ca_finishwar.info-youhaveterminatedwar');

		return true;

	}
}
