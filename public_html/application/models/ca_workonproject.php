<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Workonproject_Model extends Character_Action_Model
{

	// Costanti
	const DELTA_GLUT_X_HOUR = 2;
	const DELTA_ENERGY_X_HOUR = 5;
	protected $cancel_flag = false;
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
	// par[3] : oggetto project
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function check( $par, &$message )
	{ 
	
		// Check classe madre (compreso il check_equipment)
		
		if ( ! parent::check( $par, $message ) )					
		{ return false; }
	
		$db = Database::instance();		
				
		// controllo dati
		if ( ! $par[1] -> loaded )
		{		
			$message = kohana::lang('global.operation_not_allowed'); 
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
		
		// controllo ore di lavoro
		
		if ( $par[2] <= 0 )
		{
			$message = Kohana::lang("ca_upgradestructure.error-maxbuildhoursexceeded");
			return false;
		}
		
		if ( $par[2] > 9 )
		{
			$message = Kohana::lang("ca_upgradestructure.error-minbuildhoursmustbegreaterthanzero");
			return false;
		}
		
		// controllo che il cantiere abbia abbastanza soldi
		
		// controllo che si possa lavorare al progetto
		
		if ( $par[3] -> is_buildable() === false )
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
		
		
		// se la struttura presente è religiosa e esistono già nella regione
		// strutture (completate) di altre chiese, non è possibile procedere alla
		// costruzione
		
		$structuretype = ORM::factory('structure_type', $par[1] -> attribute1 );
		
		if ( $structuretype -> subtype == 'church' )
		{
			$res = $db -> query ( "select s.id from structures s, structure_types st
			where s.structure_type_id = st.id 
			and   st.subtype = 'church' 
			and   s.region_id = " .   $par[1] -> region_id . "
			and   st.church_id != " . $structuretype -> church_id );			
			if ( $res -> count() > 0 )
			{
				$message = kohana::lang('ca_workonproject.error_otherchurchstructurecompleted'); 
				return false;		
			}
		}
		
		return true;
		
	}

	protected function append_action( $par, &$message )	{	
		
		// toglie soldi dalla struttura.
		
		$wage = $par[2] * $par[1] -> hourlywage;
		$par[1] -> modify_coins( - $wage, 'buildsalary' );					
		$this -> character_id = $par[0] -> id;
		$this -> starttime = time();			
		$this -> status = "running";	
		$this -> basetime = $par[2];
		$this -> endtime = $this  ->  starttime + $this -> get_action_time( $par[0] );
		$this -> param1 = $par[1] -> id;
		$this -> param2 = $par[2];
		$this -> param3 = $par[3]  ->  id;
		$this -> param4 = $wage;
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
		
		// save stat
		$structure = StructureFactory_Model::create( null, $data -> param1 ); 		
		$structure -> modify_stat( 'workedhours', 
			$data -> param2, 
			$character -> id, 
			null,			
			$character -> id, 
			$character -> name, 
			false	
			); 
		
		// accumulate hours for project
		
		$project = ORM::factory('kingdomproject', $data -> param3 );		
		$project -> workedhours += $data->param2;
		$project -> save();
	
		// check for project completion
		
		if ( $project -> workedhours >= $project -> cfgkingdomproject -> required_hours and $project -> status != 'completed' )
			$project -> complete( $data -> param1, $project -> cfgkingdomproject -> type );
		
		// take off energy and glut, give money
		
		$character -> modify_energy( - self::DELTA_ENERGY_X_HOUR * $data -> param2, false, 'workonstructure' );
		$character -> modify_glut( - self::DELTA_GLUT_X_HOUR * $data -> param2 );
		
		if ( $data -> param4 > 0 )
		{	
			$structuretext = '__events.projectworkpaid;' . 
				$character -> name . ';' . $data -> param2 . ';' . $data -> param4;
			$chartext = '__events.endprojectworkpaid;' . 
				$data -> param2 . ';' . $data -> param4;			
			$character -> modify_coins( $data -> param4, 'buildsalary' );
		}
		else
		{
			$structuretext = '__events.projectwork;' . $character -> name . ';' . $data -> param2;
			$chartext = '__events.endprojectwork;' . $data -> param2 ;
		}
		
		$character -> save();
			
		// add a structure event
		Structure_Event_Model::newadd( $project -> structure_id, $structuretext );
		
		// add a character event
		Character_Event_Model::addrecord(
			$character -> id,
			'normal',
			$chartext
			);	

		return;		
	
	}
		
	public function execute_action ( $par, &$message ) 	{	}
	
	public function cancel_action()	{}		
	
	// Questa funzione costruisce un messaggio da visualizzare 
	// in attesa che la azione sia completata.
	
	public function get_action_message( $type = 'long') 
	{
		$pending_action = $this->get_pending_action();
		$message = "";				
		
		if ( $pending_action -> loaded )
		{
			if ( $type == 'long' )		
			$message = '__regionview.workonproject_longmessage';
			else
			$message = '__regionview.workonproject_shortmessage';
		}
		return $message;
	
	}	
}
