<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Rest_Model extends Character_Action_Model
{

	protected $cancel_flag = true;
	protected $immediate_action = false;
	protected $enabledifrestrained = true;

	// Effettua tutti i controlli relativi al move, sia quelli condivisi
	// con tutte le action che quelli peculiari del move
	// @input: array di parametri	
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	// $messages contiene gli errori in caso di FALSE
	// @par[0] oggetto char
	// @par[1] oggetto struttura
	// @par[2] flag se si riposa sul cart3.
	
		
	protected function check( $par, &$message )
	{ 
		$message = "";
		$relationtype = "";
		if ( ! parent::check( $par, $message, $par[0] -> id ) )					
			return false;		
		
		if ( $par[0]->glut == 0 )
		{$message = 'ca_rest.cantrestifhungry';return false;}
		
		// se il char è riposato, warning
		
		if ( $par[0] -> energy == 50 )
		{
			$message = 'ca_rest.noneedtorest';
			return false;		
		}
		
		// solo una persona può riposare nella struttura
		
		if ( $par[2] == false ) 
		{
			$playerssleepingcount = 0;
			// find players sleeping in structure.
			$playerssleeping = ORM::factory('character_action') -> where ( 
			array ( 
					'action' => 'rest',					
					'structure_id' => $par[1] -> id,
					'status' => 'running' ) ) -> find_all();
		
			foreach ( $playerssleeping as $playersleeping)
			{
				kohana::log('debug', "-> Char {$playersleeping->character_id} is sleeping in structure: {$par[1]->id}.");
				
				// don't add to counter if player trying to sleep is married to the one that is sleeping.
				if ( 
						Character_Model::is_marriedto( $par[0] -> id,
						$playersleeping -> character_id, $relationtype ) == true
				)
					;
				else
				{
					kohana::log('debug', "-> Incrementing sleeping persons counter.");
					$playerssleepingcount++;
					
				}
			}
		
			if ( $playerssleepingcount > 0 )
			{$message = 'ca_rest.error-cantrestbedisnotfree';return false;}
			// check: se la sazietà è a 0, il char non puo' riposare
		}


		return true;
	}
	
	protected function append_action( $par, &$message )
	{
		$this -> character_id = $par[0] -> id;
		if ( $par[2] == false )
			$this -> structure_id = $par[1] -> id;
		$this -> starttime = time();			
		$this -> status = "running";
		
		$info = $par[0] -> get_restfactor( $par[1], false, $par[2] );		
		$this -> param1 = $info['restfactor'];
		$this -> param2 = $par[2];
		
		if ( $par[2] == false )
			$this -> param3 = $par[1] -> id;
		
		if ( $par[2] == true )		
			Character_Event_Model::addrecord( 
			$par[0] -> id, 
			'normal', 
			'__events.reststart' . 
			';__' . 'items.cart_3_name' .
			';' . ( $info['restfactor'] ));
		else
			Character_Event_Model::addrecord( 
			$par[0] -> id, 
			'normal', 
			'__events.reststart' . 
			';__' . $par[1] -> structure_type -> name .
			';' . ( $info['restfactor'] ));		
		
		$this -> endtime = $this -> starttime + $info['timeforfullenergy'] ;
		$this -> save();
		$message = 'ca_resttavern.rest_ok';
		return true;
	}

	// Esecuzione dell' azione. 
	// Questa funzione viene chiamata quando viene invocata una 
	// complete_expired_action e gestisce le azioni
	// inserite nella character_actions
	// - si caricano i parametri dal database
	// - si esegue l' azione in base ai parametri
	// - si mette l' azione in stato completed
	
	public function complete_action( $data )
	{
	
		///////////////////////////////////////////////
		// calcola l' energia da ridare in funzione del 
		// tempo riposato. 
		//////////////////////////////////////////////
		
		$character = ORM::factory( 'character', $data -> character_id );		
		$character -> modify_energy( + 50, false, 'resting' );		
		
		Character_Event_Model::addrecord( 
			$character -> id, 
			'normal', 
			'__events.restcomplete' );			

		$character -> save();	
	
	}
	
	protected function execute_action() {}
	
	public function cancel_action( )
	{	

		$character = ORM::factory("character", Session::instance()->get('char_id'));
		
		// calcola l' energia da ridare in funzione del tempo riposato
		// energia = Fattore di riposo memorizzato quando si è iniziata 
		// l' azione * frazioni di ore trascorse
		
		$restedhours = (time() - $this -> starttime ) / 3600 ;				
		$energy = round( $restedhours * $this -> param1, 0);
		
		kohana::log('debug', 'Character started resting on: ' . Utility_Model::format_datetime($this -> starttime) . ', Ended resting on ' . 		Utility_Model::format_datetime (time()) );
		kohana::log('debug', 'Current Energy: ' . $character -> energy. ' - Rested hours: ' . $restedhours . ' - rf: '.$this->param1.' rec energy: '.$energy);
				
		Character_Event_Model::addrecord( 
			$character -> id, 
			'normal',
			'__events.restcancel') ;		
		
		$character -> modify_energy( $energy, false, 'resting' );				
		$character -> save();		
		
		return true; 
		
	}
	
	// Questa funzione costruisce un messaggio da visualizzare 
	// in attesa che la azione sia completata.
	
	public function get_action_message( $type = 'long') 
	{
		
		$pending_action = $this->get_pending_action();
		$message = "";
		
		if ( $pending_action -> loaded )
		{
				
			$now = date("F d, Y H:i:s", time() );
			if ( $type == 'long' )		
				$message = '__regionview.rest_longmessage';
			else
				$message = '__regionview.rest_shortmessage';	
		
		}
		
		return $message;
	
	}
	
}
?>
