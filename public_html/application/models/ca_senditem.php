<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Senditem_Model extends Character_Action_Model
{
	// Costanti	
	const DELTA_GLUT = 0;
	const DELTA_ENERGY = 0;

	protected $cancel_flag = false;
	protected $immediate_action = false;
	protected $info = array();
	
	public function __construct()
	{		
		parent::__construct();		
		$this->blocking_flag = false;			
		return $this;
	}
	
	// @input: 
	//	$par[0] = char che invia l'oggetto
	//	$par[1] = char che riceve l'oggetto
	//  $par[2] = quantity spedita
	//  $par[3] = item
	//  $par[4] = (non usato)
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function check( $par, &$message )
	{ 
		if ( ! parent::check( $par, $message, $par[0] -> id, $par[1] -> id ) )					
			{ return false; }
		
		$info = Item_Model::computesenddata( $par[2], $par[3] -> id, $par[0], $par[1] -> name , 'send' );
				
		if ( $info['rc'] == 'NOK' )
		{ $message = $info['message']; return FALSE; }				
	
		$this -> info = $info;
		
		return true;
		
	}


	// Funzione per l'inserimento dell'azione nel DB.
	// Questa funzione appende solo una azione _non la esegue_
	// Al momento non si prevedono azioni istantanee.
	
	protected function append_action( $par, &$message )
	{
	
		///////////////////////////////////////////////////////////		
		// Aggiungiamo la quantità dell' oggetto ad un fantomatico 
		// char -1
		///////////////////////////////////////////////////////////		
		
		$item = $par[3] -> cloneitem();
		$item -> character_id = -1;		
		$item -> quantity = $par[2]; 				
		$sendorder = rand(1,time());
		$item -> sendorder = $sendorder . ';' . $par[1]->id;		
		$item -> save();
				
		// togliamo la quantità di risorse specificata al momento del send
		// se non sono silvercoin, altrimenti è incluso nel costo...

		if ( $par[3] -> cfgitem -> tag == 'silvercoin' )
			$par[0] -> modify_coins( - ( $this -> info['cost'] + $par[2] ), 'senditems' );  
		else
		{
			$par[3] -> removeitem ('character', $par[0] -> id, $par[2]);
			$par[0] -> modify_coins( - $this -> info['cost'], 'senditems' ); 
		}
					
		// salviamo l' azione		
		
		$this -> action = 'senditem';
		$this -> character_id = $par[0]->id;
		$this -> status = 'running';
		$this -> starttime = time();
		
		kohana::log('debug', 'ca_senditem: append sender: ' . $par[0]->id . ' receiver: ' . $par[1]->id . ' time: ' .  Utility_Model::format_datetime($this -> info['time']) );		
		
		$this -> endtime = $this -> info['time'] ;					
		$this -> param1 = $par[2]; // quantity		
		$this -> param2 = $par[3]->cfgitem->id; // item type
		$this -> param3 = $sendorder; // target char
		$this -> param4 = $par[1]->id; // target char		
		$this -> save();		
				
		$message = kohana::lang('charactions.senditem_ok');
		
		// manda evento a chi invia l' item
		
		Character_Event_Model::addrecord(
			$par[0]->id, 
			'normal', 
			'__events.itemsent_event'.
			';' . $par[2].
			';__' . $par[3] -> cfgitem -> name .
			';' . $par[1]-> name .
			';' . Utility_Model::format_datetime( $this -> endtime )
		);
		
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
		$receiver = ORM::factory('character', $data -> param4 ); 		
		
		// trova l' item inviato tramite il sendorder
		
		$item = ORM::factory('item') -> 
			where ( 
				array( 
					'sendorder' => $data -> param3 . ';' . $data -> param4,
					'character_id' => -1
					) ) -> find(); 
		
		// verifichiamo se il char esiste...
		// se il char nel frattempo muore, l' item è perso per sempre
		
		if ( $item -> loaded )
		{
			if ( $receiver -> loaded )
			{

				// manda evento a chi riceve l' item
				
				$sender = ORM::factory('character', $data -> character_id);						
				Character_Event_Model::addrecord( 
					$data -> param4, 
					'normal', 
					'__events.itemreceived_event'.
					';' . $sender->name.
					';' . $data->param1.
					';__'.$item->cfgitem->name
					);
				
				$item -> additem( 'character', $receiver -> id, $item -> quantity ); 
				
				// evento per quest
				$par[0] = $item;
				$par[1] = $sender;				
				$par[2] = $receiver;				
				GameEvent_Model::process_event( $receiver, 'receivedquesttoken', $par );
				
				$item -> destroy();
				
			}
			else
				$item -> destroy();
		}
		
		return true; 
	}
	
	protected function execute_action() {}
	
	public function cancel_action( )
	{	return true; }
	
	// Questa funzione costruisce un messaggio da visualizzare 
	// in attesa che la azione sia completata.
	public function get_action_message( $type = 'long') 
	{
		return;
		$pending_action = $this->get_pending_action();
		$message = "";				
		
		if ( $pending_action -> loaded )
		{
			if ( $type == 'long' )		
			$message = kohana::lang('charactions.senditem_longmessage');
			else
			$message = kohana::lang('charactions.senditem_shortmessage');
		}
		return $message;
	
	}

	
}
