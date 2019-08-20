<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Resttavern_Model extends Character_Action_Model
{
	
	protected $cancel_flag = true;
	protected $immediate_action = false;
	protected $basetime       = 1;  
	protected $attribute      = 'none';  // attributo forza
	protected $appliedbonuses = array ( 'none' ); // bonuses da applicare
	protected $baseprice = 0;
	protected $totalprice = 0;
	protected $enabledifrestrained = true;
	protected $percentage = 0;
	
	// Effettua tutti i controlli relativi alla rest tavern, sia quelli condivisi
	// con tutte le action che quelli peculiari del move
	// @input: par[0] = char, 
	// par[1] = hours
	// par[2] = structure
	// par[3] = se true, il rest è free.
	// par[4] = prezzo pagato per punto percentuale
	// par[5] = percentuale da recuperare
	// par[6] = prezzo base
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function check( $par, &$message )	
	{
		
		$this -> percentage = max(2, $par[5]);		
		
		kohana::log('debug', "-> I have to recuperate up to {$this->percentage}%. Price is {$par[4]} * %");
		
		if ( ! parent::check( $par, $message, $par[0] -> id ) ) return false;
		
		// controllo dati
		if ( !$par[0] -> loaded or !$par[2] -> loaded )
		{
			$message = kohana::lang('global.operation_not_allowed');
			return false; }
		
		
		if ( $par[0]->energy/50 * 100 >= 100 )
		{
			$message = kohana::lang('ca_rest.noneedtorest');
			return false;		
		}
		
		if ( $par[0]->energy/50 * 100 >= $this -> percentage )
		{
			$message = kohana::lang('ca_resttavern.error-insertgreaterpercentage',
				$par[0]->energy/50 * 100 );
			return false; 			
		}		
		
		// Check, l' utente deve avere i soldi necessari
		
		if ( $par[3] == true )
		{
			$this -> baseprice = 0;
			$this -> totalprice = 0;
		}
		else
		{
			$this -> baseprice = $par[6] * ( $this -> percentage/2 - $par[0] -> energy ); 
			$this -> totalprice = $par[4] * ( $this -> percentage/2 - $par[0] -> energy ); 
		}		
		
		if ( $par[0] -> check_money( $this -> totalprice ) == false )
		{
			$message = kohana::lang('charactions.global_notenoughmoney'); 
			return false; 
		}
		
		/////////////////////////////////////////////////////////////////////////////////////
		// verifica la relazione diplomatica
		/////////////////////////////////////////////////////////////////////////////////////
		
		kohana::log('info', 'Checking diplomacy relationsships...');
		
		$rd = Diplomacy_Relation_Model::get_diplomacy_relation( $par[2] -> region -> kingdom_id, $par[0] -> region -> kingdom_id );
		
		if ( !is_null( $rd ) and $rd['type'] == 'hostile')
		{		
			$message = kohana::lang('structures_market.error-hostileaccessdenied');
			return false;				
		}

		// check: se la sazietà è a 0, il char non puo' riposare
		
		kohana::log('info', 'Checking if glut is 0...');
		
		if ( $par[0] -> glut == 0 )
		{
			$message = kohana::lang('ca_rest.cantrestifhungry');
			return false;		
		}
		
		return true;
		
	}
	
	protected function append_action( $par, &$message )
	{
		
		kohana::log('info', '-> appending action...');
		
		// se il prezzo non è free, applica tasse
		
		if ( $par[3] == false )
		{			
			
			// toglie soldi al char
			$par[0] -> modify_coins( - $this -> totalprice, 'tavernincome' );		
		
			// Incasso al castello: importo + good and service tax secondo ripartizione
			// Incasso al palazzo reale: good and service tax secondo ripartizione			
			// distribuisci tasse
			
			
			$_par[0] = $par[2]; // struttura
			$_par[1] = $par[0]; // char acquirente
			$_par[2] = $this -> totalprice;
			$_par[3] = 'service';
			$_par[4] = null;
			$_par[5] = 'tavernincome';
			$_par[6] = $this -> baseprice ;
			
			$net = Tax_ValueAdded_Model::apply( $_par );
						
			// da l' importo al castello

			$castle = $par[2] -> region -> get_controllingcastle();
			$s = ORM::factory( 'structure', $castle -> id );
			$s -> modify_coins( $this -> baseprice );
			
		}
		
		// Appendo l'azione di rest				

		$this -> character_id = $par[0]->id;
		$this -> starttime = time();			
		$this -> status = 'running';
		$this -> action = 'resttavern'; 
		
		// Tempo di rest: restfactor * numero di punti da recuperare.
			
		$rf = $par[0] -> get_restfactor( $par[2], $par[3], false ) ;
		
		$this -> param1 = $rf['restfactor'];
		$this -> param2 = $par[3];
		// restfactor thinks in points, that is % / 2.
		$this -> param3 = $this -> percentage / 2;

		// time to rest = percentuale di arrivo - energia corrente!
		$timetorest = ( ($this -> percentage/2) - $par[0] -> energy ) / $rf['restfactor'] * 3600;
		$this -> endtime = $this -> starttime + $timetorest;
		$this -> save();
			
		Character_Event_Model::addrecord( $par[0] -> id, 'normal',
			'__events.resttavernstartnew' . 
			';' . $this -> totalprice . 
			';' . Utility_Model::secs2hmstostring( $timetorest, 'hours' ) .
			';' . ($par[5])
		) ;
		
		$message = kohana::lang('ca_resttavern.rest_ok');
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
		
		$character = ORM::factory("character", $data -> character_id);		
					
		Character_Event_Model::addrecord( 
			$character -> id , 
			'normal',
			'__events.resttaverncomplete' .
			';' . ( $data -> param3 ) ) ;
		
		$character -> modify_energy( $data -> param3, true, 'resting' );
		$character -> save();	
		
		// evento per quest		
		$_par[0] = $data -> param3; // free		
		
		GameEvent_Model::process_event( $character, 'resttavern', $_par );	
	
	}
	
	protected function execute_action() {}
	
	public function cancel_action( )
	{	
	
		// Calcola l' energia da ridare in funzione del tempo riposato		
		// Energia = Fattore di riposo memorizzato quando si è iniziata 
		// l' azione * frazioni di ore trascorse
		
		
		$energypoints = round( ( time() - $this -> starttime ) / 3600  * $this -> param1, 0 );
		$character = ORM::factory("character", Session::instance() -> get('char_id'));
		
		Character_Event_Model::addrecord( $character -> id, 
			'normal', 
			'__events.restcancel'
		);			
		
		$character -> modify_energy( $energypoints, false, 'resting' );						
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
				$message = '__regionview.resttavern_longmessage';				
			else
				$message = '__regionview.resttavern_shortmessage';		
		}
		
		return $message;
	
	}
	
}
