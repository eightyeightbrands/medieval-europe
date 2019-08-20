<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Restrain_Model extends Character_Action_Model
{
	
	protected $cancel_flag = false;
	protected $immediate_action = false;
	
	const RESTRAIN_COOLDOWN = 86400; // 1 giorno
	const MAXRESTRAINPERIOD = 168; 
	
	protected $basetime       = 1;   // 1 ora
	protected $attribute      = 'none';  // attributo forza
	protected $appliedbonuses = array ( 'none' ); // bonuses da applicare
	
	public function __construct()
	{		
		parent::__construct();
		// questa azione non é bloccante per altre azioni del char.
		$this->blocking_flag = false;		
		return $this;
	}
	
	/**
  *	Effettua tutti i controlli relativi al move, sia quelli condivisi
	* con tutte le action che quelli peculiari del dig
	* @param: par
	*  par[0] = char che blocca
	*  par[1] = char da bloccare
	*  par[2] = n. ore
	*  par[3] = motivo
	* @return: TRUE = azione disponibile, FALSE = azione non disponibile
	*
	*/
	
	protected function check( $par, &$message )
	{ 
		$message = "";
		
		//var_dump ($par[3]); exit; 
		
		if ( ! parent::check( $par, $message, $par[0] -> id, $par[1] -> id ) )					
			return false;

		///////////////////////////////////////////////////////////////////////
		// controllo dati
		///////////////////////////////////////////////////////////////////////
		
		if ( ! $par[0] -> loaded )
		{ $message = kohana::lang('global.operation_not_allowed'); return FALSE; }
		
		if ( !$par[1] -> loaded )
		{ $message = kohana::lang('global.error-characterunknown'); return FALSE; }				
		
		///////////////////////////////////////////////////////////////////////		
		// la motivazione è mandatoria.
		///////////////////////////////////////////////////////////////////////
		
		if ( strlen( $par[3] ) == 0 )
		{ $message = kohana::lang('ca_restrain.reasonmandatory' ) ; return FALSE; }
		
		///////////////////////////////////////////////////////////////////////
		// controllo ore di blocco
		///////////////////////////////////////////////////////////////////////

		if ( $par[2] < 1 or $par[2] > self::MAXRESTRAINPERIOD )
		{ $message = kohana::lang('ca_restrain.durationincorrect'); return FALSE; }
		
		///////////////////////////////////////////////////////////////////////		
		// Non è possibile bloccare un char già bloccato		
		///////////////////////////////////////////////////////////////////////
		
		if ( Character_Model::is_restrained( $par[1] -> id ) )
		{ $message = kohana::lang('ca_restrain.alreadyrestrained', $par[1] -> name); return FALSE; }
	
		///////////////////////////////////////////////////////////////////////		
		// non è possibile bloccare sè stessi
		///////////////////////////////////////////////////////////////////////
		
		if ( $par[1]->id == $par[0]->id )
			{ $message = kohana::lang('ca_restrain.selfaction'); return FALSE; }
			
		///////////////////////////////////////////////////////////////////////		
		// non si può bloccare un reggente
		///////////////////////////////////////////////////////////////////////
		
		$role = $par[1] -> get_current_role() ; 
		
		if ( !is_null( $role) and in_array( $role -> tag, array( 'church_level_1', 'king' )  ) )
		{ $message = kohana::lang('ca_restrain.notenoughpower' ); return FALSE; }
		
		///////////////////////////////////////////////////////////////////////		
		// Per bloccare un char, deve essere nel regno
		///////////////////////////////////////////////////////////////////////
		
		if ( ! $par[1] -> is_inkingdom( $par[0] -> region -> kingdom ) )		
		{ $message = kohana::lang('ca_restrain.isnotinkingdom', $par[1] -> name ); return FALSE; }
		
		//////////////////////////////////////////////////////////////////////
		// il giocatore ha gli item nesessari 
		//////////////////////////////////////////////////////////////////////
		
		if ( ! Character_Model::has_item( $par[0]->id, 'paper_piece', 1 ) ) 
		{ $message = kohana::lang('charactions.paperpieceneeded'); return FALSE; }				

		//////////////////////////////////////////////////////////////////////
		// Il regno del giocatore è in guerra con il regno di chi tenta di
		// trattenerlo?
		//////////////////////////////////////////////////////////////////////
		
		$restrainerkingdomrunningwars = Kingdom_Model::get_kingdomwars( $par[0] -> region -> kingdom_id, 'running');
		$restrainedkingdomrunningwars = Kingdom_Model::get_kingdomwars( $par[1] -> region -> kingdom_id, 'running');
		
		if (
			count($restrainerkingdomrunningwars) > 0 
			and  
			count($restrainedkingdomrunningwars) > 0 
			and 
			$restrainerkingdomrunningwars[0]['war'] -> id == $restrainedkingdomrunningwars[0]['war'] -> id
		)
		{ $message = kohana::lang( 'charactions.error-characterisofenemykingdom'); return false;}	
			
		//////////////////////////////////////////////////////////////////////
		// il giocatore è in recovering ma, è nel battlefield?
		//////////////////////////////////////////////////////////////////////
		
		if ( Character_Model::is_recovering( $par[1] -> id ) and Character_Model::is_fighting( $par[1] -> id ) == true )
		{ $message = kohana::lang('ca_restrain.error-charisfighting', $par[1] -> name); return FALSE; }				
		
		//////////////////////////////////////////////////////////////////////
		// Cooldown di 24 ore
		//////////////////////////////////////////////////////////////////////
		
		$stat = Character_Model::get_stat_d( $par[1] -> id, 'lastrestrain', $par[0] -> region -> kingdom_id );
		if ( $stat -> loaded and time() - ( 24 * 3600 ) < $stat -> stat1 )
		{ $message = kohana::lang('ca_restrain.error-restraincooldown', $par[1] -> name); return FALSE; }				
		
		return true;
	
	}


	// Funzione per l'inserimento dell'azione nel DB.
	// Questa funzione appende solo una azione _non la esegue_
	// Al momento non si prevedono azioni istantanee.
	// @input: $par[0] = structure, $par[1] = char
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function append_action( $par, &$message ) 
	{
	
		$paper_piece = Item_Model::factory( null, 'paper_piece' );
		$paper_piece -> removeitem( 'character', $par[0] -> id, 1 );
		
		$this -> character_id = $par[1] -> id;
		$this -> starttime = time();
		$this -> status = "running";			
		$this -> param1 = $par[0] -> region_id;		
		$this -> param2 = $par[0] -> id;
		$this -> param3 = $par[0] -> region -> kingdom_id;
		$this -> param5 = $par[3];
		$this -> endtime = $this -> starttime + $this -> get_action_time( $par[0] ) * $par[2];		
		$this -> save();
		
		// save stat
		
		$par[1] -> modify_stat(
			'lastrestrain',
			0,
			$par[0] -> region -> kingdom_id,
			null,
			null,
			null,
			true );
		
		// event
		
		Character_Event_Model::addrecord( 
			$par[1] -> id,
			'normal', 
			'__events.restrain_targetinfo;' . 
			$par[2] . ';' .
			$par[3],
			'evidence'
		);
		
		$message = kohana::lang('ca_restrain.ok');
		
		return true;
		
	}

	// Esecuzione dell' azione. 
	
	public function complete_action( $data ) {
	
		// Salva la statistica, marca la fine del restrain.
		
		$character = ORM::factory('character', $data -> character_id );
		$character -> modify_stat(
			'lastrestrain',
			0,
			$data -> param3,
			null,
			true,
			time()
			);			
	}
	
	protected function execute_action() {}
	
	public function cancel_action( $data ){}
	
	// Questa funzione costruisce un messaggio da visualizzare 
	// in attesa che la azione sia completata.
	
	public function get_action_message( $type = 'long') 
	{
		$pending_action = $this->get_pending_action();
		$message = "";	
		$target = ORM::factory('character', $pending_action -> param1 );		
		
		
		if ( $pending_action -> loaded )
		{
			if ( $type == 'long' )		
			$message = '__regionview.restrain_longmessage;' . $target->name;
			else
				$message = '__regionview.restrain_shortmessage';
		}
		return $message;
	
	}
	
}
