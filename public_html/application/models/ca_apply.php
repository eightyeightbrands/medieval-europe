<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Apply_Model extends Character_Action_Model
{
	protected $immediate_action = true;
	protected $item = null;
	protected $enabledifrestrained = true;
	
	// Effettua tutti i controlli relativi alla eat, sia quelli condivisi
	// con tutte le action che quelli peculiari della eat
	// @input: array di parametri (par[0] memorizza l'id dell'oggetto che sto mangiando)
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	// par[0] = id dell' item da mangiare
	// par[1] = oggetto char.
	// par[2] = moltiplicatore
	
	protected function check( $par, &$message )
	{ 
		if ( ! parent::check( $par, $message, $par[1] -> id ) )					
		{ return false; }

		// Istanzio l'oggetto che sto per mangiare
		$this -> item = ORM::factory('item', $par[0]);
		
		// Controllo che l'oggetto esista
		
		if (! $this -> item -> loaded)
		{ $message = kohana::lang('charactions.item_notexist'); return FALSE; }
		
		// Controllo che l'oggetto sia consumabile
		
		if ( $this -> item -> cfgitem -> parentcategory != 'consumables' )
		{ $message = kohana::lang('ca_apply.error-notconsumable'); return FALSE; }
		
		// E' possibile consumare sino a 3 item
		if ($par[2] <= 0 or $par[2] > 3)
		{ $message = kohana::lang('ca_apply.error-wrongquantity'); return FALSE; }	
				
		if ( 
			Character_Model::has_item( 
					$par[1]->id, $this -> item -> cfgitem -> tag, $par[2] ) == false )
			{ $message = kohana::lang('charactions.item_notininventory'); return FALSE; }

		// Se l' item è healingpill verifichiamo se il char ha giÃ  30 punti di salute.
		
		if ($this -> item -> cfgitem -> tag == 'healing_pill' and $par[1] -> health >= 30 )
		{ $message = kohana::lang('ca_apply.error-alreadyhasenoughhealth'); return FALSE; }
				
		// If items is food and recovered glut is > than necessary, give a warning
    /*
		if  (
				( 
				$this -> item -> cfgitem-> subcategory == 'rawfood' 
				or
				$this -> item -> cfgitem-> subcategory == 'cookedfood'
				)
			and
				$this->item->cfgitem->spare1 * $par[2] > (50 - $par[1] -> glut)
		)
		{ $message = kohana::lang('ca_apply.error-alreadyhasenoughglut'); return FALSE; }
	  */	
		
		return true;
		
	}
	
	// nessun controllo particolare
	protected function append_action( $par, &$message )
	{}
	
	public function execute_action ( $par, &$message ) 
	{		
		
		$message = 'ca_apply.info-applyok';		
		
		kohana::log('debug', '-> Trying to apply: ' . $this -> item -> cfgitem -> tag );
		
		// food
		
		if ( 
			$this -> item -> cfgitem-> subcategory == 'rawfood' or
			$this -> item -> cfgitem-> subcategory == 'cookedfood'
		)
		{
			// Verifico che non ci siano dei malus contro la chiesa del char
			$char_church_id = $par[1]->church_id;
			
			// Conto quanti malus ci sono verso la mia chiesa
			$num_malus = Church_Model::get_num_malus_against_my_church($char_church_id, 'curseinfidels');
			
			// Calcolo la glut residua dopo applicazione di tutti i malus trovati
			$malus_to_apply = (1 - $num_malus * 0.25);
			$glut_to_apply = round($this->item->cfgitem->spare1 * $malus_to_apply);
			
			// Modifico la glut, al netto dei malus, considerando il moltiplicatore
			$par[1] -> modify_glut( $glut_to_apply * $par[2] );			
		}
		
		// healing
		
		elseif ( $this -> item -> cfgitem -> tag == 'healing_pill' )		
		{
			
			$par[1] -> modify_health( max( 30,  $par[1] -> health ), true, 'applyhealingpill' );
			
		}
		elseif ( $this -> item -> cfgitem -> tag == 'elixirofhealth' )		
		{
			
			$par[1] -> modify_health(100, true, 'applyelixirofhealth' );
			
		}
		
		// Bevande alcoliche
		elseif ($this -> item -> cfgitem -> subcategory == 'alcoholicdrink' )		
		{
			// Modificatore energia
			// *************************************************************
			
			// Verifico che non ci siano dei malus contro la chiesa del char
			$char_church_id = $par[1]->church_id;
			
			// Conto quanti malus ci sono verso la mia chiesa
			$num_malus = Church_Model::get_num_malus_against_my_church($char_church_id, 'curseinfidels');
			
			// Calcolo l'energia residua dopo applicazione di tutti i malus trovati
			$malus_to_apply = (1 - $num_malus * 0.25);
			$energy_to_apply = round($this->item->cfgitem->spare3 * $malus_to_apply);
			
			// Modifico l'energia, al netto dei malus, considerando il moltiplicatore
			$par[1] -> modify_energy( $energy_to_apply * $par[2], false, 'drink' );
	
			// aumento il livello di disintossicazione, usando anche un +/- 1,2 random.
			
			$current_il_stat = Character_Model::get_stat_d( $par[1] -> id, 	'intoxicationlevel');
		
			$r = mt_rand(0,1);
			$r2 = mt_rand(1,2);
			
			if ( $r == 0 )
				$r2 *= -1;
				
			$new_il = $current_il_stat -> value + $this -> item -> cfgitem -> spare1 * $par[2] + $r2 ;
			
			kohana::log('info', '-> drink: char: ' . $par[1] -> name . ', old_il: ' . $current_il_stat -> value . ', new il: ' . $new_il );
			
			// se il >= 50, tipsy status
		
			if ( $new_il >= 50 and !$par[1] -> has_disease( 'tipsyness' ) )
			{
				kohana::log('info', '-> drink: char: ' . $par[1] -> name . ' got drunk.');
				$obj = DiseaseFactory_Model::createDisease('tipsyness');
				$obj -> injectDisease( $par[1] -> id );
				$message = 'ca_apply.info-drinkeffect_gottipsy';
				
			}
			
			if ( $new_il >= 100 )
			{
			
				// se il >= 100, azione blocking
			
				kohana::log('info', '-> drink: char: ' . $par[1] -> name . ' passed out.');
				$obj = DiseaseFactory_Model::createDisease('drunkness');
				$obj -> injectDisease( $par[1] -> id );					
				$message = 'ca_apply.info-drinkeffect_gotdrunk';
			}
			
			// aggiorna l' intoxication level
			
			$par[1] -> modify_stat( 
				'intoxicationlevel', 
				$this -> item -> cfgitem -> spare1 * $par[2],
				null,
				null );				
		}
		elseif ( $this -> item -> cfgitem -> tag == 'elixirofdexterity' )
			Character_Model::modify_stat_d(
				$par[1] -> id,
				'dexboost',
				0,
				null,
				null,
				true,
				time() + (8*3600)			
			);		
		elseif ( $this -> item -> cfgitem -> tag == 'elixirofmight'	)
		{
			Character_Model::modify_stat_d(
				$par[1] -> id,
				'strboost',
				0,
				null,
				null,
				true,
				time() + (8*3600)			
			);	
			$par[1] -> modify_glut(  $this -> item -> cfgitem -> spare1 * $par[2] );						
		}
			
		elseif ( $this -> item -> cfgitem -> tag == 'elixirofstrength' )				
			Character_Model::modify_stat_d(
				$par[1] -> id,
				'strboost',
				0,
				null,
				null,
				true,
				time() + (8*3600)			
			);			
		elseif ( $this -> item -> cfgitem -> tag == 'elixirofintelligence' )
			Character_Model::modify_stat_d(
				$par[1] -> id,
				'intelboost',
				0,
				null,
				null,
				true,
				time() + (8*3600)			
			);
		elseif ( $this -> item -> cfgitem -> tag == 'elixirofconstitution' )
			Character_Model::modify_stat_d(
				$par[1] -> id,
				'costboost',
				0,
				null,
				null,
				true,
				time() + (8*3600)			
			);
		elseif ( $this -> item -> cfgitem -> tag == 'elixirofstamina' )
			Character_Model::modify_stat_d(
				$par[1] -> id,
				'staminaboost',
				0,
				null,
				null,
				true,
				time() + (8*3600)			
			);
		elseif ( $this -> item -> cfgitem -> tag == 'elixirofcuredisease' )
		{
			$diseases = $par[1] -> get_diseases();
			$hasdiseases = false;
			foreach ((array) $diseases as $disease )
			{
				$hasdiseases = true;
				$instance = DiseaseFactory_Model::createDisease($disease -> param1);				
				kohana::log('debug', "-> Curing disease {$disease -> param1} for char: {$par[1] -> name}");
				$instance -> cure_disease( $par[1] );
				if (in_array($disease -> param1, array('tipsyness')))
				{						
			
					Character_Model::modify_stat_d(
					$par[1] -> id,
					'intoxicationlevel',
					0,					
					null,
					null,
					true			
					);
				}
				
			}
			
			// cura salute			
			if ($hasdiseases )
				$par[1] -> modify_health(100, true, 'applyelixirofcuredisease' );
		
		}
			
		// evento per quest		
		
		$_par[0] = $this -> item; // item
		GameEvent_Model::process_event( $par[1], 'eatfood', $_par );	

		// se era legato ad un prestito, il prestito va cancellato
		
		if ( !is_null( $this -> item -> lend_id ) )
		{
			$lend = ORM::factory('structure_lentitem', $this -> item -> lend_id ); 
			$lend -> delete();
		}
		
		$message = kohana::lang( $message, 
			$par[2], 
			kohana::lang( $this -> item -> cfgitem -> name )
		);
		
		// event
		
		Character_Event_Model::addrecord( 
			$par[1] -> id,
			'normal',
			'__events.eatdrinkitem' . ";{$par[2]}" . '
			;__' . $this -> item -> cfgitem -> name 
		);
		
		// save char
		
		$par[1] -> save();
		
		// remove item
		$this -> item -> removeitem( 'character', $par[1] -> id, $par[2] );
		
		return true;
	}
	
}
