<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Modifydiplomacystatus_Model extends Character_Action_Model
{	
	protected $cancel_flag = true;
	protected $immediate_action = true;
	protected $diplomacytransactionrequirement = array
	(	
		'hostile' => 
			array(
				'neutral' => 'confirmation',
				'friendly' => 'confirmation',
				'allied' => 'confirmation'
			),
		'neutral' =>	
			array(
				'friendly' => 'confirmation',
				'allied' => 'confirmation',
				'hostile' => 'direct',
			),
		'friendly' =>	
			array(
				'neutral' => 'direct',
				'allied' => 'confirmation',
				'hostile' => 'direct',
			),
		'allied' =>	
			array(
				'neutral' => 'direct',
				'friendly' => 'direct',
				'hostile' => 'direct',
			)
	);
		
	
	// @input: 
	// $par[0] = oggetto char che invoca l' azione
	// $par[1] = oggetto structure da cui è invocata l' azione
	// $par[2] = diplomacy status info
	// $par[3] = nuovo stato
	// $par[4] = accept|refuse
	// $par[5] = proposta confermata 
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function check( $par, &$message )
	{ 
		
		$message = "";
		
		if ( ! parent::check( $par, $message ) )					
			return false;
		
		// controllo: il nuovo stato è diverso da quello precedente?
		
		if ( $par[3] == $par[2] -> type  )
		{ $message = kohana::lang('diplomacy.error-statusmustbedifferent'); return FALSE; }									
		// controllo: se non è una conferma, se c'è già una proposta inviata, errore
		
		$proposal = ORM::factory('diplomacy_proposal')
				-> where ( array (
					'sourcekingdom_id' => $par[2] -> sourcekingdom_id,
					'targetkingdom_id' => $par[2] -> targetkingdom_id,
					'status' => 'new' ))
				-> find();
		
		if (
			$par[5] == false 
			and
			$proposal -> loaded 
		)
		{ $message = kohana::lang('diplomacy.error-pendingproposalexists'); return FALSE; }							
		
		// controllo: foglio di carta e sigillo necessario
		
		if ( 
			! Character_Model::has_item( $par[0]->id, 'paper_piece', 1 ) 
			or 
			! Character_Model::has_item( $par[0]->id, 'waxseal', 1 ) 
		) 
		{ $message = kohana::lang('charactions.paperpieceandwaxsealneeded'); return FALSE; }						
		
		// Non più di due Alleati.
		
		$alliedkingdoms = 0;
		$diplomacyrelations = Configuration_Model::get_cfg_diplomacyrelations();
		
		foreach ($diplomacyrelations[$par[0]->region->kingdom_id] as $targetkingdom => $diplomacyrelation)
		{
			
			if ($diplomacyrelation['type'] == 'allied')
				$alliedkingdoms++;
		}
		
		kohana::log('debug', "-> Kingdom has {$alliedkingdoms} Allied Kingdoms.");
		
		if ($par[3] == 'allied' and $alliedkingdoms >= 2)
			{ $message = kohana::lang('diplomacy.error-maxalliedrelatinosreached'); return FALSE; }						
		
		// Regno Sorgente: Cooldown 48 ore dopo ultima guerra da attaccante
		
		$lastwarsource = Kingdom_Model::get_last_war( $par[2] -> sourcekingdom_id );
		if (
			$lastwarsource['kingdoms'][$par[2] -> sourcekingdom_id]['role'] == 'attacker'
			and
			round ( ( time() - $lastwarsource['war']->end)/(24*3600), 0 ) < kohana::config('medeur.war_newdeclarationcooldown')
		)
		{
			$message = kohana::lang( 'diplomacy.error-warcooldown');
			return false;				 			
		}					
		
		// Cooldown 15 giorni, eccetto stato OSTILE
		
		if ( 
			$par[2] -> timestamp > ( time() - kohana::config('medeur.diplomacychangecooldown') * 24 * 3600 ) 
			and 
			$par[3] != 'hostile' 
		)
		{ $message = kohana::lang('diplomacy.error-cooldownnotexpired'); return FALSE; }						
		
		
		$kingdomrunningwars = Kingdom_Model::get_kingdomwars( $par[0] -> region -> kingdom_id, 'running' );
		
		// Se il kingdom sorgente è in guerra non è possibile cambiare relazioni diplomatiche
		
		if ( 
			count( $kingdomrunningwars ) > 0 						
		)
		{ $message = kohana::lang('diplomacy.error-cannotchangediplomacyrelations'); return FALSE; }						
				
		return true;
		
	}
	
	public function execute_action( $par, &$message )
	{
		
		$sourcekingdom = ORM::factory('kingdom', $par[2] -> sourcekingdom_id );
		$targetkingdom = ORM::factory('kingdom', $par[2] -> targetkingdom_id );
		$targetroyalpalace = $targetkingdom -> get_structure( 'royalpalace' );
		$sourceking = $sourcekingdom -> get_king();
		$targetking = $targetkingdom -> get_king();			
		
		// Remove items
		
		$paper_piece = Item_Model::factory( null, 'paper_piece' );
		$paper_piece -> removeitem( "character", $par[0]->id, 1 );
		
		$waxseal = Item_Model::factory( null, 'waxseal' );
		$waxseal -> removeitem( "character", $par[0]->id, 1 );
		
		// Check type of relation
		
		$requirement = $this -> diplomacytransactionrequirement[$par[2] -> type][$par[3]];
		
		// se la modalità è diretta o è una conferma, setta stato diplomatico.
		
		if ($requirement == 'direct' or ($par[5] == true and $par[4] == 'accept'))
		{
			
			$diplomacyrelations = Configuration_Model::get_cfg_diplomacyrelations();
			$relation1 = $diplomacyrelations[$par[2] -> sourcekingdom_id][$par[2]-> targetkingdom_id];
			
			$dr1 = ORM::factory('diplomacy_relation', $relation1['id'] ); 
			$dr1 -> type = $par[3];
			$dr1 -> timestamp = time();
			$dr1 -> signedby = $sourceking -> id;
			$dr1 -> save();
			
			$relation2 = $diplomacyrelations[$par[2] -> targetkingdom_id][$par[2] -> sourcekingdom_id];
			$dr2 = ORM::factory('diplomacy_relation', $relation2['id'] ); 
			$dr2 -> type = $par[3];
			$dr2 -> timestamp = time();
			
			// se il re destinatario è assente il trattato è firmato unilateralmente dal re sorgente.
			if (!is_null($targetking))
				$dr2 -> signedby = $targetking -> id;		
			else
				$dr2 -> signedby = $sourceking -> id;		
			
			$dr2 -> save();
			
			// confirm proposal
			
			$proposal = ORM::factory('diplomacy_proposal')
				-> where ( array (
					'sourcekingdom_id' => $par[2] -> sourcekingdom_id,
					'targetkingdom_id' => $par[2] -> targetkingdom_id,
					'status' => 'new' ))
				-> find();
			
			if ( $proposal -> loaded )
			{
				$proposal -> status = 'accepted';
				$proposal -> save();
				
			}
			
			$runningwars = Kingdom_Model::get_kingdomwars( $sourcekingdom -> id, $status = 'running' );
			
			// Se la proposta è != allied e il regno proponente è 
			// ingaggiato in una guerra il regno esce dalla guerra.
			
			if ( $proposal -> diplomacyproposal != 'allied' )
			{
				
				if ( count( $runningwars ) > 0 )
				{
					
					$kingdomwar_ally = ORM::factory('Kingdom_Wars_Ally_Model')
						-> where( array(
							'kingdom_war_id' => $runningwars[0]['war'] -> id,
							'kingdom_id' => $targetkingdom -> id ) )
						-> find() 
						-> delete();
					
					
					Character_Event_Model::addrecord( 
						null, 
						'announcement', 
						'__events.kingdomexitedwar'. 
						';__' .	$targetkingdom -> name .
						';__' .	$runningwars[0]['war']-> sourcekingdomname .
						';__' .	$runningwars[0]['war']-> targetkingdomname,						
						'evidence'
						);
				}
				
			}
				
			
			// Se la proposta è di alleanza, e il regno proponente è
			// ingaggiato in una guerra, il regno che accetta l 'alleanza
			// entra direttamente in guerra.
				
			if ( $proposal -> diplomacyproposal == 'allied' )
			{
			
				
				if ( count( $runningwars ) > 0 )
				{
					
					$kingdomwars_allies = new Kingdom_Wars_Ally_Model();
					$kingdomwars_allies -> kingdom_war_id = $runningwars[0]['war'] -> id;
					$kingdomwars_allies -> kingdom_id = $targetkingdom -> id;
					$kingdomwars_allies -> save();
					
					Character_Event_Model::addrecord( 
						null, 
						'announcement', 
						'__events.kingdomjoinedwar'. 
						';__' .	$targetkingdom -> name .
						';__' .	$runningwars[0]['war']-> sourcekingdomname .
						';__' .	$runningwars[0]['war']-> targetkingdomname,						
						'evidence'
						);
				}
			}
			
			
			// Send events					
			
			Character_Event_Model::addrecord( 
				$sourceking -> id,
				'normal', 
				'__events.diplomacyproposalsource'. ';__' .			
				$par[2] -> targetkingdom_name . ';__' .
				'diplomacy.' . $par[3],
				'evidence'
				);
				
			if (!is_null($targetking))
				Character_Event_Model::addrecord( 
					$targetking -> id,
					'normal', 
					'__events.diplomacyproposaltarget'. ';__' .			
					$par[2] -> sourcekingdom_name . ';__' .
					'diplomacy.' . $par[3],
					'evidence'
					);			
			
			// town crier
			
			Character_Event_Model::addrecord( 
				null, 
				'announcement', 
				'__events.diplomacyproposalann'. ';__' .			
				$par[2] -> sourcekingdom_name . ';__' .
				$par[2] -> targetkingdom_name . ';__' .
				'diplomacy.' . $par[3],
				'evidence'
				);
			
			$message = kohana::lang( 'diplomacy.info-diplomacystatuschanged', kohana::lang( $par[2] -> targetkingdom_name ) );
			
		}
		// needs confirmation
		else
		{
			
			if (!is_null($targetking))
			{
				
				$proposal = new Diplomacy_Proposal_Model();
				$proposal -> diplomacy_relation_id = $par[2] -> id;
				$proposal -> sourcekingdom_id = $par[2] -> sourcekingdom_id;
				$proposal -> targetkingdom_id = $par[2] -> targetkingdom_id;
				$proposal -> diplomacyproposal = $par[3];
				$proposal -> status = 'new';
				$proposal -> timestamp = time();
				$proposal -> save();
				
				Character_Event_Model::addrecord( 
					$targetking -> id,
					'normal', 
					'__events.diplomacyproposal'. 
					';__' .	 $par[2] -> sourcekingdom_name . 
					';__' . 'diplomacy.' . $par[3] . 
					';' . url::base(true) . 'event/acceptdiplomacyrelationproposal/' . $par[2] -> sourcekingdom_id,
					'evidence'
				);
				
				$message = kohana::lang( 'diplomacy.info-diplomacystatusproposed', kohana::lang( $par[2] -> targetkingdom_name ) );
			}
		}
		
		$cachetag = '-diplomacyrelations';
		My_Cache_Model::delete ( $cachetag );
		
		return true;
		
	
	}
	
	protected function append_action( $par, &$message ) {}
	
	public function complete_action( $par ){}	
	
	public function cancel_action( ) {}

	public function get_action_message( $type = 'long') {}
	
}

