<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Forest_Model extends Structure_Model
{
	public function init()
	{		
		$this -> setIsbuyable(false);
		$this -> setIssellable(false);	
		$this -> setWikilink('The_Forest');				
	}
	
	// Funzione che costruisce i links comuni relativi alla struttura
	// @output: stringa contenente i links relativi a questa struttura
	public function build_common_links( $structure, $bonus = false )
	{
		$links = parent::build_common_links( $structure );
		
		$links .= html::anchor( "/structure/info/" . $structure -> id, Kohana::lang('structures_actions.global_info'), 
			array('class' => 'st_common_command')) . "<br/>" ;			

		// Azioni comuni accessibili a tutti i chars
		$links .= html::anchor( "/forest/getwood/" . $structure -> id, Kohana::lang('structures_actions.forest_getwood'),
		array('title' => Kohana::lang('structures_actions.forest_getwood_info'), 'class' => 'st_common_command',
		'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')') );
		
		if ( $bonus !== false )
		{
		   	$links .= ' - '.html::anchor( "/forest/getwood/".$structure->id."/2", 'x2',
		    array('title' => Kohana::lang('structures_actions.forest_getwood').' (x2)', 'class' => 'st_common_command',
					'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')') );
		    
		   	$links .= ' - '.html::anchor( "/forest/getwood/".$structure->id."/3", 'x3',
		    array('title' => Kohana::lang('structures_actions.forest_getwood').' (x3)', 'class' => 'st_common_command',
					'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')') );
		}
				
		$links .= "<br/>";

		$links .= html::anchor( "/forest/searchplant/" . $structure -> id, Kohana::lang('structures_actions.forest_searchplant'),
		array('title' => Kohana::lang('structures_actions.forest_searchplant_info'), 'class' => 'st_common_command',
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')') );

		if ( $bonus !== false )
		{
		   	$links .= ' - '.html::anchor( "/forest/searchplant/".$structure->id."/2", 'x2',
		    array('title' => Kohana::lang('structures_actions.forest_searchplant').' (x2)', 'class' => 'st_common_command',
					'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')') );
		    
		   	$links .= ' - '.html::anchor( "/forest/searchplant/".$structure->id."/3", 'x3',
		    array('title' => Kohana::lang('structures_actions.forest_searchplant').' (x3)', 'class' => 'st_common_command',
				'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')') );
		}

		return $links;
	}

	// Funzione che costruisce i links speciali relativi alla struttura
	// @output: stringa contenente i links relativi a questa struttura
	public function build_special_links( $structure )
	{
		$links = null;
		return $links;
	}
}
