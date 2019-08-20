<?php defined('SYSPATH') OR die('No direct access allowed.');

class Religion_2_Controller extends Template_Controller
{
	// Imposto il nome del template da usare
	public $template = 'template/gamelayout';
	
	
	/*
	* religion_2 offices
	*/
	
	function obs_manage( $structure_id )
	{
	
		$view = new View ( 'religion_2/manage' );
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$subm    = new View ('template/submenu');				
		$religiousstructureheader = new View('template/religiousstructureheader');
		$form = array( 'points' => 0, 'targetstructure_id' => null, 'character' => null, 'reason' => null );
		$cs = array(); 
		
		if ( !$_POST )
		{ 
			$structure = StructureFactory_Model::create( null, $structure_id );
			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message,
				'private', 'manage' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect( 'region/view' );
			}		
		}
		else
		{	
			$ca = null;
			
			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );
			
			// transfer points			
			
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
			
			if ( $this -> input -> post( 'submit_description' ) ) 
			{
				$structure -> description = substr($this -> input -> post ('description' ), 0, 1023);
				$structure -> save();
				Session::set_flash('user_message', "<div class=\"info_msg\">" . kohana::lang('structures.configuration_ok') . "</div>");
			}
			
			url::redirect( 'religion_2/manage/'.$structure_id ) ;
			
		}
		
		// controllo permessi	
		$info = Church_Model::get_info($structure -> structure_type -> church_id);					
		
		// carichiamo tutte le strutture della chiesa
		// e costruiamo il dropdown
		
		$churchstructures = Church_Model::helper_allchurchstructuresdropdown( $structure->structure_type->church_id, $structure->id);
		
		$lnkmenu = $structure -> get_horizontalmenu( 'manage' );		
		$view -> churchstructures = $churchstructures;
		$view -> form = $form;
		$view -> info = $info;
		$religiousstructureheader -> info = $info;		
		$religiousstructureheader -> structure = $structure;		
		$view -> religiousstructureheader = $religiousstructureheader;
		$view -> structure = $structure;		
		$subm -> submenu = $lnkmenu;		
		$view -> submenu = $subm;						
		$this -> template->sheets = $sheets;
		$this -> template->content = $view;
		
	}
	
	/**
	* Gestisce la gerarchia
	* @param: structure_id Id struttura
	* @return: none
	*/
	
	function obs_managehierarchy( $structure_id )
	{
		$view = new View ( 'religion_2/managehierarchy' );
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$subm    = new View ('template/submenu');		
		
		if ( !$_POST )
		{
			$structure = StructureFactory_Model::create( null, $structure_id );

			// controllo permessi
			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message,
				'private', 'managehierarchy' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}
		}
		else
		{
		
			kohana::log('info', kohana::debug($this -> input -> post() ));
			
			$par[0] = $character;
			$par[1] = ORM::factory('character') -> 
				where ( 'name' , $this -> input -> post('owner')) -> find(); 
			$par[2] = 'church_level_3';
			
			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );
			
			$childstructure = ORM::factory('structure', $this -> input -> post('childstructure_id'));
		 
			if ( $this -> input -> post('revoke') )
			{
				$ca = Character_Action_Model::factory("revokerole");
				$par[3] = $structure;
				$par[4] = null;
				
			}
			else
			{
				$ca = Character_Action_Model::factory("assignrole");
				$par[3] = $childstructure -> region;				
				$par[4] = $structure;
			}
			
			
			if ( $ca -> do_action( $par,  $message ) )
			{ 				
				Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
				url::redirect ( 'religion_2/managehierarchy/' . $structure -> id );
			}	
			else	
			{ 
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>"); 
				url::redirect ( 'religion_2/managehierarchy/' . $structure -> id );
			}	
		}
		
		$lnkmenu = $structure -> get_horizontalmenu( 'managehierarchy' );
		$childstructures = $structure -> get_childstructures();		
		$view -> childstructures = $childstructures;		
		$view -> structure = $structure;		
		$subm -> submenu = $lnkmenu;		
		$view -> submenu = $subm;						
		$this -> template->sheets = $sheets;
		$this -> template->content = $view;
	}
	
	/* 
	* Assegna i titoli e gli incarichi roleplay ai giocatori
	* @param   int    $structure_id    id della struttura dove avviene la nomina
	* @output  none
	*/
	
	function assign_rolerp( $structure_id )
	{
	
		$view   = new View ( 'religion_2/assign_rolerp' );
		$sheets = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$subm   = new View ('template/submenu');
		
		// Inizializzo le form
		$formroles = array
		( 
			'role'        => 'primate',		
			'region'      => null,
			'nominated'   => null,
			'place'       => null,
		);

		// Definisco gli incarichi reali
		// assegnabili
		$roles = array
		( 
			'inquisitor'   => kohana::lang('global.inquisitor_m'),
			'almoner'   => kohana::lang('global.almoner_m')
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
				url::redirect('religion_2/manage/' . $structure->id);
			}	
			else	
			{ 
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>"); 
				url::redirect ( 'religion_2/assign_rolerp/' . $structure->id );
			}
		}

		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'assign_rolerp';
		$view->submenu = $submenu;

		$view -> structure = $structure; 
		$view -> formroles = $formroles;
		$view -> roles = $roles;
		$this->template->content = $view;
		$this->template->sheets = $sheets;
				
	}
		
}
