<?php defined('SYSPATH') OR die('No direct access allowed.');

class Cfgitem_Model extends ORM
{
	const REQUESTEDFP = 3;
	protected $sorting = array('tag' => 'asc');
	protected $belongs_to = array( 'structure_type', 'church' );
	protected $has_many = array( 'cfgitem_dependency' );
	
	
	function get_needed_items()
	{
		// non aperta ad attacchi SQl-injection perchè i parametri non sono passati via request
		
		$db = Database::instance();
			$sql = "select c1.*, cd.type type, cd.quantity 
				from cfgitems c1, cfgitem_dependencies cd"
			. " where cd.cfgitem_id = " . $this -> id 
			. " and cd.source_cfgitem_id = c1.id ";
		
		$neededitems = $db -> query ( $sql )-> as_array();
		return $neededitems;	

	}
}
