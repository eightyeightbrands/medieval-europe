<?php defined('SYSPATH') OR die('No direct access allowed.');

class Kingdom_Forum_Board_Model extends Board_Model
{			
	
	protected $sorting = array('id' => 'asc');	
	protected $belongs_to = array('kingdom');	
	
	function read( $char, $board_id, &$board )
	{
		
		$board = ORM::factory('kingdom_forum_board', $board_id );		
		if ($board -> loaded == false)
		{
			$message = 'global.operation_not_allowed';
			return false;						
		}
		
		if ( Kingdom_Forum_Board_Model::haswriterights( $char, $board -> kingdom ) == false )
		{ 
			$message = 'global.operation_not_allowed';
			return false;
		}
		
		return true;
		
	}
	
	function add( $char, $data, &$message )
	{
		//var_dump($data);exit;
		
		$kingdom = Kingdom_Model::load( $data['kingdom_id'] );		
		if ( $kingdom -> loaded == false )
		{			
			$message = 'global.operation_not_allowed';
			return false;			
		}
		
		if ( Kingdom_Forum_Board_Model::haswriterights( $char, $kingdom ) == false )
		{ 
			$message = 'global.operation_not_allowed';
			return false;
		}
		
		$this -> kingdom_id = $data['kingdom_id'];
		$this -> name = $data['name'];
		$this -> description = $data['boarddescription'];
		$this -> created = date("Y-m-d H:i:s");
		$this -> updated = date("Y-m-d H:i:s");
		$this -> author = $char -> id;
		$this -> save();
		
		return true;
			
	}
	
	function edit($char, $board, $data, &$message)
	{
				
		if ($board -> loaded == false)
		{
			$message = 'global.operation_not_allowed';
			return false;						
		}		
		
		if ( Kingdom_Forum_Board_Model::haswriterights( $char, $board -> kingdom ) == false )
		{ 
			$message = 'global.operation_not_allowed';
			return false;
		}
		
		$board -> name = $data['name'];
		$board -> description = $data['boarddescription'];
		$board -> updated = date( "Y-m-d H:i:s");
		$board -> save();
		
		return true;
		
	}
	
	function delete( $char, $board, &$message)
	{
		
		if ($board -> loaded == false)
		{
			$message = 'global.operation_not_allowed';
			return false;						
		}
		
		if ( Kingdom_Forum_Board_Model::haswriterights( $char, $board -> kingdom ) == false )
		{ 
			$message = 'global.operation_not_allowed';
			return false;
		}
		
		$board -> status = 'deleted';
		$board -> updated = date("Y-m-d H:i:s");
		$board -> save();
		return true;
		
	}
	
	/**
	* Verifica se il char ha permessi di scrittura
	* @param obj $char Oggetto Char
	* @param obj $kingdom Oggetto Regno
	* @return boolean false or true
	*/
	
	function haswriterights( $char, $kingdom)
	{
		
		$currentking = $kingdom -> get_king( $kingdom -> id );		
		
		if ( $char -> id != $currentking -> id and !Auth::instance()->logged_in('admin'))
			return false;

		return true;
		
		if ( Auth::instance()->logged_in('admin') or (!is_null( $currentking) and $char -> id != $currentking -> id ) )
			return true;
				
		return false;				
		
	}

}
