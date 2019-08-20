<?php defined('SYSPATH') OR die('No direct access allowed.');

class Disease_Drunkness_Model extends Disease_Model
{

	protected $level = 1;
	protected $name = 'drunkness';
	protected $diffusion = 0; // percentuale -> 5%
	protected $hpmalus = 0;
	protected $checkinterval = 0;
	protected $strmalus = -3;
	protected $dexmalus = -3;
	protected $intelmalus = -6;
	protected $costmalus = 0;
	protected $carmalus = 0;
	protected $iscyclic = false;
	protected $iscurable = false;
	protected $isblocking = true;	
	protected $relatedaction = 'none';
	protected $timedipendent = 'Y';
	
	public function apply_effects( $char ) {
		return;		
	}
	
	/*
	** Torna la durata della malattia
	*  
	*  @param obj $char Character_Model
	*  @return int durata della malattia
	*/
	
	public function get_duration( $char )
	{
		// 6 hours
		return  6 * 3600;
	}
	
}
