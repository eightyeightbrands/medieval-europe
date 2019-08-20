<?php defined('SYSPATH') OR die('No direct access allowed.');

class Board_Factory_Model
{
	
	public function create( $name )
	{
		// assumes the use of an autoloader
        
		
		$class = ucfirst($name) . "_Forum_Board_" . "Model";
		kohana::log('debug', '-> Factory: Creating class : ' . $class );
        
		if (class_exists($class)) {
            return new $class();
        }
        else {
            throw new Exception("-> BoardFactory: Invalid class given ($class	).");
        }		
		//var_dump($class);exit;	
		return $class;
	
	}
	
}
