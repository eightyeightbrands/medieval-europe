	<?php defined('SYSPATH') OR die('No direct access allowed.');
class CA_Executeduel_Model extends Character_Action_Model
{
	protected $immediate_action = false;
	protected $duelinstance = null;
	
	// @input: array di parametri (par[0] memorizza l'id dell'oggetto che indosso)
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	// par[0]: risposta (yes/no)	
	// par[1]: oggetto char che riceve la sfida
	// par[2]: oggetto char che lancia la sfida
	
	protected function check( $par, &$message )
	{ 
		
		// controllo dati
		
		if ( !$par[1] -> loaded or 
			 !$par[2] -> loaded )
		{ $message = kohana::lang('global.operation_not_allowed'); return FALSE; }
		
		// controllo che la richiesta non sia scaduta
		$this -> duelinstance = Character_Model::get_stat_d(
			$par[2] -> id, 'launchduel', 
				$par[2] -> id,
				$par[1] -> id );
				
			
		if ( time() > $this -> duelinstance -> stat2 - (24 * 3600) )
		{ $message = kohana::lang('ca_executeduel.error-challengeexpired'); return FALSE; }
		
		// check consistenza duello
		if ( 
			$par[2] -> id != $this -> duelinstance -> param1 or 
			$par[1] -> id != $this -> duelinstance -> param2 )
		{ $message = kohana::lang('global.operation_not_allowed'); return FALSE; }
		
		// check stato duello
		
		if ( $this -> duelinstance -> spare2 != 'pending' )
		{ $message = kohana::lang('ca_executeduel.error-challengealreadyaccepted'); 
			return FALSE; }
		
		return true;
	}

	
	// nessun controllo particolare
	
	protected function append_action( $par, &$message )
	{
	
	
		if ( $par[0] == 'yes' )
		{
			// schedula un duello
			
			$b = new Battle_Model();
			$b -> source_character_id = $par[2] -> id;
			$b -> dest_character_id = $par[1] -> id;
			$b -> source_region_id = $this -> duelinstance -> spare1;	
			$b -> dest_region_id = $this -> duelinstance -> spare1;	
			$b -> type = 'duel';
			$b -> status = 'running';
			$b -> timestamp = time();
			$b -> save();
			
			$br = new Battle_Report_Model();
			$br -> battle_id = $b->id;
			$br -> save();
			
			// appendi l' azione.
			
			$this -> character_id = $par[2]->id;
			$this -> structure_id = null;
			$this -> blocking_flag = false;
			$this -> starttime = $this -> duelinstance -> stat2;
			$this -> endtime = $this -> duelinstance -> stat2;
			$this -> action = 'executeduel';
			$this -> status = "running";
			$this -> param1 = $b -> id;
			$this -> save();
			
			// aggiorna la stat
			
			$this -> duelinstance -> spare2 = 'running' ;
			$this -> duelinstance -> save();
			
			$message = kohana::lang( 'ca_executeduel.info-duelaccepted' );
			
			// evento 
			
			Character_Event_Model::addrecord( 
			$par[2] -> id,
			'normal',
			'__events.duelacceptedsource;' . 
			$par[1] -> name,
			'evidence'
			);
		
			Character_Event_Model::addrecord( 
			$par[1] -> id,
			'normal',
			'__events.duelacceptedtarget;' . $par[2] -> name,
			'evidence'
			);
		
			// evento towncrier
			
			$duellocation = ORM::factory('region', $this -> duelinstance -> spare1 );
			Character_Event_Model::addrecord(	
				null, 
				'announcement', 
				'__events.duelacceptedtowncrier;' . 
				$par[1] -> name . ';' . 
				$par[2] -> name . ';' . 
				Utility_Model::format_datetime($this -> duelinstance -> stat2),
				'evidence');							
			
		}
		else
		{
			$this -> duelinstance -> spare2 = 'completed' ;
			$this -> duelinstance -> save();
			
			// evento 
			
			Character_Event_Model::addrecord( 
			$par[2] -> id,
			'normal',
			'__events.duelrefusedsource;' . $par[1] -> name,
				'evidence'
			);
		
			Character_Event_Model::addrecord( 
			$par[1] -> id,
			'normal',
			'__events.duelrefusedtarget;' . $par[2] -> name,
				'evidence'
			);
			
			$message = kohana::lang( 'ca_executeduel.info-duelrefused' );
		}
		
		return true;
	}

	public function complete_action( $data )
	{
		
		$character = ORM::factory( 'character', $data -> character_id );		
		$par[0] = ORM::factory( 'battle', $data -> param1 );
		$battletype = Battle_TypeFactory_Model::create( $par[0] -> type );				
		$battletype -> run( $par, $report );		
		
		return;
	
	
	}
	
	public function execute_action ( $par, &$message ) 
	{}
	
}
