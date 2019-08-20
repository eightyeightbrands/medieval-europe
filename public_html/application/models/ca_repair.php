<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Repair_Model extends Character_Action_Model
{

	// Costanti	
	const DELTA_GLUT = 6;
	const DELTA_ENERGY = 12;
	const CONDITION_INCREMENT = 2.5; 	
	protected $cancel_flag = true;
	protected $immediate_action = false;	
	protected $basetime       = 1;  // 1 ora
	protected $attribute      = 'str';  // attributo forza
	protected $appliedbonuses = array ( 'workerpackage' ); // bonuses da applicare

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
				'consume_rate' => 'veryhigh'
			),
			'torso' => array
			(
				'items' => array('any'),
				'consume_rate' => 'veryhigh'
			),
			'legs' => array
			(
				'items' => array('any'),
				'consume_rate' => 'veryhigh'
			),
			'right_hand' => array
			(
				'items' => array('hammer'),
				'consume_rate' => 'veryhigh',
			),
		),
	);
	
	// @input: array di parametri	
	// par[0] : oggetto structure su cui fare l' azione
	// par[1] : oggetto char che esegue l' azione
	// par[2] : parametro code
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function check( $par, &$message )
	{ 
		// Check classe madre (compreso il check_equipment)
		if ( ! parent::check( $par, $message ) )					
		{ return false; }
		
		// check dati
		if ( !$par[0] -> loaded or !$par[1] -> loaded )
		{ $message = Kohana::lang("global.operation_not_allowed"); return false; }
		
		if ( $par[1] -> get_age() < 30 )
		{ $message = kohana::lang('ca_damage.notoldenough'); return FALSE; }				
		
		// il giocatore deve essere di una chiesa
		if ( $par[1] -> church -> name == 'nochurch' ) 
		{ $message = Kohana::lang("ca_damage.error-charisatheist"); return false; }
		
		
		// check: la struttura deve essere di tipo religiosa
		
		if ( $par[0] -> structure_type -> supertype == 'buildingsite' )
		{
			$structuretobebuilt = ORM::factory('structure_type', $par[0] -> attribute1 );
			if ( $structuretobebuilt -> subtype != 'church' )
			{
				$message = Kohana::lang('global.operation_not_allowed');
				return false;
			}
		}
		else
			if ( !in_array( $par[0] -> structure_type -> type, array( 'religion_2', 'religion_3', 'religion_4')))
				{ $message = Kohana::lang('global.operation_not_allowed'); return false; }
		
		if ( $par[0] -> state == 100 )
		{ $message = Kohana::lang("global.operation_not_allowed"); return false; }
		
		// check bonus queue
		
		$queuebonus = false;		
		if ( Character_Model::get_premiumbonus( $par[1] -> id, 'workerpackage') !== false )			
			$queuebonus = true;
				
		// Controllo, se il moltiplicatore è > 1, il char deve avere il bonus
		
		if ( !in_array ( $par[2], array( 1, 2, 3 )) or ($par[2] > 1 and ! $queuebonus ) )
			{ $message = Kohana::lang("global.operation_not_allowed"); return false; }							
				
		// il char è nella stessa regione della struttura?		
				
		if ( $par[1] -> position_id != $par[0] -> region_id )
		{	$message = kohana::lang('global.operation_not_allowed'); return false; }		
		
		// check: livelli glut e energy
		if (
			$par[1] -> energy < self::DELTA_ENERGY * $par[2] or
			$par[1] -> glut < self::DELTA_GLUT * $par[2] )
		{	$message = Kohana::lang("charactions.notenoughenergyglut");	return false;	}

		// controllo che nella struttura ci sia il materiale adatto
		if ( $par[0] -> get_item_quantity( 'wood_piece' ) < ( 1 * $par[2] ) or $par[0] -> get_item_quantity( 'iron_piece' ) < ( 1 * $par[2] ) )
		{ $message = kohana::lang('ca_repair.needematerialnotpresent'); return FALSE; }
		
		;
		
		return true;
		
	}

	protected function append_action( $par, &$message )	{	
	
		// togli un pezzo di ferro ed un pezzo di legno dalla struttura
		$item = Item_Model::factory(null, 'wood_piece');
		$item -> removeitem( "structure", $par[0] -> id, 1 * $par[2]);
		$item = Item_Model::factory(null, 'iron_piece');
		$item -> removeitem( "structure", $par[0] -> id, 1 * $par[2]);
		
		$this -> character_id = $par[1] -> id;
		$this -> starttime = time();			
		$this -> status = "running";	
		
		if ( $par[0] -> structure_type -> type == 'religion_4' )
			$this -> basetime *= 2;
		if ( $par[0] -> structure_type -> type == 'religion_3' )
			$this -> basetime *= 3;
		if ( $par[0] -> structure_type -> type == 'religion_2' )
			$this -> basetime *= 4;
		
		$this -> endtime = $this -> starttime + $this -> get_action_time( $par[1] ) * $par[2];
		$this -> param1 = $par[0] -> id;	
		$this -> param2 = $par[2];
		$this -> param3 = $this -> basetime;
		$this -> save();		

		$message = kohana::lang('ca_repair.repair-ok');
		
		return true;
	
	}

	public function complete_action( $data )	{	
	
		$character = ORM::factory('character', $data -> character_id );
		$structure = StructureFactory_Model::create( null, $data -> param1 );
		
		// Consumo degli items/vestiti indossati
		Item_Model::consume_equipment( $this->equipment, $character, $data->param2 );	
		
		// incrementa la qualità della struttura
		// cap a 100%
		if ( $structure -> loaded )
		{
			$structure -> state += self::CONDITION_INCREMENT * $data -> param2 ;
			if ( $structure -> state > 100 )
				$structure -> state = 100 ;
			$structure -> save();
		}
		else
			return;		
		
		// incremente il livello di fede solo se il char è della stessa religione
		if ( $structure -> structure_type -> church_id == $character -> church_id )
			$character -> modify_faithlevel( $data -> param2 * 2 );
		
		// aumenta il numero di AFP solo se il char è della stessa religione
		if ( $structure -> structure_type -> church_id == $character -> church_id )
			$character -> modify_stat( 'fpcontribution', $data -> param2 * 5, $character -> church_id );			
				
		// diminuisce il livello di fede solo se il char è di diversa religione	
		if ( $structure -> structure_type -> church_id != $character -> church_id and $character -> church -> name != 'nochurch' )
			$character -> modify_faithlevel( $data -> param2 * -10 );
		
		// decremente il numero di AFP per ogni danneggiamento se il char è di diversa religione
		if ( $structure -> structure_type -> church_id != $character -> church_id )
			$character -> modify_stat( 'fpcontribution', $data -> param2 * -15, $character -> church_id );			
		
		// add a structure event		
		Structure_Event_Model::newadd( $structure -> id, 
			'__events.structurerepair;' . $character -> name . ';' . self::CONDITION_INCREMENT * $data -> param2 ); 
			
		// send event to repairer
		Character_Event_Model::addrecord(
				$data -> character_id,
				'normal',
				'__events.structurerepairrepairer;__' . $structure -> structure_type -> name . ';__' . 
					$structure -> region -> name . ';' . 	self::CONDITION_INCREMENT * $data -> param2,				
				'normal'
			);	
		
		// take off energy and glut
		
		$character -> modify_energy( - self::DELTA_ENERGY * $data -> param2, false, 'repairstructure' );
		$character -> modify_glut( - self::DELTA_GLUT * $data -> param2 );
		$character -> save();			
		
		return;		
	
	}
		
	public function execute_action ( $par, &$message ) 	{	}
	
	public function cancel_action() { return true; }
	
	// Questa funzione costruisce un messaggio da visualizzare 
	// in attesa che la azione sia completata.
	
	public function get_action_message( $type = 'long') 
	{
		$pending_action = $this -> get_pending_action();
		$message = "";				
		$structure = StructureFactory_Model::create( null, $pending_action -> param1 );
				
		if ( $pending_action -> loaded )
		{
			if ( $type == 'long' )		
				$message = '__regionview.repair_longmessage;__' . $structure -> structure_type -> name;
			else
				$message = '__regionview.repair_shortmessage';
		}
		return $message;
	
	}
	
}
