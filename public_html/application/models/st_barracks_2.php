<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Barracks_2_Model extends ST_Barracks_1_Model	
{	
	
	public function init()
	{	
		parent::init();
		$this -> setCurrentLevel(2);		
		$this -> setMaxlevel(2);
		$this -> setIsbuyable(false);
		$this -> setIssellable(false);						
		$this -> setWikilink('Barracks_and_Prisons_-_Level_2');		
	}	
	
	// Funzione che costruisce i links comuni relativi alla struttura
	// @output: stringa contenente i links relativi a questa struttura
	
	public function build_common_links( $structure, $bonus = false )
	{	
					
		$links = parent::build_common_links( $structure, $bonus );		
		$char = Character_Model::get_info( Session::instance()->get('char_id') );			
		// se il char è delegato, deve esserci il link
		
		if ( Structure_Grant_Model::get_chargrant( $structure,  $char, 'captain_assistant') == true )
		{
			$links .= html::anchor( "/barracks/armory/" . $structure -> id, Kohana::lang('structures_barracks.managearmory'),
			array('title' => Kohana::lang('structures_barracks.managearmory'), 'class' => 'st_special_command')). "<br/>";		
		}
		
		return $links;
	}

	
	
}
