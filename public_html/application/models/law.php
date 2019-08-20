<?php defined('SYSPATH') OR die('No direct access allowed.');

class Law_Model extends ORM
{

protected $belongs_to = array('region');
protected $sorting = array('id' => 'desc');

}
