<?php defined('SYSPATH') OR die('No direct access allowed.');

class House_Controller extends Template_Controller
{
	// Imposto il nome del template da usare
	public $template = 'template/gamelayout';
	

	/**
	* Elenca la lista delle case
	* @param none
	* @return none
	*/

	public function index()	
	{
		
		$view = new view('house/index');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen', 'structure'=>'screen');
		$houses = ORM::factory( 'structure_type' ) -> where ('supertype', 'house' ) -> find_all(); 
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$currentregion = ORM::factory('region', $character -> position_id );		
		
		$view -> houses = $houses;
		$view -> char = $character;
		$view -> region = $currentregion;
		$this -> template->content = $view;
		$this -> template->sheets = $sheets;
		
	}

	/**
	* Permette di gestire la struttura
	* @param structure_id ID struttura
	* @return none
	*/

	public function manage( $structure_id ) 
	{

		$view = new view('house/manage');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');		
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$structure = StructureFactory_Model::create( null, $structure_id);
		$section_description = new view('structure/section_description');		
		
		if ( ! $structure->allowedaccess( $character, $structure -> getParentType() , 		
			$message, 'private', 'manage' ) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect( 'region/view/' );
		}
		
		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'manage';
		$section_description -> structure = $structure;
		$view -> section_description = $section_description;
		$view -> submenu = $submenu;		
		$view -> structure = $structure;
		$this -> template -> content = $view ;
		$this -> template -> sheets = $sheets; 

	}

}
