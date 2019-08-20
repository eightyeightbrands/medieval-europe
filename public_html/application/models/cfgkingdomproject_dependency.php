<?php defined('SYSPATH') OR die('No direct access allowed.');

class Cfgkingdomproject_Dependency_Model extends ORM
{
	protected $has_one = array( 'cfgitem' );		
}
?>
