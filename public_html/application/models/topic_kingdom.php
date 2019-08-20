<?php defined('SYSPATH') OR die('No direct access allowed.');

class Topic_Kingdom_Model extends Topic_Model
{			
	
	protected $table_name = 'kingdom_forum_topic';
	
	function add()
	{
		
		if ( $char -> id != Kingdom_Model::get_king( $char -> region -> kingdom_id ) )
		{ 
			$message = 'global.operationnotallowed');
			return false;
		}
	}
	
	function edit()
	{
		if ( $char -> id != Kingdom_Model::get_king( $char -> region -> kingdom_id ) )
		{ 
			$message = 'global.operationnotallowed');
			return false;
		}		
	}

}
