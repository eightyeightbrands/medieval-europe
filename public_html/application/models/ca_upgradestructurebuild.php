<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Upgradestructurebuild_Model extends Character_Action_Model
{

	// Costanti
	
	const DELTA_GLUT_X_HOUR = 2;
	const DELTA_ENERGY_X_HOUR = 5;
	
	protected $cancel_flag = true;
	protected $immediate_action = false;		
	protected $basetime       = 1;  // 1 ora
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
	
	// con tutte le action che quelli peculiari del seed
	// @input: array di parametri	
	// par[0] : oggetto char che effettua l' azione
	// par[1] : oggetto structure su cui fare l' azione
	// par[2] : numero di ore di lavoro	
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function check( $par, &$message )
	{ 
		// Check classe madre (compreso il check_equipment)
		
		if ( ! parent::check( $par, $message ) )					
		{ return false; }	
		
		if ( $par[2] <= 0 )
		{		
			$message = kohana::lang('ca_upgradestructure.error-minbuildhoursmustbegreaterthanzero');
			return false;
		}	
		
		if ( $par[2] > 9 )
		{		
			$message = kohana::lang('ca_upgradestructure.error-maxbuildhoursexceeded');
			return false;
		}			
		
		// controllo energia.
		
		if (
			$par[0] -> energy < self::DELTA_ENERGY_X_HOUR * $par[2] or
			$par[0] -> glut < self::DELTA_GLUT_X_HOUR * $par[2] )
		{
			$message = Kohana::lang("charactions.notenoughenergyglut");
			return false;
		}
		
		// controllo che si possa lavorare al progetto
		
		if ( $par[1] -> status != 'upgrading' )
		{
			$message = kohana::lang('ca_upgradestructure.error-structureisnotupgrading'); 
			return false;		
		}		
					
		// Controllo che ci siano abbastanza soldi nella struttura.
		
		if ( $par[1] -> get_item_quantity( 'silvercoin' ) < $par[1] -> hourlywage * $par[2] )
		{
			$message = kohana::lang('ca_upgradestructure.error-structurehasnotenoughmoneytopaywages'); 
			return false;		
		}
		
		return true;
	}
		
		
	protected function append_action( $par, &$message )	{	
		
		// toglie soldi dalla struttura.
		
		$wage = $par[2] * $par[1] -> hourlywage;
		$par[1] -> modify_coins( -$wage , 'buildsalary' );			
		$par[1] -> save();
			
		$this -> character_id = $par[0] -> id;
		$this -> starttime = time();			
		$this -> status = "running";	
		$this -> basetime = $par[2];
		$this -> endtime = $this  ->  starttime + $this -> get_action_time( $par[0] );
		$this -> param1 = $par[1] -> id;
		$this -> param2 = $wage;
		$this -> param3 = $par[2];
		$this -> save();		
		
		$message = kohana::lang('charactions.workonproject_ok');
		
		Character_Event_Model::addrecord(
				$par[0] -> id,
				'normal',
				'__events.startprojectwork' 
				);	

		return true;
	
	}

	public function complete_action( $data )
	{	
	
		$character = ORM::factory('character', $data -> character_id );
		
		// Consumo degli items/vestiti indossati	
		
		Item_Model::consume_equipment( $this->equipment, $character );			
		$structure = StructureFactory_Model::create( null, $data -> param1);				
		
		// take off energy and glut, give money
		
		$character -> modify_energy( - self::DELTA_ENERGY_X_HOUR * $data -> param3, false, 'workonstructure' );
		$character -> modify_glut( - self::DELTA_GLUT_X_HOUR * $data -> param3 );		
		$character -> modify_coins( $data -> param2, 'buildsalary' );		
		
		// upgradedworked hours indica il numero di ore lavorate sulla struttura
		$stat = Structure_Model::get_stat_d(  $structure -> id, 'upgradeworkedhours' ); 
		
		if ($stat -> loaded )
		{
			$workedhours = $stat -> value;			 
		}
		else
			$workedhours = 0;		
		
		// salva statistica personaggio
		
		$structure -> modify_stat( 
			'workedhours', 
			$data -> param3,
			$character -> id, 
			null,			
			$character -> id, 
			$character -> name, 
			false	
			);
		
		if ($workedhours + $data -> param3 >= $structure -> getHoursfornextlevel() )
		{
						
			kohana::log('debug', '-> Upgrading structure...');
			// Reset worked hours
			
			$structure -> modify_stat( 
				'upgradeworkedhours', 
				0, 
				null, 
				null,			
				null,
				null,				
				true );	
			
			// Upgrade structure
			
			$upgradedstructure = StructureFactory_Model::create( 
				$structure -> getSuperType() . '_' . ($structure -> getCurrentlevel() + 1), null );
			
			// Evento Town Crier
			if ($structure -> structure_type -> subtype == 'government' )
				Character_Event_Model::addrecord( 
					null, 
					'announcement', 
					'__events.structureupgraded' . 				
					';__' . $structure -> region -> kingdom -> name . 
					';__' . $upgradedstructure -> structure_type -> name . 
					';__' . $structure -> region -> name,				
					'evidence'
					);			
				
			$structure -> structure_type_id = $upgradedstructure -> structure_type_id;
			$structure -> status = null;
			$structure -> save();
			
		}
		else
			$structure -> modify_stat( 'upgradeworkedhours', 
				$data -> param3,
				null,			
				null,
				null,
				null,
				false	
			); 
		
		
		$structuretext = '__events.projectwork;' . $character -> name . ';' . $data -> param3;
		$chartext = '__events.endprojectwork;' . $data -> param3 ;
	
		$character -> save();					
		
		// add a character event
		
		Character_Event_Model::addrecord(
			$character -> id,
			'normal',
			'__events.endprojectwork;' . $data -> param3
			);	

		return true;
	
	}
		
	public function execute_action ( $par, &$message ) 	{	}
	
	public function cancel_action( )	{	
		
		$character = ORM::factory('character', $this -> character_id );		
		$structure = StructureFactory_Model::create( null, $this -> param1 );		
		$structure -> modify_coins( $this -> param2, 'buildcanceled'); 
		$structure -> save();
		
		return true;
		
	}
	
}
