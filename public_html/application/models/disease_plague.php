<?php defined('SYSPATH') OR die('No direct access allowed.');

class Disease_Plague_Model extends Disease_Model
{

	protected $level = 3;
	protected $name = 'plague';
	protected $diffusion = 5; // percentuale -> 5%
	protected $hpmalus = -10;
	protected $checkinterval = 12;
	protected $strmalus = -5;
	protected $dexmalus = -5;
	protected $intelmalus = -2;
	protected $costmalus = -7;
	protected $carmalus = 0;	
	protected $iscurable = true;  			// è curabile?
	protected $iscyclic = true;		
	protected $isblocking = false;
	protected $timedipendent = 'N';
	protected $cooldown = 30;
	protected $relatedaction = 'disease';
	protected $requireditem = 'potion_violet';
	protected $timetocure = 8;
	
	public function apply_effects( $char ) 	
	{
		
		kohana::log( 'info', "-> **** Trying to apply effects to: {$char -> name}");
		
		kohana::log('info', '-> Applying plague effects.');
		
		$char -> modify_health( $this -> hpmalus, false, 'plague' );
		$char -> save();
		
		Character_Event_Model::addrecord(
			$char -> id,
			'normal',
			'__events.plagueeffect;' . $this -> hpmalus,
			'evidence'			
		);		
	}
	
}
