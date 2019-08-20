<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Revokerole_Model extends Character_Action_Model
{

	protected $immediate_action = true;
	protected $required_fp = 0;
	protected $required_sc = 0;

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
			)		),
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
	
	
	// Effettua tutti i controlli relativi all' azione, sia quelli condivisi
	// con tutte le action che quelli peculiari 
	// @input: array di parametri
	// par[0]: oggetto char del revocatore
	// par[1]: oggetto char a cui si revoca il ruolo
	// par[2]: tag del ruolo	
	// par[3]: struttura da cui si sta revocando
	
	
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $message contiene il messaggio di ritorno	
	
	protected function check( $par, &$message )
	{ 
		
		if ( ! parent::check( $par, $message ) )					
		{ return false; }
		
		// verifico se il giocatore a cui si deve revocare il ruolo ce 
		// l'ha ancora		
		
		$role = $par[1] -> get_current_role();				
		if ( ! $role )
		{
			$message = sprintf(kohana::lang( 'charactions.charhasnomorerole'), 	$par[1]->name );				
			return false;		
		}
		
		/////////////////////////////////////
		// controllo costo
		/////////////////////////////////////
		
		if ( in_array( $par[2], 
			array( 'church_level_2', 'church_level_3', 'church_level_4') ) )
		{
			$this -> required_fp = Character_Role_Model::get_requiredfp( $par[1], 'revoke', $par[2] );
			$structureinfo = $par[3] -> get_info();
			if ( $structureinfo['faithpoints'] < $this -> required_fp )
			{
				$message = 
					kohana::lang( 'ca_assignrole.error-notenoughfaithpoints', $this -> required_fp );
				return false;	
			}
		}
		else
		{
			$this -> required_sc = Character_Role_Model::get_requiredcoins( $par[1], 'revoke', $par[2] );
			
			$structurecoins = $par[3] -> get_item_quantity( 'silvercoin' );
			if ( $structurecoins < $this -> required_sc )
			{
				$message = 
					kohana::lang( 'ca_assignrole.error-notenoughmoney', $this -> required_sc );
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

		$appointer_role = $par[0]->get_current_role();
		$role = $par[1] -> get_current_role();
		$revoked = ORM::factory( 'character', $par[1] -> id );		
		
		// Consumo degli items/vestiti indossati
		Item_Model::consume_equipment( $this->equipment, $par[0] );
		
		// tolgo fps
		if ( in_array( $par[2], array( 'church_level_2', 'church_level_3', 'church_level_4')) ) 		
		{		
			//var_dump($par[3]); exit;
			
			$par[3] -> modify_fp ( - $this -> required_fp, 'revokerole' );
			
			Structure_Event_Model::newadd(
				$par[3] -> id,
				'__events.fpupdaterolerevokal' . ';' . 
				$this -> required_fp .  				
				$par[1] -> get_rolename() . ';' . 
				$par[1] -> name
				);
		}
		else
		{
			$par[3] -> modify_coins( - $this -> required_sc, 'revokerole' );
		}
		
		// pubblica annuncio
		
		if ( in_array( $par[2], array( 'church_level_2', 'church_level_3', 'church_level_4')) ) 		
		{
		
			Character_Event_Model::addrecord(
				null,
				'announcement', 
				'__events.newrevokerolechurch_announcement'.
				';'.$par[0]->name .			
				$par[0] -> get_rolename() .
				$revoked -> get_rolename() .
				';'. $par[1] -> name .
				';__' . $role -> region -> name, 
				'evidence'
				);
		}
		else
		{
			Character_Event_Model::addrecord(
				null,
				'announcement', 
				'__events.newrevokerole_announcement'.
				';'.$par[0]->name .			
				$par[0] -> get_rolename().
				$revoked -> get_rolename().
				';'. $par[1] -> name .
				';__' . $role -> region -> name, 
				'evidence'
				);
			
		}	
	
		
		Character_Event_Model::addrecord(
			$par[1]->id, 
			'normal',  
			'__events.newrevokerole_event' .
			';' . $par[0] -> name .
			$par[0] -> get_rolename(),			
			'evidence'					
			);
				
		// aggiorna lo storico, e rimuovi il controllo della
		// struttura
		
		$role -> end( $par[1] ); 		
		$role -> end = time();
		$role -> current = false;
		$role -> save();
						
		$message = kohana::lang( 'charactions.revoke_role_ok', $revoked -> get_rolename( true ) , $par[1] -> name  );			

		return true;
	
	}
}
