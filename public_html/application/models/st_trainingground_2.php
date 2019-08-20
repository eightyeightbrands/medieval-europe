<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Trainingground_2_Model extends ST_Trainingground_1_Model
{

	const LEVEL = 0;
	const PAPERS_LESSONHOURS = 3;		
	
	public function init()
	{
		parent::init();
		$this -> setCurrentLevel(2);			
	}
		
	// Funzione che costruisce i links relativi
	// @output: stringa contenente i links relativi a questa struttura
	
	public function build_common_links( $structure )	
	{
		$links = parent::build_common_links( $structure );
				
		return $links;
	}
			
}
