<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Growbreeding_Model extends Character_Action_Model
{
	// Costanti	
	const CYCLE_TIME = 86400;            // Tempo ciclo (24 ore)
	const GLUT_TO_CONSUME = 20;          // Sazietà da togliere agli animali
	protected $cancel_flag = false;
	protected $immediate_action = false;	
	
	public function __construct()
	{		
		parent::__construct();
		// Questa azione non é bloccante per altre azioni del char.
		$this->blocking_flag = false;
		// Questa azione è ciclica. Questo significa che al termine
		// della stessa non verrà settata come "completed"
		$this->cycle_flag = true;		
		return $this;
	}
			
	// Nessun controllo dato che l' azione è chained dal sistema.
	protected function check( $par, &$error )
	{ 
		return true;
	}

	// Funzione per l'inserimento dell'azione nel DB.
	// Questa funzione appende solo una azione _non la esegue_
	// @input: ALLEVAMENTO
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $errors contiene gli errori in caso di FALSE
	
	protected function append_action( $par, &$error )
	{
		$this->character_id = Session::instance()->get('char_id');
		$this->starttime = time();			
		$this->status = "running";			
		$this->endtime = $this -> starttime + (self::CYCLE_TIME/kohana::config('medeur.serverspeed'));
		$this->param1 = $par->id; // Memorizzo l'id dell'allevamento
		$this->param2 = 0; // Numero di giorni di vita dell'allevamento
		$this->save();								
		return true;
	}


	// Eseguo l'azione semina aggiornando lo status del terreno, memorizzando
	// l'id dell'item che sto seminando sulla struttura e il tempo previsto per
	// il termine della crescita
	
	public function complete_action( $data )
	{
	
		kohana::log('debug', 'processing growbreeding action: ' . $data -> id );
		
		////////////////////////////////////////////////////////////////////////////////////
		// Recupero l'allevamento
		////////////////////////////////////////////////////////////////////////////////////
		
		$breeding = ORM::factory('structure', $data->param1 );
		
		/////////////////////////////////////////////////////////////////////////////////////
		// se c'è una azione di slaughtering, l' azione non deve essere eseguita.
		/////////////////////////////////////////////////////////////////////////////////////
		
		kohana::log('debug', 'id: ' . $data -> param1 . ' days: ' . $data -> param2 . 
			' health: ' . $breeding -> attribute1 ); 
		
		$slaughtering = ORM::factory ( 'character_action' )
			-> where( array ( 'action' => 'butcher', 'param1' => $data -> param1, 'status' => 'running' ) ) -> find(); 
		
		if ( $slaughtering -> loaded )
		{
			kohana::log('info', 'growbreeding check interrupted as a butchering is happening.' ); 			
			return true;
		}
		
		//////////////////////////////////////////////////////////////////////////
		// se il breeding non c'è più...
		//////////////////////////////////////////////////////////////////////////
		
		if ( ! $breeding->loaded )
		{
			kohana::log('info', 'attention! breeding related to ' . $data -> param1 . ' does not exist.' );
			$a = ORM::factory('character_action', $data->id);
			$a->delete();
			return;
		}
		
		//////////////////////////////////////////////////////////////////////////////////
		// Aggiungo un giorno di vita all'allevamento
		///////////////////////////////////////////////////////////////////////////////
		
		$days = $data->param2 + 1;		
		
		/////////////////////////////////////////////////////////////////////////
		// Se ho raggiunto il 21esimo giorno allora distruggo l'allevamento
		// e cancello l' azione
		///////////////////////////////////////////////////////////////////////////////////
		
		if ($days > 20)
		{			
			kohana::log('debug', 'deleting (license expired) breeding: ' . $breeding -> id );
			$breeding -> destroy();			
			kohana::log('debug', 'deleting growbreeding action: ' . $data->id );
			$a = ORM::factory('character_action', $data->id);
			$a -> delete();
			
			// evento
			
			Character_Event_Model::addrecord( 
				$data->character_id,
				'normal',  
				'__events.factorydeleted'						
				);	
				return;
			
		}
		else
		{
			/////////////////////////////////////////////////////////////////////////////
			// Controllo se l'allevamento è mungibile (ogni 3 giorni)
			/////////////////////////////////////////////////////////////////////////////
			
			if ( $days % 3 == 0)
			{ $breeding->attribute3 = true; }
	
			////////////////////////////////////////////////////////////////////////////////
			// Controllo se l'allevamento è macellabile (sono passati almeno 
			// 15 giorni). Se è 2, l' allevamento è stato macellato già, quindi non setto il // flag a 0
			/////////////////////////////////////////////////////////////////////////////
			
			if ( $days >= 15 and $breeding->attribute4 == 0 )
			{ $breeding->attribute4 = true; }						
			
			//////////////////////////////////////////////////////////////////////////////
			// memorizzo i giorni trascorsi sulla struttura
			//////////////////////////////////////////////////////////////////////////////
			$breeding ->attribute5 = $days ;
			
			////////////////////////////////////////////////////////////////////////////////
			// Calcolo l'eventuale morte di bestiame
			////////////////////////////////////////////////////////////////////////////////
			
			$morte = false;
			
			kohana::log( 'debug', 'allevamento ' . $breeding->id . ' stato: ' . $breeding->attribute2 );
			kohana::log( 'debug', 'allevamento ' . $breeding->id . ' death: ' . $morte );			
			
		 if ( $breeding->attribute1 > 0 )
		{	
			if ($breeding->attribute2 > 80 AND $breeding->attribute2 <= 100)			
				$morte = false;
			elseif ($breeding->attribute2 > 60 AND $breeding->attribute2 <= 80)
				$morte = (rand(1,17) == 1) ? true : false ;		
			elseif ($breeding->attribute2 > 40 AND $breeding->attribute2 <= 60)
				$morte = (rand(1,6) == 1) ? true : false ;
			elseif ($breeding->attribute2 > 20 AND $breeding->attribute2 <= 40)
				$morte = (rand(1,3) == 1) ? true : false ;			
			else if ($breeding->attribute2 > 0 and $breeding->attribute2 <= 20)
				$morte = (rand(1,2) == 1) ? true : false ;		
			else
				$morte = true;
			
			kohana::log( 'debug', 'allevamento ' . $breeding->id . ' death dopo calcolo: ' . $morte );
		}
			
		if ($morte)
		{
			/////////////////////////////////////////////////////////////////////////////////	// tolgo dei capi a seconda del tipo di allevamento
			/////////////////////////////////////////////////////////////////////////////////
			
			if ( $breeding->structure_type->type == 'breeding_silkworm' )
				$breeding->attribute1 -= rand( 30, 40 );
			else
			{
				if ( $breeding->structure_type->type == 'breeding_bee' )
				$breeding->attribute1 -= rand( 300, 400 );
				else
				$breeding->attribute1 -= 1;
			}
			
			// evento
			
			Character_Event_Model::addrecord( 
				$data->character_id,
				'normal',  
				'__events.animaldeath'						
				);								
		}
		
		/////////////////////////////////////////////////////////////////////////////////	
		// Togliamo sazietà.			/////////////////////////////////////////////////////////////////////////////////
		
		$breeding->attribute2 -= self::GLUT_TO_CONSUME;
		if ( $breeding -> attribute2 < 0 )
			$breeding -> attribute2 = 0 ; 
			
		//////////////////////////////////////////////////////////////////////////////////	// Salvo i dati dell'allevamento
		////////////////////////////////////////////////////////////////////////////			
				
		$breeding->save();
	
		///////////////////////////////////////////////////////////////////////////////			
		// Imposto il tempo per un nuovo ciclo
		///////////////////////////////////////////////////////////////////////////////
			
		$a = ORM::factory('character_action', $data->id);
		$a -> starttime = time();
		$a -> endtime = $a -> starttime + (self::CYCLE_TIME/kohana::config('medeur.serverspeed'));
		$a -> param1 = $data->param1;
		$a -> param2 = $days;
		$a -> save();
		
		}
	}	

	public function get_action_message( $type = 'long') 
	{		
		return ;
	}
	
}
