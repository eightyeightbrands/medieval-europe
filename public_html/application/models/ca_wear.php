<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Wear_Model extends Character_Action_Model
{
	
	protected $immediate_action = true;
	protected $enabledifrestrained = true;
	protected $item;

	// Effettua tutti i controlli relativi alla wear, sia quelli condivisi
	// con tutte le action che quelli peculiari della wear
	// @input: array di parametri 
	//  	par[0] memorizza l'id dell'oggetto da indossare
	//    par[1] oggettto Character
	
		
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function check( $par, &$message )
	{ 
		
		if ( ! parent::check( $par, $message ) )					
		{ return false; }

		// Istanzio l'oggetto che sto per indossare
		$this -> item = ORM::factory("item", $par[0]);
				
		// Controllo che l'oggetto esista
		if (! $this -> item -> loaded )
		{ $message = kohana::lang('charactions.item_notexist'); return FALSE; }
		
		// Controllo che l'oggetto sia indossabile
		if ( is_null( $this->item -> cfgitem -> part ) ) 			
		{ $message = kohana::lang('charactions.item_notwearable'); return FALSE; }
		
		// controllo che il personaggio abbia la forza adeguata per indossare il capo
		
		if ( !is_null( $this->item->cfgitem->requiredstrength ) and $par[1] -> get_attribute ('str') < $this->item->cfgitem->requiredstrength )
		{ $message = kohana::lang('charactions.error-notenoughstrenghttoequipitem', $this->item->cfgitem->requiredstrength); return FALSE; }
		
		// controllo che l' item esista nel contenitore

		if ( 
			Character_Model::has_item( $par[1] -> id, $this -> item -> cfgitem -> tag, 1 ) == false )
			{ $message = kohana::lang('charactions.item_notininventory'); return FALSE; }
			
		// Controllo che l' oggetto sia opportuno per il sesso
		if ( in_array($this->item->cfgitem->subcategory, array( 'M', 'F') ) and $this->item->cfgitem->subcategory != $par[1]->sex )		
		{ $message = kohana::lang('charactions.item_wrongsex'); return FALSE; }
		
		// Controllo che l' oggetto sia compatibile con le limitazioni per ruolo		
		if ( !is_null( $this->item -> cfgitem -> linked_role) )
		{
			
			// se l'oggetto è di chiesa e il linked ROLE = ALL
			// puÃ² essere indossato da tutti i ruoli meno il prete.			
			
			$role = $par[1] -> get_current_role();
			
			if ( !is_null($role) 
				and !in_array( $role -> tag, 	array( 'church_level_1', 'church_level_2', 'church_level_3' ) )
				and $this->item -> cfgitem -> linked_role == 'ALL'				
				and !is_null( $this->item -> cfgitem -> church_id ) )			
			{ $message = kohana::lang('charactions.item_wrongrole'); return FALSE; }
			
			
			// Se è impostato un ruolo e il char non ne ha uno restituisco errore
			
			if ( is_null( $role ) )
			{ $message = kohana::lang('charactions.item_wrongrole'); return FALSE; }
						
			// Se il char ha un ruolo differente da quello impostato nell'oggetto allora restituisco errore			
			
			if ( $this->item -> cfgitem -> linked_role != 'ALL' and $par[1] -> get_current_role() -> tag != $this->item -> cfgitem -> linked_role )
			{ $message = kohana::lang('charactions.item_wrongrole'); return FALSE; }
			
			// Se l' item ha una church_id diversa non puÃ² essere indossato
			if ( !is_null( $this->item -> cfgitem -> church_id ) and $this->item -> cfgitem -> church_id != $par[1] -> church_id )
			{ $message = kohana::lang('charactions.item_wrongrole'); return FALSE; }
						
		}	
			
		// se l' item che si vuole indossare ha destinazione body, il 
		// giocatore nn puo avere niente indossato su torso e legs				
		
		if ( $this->item->cfgitem->part == 'body' and
		( $par[1]->get_bodypart_item('torso') != false or $par[1]->get_bodypart_item('legs') != false )
		)	
		{ $message = kohana::lang('charactions.incompatible_worn_items_1'); return FALSE; }
		// e vicecersa
		if (( $this->item->cfgitem->part == 'torso' or $this->item->cfgitem->part == 'legs' ) 
			and 
				$par[1]->get_bodypart_item('body') != false)
		{ $message = kohana::lang('charactions.incompatible_worn_items_2'); return FALSE; }
		
		return true;
	}

	
	// nessun controllo particolare
	protected function append_action( $par, &$message )
	{	}

	// @input: par[0] memorizza l'id dell'oggetto che sto indossando
	
	public function execute_action ( $par, &$message ) 
	{
		// Carico l'oggetto che devo indossare		
		
		$this -> item = ORM::factory("item", $par[0]);
		$destination = 'undefined';	
		
		kohana::log('debug', 'item to wear: ' . $this->item->cfgitem->tag . ' category: ' . $this->item->cfgitem->category );
				
		$iteminrighthand = $par[1]->get_bodypart_item('right_hand');
		$iteminlefthand = $par[1]->get_bodypart_item('left_hand');
		
		// Se l'oggetto che andrÃ² ad indossare è un'arma large
		// allora dovrÃ² rimuovere gli oggetti presenti in entrambe le mani
		
		$removeleft=$removeright=false;

		if ($this->item->cfgitem->category == 'weapon' AND $this->item->cfgitem->size == 'L')
		{
			$removeright=true;
			$removeleft=true;
			$destination = 'right_hand';
		}
		
		// Se non sto per impugnare un'arma Large allora rimuovo solo
		// l'item presente nella mano dx
		if ($this->item->cfgitem->category == 'weapon' AND $this->item->cfgitem->size != 'L')
		{
			$removeright=true;
			$destination = 'right_hand';
		}

		// Se sto per impugnare uno scudo, rimuovo l'item presente nella mano sinistra
		// ed eventualmente quello presente sulla destra se sto impugnando un'arma Large
		
		if ($this->item->cfgitem->subcategory == 'shield' )
		{
			if ( 
				$iteminrighthand 
				and 
				$iteminrighthand->cfgitem->category=='weapon' 
				and 
				$iteminrighthand->cfgitem->size == 'L' 
			)			
				$removeright=true;
			
			$removeleft = true;			
			$destination = 'left_hand';			
			
		}
		
		// tutti gli altri tipi sono indossati secondo configurazione
		
		if ( $this->item->cfgitem->category != 'weapon' and $this->item->cfgitem->subcategory != 'shield' )
		{
			$itemtoremove = $par[1]->get_bodypart_item($this->item->cfgitem->part);
			if ( $itemtoremove )
			{
				kohana::log('debug', 'wear: i must remove: ' . $itemtoremove->cfgitem->tag );
				$itemtoremove->equipped='unequipped';
				$itemtoremove->save();
			}
			
			$destination = $this->item->cfgitem->part ;
		}
		
		// rimozione oggetti equipaggiati
		
		if ( $removeleft and $iteminlefthand )
		{
			$iteminlefthand->equipped = 'unequipped';
			$iteminlefthand->save();
		}
		
		if ( $removeright and $iteminrighthand )
		{
			$iteminrighthand->equipped = 'unequipped';
			$iteminrighthand->save();
		}
		
		if ( $this -> item -> quantity > 1 )
		{		
			
			// se l' item è nella struttura e la quantity è > 1, scorporalo
			// lo clono e lo rimuovo dal container.
			
			$newitem = $this -> item -> cloneitem();
			
			$this -> item -> removeitem( 'character', $par[1] -> id, 1 );			
			
			$newitem -> quantity = 1;			
			$newitem -> character_id = $par[1] -> id;
			$newitem -> structure_id = null;
			$newitem -> equipped = $destination;	
			$newitem -> save();
			
		}
		else
		{
			
			if (!is_null($this -> item -> structure_id ))
			{
				$this -> item -> structure_id = null;
				$this -> item -> character_id = $par[1]->id;
			}
			
			$this -> item -> equipped = $destination;
			$this -> item -> save();
		}
		
		$message = sprintf( kohana::lang( 'charactions.wear_ok'),  kohana::lang($this->item->cfgitem->name) );
		
		return true;
	
	}
}