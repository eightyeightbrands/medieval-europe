<?php defined('SYSPATH') OR die('No direct access allowed.');

class Trainingground_Controller extends Template_Controller
{
	// Imposto il nome del template da usare
	public $template = 'template/gamelayout';
	
	/**
	* Permette di allenarsi
	* con uno sparring partner
	* @param none
	* @return none
	*/
	
	public function trainwithsparring( $structure_id = null)
	{
		$maxrepeats = 20;
		$view = new View( 'trainingground/trainwithsparring');
		
		$sheets  = array('gamelayout'=>'screen', 'battlereport' => 'screen' );		
		$report = array(); 
		$weapons = array( 'a' => 'a' );
		$character = Character_Model::get_info( Session::instance()->get('char_id') ); 
		$data = null;
				
		$form = array(
			'fighter1' => 'Guglielmo di Valenza',
			'faithlevel1' => 50,
			'debugmode' => false,			
			'fightmode1' => 'normal',
			'repeats' => 1,
			'clones1' => 1,
			'health1' => 100,
			'energy1' => 50,
			'staminaboost1' => false,
			'parry1' => 0,
			'str1' => Character_Model::get_attributelimit(),
			'dex1' => Character_Model::get_attributelimit(),
			'intel1' => Character_Model::get_attributelimit(),
			'cost1' => Character_Model::get_attributelimit(),
			'weapon1' => '',
			'armorhead1' => '',
			'armortorso1' => '',
			'armorlegs1' => '',
			'armorfeet1' => '',
			'armorshield1' => '',
			'fighter2' => 'Defender',
			'faithlevel2' => 50,			
			'fightmode2' => 'normal',
			'staminaboost2' => false,
			'parry2' => 0,
			'clones2' => 1,
			'health2' => 100,
			'energy2' => 50,
			'str2' => Character_Model::get_attributelimit(),
			'dex2' => Character_Model::get_attributelimit(),
			'intel2' => Character_Model::get_attributelimit(),
			'cost2' => Character_Model::get_attributelimit(),
			'weapon2' => '',
			'armorhead2' => '',
			'armortorso2' => '',
			'armorlegs2' => '',
			'armorfeet2' => '',
			'armorshield2' => '',
		);
		
		$armors = ORM::factory( 'cfgitem' ) -> 	where ( array( 'category' => 'armor' ) ) -> find_all();
		
		$listweapons = ORM::factory( 'cfgitem' ) -> 
			in ( 'category', array( 'weapon' ) ) -> 
			orderby( 'id', 'ASC' ) -> find_all() -> select_list( 'id', 'name');
		
		$listweapons[0] = 'structures_trainingground.noweapon';
		foreach ( $listweapons as $key => &$value )		
			$listweapons[$key] = kohana::lang($value);
		
		ksort($listweapons);
		
		foreach ( $armors as $armor )
		{
			$listarmors[$armor -> part][0] = kohana::lang('structures_trainingground.noarmor');
			$listarmors[$armor -> part][$armor -> id] = kohana::lang($armor -> name) ;							
		}				
		
		
		if ( ! $_POST )
		{
			$structure = StructureFactory_Model::create( null, $structure_id );
			if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message, 'public', 'trainwithsparring' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect( 'region/view/' );
			}
		}
		else
		{	
			//var_dump($_POST);exit;
			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );
			if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message, 'public', 'trainwithsparring' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect( 'region/view/' );
			}
			
			$debug = ($this -> input -> post('debugmode') == 'debug');			
			
			if ( $this -> input -> post('repeats') > $maxrepeats )
			{
				Session::set_flash( 'user_message', "<div class=\"error_msg\">Please don't use more then {$maxrepeats} repeats.</div>");
				url::redirect('trainingground/trainwithsparring/' . $structure -> id );	
			}
			
			if ( $this -> input -> post('repeats') > 1  and $debug == true )
			{
				Session::set_flash( 'user_message', "<div class=\"error_msg\">Please don't use more then one repeats when using debug mode.</div>");
				url::redirect('trainingground/trainwithsparring/' . $structure -> id );			
			}		
			
			// check moneys
			
			if ( $this -> input -> post('fightd') )
			{
				if ( $this -> input -> post('debugmode') == true )
					$cost = 5;
				else
					$cost = 2;
					
				if ( $character -> get_item_quantity( 'doubloon' ) < $cost )
				{ 	
					Session::set_flash( 'user_message', "<div class=\"error_msg\">" . kohana::lang('bonus.error-notenoughdoubloons') . "</div>");				url::redirect('trainingground/trainwithsparring/' . $structure -> id );
				}
				else
					$character -> modify_doubloons( -$cost, 'sparringpartner' );

			}
			
			if ( $this -> input -> post('fightsc') )
			{
				if ( $this -> input -> post('debugmode') == true )
					$cost = 15;
				else
					$cost = 6;
					
				if ( $character -> check_money( $cost ) == false )
				{ 	
					Session::set_flash( 'user_message', "<div class=\"error_msg\">" . kohana::lang('charactions.global_notenoughmoney') . "</div>");		
					url::redirect('trainingground/trainwithsparring/' . $structure -> id );
				}
				else
					$character -> modify_coins( -$cost, 'sparringpartner' );

			}			
			
			$data = ST_TrainingGround_1_Model::trainwithsparring( $debug, $this -> input -> post() );
			
		}
		
				
		$form = arr::overwrite($form, $this -> input -> post()); 		
		$view -> form = $form;		
		$view -> listarmors = $listarmors;
		$view -> structure_id = $structure_id;
		$view -> listweapons = $listweapons;
		$view -> repeats = $data['repeats'];
		$view -> character = $character;
		$view -> weapons = $weapons;
		$this -> template -> sheets = $sheets;
		$view -> data = $data;		
		$this -> template -> content = $view;		
		
	}
	
	/**
	* Permette di allenarsi
	* @param structure_id id struttura
	* @return none
	*/
	
	function train( $structure_id )
	{
		$view = new View ('structure/train');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$character = Character_Model::get_info( Session::instance()->get('char_id') ); 
		
		if ( !$_POST)
		{
			// carico la struttura da db dopodichè instanzio il corretto modello			
			
			$structure = StructureFactory_Model::create( null, $structure_id );
			
			
			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message, 
				'public', 'train' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}				
					
			$model = StructureFactory_Model::create( $structure->structure_type->type, $structure_id );
			$dummies = $structure->get_item_quantity( 'wooden_dummies' );
			
		}
		else
		{
		
			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );			
			
			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message, 
				'public', 'train' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}
			
			$model = StructureFactory_Model::create( $structure->structure_type->type, $structure_id );			
			$dummies = $structure -> get_item_quantity( 'wooden_dummies' );
			
			$o = Character_Action_Model::factory("study");
			$par[0] = $character;
			$par[1] = $model;				
			$par[2] = $this -> input -> post('hours');			
			$par[3] = $this -> input -> post('course');
			
			$rec = $o -> do_action( $par, $message );			

			if ( $rec )
			{
				Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
				url::redirect( $structure -> getSuperType() . '/train/' . $structure -> id );
				
			}
			else
			{					
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");		
				url::redirect( $structure -> getSuperType() . '/train/' . $structure -> id );
			}					
			
		}
		
		$availablecourses = $structure -> getAvailablecourses();		
		$view -> availablecourses = $availablecourses;		
		$view -> dummies = $dummies;
		$view -> structure = $structure;
		$view -> char = $character;
		$view -> appliabletax = Region_Model::get_appliable_tax( 
			$structure -> region, 'valueaddedtax', 
			$character ); 
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;
	
	}
	
	/**
	* carica info della struttura
	*/
	
	function info( $structure_id )
	{
		url::redirect( '/structure/info/' . $structure_id );
	}

	// assign_rolerp
	// ***********************************************************
	// Assegna i titoli e gli incarichi reali ai giocatori
	//
	// @param   $structure_id    id del castello
	//
	// @output  none
	// ***********************************************************
	
	function assign_rolerp( $structure_id )
	{
	
		$view   = new View ( 'trainingground/assign_rolerp' );
		$sheets = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$subm   = new View ('template/submenu');
		
		// Inizializzo le form
		$formroles = array
		( 
		'role'        => 'trainer',		
		'region'      => null,
		'nominated'   => null,
		'place'       => null,
		);

		// Definisco gli incarichi reali
		// assegnabili
		$roles = array
		( 
		'trainer'   => kohana::lang('global.trainer_m')
		);

		$character = Character_Model::get_info( Session::instance()->get('char_id') );

		if ( !$_POST ) 
		{
			$structure = StructureFactory_Model::create( null, $structure_id );
			// controllo permessi		
			if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message, 
				'private', 'assign_rolerp' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}			
		}
		else
		{	
			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );
			if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message, 
				'private', 'assign_rolerp' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}

			$ca = Character_Action_Model::factory("assignrolerp");		
			//var_dump( $_POST ); exit;
			// Characther che nomina
			$par[0] = $character;
			// Character nominato
			$par[1] = ORM::factory( 'character' )->where( array('name' => $this->input->post('nominated')) )->find(); 
			// Tag ruolo
			$par[2] = $this->input->post( 'role' );
			// Regione dove avviene la nomina
			$par[3] = ORM::factory( 'region', $this->input->post( 'region_id' ) ); 
			// Struttura da dove avviene la nomina
			$par[4] = ORM::factory( 'structure', $this->input->post( 'structure_id' ) );
			// Nome del feudo da associare al titolo
			$par[5] = $this->input->post( 'place' );
			
			if ( $ca->do_action( $par,  $message ) )
			{
				Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
				url::redirect('trainingground/manage/' . $structure->id);
			}	
			else	
			{ 
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>"); 
				url::redirect ( 'trainingground/assign_rolerp/' . $structure->id );
			}
		}
		
		$lnkmenu = $structure -> get_horizontalmenu ('assign_rolerp');
		$view -> structure = $structure; 
		$subm -> submenu = $lnkmenu;		
		$view -> submenu = $subm;		
		$view -> formroles = $formroles;
		$view -> roles = $roles;
		$this->template->content = $view;
		$this->template->sheets = $sheets;
				
	}	
}
