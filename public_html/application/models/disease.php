<?php defined('SYSPATH') OR die('No direct access allowed.');

abstract class Disease_Model
{
	
	protected $level;
	protected $name;
	protected $diffusion;
	protected $hpmalus;
	protected $checkinterval;
	protected $strmalus;
	protected $dexmalus;	
	protected $iscurable;  			// è curabile?
	protected $intelmalus;	
	protected $costmalus;
	protected $carmalus;
	protected $iscyclic;					// è ciclica? Se sÃ¬ viene instanziata una azione ciclica
	protected $isblocking;
	protected $timedependent;
	protected $cooldown;         	// Se si ha la malattia meno di $cooldown giorni fa non si puÃ² riprendere
	protected $requireditem;     	// Oggetto richiesto per curare il char (tag)
	protected $timetocure;       	// Tempo base necessario a curare (ore)
	
	/*
	* Applica la malattia
	* @param obj $char Character_Model personaggio a cui la malattia è applicata
	*/
	
	public function apply( $char ) 
	{
		
		kohana::log( 'info', "-> *** Trying to apply disease ({$this -> get_name()}) effects to: {$char -> name}");
		
		if ( Character_Model::is_meditating( $char -> id ) )
		{
			kohana::log( 'info', '-> *** Char is meditating, exiting.' );
			return;
		}
		
		if ( Character_Model::is_recovering( $char -> id ) )
		{
			kohana::log( 'info', '-> *** Char is recovering, exiting.' );
			return;
		}
		
		if ( $char -> type == 'npc' )
		{
			kohana::log( 'info', '-> *** Char is NPC, exiting.' );
			return;
			
		}
		
		if ( Character_Model::is_newbie ( $char ) )
		{
			kohana::log( 'info', '-> *** Char is newbie, exiting.' );
			return;
		}
		
		if ( Character_Model::is_beingcured( $char -> id ) )
		{
			kohana::log( 'info', '-> *** Char is curing or being cured, exiting.' );
			return;
		}
		
		if ( Character_Model::is_imprisoned( $char -> id ) )
		{
			kohana::log( 'info', '-> *** Char is imprisoned, applying ONLY effects.' );
			$this -> apply_effects( $char );
			return;
		}
		
		if ( Character_Model::is_traveling( $char -> id ) )
		{
			kohana::log( 'info', '-> *** Char is traveling, applying ONLY effects.' );
			$this -> apply_effects( $char );
			return;
			
		}				
		
		// apply both effects and infection
		
		$this -> apply_effects( $char );
		$this -> apply_infection( $char );

		return;
			
	}
	
	public function get_strmalus(){	return $this -> strmalus; }
	public function get_dexmalus(){	return $this -> dexmalus; }
	public function get_intelmalus(){ return $this -> intelmalus; }
	public function get_carmalus(){	return $this -> carmalus; }
	public function get_costmalus(){ return $this -> costmalus; }
	public function get_diffusion(){ return $this -> diffusion; }
	public function get_requireditem(){ return $this -> requireditem; }
	public function get_timedependent(){ return $this -> timedependent; }
	public function get_isblocking(){ return $this -> isblocking; }
	public function get_timetocure(){ return $this -> timetocure; }
	public function get_relatedaction(){ return $this -> relatedaction; }
	public function get_checkinterval(){ return $this -> checkinterval; }
	public function get_name(){ return $this -> name; }
	public function get_cooldown(){ return ($this -> cooldown * 24 * 3600); }
	public function get_iscyclic(){	return $this -> iscyclic; }
	public function get_iscurable(){ return $this -> iscurable; }
	
	/*
	** Torna la durata della malattia
	*  
	*  @param obj $char Character_Model
	*  @return int durata della malattia
	*/
	
	public function get_duration( $char )
	{
		//default: 5 anni
		return  3* 24 * 3600;
	}
	/**
	*  cura la malattia
	*  @param obj $char Character_Model
	*  @return none
	*/
	
