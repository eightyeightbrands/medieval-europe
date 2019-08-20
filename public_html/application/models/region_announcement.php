<?php defined('SYSPATH') OR die('No direct access allowed.');

class Region_Announcement_Model extends ORM
{
	protected $table_name = "regions_announcements";
	protected $sorting = array('id' => 'desc');
	protected $belongs_to = array( 'region' ); 
}
