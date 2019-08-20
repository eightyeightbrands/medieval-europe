<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Assignrole_Model extends Character_Action_Model
{

	protected $immediate_action = true;
	protected $required_fp = 0;
	protected $required_sc = 0;
	
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
	
	/**
	* Effettua tutti i controlli relativi all' azione, sia quelli condivisi
	* con tutte le action che quelli peculiari 
	* @input: array di parametri
	* par[0]: oggetto char di chi nomina
	* par[1]: oggetto char di chi è nominato
	* par[2]: tag ruolo
	* par[3]: regione dove risiede la struttura a cui assegnare l' incarico
	* par[4]: struttura da cui parte la nomina
	* @output: TRUE = azione disponibile, FALSE = azione non disponibile
	* $message contiene il messaggio di ritorno	
	*/
	
	protected function check( $par, &$message )
	{ 
		
		kohana::log('debug', '-> Trying to appoint: ' . $par[2] . '-' . $par[1] -> name );
		
		if ( parent::check( $par, $message, $par[0] -> id, $par[1] -> id ) == false )					
		{ return false; }				
				
		////////////////////////////////////////////
		// controllo parametri manipolabili
		////////////////////////////////////////////
		
		if (
				!$par[0] -> loaded 		
			or !$par[3] -> loaded
			or !$par[4] -> loaded )
		{$message = kohana::lang( 'global.operation_not_allowed');return false;}
		
		if ( !$par[1] -> loaded or $par[1] -> name == ''  )
		{$message = kohana::lang( 'global.error-characterunknown');return false;}
		
		$appointer_role = $par[0] -> get_current_role();
		$appointed_role = $par[1] -> get_current_role();
		
		// Non applicabile ad un NPC
		
		if ($par[1] -> type == 'npc')
		{$message = kohana::lang( 'ca_assignrole.error-cantassignroletonpc'); return false;}			
		
		////////////////////////////////////////////
		// Il player ha già un ruolo NON GDR?
		////////////////////////////////////////////
		
		if ( !is_null( $appointed_role) and $appointed_role -> gdr == false )
		{		
			$message = kohana::lang( 'ca_assignrole.error-rolealreadyowned',
				$par[1] -> name,
				kohana::lang('global.' . $appointed_role -> tag), 
				kohana::lang($appointed_role -> region -> name) );
			return false;
		}		
		
		////////////////////////////////////////////
		// controllo se il ruolo per la regione è
		// già occupato
		////////////////////////////////////////////
		
		$role = $par[3] -> get_roledetails( $par[2] ); 		
		
		if (!is_null( $role ))		
			{$message = kohana::lang( 'global.rolealreadyassigned'); return false;}		
		
		// esiste la struttura ?
		// il buildingsite non si controlla
		
		$structuretags = Character_Role_Model::get_controlledstructurestags( $par[2] );		
		foreach ( $structuretags as $structuretag )
		{
			if ( $structuretag == 'buildingsite' ) 
				continue;
			$s = $par[3] -> get_structures( $structuretag ) ;
			if ( count($s) == 0 )
			{
				$message = kohana::lang( 'ca_assignrole.structuredoesnotexist', 
				kohana::lang( 'structures.' . $structuretag), 
				kohana::lang($par[3] -> name ));
				return false;
			}
			
		}
		
		/////////////////////////////////////
		// controllo costo
		/////////////////////////////////////
		
		if ( in_array( $par[2], 
			array( 'church_level_2', 'church_level_3', 'church_level_4') ) )
		{
			$this -> required_fp = Character_Role_Model::get_requiredfp( $par[1], 'assign', $par[2] );
			$structureinfo = $par[4] -> get_info();
			kohana::log('debug', "ID: {$par[4]->id}, FP: {$structureinfo['faithpoints']}");
			if ( $structureinfo['faithpoints'] < $this -> required_fp )
			{
				$message = 
					kohana::lang( 'ca_assignrole.error-notenoughfaithpoints', $this -> required_fp );
				return false;	
			}
		}
		else
		{
			$this -> required_sc = Character_Role_Model::get_requiredcoins( $par[1], 'assign', $par[2] );
			$structurecoins = $par[4] -> get_item_quantity( 'silvercoin' );
			if ( $structurecoins < $this -> required_sc )
			{
				$message = 
					kohana::lang( 'ca_assignrole.error-notenoughmoney', $this -> required_sc );
				return false;	
			}
		}
		
		// il candidato è dello stesso regno?
		// controllo effettuato solo per inc. governativi
		
		if ( !in_array( $par[2], 
			array( 'church_level_2', 'church_level_3', 'church_level_4') ) 
			and $par[1] -> region_id != $par[3] -> id )
		{
			$message = kohana::lang( 'ca_assignrole.error-candidatemustberesident', kohana::lang(
				$par[3] -> name) );
			return false;	
		}		
		
		////////////////////////////////////////////		
		// il candidato	 ha i corretti attributi?
		////////////////////////////////////////////
		
		if ( Character_Role_Model::check_eligibility( $par[1], $par[2], $par[0] -> church, $message ) == false )
		{						
			return false;
		}
				
		return true;
	}

	
	// nessun controllo particolare
	protected function append_action( $par, &$message )
	{	}

	public function execute_action ( $par, &$message) 
	{	
	
		$appointer_role = $par[0] -> get_current_role();
		
		// Consumo degli items/vestiti indossati
		Item_Model::consume_equipment( $this -> equipment, $par[0] );
		
		///////////////////////////////////////////
		// creo il ruolo;
		///////////////////////////////////////////
		
		Character_Role_Model::start( $par[1], $par[2], $par[3], $par[4] -> id, null, false );
		$appointed = ORM::factory( 'character', $par[1] -> id );		
		
		// tolgo fps
		
		if ( in_array( $par[2], array( 'church_level_2', 'church_level_3', 'church_level_4')) ) 		
		{		
			$par[4] -> modify_fp ( - $this -> required_fp, 'roleassignment' );
			Structure_Event_Model::newadd(
				$par[4] -> id,
				'__events.fpupdateroleassignment' . 
				';' . $this -> required_fp . 
				$appointed -> get_rolename() . ';' . 
				$appointed -> name 				
				);
		}
		else
		{
			$par[4] -> modify_coins( - $this -> required_sc, 'roleassignment' );
		}
		
				
		// pubblica annuncio
		if ( in_array( $par[2], array( 'church_level_2', 'church_level_3', 'church_level_4') ) )
		{
			Character_Event_Model::addrecord(
				null,
				'announcement', 
				'__events.newappointmentchurch_announcement'.
				';'. $par[0] -> name .			
				$par[0] -> get_rolename().
				';'. $par[1] -> name .
				$appointed -> get_rolename() . 
				';__' . $par[3] -> name, 
				'evidence'
				);
			Character_Event_Model::addrecord(
				$par[1]->id, 
				'normal',  
				'__events.newappointchurchrole_event'.
				';'. $par[0]->name .
				$par[0] -> get_rolename() .
				$appointed -> get_rolename()			
				);
		}
		else
		{
			Character_Event_Model::addrecord(
				null,
				'announcement', 
				'__events.newappointment_announcement'.
				';'. $par[0] -> name .			
				$par[0] -> get_rolename().
				';'. $par[1] -> name .
				$appointed -> get_rolename(),
				'evidence'
				);	
			Character_Event_Model::addrecord(
				$par[1]->id, 
				'normal',  
				'__events.newappointrole_event'.
				';'. $par[0]->name .
				$par[0] -> get_rolename() .
				$appointed -> get_rolename()			
				);
		}
	
					
		$message = kohana::lang( 'structures.assignrole', $par[1] -> name , $appointed -> get_rolename( true ) );

		return true;

	}
	
}
