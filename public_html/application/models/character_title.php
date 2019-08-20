<?php defined('SYSPATH') OR die('No direct access allowed.');

class Character_Title_Model extends ORM
{
  protected $sorting = array('position' => 'asc'); 
  protected $belongs_to = array('cfgachievement');
  
}