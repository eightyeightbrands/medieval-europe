<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Damage_Model extends Character_Action_Model
{

	// Costanti	
	const DELTA_GLUT = 6;
	const DELTA_ENERGY = 12;
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
				'items' => array('pickaxe'),
				'consume_rate' => 'veryhigh',
			),
		),
	);
	
	// con tutte le action che quelli peculiari del seed
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
		
		// check: age > 30
		
		if ( $par[1] -> is_newbie($par[1])==true )
		{ $message = kohana::lang('ca_damage.notoldenough'); return FALSE; }				
		
		if ( $par[0] -> structure_type -> supertype == 'buildingsite' )
		{
			$structuretobebuilt = ORM::factory('structure_type', $par[0] -> attribute1 );
			if ( $structuretobebuilt -> subtype != 'church' )
			{
				$message = Kohana::lang('ca_damage.error-cantdamagethisstructure');
				return false;
			}
		}
		else
			if ( !in_array( $par[0] -> structure_type -> type, array( 'religion_2', 'religion_3', 'religion_4')))
				{ $message = Kohana::lang('ca_damage.error-cantdamagethisstructure'); return false; }
				
		// il giocatore deve essere di una chiesa o ateo ma del regno
		// dove la chiesa risiede
		
		if ( $par[1] -> church -> name == 'nochurch' and $par[1] -> region -> kingdom_id != $par[0] -> region -> kingdom_id ) 
		{ $message = Kohana::lang("ca_damage.error-charisatheist"); return false; }
		
		// check: non è possibile danneggiare una struttura se esistono delle
		// strutture figlie
		
		$childstructures = ORM::factory('structure') -> where
			( array ( 'parent_structure_id' => $par[0] -> id  ) ) -> find_all();
			
		if ( count($childstructures) > 0 )
		{ $message = Kohana::lang("ca_damage.error-childstructurefound"); return false; }
	
		// check: non è possibile danneggiare una struttura se la diplomazia del regno della struttura è ostile 
		// con quella del pg che compie l'azione
		
		$dr = Diplomacy_Relation_Model::get_diplomacy_relation( 
			$par[0] -> region -> kingdom_id , 
			$par[1] -> region -> kingdom_id );
		
		if ( !is_null( $dr ) 
			and $dr['type'] == 'hostile' )
		{
			$message = kohana::lang('ca_damage.error-hostileaccessdenied'); 
			return false;				
		}
						
		// check bonus queue
		
		$queuebonus = false;
		if ( Character_Model::get_premiumbonus( $par[1] -> id, 'workerpackage') !== false )			
			$queuebonus = true;
				
		// Controllo, se il moltiplicatore è > 1, il char deve avere il bonus
		if ( !in_array ( $par[2], array( 1, 2, 3 )) or ($par[2] > 1 and ! $queuebonus ) )
			{ $message = Kohana::lang("global.operation_not_allowed"); return false; }
		
		// check: se < 10% e ha strutture figlie, errore
		$childstructures = $par[0] -> get_childstructures();				
		
		if ( $par[0] -> state <= 10 and $childstructures -> count() > 0 ) 
			{ $message = Kohana::lang("ca_damage.childstructurepresent"); return false; }
		
		// check: il char è nella stessa regione della struttura
				
		if ( $par[1] -> position_id != $par[0] -> region_id )
		{	$message = kohana::lang('global.operation_not_allowed'); return false; }		
		
		// check: livelli glut e energy
		
		if (
			$par[1] -> energy < self::DELTA_ENERGY * $par[2] or
			$par[1] -> glut < self::DELTA_GLUT * $par[2] )
		{	$message = Kohana::lang("charactions.notenoughenergyglut");	return false;	}

		
		return true;
		
	}

	protected function append_action( $par, &$message )	{	
		
		if ( $par[0] -> structure_type -> type == 'religion_4' )
			$this -> basetime *= 2;
		
		if ( $par[0] -> structure_type -> type == 'religion_3' )
			$this -> basetime *= 3;
		
			if ( $par[0] -> structure_type -> type == 'religion_2' )
			$this -> basetime *= 4;
						
		$this -> character_id = $par[1] -> id;
		$this -> starttime = time();			
		$this -> status = "running";			
		$this -> endtime = $this -> starttime + $this -> get_action_time( $par[1] ) * $par[2];
		$this -> param1 = $par[0] -> id;	
		$this -> param2 = $par[2];
		$this -> param3 = $this -> basetime;
		$this -> save();		

		$message = kohana::lang('ca_damage.damage-ok');
		
		return true;
	
	}

	public function complete_action( $data )	{	
	
		
		$character = ORM::factory('character', $data -> character_id );
		$structure = StructureFactory_Model::create( null, $data -> param1 );
		
		// Consumo degli items/vestiti indossati
		Item_Model::consume_equipment( $this->equipment, $character, $data->param2 );	
				
		// decrementa la qualità della struttura		
		if ( $structure -> loaded )
		{
			if ( $character -> church -> name == 'nochurch' )
				$decrement = 0.5;
			else
				$decrement = 1;
				
			kohana::log('info', 'Damaging structure ' . $structure -> id . ' : ' . $decrement  * $data -> param2 ); 
			
			$structure -> state = $structure -> state - ($decrement * $data -> param2) ;
			$structure -> save();
		}
		
		else
			return;		
		
		// decremente il livello di fede solo se il char è della stessa religione
		if ( $structure -> structure_type -> church_id == $character -> church_id )
			$character -> modify_faithlevel( $data -> param2 * -15 );
		
		// decremente il numero di AFP per ogni danneggiamento se il char è della stessa religione
		if ( $structure -> structure_type -> church_id == $character -> church_id )
			$character -> modify_stat( 
				'fpcontribution', $data -> param2 * -25, $character -> church_id );

		// aumenta il livello di fede solo se il char è di diversa religione	
		if ( $structure -> structure_type -> church_id != $character -> church_id and $character -> church -> name != 'nochurch' )
			$character -> modify_faithlevel( $data -> param2 * 2 );
	
		// aumenta il numero di AFP solo se il char è di diversa religione	
		if ( $structure -> structure_type -> church_id != $character -> church_id and $character -> church -> name != 'nochurch' )
			$character -> modify_stat( 
				'fpcontribution', $data -> param2 * 5, $character -> church_id );			

		// add a structure event
		Structure_Event_Model::newadd( $structure -> id, 
			'__events.structuredamage;' . $character -> name . ';' . $decrement * $data -> param2 ); 
		
		// send event to owner
		if ( !is_null( $structure -> character -> id ) )
			Character_Event_Model::addrecord(
					$structure -> character -> id,
					'normal',
					'__events.structuredamageowner;__' . $structure -> structure_type -> name . ';__' . 
						$structure -> region -> name,
					'evidence'
				);
			
		// send event to damager
		Character_Event_Model::addrecord(
				$data -> character_id,
				'normal',
				'__events.structuredamagedamager;__' . $structure -> structure_type -> name . ';__' . 
					$structure -> region -> name . ';' . 	$decrement * $data -> param2,				
				'normal'
			);	
		
		// se la condizione della struttura è < 0, distruggila		
		// distruggi anche tutti le strutture figlie se in costruzione
		
		if ( $structure -> state <= 0 )
		{
			// solo se non è un cantiere, rimuovi il ruolo
			if ($structure -> structure_type -> supertype != 'buildingsite' )			
			{
				$role = $structure -> character -> get_current_role() ; 
				if ( !is_null( $role ) )
					$role -> end(); 
			}
			
			// evento
			
			Character_Event_Model::addrecord(
				null,
				'announcement',
				'__events.structurecompletelydamaged;__' . $structure -> structure_type -> name . ';__' . 
					$structure -> region -> name,					
				'evidence'
			);
			
			$structure -> destroy (); 
		}
		// 	
		
		// take off energy and glut
		$character -> modify_energy( - self::DELTA_ENERGY * $data -> param2, false, 'damagestructure' );
		$character -> modify_glut( - self::DELTA_GLUT * $data -> param2 );
		$character -> save();
			
		
		
		return;		
	
	}
		
	public function execute_action ( $par, &$message ) 	{	}
	
	public function cancel_action() { return true ; }
	
	// Questa funzione costruisce un messaggio da visualizzare 
	// in attesa che la azione sia completata.
	
	public function get_action_message( $type = 'long') 
	{
		$pending_action = $this->get_pending_action();
		$message = "";				
		
		if ( $pending_action -> loaded )
		{
			if ( $type == 'long' )		
			$message = '__regionview.damage_longmessage';
			else
			$message = '__regionview.damage_shortmessage';
		}
		return $message;
	
	}
}
