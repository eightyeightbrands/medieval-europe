<?php defined('SYSPATH') OR die('No direct access allowed.');

class GameEvent_Subscription_Model extends ORM
{
	
	protected $has_one = array('cfggameevent') ; 
	
}