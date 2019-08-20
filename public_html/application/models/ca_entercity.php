<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Entercity_Model extends Character_Action_Model
{
	protected $immediate_action = true;
	
	/*
	 * par[0] = oggetto char
	 * par[1] = oggetto struttura	 
	*/
	
	protected function check( $par, &$message )
	{ 
		if ( ! parent::check( $par, $message ) )					
		{ return false; }
		
		// E' possibile entrare in cittÃ  solo se le azioni diplomatiche lo consentono.		
		// check se il rapporto tra il regno e il regno del char è ostile
		
		$dr = Diplomacy_Relation_Model::get_diplomacy_relation( 
			$par[1] -> region -> kingdom_id, 
			$par[0] -> region -> kingdom_id );
			
		$stat = $par[0] -> get_stat_d( $par[0] -> id, 'accesspermit', $par[1] -> region -> kingdom_id ); 					
			
		if ( !is_null( $dr ) 
			and $dr['type'] == 'hostile' 
			and ( !$stat -> loaded or $stat -> value < time() )
		)
		{
			$message = kohana::lang('ca_move.error-hostileaccessdenied'); 
			return false;				
		}
		
		
		// Se il kingdom B è in guerra, il char non puÃ² muoversi se
		// è alleato di almeno un regno che sta attaccando
		
		$data = null;
		$isonwar = $par[1] -> region -> kingdom -> is_fighting( $par[1] -> region -> kingdom_id, $data);		
		
		if ( $isonwar !== false )		{
			// per ogni battaglia, verifichiamo se il char è alleato di un regno
			// che sta attaccando il regno B
			foreach ( $data['battles'] as $battle )
			{
			
				$attackingregion = ORM::factory('region', $battle -> source_region_id );
				// consideriamo solo le battaglie in cui il regno verso cui ci si 
				// sta muovendo è attaccato
				if ( $attackingregion -> kingdom_id != $par[1] -> region -> kingdom_id )
				{
					
					// stabiliamo la relazione tra il regno di chi si muove e il regno
					// che sta attaccando
					$dr = Diplomacy_Relation_Model::get_diplomacy_relation( 					
						$par[0] -> region -> kingdom_id,
						$attackingregion -> kingdom_id);				
				
					if ( 
						!is_null( $dr ) 
						and $dr['type'] == 'allied' 
						and $par[0] -> region -> kingdom_id != $par[1] -> region -> kingdom_id						
						and ( !$stat -> loaded or $stat -> value < time() ))
					{
					
						$message = kohana::lang('ca_move.error-diplomacyrelationhostileorallied'); 
						return false;
					
					}
				}
			}
		
		}
		
		return true;
	}

	
	// nessun controllo particolare
	protected function append_action( $par, &$message )
	{}

	public function execute_action ( $par, &$message ) 
	{
	
		$db = Database::instance(); 
		
		// se il battlefield esiste...
		
		if ( $par[1] -> loaded )
		{		
			$sql = "
				delete 
				from battle_participants 
				where battle_id = " . $par[1] -> attribute1 . 
				" and character_id = " . $par[0] -> id ; 
			
			$db -> query( $sql ); 
			
		}
				
		$par[0] -> modify_stat( 
			'fighting', 
			false,
			null,
			null,
			true );
	
		Character_Event_Model::addrecord( $par[0] -> id , 'normal', '__events.battlefield_leave');		
		
		$message = kohana::lang( 'ca_entercity.enteredcity-ok');
		
		return true;
	}
}
