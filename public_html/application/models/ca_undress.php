<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Undress_Model extends Character_Action_Model
{
	protected $immediate_action = true;
	protected $enabledifrestrained = true;

	// Effettua tutti i controlli relativi alla eat, sia quelli condivisi
	// con tutte le action che quelli peculiari della eat
	// @input: array di parametri
	// par[0]: Id oggetto
	// par[1]: id Char
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function check( $par, &$message )
	{ 
		if ( ! parent::check( $par, $message ) )					
		{ return false; }

		// Istanzio l'oggetto che sto per indossare
		$item = ORM::factory("item", $par[0]);
		
		// Controllo che l'oggetto esista
		
		if (! $item -> loaded)
		{ $message = kohana::lang('charactions.item_notexist'); return FALSE; }
		// Controllo che l'oggetto sia rimuovibile
		
		if ( is_null( $item -> cfgitem -> part ) )		
		{ $message = kohana::lang('charactions.item_notundressable'); return FALSE; }
		// Controllo che l'oggetto sia nell'inventario del char
		if ($item->character_id != $par[1])
		{ $message = kohana::lang('charactions.item_notininventory'); return FALSE; }

		return true;
	}

	
	// nessun controllo particolare
	protected function append_action( $par, &$message )
	{	}

	// Sazia il char
	// @input: par[0] memorizza l'id dell'oggetto che sto mangiando
	public function execute_action ( $par, &$message ) 
	{
		$item = ORM::factory("item", $par[0]);
		$item->equipped = 'unequipped';
		$item->save();

		$message = sprintf( kohana::lang( 'charactions.undress_ok'),  kohana::lang($item->cfgitem->name) );
		return true;
	}
}
