<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Attackchar_Model extends Character_Action_Model
{
	protected $immediate_action = true;	
	protected $enabledifrestrained = true;
	protected $callablebynpc = false;	
	protected $appliabletonpc = true;	
		
	// Effettua tutti i controlli relativi alla eat, sia quelli condivisi
	// con tutte le action che quelli peculiari della eat
	// @input: array di parametri (par[0] memorizza l'id dell'oggetto che sto mangiando)
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	// par[0] = id Char che attacca
	// par[1] = id Char che è attaccato
	
	protected function check( $par, &$message )
	{ 
	
		// controllo dati
		
		
		// controlli base
		if ( ! parent::check( $par, $message, $par[0] -> id, $par[1] -> id ) )					
		{ return false; }
		
		if ( $par[1] -> status == 'dead' )
		{ $message = kohana::lang('ca_attackchar.error-charisnothere'); return FALSE; }
		
		// controllo che il difensore sia un npc
		if ( $par[1] -> type != 'npc' )
		{ $message = kohana::lang('ca_attackchar.error-cantattackpc'); return FALSE; }
		
		// controllo che attaccante e difensore siano nella stessa regione
		if ( $par[0] -> position_id != $par[1] -> position_id )
		{ $message = kohana::lang('ca_attackchar.error-notinsameregion'); return FALSE; }
		
		// controllo che attaccante abbia almeno 10 energia
		if ( $par[0] -> energy < 10 )
		{ $message = kohana::lang('ca_attackchar.error-youaretootired'); return FALSE; }	
	
		return true;
	}
	
	
	protected function append_action( $par, &$message ){}
		
	public function execute_action ( $par, &$message ) 
	{		
		
		$message = kohana::lang('ca_attackchar.info-ok');
		$battlereport = '';
		
		// lock target char
		
		Database::instance() -> query("
		SELECT id 
		FROM   characters 
		WHERE  id = {$par[1] -> id} FOR UPDATE");
		
		// add battle 
		
		$bm = new Battle_Model();
		$bm -> source_character_id = $par[0] -> id;
		$bm -> source_region_id = $par[0] -> position_id;
		$bm -> dest_character_id = $par[1] -> id;
		$bm -> dest_region_id = $par[1] -> position_id; 
		$bm -> type = 'pcvsnpc';
		$bm -> status = 'running';						
		$bm -> timestamp = time();
		$bm -> save();
		
		$bmr = new Battle_Report_Model();
		$bmr -> battle_id = $bm -> id;
		$bmr -> save();
		
		// call battle
		
		$battletype = Battle_TypeFactory_Model::create( $bm -> type );		
		$par[0] = $bm;		
		if ( $battletype -> run ( $par, $battlereport ) == false )		
		{ $message = kohana::lang('ca_attackchar.error-charisnothere'); return FALSE; }
			
		return true;
		
	}
	
}
