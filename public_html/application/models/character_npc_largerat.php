	<?php defined('SYSPATH') OR die('No direct access allowed.');

	
class Character_NPC_Largerat_Model extends Character_NPC_Smallrat_Model
{
	
	// i parametri = ai campi delle tabelle
	// vanno settati con get e set.
	
	protected $maxhealth = 20;	
	protected $respawntime = 120;
	protected $rate = 0.025;
	protected $maxnumber = 1000;
	
		
	function create( $name )
	{		
		parent::create( $name );
		$this -> setName ( $name );	
		$this -> setNpctag( 'largerat' );
		$this -> setStr(4);		
		$this -> setDex(4);
		$this -> setSex('M');		
		$this -> setIntel(10);		
		$this -> setCost(2);
		$this -> setCar(1);		
		$this -> setGlut(50);
		$this -> setEnergy(50);
		$this -> setHealth(20);		
		$this -> setName ( $name );			
	}
	
}
?>