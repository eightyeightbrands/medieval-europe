<?php defined('SYSPATH') OR die('No direct access allowed.');	
class CA_Upgradestructurelevel_Model extends Character_Action_Model
{
		
	protected $cancel_flag = false;
	protected $immediate_action = true;	
	protected $basetime       = null;
	protected $attribute      = null;
	protected $appliedbonuses = null;
	
	// L'azione richiede che il personaggio indossi
	// un determinato equipaggiamento
	
	protected $requiresequipment = false;	
	
	// Effettua tutti i controlli relativi al move, sia quelli condivisi
	// con tutte le action che quelli peculiari del dig
	// @input: 
	// $par[0] = character
	// $par[1] = structure		
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function check( $par, &$message )
	{ 
		
		if ( ! parent::check( $par, $message ) )					
			return false;
		
		// La struttura è upgradabile?
		
		if ($par[1] -> getIsupgradable() == false )
		{ $message = kohana::lang('structures.error-structureisnotupgradable'); return FALSE; }		
		
		// La struttura è già al massimo livello?
		
		if ($par[1]-> getCurrentlevel() == $par[1]-> getMaxlevel() )
		{ $message = kohana::lang('structures.error-structureisalreadyatmaxlevel'); return FALSE; }		
		
		// Ci sono i materiali nello storage?
		
		foreach ($par[1]-> getNeededmaterialfornextlevel() as $itemtag => $neededquantity)
		{
			if ( $par[1] -> get_item_quantity( $itemtag ) < $neededquantity)
			{ $message = kohana::lang('structures.error-structuredoesnotcontainneededmaterialfornextleveltoupgrade'); return FALSE; }
			
		}		
		
		return true;
		
	}


	// Funzione per l'inserimento dell'azione nel DB.
	// Questa funzione appende solo una azione _non la esegue_
	// Al momento non si prevedono azioni istantanee.
	// @input: $par[0] = structure, $par[1] = char
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function append_action( $par, &$message )
	{
	
	}

	// Esecuzione dell' azione. 
	// Questa funzione viene chiamata quando viene invocata una 
	// complete_expired_action e gestisce le azioni
	// inserite nella character_actions
	// - si caricano i parametri dal database
	// - si esegue l' azione in base ai parametri
	// - si mette l' azione in stato completed
	
	public function complete_action( $data ) {}
	
	public function execute_action ( $par, &$message) 
	{
		// Remove items.
		
		foreach ($par[1]-> getNeededmaterialfornextlevel() as $itemtag => $neededquantity)
		{
			$item = Item_Model::factory(null, $itemtag);
			$item -> removeitem( "structure", $par[1]-> id, $neededquantity);		
		}
		
		// Set status to building
		
		$par[1] -> status = 'upgrading';
		$par[1] -> hourlywage = 0;
		$par[1] -> save();
				
		// evento in town crier
		
		Character_Event_Model::addrecord( 
			null, 
			'announcement', 
			'__events.startedupgrade' .
			';' . $par[0] -> name . 
			';__' . $par[1] -> structure_type -> name . 
			';' . ($par[1] -> getCurrentlevel() + 1) . 
			';__' . $par[1] -> region -> name			
		);
	
		$message = kohana::lang('kingdomprojects.startkingdomproject_ok');
		
		return true;
		
	}
	
	public function cancel_action() { return true; }
	
	// Questa funzione costruisce un messaggio da visualizzare 
	// in attesa che la azione sia completata.
	public function get_action_message( $type = 'long') {}
	
}
