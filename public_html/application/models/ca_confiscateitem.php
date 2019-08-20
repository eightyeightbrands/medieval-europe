<?php defined('SYSPATH') OR die('No direct access allowed.');


class CA_Confiscateitem_Model extends Character_Action_Model
{
	
	protected $cancel_flag = false;
	protected $immediate_action = true;
	
	/**
  *	Effettua tutti i controlli relativi al move, sia quelli condivisi
	* con tutte le action che quelli peculiari del dig
	* @param: par
	*  par[0] = char che confisca
	*  par[1] = char che avrà un item confiscato
	*  par[2] = item da confiscare	
	*  par[3] = quantità da confiscare
	*  par[4] = motivo confisca
	* @return: TRUE = azione disponibile, FALSE = azione non disponibile
	*
	*/
	
	protected function check( $par, &$message )
	{ 
		$message = "";
		
		if ( ! parent::check( $par, $message ) )					
			return false;
			
		// controllo dati
		
		if ( 
			! $par[0] -> loaded or 
			! $par[1] -> loaded or 
			! $par[2] -> loaded )
		{ $message = kohana::lang('global.operation_not_allowed'); return FALSE; }

		// non è possibile confiscare oggetti di un char canceled, nè di un admin.
		
		if ( $par[1] -> user -> status == 'canceled' or  $par[1] -> user -> status == 'suspended' )
		{ $message = kohana::lang('ca_confiscateitem.error-useriscanceled'); return FALSE; }	
		
		if ( Character_Model::has_merole( $par[1], 'admin' ) )
		{ $message = kohana::lang('global.operation_not_allowed'); return FALSE; }	
	
		// check quantità
		
		if ( $par[3] < 1 or $par[3] > $par[2] -> quantity )
		{ $message = kohana::lang('ca_confiscateitem.error-wrongquantity'); return FALSE; }		
		
		// non è possibile confiscare alcuni oggetti
		
		if ( ! $par[2] -> cfgitem -> confiscable )
		{ $message = kohana::lang('ca_confiscateitem.confiscate_nonconfiscableitem'); return FALSE; }		
		
		// Il char che confisca deve essere sheriff del regno		
		
		$role = $par[0] -> get_current_role();		
		
		if ( is_null( $role ) or $role -> tag != 'sheriff' or $par[2] -> structure -> region -> kingdom -> id != $par[0] -> region -> kingdom -> id ) 
		{ $message = kohana::lang('global.operation_not_allowed'); return FALSE; }
					
		// nelle caserme ci deve essere abbastanza spazio.		
	
		$prison = $par[0] -> region -> get_structure( 'barracks' ); 
		
		$itemsweight = $par[2] -> get_totalweight( $par[3] ); 
		$storableweight = $prison ->  get_storableweight( $prison );
		
		if ( $storableweight < $itemsweight )
		{ $message = kohana::lang('charactions.drop_storablecapacityfinished'); return false;	}
		
		////////////////////////////////////////////////////////////////////////
		// Gli oggetti del Re non possono essere confiscati
		////////////////////////////////////////////////////////////////////////
		
		$king =  $par[0]->region -> get_roledetails( 'king' ); 
		
		//kohana::log('debug', kohana::debug( $king ) ); exit(); 
		
		if ( !is_null( $king) and $par[1] -> id == $king -> character_id )
		{ $message = kohana::lang('ca_confiscateitem.confiscate_kingowneditem'); return FALSE; }		
		
		return true;
	}

	// Funzione per l'inserimento dell'azione nel DB.
	// Questa funzione appende solo una azione _non la esegue_
	// Al momento non si prevedono azioni istantanee.
	// @input: $par[0] = structure, $par[1] = char
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function append_action( $par, &$message )  {}

	// Esecuzione dell' azione. 
	// Questa funzione viene chiamata quando viene invocata una 
	// complete_expired_action e gestisce le azioni
	// inserite nella character_actions
	// - si caricano i parametri dal database
	// - si esegue l' azione in base ai parametri
	// - si mette l' azione in stato completed
	
	public function complete_action( $data ) 	{	}
	
	protected function execute_action( $par, &$message ) {
			
		// evento al char a cui è stato confiscato l' item				
		Character_Event_Model::addrecord(
			$par[1] -> id, 
			'normal',
			'__events.confiscateditem_targetinfo' . 
			';' . $par[0] -> name .
			';' . $par[3] .
			';__' . $par[2] -> cfgitem -> name .
			';' . $par[4],
			'evidence'
			);
		
		// trova la prigione dello sceriffo che confisca
		
		$prison = $par[0] -> region -> get_structure( 'barracks' ); 
		
		// traccia evento nella struttura.		
		Structure_Event_Model::newadd( 
			$prison -> id, 		
			'__events.confiscateditem_sourceinfo' . 
			';' . $par[1] -> name .
			';' . $par[3] .
			';__' . $par[2] -> cfgitem -> name .
			';' . $par[4]		
			);		
		
		
		// evento per lo sceriffo		
		
		Character_Event_Model::addrecord(
			$par[0] -> id,
			'normal',
			'__events.confiscateditem_sourceinfo' . 
			';' . $par[1]->name .
			';' . $par[3] .
			';__' . $par[2] -> cfgitem -> name .
			';' . $par[4],	
			'evidence'
			);
		
		$market = ORM::factory('structure', $par[2] -> structure_id );
		$par[2] -> removeitem( 'structure', $market -> id, $par[3]);
		$par[2] -> character_id = null;
		$par[2] -> seller_id = null;
		$par[2] -> additem( 'structure',    $prison -> id, $par[3]);
		
		$message = kohana::lang('ca_confiscateitem.confiscateitem-ok'); 
		
		return true; 
	
	}
	
	public function cancel_action( ) {}
		
	
}
