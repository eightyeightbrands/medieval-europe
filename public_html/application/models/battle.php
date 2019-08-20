<?php defined('SYSPATH') OR die('No direct access allowed.');

class Battle_Model extends ORM
{
	protected $has_many = array('battle_participant') ; 
}