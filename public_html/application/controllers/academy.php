<?php defined('SYSPATH') OR die('No direct access allowed.');

class Academy_Controller extends Template_Controller
{
	// Imposto il nome del template da usare
	public $template = 'template/gamelayout';
	
	/**
	* Permette di studiare un argomento
	* @param int $structure_id id struttura
	* @return none
	*/
	
	function study( $structure_id )
	{
		
		$view = new View ('/structure/study');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen', 'structure'=>'screen');
		
		$character = Character_Model::get_info( Session::instance()->get('char_id') ); 
		
		if ( !$_POST)
		{
			// carico la struttura da db dopodichè instanzio il corretto modello
			// (structure -> st_academy -> st_academy_level_x)
			
			$structure = StructureFactory_Model::create( null, $structure_id );
			
			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message, 
				'public', 'study' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}					
			
		}
		else
		{

			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );
			
			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message, 
				'public', 'study' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}	
			
			$o = Character_Action_Model::factory("study");
			$par[0] = $character;
			$par[1] = $structure;				
			$par[2] = $this->input->post('hours');			
			$par[3] = $this->input->post('course');
			
			$rec = $o->do_action( $par, $message );			

			if ( $rec )
			{
				Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
				url::redirect('/region/view/' . $character -> position_id );
				return;
				
			}
			else
			{					
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");		
			}			
		
		}
		
		$availablecourses = $structure -> getAvailablecourses();		
		$view -> availablecourses = $availablecourses;
		$view -> structure = $structure ;
		$view -> char = $character ;			
		$view -> appliabletax = Region_Model::get_appliable_tax( $structure -> region, 'valueaddedtax', $character ); 		
		$this -> template -> content = $view ;
		$this -> template -> sheets = $sheets;
	
	}

	/**
	* Assegna i titoli e gli incarichi reali ai giocatori
	* @param  int $structure_id id del castello
	* @return none
	*/
	
	function assign_rolerp( $structure_id = null )
	{
	
		$view   = new View ( 'academy/assign_rolerp' );
		$sheets = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$subm   = new View ('template/submenu');
		
		// Inizializzo le form
		$formroles = array
		( 
		'role'        => 'assistant',		
		'region'      => null,
		'nominated'   => null,
		'place'       => null,
		);

		// Definisco gli incarichi reali
		// assegnabili
		$roles = array
		( 
		'assistant'   => kohana::lang('global.assistant_m')
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
			$par[0] = $character;
			$par[1] = ORM::factory( 'character' )->where( array('name' => $this->input->post('nominated')) )->find(); 
			$par[2] = $this->input->post( 'role' );
			$par[3] = ORM::factory( 'region', $this->input->post( 'region_id' ) ); 
			$par[4] = ORM::factory( 'structure', $this->input->post( 'structure_id' ) );
			$par[5] = $this->input->post( 'place' );
			
			if ( $ca->do_action( $par,  $message ) )
			{
				Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
				url::redirect('academy/manage/' . $structure->id);
			}	
			else	
			{ 
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>"); 
				url::redirect ( 'academy/assign_rolerp/' . $structure->id );
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
