<?php defined('SYSPATH') OR die('No direct access allowed.');

class Religion_Model extends ORM
{	
	protected $has_many = array('church');	
}
