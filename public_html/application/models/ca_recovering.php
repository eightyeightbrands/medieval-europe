	<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Recovering_Model extends Character_Action_Model
{
	// Costanti
	const DELTA_ENERGY = 0;        // Energia necessaria per la semina
	const DELTA_GLUT = 0; 		   // consumo di sazietà	
	const FACTOR = 0.4;            // production value
	const RECOVEREDHEALTH = 30;
	const RECOVEREDENERGY = 8;
	const RECOVEREDHEALTH_DUEL = 70;
	const RECOVEREDENERGY_DUEL = 16;
	
	protected $basetime       = 1;  // 1 sec
	protected $attribute      = 'none';  // attributo forza
	protected $appliedbonuses = array ( 'none' ); // bonuses da applicare
	protected $enabledifrestrained = true;

	
	protected $cancel_flag = false; // se true, la azione è cancellabile dal pg.	
	protected $immediate_action = false;	
	
	// Effettua tutti i controlli relativi al seed, sia quelli condivisi
	// con tutte le action che quelli peculiari del seed
	// @input: array di parametri 
	// par[0] = oggetto char
	// par[1] = costituzione del char
	// par[2] = tipo battaglia
	// par[3] = ha ferite sanguinanti?
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE	
	
	protected function check( $par, &$message )
	{ 
		if ( ! parent::check( $par, $message ) )					
		{ return FALSE; }
		
		return true;
	}

	public function append_action( $par, &$message )
	{ 
	
		// add health before (to avoid accidental deaths)
			
		$par[0] -> modify_health ( 15, true); 
		$par[0] -> save();
		
		$this -> basetime = ( 24 / pow( $par[1], self::FACTOR ) );
		$this -> action = 'recovering';
		$this -> status = 'running';
		$this -> param1 = $par[2];		
		$this -> param2 = $par[3];	
		$this -> starttime = time();
		$this -> character_id = $par[0] -> id;
		$this -> endtime =  $this -> starttime + $this  ->  get_action_time( $par[0] );		
		$this -> save();		
	}
	
	public function cancel_action() { return true; }
	
	public function complete_action( $data )
	{
		
		$character = ORM::factory( 'character', $data -> character_id );		
		$elapsed = $data -> endtime - $data -> starttime  ;	
		$lostattributepoint = false;
		
		if ($data -> param1 == 'duel' )
		{
			$character -> modify_health ( self::RECOVEREDHEALTH_DUEL, true ); 
			$character -> modify_energy ( self::RECOVEREDENERGY_DUEL, true, 'recovering' ); 
		}
		else
		{
			$character -> modify_health ( self::RECOVEREDHEALTH, true); 
			$character -> modify_energy ( self::RECOVEREDENERGY, true, 'recovering' ); 
		}
		
		// only if param2 is true (set by battle type) check for attribute loss.
		
		if ( $data -> param1 != 'duel' )
		{
			
			// roll for attribute removal. Chance goes from 0 to 8%		
			
			$chance = round(
				pow(
					max( Character_Model::get_attributelimit()  - $character -> get_attribute( 'cost' ), 0),0.8
				), 0 
			);
							
			mt_srand();
			$r = mt_rand( 1, 100 );
			
			kohana::log('debug', '-> Chance for attribute removal: ' . $chance . ' roll: ' . $r );
			
			if ( $r <= $chance )	
			{
								
				mt_srand();	
				$r = mt_rand( 1, 5 );
				if ( $r == 1 )
					if ( $character -> get_attribute( 'str' ) > 1 )
					{
						$attributelost = 'str';
						$character -> str-- ;
						$lostattributepoint = true;
					}
				if ( $r == 2 )
					if ( $character -> get_attribute( 'dex' ) > 1 ) {					
						$attributelost = 'dex';
						$character -> dex-- ;
						$lostattributepoint = true;
					}
				if ( $r == 3 )
					if ( $character -> get_attribute( 'int' ) > 1 ) {
						$attributelost = 'int';
						$lostattributepoint = true;
						$character -> int-- ;
					}
				if ( $r == 4 )
					if ( $character -> get_attribute( 'car' ) > 1 ) {
						$attributelost = 'car';
						$lostattributepoint = true;
						$character -> car-- ;
					}
				if ( $r == 5 )
					if ( $character -> get_attribute( 'cost' ) > 1 ) {
						$attributelost = 'cost';
						$lostattributepoint = true;
						$character -> cost-- ;
					}			
			}
			
			if ( $lostattributepoint )
			{
				Character_Event_Model::addrecord( 
					$character -> id, 
					'normal', 
					'__events.lostattributepoint;' . 
					'__character.create_char' . $attributelost, 'evidence'); 
			}
		
			// Lancio per ferite sanguinanti. Le probabilità vanno
			// da 0 a 16% a seconda della costituzione.
			
			if ($data -> param2 == true)			
				$chance = round(
					pow(
						max( Character_Model::get_attributelimit()  - $character -> get_attribute( 'cost' ), 0), 1.4
					), 0 
				);
			else
				$chance = 0;
			
			$r = mt_rand( 1, 100 );
			
			kohana::log('debug', '-> Chance for bleeding wound: ' . $chance . ' roll: ' . $r );
			
			if ( $r <= $chance )
			{
				
				$disease = DiseaseFactory_Model::createDisease('bleedingwound');
				$disease -> injectdisease($character->id);				
			}
		
		}
		
		$character -> save();		
		
	}

	public function get_action_message( $type = 'long') 
	{
		$pending_action = $this->get_pending_action();
		$message = "";
		
		if ( $pending_action -> loaded )
		{
				if ( $type == 'long' )		
					$message = '__regionview.recovering_longmessage';
			else
				$message = '__regionview.recovering_shortmessage';
		}
		
		return $message;
	
	}
}
