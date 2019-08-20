<?php defined('SYSPATH') OR die('No direct access allowed.');
class CA_Launchduel_Model extends Character_Action_Model
{
	protected $immediate_action = true;
	protected $dueltime = null;
	protected $duellocation = null;
	
	// @input: array di parametri (par[0] memorizza l'id dell'oggetto che indosso)
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	// par[0]: oggetto char che lancia il duel
	// par[1]: oggetto char che riceve la sfida
	// par[2]: data del duello
	// par[3]: ora del duello
	// par[4]: oggetto regione
	
	protected function check( $par, &$message )
	{ 
		
		// controllo dati
		
		if ( !$par[0] -> loaded or 
			 !$par[1] -> loaded or
			 $par[0] -> id == $par[1] -> id )
		{ $message = kohana::lang('global.operation_not_allowed'); return FALSE; }
		
		// check items necessari
		
		if ( 
			! Character_Model::has_item( $par[0]->id, 'paper_piece', 1 ) or 
			! Character_Model::has_item( $par[0]->id, 'waxseal', 1 ) ) 
		{ $message = kohana::lang('charactions.paperpieceandwaxsealneeded'); return FALSE; }				
		
		// check età sfidato
		if ( $par[1] -> get_age() <  kohana::config('medeur.mindaystofight', 30)  )
		{ $message = kohana::lang('ca_launchduel.error-targetistooyoung', 
			$par[1] -> name ); return FALSE; }				
		
		// check data: non nel passato, almeno fra 4 giorni.				
		// location is mandatory, not in sea.
		
		if ( 
			$par[4] -> loaded == false or
			$par[4] -> type == 'sea' )
		{ $message = kohana::lang('ca_launchduel.error-duellocation-incorrect'); return FALSE; }				
		
		// La data del duello deve essere almeno fra 48 ore.
		
		$completedate = $par[2] . ' ' . $par[3];
		$converteddate = date_parse_from_format("Y-m-d H:i", $completedate);
		if ( $converteddate['error_count'] > 0 )
		{ $message = kohana::lang('ca_launchduel.error-dueldatetime-incorrect'); return FALSE; }				
		
		$this -> dueltime = mktime( 
			$converteddate['hour'], 
			$converteddate['minute'], 
			$converteddate['second'], 			
			$converteddate['month'], 
			$converteddate['day'], 
			$converteddate['year']);
		
		//var_dump( 'time: ' . date("d-m-Y H:i:s", time() + (48 * 3600 )) . ' duel: ' . date("d-m-Y H:i:s", $this -> dueltime )); exit;
		
		if ( time() + (48 * 3600 ) > $this -> dueltime )
		{ $message = kohana::lang('ca_launchduel.error-dueltime-tooearly'); return FALSE; }								
		// cooldown: almeno 7 giorni devono passare da una sfida all'altra
		
		$lastduel = Character_Model::get_stat_d( 
			$par[0] -> id, 'launchduel', $par[0] -> id, $par[1] -> id );
			
		
		if ( $lastduel -> loaded and ( time() - ( 7 * 24 * 3600 ) < $lastduel -> stat1 ) )
		{ $message = kohana::lang('ca_launchduel.error-duelcooldown'); return FALSE; }		
				
		return true;
	}

	
	// nessun controllo particolare
	protected function append_action( $par, &$message )
	{}

	public function complete_action( $data)
	{}
	
	public function execute_action ( $par, &$message ) 
	{
		
		$paper_piece = Item_Model::factory( null, 'paper_piece' );
		$paper_piece -> removeitem( "character", $par[0]->id, 1 );
		
		$waxseal = Item_Model::factory( null, 'waxseal' );
		$waxseal -> removeitem( "character", $par[0]->id, 1 );
		
		// salva stat
		
		$par[0] -> modify_stat(			
			'launchduel',
			0,
			$par[0] -> id,
			$par[1] -> id,
			true,
			time(),
			$this -> dueltime,
			$par[4] -> id,
			'pending',
			null,
			null		
		);
	
		// eventi
		
		Character_Event_Model::addrecord( 
			$par[1] -> id,
			'normal',
			'__events.launchdueltarget;' . $par[0] -> name . ';__' . 
				$par[4] -> name . ';' . 
				Utility_Model::format_datetime($this -> dueltime) . ';' . 
				Utility_Model::format_datetime($this -> dueltime - ( 24 * 3600 ) ) . ';' . 
				url::base(true). 'character/confirmduel/yes/'. $par[1] -> id . '/' . $par[0] -> id . ';' . 
				url::base(true). 'character/confirmduel/no/'. $par[1] -> id . '/' . $par[0] -> id,
				'evidence'
			);
		
		Character_Event_Model::addrecord( 
			$par[0] -> id,
			'normal',
			'__events.launchduelsource;' . $par[1] -> name . ';__' . 
				$par[4] -> name . ';' . 
				Utility_Model::format_datetime($this -> dueltime),
				'evidence'
			);
		
		$message = kohana::lang( 'ca_launchduel.info-duellaunched', $par[1] -> name );
		return true;
	}
}
