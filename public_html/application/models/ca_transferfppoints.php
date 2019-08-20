<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Transferfppoints_Model extends Character_Action_Model
{

	// Costanti		
	const COSTPERCENTAGE = 10;
	protected $cancel_flag = false;
	protected $immediate_action = true;
	protected $totalcost = 0;
	
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
	

	// @input: array di parametri	
	// par[0] : oggetto char che compie l'azione
	// par[1] : oggetto structure da cui parte l' opzione
	// par[2] : oggetto structure target
	// par[3] : punti da trasferire
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function check( $par, &$message )
	{ 
		
		$message = "";
		
		if ( ! parent::check( $par, $message ) )					
			return false;
		
		// check dati
		
		if ( !$par[0] -> loaded or !$par[1] -> loaded  or !$par[2] -> loaded )
		{ $message = Kohana::lang("global.operation_not_allowed"); return false; }
		
		// gli fp points devono essere > 0 e < degli fp points a disposizione (inclusa la % persa).
		
		$fppoints = Structure_Model::get_stat_d( $par[1] -> id, 'faithpoints' ); 		
		$this -> totalcost = round( $par[3] * ( 100 + (self::COSTPERCENTAGE ) ) / 100);
		
		if ( $par[3] <= 0 )
		{ $message = Kohana::lang("ca_transferfppoints.negativefppoints"); return false; }
		
		if ($par[1] -> structure_type -> church_id != $par[2] -> structure_type -> church_id )
		{ $message = Kohana::lang("ca_transferfppoints.error-churchmustbesamereligion"); return false; }
	
		if ( !$fppoints -> loaded or $this -> totalcost > $fppoints -> value)
		{ $message = Kohana::lang("global.error-notenoughfp", $this -> totalcost); return false; }			
		
		return true;
		
	}

	protected function append_action( $par, &$message )	{	}

	public function complete_action( $data )	{	}

	public function execute_action ( $par, &$message ) 	{	
	
		// Consumo degli items/vestiti indossati
		Item_Model::consume_equipment( $this->equipment, $par[0] );
		$par[1] -> modify_fp( - $this -> totalcost, 'fptransfer' );
		$par[2] -> modify_fp( + $par[3], 'fptransfer' );

		// evento struttura source
		
		Structure_Event_Model::newadd(
			$par[1] -> id,
			'__events.fppointstranferedsource' . 							
			';' . $par[3] .
			';__' . $par[2] -> structure_type -> name .
			';__' . $par[2] -> region -> name .
			';' . $this -> totalcost
			);
		
		// evento struttura target
		
		Structure_Event_Model::newadd(
			$par[2] -> id,
			'__events.fppointstranferedtarget' . 				
			';' . $par[0] -> name . 
			';' . $par[3]
			);
	
		$message = kohana::lang('ca_transferfppoints.transfer-ok'); 
		
		return true;
		
	}
	
	public function cancel_action( )	{	}		

	
}
