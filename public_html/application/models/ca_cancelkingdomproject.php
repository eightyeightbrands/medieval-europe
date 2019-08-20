<?php defined('SYSPATH') OR die('No direct access allowed.');


class CA_Cancelkingdomproject_Model extends Character_Action_Model
{
	
	protected $cancel_flag = false;
	protected $immediate_action = true;
	
	// L'azione richiede che il personaggio indossi
	// un determinato equipaggiamento
	
	protected $requiresequipment = true;
	
	// Equipaggiamento o vestiario necessario in base al ruolo
	
	protected $equipment = array
	(
		'church_level_1' => array
		(
			'head' => array
			(
				'items' => array('mitra_rome','panzva_turnu','hat_cairo','hat_kiev','hat_norse'),
				'consume_rate' => 'verylow',
			),
			'right_hand' => array
			(
				'items' => array('mysticrod_turnu','scepter_kiev','pastoral_rome', 'mysticrod_cairo','scepter_norse'),
				'consume_rate' => 'verylow',
			),
			'body' => array
			(
				'items' => array('tunic_church_level_1_rome', 'tunic_church_level_1_turnu', 'tunic_church_level_1_kiev', 'tunic_church_level_1_cairo','tunic_church_level_1_norse'),
				'consume_rate' => 'verylow',
			),
			'feet' => array
			(
				'items' => array('shoesm_1', 'shoesf_1'),
				'consume_rate' => 'verylow',
			)
		),
		'church_level_2' => array
		(
			'head' => array
			(
				'items' => array('mitra_rome','panzva_turnu','hat_cairo','hat_kiev','hat_norse'),
				'consume_rate' => 'verylow',
			),
			'right_hand' => array
			(
				'items' => array('mysticrod_turnu','scepter_kiev','pastoral_rome', 'mysticrod_cairo','scepter_norse'),
				'consume_rate' => 'verylow',
			),
			'body' => array
			(
				'items' => array('tunic_church_level_2_rome', 'tunic_church_level_2_turnu', 'tunic_church_level_2_kiev', 'tunic_church_level_2_cairo','tunic_church_level_2_norse'),
				'consume_rate' => 'verylow',
			),
			'feet' => array
			(
				'items' => array('shoesm_1', 'shoesf_1'),
				'consume_rate' => 'verylow'
			)		
		),
		'church_level_3' => array
		(
			'head' => array
			(
				'items' => array('mitra_rome','panzva_turnu','hat_cairo','hat_kiev','hat_norse'),
				'consume_rate' => 'verylow'
			),
			'right_hand' => array
			(
				'items' => array('mysticrod_turnu','scepter_kiev','pastoral_rome', 'mysticrod_cairo','scepter_norse'),
				'consume_rate' => 'verylow',
			),
			'body' => array
			(
				'items' => array('tunic_church_level_3_rome', 'tunic_church_level_3_turnu', 'tunic_church_level_3_kiev', 'tunic_church_level_3_cairo','tunic_church_level_3_norse'),
				'consume_rate' => 'verylow',
			),
			'feet' => array
			(
				'items' => array('shoesm_1', 'shoesf_1'),
				'consume_rate' => 'verylow',
			)
		),
		'church_level_4' => array
		(
			'right_hand' => array
			(
				'items' => array('holybook'),
				'consume_rate' => 'verylow',
			),
			'body' => array
			(
				'items' => array('tunic_church_level_4_rome', 'tunic_church_level_4_turnu', 'tunic_church_level_4_kiev', 'tunic_church_level_4_cairo','tunic_church_level_4_norse'),
				'consume_rate' => 'verylow',
			),
			'feet' => array
			(
				'items' => array('shoesm_1', 'shoesf_1'),
				'consume_rate' => 'verylow',
			),
		),
		'all' => array
		(
			'body' => array
			(
				'items' => array('any'),
				'consume_rate' => 'verylow'
			),
			'torso' => array
			(
				'items' => array('any'),
				'consume_rate' => 'verylow'
			),
			'legs' => array
			(
				'items' => array('any'),
				'consume_rate' => 'verylow'
			),
			'feet' => array
			(
				'items' => array('any'),
				'consume_rate' => 'verylow'
			),
		),
	);
	
	
	
	public function __construct()
	{		
		parent::__construct();
		// questa azione non é bloccante per altre azioni del char.
		$this->blocking_flag = false;		
		return $this;
	}
	
	/**
  *	Effettua tutti i controlli relativi al move, sia quelli condivisi
	* con tutte le action che quelli peculiari del dig
	* @param: par
	*  par[0] = character
	*  par[1] = progetto del regno	
	* @return: TRUE = azione disponibile, FALSE = azione non disponibile
	*
	*/
	
	protected function check( $par, &$message )
	{ 
		$message = "";
		
		if ( ! parent::check( $par, $message ) )					
			return false;
		
		// controllo dati
		
		if ( !$par[0] -> loaded or !$par[1] -> loaded )
		{ $message = kohana::lang('global.operation_not_allowed'); return FALSE; }
		
		// controllo se la regione o il kingdom sono corretti
		if ( $par[1] -> region -> kingdom_id != $par[0] -> region -> kingdom_id )
		{ $message = kohana::lang('global.operation_not_allowed'); return FALSE; }
		
		// controllo che non ci sia qualcosa nella struttura
		
		$buildingsite = ORM::factory('structure', $par[1] -> structure_id );
		
		$items = $buildingsite -> get_items();
		if ( count($items) > 0 )
		{ $message = kohana::lang('ca_cancelkingdomproject.error-itempresent'); return FALSE; }
		
		
		return true;
	}


	// Funzione per l'inserimento dell'azione nel DB.
	// Questa funzione appende solo una azione _non la esegue_
	// Al momento non si prevedono azioni istantanee.
	// @input: $par[0] = structure, $par[1] = char
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function append_action( $par, &$message )  {}

	// Esecuzione dell' azione. 
	// Questa funzione viene chiamata quando viene invocata una 
	// complete_expired_action e gestisce le azioni
	// inserite nella character_actions
	// - si caricano i parametri dal database
	// - si esegue l' azione in base ai parametri
	// - si mette l' azione in stato completed
	
	public function complete_action( $data ) 	{	}
	
	protected function execute_action( $par, &$message ) {
	
		// Consumo degli items/vestiti indossati
		Item_Model::consume_equipment( $this->equipment, $par[0] );
		
		// cancella la struttura cantiere
		$structure = StructureFactory_Model::create( null, $par[1] -> structure_id );
		
		if ( $structure -> loaded )
			$structure -> destroy();
		
		// eventi
		
		Character_Event_Model::addrecord( 
			$par[0] -> id,
			'normal',  
			'__events.kingdomprojectcanceled' .
			';' . $par[0] -> name . 
			';__' . $par[1] -> cfgkingdomproject -> name . 
			';' . $par[0] -> name,
			'evidence'
		);		
		
		// cancella il progetto
		
		$par[1] -> delete();
		
		$message = kohana::lang('ca_cancelkingdomproject.info-canceledproject');
		return true; 
	
	}
	
	public function cancel_action( ) {}
	
	
}
