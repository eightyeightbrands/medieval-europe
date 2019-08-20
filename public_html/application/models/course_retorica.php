<?php defined('SYSPATH') OR die('No direct access allowed.');

class Course_Retorica_Model extends Course_Model
{
	
	protected $coursetype = 'attribute';
	
	/**
	* Ritorna il livello a cui può essere studiato il corso
	* @param obj $char Character_Model
	* @return int Livello a cui si può studiare il corso
	*/
	
	public function getLevel( $char )
	{
		return $char -> get_attribute( 'car', false) + 1 ;		
	}
	
	/**
	* Completa il corso
	* @param obj $char Character_Model
	* @return none
	*/
	
	public function completeCourse( $char ) 
	{
		
		$oldvalue = $char -> car;
		$newvalue = min (20, $char -> car + 1) ;
		$char -> car = $newvalue;
		$increasedattr = 'create_charcar';				
				
		if ( $char -> car == 20 ) 
			Achievement_Model::compute_achievement ( 'stat_car', 20, $char -> id ); 		
		
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
