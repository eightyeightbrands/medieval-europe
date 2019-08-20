<?php defined('SYSPATH') OR die('No direct access allowed.');


class CA_Changecity_Model extends Character_Action_Model
{

	protected $cancel_flag = false;
	protected $immediate_action = true;	
	const TIMEBETWEENCHANGES = 2592000;// 30 giorni
	protected $change_price = 0;
	
	// Effettua tutti i controlli relativi al move, sia quelli condivisi
	// con tutte le action che quelli peculiari del move
	// @input: $par[0] = character
	//         $par[1] = oggetto regione di destinazione
	//         $par[2] = costo del trasferimento
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function check( $par, &$message )
	{ 
		$message = "";
		
		if ( ! parent::check( $par, $message ) )					
			return false;
		
		// Controllo parametri
		if ( !$par[0]->loaded or !$par[1]->loaded )
			{ $message = kohana::lang('global.operation_not_allowed'); return FALSE; }
		
		// controllo se c'è la diplomazia ostile tra i regni
		$dr = Diplomacy_Relation_Model::get_diplomacy_relation( 
			$par[1] -> kingdom_id, 
			$par[0] -> region -> kingdom_id );
		
		if ( !is_null( $dr ) 
			and $dr['type'] == 'hostile' )
		{
			$message = kohana::lang('ca_changecity.error-hostileaccessdenied'); 
			return false;				
		}
		
		// non ci si può trasferire nella città dove si è residenti.
		if ( $par[1]->id == $par[0]->region_id )
			{ $message = sprintf( 
				kohana::lang('charactions.change_city_samecity'), kohana::lang($par[1]->name))				
				; return FALSE; }		
		
		// Per trasferirsi bisogna essere nella città di arrivo
		if ( $par[1]->id != $par[0]->position_id )
			{ $message = sprintf( 
				kohana::lang('charactions.change_city_mustbeintargetcity'), kohana::lang($par[1]->name))				
				; return FALSE; }		
		
		// Non è possibile trasferirsi in una regione indipendente
		if ( $par[1] -> is_independent() ) 
		{	$message = kohana::lang('charactions.change_city_destregionisindependent', kohana::lang($par[1]->name)); return false; }	
		
		// Non è possibile trasferirsi in una regione piena
		if ( $par[1] -> is_full() )		
		{	$message = kohana::lang('charactions.change_city_destregionisfull', kohana::lang($par[1]->name)); return false; }	
		
		if ( $par[0] -> is_newbie($par[0])==false and $par[0] -> check_money( $par[2] ) == false )
		{	$message = kohana::lang('charactions.global_notenoughmoney'); return false; }
		
		// Cooldown su cambio regno.			
		$changedkingdom = $par[0] -> get_stats( 'changedkingdom' );
		if ( !is_null( $changedkingdom ) and $changedkingdom[0] -> value > ( time() - self::TIMEBETWEENCHANGES) )	
		 {	$message = kohana::lang('charactions.change_city_timenotexpired'); return false; }
		 
		// check se il personaggio ha un ruolo
		$role = $par[0] -> get_current_role();
		if ( !is_null( $role ) and $role -> get_roletype( ) == 'government' )
			{	$message = kohana::lang('charactions.change_city_charhasarole'); return false; }
		
		
		return true;
	}

	public function execute_action ( $par, &$message) 
	{
		
		// cambia residenza del char e memorizza la data di trasferimento.
		
		$source = ORM::factory('region', $par[0] -> region_id );
		$dest = $par[1];
		
		$par[0]->region_id = $par[1]->id;
		$par[0]->region -> kingdom -> id = $par[1]->kingdom_id;
					
		// memorizzo quando ha cambiato la città, ma anche quando ha cambiato il regno.
		
		$par[0] -> modify_stat( 
			'changedregion', 
			time(), 
			null, 
			null, 
			true );
			
		if ( $source -> kingdom -> id != $dest -> kingdom_id )
			$par[0] -> modify_stat ( 
				'changedkingdom', 
				time(), 
				null, 
				null, 
				true );				
		
		// sposta le strutture locked.
		
		$lockedstructures = ORM::factory('structure') -> where (
			array( 
				'character_id' => $par[0] -> id,
				'locked' => true,
				'region_id' => $source -> id)) -> find_all();
		
		if ($lockedstructures -> count() > 0)
			foreach ( $lockedstructures as $lockedstructure )
			{
				$lockedstructure -> region_id = $dest -> id;
				$lockedstructure -> save();
			}
		
		// togli i soldi.
		
		$par[0] -> modify_location( $par[1]->id );
		
		if ( $par[0] -> is_newbie($par[0]) == false )
			$par[0] -> modify_coins( - $par[2], 'changecity' );
		
		$par[0] -> save();
		
		// aggiungi un evento a chi cambia residenza
		
		Character_Event_Model::addrecord( $par[0]->id, 'normal', '__events.city_change_eventtext;__'.$par[1]->name);
		$message = sprintf( kohana::lang( 'charactions.change_city_ok'),   kohana::lang($par[1]->name));
		
		// evento al Re e al vassallo che perdono un giocatore
				
		$king = $source -> get_roledetails( 'king' );
		$vassal = $source -> get_roledetails( 'vassal' );
		
		if ( !is_null ( $king ) )
		{
							
			Character_Event_Model::addrecord( $king->character_id, 'normal', '__events.city_change_info_source' . 
				';' . $par[0]->name . ';__' . $source-> kingdom -> get_name()   .  ';__'.$source->name  .
		     ';__' . $dest->kingdom -> get_name()   . ';__' . $dest->name,
				'evidence'
				);
		}
		
		if ( !is_null( $vassal) ) 
		{
					
			Character_Event_Model::addrecord( $vassal->character_id, 'normal', '__events.city_change_info_source' . 
				';' . $par[0]->name . ';__' . $source->kingdom -> get_name()   .  ';__'.$source->name  .
		     ';__' . $dest->kingdom -> get_name()   . ';__' . $dest->name,
				'evidence' 
				);
		}
		
		// evento al Re e al vassallo che guadagnano un giocatore
		
		$king = $dest -> get_roledetails( 'king' );
		$vassal = $dest -> get_roledetails( 'vassal' );
		
		if ( !is_null ( $king ) )
		{
							
			Character_Event_Model::addrecord( $king->character_id, 'normal', '__events.city_change_info_dest' . 
				';__'.$dest->kingdom -> get_name()    . ';__' . $dest->name  .  ';' . $par[0]->name . 
				';__' . $source->kingdom -> get_name()  . ';__' . $source->name,
				'evidence' 
				);
		}
		
		if ( !is_null( $vassal) ) 
		{
							
			Character_Event_Model::addrecord( $vassal->character_id, 'normal', '__events.city_change_info_dest' . 
				';__'.$dest->kingdom -> get_name()   .  ';__' . $dest->name .  ';' . $par[0]->name . 
				';__' . $source->kingdom -> get_name()  . ';__' . $source->name,
				'evidence' 
				);
		}
		
		return true;
	}


	
	protected function append_action( $par, &$message ){}

	public function complete_action( $data ){}
	
	public function cancel_action( )	{	}
	
	public function get_action_message( $type = 'long') {	}
	
	
}
