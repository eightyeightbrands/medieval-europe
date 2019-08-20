<?php defined('SYSPATH') OR die('No direct access allowed.');

class User_Language_Model extends ORM
{
	protected $belongs_to = array( 'user' );		
}