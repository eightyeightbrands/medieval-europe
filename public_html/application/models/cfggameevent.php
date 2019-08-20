<?php defined('SYSPATH') OR die('No direct access allowed.');

class CfgGameEvent_Model extends ORM
{
	
	protected $has_many = array('gameevent_subscription') ; 
	
}