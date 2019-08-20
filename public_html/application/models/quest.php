<?php defined('SYSPATH') OR die('No direct access allowed.');

class Quest_Model
{
	protected $steps;
	protected $stepsnumber;
	protected $name = '';
	protected $strinit = '';
	protected $id = null;
	protected $author_id = null;
	protected $path = '';
	
	function __construct() {		
				
		// inizializza gli steps
		
		kohana::log('debug', '-> Initializing steps...');
		
		for ( $i = 1; $i <= 10; $i++ )
		{		
			$this -> steps[$i] = new stdClass();
			if ( $i <= $this -> stepsnumber )
			{
				
				$this -> steps[$i] -> summary = 
					'quests.' . $this -> name . '_step' . $i . '_summary';
				$this -> steps[$i] -> status = 'uncompleted' ;
			}
			else
			{
				$this -> steps[$i] -> status = 'nonexistent' ;			
			}
			
			$this -> steps[$i] -> id = $i; 
		}
	}
	
	/**
	* Get info about this quest
	* @param int $char_id ID personaggio
	* @return array $info
	*/
	
	function get_info( $char_id )
	{
		$info = array();
		
		$info['id'] = $this -> get_id() ;
		$info['author_id'] = $this -> get_author_id() ;	
		$info['path'] = kohana::lang('quests.' . $this -> get_path() );
		$info['descriptivename'] = kohana::lang('quests.' . $this -> get_name() . '_name' );		
		$info['name'] = $this -> get_name();
		$info['steps'] = $this -> get_steps( $char_id );	
		$info['currentstep'] = $this -> get_currentstep($char_id);		
		$info['description'] = $this -> get_description(); 	
		$info['status'] = $this -> get_status($char_id);	
		$info['rewards'] = $this -> get_rewards();
		$info['started'] = $this -> get_startdate($char_id);
		$info['finished'] = $this -> get_enddate($char_id);
		
		return $info;
		
	}
	
	/**
	* Attiva un quest per un char
	* @param obj $character oggetto char
	* @param str $spare3
	* @param str $spare4
	* @return none
	*/
	
	function activate( $character, &$message, $spare3 = null, $spare4 = null )
	{
		
		if ( $this -> get_status($character) != 'inactive' )
		{
			$message = 'quests.error-questalreadyactiveorcompleted';
			return false;
		}		
		
		// TODO: Controllo che non ci siano altri quest attivi
		
		$activequest = Character_Model::get_active_quest( $character -> id );
		if (!is_null($activequest))
		{
			$message = 'quests.error-thereisalreadyanactivequest';
			return false;
		}		
		
		My_Cache_Model::delete("-activequest-{$character->id}"); 
		
		$message = 'quests.info-activatedok';
		return true;
		
	}	
	
	/*
	* Initialize a quest: makes an entry in character_stat
	* @param obj $character oggetto char
	* @param str $spare3
	* @param str $spare4
	* @return none
	*/
	
	function initialize( $character, $spare3 = null, $spare4 = null )
	{		
	
		$character -> modify_stat( 
			'quest', 
			$this -> id, 
			$this -> get_name(),
			'active', 
			true,			
			null,
			$this -> strinit,
			time(),
			null,
			$spare3,
			$spare4); 	
	
	}
	
	/**
	* ritorna il character id dell' autore
	* @param none
	* @return int $author_id
	*/
	
	function get_author_id () 
	{
		return $this -> author_id ;
	}
	
	/**
	* ritorna il path del quest
	* @param none
	* @return str $path Path del Quest
	*/
	
	function get_path () 
	{
		return $this -> path ;
	}
	
	/**
	* ritorna l' ID del quest
	* @param none
	* @return int $id ID del quest
	*/
	
	function get_id () 
	{
		return $this -> id ;
	}
	
	/**
	* ritorna i premi 
	* @param obj $character oggetto char
	* @return str da tradurre
	*/
	
	function get_rewards()
	{
		return 'quests.rewards_' . $this -> get_name();
	}
	
	/**
	* ritorna il currentstep
	* @param $character oggetto char
	* @return nome
	*/
	
	function get_currentstep( $character_id )
	{
		
		$steps = $this -> get_steps( $character_id );
		
		foreach ( $steps as $step )
			if ( $step -> status == 'uncompleted' )
				return $step ;	
	}
	
	/**
	* ritorna il nome del quest
	* @param none
	* @return nome
	*/
	
	function get_name () 
	{
		return $this -> name ;
	}
	
	/**
	* ritorna quando è stato attivato il test
	* @param none
	* @return nome
	*/
	
	function get_startdate ( $character_id ) 
	{
		$quest = Character_Model::get_stat_d( 
			$character_id ,
			'quest',
			$this -> get_name() );
			
		return $quest -> spare1 ;
	}
	
	/**
	* ritorna quando è stato completato il test
	* @param none
	* @return nome
	*/
	
	function get_enddate ( $character_id ) 
	{
		$quest = Character_Model::get_stat_d( 
			$character_id,
			'quest',
			$this -> get_name() );
			
		return $quest -> spare2 ;
	}
	
	/**
	* ritorna la descrizione del quest
	* @param none
	* @return descrizione
	*/
	
	function get_description() {
		return kohana::lang('quests.' . $this -> get_name() . '_description');
		
	}
	
	
	/**
	* Processa l' evento
	* @param char oggetto char
	* @param event nome evento
	* @param par vettori di parametri
	* @instance istanza del quest (tabella character_stat)
	* @return descrizione
	*/
	
	function process_event( $char, $event, $par, $instance )
	{
		call_user_func_array( array( $this, 'process_event_' . $event ), 
			array( $char, $event, $par, $instance ));		
	}
		
