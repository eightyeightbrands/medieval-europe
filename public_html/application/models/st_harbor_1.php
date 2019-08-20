<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Harbor_1_Model extends Structure_Model
{
	public function init()
	{		
		$this -> setCurrentLevel(1);
		$this -> setMaxlevel(1);		
		$this -> setParenttype('harbor');		
		$this -> setSupertype('harbor');
		$this -> setIsbuyable(false);
		$this -> setIssellable(false);	
		$this -> setWikilink('En_US_Harbor');
	}

	// Funzione che costruisce i links comuni relativi alla struttura
	// @output: stringa contenente i links relativi a questa struttura
	public function build_common_links( $structure )
	{
		$links = parent::build_common_links( $structure );
		
		// Azioni comuni accessibili a tutti i chars
		$links .= html::anchor( "/map/view", Kohana::lang('structures_actions.harbor_sail'), array('class' => 'st_common_command')) . "<br/>";
		$links .= html::anchor( "/structure/info/" . $structure -> id, Kohana::lang('structures_actions.global_info'), array('class' => 'st_common_command')) . "<br/>" ;	
				
		return $links;
	}

	// Funzione che costruisce i links speciali relativi alla struttura
	// @output: stringa contenente i links relativi a questa struttura
	public function build_special_links( $structure)
	{
		$links = '';
		return $links;
	}
}
