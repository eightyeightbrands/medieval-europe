<?php defined('SYSPATH') OR die('No direct access allowed.');


class CA_Fish_Model extends Character_Action_Model
{
	// Costanti	
	const DELTA_GLUT = 6;
	const DELTA_ENERGY = 12;

	protected $cancel_flag = true;
	protected $immediate_action = false;
	
	protected $basetime       = 3;  // 3 ore
	protected $attribute      = 'str';  // attributo forza
	protected $appliedbonuses = array ( 'workerpackage'); // bonuses da applicare

	// L'azione richiede che il personaggio indossi
	// un determinato equipaggiamento
	protected $requiresequipment = true;
	
	// Equipaggiamento o vestiario necessario in base al ruolo
	// Consume_rate = percentuale di consumo dell'item
	protected $equipment = array
	(
		'all' => array
		(
			'body' => array
			(
				'items' => array('any'),
				'consume_rate' => 'medium'
			),
			'torso' => array
			(
				'items' => array('any'),
				'consume_rate' => 'medium'
			),
			'legs' => array
			(
				'items' => array('any'),
				'consume_rate' => 'medium'
			),
			'right_hand' => array
			(
				'items' => array('fishing_net'),
				'consume_rate' => 'medium',
			),
		),
	);
	
	// Effettua tutti i controlli relativi al move, sia quelli condivisi
	// con tutte le action che quelli peculiari del move
	// @input: $par[0] = structure, $par[1] = char, $par[2] = parametro code
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function check( $par, &$message )
	{ 
		// Check classe madre (compreso il check_equipment)
		if ( ! parent::check( $par, $message ) )					
		{ return false; }
		
		// controllo parametri URL
		$queuebonus = false;
		if ( Character_Model::get_premiumbonus( $par[1] -> id, 'workerpackage') !== false )			
			$queuebonus = true;
		
		// Controllo, se il moltiplicatore è > 1, il char deve avere il bonus
		if ( !in_array ( $par[2], array( 1, 2, 3 )) or ($par[2] > 1 and ! $queuebonus ) )
				{ $message = Kohana::lang("global.operation_not_allowed"); return false; }
		
		// Controllo che il char abbia l'energia e la sazieta' richiesti
		if (
			$par[1]->energy < (self::DELTA_ENERGY * $par[2])  or
			$par[1] -> glut < (self::DELTA_GLUT * $par[2]) )
		{   $message = Kohana::lang("charactions.notenoughenergyglut"); return false; }		
		
		
		if ( $par[2] > 1 )
		{
			$items = $this -> computeproduction( $par );		
			foreach ( $items as $key => $value )
			{
				foreach ( $par[0] -> structure_resource as $resource )
					if ( $resource -> resource == $key )				
						if ( $par[0] -> check_resource_status( $key, $value ) == false )
						{ $message = kohana::lang('ca_fish.resourcenotenough'); return FALSE; }
			}		
		}
		else
		{
			$resource_status = $par[0] -> check_resource_status( 'fish', 1 );		
			
			if ( $resource_status == false )
				{ $message = kohana::lang('ca_fish.resourceisdepleted'); return FALSE; }
		}
		
		return true;
	}
	
	/*
	* Prevede quante unità estrarre in base alle caratteristiche
	* del char e ad altri parametri
	*/
	
	private function computeproduction( $par )
	{
				
		$item = Item_Model::factory( null, 'fish' );
		
		// Applica fattore 'carestia'
		
		$production = $item -> computeproduction( $par[1] ) * $par[2];
		
		kohana::log('debug', "-> Original produced quantity: {$production}");
		
		$productionfactor = Kohana::config('medeur.productionfactor');								
		$production = round( max(1, $production * $productionfactor / 100), 0);
		
		kohana::log('debug', "-> Original produced quantity after production factor: {$production}");
		
		$items[$item->cfgitem->tag] = $production;
		
		return $items ;
		
	}

