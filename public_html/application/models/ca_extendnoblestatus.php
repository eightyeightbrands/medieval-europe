<?php defined('SYSPATH') OR die('No direct access allowed.');
class CA_Extendnoblestatus_Model extends Character_Action_Model
{
	protected $immediate_action = true;
	protected $tariffplan = array( 
						'1'   => 10,
						'3'   => 30,
						'5'   => 50,
						'10'  => 70,
						'15'  => 100,
						'30'  => 190,
						'60'  => 380,
						'90'  => 550,
						'120' => 710,
						'180' => 1060,
						'365' => 2100,
						);
	protected $bonus = null;
						
	
	// Effettua tutti i controlli relativi alla wear, sia quelli condivisi
	// con tutte le action che quelli peculiari della wear
	// @input: array di parametri (par[0] memorizza l'id dell'oggetto che indosso)
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	// par[0]: oggetto char 
	// par[1]: titolo nobiliare scelto
	// par[2]: durata in giorni del bonus
	
	protected function check( $par, &$message )
	{ 
		
		// controllo che il char abbia sufficienti dobloni
		if ( $par[0]->get_item_quantity( 'doubloon' ) < $this -> tariffplan[$par[2]] )
		{ $message = kohana::lang('bonus.error-notenoughdoubloons'); return FALSE; }				
		
		$this -> bonus = Character_Model::get_premiumbonus( $par[0] -> id, 'basicpackage' );
		
		if ( $this -> bonus == false ) 
		{ $message = kohana::lang('ca_acquirenoblestatus.packagenotowned'); return FALSE; }				
		
		// controllo titolo e sesso		
		if ( in_array( $par[1], array( 'monsignor', 'don', 'warlord' ) ) and 
			$par[0] -> sex == 'F') 
		{ $message = kohana::lang('ca_acquirenoblestatus.error-isonlymale'); return FALSE; }	
				
		return true;
	}

	
	// nessun controllo particolare
	protected function append_action( $par, &$message )
	{	}

	public function complete_action( $data)
	{ }
	
	public function execute_action ( $par, &$message ) 
	{	
		
		if ( $par[0]->sex == 'M' )
			$param1 = $par[1].'_m' ;
		else
			$param1 = $par[1].'_f' ;
		
		character_premiumbonus_Model::add(
			$par[0], 
			null,
			'basicpackage',
			$this -> tariffplan[$par[2]],
			($par[2] * 24 * 3600),
			$param1
		);	
				
		$par[0] -> modify_doubloons( -$this -> tariffplan[$par[2]], 'noblebonus' );
		$par[0] -> save();
				
		My_Cache_Model::set(  '-charinfo_' . $par[0] -> id . '_nobiliartitle', $param1 );		
		My_Cache_Model::delete(  '-charinfo_' . $par[0] -> id . '_bonuses' );

		$message = kohana::lang( 'ca_acquirenoblestatus.extend-ok' ); 
		
		return true;
	}
}
