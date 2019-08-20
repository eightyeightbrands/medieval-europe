<?php defined('SYSPATH') OR die('No direct access allowed.');

class Battle_participant_Model extends ORM
{
	protected $table_name = "battle_participants";
	protected $belongs_to = array('battle');
	protected $has_one = array( 'character' );

	
}
