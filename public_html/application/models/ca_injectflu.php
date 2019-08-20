<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Injectflu_Model extends Character_Action_Model
{
	// Azione ad esecuzione immediata
	protected $immediate_action = true;
		
	// Effettua tutti i controlli relativi alla Startnativerevolt, sia quelli condivisi
	// con tutte le action che quelli peculiari della Startnativerevolt
	// @input:   $par        array di parametri
	// @output:  boolean     TRUE = azione disponibile, FALSE = azione non disponibile
	//           $messages   contiene gli errori in caso di FALSE (per riferimento)
	protected function check( $par, &$message )
	{ 
		return true;	
	}

	// Viene eseguita nel caso in cui l'azione non si di tipo immediato
	// La char_action viene collegata al character fino alla sua scadenza
	// In questo caso l'azione è di tipo immediato e quindi la funzione non viene evocata
	protected function append_action( $par, &$message )
	{}
	
	// Funzione che esegue tutte le azioni collegate al completamento della char_action nativerevolt.
	// In questo caso, essendo di tipo immediato, viene richiamata subito
	// @input:   $data       array di parametri
	// @output:  boolean     TRUE / FALSE = azione andata a buon fine
	public function complete_action ( $data ) 
	{
		// Data e ora inizio elaborazione
		$start=time();
		
		kohana::log('debug','-> Inject FLU: START');

		// Seleziono tutti i char con body o legs naked
		
		$sql = 		
		"
			SELECT c.id , c.name 
			FROM  characters c, users u
			WHERE c.user_id = u.id 			
			AND   u.status IN ('active') ";
			
		$candidates = Database::instance() -> query($sql)	;
		
		$flu = DiseaseFactory_Model::createDisease('flu');		
		mt_srand();
		foreach ($candidates as $candidate)
		{
			kohana::log('debug', "-> Trying to inject char {$candidate -> name}");
			if (Character_Model::is_naked( $candidate -> id ) == false )
				continue;
			kohana::log('debug', "{$candidate -> name} is naked, trying to give him the flu...");
			
			$roll = mt_rand(1,100);
			if ($roll <= 10)
			{				
				$flu -> injectdisease( $candidate -> id );
			}
		}
				
		// Rischedula azione.
		
		$action = ORM::factory('character_action') -> 
			where( array( 
				'character_id' => -1, 
				'action' => 'injectflu' ) ) -> find();
		
		mt_srand();
		$nextdate = time() + (7 * 24 * 3600);
		$action -> starttime = $nextdate;
		$action -> endtime = $nextdate;
		$action -> save();
		
		// Data e ora fine elaborazione
		$end = time();
		kohana::log('debug', '-> Inject Flu, elapsed: ' . (($end - $start)/(3600)) . ' secs');
		
		// Azione completata correttamente
		
		return true;
	}
}
