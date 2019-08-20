<?php defined('SYSPATH') OR die('No direct access allowed.');

class Course_Logica_Model extends Course_Model
{
	
	protected $coursetype = 'attribute';
	
	/**
	* Ritorna il livello a cui può essere studiato il corso
	* @param obj $char Character_Model
	* @return int Livello a cui si può studiare il corso
	*/
	
	public function getLevel( $char )
	{
		return $char -> get_attribute( 'intel', false) + 1 ;		
	}
		
	/**
	* Completa il corso
	* @param obj $char Character_Model
	* @return none
	*/
	
	public function completeCourse( $char ) 
	{
		
		$oldvalue = $char -> intel;
		$newvalue = min (20, $char -> intel + 1) ;
		$char -> intel = $newvalue;
		$increasedattr = 'create_charint';				
				
		if ( $char -> intel == 20 ) 
			Achievement_Model::compute_achievement ( 'stat_intel', 20, $char -> id ); 
		
		Character_Model::modify_stat_d( 
			$char -> id,
			'studiedhours', 
			0,
			$this -> getTag(),
			null, 
			true,
			0);
			
		Character_Event_Model::addrecord( 
			$char -> id,
			'normal',  
			'__events.coursecompleted'.';__' . 'structures.course_' . $this -> getTag() . '_name' . ';__character.' . $increasedattr . 
			';' . $oldvalue . ';' . $newvalue,
			'evidence'
			);
			
	}
	
	
	
	
}