	public function cure_disease( $char )
	{
	
		kohana::lang('debug', "-> Curing {$char->name} from disease: {$this -> get_name()}");
		
		kohana::lang('debug', "-> Making disease inactive...");
		
		$char -> modify_stat( 
			'disease', 
			0, 
			$this -> get_name(),
			null,
			true,  
			0,
			0,
			'inactive',
			$this -> timedependent,
			time(),
			null,
			null		
		);
		
		kohana::lang('debug', "-> Deleting recurring action...");
		
		kohana::lang('debug', "-> Deleting recurring action for disease [{$this->get_name()}]");
		
		$actions = ORM::factory('character_action') -> 
			where ( array 
				(
					'action' => 'disease', 
					'character_id' => $char -> id,
					'param1' => $this -> get_name()
				)
			) -> find_all();
			
		foreach ($actions as $action )
			$action -> delete();
				
		Character_Event_Model::addrecord(				
			$char -> id, 
			'normal',
			'__events.diseasecured' . ';__' . 'character.disease_' . $this -> name ,
			'evidence' );

	}		
	
	/*
	** Inject disease
	*  
	*  @param int $char_id ID Character
	*  @return none
	*/
	
	public function injectdisease( $char_id )
	{	
	
		$char = ORM::factory('character', $char_id );
	
		kohana::log( 'info', '-> Trying to inject to char ' . $char -> name . ' sickness: ' . $this -> get_name() );
	
		if ( $char -> is_newbie( $char ) )
		{
			kohana::log( 'info', '-> Char ' . $char -> name . ' is newbie.' );
			return;
		}	
	
		if ( $this -> is_active( $char ) )
		{
			kohana::log( 'info', '-> Char ' . $char -> name . ' has already sickness: ' . $this -> get_name());
			return;
		}
		
		if ( $char -> type == 'npc' )
		{
			kohana::log( 'info', '-> *** Char is NPC, exiting.' );
			return;
			
		}
	
		// Inietta la malattia
	
		Character_Model::modify_stat_d( 
			$char -> id,
			'disease', 
			0, 
			$this -> get_name(),
			null,
			true,  
			0,
			0,
			'active',
			$this -> get_timedependent(),			
			time(),
			null,
			null
		);
				
		
		if ( $this -> get_iscyclic() == true )
		{
			
			$action = new Character_Action_Model();
			$action -> character_id = $char -> id;
			$action -> action = 'disease';
			$action -> blocking_flag = false;
			$action -> cycle_flag = true;
			$action -> param1 = $this -> get_name();
			$action -> status = 'running';
			$action -> starttime = $this -> get_nextapplytime();
			$action -> endtime = $action -> starttime;
			$action -> save();
		}
		
		// Se è blocking, fa collassare il char
		
		if ( $this -> get_isblocking() == true )
		{
			$a = new Character_Action_Model();
			$a -> action = $this -> name;
			$a -> character_id = $char -> id; 
			$a -> starttime = time();			
			$a -> status = "running";			
			$a -> blocking_flag = true;
			$a -> endtime = $a -> starttime + $this -> get_duration( $char );
			$a -> save();
						
		}
		
		Character_Event_Model::addrecord( 
			$char -> id,
			'normal',
			'__events.gotdisease_' . $this -> name,
			'evidence'
			);
			
		kohana::log( 'info', '-> Char ' . $char -> name . ' injected with sickness: ' . $this -> get_name() );	

	}
	
	/*
	* torna se la malattia è attiva oppure no
	* @param $char oggetto char
	* @return false | true 
	*/
	
	public function is_active( $char )
	{
	
		$sickness = Character_Model::get_stat_d( 
			$char -> id,
			$this -> name );
		
		if ( $sickness -> loaded and $sickness -> spare1 == 'active' )
			return true;
		else
			return false;
	
	}	
	
	public abstract function apply_effects( $char );
	
