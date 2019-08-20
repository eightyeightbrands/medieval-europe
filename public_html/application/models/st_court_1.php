
<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Court_1_Model extends Structure_Model
{	
	public function init()
	{	
		$this -> setCurrentLevel(1);
		$this -> setParenttype('court');
		$this -> setSupertype('court');
		$this -> setMaxlevel(1);
		$this -> setIsbuyable(false);
		$this -> setIssellable(false);	
		$this -> setStorage(5000000);
		$this -> setWikilink('En_US_TheCourt');		
		
	}
	
	// Funzione che costruisce i links relativi
	// @output: stringa contenente i links relativi a questa struttura
	
	public function build_common_links( $structure ) 
	{
		$links = parent::build_common_links( $structure );		

		
		$links .= html::anchor( "/structure/donate/" . $structure -> id, Kohana::lang('structures_actions.global_deposit'), array('class' => 'st_common_command')) . "<br/>";
		$links .= html::anchor( "/structure/info/" . $structure -> id, Kohana::lang('structures_actions.global_info'), array('class' => 'st_common_command')) . "<br/>" ;	
		
		return $links;
	}
	
	public function build_special_links( $structure )
	{
		
		$links = parent::build_special_links( $structure );		
		$links .= html::anchor( "/court/manage/" . $structure -> id, Kohana::lang('global.manage'), array('class' => 'st_special_command')). "<br/>";
		$links .= html::anchor( 
			"/structure/rest/" . $structure -> id, Kohana::lang('global.rest'),
				array('title' => Kohana::lang('global.rest'), 'class' => 'st_special_command'))
			. "<br/>"; 		

		
		return $links;
	}
	
}
