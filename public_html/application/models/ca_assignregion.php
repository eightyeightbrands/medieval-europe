<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Assignregion_Model extends Character_Action_Model
{

	protected $immediate_action = true;

	// Effettua tutti i controlli relativi all' azione, sia quelli condivisi
	// con tutte le action che quelli peculiari 
	// @input: array di parametri
	// par[0]: oggetto char di chi assegna la regione (re)
	// par[1]: oggetto regione da assegnare
	// par[2]: oggetto char del vassallo che ha il controllo della regione
	// par[3]: oggetto char del vassallo che avrà il controllo della regione
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $message contiene il messaggio di ritorno	
	
	protected function check( $par, &$message )
	{ 
		
		if ( ! parent::check( $par, $message, $par[0] -> id, $par[3] -> id ) )					
		{ return false; }
		
		// controllo parametri manipolabili. il vassallo che ha il controllo della regione potrebbe non esistere,
		// quindi non lo controllo
		
		if ( !$par[1]->loaded or !$par[3]->loaded )
		{$message = kohana::lang( 'global.operation_not_allowed');return false;}

		// controllo che il vassallo destinatario sia del regno giusto
		if ( $par[3] -> region -> kingdom -> id != $par[0] -> region -> kingdom -> id )
		{$message = kohana::lang( 'ca_assignregion.vassalnotfromthekingdom', $par[3] -> name );return false;}

		// controllo che il destinatario, sia effettivamente un vassallo		
		$role = $par[3] -> get_current_role();
		if ( is_null( $role ) or $role -> tag != 'vassal' )		
		{$message = kohana::lang( 'global.operation_not_allowed');return false;}
		
		// controllo che la regione non sia già controllata dal vassallo
		if ( $par[2] -> id == $par[3] -> id )
		{$message = kohana::lang( 'ca_assignregion.regionalreadycontrolled', $par[3] -> name);return false;}

		// controllo che la regione sia del regno
		if ( $par[1] -> kingdom -> id != $par[0] -> region -> kingdom -> id )
		{$message = kohana::lang( 'ca_assignregion.regionnotownedbykingdom');return false;}		

		// Se la regione ha un castello, non è assegnabile.
		$castle = $par[1] -> get_structure('castle'); 
		if ( !is_null( $castle ) )
		{$message = kohana::lang( 'ca_assignregion.regionnotassignable');return false;}		

		// se il regno è in guerra, non si possono assegnare regioni
		$data = null;
		$iskingdomfighting = $par[0] -> region -> kingdom -> is_fighting( $par[0] -> region -> kingdom_id, $data );
		if ( $iskingdomfighting == true )
		{$message = kohana::lang( 'ca_assignregion.regionnotassignable_war');return false;}		

		
		
		return true;
	}

	
	// nessun controllo particolare
	protected function append_action( $par, &$message )
	{}

	public function execute_action ( $par, &$message) 
	{	
		
		// trovo il nativevillage della regione da donare
		
		$nativevillage = $par[1] -> get_structure('nativevillage'); 		
		
		// var_dump($nativevillage);exit;
		
		// trovo il castello del vassallo
		
		$controlledcastle = null;
		
		$role = $par[3] -> get_current_role();
		$controlledstructures = $role -> get_controlledstructures();
		foreach( $controlledstructures as $controlledstructure )
			if ( $controlledstructure -> getSupertype() == 'castle' )
			{
				$controlledcastle = $controlledstructure;
				break;
			}
				
		// trovo i buildingsite, gli assegno il nuovo vassallo
		// e li lego al castello
		
		$buildingsites = $par[1] -> get_structures( 'buildingsite' );						
		if ( !is_null( $buildingsites ) )
			foreach ( $buildingsites as $buildingsite )
			{
				kohana::log('debug', "Processing buildingsite: {$buildingsite->id}");
				$structure_type = ORM::factory('structure_type', $buildingsite -> attribute1 ); 
				
				if ( !in_array( $structure_type -> supertype, array( 'castle', 'academy', 'trainingground' ) ) )
				{
					$buildingsite -> parent_structure_id = $controlledcastle -> id;
					$buildingsite -> character_id = $controlledcastle -> character_id ;
					$buildingsite -> save();
				}					
			}
		
		// assegno il native village al castello del vassallo.
		
		$nativevillage -> parent_structure_id = $controlledcastle -> id;
		$nativevillage -> save();	
		
		// pubblica annuncio
		
		Character_Event_Model::addrecord( 
			null, 
			'announcement', 
			'__events.assign_region_announcement' .
			';' . $par[0] -> name . 
			';' . $par[0] -> get_rolename() . 
			';__' . $par[1] -> name . 
			';'   . $par[3] -> name, 
		'evidence' ); 
			
		// manda evento al vassallo che ha ricevuto
		// la regione
		
		
			Character_Event_Model::addrecord( 
				$par[3]->id, 
				'normal',  
				'__events.assign_region_sourcevassal' .
				';' . $par[0] -> name . 
				';' . $par[0] -> get_rolename() . 
				';__' . $par[1] -> name,
				'evidence'
				);
		
		
		
		$message = kohana::lang( 'ca_assignregion.assign_region-ok', kohana::lang( $par[1] -> name ), $par[3] -> name );
		return true;

	}
	
}
