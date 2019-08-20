<?php defined('SYSPATH') OR die('No direct access allowed.');
class CA_Recuperateiron_Model extends Character_Action_Model
{
	
	const DELTA_ENERGY = 10;
	const DELTA_GLUT = 10;
	
	protected $cancel_flag = false;
	protected $immediate_action = false;	
	protected $basetime       = 8;  // 3 ore
	protected $attribute      = 'str';  // attributo forza
	protected $appliedbonuses = array ('workerpackage'); // bonuses da applicare
	protected $structure = null;
	protected $skill = null;
	
	
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
				'consume_rate' => 'veryhigh'
			),
			'torso' => array
			(
				'items' => array('any'),
				'consume_rate' => 'veryhigh'
			),
			'legs' => array
			(
				'items' => array('any'),
				'consume_rate' => 'veryhigh'
			),
			'right_hand' => array
			(
				'items' => array('shovel'),
				'consume_rate' => 'high',
			),
		),
	);
	
	/*
	* Check preliminari all'esecuzione dell'azione
	* @input:   $par[0] = char, 
	*           $par[1] = ID item
	* @output:  TRUE = azione disponibile, FALSE = azione non disponibile
	*           $messages contiene gli errori in caso di FALSE
	*/
	protected function check( $par, &$message )
	{ 
		$message = "";
		
		if ( ! parent::check( $par, $message ) )					
			return false;

		// Check: La Forgia esiste?
		$furnace = ORM::factory('item', $par[1] );				
		
		if ( $furnace -> loaded == false )
		{ $message = Kohana::lang("global.operation_not_allowed"); return false; }
		
		// il char ha lo skill 'Recuperateiron'?
				
		if ( Skill_Model::character_has_skill( $par[0] -> id, 'recuperateiron') == false )		
		{ 
			$message = Kohana::lang(
				'global.error-noskill', kohana::lang('character.skill_recuperateiron_name') ); 
			return false;
		}
		
		// La forgia è in una fucina?

		if (is_null( $furnace -> structure_id ))
		{ $message = Kohana::lang("ca_recuperateiron.error-furnacenotinstructure"); return false; }
			
		$this -> structure = StructureFactory_Model::create( null, $furnace -> structure_id);
		
		if ($this -> structure -> getSupertype() != 'blacksmith' or $this -> structure -> character_id != $par[0] -> id )
		{ $message = Kohana::lang("ca_recuperateiron.error-furnacenotinstructure"); return false; }

		// La struttura appartiene al char?
		
		if ($this -> structure -> character_id != $par[0] -> id )
		{ $message = Kohana::lang("ca_recuperateiron.error-furnacenotinstructure"); return false; }		
	
		// Nella directory del negozio ci sono almeno 5 coals?
		
		if ( $this -> structure -> contains_item('coal_piece', 5) == false )
		{ $message = Kohana::lang("ca_recuperateiron.error-structuredoesnotcontainsenoughcoal", 5); return false; }
	
		// Check: il char non ha l'energia e la sazieta' richiesti		
		if
		(
			$par[0] -> energy < (self::DELTA_ENERGY)  or
			$par[0] -> glut < (self::DELTA_GLUT)
		)
		{ $message = Kohana::lang("charactions.notenoughenergyglut"); return false; }
		
		
		// Tutti i check sono stati superati

		return true;
	}


	// Funzione per l'inserimento dell'azione nel DB.
	// Questa funzione appende solo una azione _non la esegue_
	// Al momento non si prevedono azioni istantanee.
	// @input: $par[0] = structure, $par[1] = char
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function append_action( $par, &$message )
	{
	
		$this -> character_id = $par[0] -> id;
		$this -> structure_id = $this -> structure -> id;
		$this -> starttime = time();
		$this -> status = "running";		
		$this -> endtime = $this -> starttime + $this -> get_action_time( $par[0] );		
		
		// Precalcolo ferro
		
		$allcraftableitems = Configuration_Model::get_craftableitems();
		$itemstodestroy = array();
		$recuperatediron = 0;
		
		$skillrecuperateiron = Skillfactory_Model::create('recuperateiron');
		
		$i = 0;
		foreach ($this -> structure -> item as $item )
		{
			// non recuperare ferro da tools (fornace ecc)
			
			if ($item -> cfgitem -> parentcategory == 'structuretool' )
				continue;
			
			kohana::log('debug', "-> Examining {$item -> cfgitem -> tag}...");
			
			if (isset($allcraftableitems[$item -> cfgitem -> tag]))
			{
				kohana::log('debug', kohana::debug($allcraftableitems[$item -> cfgitem -> tag]));
				
				if (
					array_key_exists( 
					'iron_piece',
					$allcraftableitems[$item -> cfgitem -> tag]['requireditems'])
					and
					$item -> quality < 100 					
				)
					{
						
						kohana::log('debug', "-> Item {$item -> cfgitem -> tag} contains iron that can be recuperated.");
						
						$itemstodestroy[$i]['item'] = $item;
						$itemstodestroy[$i]['quantity'] = $item -> quantity;
						$itemstodestroy[$i]['quality'] = $item -> quality;
						$itemstodestroy[$i]['iron']['originalquantity'] 
							= $allcraftableitems[$item -> cfgitem -> tag]['requireditems']['iron_piece']['quantity']; 	
							
						$itemstodestroy[$i]['iron']['recuperatedironfromitem']
							= round(
								$allcraftableitems[$item -> cfgitem -> tag]['requireditems']['iron_piece']['quantity'] 
								* $item -> quantity 
								* (50 + $skillrecuperateiron -> getProficiency( $par[0] -> id ) / 2 ) / 100
								* 80/100,
								0 );
								
						$recuperatediron += $itemstodestroy[$i]['iron']['recuperatedironfromitem'];
						
						// event: 
						
						Character_Event_Model::addrecord( 
							$par[0] -> id, 
							'normal', 
							'__events.itemputinfurnace' . 
							';' . $item -> quantity .
							';__' . $item -> cfgitem -> name . 
							';' . $item -> quality . 
							';' . $itemstodestroy[$i]['iron']['recuperatedironfromitem'] );						
					
					}			
			}
			$i++;
		}
		
		if ( $recuperatediron == 0 )
			{ $message = Kohana::lang("ca_recuperateiron.error-nothingtorecupearte"); return false; }		
		
		// rimuove coal
		
		$coal = Item_Model::factory( null, 'coal_piece' );				
		$coal -> removeitem( 'structure', $this -> structure -> id, 5);
		
		// rimuove oggetti
		
		foreach ( $itemstodestroy as $itemtodestroy )
		{
			$itemtodestroy['item'] -> destroy();			
		}
		
		// abbassa quality della fornace 
		
		Item_Model::consumeitem_instructure( 
			'furnace',
			$this -> structure -> id,
			0.25
		);
						
		$this -> param1 = $recuperatediron; 		
		$this -> save();		

		$message = kohana::lang('ca_recuperateiron.info-ok');
		
		return true;
		
	}

	// Esecuzione dell' azione. 
	// Questa funzione viene chiamata quando viene invocata una 
	// complete_expired_action e gestisce le azioni
	// inserite nella character_actions
	// - si caricano i parametri dal database
	// - si esegue l' azione in base ai parametri
	// - si mette l' azione in stato completed
	
	public function complete_action( $data )
	{
		
		$char = ORM::factory( 'character', $data -> character_id  );
		$structure = StructureFactory_Model::create( null, $data -> structure_id  );
		$recuperatediron = $data -> param1;
		
		// Consumo degli items/vestiti obbligatori indossati
		Item_Model::consume_equipment( $this->equipment, $char );
		
		// Sottraggo l'energia e la sazietà al char
		
		$char -> modify_energy ( - self::DELTA_ENERGY, false, 'recuperateiron' );
		$char -> modify_glut ( - self::DELTA_GLUT );
		$char -> save();			
		
		// deposit iron		
		
		$iron = Item_Model::factory( null, 'iron_piece');
		$iron -> additem( 'structure', $structure -> id, $recuperatediron );
		
		Character_Event_Model::addrecord( 
			$char -> id, 
			'normal', 
			'__events.ironrecuperated' . 
			';' . $recuperatediron );						
		
		// aumenta skill
		
		$skillrecuperateiron = SkillFactory_Model::create('recuperateiron');
		$skillrecuperateiron -> increaseproficiency( $char -> id );
		
		return; 
		
	}
	
	protected function execute_action() {}
	
	public function cancel_action() {}


}
