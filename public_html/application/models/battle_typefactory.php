<?php defined('SYSPATH') OR die('No direct access allowed.');

class Battle_TypeFactory_Model
{
	
	public function create( $name )
	{
		// assumes the use of an autoloader
        
		$class = "Battle_" . ucfirst($name) . "_Model";
		kohana::log('debug', '-> Factory: Creating class : ' . $class );
        
		if (class_exists($class)) {
            return new $class();
        }
        else {
            throw new Exception("-> Battle Type Factory: Invalid battle type class given ($name).");
        }		
		
		return $class;
	
	}
}
