<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Giveitem_Model extends Character_Action_Model
{

	protected $immediate_action = true;
	protected $cfgitem = null;

	// Effettua tutti i controlli relativi all' azione, sia quelli condivisi
	// con tutte le action che quelli peculiari 
	// @input: array di parametri
	// par[0]: oggetto character destinatario
	// par[1]: cfgitem
	// par[2]: quantità
	// par[3]: causale
	// par[4]: char che invia
	
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $message contiene il messaggio di ritorno	
	
	protected function check( $par, &$message )
	{ 				
		
		if ( ! $par[0] -> loaded  )
		{
			$message = 'This char does not exist.';
			return false;
		}
		
		$this -> cfgitem = $par[1];
		
		if ( $par[2] < 0 )
		{
			$ownedquantity = Character_Model::get_item_quantity_d( $par[0] -> id, $this -> cfgitem -> tag );
			//var_dump($ownedquantity."-".$par[2]);exit;
			if (abs($ownedquantity) < abs($par[2]))
			{
				$message = "This char has only {$ownedquantity} " . kohana::lang($this -> cfgitem -> name);
				return false;				
			}			
		}

		return true;

	}

	
	// nessun controllo particolare
	protected function append_action( $par, &$message )
	{	}

	public function execute_action ( $par, &$message) 
	{
				
		kohana::log('debug', '-> giving item: ' . $this -> cfgitem -> tag . 'to: ' . $par[0] -> name );
		
		if ( $this -> cfgitem -> tag == 'doubloon' )
		{
			$par[0] -> modify_doubloons( $par[2], $par[3] ); 
		}
		elseif ( $this -> cfgitem -> tag == 'silvercoin' )
		{
			$par[0] -> modify_coins( $par[2], 'adminsend' ); 
		} 
		else
		{			
			$item = Item_Model::factory( null, $this -> cfgitem -> tag );
			
			if ( $par[2] > 0 )
				$item -> additem( 'character', $par[0] -> id, abs($par[2]) ); 
			else
				$item -> removeitem( 'character', $par[0] -> id, abs($par[2]) ); 
		}
		
		$par[0] -> save();
		
		// send event
		
		Character_Event_Model::addrecord( 
			$par[0] -> id, 
			'normal',  
			'__events.admin_item_received'.
			';' . $par[2] . 
			';__' . $this -> cfgitem -> name .
			';' . $par[3]
			);
		
		$message = kohana::lang( 'charactions.giveitems_ok');
	
		return true;

	}
}
