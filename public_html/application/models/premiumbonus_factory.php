<?php defined('SYSPATH') OR die('No direct access allowed.');

class PremiumBonus_Factory_Model
{
	
	public function create( $name )
	{
		// assumes the use of an autoloader
        
		if ( strpos ($name, 'atelier-license') !== false )
			$name = str_replace(  '-', '_', $name );	
		
		$class = "PremiumBonus_" . ucfirst($name) . "_Model";
		kohana::log('debug', '-> Factory: Creating class : ' . $class );
        
		if (class_exists($class)) {
            return new $class();
        }
        else {
            throw new Exception("-> PremiumBonusFactory: Invalid class given ($class	).");
        }		
		//var_dump($class);exit;	
		return $class;
	
	}
	
}
