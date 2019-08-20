<?php defined('SYSPATH') OR die('No direct access allowed.');

class Religion_4_Controller extends Template_Controller
{
	// Imposto il nome del template da usare
	public $template = 'template/gamelayout';
	
	
	/*
	* religion_4 offices
	*/
	
	function manage( $structure_id )
	{
	
		$view = new View ( 'religion_4/manage' );
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$structure = StructureFactory_Model::create( null, $structure_id );
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$subm    = new View ('template/submenu');		
		$religiousstructureheader = new View('template/religiousstructureheader');
		$form = array (
			'description' => '',
			'points' => 0, 
			'targetstructure_id' => null,
			);
		
		// controllo permessi
		if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message,
			'private', 'manage') )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect('region/view/');
		}	
		
		if ( !$_POST )
			;
		else
		{
			
			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );
			
			// trasferisci FP
			
			if ( $this -> input -> post('transfer' ) )
			{
				$ca = Character_Action_Model::factory("transferfppoints");				
				$par[0] = $character;		
				$par[1] = $structure;
				$par[2] = ORM::factory('structure', $this -> input -> post('targetstructure_id' ) );
				$par[3] = $this -> input -> post('points');
				
				if ( $ca -> do_action( $par,  $message ) )
				{ 				
					Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");					
				}	
				else	
				{ 
					$form = arr::overwrite( $form, $this -> input -> post() ); 				
					Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>"); 									
				}	
			
			}
			
			// cambia descrizione
			
			if ( $this -> input -> post( 'submit_description' ) ) 
			{
				$structure -> description = substr($this -> input -> post ('description' ), 0, 1023);
				$structure -> save();
				Session::set_flash('user_message', "<div class=\"info_msg\">" . kohana::lang('structures.configuration_ok') . "</div>");
				
			}
			
			if ( $this -> input -> post( 'submit_message' ) ) 
			{
				$structure -> message = substr($this -> input -> post ('message' ), 0, 1023); 				
				$structure -> save();
				Session::set_flash('user_message', "<div class=\"info_msg\">" . kohana::lang('structures.configuration_ok') . "</div>");
			}
			
			url::redirect('religion_4/manage/' . $structure -> id ) ;
		
		}
		
		// carichiamo tutte le strutture della chiesa
		// e costruiamo il dropdown
		
		$churchstructures = Church_Model::helper_allchurchstructuresdropdown( $structure->structure_type->church_id, $structure->id);		
		
		$lnkmenu = $structure -> get_horizontalmenu( 'manage' );
		
		$info = Church_Model::get_info($structure -> structure_type -> church_id);					
		$structureinfo = $structure -> get_info();
		$rfavailability = $structure -> get_option('rfavailability');		
		$view -> info = $info;
		$view -> structureinfo = $structureinfo;
		$view -> churchstructures = $churchstructures;
		$view -> rfavailability = $rfavailability;
		$religiousstructureheader -> info = $info;		
		$religiousstructureheader -> structure = $structure;		
		$view -> religiousstructureheader = $religiousstructureheader;
		$view -> structure = $structure;		
		$subm -> submenu = $lnkmenu;		
		$view -> submenu = $subm;						
		$view -> form = $form;
		$this -> template->sheets = $sheets;
		$this -> template->content = $view;
		
	}
	
	
	function celebratemarriage( $structure_id )
	{
		
		$view = new View ( 'religion_4/celebratemarriage' );
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		
		if (!$_POST )
		{
			$structure = StructureFactory_Model::create( null, $structure_id );
			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message, 'private', 'celebratemarriage' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}	
			
			$view -> annulmentchar = '';
			$view -> celebratehusband = '';
			$view -> celebratewife = '';
		}
		else
		{
		
			
			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );
			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message, 'private', 'celebratemarriage' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}
			
			
			if ( $this -> input -> post('startmarriage' ) )
			{
				$par[0] = $character;
				$par[1] = $structure;
				$par[2] = ORM::factory('character') -> where ('name', $this -> input -> post('celebratehusband')) -> find();
				$par[3] = ORM::factory('character') -> where ('name', $this -> input -> post('celebratewife')) -> find();
				
				$ca = Character_Action_Model::factory("celebratemarriage");		
				$view -> celebratewife = $this -> input -> post('celebratewife');
				$view -> celebratehusband = $this -> input -> post('celebratehusband');
				$view -> annulmentchar = '';
			}
			elseif ( $this -> input -> post('cancelmarriage' ) )
			{
				$par[0] = $character;
				$par[1] = $structure;
				$par[2] = ORM::factory('character') -> where ('name', $this -> input -> post('annulmentchar')) -> find();				
				$ca = Character_Action_Model::factory("cancelmarriage");		
				$view -> annulmentchar = $this -> input -> post('annulmentchar');
				$view -> celebratehusband = '';
				$view -> celebratewife = '';
			}
			else
			{
				Session::set_flash('user_message', "<div class=\"info_msg\">". $kohana::lang('global.operation_not_allowed') . "</div>");
				url::redirect ( 'religion_4/celebratemarriage/' . $structure -> id );
			}			
			
			if ( $ca -> do_action( $par,  $message ) )
			{ 				
				Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");				
				//url::redirect ( 'religion_4/celebratemarriage/' . $structure -> id );
			}	
			else	
			{ 
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>"); 
				//url::redirect ( 'religion_4/celebratemarriage/' . $structure -> id );
			}
		
		}
		
		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'celebratemarriage';
		$view->submenu = $submenu;

		
		$view -> structure = $structure;		
		$this -> template -> sheets = $sheets;
		$this -> template -> content = $view;	
	}
	
	/**
	* Donazione denari
	*/
	
	function donatecoins( $structure_id )
	{
		
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$structure = StructureFactory_Model::create( null, $structure_id );
		
		// controllo permessi
		if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message, 'public', 'donatecoins' ) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect('region/view/');
		}	
		
		$par[0] = $character;
		$par[1] = $structure;
		
		$ca = Character_Action_Model::factory("donatecoins");		
		if ( $ca -> do_action( $par,  $message ) )
		{ 				
			Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
			url::redirect ( 'region/view/' );
		}	
		else	
		{ 
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>"); 
			url::redirect ( 'region/view/' );
		}	
	}
	
	
	/* 
	* Assegna i titoli e gli incarichi roleplay ai giocatori
	* @param   int    $structure_id    id della struttura dove avviene la nomina
	* @output  none
	*/	
	function assign_rolerp( $structure_id )
	{
		$view   = new View ( 'religion_4/assign_rolerp' );
		$sheets = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$subm   = new View ('template/submenu');
		
		// Inizializzo le form
		$formroles = array
		(
			'role'        => 'acolyte',		
			'region'      => null,
			'nominated'   => null,
			'place'       => null,
		);

		// Definisco gli incarichi roleplay assegnabili
		$roles = array
		( 
			'acolyte'   => kohana::lang('global.acolyte_m')
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
				url::redirect('religion_3/manage/' . $structure->id);
			}	
			else	
			{ 
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>"); 
				url::redirect ( 'religion_3/assign_rolerp/' . $structure->id );
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
