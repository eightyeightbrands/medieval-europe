<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Declarewar_Model extends Character_Action_Model
{
	
	const REQUIREDFUNDS = 3000;
	
	protected $immediate_action = true;
	
	// Effettua tutti i controlli relativi all' azione, sia quelli condivisi
	// con tutte le action che quelli peculiari 
	// @input: array di parametri
	// par[0]: oggetto char
	// par[1]: oggetto regno a cui si dichiara guerra
	// par[2]: struttura palazzo reale
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $message contiene il messaggio di ritorno	
	
	protected function check( $par, &$message )
	{ 					
		sleep(15);
		
		// non si può dichiarare guerra a sè stessi		
		
		if ( $par[1] -> id == $par[0] -> region -> kingdom_id)
		{
			$message = kohana::lang( 'ca_declarewar.error-cannotdeclarewarself');
			return false;
		}				
		
		// controllo se ci sono sufficienti fondi nel Palazzo Reale
		
		if ( $par[2] -> get_item_quantity( 'silvercoin' ) < self::REQUIREDFUNDS )
		{
			$message = kohana::lang( 'ca_declarewar.error-missingrequiredfunds', self::REQUIREDFUNDS);
			return false;			
		}
		
		// controllo: per dichiarare guerra si deve aver dichiarato hostile almeno da 48h
		
		$dr = Diplomacy_Relation_Model::get_diplomacy_relation( $par[0] -> region -> kingdom_id, $par[1] -> id );
		
		if ( $dr['type'] != 'hostile' or ($dr['type'] == 'hostile' and $dr['timestamp'] > ( time() - (48*3600)) ) )
		{
			$message = kohana::lang( 'ca_declarewar.error-hostilestatuscooldownfailed', self::REQUIREDFUNDS);
			return false;			
			
		}
		
		
		// controllo che il regno che dichiara guerra non sia già impegnato in guerra
		
		$attackingkingdomrunningwars = Kingdom_Model::get_kingdomwars( $par[0] -> region -> kingdom_id, 'running');		
		if (count($attackingkingdomrunningwars) > 0 )
		{ $message = kohana::lang( 'ca_declarewar.error-attackingkingdomisatwar'); return false;}
	
		// controllo che il regno a cui si dichiara guerra non sia già impegnato in guerra
		
		$defendingkingdomrunningwars = Kingdom_Model::get_kingdomwars( $par[1] -> id, 'running');
		if (count($defendingkingdomrunningwars) > 0 )
		{ $message = kohana::lang( 'ca_declarewar.error-defendingkingdomisatwar'); return false;}	
		
		
		// controllo che il regno non abbia partecipato come attaccante
		// ad una guerra nelle ultime ore
		
		$lastwar = Kingdom_Model::get_last_war( $par[0] -> region -> kingdom_id );
		
		if (
			$lastwar['kingdoms'][$par[0] -> region -> kingdom_id]['role'] == 'attacker'
			and
			round ( ( time() - $lastwar['war'] -> end )/(24*3600), 0 ) < kohana::config('medeur.war_newdeclarationcooldown')
		)
		{
			$message = kohana::lang( 'ca_declarewar.error-cantdeclarewarsosoon');
			return false;				 			
		}						
				
		return true;				
	}
		
	protected function append_action( $par, &$message ) {}

	function complete_action( $data ) {}
	
	public function execute_action ( $par, &$message) 
	{
		
		// Toglie denari a struttura
		
		$par[2] -> modify_coins( - self::REQUIREDFUNDS, 'wardeclaration');
		
		$war = new Kingdom_War_Model();
		$war -> source_kingdom_id = $par[0] -> region -> kingdom_id ;
		$war -> target_kingdom_id = $par[1] -> id;
		$war -> start = time();
		$war -> save();
		
		
		
		$sourcekingdomallies = Kingdom_Model::get_allies($par[0] -> region -> kingdom_id);
		$targetkingdomallies = Kingdom_Model::get_allies($par[1] -> id);

		// Agg. Regno Che dichiara
		$kingdomwar_kingdom = new Kingdom_Wars_Ally_Model();
		$kingdomwar_kingdom -> kingdom_war_id = $war -> id;
		$kingdomwar_kingdom -> kingdom_id = $par[0] -> region -> kingdom_id;
		$kingdomwar_kingdom -> role = 'attacker';
		$kingdomwar_kingdom -> save();
		
		// Agg. Regno a cui è dichiarato 
		$kingdomwar_kingdom = new Kingdom_Wars_Ally_Model();
		$kingdomwar_kingdom -> kingdom_war_id = $war -> id;
		$kingdomwar_kingdom -> kingdom_id = $par[1] -> id;
		$kingdomwar_kingdom -> role = 'defender';
		$kingdomwar_kingdom -> save();
		
		
		
		// Alleati
		foreach( $sourcekingdomallies as $sourcekingdomally_id )
		{
			$kingdomwar_kingdom = new Kingdom_Wars_Ally_Model();
			$kingdomwar_kingdom -> kingdom_war_id = $war -> id;
			$kingdomwar_kingdom -> kingdom_id = $sourcekingdomally_id;
			$kingdomwar_kingdom -> role = 'attacker';
			$kingdomwar_kingdom -> save();
		}
		
		foreach( $targetkingdomallies as $targetkingdomally_id )
		{
			$kingdomwar_kingdom = new Kingdom_Wars_Ally_Model();
			$kingdomwar_kingdom -> kingdom_war_id = $war -> id;
			$kingdomwar_kingdom -> kingdom_id = $targetkingdomally_id;
			$kingdomwar_kingdom -> role = 'defender';
			$kingdomwar_kingdom -> save();
		}
		
		My_Cache_Model::delete('-cfg-kingdomswars');
		
						
		Character_Event_Model::addrecord(
			$par[0] -> id,
			'normal',
			'__events.wardeclarationsource' . 			
			';__' . $par[1] -> name,			
			'evidence'			
		);		
		
		$targetking = $par[1] -> get_king();		
		if (!is_null($targetking))
			Character_Event_Model::addrecord(
				$targetking -> id,
				'normal',
				'__events.wardeclarationtarget' . 						
				';__' . $par[0] -> region -> kingdom -> name,
				'evidence'			
			);		
		
		// town crier 
		
		Character_Event_Model::addrecord( 
			null, 
			'announcement', 			
			'__events.warstarted' . 
			';__' . $par[0] -> region -> kingdom -> name . 
			';__' . $par[1] -> name,
			'evidence' );			
		
		$message = kohana::lang( 'ca_declarewaraction.wardeclaration_ok',  kohana::lang($par[1]->name) );
		
		return true;

	}
}
