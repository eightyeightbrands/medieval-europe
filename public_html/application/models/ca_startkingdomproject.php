<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Startkingdomproject_Model extends Character_Action_Model
{

	protected $immediate_action = true;
	protected $feasibilityinfo = array();
	
	// L'azione richiede che il personaggio indossi
	// un determinato equipaggiamento
	
	protected $requiresequipment = true;
	
	// Equipaggiamento o vestiario necessario in base al ruolo
	
	protected $equipment = array
	(
		'church_level_1' => array
		(
			'head' => array
			(
				'items' => array('mitra_rome','panzva_turnu','hat_cairo','hat_kiev','hat_norse'),
				'consume_rate' => 'verylow',
			),
			'right_hand' => array
			(
				'items' => array('mysticrod_turnu','scepter_kiev','pastoral_rome', 'mysticrod_cairo','scepter_norse'),
				'consume_rate' => 'verylow',
			),
			'body' => array
			(
				'items' => array('tunic_church_level_1_rome', 'tunic_church_level_1_turnu', 'tunic_church_level_1_kiev', 'tunic_church_level_1_cairo','tunic_church_level_1_norse'),
				'consume_rate' => 'verylow',
			),
			'feet' => array
			(
				'items' => array('shoesm_1', 'shoesf_1'),
				'consume_rate' => 'verylow',
			)
		),
		'church_level_2' => array
		(
			'head' => array
			(
				'items' => array('mitra_rome','panzva_turnu','hat_cairo','hat_kiev','hat_norse'),
				'consume_rate' => 'verylow',
			),
			'right_hand' => array
			(
				'items' => array('mysticrod_turnu','scepter_kiev','pastoral_rome', 'mysticrod_cairo','scepter_norse'),
				'consume_rate' => 'verylow',
			),
			'body' => array
			(
				'items' => array('tunic_church_level_2_rome', 'tunic_church_level_2_turnu', 'tunic_church_level_2_kiev', 'tunic_church_level_2_cairo','tunic_church_level_2_norse'),
				'consume_rate' => 'verylow',
			),
			'feet' => array
			(
				'items' => array('shoesm_1', 'shoesf_1'),
				'consume_rate' => 'verylow'
			)		),
		'church_level_3' => array
		(
			'head' => array
			(
				'items' => array('mitra_rome','panzva_turnu','hat_cairo','hat_kiev','hat_norse'),
				'consume_rate' => 'verylow'
			),
			'right_hand' => array
			(
				'items' => array('mysticrod_turnu','scepter_kiev','pastoral_rome', 'mysticrod_cairo','scepter_norse'),
				'consume_rate' => 'verylow',
			),
			'body' => array
			(
				'items' => array('tunic_church_level_3_rome', 'tunic_church_level_3_turnu', 'tunic_church_level_3_kiev', 'tunic_church_level_3_cairo','tunic_church_level_3_norse'),
				'consume_rate' => 'verylow',
			),
			'feet' => array
			(
				'items' => array('shoesm_1', 'shoesf_1'),
				'consume_rate' => 'verylow',
			)
		),
		'church_level_4' => array
		(
			'right_hand' => array
			(
				'items' => array('holybook'),
				'consume_rate' => 'verylow',
			),
			'body' => array
			(
				'items' => array('tunic_church_level_4_rome', 'tunic_church_level_4_turnu', 'tunic_church_level_4_kiev', 'tunic_church_level_4_cairo','tunic_church_level_4_norse'),
				'consume_rate' => 'verylow',
			),
			'feet' => array
			(
				'items' => array('shoesm_1', 'shoesf_1'),
				'consume_rate' => 'verylow',
			),
		),
		'all' => array
		(
			'body' => array
			(
				'items' => array('any'),
				'consume_rate' => 'verylow'
			),
			'torso' => array
			(
				'items' => array('any'),
				'consume_rate' => 'verylow'
			),
			'legs' => array
			(
				'items' => array('any'),
				'consume_rate' => 'verylow'
			),
			'feet' => array
			(
				'items' => array('any'),
				'consume_rate' => 'verylow'
			),
		),
	);
	
	

	// con tutte le action che quelli peculiari del seed
	// @input: array di parametri	
	// par[0] : oggetto char che effettua l' azione
	// par[1] : oggetto cfgkingdomproject
	// par[2] : tipo struttura
	// par[3] : regione dove la struttura che lancia il progetto è situata
	// par[4] : regione dove la struttura deve essere costruita 
	// par[5] : struttura da cui è lanciato il progetto
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function check( $par, &$message )
	{ 
		
		$message = "";
		
		// Metodo ereditato dal modello Character_Action. Controllo che non ci siano
		// altre azioni in corso			
		if ( ! parent::check( $par, $message ) )					
		{ return FALSE; }		
				
		// controllo dati
		if ( !$par[1] -> loaded or !$par[2] -> loaded )
		{		
			$message = kohana::lang('global.operation_not_allowed'); 
			return false;
		}
		
		//controllo che il progetto sia effettivamente startabile
		$this -> feasibilityinfo = CfgKingdomProject_Model::checkprojectfeasibility( $par[1], $par[2], $par[3], $par[4], $par[5]);		
		if ( $this -> feasibilityinfo['result'] == false )
		{
		
			$message = $this -> feasibilityinfo['message'];
			return false;
		}
		
		// controllo che la struttura abbia abb. faith points	
		if ( $par[5] -> structure_type -> subtype == 'church' )
		{
			$fp = Structure_Model::get_stat_d( $par[5] -> id, 'faithpoints' ); 
			kohana::log('debug', 'fp: ' . kohana::debug($this -> feasibilityinfo )); 
			if ( !$fp -> loaded or $fp -> value < $this -> feasibilityinfo['cost'] )
			{
				$message = kohana::lang('global.error-notenoughfp', $this -> feasibilityinfo['cost']); 
				return false;
			}
		}
		
		return true;
		
	}

	protected function append_action( $par, &$message )	{	}

	public function complete_action( $data )	{	}
		
	public function execute_action ( $par, &$message ) 
	{
	
	
		// Consumo degli items/vestiti indossati
		Item_Model::consume_equipment( $this->equipment, $par[0] );
		
		
		// Aggiungiamo un cantiere nella regione
		// specificata		
				
		$s = StructureFactory_Model::create('buildingsite_1', null);		
		
		// Se la struttura da costruire è una chiesa, 
		// allora è necessario settare la struttura padre	
		// e togliere i fp.
		
		if ( $par[2] -> subtype == 'church' )
		{
			$par[5] -> modify_fp( -$this -> feasibilityinfo['cost'], 'communityprojectlaunch' ); 
			$par[5] -> save();
			
		}

		$s -> parent_structure_id = $par[5] -> id;		
		$s -> region_id = $par[4] -> id;		
		
		// Chi lancia il progetto è owner della struttura.
		$s -> character_id = $par[0] -> id;		
		
		// memorizziamo il tipo di struttura
		$s -> attribute1 = $par[2] -> id ;
		
		//setta hourly wage
		$s -> hourlywage = 0;
		$s -> save();
				
		$project = new Kingdomproject_Model();
		$project -> cfgkingdomproject_id = $par[1] -> id;
		$project -> region_id = $par[4] -> id;
		$project -> structure_id = $s -> id;
		$project -> status = 'collectingmaterials';
		$project -> start = time();
		$project -> workedhours = 0;
		$project -> startedby = $par[0] -> get_signature();
		$project -> save();
		
		// evento in town crier
		
		Character_Event_Model::addrecord( 
			null, 
			'announcement', 
			'__events.startedproject' .
			';' . $par[0] -> name . 
			';__' . $par[2] -> name .
			';__' . $par[4] -> name				
			);
	
		$message = kohana::lang('kingdomprojects.startkingdomproject_ok');
		
		return true;
	
	}
	
}
