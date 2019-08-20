<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Breeding_silkworm_region_Model extends Structure_Model
{
	public function init()
	{		
		$this -> setIsbuyable(false);
		$this -> setIssellable(false);						
		$this -> setBaseprice(100);
		$this -> setWikilink('Farms');	
	}
	
	// Funzione che costruisce i links comuni relativi alla struttura
	// @output: stringa contenente i links relativi a questa struttura
	public function build_common_links( $structure )
	{
		
		$links = parent::build_common_links( $structure );
		
		$links .= html::anchor( "/structure/info/" . $structure -> id, Kohana::lang('structures_actions.global_info'), 
			array('class' => 'st_common_command')) . "<br/>" ;	
		$links .= Kohana::lang('structures_breeding_silkworms.silkworm_structure') . $structure->structure_type->attribute2 ."<br/>";
		$links .= '<br/>';

		// Azioni comuni accessibili a tutti i chars
		if ( $structure -> region -> is_independent() == false ) 
			$links .= html::anchor( "/breeding/buyanimals/"  . $structure -> structure_type -> type, Kohana::lang('structures_actions.breeding_buyanimals'),
				array('class' => 'st_common_command' )). "<br/>";		
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