	/**
	* ritorna gli step del quest
	* @param $character oggetto char
	* @return descrizione
	*/
	
	function get_steps ( $character_id ) 
	{
				
		$quest = Character_Model::get_stat_d( 
			$character_id ,
			'quest',
			$this -> name );
			
		if ( $quest -> loaded )
		{
			$stepsarray = str_split( $quest -> stat2, 1 );
			$i = 0;
			
			foreach ( $this -> steps as $step )
			{
				switch ( $stepsarray[$i] )
				{
					case '0': $step -> status = 'uncompleted'; break;
					case '1': $step -> status = 'completed'; break;
					case '-': $step -> status = 'nonexistent'; break;
				}
				$i++;
			}
		}		
		
		return $this -> steps ;
	}
	
	/**
	* Returns the quest status
	* @param obj $character Character_Model
	* @return string $status quest status (inactive, complete, ecc)
	*/
	
	function get_status( $character_id )
	{		
		
		$quest = Character_Model::get_stat_d( 
			$character_id ,
			'quest',
			$this -> get_name() );
		
		if ( !$quest -> loaded )
			return 'inactive';
		
		return $quest -> param2;
			
	}

	/**
	* Completa lo step
	* @param $char oggetto char	
	* @param $instance istanza quest (character_stat)
	* @param $stepnumber numero dello step
	* @return none;
	*/

	
	function complete_step( $char, $instance, $stepnumber )
	{
		kohana::log('debug', '-> Quest: Completing step ' . $stepnumber . '... ' );
		
		// evitiamo di completare più volte lo stesso step
		kohana::log('debug', "-> Quest: Checking if step $stepnumber is already completed...");
		$str = (string) $instance -> stat2;				
		if ( $str[$stepnumber] == 1 )
			return;		
			
		kohana::log('debug', "-> Quest: Checking if previous steps are completed...");
		
		// se i precedenti step non sono completati, non si completa lo step.
		$steps = $this -> get_steps( $char -> id );
		foreach ( $steps as $step )
			if ( $step -> status == 'uncompleted' and ($step -> id - 1)	< $stepnumber ) 
				return;
		
		kohana::log('debug', "-> Quest: Completing step $stepnumber");	
		
		$str[$stepnumber] = 1;
		$instance -> stat2 = $str; 
		$instance -> save();
		
		Character_Event_Model::addrecord(
			$char -> id,
			'normal',
			'__events.queststepcompleted' .
			';__' . 'quests.' . $this -> name . '_name' . 
			';' . ($stepnumber + 1) ); 
		
		kohana::log('debug', "-> Quest: completing quest...");	
		
		$this -> complete_quest( $char, $instance );
	
	}

	
	/**
	* Verifica se il quest è completato. Se è completato
	* @param $char oggetto char	
	* @param $instance istanza quest (character_stat)
	* @return none;
	*/
	
	function complete_quest( $character, $instance )
	{
		kohana::log('debug', '-> Quest: Check if Quest is complete...');
		
		$steps = $this -> get_steps( $character -> id );
		
		$completed = true;
		
		foreach ( $steps as $step )
			if ( $step -> status == 'uncompleted' ) 
				$completed = false;
				
		if ( $completed == true ) {
			kohana::log('debug', '-> Quest: Completed! Finalizing quest...' );
			$this -> finalize_quest( $character, $instance );
			$this -> end_quest( $character, $instance );				
		}
		
		return;
		
	}
	
	public function finalize_quest( $char, $instance ) 
	{}
	
	/**
	* Compie azioni dovute per il completamento quest
	* @param $char oggetto char	
	* @param $instance istanza quest (character_stat)
	* @return none
	*/
	
	function end_quest( $char, $instance ) {
	
		kohana::log('debug', '-> ' . get_class($this) . ': Ending quest.');
		
		Character_Event_Model::addrecord
		(
			$char -> id,
			'normal',
			'__events.questcompleted' .
			';__' . 'quests.' . $this -> name . '_name' . 
			';' . html::anchor( 'character/myquests', kohana::lang('character.missions')),
			'evidence'
		);
		
		
		
		$tutor = ORM::factory('character', $char -> tutor_id );
		if ( $tutor -> loaded )
			Character_Event_Model::addrecord
				(
					$tutor -> id,
					'normal',
					'__events.questcompletedbychar' .
					';' . $char -> name . 
					';__' . 'quests.' . $this -> name . '_name'				
				);	
		
		$king = $char -> region -> get_charinrole('king');
		if (!is_null($king))
			Character_Event_Model::addrecord
			(
				$king -> id,
				'normal',
				'__events.questcompletedbychar' .
				';' . $char -> name . 
				';__' . 'quests.' . $this -> name . '_name'				
			);
		
		$vassal = $char -> region -> get_charinrole('vassal');
		if (!is_null($vassal))
			Character_Event_Model::addrecord
			(
				$vassal -> id,
				'normal',
				'__events.questcompletedbychar' .
				';' . $char -> name . 
				';__' . 'quests.' . $this -> name . '_name'				
			);	
		
		$chancellor = $char -> region -> get_charinrolegdr('chancellor');
		if (!empty($chancellor))
			Character_Event_Model::addrecord
			(
				$chancellor[0] -> id,
				'normal',
				'__events.questcompletedbychar' .
				';' . $char -> name . 
				';__' . 'quests.' . $this -> name . '_name'				
			);	
		
		My_Cache_Model::delete("-activequest-{$char->id}");
		$instance -> param2 = 'completed';
		$instance -> spare2	 = time();
		$instance -> save();
		
		
		
	}

}
