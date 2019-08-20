<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Study_Model extends Character_Action_Model
{
	// Sazietà richiesta per ogni ora di studio
	const DELTA_GLUT_X_HOUR = 2;
	
	// Energia richiesta per ogni ora di studio
	const DELTA_ENERGY_X_HOUR = 5;	
	
	protected $cancel_flag = true;
	protected $immediate_action = false;	
	protected $basetime = 1;  // 1 ora	
	protected $attribute = 'none';  // nessun attributo
	protected $appliedbonuses = array ( 'concentrateandlearn' ); // bonus da applicare	
	protected $price;
	protected $baseprice;
	protected $course;
	protected $supportitem;
	protected $consumerate;
	
	// L'azione richiede che il personaggio indossi
	// un determinato equipaggiamento
	protected $requiresequipment = true;
	
	// Equipaggiamento o vestiario necessario in base al ruolo
	// Consume_rate = percentuale di consumo dell'item
	protected $equipment = array
	(
		'all' => array
		(
			'body' => array
			(
				'items' => array('any'),
				'consume_rate' => 'low'
			),
			'torso' => array
			(
				'items' => array('any'),
				'consume_rate' => 'low'
			),
			'legs' => array
			(
				'items' => array('any'),
				'consume_rate' => 'low'
			),
			'feet' => array
			(
				'items' => array('any'),
				'consume_rate' => 'low'
			),
			'right_hand' => array
			(
				'items' => null,
				'consume_rate' => 'medium',
			),
		),
	);
	
	// @input: array di parametri	
	// par[0] : oggetto char che effettua l' azione
	// par[1] : oggetto structure su cui fare l' azione
	// par[2] : numero di ore di studio
	// par[3] : corso
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function check( $par, &$message )
	{ 
	
		if ($par[1] -> structure_type -> supertype == 'academy' )
			$this -> equipment['all']['right_hand']['items'] = array('writingkit');
		else
			$this -> equipment['all']['right_hand']['items'] = array('woodensword');
		
		$this -> equipment['all']['right_hand']['consume_rate'] = 'medium';					
				
		// Check classe madre (compreso il check_equipment)
	
		if ( ! parent::check( $par, $message ) )					
		{ return FALSE; }		
				
		// Check: il char non ha sufficiente energia
		// Check: il char non ha sufficiente sazietà
		if
		( 
			( $par[0] -> energy < self::DELTA_ENERGY_X_HOUR * $par[2] )
			or ( $par[0] -> glut < self::DELTA_GLUT_X_HOUR * $par[2] )
		)
		{
			$message = kohana::lang('charactions.notenoughenergyglut'); 
			return false;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////
		// verifica la relazione diplomatica
		/////////////////////////////////////////////////////////////////////////////////////
		
		$rd = Diplomacy_Relation_Model::get_diplomacy_relation( $par[1] -> region -> kingdom_id, $par[0] -> region -> kingdom_id );
		
		if ( !is_null( $rd ) and $rd['type'] == 'hostile')
		{						
			$message = kohana::lang('structures_market.error-hostileaccessdenied'); 
			return false;				
		}
		
		$this -> course = CourseFactory_Model::create($par[3]);
		
		// controllo che il char abbia i soldi
		
		$price = $this -> course -> getPricePerHour($par[0], $par[1]);
		
		$this -> price = $price['pricewithtax'] * $par[2];
		$this -> baseprice = $price['price'] * $par[2];
		
		if ( ! $par[0] -> check_money( $this -> price ) )
		{
			$message = kohana::lang('charactions.global_notenoughmoney'); 
			return false;
		}
		
		// controllo se il char ha già troppi skilsl
		if ( 
			$this -> course -> getCoursetype() == 'skill' 
			and
			Skill_Model::get_character_skillcount( $par[0] -> id ) >= 3 			
		)
		{
			$message = kohana::lang('charactions.error-toomanyskillslearned'); 
			return false;		
		}
		
		// controllo che non abbia già masterizzato il corso
		
		if ( 
			$this -> course -> getCoursetype() == 'attribute' 
			and 
			$this -> course -> getLevel($par[0]) > 20 )
		{
			$message = kohana::lang('global.operation_not_allowed'); 
			return false;		
		}
		
		// ci sono sufficienti ore di lezione? 
		// il proprietario della struttura non ha bisogno di ore di lezione.
		
		if ( $this -> course -> getAvailableHours($par[1]) < $par[2] )
		{
			$message = kohana::lang( 'structures.error-hoursnotavailable'); 
			return false;		
		}
		
		
		// il char ha inserito troppe ore?
		
		if ( $par[2] > $this -> course -> getLeftHours($par[0]) )
		{
			$message = kohana::lang( 'structures.error-toomanyhoursstudyinserted'); 
			return false;	
			
		}
		
		// ci sono sufficienti pezzi di carta e/o pupazzi di legno?
		
		if ($par[1] -> structure_type -> supertype == 'academy' )
		{		
			$this -> supportitem = 'paper_piece';
			$this -> consumerate = 100;
		}
		else
		{
			$this -> supportitem = 'wood_dummy';
			$this -> consumerate = 4;
		}
		
		
		return true;
		
	}

	protected function append_action( $par, &$message )	{	
		
		$quantitytoremove = 0;
		
		// toglie soldi dal giocatore
		
		$par[0] -> modify_coins( - $this -> price, 'study' );				
		
		// dai i soldi delle tasse a castello e palazzo reale
		
		$_par[0] = $par[1];
		$_par[1] = $par[0];
		$_par[2] = $this -> price;
		$_par[3] = 'service';
		$_par[4] = null;		
		$_par[5] = $par[3] ;		
		$_par[6] = $this -> baseprice;
		
		$tax = new Tax_Valueadded_Model();
		$net = $tax -> apply( $_par );
		
		// metti il netto nella struttura
		
		$par[1] -> modify_coins( $net, 'study' );		
		$text = '__events.structuregain;' . 
			$net . 
			';' . $par[0] -> name . 
			';' . '__structures.course_' . $_par[5] . '_name' ;
		
		Structure_Event_Model::newadd( $par[1] -> id, $text );		
					
		// evento informativo
		
		Character_Event_Model::addrecord( 
			$par[0] -> id,
			'normal',
			'__events.studystart' . ';' . 
			'__structures.course_' . $par[3] . '_name'
			);
							
		// brucia il materiale di studio
		
		if ($par[1] -> structure_type -> supertype == 'academy' )
		{
			$itemtoremove = Item_Model::factory( null, $this -> supportitem);
			$itemtoremove -> removeitem( 'structure', $par[1] -> id, $par[2]);
		}
		else
			Item_Model::consumeitem_instructure( $this -> supportitem, $par[1] -> id, $this -> consumerate * $par[2] );		
		
		
		$this -> character_id = $par[0] -> id;
		$this -> structure_id = $par[1] -> id;
		$this -> starttime = time();			
		$this -> status = "running";			
		$this -> endtime = $this -> starttime + $this -> get_action_time( $par[0] ) * $par[2];	
		$this -> param1 = $par[2];
		$this -> param2 = $par[3];				
		$this -> save();		

		$message = kohana::lang('structures_' . $par[1] -> structure_type -> supertype . '.study_ok');
		
		return true;
	
	}

	public function complete_action( $data )	
	{
			
		$character = ORM::factory('character', $data -> character_id );		
		$this -> course = CourseFactory_Model::create($data -> param2);
		$studiedhours = $this -> course -> getStudiedHours( $character );
		
		kohana::log('debug', '------ Study -------');
		kohana::log('debug',"-> Character: {$character -> name}");
		kohana::log('debug',"-> Course: {$this->course->getTag()}");
		kohana::log('debug',"-> Studied Hours: {$studiedhours}");
		
		// Consumo degli items/vestiti obbligatori indossati
		
		Item_Model::consume_equipment( $this -> equipment, $character, $data -> param1 );
		
		Character_Event_Model::addrecord( 
			$character -> id,
			'normal',
			'__events.studyfinished' . 
			';' . $data -> param1 . 
			';__structures.course_' . $data -> param2 . '_name'
		);
		
		// Processa evento study
		
		$_par[0] = $data -> param2; 
		GameEvent_Model::process_event( $character, 'study', $_par );	
		
		// Se il corso è completato, si aumenta la caratteristica
		
		if ( $studiedhours + $data -> param1 >= $this -> course -> getNeededHours($character) )		
		{
					
			kohana::log('debug', '-> Ore di studio necessarie: ' . $this -> course -> getNeededHours($character) );
			kohana::log('debug', '-> ' . $character->name . ' ' . $character -> id . '  finished a course level: ' . $data -> param2 );		
			$this -> course -> completeCourse($character);
			
		}		
		else
		{
			kohana::log('debug', "-> Adding {$data -> param1} hours for {$character -> name} to course: {$this -> course -> getTag()}");
			$this -> course -> addStudyHours( $character, $data -> param1 );
		}
		
		// riduci energia e sazietà.
		
		$character -> modify_energy( - self::DELTA_ENERGY_X_HOUR * $data -> param1, false, 'study' );
		$character -> modify_glut( - self::DELTA_GLUT_X_HOUR * $data -> param1 );
		
		$character -> save();
		
		return;		
	
	}
		
	public function execute_action ( $par, &$message ) 	{	}
	
	public function cancel_action() { return true; } 
		
	// Questa funzione costruisce un messaggio da visualizzare 
	// in attesa che la azione sia completata.
	
	public function get_action_message( $type = 'long') 
	{
		$pending_action = $this->get_pending_action();
		$message = "";				
		
		if ( $pending_action -> loaded )
		{
			if ( $type == 'long' )		
			$message = '__regionview.study_longmessage';
			else
			$message = '__regionview.study_shortmessage';
		}
		return $message;
	
	}	
}
