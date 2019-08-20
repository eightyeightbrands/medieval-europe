<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Revokerolerp_Model extends Character_Action_Model
{

	protected $immediate_action = true;

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
	
	
	// check
	// ***********************************************************
	// Eseguie tutti i controlli prima dell'esecuzione o
	// dell'append della charaction
	//
	// @param   par[0]: oggetto ruolo
	// @param   par[1]: oggetto struttura da dove si effettua la
	//                    revoca
	//
	// @output  TRUE = azione disponibile, FALSE = azione non disponibile
	// @output  $message contiene il messaggio di ritorno
	// ***********************************************************
	
	protected function check( $par, &$message )
	{ 
		if ( ! parent::check( $par, $message ) )					
		{ return false; }
		
		// Controllo che la struttura da dove
		// viene la revoca corrisponda a quella da
		// cui è stata fatta la nomina
		if ($par[0]->structure_id != $par[1]->id)
		{ $message = 'global.operation_not_allowed'; return false; }
				
		// Controllo che il char che esegue la revoca
		// sia il gestore della struttura
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		if ($character->id != $par[1]->character_id)
		{ $message = 'global.operation_not_allowed'; return false; }
		
		// Controllo che il char si trovi nella stessa
		// regione della struttura.
		if ($character->position_id != $par[1]->region_id)
		{ $message = 'global.operation_not_allowed'; return false; }
		return true;
	}

	// nessun controllo particolare
	protected function append_action( $par, &$message ) { }

	public function execute_action ( $par, &$message) 
	{
		
		$revoked = ORM::factory( 'character', $par[0] -> character_id );		
		
		// Consumo degli items/vestiti indossati
		Item_Model::consume_equipment( $this->equipment, $par[1] -> character );
		
		if ($par[0]->place != null)
		{
			$msg2 = '__events.gdrtitlerevoked_announcement;';
			$msg3 = 'structures.revokerprole';
		}
		else
		{
			$msg2 = '__events.gdrrolerevoked_announcement;';
			$msg3 = 'structures.revokerprole';
		}
		
		// Pubblica annuncio negli eventi del char
		Character_Event_Model::addrecord( $par[0]->character->id, 'normal', $msg2 );
		
		// rimuovo eventuali grant
		if ( $par[0] -> tag == 'chancellor' )
		{
			$royalpalace = $par[1] -> region -> get_controllingroyalpalace();
			$ca = new CA_Revokestructuregrant_Model();
			$_par[0] = $royalpalace;
			$_par[1] = $revoked;
			$_par[2] = 'chancellor';
			$ca -> do_action( $_par, $message );
		}
		
		Character_Event_Model::addrecord(
			$par[1]-> character_id, 
			'normal',  
			'__events.gdrtitlerevokedsource'.
			';__global.title_' . $par[0] -> tag . '_' . strtolower($par[0] -> character -> sex) . 
			';__global.of' .
			';' . $par[0] -> place .
			';' . $par[0] -> character -> name
			);
		
		// Termino il ruolo
		
		$par[0] -> end(); 		
						
		$message = kohana::lang($msg3);			

		return true;
	}
}
