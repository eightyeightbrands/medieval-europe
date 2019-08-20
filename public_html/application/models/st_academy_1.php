<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Academy_1_Model extends Structure_Model
{

	const LEVEL = 0;
	const PAPERS_LESSONHOURS = 3;
	protected $basecourses = array
	( 
		'logica',
		'retorica',
	);
	
	protected $installablecourses = array
	(
		'metallurgy_1',
	);
	
	public function init()
	{
		$this -> setCurrentLevel(1);
		$this -> setParenttype('academy');
		$this -> setSupertype('academy');
		$this -> setMaxlevel(2);
		$this -> setIsbuyable(false);
		$this -> setIsupgradable(true);
		$this -> setIssellable(false);				
		$this -> setHoursfornextlevel(1300);			
		$this -> setNeededmaterialfornextlevel(
			array(
				'iron_piece' => 900,
				'wood_piece' => 1100,
				'stone_piece' => 1100,
			)
		);
		$this -> setWikilink('En_US_TheAcademy');
		$this -> setStorage(10000000);
	}
		
	// Funzione che costruisce i links relativi
	// @output: stringa contenente i links relativi a questa struttura
	
	public function build_common_links( $structure )
	{
				
		$links = parent::build_common_links( $structure );
		
		$links .= html::anchor( "/structure/donate/" . $structure -> id, Kohana::lang('structures_actions.global_deposit'), array('class' => 'st_common_command')) . "<br/>" ;
		$links .= html::anchor( "/structure/info/" . $structure -> id, Kohana::lang('structures_actions.global_info'), array('class' => 'st_common_command')) . "<br/>" ;		
		$links .= html::anchor( "/academy/study/" . $structure -> id, Kohana::lang('structures_actions.global_study'), array('class' => 'st_common_command')) . "<br/>" ;
		
		return $links;
	}
	
	public function build_special_links( $structure )
	{
			
		$links = parent::build_special_links( $structure );				
		$links .= html::anchor( 
			"/structure/rest/" . $structure -> id, Kohana::lang('global.rest'),
				array('title' => Kohana::lang('global.rest'), 'class' => 'st_special_command'))
			. "<br/>"; 		

		return $links;
	}	
		
	
}
