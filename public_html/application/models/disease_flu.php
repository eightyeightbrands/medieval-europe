<?php defined('SYSPATH') OR die('No direct access allowed.');

class Disease_Flu_Model extends Disease_Model
{

	protected $level = 1;
	protected $name = 'flu';
	protected $diffusion = 10;
	protected $hpmalus = 0;
	protected $checkinterval = 12;
	protected $strmalus = -1;
	protected $dexmalus = -1;
	protected $intelmalus = 0;
	protected $costmalus = 0;
	protected $carmalus = 0;	
	protected $iscyclic = true;
	protected $isblocking = false;
	protected $timedipendent = 'N';
	protected $cooldown = 20;
	protected $relatedaction = 'disease';
	protected $requireditem = 'mandragora_brew';
	protected $timetocure = 1;

	
	public function apply_effects( $char ) 	
	{
		kohana::log( 'info', "-> **** Trying to apply effects to: {$char -> name}");
		kohana::log( 'info', '-> Applying Flu effects.');
		
		$char -> modify_health( $this -> hpmalus );
		$char -> save();
		
		Character_Event_Model::addrecord(
			$char -> id,
			'normal',
			'__events.plagueeffect;' . $this -> hpmalus,
			'evidence'			
		);	
		
	}
	
}
