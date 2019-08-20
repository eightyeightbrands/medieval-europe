<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Well_1_Model extends Structure_Model
{
	
	// Costanti
	const BASE_WATER = 1;
	
	public function init()
	{		
		$this -> setCurrentLevel(1);
		$this -> setMaxlevel(1);
		$this -> setParenttype('well');
		$this -> setSupertype('well');
		$this -> setIsbuyable(false);
		$this -> setIssellable(false);
			
	}
	
	// Funzione che costruisce i links comuni relativi al pozzo
	// @output: stringa contenente i links
	public function build_common_links( $structure, $bonus = false )
	{
		
		$links = parent::build_common_links( $structure );
		$links .= html::anchor( "/structure/info/" . $structure -> id, Kohana::lang('structures_actions.global_info'), 		array('class' => 'st_common_command')) . "<br/>" ;		
		
		$links .= html::anchor( "/well/collect_water/".$structure->id, Kohana::lang('structures_well.collect_water'),
		    array('title' => Kohana::lang('structures_well.collect_water').' (x2)', 'class' => 'st_common_command',
					'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ));
		
		if ( $bonus !== false )
		{
		   	$links .= ' - '.html::anchor( "/well/collect_water/".$structure->id."/2", 'x2',
		    array('title' => Kohana::lang('structures_well.collect_water').' (x2)', 'class' => 'st_common_command',
					'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ));
		    
		   	$links .= ' - '.html::anchor( "/well/collect_water/".$structure->id."/3", 'x3',
		    array('title' => Kohana::lang('structures_well.collect_water').' (x3)', 'class' => 'st_common_command',
					'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ));
		}
		
		return $links;
	}
	
	// Funzione che costruisce i links speciali del pozzo
	// @output: stringa contenente i links
	public function build_special_links( $structure )
	{
		$links = "";
		return $links;
	}	

	
	/**
	* Funzione che calcola la quantità di acqua che �
	* possibile estrarre
	* @param  none
	* @return qtà di acqua
	*/
	public function get_num_water()
	{
		return self::BASE_WATER;
	}

}
