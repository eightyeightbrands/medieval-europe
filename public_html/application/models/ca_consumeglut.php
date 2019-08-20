<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Consumeglut_Model extends Character_Action_Model
{
	// Costanti	
	const CYCLE_TIME = 86400;            // Tempo ciclo
	const CYCLE_CONSUME = 8;             // Sazietà da consumare
	const STARVING = -10;				 // Punti salute da togliere quando si è affamati
	const FAITHLEVEL_DELTA = -1;         // Punti di fedeltà da consumare
	
	protected $enabledifrestrained = true;
	protected $cancel_flag = false;
	protected $immediate_action = false;	
	
	public function __construct()
	{		
		parent::__construct();
		$this -> blocking_flag = false;		
		$this -> cycle_flag = true;		
		$this -> starttime = time() + self::CYCLE_TIME;
		$this -> endtime = time() +  self::CYCLE_TIME;
		return $this;
	}
			
	// Nessun controllo dato che l' azione è chained dal sistema.
	protected function check( $par, &$error )
	{ 
		return true;
	}

	// Funzione per l'inserimento dell'azione nel DB.
	// Questa funzione appende solo una azione _non la esegue_
	// @input: array di parametri. 	
	// @par[0] $character_id ID personaggio
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $errors contiene gli errori in caso di FALSE
	
	protected function append_action( $par, &$error ) {}	

	public function complete_action( $data )
	{
		
		// Recupero il char
		
		$char = ORM::factory('character', $data -> character_id);
		
		if ( 
			Character_Model::has_merole( $char, 'admin') 
				or
			Character_Model::has_merole( $char, 'staff') 	
				or
			Character_Model::has_merole( $char, 'bot') 	
		)
			return;
		
		if ($char -> user -> status == 'banned' )
			return;
		
		// Aggiorno il char sul db, aggiorno la sazietà e la salute in sessione
		// Se il char ha abbastanza sazietà allora scalo i punti,
		// se la sazietà è bassa o = a 0 allora inizio a scalare anche i punti
		// salute. Se la salute è = 0 allora il pg muore.
		
		// prima di consumare la glut, verifichiamo se sia la glut che la salute sono 
		// a 0. Se il char è in meditazione, o in convalescenza non lo faccio morire.
		// In prigione può morire.
				
		kohana::log('info', '-> Consumeglut: char ' . $char->name . ' health: ' . $char -> health . ', glut: ' . $char -> glut );
		
		if ( 
				( 
					Character_Model::is_meditating( $char -> id ) == false 
					and 
					Character_Model::is_recovering( $char -> id ) == false 					
				) and
			$char -> health < 0 )
		{
			kohana::log('info', '-> Consumeglut: char ' . $char -> name . ' Checking health...' );
			kohana::log('info', '-> Consumeglut: char ' . $char -> name . ' health is <= 0, deleting char...' );
			
			$char -> deletecharfromdb( );	
			return;
		}
		
		//////////////////////////////////////////////////////////////////////////////////
		// prima di consumare la glut, se la glut è già a 0, tolgo 10 punti salute
		//////////////////////////////////////////////////////////////////////////////////
		
		if ( 
				( 
					Character_Model::is_meditating( $char -> id ) == false 
					and 
					Character_Model::is_recovering( $char -> id ) == false 										
				) 
				and $char -> glut <= 0
				and $char -> type != 'npc' 
				)
		{
			kohana::log('debug', '-> Consumeglut: char ' . $char -> name . ' Checking glut for starving situation...' );
			kohana::log('debug', '-> Consumeglut: char ' . $char->name . ' health: ' . $char -> health);
			kohana::log('debug', '-> Consumeglut: char ' . $char->name . ' glut is <= 0, taking away health...' );
			$char -> modify_health ( self::STARVING, false, 'starving' );
			kohana::log('info', '-> Consumeglut: char ' . $char->name . ' health: ' . $char -> health);
			
			// Evento per il char
			Character_Event_Model::addrecord
			( 
				// Char id
				$char -> id,
				// Tipo evento
				'normal',
				// Testo
				'__ca_consumeglut.event_char_starving',
				// Classe
				'normal'
			);	
			
			
			$char -> save();
		}
		
		// se il char sta meditando o è in convalescenza, non consumo
		// il parametro glut				
		
		if (
			$char -> glut > 0 and 
			Character_Model::is_meditating( $char -> id ) == false 
			and 
			Character_Model::is_recovering( $char -> id ) == false 					
			and
			Character_Model::is_imprisoned( $char -> id ) == false 
		)
		{
			kohana::log('debug', '-> Consumeglut: char ' . $char -> name . ' Consuming glut...' );
			kohana::log( 'debug', '-> Consumeglut: char ' . $char -> name . ' glut is now: ' . $char -> glut );
			$char -> modify_glut (-self::CYCLE_CONSUME);
			$char -> save();
			kohana::log('info', '-> Consumeglut: char ' . $char->name . ' glut is now: ' . $char-> glut);
		
		}
				
		// se il giocatore è in prigione, verifico se perde un attributo 
			
		if ( Character_Model::is_imprisoned( $char -> id ) )
		{
			$const = $char -> get_attribute ( 'cost' );
						
			$r = rand(1, 100);
			$chance = max( 0, 30 - $const ); 
			kohana::log('debug', 'char: ' . $char -> name . ' const: ' . $const . ' chance: ' . $chance . ' rand: ' . $r ); 
			if ( $r <= $chance )
			{
				kohana::log('debug', 'taking off a point for a random attribute.' ); 
				 
				while ( 1 )
				{
					$attributes = array ( 'str', 'dex', 'intel', 'cost', 'car' ); 
					$attributekey = array_rand ( $attributes, 1 ); 
					kohana::log('debug', '-> Attribute picked: ' . kohana::debug($attributes[$attributekey] ) ); 
					kohana::log('debug', $char -> get_attribute( $attributes[$attributekey]  ) );
					if ( $char -> get_attribute( $attributes[$attributekey] ) > 1 )
					{
						$char -> set_attribute( $attributes[$attributekey] , -1 ); 
						
						Character_Event_Model::addrecord( 
							$char -> id, 
							'normal',  
							'__events.jail_attributelost' .
							';__'.'character.create_char' . $attributes[$attributekey] ,
							'evidence'
						);
						break; 
					}
				}
			}
		}
		
		// riduco gli skills
			
		kohana::log('info', '-> Reducing skills proficiency...');
		
		$skills = $char -> get_stats( 'skill' );
		
		if (!is_null( $skills))
			foreach ( (array) $skills as $skill )
			{
				$skillinstance = SkillFactory_Model::create ($skill -> param1);
				kohana::log('info', '-> Reducing skill ' . $skillinstance -> getTag() . ' for char: ' . $char -> name );
				if ($skillinstance -> getProficiency( $char -> id ) > 0 )
					$skillinstance -> decreaseproficiency( $char -> id );
			}
			
		// Tolgo una percentuale di faithlevel
		// ai religiosi
		
		if ( !is_null ($char -> church_id) and $char -> church -> name != 'nochurch' )
			$char -> modify_faithlevel( self::FAITHLEVEL_DELTA ); 
			
		// Imposto il tempo per un nuovo ciclo
		
		$a = ORM::factory('character_action', $data -> id );
		$a -> starttime = time() + self::CYCLE_TIME +  ( mt_rand( -2, 2 ) * 3600 );
		$a -> endtime = $a -> starttime;
		$a -> save();
		
		}
	

	// Questa funzione costruisce un messaggio da visualizzare 
	// in attesa che la azione sia completata.
	public function get_action_message( $type = 'long') 
	{		
		return ;
	}
	
}
