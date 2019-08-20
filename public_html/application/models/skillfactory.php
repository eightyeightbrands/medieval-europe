<?php defined('SYSPATH') OR die('No direct access allowed.');

class SkillFactory_Model
{
	/**
	* Istanzia una classe Skill
	* @param str $name Nome del corso
	* @return obj 
	*/
	
	public function create($tag)
	{		
		kohana::log('debug', '----- SKILLFACTORY -----');				
		$class = "Skill_" . ucfirst($tag) . '_Model';
		
		kohana::log('debug', "-> Skill Factory: Creating class : [{$class}], type [{$tag}]");
		 
		if (class_exists($class))
			$instance = new $class();			
		else
			throw new Exception("-> Skill Factory: Invalid class given [($class)].");		
		return $instance;
	}
	
}