	/**
	* Applica un infezione 
	* @param Character_Model $char Personaggio che ha la malattia
	* @return none
	*/
	
	public function apply_infection( $char )
	{	
		
		// Se la malattia non è infettiva... no action
		
		if ($this -> get_diffusion() == 0 )
		{
			kohana::log( 'info', "-> *** Disease: {$this -> get_name()} is not infective, returning.");
			return;
		}
		
		// Trova tutti i char che sono nella stessa regione 
		// di terra, infetta quelli che non sono malati della stessa malattia 
		// o che l' hanno avuta almeno 15 giorni fa.
		
		kohana::log( 'info', "-> *** Trying to infect people... ***");
		
		$charstoinfect = Database::instance() -> query( 
			"
				select c.id from characters c, regions r 
				where c.position_id = r.id
				and   r.type != 'sea' 
				and	  c.position_id = " . $char -> position_id ) -> as_array();
		
		shuffle( $charstoinfect ); 
		
		foreach ( (array) $charstoinfect as $chartoinfect )
		{
			
			$char = ORM::factory('character', $chartoinfect -> id );
			
			kohana::log( 'info', '-> *** Trying to infect ' . $char -> name ); 			
			
			// se è giÃ  infettato non puÃ² essere reinfettato. 
			
			if ( $this -> is_active( $char ) )
			{
				kohana::log( 'info', '-> Char has already sickness: ' . $this -> name );
				continue;
			}
			
			// Controlliamo quando ha preso la malattia
			
			$gotthisdisease = Character_Model::get_stat_d( 			
				$char -> id,
				'disease', 
				$this -> name );			
			
			if ( !is_null( $gotthisdisease ) and $gotthisdisease -> spare1 == 'active' )
			{
				kohana::log( 'info', "-> Char already has the disease, skipping." );
				continue;
			}
			
			// se è stato curato ed ha avuto la malattia non piÃ¹ di cooldown giorni fa
			// giorni fa non puÃ² prenderla ancora
			
			if ( 
				!is_null( $gotthisdisease ) and 
				$gotthisdisease -> spare1 == 'inactive' and	
				$gotthisdisease -> spare3 > ( time() - $this -> get_cooldown() ) )	
			{				
				kohana::log( 'info', "-> Char had this disease less than " . $this -> get_cooldown() . ' days ago, skipping.' );
				continue;
			}
			
			// se è in meditazione non puÃ² essere infettato			
			
			if ( Character_Model::is_meditating( $char -> id ) )
			{
				kohana::log( 'info', '-> Char is meditating, exiting.' );
				continue;
			}
			
			// se ha meno di 30 giorni non puÃ² essere infettato
			
			if ( Character_Model::is_newbie ( $char ) )
			{
				kohana::log( 'info', '-> Char is newbie, exiting.' );
				continue;
			}
			
			// se si sta muovendo non è possibile infettarlo
			
			if ( Character_Model::is_traveling( $char -> id ) )
			{
				kohana::log( 'info', '-> Char is traveling, exiting.' );
				continue;			
			}
			
			// prova ad infettalo
			
			kohana::log( 'info', '-> ' . $char -> name . ' has a chance to be infected.');
			
			$rnd = mt_rand (1, 100);
			kohana::log( 'info', '-> ' . $char -> name . ' has a chance to be infected. Roll: ' . $rnd . 
				' diffusion: ' . $this -> get_diffusion() );
			
			if ( $rnd <= $this -> get_diffusion() )
			{
				kohana::log( 'info', '-> ' . $char -> name . ' infected with: ' . $this -> name );
				
				// installa una statistica 
				
				$this -> injectdisease( $char -> id );
				
			}
		}	
	}
	
	/**
	* Computes next application of disease
	* @param none
	* @return none
	*/
	
	public function get_nextapplytime()
	{
		return ( time() + ( $this -> checkinterval * 3600 ) + ( mt_rand( -2, 2 ) * 3600  ) );			
	}
	
}
