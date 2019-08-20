<?php defined('SYSPATH') OR die('No direct access allowed.');

class Course_Metallurgy_1_Model extends Course_Model
{
	
	protected $coursetype = 'skill';
	protected $linkedskill = 'recuperateiron';
	
	/**
	* Ritorna il livello a cui può essere studiato il corso
	* @param obj $char Character_Model
	* @return int Livello a cui si può studiare il corso
	*/
	
	public function getLevel( $char )
	{
		return 1;
	}
		
	/**
	* Complete il corso
	* @param obj $char Character_Model
	* @return none
	*/
	
	public function completeCourse( $char ) 
	{
		
		$skill = Skillfactory_Model::create('recuperateiron');
		$skill -> add( $char );
		
		Character_Event_Model::addrecord( 
			$char -> id,
			'normal',  
			'__events.coursecompletedskill'.';__' . 'structures.course_' . $this -> getTag() . '_name',
			'evidence'
			);
			
	}

	
}
