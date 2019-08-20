<?php defined('SYSPATH') OR die('No direct access allowed.');


class CA_Takefromground_Model extends Character_Action_Model
{

	protected $immediate_action = true;
	private $item;

	// con tutte le action che quelli peculiari del seed
	// @input: array di parametri	
	// par[0] : oggetto personaggio che prende l' item
	// par[1] : itemid
	// par[2] : quantità
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function check( $par, &$message )
	{ 
		
		$message = "";
		
		if ( ! parent::check( $par, $message ) )					
		{ return FALSE; }		
		
		// check: l'oggetto esiste nella regione dove è il char?
		
		$this -> item = ORM::factory('item')
			-> where ( array(
				'id' => $par[1],
				'region_id' => $par[0] -> position_id ) ) -> find();
		
		if ($this -> item -> loaded == false )		
		{
			$message = kohana::lang('ca_takefromground.error-itemdonotexists'); 			
			return false;
		}
		
		// check: numero oggetti
				
		if ($par[2] != 999 and $this -> item -> quantity < $par[2] )		
		{
			$message = kohana::lang('ca_takefromground.error-notenoughitems'); 			
			return false;
		}
		
		// check: peso trasportabile
		
		if ( $par[0] -> get_transportableweight() <= 0 )
		{
			$message = kohana::lang('structures.maxtransportableweightreached'); 
			return false;				
		}
		
		return true;
		
	}

	protected function append_action( $par, &$message ) {}

	public function complete_action( $data ) {}
	
	public function execute_action ( $par, &$message ) 
	{
				
		// Update transazionale
		
		$db = Database::instance();		
			
		// lock target row

		Database::instance() -> query("
		SELECT id 
		FROM   items
		WHERE  id = {$this -> item -> id} FOR UPDATE");
		
		if ($par[2] == 999 )
			$quantity = $this -> item -> quantity;
		else
			$quantity = $par[2];
		
		// evento
		
		Character_Event_Model::addrecord(
			$par[0] -> id, 
			'normal', 
			'__events.itemtakenfromground' . 
			';' . $quantity .
			';__' . $this -> item -> cfgitem -> name . 
			';__' . $this -> item -> region -> name );			
	
					
		$this -> item -> additem( "character", $par[0] -> id , $quantity );			
		$this -> item -> removeitem( "region", $par[0] -> position_id, $quantity );		
		
			
		$message = kohana::lang('ca_takefromground.info-itemtaken'); 			
		
		return true;
		
	}
	
}
