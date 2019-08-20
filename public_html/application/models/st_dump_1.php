<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Dump_1_Model extends Structure_Model
{
	public function init()
	{		
	
		$this -> setCurrentLevel(1);
		$this -> setParenttype('dump');
		$this -> setSupertype('dump');
		$this -> setMaxlevel(1);
		$this -> setIsbuyable(false);
		$this -> setIssellable(false);	
		$this -> setStorage(10000000000);	
		$this -> setWikilink('En_US_TheDump');
	}
	
	// Funzione che costruisce i links comuni relativi alla struttura
	// @output: stringa contenente i links relativi a questa struttura
	public function build_common_links( $structure )
	{
		$links = parent::build_common_links( $structure );
		
		$links .= html::anchor( "/structure/donate/" . $structure -> id, Kohana::lang('structures_actions.global_deposit'), array('class' => 'st_common_command')) . "<br/>";
		$links .= html::anchor( "/dump/search/" . $structure -> id, Kohana::lang('structures_dump.search'), array('class' => 'st_common_command')) . "<br/>";
		
		return $links;
	}

	// Funzione che costruisce i links speciali relativi alla struttura
	// @output: stringa contenente i links relativi a questa struttura
	public function build_special_links( $structure )
	{
		// Azioni speciali accessibili solo al char che governa la struttura
		$links = parent::build_special_links( $structure );

		return $links;
	}
	
	
	
}
