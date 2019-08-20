<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Growfield_Model extends Character_Action_Model
{
	// Costanti	
	protected $cancel_flag = false;
	protected $immediate_action = false;	
	
	public function __construct()
	{		
		parent::__construct();
		// questa azione non é bloccante per altre azioni del char.
		$this->blocking_flag = false;		
		return $this;
	}
			
	// Nessun controllo dato che l' azione è chained dal sistema.
	protected function check( $par, &$message )
	{ 
		return true;
	}

	// Funzione per l'inserimento dell'azione nel DB.
	// Questa funzione appende solo una azione _non la esegue_
	// @input: array di parametri. 	
	// Per l'azione move uso tre parametri
	// $par[0] che rappresenta l'id del campo che sta crescendo
	// $par[1] che rappresente l'id dell'item da seminare
	// $par[2] char id che ha coltivato il campo
	// $par[3] tempo di crescita
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function append_action( $par, &$message )
	{		
	
		$this->character_id = $par[4] -> id; 
		$this->starttime = time();			
		$this->status = "running";			
		$this->endtime = $this->starttime + $par[3];
		$this->param1 = $par[0];
		$this->param2 = $par[1];
		$this->save();								
		
		return true;
	}


	// Eseguo l'azione growfield 
	public function complete_action( $data )
	{
		// Recupero il terreno che sto seminando tramite il primo parametro
		
		$terrain = StructureFactory_Model::create(null, $data -> param1);
		if (!is_null($terrain))
		{// Imposto il terreno come maturo
			$terrain->attribute1 = 2;
			$terrain->save();
		}
                   
	}	

	// Questa funzione costruisce un messaggio da visualizzare 
	// in attesa che la azione sia completata.
	public function get_action_message( $type = 'long') 
	{		
		return ;
	}
	
}
