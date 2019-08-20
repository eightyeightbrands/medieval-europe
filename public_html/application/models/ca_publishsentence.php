<?php defined('SYSPATH') OR die('No direct access allowed.');
class CA_Publishsentence_Model extends Character_Action_Model
{
	protected $immediate_action = true;
	
	// Effettua tutti i controlli relativi alla wear, sia quelli condivisi
	// con tutte le action che quelli peculiari della wear
	// @input: array di parametri (par[0] memorizza l'id dell'oggetto che indosso)
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	// par[0]: oggetto char del magistrato
	// par[1]: Nome del ricevente sentenza
	// par[2]: testo sentenza
	// par[3]: oggetto struttura della magistratura
	
	protected function check( $par, &$message )
	{ 
		if ( ! parent::check( $par, $message ) )					
		{ return false; }
		// controllo parametri
		
		if ( strlen( $par[2])<= 0 or strlen( $par[1]) <= 0 )
		{ $message = kohana::lang('charactions.publishsentence_parametersempty'); return FALSE; }
		
		if ( strlen( $par[2]) > 255 )
		{ $message = kohana::lang('charactions.publishsentence_sentencetoolong'); return FALSE; }
		
		$targetchar = ORM::factory( "character")->where ( array( 'name' => $par[1]))->find(); 
		
		if ( !$targetchar->loaded )
		{ $message = kohana::lang('global.error-characterunknown'); return FALSE; }
		
		if ( $targetchar->id == $par[0]->id )
		{ $message = kohana::lang('global.selfaction_notpossible'); return FALSE; }		
		
		// I Re non possono essere imprigionati. Gli altri sì.
		
		$role = $targetchar->get_current_role();
		
		if (  !is_null($role) and $role->tag == 'king' )
		{ $message = 
			sprintf( kohana::lang('charactions.publishsentence_characterhasarole'),
				$targetchar->name . ",  " . kohana::lang($role->role) . " " . kohana::lang('global.of') . " " . kohana::lang($role->region->name) ); return false; }

		

		
		
		return true;
	}

	
	// nessun controllo particolare
	protected function append_action( $par, &$message )
	{	}

	public function complete_action( $data)
	{ }
	
	// Sazia il char
	// @input: par[0] memorizza l'id dell'oggetto che sto mangiando
	public function execute_action ( $par, &$message ) 
	{
	
	
		// aggiungo una sentenza per l' esecuzione
		$sentence = new  Character_Sentence_Model();
		$targetchar = ORM::factory( "character")->where ( array( 'name' => $par[1]))->find(); 
		$sentence->character_id = $targetchar->id;
		$sentence->issued_by = $par[0]->id;
		$sentence->issuedate = time();
		$sentence->text = $par[2];
		$sentence->status = 'new';
		
		// trovo la struttura prigione del nodo
		$region = ORM::factory("region", $par[3]->region_id);
		$barrack = $region->get_structure( 'barracks');
		$sentence->structure_id = $barrack->id;
		$sentence->save();
			
		// manda email e annuncia
		
		$role = $par[0]->get_current_role();
		// pubblica annuncio
		
		Character_Event_Model::addrecord( 
			null, 
			'announcement', 
			'__events.publishsentence_announcement'.
			';'.$par[0]->name.
			';__'.$role->role.
			';__'.'global.of'.
			';__'.$role->region->name.
			';'.$targetchar->name.
			';'.	$par[2]
			);
		
		// manda evento a sceriffo
		
		$sheriffrole = $region->get_roledetails( 'sheriff') ;
		//print kohana::debug( $sheriffrole )	;exit();
		
		if ( $sheriffrole ) 
		{
			
			Character_Event_Model::addrecord( 
				$sheriffrole->character_id, 
				'normal', 
				'__events.publishsentence_announcement'.
				';'.$par[0]->name.
				';__'.$role->role.
				';__'.'global.of'.
				';__'.$role->region->name.
				';'.$targetchar->name.
				';'.	$par[2]
				);
		}
		
		// manda un evento al destinatario
		
		Character_Event_Model::addrecord( 
			$targetchar->id, 
			'normal', 
			'__events.publishsentence_announcement'.
			';'.$par[0]->name.
			';__'.$role->role.
			';__'.'global.of'.
			';__'.$role->region->name.
			';'.$targetchar->name.
			';'.	$par[2]
			);

		
		$message = kohana::lang( 'charactions.publishsentence_ok');
		return true;
	}
}
