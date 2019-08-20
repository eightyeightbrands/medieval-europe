<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Watchtower_1_Model extends Structure_Model
{	
	
	public function init()
	{		
		$this -> setCurrentLevel(1);
		$this -> setParenttype('watchtower');
		$this -> setSupertype('watchtower');
		$this -> setMaxlevel(1);
		$this -> setIsbuyable(false);
		$this -> setIssellable(false);	
		$this -> setStorage(250000);	
		$this -> setWikilink('Watchtower');		
	}
	
	// Funzione che costruisce i links comuni relativi alla struttura
	// @output: stringa contenente i links relativi a questa struttura
	
	public function build_common_links( $structure, $bonus = false )
	{
		$char = Character_Model::get_info( Session::instance()->get('char_id') );		
		$links = parent::build_common_links( $structure );
				
		$links .= html::anchor( "/structure/donate/" . $structure -> id, Kohana::lang('structures_actions.global_deposit'), array('class' => 'st_common_command')) . "<br/>";
		
		$info = $structure -> get_info();		
		if ( ! is_null ($info ))
			$links .= html::anchor( "/structure/info/" . $structure -> id, Kohana::lang('structures_actions.global_info'), array('class' => 'st_common_command')) . "<br/>" ;		
		
		if ( Structure_Grant_Model::get_chargrant( $structure,  $char, 'guard_assistant') == true )
		{
			$links .= html::anchor( "/structure/rest/" . $structure -> id, Kohana::lang('global.rest'),
			array('title' => Kohana::lang('global.rest'), 'class' => 'st_special_command')). "<br/>";
			
			$links .= html::anchor( "/watchtower/watch/" . $structure -> id, Kohana::lang('global.watch'),
			array('title' => Kohana::lang('global.watch'), 'class' => 'st_special_command')). "<br/>";
		
		}
		
		$links .= "<br/>";

		return $links;
	}

	// Funzione che costruisce i links speciali relativi alla struttura
	// @output: stringa contenente i links relativi a questa struttura
	
	public function build_special_links( $structure )
	{
		// Azioni speciali accessibili solo al char che governa la struttura
		$links = parent::build_special_links( $structure );
		
		$links .= html::anchor( "/watchtower/manage/" . $structure -> id, Kohana::lang('global.manage'),
			array('title' => Kohana::lang('global.manage'), 'class' => 'st_special_command')). "<br/>";
			
		$links .= html::anchor( 
			"/structure/rest/" . $structure -> id, Kohana::lang('global.rest'),
				array('title' => Kohana::lang('global.rest'), 'class' => 'st_special_command'))
			. "<br/>"; 		

				
		return $links;
	}
	
	
	
}
