<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Mine_clay_Model extends Structure_Model
{
	
	public function init()
	{		
		$this -> setCurrentLevel(1);
		$this -> setMaxlevel(1);
		$this -> setParenttype('mine');
		$this -> setSupertype('mine_clay');
		$this -> setIsbuyable(false);
		$this -> setIssellable(false);						
		$this -> setWikilink('The_Mine');		
	}
	
	// Funzione che costruisce i links comuni relativi alla struttura
	// @output: stringa contenente i links relativi a questa struttura
	public function build_common_links( $structure, $bonus = false )
	{
		$links = parent::build_common_links( $structure );
		
		$links .= html::anchor( "/structure/info/" . $structure -> id, Kohana::lang('structures_actions.global_info'), 
			array('class' => 'st_common_command')) . "<br/>" ;		

		// Azioni comuni accessibili a tutti i chars
		$links .= html::anchor( "/mine_clay/shovel/" . $structure -> id, Kohana::lang('structures_mine_clay.mine_dig'), array( 'class' => 'st_common_command',
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')') );

		if ( $bonus !== false )
		{
		   	$links .= ' - '.html::anchor( "/mine_clay/shovel/".$structure->id."/2", 'x2',
		    array('title' => Kohana::lang('structures_mine_clay.mine_dig').' (x2)', 'class' => 'st_common_command',
					'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')') );
		    
		   	$links .= ' - '.html::anchor( "/mine_clay/shovel/".$structure->id."/3", 'x3',
		    array('title' => Kohana::lang('structures_mine_clay.mine_dig').' (x3)', 'class' => 'st_common_command',
					'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')') );
		}
				
		$links .= "<br/>";

		return $links;
	}

	// Funzione che costruisce i links speciali relativi alla struttura
	// @output: stringa contenente i links relativi a questa struttura
	public function build_special_links( $structure )
	{
		$links = '';
		return $links;
	}
}