	// Funzione per l'inserimento dell'azione nel DB.
	// Questa funzione appende solo una azione _non la esegue_
	// Al momento non si prevedono azioni istantanee.
	// @input: $par[0] = structure, $par[1] = char
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function append_action( $par, &$message )
	{
		
		$items = $this -> computeproduction( $par );
		$param2 = '';		
		// toglie la quantità alle risorse
		foreach ( $items as $key => $value )
		{
			foreach ( $par[0] -> structure_resource as $resource )
				if ( $resource -> resource == $key )
				{ 					
					$quantity = min ( $resource -> current, $value );					
					$param2 .= $key.'-'.$quantity.';';
					$resource -> modify_quantity( - $quantity );
					$resource -> save();
				}
		}
		
		$timeaction = $this->get_action_time( $par[1] )* $par[2];
		$this->character_id = $par[1]->id;
		$this->starttime = time();			
		$this->status = "running";	
		$this->param1 = $par[0] -> id;
		$this->param2 = $param2;
		$this->param3 = $par[2];
		$this->endtime = $this->starttime + $timeaction;			
		$this->save();		
								
		$message = kohana::lang('ca_fish.fish-ok'); 
		return true;
	}

	// Esecuzione dell' azione. 
	// Questa funzione viene chiamata quando viene invocata una 
	// complete_expired_action e gestisce le azioni
	// inserite nella character_actions
	// - si caricano i parametri dal database
	// - si esegue l' azione in base ai parametri
	// - si mette l' azione in stato completed
	
	public function complete_action( $data )
	{
		
		$char = ORM::factory('character')->find( $data->character_id );

		// Consumo degli items/vestiti indossati
		Item_Model::consume_equipment( $this->equipment, $char, $data->param3 );	
		
		///////////////////////////////////////////////////////////////////
		// Sottraggo l'energia e la sazietà al char
		///////////////////////////////////////////////////////////////////
		
			// Sottraggo l'energia e la sazietà al char
		$char->modify_energy ( - self::DELTA_ENERGY * $data->param3, false, 'fishing' );
		$char->modify_glut ( - self::DELTA_GLUT * $data->param3);	
		$char->save();	
		
		$items = explode ( ';', $data -> param2, -1);
		//kohana::log('debug', 'items: ' . kohana::debug($items )); exit; 
		foreach ( $items as $item )
		{
			list ( $itemtag, $quantity ) = explode ('-', $item ); 						
			
			if ( $quantity > 0 )
			{
				$item = Item_Model::factory( null, $itemtag );				
				kohana::log('debug', 'Added item: ' . $item -> cfgitem -> tag ); 
				$item->additem("character", $char->id, $quantity);
				
						
				Character_Event_Model::addrecord( $char -> id, 
				'normal', '__events.fishok' . 
				';' . $quantity . 
				';__' . $item ->cfgitem -> name,
				'normal' );
				$char -> modify_stat( 'itemproduction', $quantity, $item->cfgitem->id );		
			}
			else
			{
						
				Character_Event_Model::addrecord( $char -> id, 'normal', '__events.fishnotok', 'normal' );
			}
		}			
			
		// Cache
		My_Cache_Model::delete('-cfg-regions-resources');	
	}
	
	protected function execute_action() {}
	
	public function cancel_action( )
	{						
		$structure = StructureFactory_Model::create( null, $this -> param1 );
		$items = explode ( ';', $this -> param2, -1 );
		foreach ( $items as $item )
		{
			list ( $itemtag, $quantity ) = explode ('-', $item ); 
			foreach ( $structure -> structure_resource as $resource )
			if ( $resource -> resource == $itemtag )
			{ 					
				$resource -> modify_quantity( + $quantity );
				$resource -> save();
			}
		}
		
		return true; 
		
	}
	
	// Questa funzione costruisce un messaggio da visualizzare 
	// in attesa che la azione sia completata.
	public function get_action_message( $type = 'long') 
	{
		$pending_action = $this->get_pending_action();
		$message = "";				
		
		if ( $pending_action -> loaded )
		{
			if ( $type == 'long' )		
			$message = '__regionview.fish_longmessage';
			else
			$message = '__regionview.fish_shortmessage';
		}
		return $message;
	
	}
	
}
