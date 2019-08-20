<?php defined('SYSPATH') OR die('No direct access allowed.');

class Structure_Lentitem_Model extends ORM
{
	
	protected $table_name = 'structure_lentitems';
	protected $belongs_to = array('structure');

	
}