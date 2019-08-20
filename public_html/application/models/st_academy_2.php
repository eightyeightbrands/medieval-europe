<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Academy_2_Model extends ST_Academy_1_Model
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
	
	/**
	* Ritorna informazioni sui corsi installati
	* @param none
	* @return array corsi installati
	*/
	
	function getInstalledcourses()
	{
		
		$courses = parent::getInstalledcourses();
		
		// Carica quelli configurati
		
		$coursestats = Structure_Model::get_stats('course');
		if (!is_null($coursestats))
			foreach ($coursestats as $coursestat)
				$courses[] = $coursestat -> searchparam1;
		//var_dump($courses); exit;
		return array_unique($courses);
				
	}
			
}
