<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Battlefield_1_Model extends Structure_Model
{
	
	
	public function init()
	{		
		$this -> setCurrentLevel(1);
		$this -> setParenttype('battlefield');
		$this -> setSupertype('battlefield');
		$this -> setMaxlevel(1);
		$this -> setIsbuyable(false);
		$this -> setIssellable(false);	
		$this -> setStorage(10000000000);
	}	
	
	// Funzione che costruisce i links comuni relativi alla struttura
	// @output: stringa contenente i links relativi a questa struttura
	
	public function build_common_links( $structure )
	{
		// Azioni comuni accessibili a tutti i chars
		
		$links = "";
		
		$battle = ORM::factory("battle", $structure -> attribute1 ) ;				
		
		if ( $battle -> type == 'nativerevolt' )
		{			
			$links .= Kohana::lang('structures_battlefield.nativerevoltbattleinfo');
		}		
		else
		{		
	
			$s = $structure; 			
			$b = ORM::factory("battle", $s -> attribute1) ;
			$region_attacking = ORM::factory("region", $b -> source_region_id) ;
			$region_attacked = ORM::factory("region",  $b -> dest_region_id ) ;
			$links .= ($b -> type == 'raid') ? 
				Kohana::lang('structures_battlefield.raidinfo',
				kohana::lang($region_attacking -> kingdom -> name), 
				kohana::lang($region_attacked -> name)) : 
				Kohana::lang('structures_battlefield.siegeinfo',
				kohana::lang($region_attacking -> kingdom -> name), 
				kohana::lang($region_attacked -> name));		
		}
		
		
		$links .= "<br/><br/>";
		
		$links .= html::anchor( "/battlefield/enter/" . $structure -> id, Kohana::lang('structures_battlefield.enterbattlefield'),
			array('class' => 'st_common_command'))."<br/>";	

		return $links;
	}
	
	// Funzione che costruisce i links speciali relativi alla struttura
	// @output: stringa contenente i links relativi a questa struttura
	
	public function build_special_links( $structure )
	{
				
		
		// Azioni speciali accessibili solo al char che governa la struttura
		
		$links = "";
	
		return $links;
	}

	
}