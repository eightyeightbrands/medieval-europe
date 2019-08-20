<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Removedogmabonus_Model extends Character_Action_Model
{
	protected $cancel_flag = false;
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
				'consume_rate' => 'veryhigh',
			),
			'right_hand' => array
			(
				'items' => array('mysticrod_turnu','scepter_kiev','pastoral_rome', 'mysticrod_cairo','scepter_norse'),
				'consume_rate' => 'veryhigh',
			),
			'body' => array
			(
				'items' => array('tunic_church_level_1_rome', 'tunic_church_level_1_turnu', 'tunic_church_level_1_kiev', 'tunic_church_level_1_cairo','tunic_church_level_1_norse'),
				'consume_rate' => 'veryhigh',
			),
			'feet' => array
			(
				'items' => array('shoesm_1', 'shoesf_1'),
				'consume_rate' => 'veryhigh',
			)
		),
	);
	
	/*
	* Effettua tutti i controlli relativi al move, sia quelli condivisi
	* con tutte le action che quelli peculiari del move
	* @param    array    $par      [0]->structure, [1]->char, [2]->dogma
	* @param    string   $message   passato per referenza
	* @output:  TRUE = azione disponibile, FALSE = azione non disponibile
	*/
	protected function check( $par, &$message )
	{ 
		$message = "";
		
		if ( ! parent::check( $par, $message ) )					
			return false;
		
		// Controllo struttura caricata
		// Controllo char caricato
		// Controllo dogma caricato
		// Controllo strutture e char nella stessa regione
		if
		(
			!$par[0] -> loaded 
			or !$par[1] -> loaded 
			or !$par[2] -> loaded 
			or $par[0] -> region -> id != $par[1] -> position_id
		)
		{ $message = Kohana::lang("global.operation_not_allowed"); return false; }		
		
		// Tutti i controlli sono stati superati
		return true;
	}

	/* Append action - Non usata - Azione di tipo immediato */
	protected function append_action( $par, &$message ) { return true; }

	/* Complete action - Non usata - Azione di tipo immediato */
	public function complete_action( $data ) { }
	
	/*
	* Execute action - Eseguita per azioni di tipo immediato
	* @param    array    $par      [0]->structure, [1]->char, [2]->dogma
	* @param    string   $message   passato per referenza
	* @return   none
	*/
	protected function execute_action( $par, &$message )
	{
		// Aggiungo log di sistema
		kohana::log('debug', '-> Char ' . $par[1]->name . ' is removing dogma ' . $par[2]->cfgdogmabonus->dogma .' from church '. $par[1] -> church -> religion -> name );
		
		// Gestione gli eventi
		//************************************************
		// Evento per il town crier
		Character_Event_Model::addrecord
		(
			// Char id
			null,
			// Tipo evento
			'announcement',
			// Testo
			'__ca_removedogmabonus.event_global_remove_dogma;__religion.church-' . $par[2] -> church -> name . ';__religion.dogmabonus_' . $par[2] -> cfgdogmabonus -> bonus . ';__religion.dogma_' . $par[2] -> cfgdogmabonus -> dogma,
			'evidence'
		);
		// Evento per il char
		Character_Event_Model::addrecord
		( 
			// Char id
			$par[1] -> id,
			// Tipo evento
			'normal',
			// Testo
			'__ca_removedogmabonus.event_char_remove_dogma;__religion.dogmabonus_' . $par[2] -> cfgdogmabonus -> bonus . ';__religion.dogma_' . $par[2] -> cfgdogmabonus -> dogma,
			// Classe
			'normal'
		);
		
		// Cancello il dogma della chiesa
		//************************************************
		$par[2]->delete();
		
		// Consumo degli items/vestiti indossati
		Item_Model::consume_equipment( $this->equipment, $par[1] );
		
		// Messaggio di informazione
		$message = kohana::lang('ca_removedogmabonus.message_info_remove_dogma');
		// Azione eseguita correttamente
		return true;
	}
	
	/* Cancel action - Non usata - Azione di tipo immediato */
	public function cancel_action() { return true; }
}
