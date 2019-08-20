<?php defined('SYSPATH') OR die('No direct access allowed.');

class Disease_Tipsyness_Model extends Disease_Model
{

	protected $level = 1;
	protected $name = 'tipsyness';
	protected $diffusion = 0; // percentuale -> 5%
	protected $hpmalus = 0;
	protected $checkinterval = 0;
	protected $strmalus = -3;
	protected $dexmalus = -3;
	protected $intelmalus = -6;
	protected $iscurable = false;  			// è curabile?
	protected $costmalus = 0;
	protected $carmalus = 0;
	protected $iscyclic = false;		
	protected $isblocking = false;	
	protected $timedipendent = 'Y';
	protected $relatedaction = 'none';

	public function apply_effects( $char ) {
		return;		
	}
	
}
