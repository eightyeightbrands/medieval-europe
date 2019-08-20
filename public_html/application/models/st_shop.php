<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Shop_Model extends Structure_Model
{	
	
	public function init()
	{		
		$this -> setCurrentLevel(1);
		$this -> setIsbuyable(true);
		$this -> setIssellable(true);
		$this -> setBaseprice(400);
		$this -> setStorage(250000);
		$this -> setParenttype('shop');
		$this -> setMaxlevel(2);
		$this -> setWikilink('Workshops');
	}	
	
	public function build_common_links( $structure ) 
	{
		$links = parent::build_common_links( $structure );
		
		// Informations
		$links .= html::anchor( "/structure/info/" . $structure -> id, Kohana::lang('structures_actions.global_info'), array('class' => 'st_common_command')) . "<br/>";
		
		
		return $links;
	}
	
	
	// Funzione che costruisce i links relativi
	// @output: stringa contenente i links relativi a questa struttura
	
	public function build_special_links( $structure )
	{

		$links = parent::build_special_links( $structure );

		// craft
		$links .= html::anchor( "/structure/listcraftableitems/" . $structure -> id, Kohana::lang('global.craft'),
			array('class' => 'st_special_command')). "<br/>";
		// sell		
		$links .= html::anchor( "structure/sell/". $structure -> id, Kohana::lang('global.sell'),		
			array (
			'class' => 'st_special_command',
			'title' => Kohana::lang('global.sell' ) )) . "<br/>"; 
				
		return $links;
	}	

}
