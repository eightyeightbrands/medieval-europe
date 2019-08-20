<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Assignrolerp_Model extends Character_Action_Model
{
	// Azione di tipo immediato
	protected $immediate_action = true;

	// Costanti
	// ***********************************************************
	// Definizione del massimo numero dei ruoli assegnabili da
	// ogni ruolo nel gioco
	// ***********************************************************
	
	const RPROLE_KING_SENESCHAL = 1;
	const RPROLE_KING_CONSTABLE = 1;
	const RPROLE_KING_CHANCELLOR = 1;
	const RPROLE_KING_CHAMBERLAIN = 1;
	const RPROLE_KING_TREASURER = 1;
	const RPROLE_KING_AMBASSADOR = 3;
	const RPROLE_KING_CHAPLAIN = 1;

	const RPROLE_KING_PRINCE = 1;
	const RPROLE_KING_MARQUIS = 1;
	const RPROLE_KING_DUKE = 1;
	const RPROLE_KING_EARL = 1;
	const RPROLE_KING_VISCOUNT = 1;
	const RPROLE_KING_BARON = 2;
	
	const RPROLE_VASSAL_PREFECT = 1;
	const RPROLE_VASSAL_CUSTOMSOFFICER = 1;
	const RPROLE_VASSAL_LORD = 2;
	const RPROLE_VASSAL_KNIGHT = 4;
	const RPROLE_SHERIFF_LIEUTENANT = 1;	
	const RPROLE_JUDGE_BAILIFF = 1;	
	const RPROLE_DRILLMASTER_TRAINER = 1;	
	const RPROLE_DIRECTOR_ASSISTANT = 1;
	
	const RPROLE_CHURCH1_PRIMATE = 5;
	const RPROLE_CHURCH1_VICAR = 1;
	const RPROLE_CHURCH1_GINQUISITOR = 1;
	const RPROLE_CHURCH1_GALMONER = 1;
	const RPROLE_CHURCH1_AMBASSADOR = 2;
	
	const RPROLE_CHURCH2_INQUISITOR = 1;
	const RPROLE_CHURCH2_ALMONER = 1;
	
	const RPROLE_CHURCH3_MONK = 3;
	
	const RPROLE_CHURCH4_ACOLYTE = 1;
	
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
			)		
		),
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
	// @param   par[0]: oggetto char di chi nomina
	// @param   par[1]: oggetto char di chi è nominato
	// @param   par[2]: tag ruolo
	// @param   par[3]: regione da cui parte la nomina
	// @param   par[4]: struttura da cui parte la nomina
	// @param   par[5]: nome del feudo
	//
	// @output  TRUE = azione disponibile, FALSE = azione non disponibile
	// @output  $message contiene il messaggio di ritorno
	// ***********************************************************
	
	protected function check( $par, &$message )
	{ 
		if ( ! parent::check( $par, $message, $par[0] -> id, $par[1] -> id ) )					
		{ return false; }				
		
		kohana::log('debug', 'Trying to appoint RP role: ' . $par[2] . '-' . $par[1]->name );

		////////////////////////////////////////////
		// Check parametri manipolabili
		////////////////////////////////////////////
		
		if ( !$par[0]->loaded or !$par[4]->loaded	)
		{ $message = kohana::lang( 'global.operation_not_allowed'); return false; }

		if ( !$par[1] -> loaded or $par[1] -> name == ''  )
		{ $message = kohana::lang( 'global.error-characterunknown'); return false; }

		$appointer_role = $par[0] -> get_current_role();

		// Il char può nominare solo alcuni ruoli rp
		// in base al proprio ruolo
		
		if
		(
			(
				in_array( $par[2], array('seneschal', 'constable', 'chancellor', 'chamberlain', 'tresaurer', 
				'ambassador', 'chaplain', 'prince', 'duke', 'marquis', 'earl', 'viscount', 'baron') ) 
				and ($appointer_role and $appointer_role->tag != 'king')
			)
			or
			(
				in_array( $par[2], array('prefect', 'customsofficer', 'lord', 'knight') ) 
				and ($appointer_role and $appointer_role->tag != 'vassal')
			)
			or
			(
				in_array( $par[2], array('lieutenant') ) 
				and ($appointer_role and $appointer_role->tag != 'sheriff')
			)
			or
			(
				in_array( $par[2], array('bailiff') ) 
				and ($appointer_role and $appointer_role->tag != 'judge')
			)
			or
			(
				in_array( $par[2], array('trainer') ) 
				and ($appointer_role and $appointer_role->tag != 'drillmaster')
			)
			or
			(
				in_array( $par[2], array('assistant') ) 
				and ($appointer_role and $appointer_role->tag != 'academydirector')
			)
			or
			(
				in_array( $par[2], array('primate', 'generalvicar', 'greatinquisitor', 'greatalmoner', 'ambassadorchurch') ) 
				and ($appointer_role and $appointer_role->tag != 'church_level_1')
			)
			or
			(
				in_array( $par[2], array('inquisitor', 'almoner') ) 
				and ($appointer_role and $appointer_role->tag != 'church_level_2')
			)
			or
			(
				in_array( $par[2], array('monk') ) 
				and ($appointer_role and $appointer_role->tag != 'church_level_3')
			)
			or
			(
				in_array( $par[2], array('acolyte') ) 
				and ($appointer_role and $appointer_role->tag != 'church_level_4')
			)
		)
		{		
			$message = kohana::lang( 'global.operation_not_allowed');
			return false;
		}

		// Char must be of the same kingdom only for
		// Government Roles
		
		if ( 
				$appointer_role -> get_roletype() != 'religious' 
				and 
				$par[0] -> region -> kingdom_id != $par[1] -> region -> kingdom_id 
				and
				in_array($par[2], 
					array
					(
						'seneschal', 'constable', 'chancellor', 'chamberlain', 'tresaurer', 
						'ambassador', 'chaplain', 'prefect', 'customsofficer',
						'lieutenant', 'bailiff', 'trainer', 'assistant' 			
					) 
				)			
			) 
		{
			$message = kohana::lang( 'ca_assignrolerp.error-charnotofthesamekingdom');
			return false;		
		}
		
		/////////////////////////////////////////////////////////////////////////////		
		// il candidato fa parte della stessa chiesa di chi lo sta nominando?
		/////////////////////////////////////////////////////////////////////////////	

		if ( $appointer_role -> get_roletype() == 'religious' and	$par[0] -> church -> id  != $par[1] -> church -> id ) 
		{	
			$message =kohana::lang( 'ca_assignrolerp.error-charnotofthesamechurch');
			return false;
		}	
		
		// Verifico che non sia superato il numero dei ruoli RP
		// consentiti da questa struttura		
		
		$numroles = $par[4] -> count_rproles_assigned_by_structure( $par[2] );
		
		if 
		(
			(
			$par[2] == 'seneschal'
			and $numroles >= self::RPROLE_KING_SENESCHAL
			and $par[4]->structure_type->type == "royalpalace" 
			and $appointer_role->tag == 'king'
			)
			or
			(
			$par[2] == 'constable'
			and $numroles >= self::RPROLE_KING_CONSTABLE
			and $par[4]->structure_type->type == "royalpalace" 
			and $appointer_role->tag == 'king'
			)
			or
			(
			$par[2] == 'chancellor'
			and $numroles >= self::RPROLE_KING_CHANCELLOR
			and $par[4]->structure_type->type == "royalpalace" 
			and $appointer_role->tag == 'king'
			)
			or
			(
			$par[2] == 'chamberlain'
			and $numroles >= self::RPROLE_KING_CHAMBERLAIN
			and $par[4]->structure_type->type == "royalpalace" 
			and $appointer_role->tag == 'king'
			)
			or
			(
			$par[2] == 'treasurer'
			and $numroles >= self::RPROLE_KING_TREASURER
			and $par[4]->structure_type->type == "royalpalace" 
			and $appointer_role->tag == 'king'
			)
			or
			(
			$par[2] == 'ambassador'
			and $numroles >= self::RPROLE_KING_AMBASSADOR
			and $par[4]->structure_type->type == "royalpalace" 
			and $appointer_role->tag == 'king'
			)
			or
			(
			$par[2] == 'chaplain'
			and $numroles >= self::RPROLE_KING_CHAPLAIN
			and $par[4]->structure_type->type == "royalpalace" 
			and $appointer_role->tag == 'king'
			)
			or
			(
			$par[2] == 'prince'
			and $numroles >= self::RPROLE_KING_PRINCE
			and $par[4]->structure_type->type == "royalpalace" 
			and $appointer_role->tag == 'king'
			)
			or
			(
			$par[2] == 'duke'
			and $numroles >= ( self::RPROLE_KING_DUKE * $par[4] -> get_childstructures_tot( 'castle' ) )
			and $par[4]->structure_type->type == "royalpalace" 
			and $appointer_role->tag == 'king'
			)
			or
			(
			$par[2] == 'marquis'
			and $numroles >= ( self::RPROLE_KING_MARQUIS * $par[4] -> get_childstructures_tot( 'castle' ) )			
			and $par[4]->structure_type->type == "royalpalace" 
			and $appointer_role->tag == 'king'
			)
			or
			(
			$par[2] == 'earl'
			and $numroles >= ( self::RPROLE_KING_EARL * $par[4] -> get_childstructures_tot( 'castle' ) )
			and $par[4]->structure_type->type == "royalpalace" 
			and $appointer_role->tag == 'king'
			)
			or
			(
			// Per il visconte devo considerare anche il numero totale dei castelli
			$par[2] == 'viscount'
			and $numroles >= ( self::RPROLE_KING_VISCOUNT * $par[4]->get_childstructures_tot( 'castle' ) )
			and $par[4]->structure_type->type == "royalpalace" 
			and $appointer_role->tag == 'king'
			)
			or
			(
			// Per il barone devo considerare anche il numero totale dei castelli
			$par[2] == 'baron'
			and $numroles >= ( self::RPROLE_KING_BARON * $par[4]->get_childstructures_tot( 'castle' ) )
			and $par[4]->structure_type->type == "royalpalace" 
			and $appointer_role->tag == 'king'
			)
			or
			(
			$par[2] == 'prefect'
			and $numroles >= self::RPROLE_VASSAL_PREFECT
			and $par[4]->structure_type->type == "castle" 
			and $appointer_role->tag == 'vassal'
			)
			or
			(
			$par[2] == 'customsofficer'
			and $numroles >= self::RPROLE_VASSAL_CUSTOMSOFFICER
			and $par[4]->structure_type->type == "castle" 
			and $appointer_role->tag == 'vassal'
			)
			or
			(
			$par[2] == 'lord'
			and $numroles >= self::RPROLE_VASSAL_LORD
			and $par[4]->structure_type->type == "castle" 
			and $appointer_role->tag == 'vassal'
			)
			or
			(
			$par[2] == 'knight'
			and $numroles >= self::RPROLE_VASSAL_KNIGHT
			and $par[4]->structure_type->type == "castle" 
			and $appointer_role->tag == 'vassal'
			)
			or
			(
			$par[2] == 'lieutenant'
			and $numroles >= self::RPROLE_SHERIFF_LIEUTENANT
			and ($par[4]->structure_type->type == "barracks_1" or $par[4]->structure_type->type == "barracks_2")
			and $appointer_role->tag == 'sheriff'
			)
			or
			(
			$par[2] == 'bailiff'
			and $numroles >= self::RPROLE_JUDGE_BAILIFF
			and $par[4]->structure_type->type == "court" 
			and $appointer_role->tag == 'judge'
			)
			or
			(
			$par[2] == 'trainer'
			and $numroles >= self::RPROLE_DRILLMASTER_TRAINER
			and $par[4]->structure_type->type == "trainingground_1"
			and $appointer_role->tag == 'drillmaster'
			)
			or
			(
			$par[2] == 'assistant'
			and $numroles >= self::RPROLE_DIRECTOR_ASSISTANT
			and ($par[4]->structure_type->type == "academy" or $par[4]->structure_type->type == "academy_1" or $par[4]->structure_type->type == "academy_2")
			and $appointer_role->tag == 'academydirector'
			)
			or
			(
			$par[2] == 'primate'
			and $numroles >= self::RPROLE_CHURCH1_PRIMATE
			and $par[4]->structure_type->type == "religion_1"
			and $appointer_role->tag == 'church_level_1'
			)
			or
			(
			$par[2] == 'generalvicar'
			and $numroles >= self::RPROLE_CHURCH1_VICAR
			and $par[4]->structure_type->type == "religion_1"
			and $appointer_role->tag == 'church_level_1'
			)
			or
			(
			$par[2] == 'greatinquisitor'
			and $numroles >= self::RPROLE_CHURCH1_GINQUISITOR
			and $par[4]->structure_type->type == "religion_1"
			and $appointer_role->tag == 'church_level_1'
			)
			or
			(
			$par[2] == 'greatalmoner'
			and $numroles >= self::RPROLE_CHURCH1_GALMONER
			and $par[4]->structure_type->type == "religion_1"
			and $appointer_role->tag == 'church_level_1'
			)
			or
			(
			$par[2] == 'ambassador'
			and $numroles >= self::RPROLE_CHURCH1_AMBASSADOR
			and $par[4]->structure_type->type == "religion_1"
			and $appointer_role->tag == 'church_level_1'
			)
			or
			(
			$par[2] == 'inquisitor'
			and $numroles >= self::RPROLE_CHURCH2_INQUISITOR
			and $par[4]->structure_type->type == "religion_2"
			and $appointer_role->tag == 'church_level_2'
			)
			or
			(
			$par[2] == 'almoner'
			and $numroles >= self::RPROLE_CHURCH2_ALMONER
			and $par[4]->structure_type->type == "religion_2"
			and $appointer_role->tag == 'church_level_2'
			)
			or
			(
			$par[2] == 'monk'
			and $numroles >= self::RPROLE_CHURCH3_MONK
			and $par[4]->structure_type->type == "religion_3"
			and $appointer_role->tag == 'church_level_3'
			)
			or
			(
			$par[2] == 'acolyte'
			and $numroles >= self::RPROLE_CHURCH4_ACOLYTE
			and $par[4]->structure_type->type == "religion_4"
			and $appointer_role->tag == 'church_level_4'
			)
		)		
		{ $message = kohana::lang('ca_assignrole.max-assignment-reached'); return false; }		

		// Controllo che il char non abbia già il ruolo
		
		$gdrrole = ORM::factory('character_role') -> where
			( 
				array( 
				'character_id' => $par[1] -> id,
				'gdr' => true,
				'current' => true,
				'kingdom_id' => $par[0] -> region -> kingdom_id,
				'current' => true, 
				'tag' => $par[2])) -> count_all();
				
		if ( $gdrrole >= 1 )
		{ $message = kohana::lang('ca_assignrole.error-charhasalreadyrole'); return false; }		
		
		
		return true;
	}

	// Azione immediata -> nessun controllo
	
	protected function append_action( $par, &$message ) {}

	public function execute_action ( $par, &$message) 	
	{			
	
		$appointer_role = $par[0] -> get_current_role();
		
		// Consumo degli items/vestiti indossati
		Item_Model::consume_equipment( $this->equipment, $par[0]);
		
		///////////////////////////////////////////
		// Creo il ruolo RP;
		///////////////////////////////////////////
		
		Character_Role_Model::start( $par[1], $par[2], $par[3], $par[4]->id, $par[5], true );
		
		// reload
		
		$appointed = ORM::factory( 'character', $par[1] -> id );		
		
		// Recupero tutti i titoli customizzati del regno
		$knt = new Kingdom_Nobletitle_Model;
		$customtitles = $knt->get_customisedtitles($par[0] -> region -> kingdom ->id);
		$temprole='';
		// Verifico se il titolo assegnato è customizzato
		// Passo al messaggio del town crier il titolo giusto
		if ( array_key_exists($par[2],$customtitles) )
		{
			// Ritorno il titolo in base al sesso del character
			if ($appointed->sex = 'M')
			{ $temprole = $customtitles[$par[2]]['customisedtitle_m']; }
			else
			{ $temprole = $customtitles[$par[2]]['customisedtitle_f']; }
		}
		else
		{
			$temprole = kohana::lang('global.' . $par[2] . '_' . strtolower( $appointed -> sex));
		}
		//var_dump ($temprole);
		//var_dump($customtitles);exit;
		
		if ($par[5] != null)
		{
			$msg1 = '__events.gdrtitleappointment_announcement;' . 
			$par[1] -> name . ';' . $temprole . ';' .
			$par[5];
				
			$msg2 = '__events.gdrtitleappointed_announcement;';
			$msg3 = 'structures.assignrptitle';
		}
		else
		{
			
			$msg1 = '__events.gdrroleappointment_announcement;' . 				
			$par[1] -> name . ';' . $temprole . ';__' . 
			$par[0] -> region -> kingdom -> get_name() ;	

			$msg2 = '__events.gdrroleappointed_announcement;';
			$msg3 = 'structures.assignrprole';
		}
		
		// se il titolo è cancelliere, dò le grant dovute
		if ( $par[2] == 'chancellor' )
		{
			$royalpalace = $par[4] -> region -> get_controllingroyalpalace();
			$ca = new CA_Assignstructuregrant_Model();
			$_par[0] = $royalpalace;
			$_par[1] = $par[1];
			$_par[2] = $par[2];
			$ca -> do_action( $_par, $message );	
		}
		
		// Pubblica annuncio nel town crier
		Character_Event_Model::addrecord(	null, 'announcement', $msg1	);
		
		// Pubblica annuncio negli eventi del char nominato
		Character_Event_Model::addrecord( $par[1] -> id, 'normal', $msg2 );
		
		// Annuncio per chi nomina
		Character_Event_Model::addrecord(
			$par[0] -> id,
			'normal',  
			'__events.gdrtitleassignedsource'.
			';' . $temprole .  
			';__global.of' .
			';' . $par[5] .
			';' . $par[1] -> name
			);
		
		// Flash message
		$message = kohana::lang( $msg3 );

		return true;
	}
}
