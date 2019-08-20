<?php defined('SYSPATH') OR die('No direct access allowed.');

class Group_character_Model extends ORM
{
	protected $has_one = array( 'character' );
}
