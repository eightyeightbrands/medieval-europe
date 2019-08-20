<?php defined('SYSPATH') OR die('No direct access allowed.');

class DiseaseFactory_Model
{
	
	public static function createDisease( $name )
	{
		// assumes the use of an autoloader
        
		$class = "Disease_" . $name . "_Model";
		kohana::log('debug', '-> Factory: Creating disease: ' . $class );
        
		if (class_exists($class)) {
            return new $class();
        }
        else {
            throw new Exception("-> DiseaseFactory: Invalid disease class given ($name).");
        }		
		
		return $class;
	
	}
}	
