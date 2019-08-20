<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Adddogmabonus_Model extends Character_Action_Model
{
	// Costanti
	const MAX_DOGMA_BONUSES = 4;
	
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
				'items' => array('mitra_rome','panzva_turnu','hat_cairo','hat_kiev', 'hat_norse'),
				'consume_rate' => 'veryhigh',
			),
			'right_hand' => array
			(
				'items' => array('mysticrod_turnu','scepter_kiev','pastoral_rome', 'mysticrod_cairo', 'scepter_norse'),
				'consume_rate' => 'veryhigh',
			),
			'body' => array
			(
				'items' => array('tunic_church_level_1_rome', 'tunic_church_level_1_turnu', 'tunic_church_level_1_kiev', 'tunic_church_level_1_cairo', 'tunic_church_level_1_norse'),
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
	* @param    array    $par      [0]->structure, [1]->char, [2]->bonus dogma, [3]->FP cost
	* @param    string   $message   passato per referenza
	* @output:  TRUE = azione disponibile, FALSE = azione non disponibile
	*/
	protected function check( $par, &$message )
	{ 
		$message = "";
		
		if ( ! parent::check( $par, $message ) )					
			return false;

		// Check: struttura caricata
		// Check: char caricato
		// Check: bonus dogma caricato
		// Check: struttura e char nella stessa regione
		if
		(
			!$par[0] -> loaded 
			or !$par[1] -> loaded 
			or !$par[2] -> loaded 
			or $par[0] -> region -> id != $par[1] -> position_id
		)
		{ $message = Kohana::lang("global.operation_not_allowed"); return false; }		
		
		if ($par[2] -> bonus == 'meditateanddefend' or $par[2] -> bonus == 'killtheinfidels')
		{			
			$message = 'Sorry, but this dogma is currently disabled.'; 
			return false;
		}
		
		// Check: la chiesa ha il numero di faith points necessari
		$fp_in_structure = $par[0]->get_stat_d($par[0]->id, 'faithpoints');
		if ($par[3] > $fp_in_structure->value)
		{
			$message = Kohana::lang("global.error-notenoughfp", $par[3]); return false;
		}
		
		// Check: il bonus non è giÃ  presente nella chiesa
		if ( Church_Model::has_dogma_bonus( $par[0]->structure_type->church_id, $par[2]->bonus) )
		{
			$message = Kohana::lang("ca_adddogmabonus.error-bonus-already-taken", $par[3]); return false;
		}
		
		// Check: il bonus è Curse Infidels
		// Check: la chiesa è la stessa della maledizione
		// (Non posso prendere un malus contro la mia stessa chiesa)
		if ($par[2]->bonus == 'curseinfidels_'.$par[1]->church->name)
		{
			$message = Kohana::lang("ca_adddogmabonus.error-no-same-church"); return false;
		}
		
		// Check: è stato giÃ  raggiunto il numero massimo dei bonus
		if ( Church_Model::count_dogma_bonus( $par[0]->structure_type->church_id ) >= self::MAX_DOGMA_BONUSES )
		{
			$message = Kohana::lang("ca_adddogmabonus.error-max-bonus-reached", $par[3]); return false;
		}
		
		// Tutti i controlli sono stati superati
		return true;
	}

	/* Append action - Non usata - Azione di tipo immediato */
	protected function append_action( $par, &$message ) { return true; }

	/* Complete action - Non usata - Azione di tipo immediato */
	public function complete_action( $data ) { }
	
	/*
	* Execute action - Eseguita per azioni di tipo immediato
	* @param    array    $par      [0]->structure, [1]->char, [2]->bonus dogma, [3]->FP cost
	* @param    string   $message   passato per referenza
	* @return   none
	*/
	protected function execute_action( $par, &$message )
	{
		// Aggiungo log di sistema
		kohana::log('debug', '-> Char ' . $par[1]->name . ' is adding dogma ' . $par[2]->bonus .' to church '. $par[1] -> church -> religion -> name );
		
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
			'__ca_adddogmabonus.event_global_add_dogma;__religion.church-' . $par[1] -> church -> name . ';__religion.dogma_' . $par[2] -> dogma. ';__religion.dogmabonus_' . $par[2] -> bonus,
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
			'__ca_adddogmabonus.event_char_add_dogma;__religion.dogmabonus_' . $par[2] -> bonus . ';__religion.dogma_' . $par[2] -> dogma,
			// Classe
			'normal'
		);

		// Gestione bonus dogma
		//************************************************
		// Rimuovo i punti fede dalla chiesa
		$par[0] -> modify_stat
		( 
			'faithpoints',    // stat
			-$par[3],         // value
			null,             // parametro di ricerca 1
			null,             // parametro di ricerca 2
			null,             // valore campo spare1
			null,             // valore campo spare1
			false             // rimpiazza il valore
		);
		
		// Aggiungo il dogma alla chiesa
		$newbonus = new Church_dogmabonus_model;
		$newbonus->church_id = $par[1]->church->id;
		$newbonus->cfgdogmabonus_id = $par[2]->id;
		$newbonus->timestamp = date('Y-m-d H:i:s');
		$newbonus->save();
		
		// Consumo degli items/vestiti indossati
		Item_Model::consume_equipment( $this->equipment, $par[1] );
		
		// Messaggio di informazione
		$message = kohana::lang('ca_adddogmabonus.message_info_add_dogma');
		
		// Azione eseguita correttamente
		return true;
	}
	
	/* Cancel action - Non usata - Azione di tipo immediato */
	public function cancel_action() { return true; }
}
