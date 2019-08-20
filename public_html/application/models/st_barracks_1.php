<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Barracks_1_Model extends Structure_Model
{
	
	public function init()
	{	
		$this -> setCurrentLevel(1);
		$this -> setParenttype('barracks');
		$this -> setSupertype('barracks');
		$this -> setMaxlevel(2);
		$this -> setIsbuyable(false);
		$this -> setIssellable(false);				
		$this -> setHoursfornextlevel(375);		
		$this -> setWikilink('Barracks_and_Prisons');
		$this -> setNeededmaterialfornextlevel(
			array(
				'brick' => 300,
				'wood_piece' => 150,
				'stone_piece' => 100,
			)
		);
		$this -> setStorage(4000000);
	}		
	
	// Funzione che costruisce i links comuni relativi alla struttura
	// @output: stringa contenente i links relativi a questa struttura
	
	public function build_common_links( $structure, $bonus = false )
	{
				
		// Azioni comuni accessibili a tutti i chars
		
	
			
		$links = parent::build_common_links( $structure );				
				
			
		$links .= html::anchor( "/structure/donate/" . $structure -> id, Kohana::lang('structures_actions.global_deposit'), array('class' => 'st_common_command')) . "<br/>";
		
		$info = $structure -> get_info();		
		if ( ! is_null ($info ))
			$links .= html::anchor( "/structure/info/" . $structure -> id, Kohana::lang('structures_actions.global_info'), array('class' => 'st_common_command')) . "<br/>" ;		
			
		$links .= html::anchor( "/barracks/clean/", Kohana::lang('structures_actions.barracks_clean'),
		array('title' => Kohana::lang('structures_actions.barracks_clean_info'), 'class' => 'st_common_command'));
						
		if ( $bonus !== false )
		{
		   	$links .= ' - '.html::anchor( "/barracks/clean/2", 'x2',
		    array('title' => Kohana::lang('structures_actions.barracks_clean').' (x2)', 'class' => 'st_common_command',
				'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')') );
		    
		   	$links .= ' - '.html::anchor( "/barracks/clean/3", 'x3',
		    array('title' => Kohana::lang('structures_actions.barracks_clean').' (x3)', 'class' => 'st_common_command',
				'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')') );
		}
				
		$links .= "<br/>";

		return $links;
	}

	// Funzione che costruisce i links speciali relativi alla struttura
	// @output: stringa contenente i links relativi a questa struttura
	
	public function build_special_links( $structure )
	{
		// Azioni speciali accessibili solo al char che governa la struttura
		
		$links = parent::build_special_links( $structure );
		
		$links .= html::anchor( 
			"/structure/rest/" . $structure -> id, Kohana::lang('global.rest'),
				array('title' => Kohana::lang('global.rest'), 'class' => 'st_special_command'))
			. "<br/>"; 		
		
			
		return $links;
	}
	
	
	
}
