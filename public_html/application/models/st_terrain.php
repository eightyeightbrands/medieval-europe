<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Terrain_Model extends Structure_Model
{
	// Attribute1: lo uso per memorizzare lo stato del campo
	//             0 = campo incolto
	//             1 = campo seminato
	//             2 = campo pronto per il raccolto
	// Attribute2: lo uso per memorizzare l'id di cosa sto coltivando
		
	
	public function init()
	{		
		$this -> setCurrentLevel(1);
		$this -> setIsbuyable(true);
		$this -> setIssellable(true);
		$this -> setBaseprice(100);
		$this -> setSupertype('terrain');
		$this -> setParenttype('terrain');
		$this -> setisUpgradable(true);
		$this -> setMaxlevel(2);
		$this -> setWikilink('En_US_Fields');		
		$this -> setStorage(250000);		
	}	
	

	// Costanti
	const ID_TERRAIN = 1;           // Type id per il tipo terreno
	const TERRAIN_BASE_VALUE = 100; // Valore monetario di base del terreno in game
	
	// Funzione che costruisce i links comuni relativi al terreno
	// @output: stringa contenente i links
	
	public function build_common_links( $structure )
	{
		
		$links = parent::build_common_links( $structure );
		
		$links .= html::anchor( "/structure/info/" . $structure -> id, Kohana::lang('structures_actions.global_info'), array('class' => 'st_common_command')) . "<br/>";	
		
		
		return $links;
	
	}
	
	// Funzione che costruisce i links speciali al terreno
	// @output: stringa contenente i links
		
	public function build_special_links( $structure )
	{
		
		// Link comune a tutti gli stadi di crescita del terreno
		$links = parent::build_special_links( $structure );

		$links .= html::anchor( "/terrain/seed/" . $structure -> id, Kohana::lang('structures_terrain.seed'),
			array('title' => Kohana::lang('structures_terrain.seed'), 'class' => 'st_special_command')). "<br/>";
		
		$links .= html::anchor( "/terrain/harvest/" . $structure -> id, Kohana::lang('structures_terrain.harvest'),
			array('title' => Kohana::lang('structures_terrain.harvest'), 'class' => 'st_special_command')). "<br/>";		
		
		$links .= html::anchor( "/structure/sell/" . $structure -> id, Kohana::lang('global.sell'),
			array('title' => Kohana::lang('global.sell'), 'class' => 'st_special_command')). "<br/>";	
	
		return $links;
	}	

	
	/**
	* Funzione che calcola il numero di terreni previsti per la regione
	* in base alle sua geografia
	* Nota: le capitali hanno tutte il massimo dei terreni previsti
	* @param region regione
	* @return numero di fields
	*/
	
	public function get_maxfield_x_region( $region )
	{
	
		if ($region -> capital)
			return 40;
		else
		{
			switch ( $region -> geography )
			{
			case "plains":
				return 35; break;
			case "hills":
				return 25; break;
			case "mountains":
				return 15; break;
			}
		}
	}
	
}
