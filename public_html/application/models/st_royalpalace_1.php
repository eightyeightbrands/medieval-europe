<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Royalpalace_1_Model extends Structure_Model
{
	public function init()
	{	
		$this -> setCurrentLevel(1);
		$this -> setParenttype('royalpalace');
		$this -> setSupertype('royalpalace');
		$this -> setMaxlevel(1);
		$this -> setIsbuyable(false);
		$this -> setIssellable(false);	
		$this -> setStorage(100000000);	
		$this -> setWikilink('En_US_royalpalace');
	}
		
	public function build_common_links( $structure )
	{
		
		$links = parent::build_common_links( $structure );
			
		$links .= html::anchor( "/structure/info/" . $structure -> id, Kohana::lang('structures_actions.global_info'), array('class' => 'st_common_command')) . "<br/>" ;		
		$links .= html::anchor( "/structure/donate/" . $structure -> id, Kohana::lang('structures_actions.global_deposit'), array('class' => 'st_common_command')) . "<br/>";
		$links .= html::anchor( "/royalpalace/throne_room/" . $structure -> id, Kohana::lang('structures_actions.throne_room'), array('class' => 'st_common_command')). "<br/>";			
		$links .= html::anchor( "/royalpalace/declarerevolt/" . $structure -> id, Kohana::lang('structures_royalpalace.declarerevolt'), array('class' => 'st_common_command')). "<br/>";
		
		return $links;
	}

	public function build_special_links( $structure )
	{
		// setta i link comuni a tutte le strutture
		
		$links = parent::build_special_links( $structure );
		$links .= html::anchor( "/structure/rest/" . $structure -> id, Kohana::lang('global.rest'), array('class' => 'st_special_command')). "<br/>";
		return $links;
	}
}
