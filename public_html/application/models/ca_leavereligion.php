<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Leavereligion_Model extends Character_Action_Model
{
	protected $cancel_flag = false;
	protected $immediate_action = true;
	
	/*
	* Effettua tutti i controlli relativi al move, sia quelli condivisi
	* con tutte le action che quelli peculiari del move
	* @param    array    $par      [0]->char
	* @param    string   $message   passato per referenza
	* @output:  TRUE = azione disponibile, FALSE = azione non disponibile
	*/
	
	protected function check( $par, &$message )
	{ 
		$message = "";
		
		if ( ! parent::check( $par, $message ) )					
			return false;
		
		
		// Verifico che il char non abbia un ruolo religioso
		
		if ( $par[0]->has_religious_role() )
		{ $message = Kohana::lang("ca_leavereligion.char_error_has_religiuos_role"); return false; }
	
		// Tutti i controlli sono stati superati
		
		return true;
		
	}

	/* Append action - Non usata - Azione di tipo immediato */
	protected function append_action( $par, &$message ) { return true; }

	/* Complete action - Non usata - Azione di tipo immediato */
	public function complete_action( $data ) { }
	
	/*
	* Execute action - Eseguita per azioni di tipo immediato
	* @param    array    $par [0]->char
	* @param    string   $message   passato per referenza
	* @return   none
	*/
	protected function execute_action( $par, &$message )
	{
		
		kohana::log('debug', '-> Char ' . $par[0]->name . ' is leaving religion:' . $par[0] -> church -> religion -> name );
		
		// Gestione gli eventi
		//************************************************
		// Evento per il char
		
		Character_Event_Model::addrecord
		( 
			// Char id
			$par[0] -> id,
			// Tipo evento
			'normal',
			// Testo
			'__ca_leavereligion.event_char_abandon_religion;__' . 'religion.church-' . $par[0] -> church -> name,
			// Classe
			'normal'
		);	
		
		// Evento per il town crier
		Character_Event_Model::addrecord
		(
			// Char id
			null,
			// Tipo evento
			'announcement',
			// Testo
			'__ca_leavereligion.event_global_abandon_religion;' . $par[0] -> name . ';__' . 'religion.church-' . $par[0] -> church -> name
		);
		
		// Azzero le statistiche dell'attuale religione
		//************************************************
		// Livello di fede
		
		$par[0] -> modify_stat 
		( 
			'faithlevel',           // Stat
			0,                      // Valore
			null,                   // Param 1
			null,                   // Param 2
			true                    // Sovrascrive
		);
		// Punti fede accumulati per questa religione
		$par[0] -> modify_stat
		( 
			'fpcontribution',       // Stat
			0,                      // Valore
			$par[0] -> church_id,   // Param 1
			null,                   // Param 2
			true                    // Sovrascrive
		);
		// Soldi elemosinati alla religione
		$par[0] -> modify_stat
		( 
			'alms',                 // Stat
			0,                      // Valore
			$par[0] -> church_id,   // Param 1
			null,                   // Param 2
			true                    // Sovrascrive
		);
		
		// Cambio la religione in atea
		//************************************************
		// Carico la chiesa/religione atea
		
		$atheism = ORM::factory('church')->where('name', 'nochurch')->find();
		
		// Imposto la religione del char come atea
		$par[0] -> church_id = $atheism->id;
		$par[0] -> save();

		// Gestione badge
		//************************************************
		// Cancello il badge relativo le religioni
		// (stat_fpcontribution)
		
		Achievement_Model::remove($par[0] -> id, 'stat_fpcontribution', 'all');			

		// Messaggio di informazione
		$message = kohana::lang('ca_leavereligion.message_info_abandon');
		// Azione eseguita correttamente
		return true;
	}
	
	/* Cancel action - Non usata - Azione di tipo immediato */
	public function cancel_action() { return true; }
}
