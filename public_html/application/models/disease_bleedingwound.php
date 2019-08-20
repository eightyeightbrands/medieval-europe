<?php defined('SYSPATH') OR die('No direct access allowed.');

class Disease_Bleedingwound_Model extends Disease_Model
{
	
	protected $level = 2;
	protected $name = 'bleedingwound';
	protected $diffusion = 0; // percentuale -> 5%
	protected $hpmalus = -1;
	protected $checkinterval = 4;
	protected $strmalus = -2;
	protected $dexmalus = -2;	
	protected $intelmalus = 0;
	protected $costmalus = -1;
	protected $iscurable = true;
	protected $iscyclic = true;
	protected $carmalus = 0;	
	protected $isblocking = false;
	protected $timedipendent = 'N';
	protected $cooldown = 0;
	protected $relatedaction = 'disease';
	protected $requireditem = 'surgicalkit';
	protected $timetocure = 4;
	
	public function apply_effects( $char ) 	
	{
		
		kohana::log( 'info', "-> **** Trying to apply effects to: {$char -> name}");
		
		kohana::log('info', '-> Applying bleedingwounds effects.');
		
		$char -> modify_health( $this -> hpmalus, false, 'bleedingwounds' );
		$char -> save();
		
		Character_Event_Model::addrecord(
			$char -> id,
			'normal',
			'__events.bleedingwoundeffect;' . $this -> hpmalus,
			'evidence'			
		);		
	}
	
}
